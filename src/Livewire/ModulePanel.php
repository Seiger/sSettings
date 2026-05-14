<?php namespace Seiger\sSettings\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Attributes\On;
use Seiger\sSettings\Support\SettingsSchemaRepository;

/**
 * @phpstan-type SchemaField array{name: string, label: string, description: string, type: string, options?: string}
 * @phpstan-type SchemaTab array{label: string, fields: list<SchemaField>}
 * @phpstan-type ModuleTab array{key: string, label: string, icon: string, type: string, permission?: string}
 */
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

    #[On('ssettings-schema-saved')]
    public function refreshSchemaTabs(): void
    {
        $schema = app(SettingsSchemaRepository::class);
        $this->activeTab = $this->normalizeActiveTab($this->activeTab, $schema->read());
    }

    public function render(SettingsSchemaRepository $schema): View|Factory
    {
        $schemaTabs = $schema->read();
        $this->activeTab = $this->normalizeActiveTab($this->activeTab, $schemaTabs);

        return view('sSettings::livewire.module-panel', [
            'tabs' => $this->tabs($schemaTabs),
            'title' => $this->activeTab === 'configure'
                ? $this->translation('global.edit_settings', 'Edit settings')
                : $this->translation('sSettings::global.title', 'Settings'),
        ]);
    }

    /**
     * @param array<string, SchemaTab> $schemaTabs
     * @return list<ModuleTab>
     */
    protected function tabs(array $schemaTabs): array
    {
        $tabs = [];

        foreach ($schemaTabs as $key => $tab) {
            $label = trim($this->translation($tab['label'] ?: 'sSettings::global.no_title', $tab['label'] ?: 'No title'));
            $tabs[] = [
                'key' => (string) $key,
                'label' => $label,
                'icon' => 'folder',
                'type' => 'settings',
            ];
        }

        $tabs[] = [
            'key' => 'configure',
            'label' => $this->translation('global.edit_settings', 'Edit settings'),
            'icon' => 'settings',
            'permission' => 'settings',
            'type' => 'configure',
        ];

        return $tabs;
    }

    /** @param array<string, SchemaTab> $schemaTabs */
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

    protected function translation(string $key, string $fallback): string
    {
        $value = __($key);

        return is_string($value) ? $value : $fallback;
    }
}
