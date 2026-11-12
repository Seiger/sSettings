<?php namespace Seiger\sSettings\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Seiger\sSettings\Support\FieldCatalog;
use Seiger\sSettings\Support\ManagerSettingsAccess;
use Seiger\sSettings\Support\SettingsSchemaRepository;
use Seiger\sSettings\Support\SystemSettingsStore;

/**
 * Loads and edits the settings schema for authorized managers.
 *
 * @phpstan-type OptionRow array{value: string, label: string}
 * @phpstan-type FieldRow array<string, mixed>
 * @phpstan-type TabRow array<string, mixed>
 * @phpstan-type SchemaField array{name: string, label: string, description: string, type: string, options?: string, multilingual?: bool}
 * @phpstan-type SchemaTab array{label: string, fields: list<SchemaField>}
 */
class ConfigurePanel extends Component
{
    /** @var array<int, TabRow> */
    #[Locked]
    public array $tabs = [];
    /** @var array<int, TabRow> */
    public array $cleanTabs = [];
    #[Locked]
    public ?string $schemaRevision = null;
    /** @var array<string, bool> Removed names map to true and renamed names to false. */
    #[Locked]
    public array $retiredFields = [];
    public bool $dirty = false;
    public bool $saved = false;
    public string $error = '';
    public string $openFieldUid = '';

    /**
     * Check the manager's current settings grant before mounting or processing an update.
     *
     * @since 2.1.0
     */
    public function boot(): void
    {
        ManagerSettingsAccess::authorize();
    }

    public function mount(SettingsSchemaRepository $schema): void
    {
        $this->fillData($schema);
    }

    public function updatedTabs(): void
    {
        $this->saved = false;
        $this->error = '';
        $this->dirty = $this->snapshot($this->tabs) !== $this->snapshot($this->cleanTabs);

        if ($this->dirty) {
            $this->dispatch('ssettings-dirty');
        }
    }

    public function addTab(): void
    {
        $this->addTabAfter(count($this->tabs) - 1);
    }

    public function addTabAfter(int $index): void
    {
        $tab = [
            '_uid' => $this->uid('tab'),
            'key' => $this->uniqueTabKey(),
            'label' => '',
            'fields' => [],
        ];

        array_splice($this->tabs, max(0, $index + 1), 0, [$tab]);
        $this->updatedTabs();
        $this->dispatch('evo-ui:inline-create.created', root: 'ssettings-configure', id: $tab['_uid'], uid: $tab['_uid']);
    }

    /** Record the fields explicitly removed with this tab before changing editor state. */
    public function removeTab(int $index): void
    {
        if (count($this->tabs) < 2 || !isset($this->tabs[$index])) {
            return;
        }

        foreach ($this->tabs[$index]['fields'] as $field) {
            $this->retiredFields[$field['name']] = true;
        }
        unset($this->tabs[$index]);
        $this->tabs = array_values($this->tabs);
        $this->updatedTabs();
    }

    public function moveTab(int $index, int $direction): void
    {
        $this->tabs = $this->move($this->tabs, $index, $direction);
        $this->updatedTabs();
    }

    public function reorderTabs(int $from, int $to): void
    {
        $this->tabs = $this->moveTo($this->tabs, $from, $to);
        $this->updatedTabs();
    }

    public function addField(int $tabIndex): void
    {
        $this->addFieldAfter($tabIndex, count((array) ($this->tabs[$tabIndex]['fields'] ?? [])) - 1);
    }

    public function addFieldAfter(int $tabIndex, int $fieldIndex): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['fields'] = array_values((array) ($this->tabs[$tabIndex]['fields'] ?? []));

        $field = [
            '_uid' => $this->uid('field'),
            'name' => $this->uniqueFieldName(),
            'label' => '',
            'description' => '',
            'type' => 'text',
        ];

        $this->openFieldUid = $field['_uid'];
        array_splice($this->tabs[$tabIndex]['fields'], max(0, $fieldIndex + 1), 0, [$field]);
        $this->updatedTabs();
        $this->dispatch('evo-ui:inline-create.created', root: 'ssettings-configure', id: $field['_uid'], uid: $field['_uid']);
    }

    public function commitFieldEdit(): void
    {
        $this->openFieldUid = '';
        $this->updatedTabs();
    }

    public function clearOpenField(): void
    {
        $this->openFieldUid = '';
    }

    public function updateTabLabelByUid(string $tabUid, string $label): void
    {
        foreach ($this->tabs as $tabIndex => $tab) {
            if (($tab['_uid'] ?? '') !== $tabUid) {
                continue;
            }

            $this->tabs[$tabIndex]['label'] = $label;
            $this->updatedTabs();
            return;
        }
    }

    /**
     * Update a field and preserve its previous values when the manager renames it.
     *
     * @param array<mixed> $options
     */
    public function updateFieldByUid(string $fieldUid, string $name, string $label, string $description, string $type, array $options = [], ?bool $multilingual = null): void
    {
        $catalog = app(FieldCatalog::class);
        $type = $catalog->normalizeType($type);

        foreach ($this->tabs as $tabIndex => $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $fieldIndex => $field) {
                if (($field['_uid'] ?? '') !== $fieldUid) {
                    continue;
                }

                if ($field['name'] !== $name) {
                    $this->retiredFields[$field['name']] = false;
                }
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['name'] = $name;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['label'] = $label;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['description'] = $description;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['type'] = $type;
                if (!$catalog->supportsMultilingual($type)) {
                    unset($this->tabs[$tabIndex]['fields'][$fieldIndex]['multilingual']);
                } elseif ($multilingual !== null && app(\Seiger\sSettings\Support\SettingsLanguages::class)->available()) {
                    $this->tabs[$tabIndex]['fields'][$fieldIndex]['multilingual'] = $multilingual;
                }
                if ($catalog->supportsOptions($type)) {
                    $this->tabs[$tabIndex]['fields'][$fieldIndex]['options'] = $catalog->serializeOptions($options);
                } else {
                    unset($this->tabs[$tabIndex]['fields'][$fieldIndex]['options']);
                }
                $this->openFieldUid = '';
                $this->updatedTabs();
                return;
            }
        }
    }

    /** Record an explicit field removal for the next schema save. */
    public function removeField(int $tabIndex, int $fieldIndex): void
    {
        if (!isset($this->tabs[$tabIndex]['fields'][$fieldIndex])) {
            return;
        }

        $this->retiredFields[$this->tabs[$tabIndex]['fields'][$fieldIndex]['name']] = true;
        unset($this->tabs[$tabIndex]['fields'][$fieldIndex]);
        $this->tabs[$tabIndex]['fields'] = array_values($this->tabs[$tabIndex]['fields']);
        $this->updatedTabs();
    }

    public function moveField(int $tabIndex, int $fieldIndex, int $direction): void
    {
        if (!isset($this->tabs[$tabIndex]['fields'])) {
            return;
        }

        $this->tabs[$tabIndex]['fields'] = $this->move($this->tabs[$tabIndex]['fields'], $fieldIndex, $direction);
        $this->updatedTabs();
    }

    public function moveFieldStep(int $tabIndex, int $fieldIndex, int $direction): void
    {
        if (!isset($this->tabs[$tabIndex]['fields'][$fieldIndex]) || $direction === 0) {
            return;
        }

        $this->tabs[$tabIndex]['fields'] = array_values((array) $this->tabs[$tabIndex]['fields']);

        if ($direction < 0) {
            if ($fieldIndex > 0) {
                $this->tabs[$tabIndex]['fields'] = $this->move($this->tabs[$tabIndex]['fields'], $fieldIndex, -1);
                $this->updatedTabs();
                return;
            }

            $previousTab = $tabIndex - 1;
            if (!isset($this->tabs[$previousTab])) {
                return;
            }

            $field = $this->pullField($tabIndex, $fieldIndex);
            $this->tabs[$previousTab]['fields'] = array_values((array) ($this->tabs[$previousTab]['fields'] ?? []));
            $this->tabs[$previousTab]['fields'][] = $field;
            $this->updatedTabs();
            return;
        }

        $lastIndex = count($this->tabs[$tabIndex]['fields']) - 1;
        if ($fieldIndex < $lastIndex) {
            $this->tabs[$tabIndex]['fields'] = $this->move($this->tabs[$tabIndex]['fields'], $fieldIndex, 1);
            $this->updatedTabs();
            return;
        }

        $nextTab = $tabIndex + 1;
        if (!isset($this->tabs[$nextTab])) {
            return;
        }

        $field = $this->pullField($tabIndex, $fieldIndex);
        $this->tabs[$nextTab]['fields'] = array_values((array) ($this->tabs[$nextTab]['fields'] ?? []));
        array_unshift($this->tabs[$nextTab]['fields'], $field);
        $this->updatedTabs();
    }

    public function reorderFields(int $tabIndex, int $from, int $to): void
    {
        if (!isset($this->tabs[$tabIndex]['fields'])) {
            return;
        }

        $this->tabs[$tabIndex]['fields'] = $this->moveTo($this->tabs[$tabIndex]['fields'], $from, $to);
        $this->updatedTabs();
    }

    /** @param list<string> $uids */
    public function sortTabs(array $uids): void
    {
        $this->tabs = $this->sortByUid($this->tabs, $uids);
        $this->updatedTabs();
    }

    public function sortTabByUid(string $tabUid, int $position): void
    {
        $from = $this->tabIndexByUid($tabUid);

        if ($from === null) {
            return;
        }

        $tab = $this->tabs[$from];
        unset($this->tabs[$from]);
        $this->tabs = array_values($this->tabs);

        array_splice($this->tabs, max(0, min($position, count($this->tabs))), 0, [$tab]);
        $this->updatedTabs();
    }

    /** @param list<string> $uids */
    public function sortFields(string $tabUid, array $uids): void
    {
        foreach ($this->tabs as $tabIndex => $tab) {
            if (($tab['_uid'] ?? '') !== $tabUid) {
                continue;
            }

            $this->tabs[$tabIndex]['fields'] = $this->sortByUid((array) ($tab['fields'] ?? []), $uids);
            $this->updatedTabs();
            return;
        }
    }

    /** @param array<string, list<string>> $tabFields */
    public function sortAllFields(array $tabFields): void
    {
        $fieldsByUid = [];
        $originalTabByUid = [];

        foreach ($this->tabs as $tab) {
            $tabUid = (string) ($tab['_uid'] ?? '');

            foreach ((array) ($tab['fields'] ?? []) as $field) {
                $fieldUid = (string) ($field['_uid'] ?? '');

                if ($fieldUid === '') {
                    continue;
                }

                $fieldsByUid[$fieldUid] = $field;
                $originalTabByUid[$fieldUid] = $tabUid;
            }
        }

        foreach ($this->tabs as $tabIndex => $tab) {
            $tabUid = (string) ($tab['_uid'] ?? '');
            $uids = (array) ($tabFields[$tabUid] ?? []);
            $this->tabs[$tabIndex]['fields'] = [];

            foreach ($uids as $uid) {
                $uid = (string) $uid;

                if (!isset($fieldsByUid[$uid])) {
                    continue;
                }

                $this->tabs[$tabIndex]['fields'][] = $fieldsByUid[$uid];
                unset($fieldsByUid[$uid]);
            }
        }

        foreach ($this->tabs as $tabIndex => $tab) {
            $tabUid = (string) ($tab['_uid'] ?? '');

            foreach ($fieldsByUid as $uid => $field) {
                if (($originalTabByUid[$uid] ?? '') !== $tabUid) {
                    continue;
                }

                $this->tabs[$tabIndex]['fields'][] = $field;
                unset($fieldsByUid[$uid]);
            }
        }

        if ($fieldsByUid !== [] && isset($this->tabs[0])) {
            $this->tabs[0]['fields'] = array_values([
                ...(array) ($this->tabs[0]['fields'] ?? []),
                ...array_values($fieldsByUid),
            ]);
        }

        $this->updatedTabs();
    }

    public function sortFieldByUid(string $fieldUid, int $position, string $tabUid): void
    {
        $targetTabIndex = $this->tabIndexByUid($tabUid);

        if ($targetTabIndex === null) {
            return;
        }

        $field = $this->pullFieldByUid($fieldUid);

        if ($field === null) {
            return;
        }

        $this->tabs[$targetTabIndex]['fields'] = array_values((array) ($this->tabs[$targetTabIndex]['fields'] ?? []));
        array_splice(
            $this->tabs[$targetTabIndex]['fields'],
            max(0, min($position, count($this->tabs[$targetTabIndex]['fields']))),
            0,
            [$field]
        );
        $this->updatedTabs();
    }

    /** Save the editor schema with the recorded removals after checking the current settings grant. */
    public function save(SettingsSchemaRepository $schema, SystemSettingsStore $store): bool
    {
        if (!ManagerSettingsAccess::allows()) {
            $message = __('global.access_permission_denied');
            $this->error = is_string($message) ? $message : 'Access permission denied.';
            return false;
        }

        $this->saved = false;
        $this->error = '';

        try {
            $normalized = $store->saveSchema($schema, $this->toSchema(), $this->schemaRevision, $this->retiredFields);
            $this->schemaRevision = hash('sha256', serialize($normalized));
            $this->retiredFields = [];
            $this->openFieldUid = '';
            $this->fillDataFromSchema($normalized);
            $this->saved = true;
            $this->dirty = false;
            $this->dispatch('ssettings-schema-saved');
            $this->dispatch('evo-ui:form.saved', preset: 'ssettings.configure');
            return true;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    public function render(FieldCatalog $catalog): View|Factory
    {
        return view('sSettings::livewire.configure-panel', [
            'types' => $catalog->all(),
            'optionTypes' => $catalog->optionTypes(),
            'catalog' => $catalog,
            'multilingualAvailable' => app(\Seiger\sSettings\Support\SettingsLanguages::class)->available(),
            'writable' => app(SettingsSchemaRepository::class)->isWritable(),
        ]);
    }

    /** Load the editor from the same snapshot used to identify its persisted baseline. */
    protected function fillData(SettingsSchemaRepository $schema): void
    {
        $snapshot = $schema->readSnapshot();
        $this->schemaRevision = $snapshot['revision'];
        $this->retiredFields = [];
        $this->fillDataFromSchema($snapshot['schema']);
    }

    /**
     * Restore editor rows with their translated text and the source needed for saving.
     *
     * @param array<string, SchemaTab> $schema
     */
    protected function fillDataFromSchema(array $schema): void
    {
        $previousTabs = array_values($this->tabs);
        $previousTabsByKey = collect($previousTabs)
            ->filter(fn (array $tab): bool => (string) ($tab['key'] ?? '') !== '')
            ->keyBy(fn (array $tab): string => (string) ($tab['key'] ?? ''));

        $tabs = [];
        $tabPosition = 0;

        foreach ($schema as $key => $tab) {
            $previousTab = $previousTabsByKey->get((string) $key, $previousTabs[$tabPosition] ?? []);
            $previousFields = array_values((array) ($previousTab['fields'] ?? []));
            $previousFieldsByName = collect($previousFields)
                ->filter(fn (array $field): bool => (string) ($field['name'] ?? '') !== '')
                ->keyBy(fn (array $field): string => (string) ($field['name'] ?? ''));

            $fields = [];
            $fieldPosition = 0;

            foreach ((array) ($tab['fields'] ?? []) as $field) {
                $previousField = $previousFieldsByName->get((string) ($field['name'] ?? ''), $previousFields[$fieldPosition] ?? []);
                $fields[] = $this->withDisplayText([
                    ...$field,
                    '_uid' => (string) ($previousField['_uid'] ?? $this->uid('field')),
                ], 'label', 'description');
                $fieldPosition++;
            }

            $tabs[] = $this->withDisplayText([
                '_uid' => (string) ($previousTab['_uid'] ?? $this->uid('tab')),
                'key' => (string) $key,
                'label' => $tab['label'] ?? '',
                'fields' => $fields,
            ], 'label');
            $tabPosition++;
        }

        $this->tabs = $tabs;
        $this->cleanTabs = $this->tabs;
        $this->dirty = false;
    }

    /**
     * Serialize edits while restoring the source of unchanged translated text.
     *
     * @return list<array{key: string, label: string, fields: list<SchemaField>}>
     */
    protected function toSchema(): array
    {
        $schema = [];

        foreach ($this->tabs as $tab) {
            $fields = [];

            foreach ($tab['fields'] as $field) {
                $schemaField = [
                    'name' => (string) ($field['name'] ?? ''),
                    'label' => $this->sourceText($field, 'label'),
                    'description' => $this->sourceText($field, 'description'),
                    'type' => (string) ($field['type'] ?? 'text'),
                ];

                if (isset($field['options'])) {
                    $schemaField['options'] = (string) $field['options'];
                }
                if (!empty($field['multilingual'])) {
                    $schemaField['multilingual'] = true;
                }

                $fields[] = $schemaField;
            }

            $schema[] = [
                'key' => $tab['key'],
                'label' => $this->sourceText($tab, 'label'),
                'fields' => $fields,
            ];
        }

        return $schema;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function move(array $items, int $index, int $direction): array
    {
        $target = $index + $direction;
        if (!isset($items[$index]) || $target < 0 || $target >= count($items)) {
            return $items;
        }

        $tmp = $items[$target];
        $items[$target] = $items[$index];
        $items[$index] = $tmp;

        return array_values($items);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function moveTo(array $items, int $from, int $to): array
    {
        if (!isset($items[$from]) || !isset($items[$to]) || $from === $to) {
            return array_values($items);
        }

        $item = $items[$from];
        array_splice($items, $from, 1);
        array_splice($items, $to, 0, [$item]);

        return array_values($items);
    }

    /** @return FieldRow */
    protected function pullField(int $tabIndex, int $fieldIndex): array
    {
        $field = $this->tabs[$tabIndex]['fields'][$fieldIndex];
        unset($this->tabs[$tabIndex]['fields'][$fieldIndex]);
        $this->tabs[$tabIndex]['fields'] = array_values($this->tabs[$tabIndex]['fields']);

        return $field;
    }

    protected function tabIndexByUid(string $uid): ?int
    {
        foreach ($this->tabs as $index => $tab) {
            if (($tab['_uid'] ?? '') === $uid) {
                return $index;
            }
        }

        return null;
    }

    /** @return FieldRow|null */
    protected function pullFieldByUid(string $fieldUid): ?array
    {
        foreach ($this->tabs as $tabIndex => $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $fieldIndex => $field) {
                if (($field['_uid'] ?? '') !== $fieldUid) {
                    continue;
                }

                return $this->pullField($tabIndex, $fieldIndex);
            }
        }

        return null;
    }

    /** @param array<mixed> $data */
    protected function snapshot(array $data): string
    {
        return json_encode($this->snapshotData($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    protected function snapshotData(array $data): array
    {
        $snapshot = [];

        foreach ($data as $key => $value) {
            if ($key === '_uid') {
                continue;
            }

            $snapshot[$key] = is_array($value) ? $this->snapshotData($value) : $value;
        }

        return $snapshot;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param list<string> $uids
     * @return array<int, array<string, mixed>>
     */
    protected function sortByUid(array $items, array $uids): array
    {
        $items = array_values($items);
        $byUid = [];
        $sorted = [];
        $used = [];

        foreach ($items as $item) {
            $uid = (string) ($item['_uid'] ?? '');

            if ($uid !== '') {
                $byUid[$uid] = $item;
            }
        }

        foreach ($uids as $uid) {
            $uid = (string) $uid;
            if ($uid === '' || !isset($byUid[$uid])) {
                continue;
            }

            $sorted[] = $byUid[$uid];
            $used[$uid] = true;
        }

        foreach ($items as $item) {
            $uid = (string) ($item['_uid'] ?? '');

            if ($uid !== '' && isset($used[$uid])) {
                continue;
            }

            $sorted[] = $item;
        }

        return $sorted;
    }

    protected function uid(string $prefix): string
    {
        return $prefix . '-' . str_replace('.', '', uniqid('', true));
    }

    protected function uniqueTabKey(): string
    {
        $used = collect($this->tabs)
            ->map(fn (array $tab): string => (string) ($tab['key'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return $this->nextUnique('new_tab', array_values($used));
    }

    protected function uniqueFieldName(): string
    {
        $used = collect($this->tabs)
            ->flatMap(fn (array $tab): array => (array) ($tab['fields'] ?? []))
            ->map(fn (array $field): string => (string) ($field['name'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return $this->nextUnique('new_field', array_values($used));
    }

    /** @param list<string> $used */
    protected function nextUnique(string $base, array $used): string
    {
        $used = array_flip(array_map('strval', $used));
        $index = 1;

        do {
            $candidate = $base . '_' . $index;
            $index++;
        } while (isset($used[$candidate]));

        return $candidate;
    }

    /** Resolve a schema string for display in the editor. */
    protected function displayText(string $value): string
    {
        return app(FieldCatalog::class)->displayText($value);
    }

    /**
     * Keep the original text and its displayed form together in the locked editor row.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     * @since 2.1.0
     */
    private function withDisplayText(array $row, string ...$keys): array
    {
        foreach ($keys as $key) {
            $source = (string) ($row[$key] ?? '');
            $display = trim($this->displayText($source));
            $row[$key] = $display;

            if ($display !== $source) {
                $row['_sourceText'][$key] = [$source, $display];
            }
        }

        return $row;
    }

    /**
     * Keep the original key when the editor still contains the text it displayed.
     *
     * @param array<string, mixed> $row
     * @since 2.1.0
     */
    private function sourceText(array $row, string $key): string
    {
        $value = (string) ($row[$key] ?? '');
        [$source, $display] = $row['_sourceText'][$key] ?? [$value, $value];

        return trim($value) === $display ? $source : $value;
    }
}
