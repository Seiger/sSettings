<?php namespace Seiger\sSettings\Support;

use Illuminate\Database\Eloquent\Builder;
use EvolutionCMS\Models\SystemSetting;

final class SystemSettingsStore
{
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
     * @param array<string, array<string, mixed>> $schema
     * @param array<string, mixed> $values
     */
    public function saveValues(array $schema, array $values): void
    {
        foreach ($this->fieldDefinitions($schema) as $field) {
            $name = 'sset_' . $field['name'];
            if (!array_key_exists($name, $values)) {
                continue;
            }
            $value = $this->normalizeValue($field, $values[$name] ?? null);

            $setting = $this->query()->firstOrCreate(['setting_name' => $name]);
            $setting->setting_name = $name;
            $setting->setting_value = $value;
            $setting->save();
            evo()->setConfig($name, $value);
            if (isset($field['_base_name']) && $field['_locale'] === app(SettingsLanguages::class)->defaultLocale()) {
                $baseName = 'sset_' . $field['_base_name'];
                $this->query()->updateOrCreate(['setting_name' => $baseName], ['setting_value' => $value]);
                evo()->setConfig($baseName, $value);
            }
        }

        evo()->clearCache('full');
    }

    /**
     * @param array<string, array<string, mixed>> $schema
     */
    public function syncSchema(array $schema): void
    {
        $definitions = $this->fieldDefinitions($schema);
        $owned = collect($definitions)
            ->map(fn (array $field): string => 'sset_' . $field['name'])
            ->values()
            ->all();

        foreach ($owned as $name) {
            $definition = collect($definitions)->firstWhere('name', substr($name, 5));
            $initialValue = isset($definition['_base_name']) && $definition['_locale'] === app(SettingsLanguages::class)->defaultLocale()
                ? evo()->getConfig('sset_' . $definition['_base_name'], '') : '';
            $setting = $this->query()->firstOrCreate(['setting_name' => $name], ['setting_value' => $initialValue]);
            $setting->setting_name = $name;
            $setting->save();
        }

        // Keep base keys and archived language values while their field still exists.
        // Removing/re-adding a language or disabling multilingual must not erase translations.
        $baseNames = [];
        foreach ($schema as $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $field) {
                if (!empty($field['name']) && $this->fields->storesValue((string) ($field['type'] ?? 'text'))) {
                    $baseNames[] = 'sset_' . $field['name'];
                }
            }
        }
        $owned = array_merge($owned, $baseNames);
        foreach ($this->query()->where('setting_name', 'like', 'sset_%')->pluck('setting_name') as $existingName) {
            foreach ($baseNames as $baseName) {
                if (preg_match('/^' . preg_quote($baseName, '/') . '_[a-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})*$/D', $existingName)) {
                    $owned[] = $existingName;
                    break;
                }
            }
        }

        $this->query()
            ->where('setting_name', 'like', 'sset_%')
            ->whereNotIn('setting_name', $owned)
            ->delete();

        evo()->clearCache('full');
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
