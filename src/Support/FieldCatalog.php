<?php namespace Seiger\sSettings\Support;

final class FieldCatalog
{
    /** @var array<string, string> */
    public const TYPES = [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'textareamini' => 'Textarea (Mini)',
        'richtext' => 'RichText',
        'dropdown' => 'DropDown List Menu',
        'listbox' => 'Listbox (Single-Select)',
        'listboxmultiple' => 'Listbox (Multi-Select)',
        'radio' => 'Radio Options',
        'image' => 'Image',
        'file' => 'File',
        'url' => 'URL',
        'email' => 'Email',
        'number' => 'Number',
        'date' => 'Date',
        'checkbox' => 'Check Box',
        'checkboxgroup' => 'Check Box Group',
        'divider' => 'Divider',
    ];

    /** @var list<string> */
    public const OPTION_TYPES = [
        'dropdown',
        'listbox',
        'listboxmultiple',
        'radio',
        'checkboxgroup',
    ];

    /** @var list<string> */
    public const MULTIPLE_VALUE_TYPES = [
        'listboxmultiple',
        'checkboxgroup',
    ];

    /** @return array<string, string> */
    public function all(): array
    {
        return self::TYPES;
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_keys(self::TYPES);
    }

    public function has(string $type): bool
    {
        return array_key_exists($this->normalizeType($type), self::TYPES);
    }

    public function label(string $type): string
    {
        return self::TYPES[$this->normalizeType($type)] ?? self::TYPES['text'];
    }

    public function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $type = str_replace(['_', '-', ' '], '', $type);

        $aliases = [
            'textareamini' => 'textareamini',
            'textarea(mini)' => 'textareamini',
            'richtext' => 'richtext',
            'richeditor' => 'richtext',
            'dropdown' => 'dropdown',
            'dropdownlist' => 'dropdown',
            'dropdownlistmenu' => 'dropdown',
            'select' => 'dropdown',
            'listbox' => 'listbox',
            'listboxsingle' => 'listbox',
            'listboxsingleselect' => 'listbox',
            'listboxmultiple' => 'listboxmultiple',
            'listboxmultiselect' => 'listboxmultiple',
            'multiselect' => 'listboxmultiple',
            'multiple' => 'listboxmultiple',
            'option' => 'radio',
            'radio' => 'radio',
            'radiooptions' => 'radio',
            'url' => 'url',
            'email' => 'email',
            'number' => 'number',
            'date' => 'date',
            'checkboxgroup' => 'checkboxgroup',
            'checkboxes' => 'checkboxgroup',
            'checkboxoptions' => 'checkboxgroup',
        ];

        $type = $aliases[$type] ?? $type;

        return array_key_exists($type, self::TYPES) ? $type : 'text';
    }

    public function storesValue(string $type): bool
    {
        return $this->normalizeType($type) !== 'divider';
    }

    /** @return list<string> */
    public function optionTypes(): array
    {
        return self::OPTION_TYPES;
    }

    public function supportsOptions(string $type): bool
    {
        return in_array($this->normalizeType($type), self::OPTION_TYPES, true);
    }

    public function storesMultipleValues(string $type): bool
    {
        return in_array($this->normalizeType($type), self::MULTIPLE_VALUE_TYPES, true);
    }

    /** @return list<array{value: string, label: string}> */
    public function parseOptions(mixed $options): array
    {
        if (is_array($options)) {
            $rows = $options;
        } else {
            $rows = explode('||', trim((string) $options));
        }

        $parsed = [];

        foreach ($rows as $row) {
            if (is_array($row)) {
                $value = trim((string) ($row['value'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
            } else {
                $parts = explode('==', (string) $row, 2);
                $value = trim((string) ($parts[0] ?? ''));
                $label = trim((string) ($parts[1] ?? ''));
            }

            if ($value === '' && $label === '') {
                continue;
            }

            if ($value === '') {
                $value = $label;
            }

            if ($label === '') {
                $label = $value;
            }

            $parsed[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $parsed;
    }

    public function serializeOptions(mixed $options): string
    {
        return collect($this->parseOptions($options))
            ->map(fn (array $option): string => $this->cleanOptionPart($option['value']) . '==' . $this->cleanOptionPart($option['label']))
            ->filter()
            ->implode('||');
    }

    /** @return list<string> */
    public function parseMultipleValue(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(
                fn (mixed $item): string => trim((string) $item),
                $value
            ), fn (string $item): bool => $item !== ''));
        }

        $value = trim((string) $value);

        return $value === '' ? [] : array_values(array_filter(array_map(
            fn (string $item): string => trim($item),
            explode('||', $value)
        ), fn (string $item): bool => $item !== ''));
    }

    public function serializeMultipleValue(mixed $value): string
    {
        return implode('||', $this->parseMultipleValue($value));
    }

    protected function cleanOptionPart(string $value): string
    {
        return trim(str_replace(['||', '=='], ['', '='], $value));
    }
}
