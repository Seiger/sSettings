<?php namespace Seiger\sSettings\Livewire;

use Livewire\Component;
use Seiger\sSettings\Support\FieldCatalog;
use Seiger\sSettings\Support\SettingsSchemaRepository;
use Seiger\sSettings\Support\SystemSettingsStore;

class ConfigurePanel extends Component
{
    public array $tabs = [];
    public array $cleanTabs = [];
    public bool $dirty = false;
    public bool $saved = false;
    public string $error = '';
    public string $openFieldUid = '';

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
    }

    public function removeTab(int $index): void
    {
        if (count($this->tabs) < 2 || !isset($this->tabs[$index])) {
            return;
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

    public function updateFieldByUid(string $fieldUid, string $name, string $label, string $description, string $type, array $options = []): void
    {
        $catalog = app(FieldCatalog::class);
        $type = $catalog->normalizeType($type);

        foreach ($this->tabs as $tabIndex => $tab) {
            foreach ((array) ($tab['fields'] ?? []) as $fieldIndex => $field) {
                if (($field['_uid'] ?? '') !== $fieldUid) {
                    continue;
                }

                $this->tabs[$tabIndex]['fields'][$fieldIndex]['name'] = $name;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['label'] = $label;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['description'] = $description;
                $this->tabs[$tabIndex]['fields'][$fieldIndex]['type'] = $type;
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

    public function removeField(int $tabIndex, int $fieldIndex): void
    {
        if (!isset($this->tabs[$tabIndex]['fields'][$fieldIndex])) {
            return;
        }

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

    public function save(SettingsSchemaRepository $schema, SystemSettingsStore $store): bool
    {
        if (!evo()->hasPermission('settings', 'mgr')) {
            $this->error = __('global.access_permission_denied');
            return false;
        }

        $this->saved = false;
        $this->error = '';

        try {
            $normalized = $schema->save($this->toSchema());
            $store->syncSchema($normalized);
            $this->openFieldUid = '';
            $this->fillDataFromSchema($normalized);
            $this->saved = true;
            $this->dirty = false;
            $this->dispatch('evo-ui:form.saved', preset: 'ssettings.configure');
            return true;
        } catch (\Throwable $exception) {
            $this->error = $exception->getMessage();
            return false;
        }
    }

    public function render(FieldCatalog $catalog)
    {
        return view('sSettings::livewire.configure-panel', [
            'types' => $catalog->all(),
            'optionTypes' => $catalog->optionTypes(),
            'catalog' => $catalog,
            'writable' => app(SettingsSchemaRepository::class)->isWritable(),
        ]);
    }

    protected function fillData(SettingsSchemaRepository $schema): void
    {
        $this->fillDataFromSchema($schema->read());
    }

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
                $fields[] = [
                    ...$field,
                    '_uid' => (string) ($previousField['_uid'] ?? $this->uid('field')),
                    'label' => $this->displayText((string) ($field['label'] ?? '')),
                    'description' => $this->displayText((string) ($field['description'] ?? '')),
                ];
                $fieldPosition++;
            }

            $tabs[] = [
                '_uid' => (string) ($previousTab['_uid'] ?? $this->uid('tab')),
                'key' => (string) $key,
                'label' => $this->displayText((string) ($tab['label'] ?? '')),
                'fields' => $fields,
            ];
            $tabPosition++;
        }

        $this->tabs = $tabs;
        $this->cleanTabs = $this->tabs;
        $this->dirty = false;
    }

    protected function toSchema(): array
    {
        $schema = [];

        foreach ($this->tabs as $tab) {
            $schema[] = [
                'key' => $tab['key'] ?? '',
                'label' => $tab['label'] ?? '',
                'fields' => collect((array) ($tab['fields'] ?? []))
                    ->map(fn (array $field): array => collect($field)->except('_uid')->all())
                    ->values()
                    ->all(),
            ];
        }

        return $schema;
    }

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

    protected function snapshot(array $data): string
    {
        return json_encode($this->snapshotData($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

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

    protected function sortByUid(array $items, array $uids): array
    {
        $items = array_values($items);
        $byUid = collect($items)->keyBy(fn (array $item): string => (string) ($item['_uid'] ?? ''));
        $sorted = [];

        foreach ($uids as $uid) {
            $uid = (string) $uid;
            if ($uid === '' || !$byUid->has($uid)) {
                continue;
            }

            $sorted[] = $byUid->get($uid);
            $byUid->forget($uid);
        }

        return array_values([...$sorted, ...$byUid->values()->all()]);
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

        return $this->nextUnique('new_tab', $used);
    }

    protected function uniqueFieldName(): string
    {
        $used = collect($this->tabs)
            ->flatMap(fn (array $tab): array => (array) ($tab['fields'] ?? []))
            ->map(fn (array $field): string => (string) ($field['name'] ?? ''))
            ->filter()
            ->values()
            ->all();

        return $this->nextUnique('new_field', $used);
    }

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

    protected function displayText(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return (string) __($value);
    }
}
