<?php namespace Seiger\sSettings\Livewire;

use Livewire\Component;
use Seiger\sSettings\Support\FieldCatalog;
use Seiger\sSettings\Support\SettingsSchemaRepository;
use Seiger\sSettings\Support\SystemSettingsStore;

class SettingsPanel extends Component
{
    public array $schema = [];
    public array $data = [];
    public array $cleanData = [];
    public string $activeTab = '';
    public bool $dirty = false;
    public bool $saved = false;

    public function mount(SettingsSchemaRepository $schema, SystemSettingsStore $store, string $activeTab = ''): void
    {
        $this->activeTab = $activeTab;
        $this->fillData($schema, $store);
    }

    public function updatedData(): void
    {
        $this->saved = false;
        $this->dirty = $this->snapshot($this->data) !== $this->snapshot($this->cleanData);
    }

    public function save(SettingsSchemaRepository $schema, SystemSettingsStore $store): void
    {
        $this->saved = false;
        $store->saveValues($schema->read(), $this->data);
        $this->fillData($schema, $store);
        $this->saved = true;
        $this->dirty = false;
        $this->dispatch('evo-ui:form.saved', preset: 'ssettings.values');
    }

    public function setActiveTab(string $tab): void
    {
        if (isset($this->schema[$tab])) {
            $this->activeTab = $tab;
        }
    }

    public function render(FieldCatalog $catalog)
    {
        return view('sSettings::livewire.settings-panel', [
            'catalog' => $catalog,
            'tabs' => $this->schema,
        ]);
    }

    protected function fillData(SettingsSchemaRepository $schema, SystemSettingsStore $store): void
    {
        $this->schema = $schema->read();
        $this->data = $store->values($this->schema);
        $this->cleanData = $this->data;
        if ($this->activeTab === '' || !isset($this->schema[$this->activeTab])) {
            $this->activeTab = (string) array_key_first($this->schema);
        }
        $this->dirty = false;
    }

    protected function snapshot(array $data): string
    {
        ksort($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
}
