<?php namespace Seiger\sSettings\Support;

use Illuminate\Database\Eloquent\Builder;
use EvolutionCMS\Models\SystemSetting;

/** Persists sSettings values and records the keys created by this package. */
final class SystemSettingsStore
{
    private const OWNERSHIP_KEY = 'ssettings_schema_ownership';

    public function __construct(
        protected FieldCatalog $fields
    ) {
    }

    /**
     * @param array<string, array<string, mixed>> $schema
     * @return array<string, mixed>
     */
    public function values(array $schema): array
    {
        $values = [];

        foreach ($this->fieldDefinitions($schema) as $field) {
            $name = 'sset_' . $field['name'];
            $type = $this->fields->normalizeType((string) ($field['type'] ?? 'text'));
            $fallback = isset($field['_base_name']) && $field['_locale'] === app(SettingsLanguages::class)->defaultLocale()
                ? evo()->getConfig('sset_' . $field['_base_name'], '')
                : $this->defaultValue($field);
            $value = evo()->getConfig($name, $fallback);
            $values[$name] = $this->fields->storesMultipleValues($type)
                ? $this->fields->parseMultipleValue($value)
                : $value;
        }

        return $values;
    }

    /**
     * Write submitted values and record ownership only when this store creates a row.
     *
     * Clear the full cache after the transaction if a submitted value or its base key changed.
     *
     * @param array<string, array<string, mixed>> $schema
     * @param array<string, mixed> $values
     */
    public function saveValues(array $schema, array $values): void
    {
        $definitions = $this->fieldDefinitions($schema);
        [$written, $changed] = $this->withOwnership(function (?array &$ownership) use ($definitions, $values): array {
            $written = [];
            $changed = false;

            foreach ($definitions as $field) {
                $name = 'sset_' . $field['name'];
                if (!array_key_exists($name, $values)) {
                    continue;
                }

                $value = $this->normalizeValue($field, $values[$name] ?? null);
                $base = $field['_base_name'] ?? $field['name'];
                $setting = $this->findOrCreate($name, $base, $value, $ownership);
                $setting->setting_value = $value;
                if (!$setting->save()) {
                    throw new \RuntimeException('Cannot save the setting value.');
                }
                $changed = $changed || $setting->wasRecentlyCreated || $setting->wasChanged('setting_value');
                $written[$name] = $value;

                if (isset($field['_base_name']) && $field['_locale'] === app(SettingsLanguages::class)->defaultLocale()) {
                    $baseName = 'sset_' . $base;
                    $setting = $this->findOrCreate($baseName, $base, $value, $ownership);
                    $setting->setting_value = $value;
                    if (!$setting->save()) {
                        throw new \RuntimeException('Cannot save the default language setting value.');
                    }
                    $changed = $changed || $setting->wasRecentlyCreated || $setting->wasChanged('setting_value');
                    $written[$baseName] = $value;
                }
            }

            return [$written, $changed];
        });

        foreach ($written as $name => $value) {
            evo()->setConfig($name, $value);
        }

        if ($changed) {
            evo()->clearCache('full');
        }
    }

    /**
     * Create missing settings for the supplied schema without inferring removals from it.
     *
     * @param array<string, array<string, mixed>> $schema
     */
    public function syncSchema(array $schema): void
    {
        $definitions = $this->fieldDefinitions($schema);
        if ($definitions === []) {
            return;
        }

        $this->withOwnership(function (?array &$ownership) use ($definitions): void {
            $this->syncDefinitions($definitions, $ownership);
        });

        evo()->clearCache('full');
    }

    /**
     * Save the editor schema and prune only confirmed removals from its persisted baseline.
     *
     * Serialize package writes through the ownership record so a concurrent save cannot
     * replace the baseline between checking it and deleting its retired keys.
     *
     * @param array<mixed> $tabs
     * @param array<string, bool> $retiredFields Removed names map to true and renamed names to false.
     * @return array<string, array<string, mixed>>
     * @since 2.1.0
     */
    public function saveSchema(SettingsSchemaRepository $repository, array $tabs, ?string $revision, array $retiredFields): array
    {
        $validStructure = $repository->hasValidStructure($tabs);
        $candidate = $repository->normalize($tabs);
        $definitions = $this->fieldDefinitions($candidate);
        $nextFields = $this->declaredNames($candidate);

        $saved = $this->withOwnership(function (?array &$ownership) use ($repository, $revision, $retiredFields, $validStructure, $candidate, $definitions, $nextFields): array {
            $previous = $repository->readSnapshot();
            $previousFields = $previous['revision'] === null ? [] : $this->declaredNames($previous['schema']);
            $knownFields = $ownership['fields'] ?? [];
            $missingFields = array_diff($previousFields, $nextFields);
            $completeEdit = $validStructure && $revision !== null && $revision === $previous['revision']
                && array_diff($knownFields, $previousFields) === []
                && array_diff($missingFields, array_keys($retiredFields)) === [];

            $this->syncDefinitions($definitions, $ownership);

            if ($ownership !== null && $completeEdit && $knownFields !== [] && $definitions !== []) {
                $removedFields = array_keys($retiredFields, true, true);
                $this->pruneOwnedKeys($ownership, array_intersect($missingFields, $removedFields), $nextFields, $definitions);
            }

            if ($ownership !== null && $validStructure && ($completeEdit
                || ($missingFields === [] && array_diff($knownFields, $nextFields) === []))) {
                $ownership['fields'] = $nextFields;
            }

            return $repository->save($candidate);
        });

        evo()->clearCache('full');

        return $saved;
    }

    /**
     * Serialize writes and persist the provenance of newly inserted setting rows.
     *
     * @template T
     * @param callable(?array): T $operation Receives the ownership record by reference.
     * @return T
     * @since 2.1.0
     */
    private function withOwnership(callable $operation): mixed
    {
        return $this->query()->getModel()->getConnection()->transaction(function () use ($operation): mixed {
            $record = $this->query()->lockForUpdate()->firstOrCreate(['setting_name' => self::OWNERSHIP_KEY], [
                'setting_value' => '{"version":1,"fields":[],"keys":[]}',
            ]);
            if (!$record->exists) {
                throw new \RuntimeException('Cannot initialize sSettings key ownership.');
            }
            $ownership = json_decode((string) $record->setting_value, true);
            if ($record->setting_name !== self::OWNERSHIP_KEY || ($ownership['version'] ?? null) !== 1
                || !is_array($ownership['fields'] ?? null) || !array_is_list($ownership['fields'])
                || !is_array($ownership['keys'] ?? null)
                || array_filter($ownership['fields'], 'is_string') !== $ownership['fields']
                || array_filter($ownership['keys'], 'is_string') !== $ownership['keys']) {
                $ownership = null;
            }

            $result = $operation($ownership);
            if ($ownership !== null) {
                $record->setting_value = json_encode($ownership, JSON_THROW_ON_ERROR);
                if (!$record->save()) {
                    throw new \RuntimeException('Cannot save sSettings key ownership.');
                }
            }

            return $result;
        });
    }

    /**
     * Claim only keys inserted by sSettings and associate localized keys with their base field.
     *
     * @since 2.1.0
     */
    private function findOrCreate(string $name, string $field, string $initialValue, ?array &$ownership): SystemSetting
    {
        $setting = $this->query()->lockForUpdate()->firstOrCreate(['setting_name' => $name], ['setting_value' => $initialValue]);
        if (!$setting->exists) {
            throw new \RuntimeException('Cannot create the setting value.');
        }
        if ($ownership !== null && $setting->wasRecentlyCreated && $setting->setting_name === $name) {
            $ownership['keys'][$name] = $field;
        }

        return $setting;
    }

    /**
     * Seed missing values while preserving stored values, including archived locales.
     *
     * @param list<array<string, mixed>> $definitions
     * @since 2.1.0
     */
    private function syncDefinitions(array $definitions, ?array &$ownership): void
    {
        foreach ($definitions as $field) {
            $base = $field['_base_name'] ?? $field['name'];
            $initialValue = isset($field['_base_name']) && $field['_locale'] === app(SettingsLanguages::class)->defaultLocale()
                ? (string) evo()->getConfig('sset_' . $base, '') : '';
            $this->findOrCreate('sset_' . $field['name'], $base, $initialValue, $ownership);
        }
    }

    /**
     * Delete exact registered keys for explicitly removed fields, excluding keys still in use.
     *
     * @param array<int, string> $removedFields
     * @param list<string> $nextFields
     * @param list<array<string, mixed>> $definitions
     * @since 2.1.0
     */
    private function pruneOwnedKeys(array &$ownership, array $removedFields, array $nextFields, array $definitions): void
    {
        $activeKeys = array_fill_keys(array_map(static fn (string $name): string => 'sset_' . $name,
            array_merge($nextFields, array_column($definitions, 'name'))), true);
        $retiredKeys = [];
        foreach ($ownership['keys'] as $name => $field) {
            if (in_array($field, $removedFields, true) && str_starts_with((string) $name, 'sset_') && !isset($activeKeys[$name])) {
                $retiredKeys[] = $name;
            }
        }
        if ($retiredKeys === []) {
            return;
        }

        // A case-insensitive database can match a differently spelled name in whereIn().
        $existing = $this->query()->whereIn('setting_name', $retiredKeys)->lockForUpdate()->pluck('setting_name')->all();
        $exact = array_values(array_intersect($retiredKeys, $existing));
        if ($exact !== []) {
            $this->query()->whereIn('setting_name', $exact)->delete();
        }
        foreach ($retiredKeys as $name) {
            unset($ownership['keys'][$name]);
        }
    }

    /**
     * List declared field names without expanding locale keys or ignoring dividers.
     *
     * @param array<string, array<string, mixed>> $schema
     * @return list<string>
     * @since 2.1.0
     */
    private function declaredNames(array $schema): array
    {
        $names = [];
        foreach ($schema as $tab) {
            foreach ($tab['fields'] as $field) {
                $names[] = $field['name'];
            }
        }

        return $names;
    }

    /**
     * @param array<string, array<string, mixed>> $schema
     * @return array<int, array<string, mixed>>
     */
    public function fieldDefinitions(array $schema): array
    {
        $fields = [];
        $declared = [];
        foreach ($schema as $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $field) {
                if (is_array($field) && !empty($field['name'])) {
                    $declared[] = $field;
                }
            }
        }
        // Reserve language suffixes even before that language is enabled in sLang.
        foreach ($declared as $field) {
            if (empty($field['multilingual']) || !$this->fields->supportsMultilingual((string) ($field['type'] ?? 'text'))) {
                continue;
            }
            foreach ($declared as $other) {
                if (preg_match('/^' . preg_quote($field['name'], '/') . '_[a-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/D', $other['name'])) {
                    throw new \RuntimeException(__('sSettings::global.multilingual_key_conflict'));
                }
            }
        }

        foreach ($schema as $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $field) {
                if (!is_array($field) || empty($field['name'])) {
                    continue;
                }

                if (!$this->fields->storesValue((string) ($field['type'] ?? 'text'))) {
                    continue;
                }

                foreach ($this->localizedFields($field) as $localizedField) {
                    $fields[] = $localizedField;
                }
            }
        }

        $names = array_column($fields, 'name');
        if (count($names) !== count(array_unique($names))) {
            throw new \RuntimeException(__('sSettings::global.multilingual_key_conflict'));
        }
        return $fields;
    }

    /** @param array<string, mixed> $field @return list<array<string, mixed>> */
    public function localizedFields(array $field): array
    {
        if (empty($field['multilingual']) || !$this->fields->supportsMultilingual((string) ($field['type'] ?? 'text'))) {
            return [$field];
        }
        $locales = app(SettingsLanguages::class)->locales();
        if ($locales === []) {
            return [$field];
        }
        return array_map(fn (string $locale): array => array_replace($field, [
            'name' => $field['name'] . '_' . $locale,
            '_base_name' => $field['name'],
            '_locale' => $locale,
        ]), $locales);
    }

    /**
     * @param array<string, mixed> $field
     */
    protected function defaultValue(array $field): string
    {
        $type = $this->fields->normalizeType((string) ($field['type'] ?? 'text'));

        return $type === 'checkbox' ? '0' : '';
    }

    /**
     * @param array<string, mixed> $field
     */
    protected function normalizeValue(array $field, mixed $value): string
    {
        return match ($this->fields->normalizeType((string) ($field['type'] ?? 'text'))) {
            'checkbox' => $value ? '1' : '0',
            'checkboxgroup', 'listboxmultiple' => $this->fields->serializeMultipleValue($value),
            default => is_scalar($value) ? trim((string) $value) : '',
        };
    }

    /**
     * @return Builder<SystemSetting>
     */
    protected function query(): Builder
    {
        return (new SystemSetting())->newQuery();
    }
}
