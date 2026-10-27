<?php namespace Seiger\sSettings\Support;

use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Read the project settings schema and replace it atomically after parsing the generated PHP.
 *
 * @phpstan-type SchemaField array{name: string, label: string, description: string, type: string, options?: string, multilingual?: bool}
 * @phpstan-type SchemaTab array{label: string, fields: list<SchemaField>}
 */
final class SettingsSchemaRepository
{
    public const FILE = 'custom/config/seiger/settings/sSettings.php';

    public function __construct(
        protected FieldCatalog $fields
    ) {
    }

    public function path(): string
    {
        $corePath = defined('EVO_CORE_PATH')
            ? (string) constant('EVO_CORE_PATH')
            : getcwd() . DIRECTORY_SEPARATOR;

        return rtrim($corePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::FILE;
    }

    public function exists(): bool
    {
        return is_file($this->path());
    }

    public function isWritable(): bool
    {
        $path = $this->path();

        return is_file($path) ? is_writable($path) : is_writable(dirname($path));
    }

    /** @return array<string, SchemaTab> */
    public function read(): array
    {
        $path = $this->path();

        if (!is_file($path)) {
            return $this->defaultSchema();
        }

        try {
            $schema = require $path;
        } catch (Throwable) {
            $schema = [];
        }

        return $this->normalize(is_array($schema) ? $schema : []);
    }

    /**
     * Normalize and parse the candidate in the active PHP process before writing a temporary file.
     *
     * PHP_BINARY can point to php-fpm in manager requests. Parsing in-process keeps
     * syntax validation independent of a CLI executable and disabled shell functions.
     * The existing config is replaced only after the complete candidate is written.
     *
     * @param array<mixed> $tabs
     * @return array<string, SchemaTab>
     * @throws RuntimeException When validation, writing or replacement fails.
     */
    public function save(array $tabs): array
    {
        if (!$this->isWritable()) {
            throw new RuntimeException($this->translation('sSettings::global.not_writable', 'Settings config is not writable.'));
        }

        $schema = $this->normalize($tabs);
        $path = $this->path();
        $dir = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create settings config directory.');
        }

        $payload = "<?php return " . var_export($schema, true) . ";\n";
        try {
            token_get_all($payload, TOKEN_PARSE);
        } catch (\ParseError $exception) {
            throw new RuntimeException('Generated settings config is invalid.', 0, $exception);
        }

        $tmp = $path . '.tmp';

        if (file_put_contents($tmp, $payload, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write temporary settings config.');
        }

        if (!rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Cannot replace settings config.');
        }

        return $schema;
    }

    /**
     * @param array<mixed> $tabs
     * @return array<string, SchemaTab>
     */
    public function normalize(array $tabs): array
    {
        $normalized = [];
        $usedTabKeys = [];
        $usedFieldNames = [];
        $index = 1;

        foreach ($tabs as $key => $tab) {
            $tab = is_array($tab) ? $tab : [];
            $tabKey = $this->uniqueKey(
                $this->normalizeKey((string) ($tab['key'] ?? $key), 'tab' . $index, true),
                $usedTabKeys
            );
            $label = trim((string) ($tab['label'] ?? ''));
            $fields = [];

            foreach (array_values((array) ($tab['fields'] ?? [])) as $fieldIndex => $field) {
                if (!is_array($field)) {
                    continue;
                }

                $fieldName = $this->uniqueKey(
                    $this->normalizeKey((string) ($field['name'] ?? ''), 'field' . ($fieldIndex + 1)),
                    $usedFieldNames
                );
                $type = $this->fields->normalizeType((string) ($field['type'] ?? 'text'));

                $normalizedField = [
                    'name' => $fieldName,
                    'label' => $this->normalizeLabel((string) ($field['label'] ?? '')),
                    'description' => $this->normalizeLabel((string) ($field['description'] ?? '')),
                    'type' => $type,
                ];

                if ($this->fields->supportsOptions($type)) {
                    $normalizedField['options'] = $this->fields->serializeOptions($field['options'] ?? '');
                }

                if ($this->fields->supportsMultilingual($type) && !empty($field['multilingual'])) {
                    $normalizedField['multilingual'] = true;
                }

                $fields[] = $normalizedField;
            }

            $normalized[$tabKey] = [
                'label' => $this->normalizeLabel($label),
                'fields' => $fields,
            ];

            $index++;
        }

        return $normalized !== [] ? $normalized : $this->defaultSchema();
    }

    /** @return array<string, SchemaTab> */
    public function defaultSchema(): array
    {
        $path = dirname(__DIR__, 2) . '/config/sSettingsSettings.php';
        $schema = is_file($path) ? require $path : [];

        return is_array($schema) && $schema !== [] ? $this->normalize($schema) : [
            'basicTab' => [
                'label' => 'sSettings::global.basicTab',
                'fields' => [],
            ],
        ];
    }

    protected function normalizeKey(string $key, string $fallback, bool $preserveCase = false): string
    {
        $key = trim($key);
        if ($key === '') {
            $key = $fallback;
        }

        if ($preserveCase && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $key)) {
            return $key;
        }

        $slug = Str::slug($key, '_', 'en');

        return $slug !== '' ? $slug : $fallback;
    }

    /** @param array<string, true> $used */
    protected function uniqueKey(string $key, array &$used): string
    {
        $base = $key !== '' ? $key : 'item';
        $candidate = $base;
        $suffix = 2;

        while (isset($used[$candidate])) {
            $candidate = $base . '_' . $suffix;
            $suffix++;
        }

        $used[$candidate] = true;

        return $candidate;
    }

    protected function normalizeLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (str_contains($value, '::')) {
            return $value;
        }

        $base = dirname(__DIR__, 2) . '/lang';
        $currentLocale = evo()->getLocale();
        $locales = array_values(array_unique([
            is_string($currentLocale) && $currentLocale !== '' ? $currentLocale : 'en',
            'en',
            'uk',
            'ru',
            'fr',
        ]));

        foreach ($locales as $locale) {
            $langPath = $base . '/' . $locale . '/global.php';
            if (!is_file($langPath)) {
                continue;
            }

            $translations = include $langPath;
            if (!is_array($translations)) {
                continue;
            }

            $key = array_search($value, $translations, true);
            if (is_string($key)) {
                return 'sSettings::global.' . $key;
            }
        }

        return $value;
    }

    protected function translation(string $key, string $fallback): string
    {
        $value = __($key);

        return is_string($value) ? $value : $fallback;
    }
}
