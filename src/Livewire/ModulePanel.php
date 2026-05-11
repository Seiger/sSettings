<?php namespace Seiger\sSettings\Livewire;

use Livewire\Component;
use Seiger\sSettings\Support\SettingsSchemaRepository;

class ModulePanel extends Component
{
    public string $activeTab = 'settings';
    public bool $showUnsavedPrompt = false;
    public string $pendingTab = '';

    public function mount(SettingsSchemaRepository $schema, string $activeTab = 'settings'): void
    {
        $this->activeTab = $this->normalizeActiveTab($activeTab, $schema->read());
    }

    public function switchTab(SettingsSchemaRepository $schema, string $tab): void
    {
        $normalizedTab = $this->normalizeActiveTab($tab, $schema->read());

        if ($normalizedTab !== $tab && $tab !== 'settings') {
            return;
        }

        $this->activeTab = $normalizedTab;
        $this->pendingTab = '';
        $this->showUnsavedPrompt = false;
    }

    public function render(SettingsSchemaRepository $schema)
    {
        $schemaTabs = $schema->read();
        $this->activeTab = $this->normalizeActiveTab($this->activeTab, $schemaTabs);

        return view('sSettings::livewire.module-panel', [
            'tabs' => $this->tabs($schemaTabs),
            'title' => $this->activeTab === 'configure' ? __('global.edit_settings') : __('sSettings::global.title'),
        ]);
    }

    protected function tabs(array $schemaTabs): array
    {
        $tabs = [];

        foreach ($schemaTabs as $key => $tab) {
            $label = trim((string) __($tab['label'] ?: 'sSettings::global.no_title'));
            $tabs[] = [
                'key' => (string) $key,
                'label' => $label,
                'icon' => 'folder',
                'type' => 'settings',
            ];
        }

        $tabs[] = [
            'key' => 'configure',
            'label' => __('global.edit_settings'),
            'icon' => 'settings',
            'permission' => 'settings',
            'type' => 'configure',
        ];

        return $tabs;
    }

    protected function normalizeActiveTab(string $activeTab, array $schemaTabs): string
    {
        if ($activeTab === 'configure') {
            return 'configure';
        }

        if ($activeTab === 'settings' || $activeTab === '' || !isset($schemaTabs[$activeTab])) {
            return (string) (array_key_first($schemaTabs) ?: 'configure');
        }

        return $activeTab;
    }
}
