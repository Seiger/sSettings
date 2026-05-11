<?php namespace Seiger\sSettings\Support;

use EvolutionCMS\Models\SystemSetting;

final class SystemSettingsStore
{
    public function __construct(
        protected FieldCatalog $fields
    ) {
    }

    public function values(array $schema): array
    {
        $values = [];

        foreach ($this->fieldDefinitions($schema) as $field) {
            $name = 'sset_' . $field['name'];
            $type = $this->fields->normalizeType((string) ($field['type'] ?? 'text'));
            $value = evo()->getConfig($name, $this->defaultValue($field));
            $values[$name] = $this->fields->storesMultipleValues($type)
                ? $this->fields->parseMultipleValue($value)
                : $value;
        }

        return $values;
    }

    public function saveValues(array $schema, array $values): void
    {
        foreach ($this->fieldDefinitions($schema) as $field) {
            $name = 'sset_' . $field['name'];
            $value = $this->normalizeValue($field, $values[$name] ?? null);

            $setting = SystemSetting::whereSettingName($name)->firstOrCreate();
            $setting->setting_name = $name;
            $setting->setting_value = $value;
            $setting->save();
            evo()->setConfig($name, $value);
        }

        evo()->clearCache('full');
    }

    public function syncSchema(array $schema): void
    {
        $owned = collect($this->fieldDefinitions($schema))
            ->map(fn (array $field): string => 'sset_' . $field['name'])
            ->values()
            ->all();

        foreach ($owned as $name) {
            $setting = SystemSetting::whereSettingName($name)->firstOrCreate();
            $setting->setting_name = $name;
            $setting->save();
        }

        SystemSetting::where('setting_name', 'like', 'sset_%')
            ->whereNotIn('setting_name', $owned)
            ->delete();

        evo()->clearCache('full');
    }

    public function fieldDefinitions(array $schema): array
    {
        $fields = [];

        foreach ($schema as $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $field) {
                if (!is_array($field) || empty($field['name'])) {
                    continue;
                }

                if (!$this->fields->storesValue((string) ($field['type'] ?? 'text'))) {
                    continue;
                }

                $fields[] = $field;
            }
        }

        return $fields;
    }

    protected function defaultValue(array $field): string
    {
        $type = $this->fields->normalizeType((string) ($field['type'] ?? 'text'));

        return $type === 'checkbox' ? '0' : '';
    }

    protected function normalizeValue(array $field, mixed $value): string
    {
        return match ($this->fields->normalizeType((string) ($field['type'] ?? 'text'))) {
            'checkbox' => $value ? '1' : '0',
            'checkboxgroup', 'listboxmultiple' => $this->fields->serializeMultipleValue($value),
            default => is_scalar($value) ? trim((string) $value) : '',
        };
    }
}
