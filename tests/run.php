<?php

declare(strict_types=1);

$demoCore = getenv('SSETTINGS_DEMO_CORE') ?: '/Users/dmi3yy/PhpstormProjects/Extras/sArticles/demo/core';

if (!is_file($demoCore . '/bootstrap.php')) {
    fwrite(STDERR, "Demo core bootstrap not found: {$demoCore}\n");
    exit(1);
}

defined('EVO_API_MODE') || define('EVO_API_MODE', true);
defined('IN_MANAGER_MODE') || define('IN_MANAGER_MODE', true);
defined('IN_INSTALL_MODE') || define('IN_INSTALL_MODE', false);

require $demoCore . '/bootstrap.php';

$catalog = new Seiger\sSettings\Support\FieldCatalog();
assertSame(['text', 'textarea', 'textareamini', 'richtext', 'dropdown', 'listbox', 'listboxmultiple', 'radio', 'image', 'file', 'url', 'email', 'number', 'date', 'checkbox', 'checkboxgroup', 'divider'], $catalog->keys(), 'field catalogue keys');
assertSame('textareamini', $catalog->normalizeType('Textarea Mini'), 'textarea mini normalization');
assertSame('listboxmultiple', $catalog->normalizeType('listbox-multiple'), 'Evolution multi listbox normalization');
assertSame('radio', $catalog->normalizeType('Radio Options'), 'Evolution radio normalization');
assertSame(true, $catalog->supportsOptions('dropdown'), 'dropdown supports value/label options');
assertSame('one==One||two==Two', $catalog->serializeOptions([['value' => 'one', 'label' => 'One'], ['value' => 'two', 'label' => 'Two']]), 'options serialize to Evolution-style value label pairs');
assertSame([['value' => 'one', 'label' => 'One'], ['value' => 'two', 'label' => 'Two']], $catalog->parseOptions('one==One||two==Two'), 'options parse from Evolution-style value label pairs');
assertSame('one||two', $catalog->serializeMultipleValue(['one', '', 'two']), 'multiple values serialize compactly');
assertSame(false, $catalog->storesValue('divider'), 'divider value ownership');

$composer = json_decode((string) file_get_contents(dirname(__DIR__) . '/composer.json'), true);
assertSame('GPL-3.0-or-later', $composer['license'] ?? null, 'composer license uses explicit SPDX expression');
assertSame('^1.0.1', $composer['require']['evolution-cms/evo-ui'] ?? null, 'composer evo-ui dependency uses release constraint');
assertSame('php tests/run.php', $composer['scripts']['test'] ?? null, 'composer test script runs the smoke suite');

$readme = (string) file_get_contents(dirname(__DIR__) . '/README.md');
assertContains('composer validate --strict --no-check-publish', $readme, 'README documents composer validate release gate');
assertContains('composer test', $readme, 'README documents composer test release gate');
assertContains('php tests/run.php', $readme, 'README documents direct smoke runner');

$schema = new Seiger\sSettings\Support\SettingsSchemaRepository($catalog);
$normalized = $schema->normalize([
    'Contact Tab' => [
        'label' => 'Basic Information',
        'fields' => [
            [
                'name' => 'Phone Number',
                'label' => 'Phone',
                'description' => 'Enter the site phone number.',
                'type' => 'Textarea Mini',
            ],
            [
                'name' => 'Section Break',
                'label' => 'Social networks',
                'type' => 'divider',
            ],
            [
                'name' => 'Region',
                'label' => 'Region',
                'description' => 'Choose a region',
                'type' => 'DropDown List Menu',
                'options' => [
                    ['value' => 'ua', 'label' => 'Ukraine'],
                    ['value' => 'eu', 'label' => 'Europe'],
                ],
            ],
        ],
    ],
]);

assertSame('contact_tab', array_key_first($normalized), 'tab key normalization');
assertSame('sSettings::global.basicTab', $normalized['contact_tab']['label'], 'tab label reverse translation');
assertSame('phone_number', $normalized['contact_tab']['fields'][0]['name'], 'field key normalization');
assertSame('sSettings::global.phone', $normalized['contact_tab']['fields'][0]['label'], 'field label reverse translation');
assertSame('textareamini', $normalized['contact_tab']['fields'][0]['type'], 'field type normalization');
assertSame('divider', $normalized['contact_tab']['fields'][1]['type'], 'divider type normalization');
assertSame('dropdown', $normalized['contact_tab']['fields'][2]['type'], 'dropdown type normalization');
assertSame('ua==Ukraine||eu==Europe', $normalized['contact_tab']['fields'][2]['options'] ?? '', 'option fields preserve serialized options');

$plugin = (string) file_get_contents(dirname(__DIR__) . '/plugins/sSettingsPlugin.php');
assertContains('OnManagerMenuPrerender', $plugin, 'tools menu hook remains');
assertContains('<span class="menu-module-icon" aria-hidden="true">', $plugin, 'manager menu uses Frame module icon wrapper');
assertContains('svg($icon)->toHtml()', $plugin, 'manager menu uses Blade icon render');
assertContains("'index.php?a=112&id=' . md5(\$title)", $plugin, 'manager menu opens registered module action');
assertNotContains('ssettings-manager-menu-icon', $plugin, 'custom manager menu icon wrapper removed');

$serviceProvider = (string) file_get_contents(dirname(__DIR__) . '/src/sSettingsServiceProvider.php');
assertContains('$this->app->registerModule(', $serviceProvider, 'manager module registration');
assertContains("'/module/sSettingsModule.php'", $serviceProvider, 'manager module file registration');
assertContains("\$labels['module_icon'] ?? \$labels['icon']", $serviceProvider, 'manager module icon registration');
assertContains("'hidden' => true", $serviceProvider, 'registered module hidden from modules menu');
assertNotContains('assets/ssettings.css', $serviceProvider, 'manager UI stylesheet is owned by EvoUI, not sSettings');

$module = (string) file_get_contents(dirname(__DIR__) . '/module/sSettingsModule.php');
assertContains('sSettingsController::class', $module, 'module entrypoint controller');
assertContains('->index()', $module, 'module entrypoint index view');

$routes = (string) file_get_contents(dirname(__DIR__) . '/src/Http/routes.php');
assertContains("Route::get('/', [sSettingsController::class, 'index'])->name('index');", $routes, 'manager index route remains');
assertContains("Route::get('configure', [sSettingsController::class, 'configure'])->name('configure');", $routes, 'legacy configure URL still opens the top-level configure tab');
assertNotContains("Route::get('assets/{file}'", $routes, 'module no longer serves local manager stylesheet assets');
assertNotContains('Route::post', $routes, 'legacy POST routes removed in favor of Livewire save actions');

$controller = (string) file_get_contents(dirname(__DIR__) . '/src/Controllers/sSettingsController.php');
assertNotContains('function updateSettings', $controller, 'legacy settings POST controller removed');
assertNotContains('function updateConfigure', $controller, 'legacy configure POST controller removed');
assertNotContains('function asset(', $controller, 'controller no longer serves module-owned CSS assets');
assertContains('ViewFactory::make', $controller, 'controller renders through the typed View facade');

foreach (['en', 'uk', 'ru', 'fr'] as $locale) {
    $translations = include dirname(__DIR__) . '/lang/' . $locale . '/global.php';

    assertSame('tabler-settings', $translations['icon'], "{$locale} icon");
    assertSame('tabler-settings', $translations['module_icon'], "{$locale} module icon");
    assertSame(true, isset($translations['add_tab_after']), "{$locale} add tab after translation");
    assertSame(true, isset($translations['add_field_after']), "{$locale} add field after translation");
    assertSame(true, isset($translations['drag_to_reorder']), "{$locale} drag translation");
    assertSame(true, isset($translations['edit_field_settings']), "{$locale} field modal translation");
    assertSame(true, isset($translations['move_down']), "{$locale} move down translation");
    assertSame(true, isset($translations['move_up']), "{$locale} move up translation");
    assertSame(true, isset($translations['tab_key']), "{$locale} tab key translation");
    assertSame(true, isset($translations['tab_label']), "{$locale} tab label translation");
    assertSame(true, isset($translations['field_options']), "{$locale} field options translation");
    assertSame(true, isset($translations['option_value']), "{$locale} option value translation");
    assertSame(true, isset($translations['option_label']), "{$locale} option label translation");
    assertSame(true, isset($translations['add_option']), "{$locale} add option translation");
    assertSame(true, isset($translations['remove_option']), "{$locale} remove option translation");
    assertSame(true, isset($translations['no_options']), "{$locale} no options translation");
}

$settingsPanel = (string) file_get_contents(dirname(__DIR__) . '/views/livewire/settings-panel.blade.php');
$evoStyles = (string) file_get_contents('/Users/dmi3yy/PhpstormProjects/Extras/evo-ui/resources/css/evo-ui.css');
assertSame(false, is_file(dirname(__DIR__) . '/assets/ssettings.css'), 'sSettings does not ship local manager UI CSS');
assertContains('evo-ui-form-surface--layout-settings', $settingsPanel, 'values screen uses EvoUI settings form surface');
assertContains('evo-ui-settings-values', $settingsPanel, 'values root uses EvoUI settings values primitive');
assertNotContains('<style', $settingsPanel, 'values screen does not contain inline style blocks');
assertNotContains('ssettings-schema-tabs', $settingsPanel, 'values screen no longer renders nested schema tabs');
assertNotContains('wire:click="setActiveTab', $settingsPanel, 'values tab switch moved to module-level navigation');
assertContains('evo-ui-settings-row__usage', $settingsPanel, 'values usage hint uses EvoUI settings row');
assertContains('evo-ui-settings-row__description', $settingsPanel, 'values field description uses EvoUI settings row');
assertContains('.evo-ui-settings-row__usage', $evoStyles, 'EvoUI owns values usage styling');
assertContains('minmax(260px, 340px)', $evoStyles, 'EvoUI settings usage column is wide enough for readable config keys');
assertContains('gap: 14px;', $evoStyles, 'EvoUI settings rows use a controlled label/control gap');
assertContains('overflow-wrap: anywhere;', $evoStyles, 'EvoUI settings usage code wraps instead of clipping long config keys');
assertContains('font-size: 9.5px', $evoStyles, 'EvoUI settings usage hints use smaller compact type');
assertContains('font-weight: 560', $evoStyles, 'EvoUI settings labels are not overly bold');
assertContains('padding: 7px 0;', $evoStyles, 'EvoUI settings rows stay compact without noisy dividers');
assertNotContains('ssettings-compact-row', $settingsPanel, 'values screen has no module-local settings row classes');
assertNotContains('repeat(auto-fit, minmax(260px, 1fr))', $settingsPanel, 'old sparse values grid removed');
assertContains('savedClean: @js($saved)', $settingsPanel, 'values save tracks saved-clean state separately from local dirty');
assertContains('saving: false', $settingsPanel, 'values save tracks in-flight save state');
assertContains('submitSave()', $settingsPanel, 'values save uses a guarded single-flight submit');
assertContains("typeof localDirty !== 'undefined'", $settingsPanel, 'values save button guards Alpine state during Livewire morphs');
assertContains('wire:loading.class="is-disabled"', $settingsPanel, 'values save button looks disabled during Livewire save');
assertContains('fieldSnapshot()', $settingsPanel, 'values dirty state compares field snapshots');
assertContains('trackedFields()', $settingsPanel, 'values dirty snapshot uses tracked form fields');
assertContains("!field.closest('.evo-ui-modal')", $settingsPanel, 'values dirty snapshot ignores modal controls');
assertContains('suppressDirtyUntil: 0', $settingsPanel, 'values dirty state suppresses post-save blur changes');
assertContains('forceClean()', $settingsPanel, 'values save forces the DOM dirty marker clean');
assertContains('x-on:evo-ui:form.saved.window="markSaved()"', $settingsPanel, 'values save clears local dirty marker after persisted save');
assertContains('savedFeedback: false', $settingsPanel, 'values save uses in-button saved feedback state');
assertNotContains('evo-ui-save-toast evo-ui-save-toast--success', $settingsPanel, 'values save must not render a floating saved toast');
assertNotContains('showSavedToast', $settingsPanel, 'values save feedback is not a floating toast state');
assertContains('1600', $settingsPanel, 'values save button feedback auto-dismisses after a short delay');
assertNotContains('ssettings-saved', $settingsPanel, 'values save no longer renders local inline saved label');
assertContains('class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"', $settingsPanel, 'values save uses visible evo-ui button classes');
assertContains('name="circle-check"', $settingsPanel, 'values save switches icon for saved feedback without changing button width');
assertContains("<span class=\"evo-ui-btn__label\">@lang('evo::global.action_save')</span>", $settingsPanel, 'values save keeps visible label stable during saved feedback');
assertNotContains('x-text="savedFeedback ?', $settingsPanel, 'values save must not change visible text during saved feedback');
assertContains("'is-saved': (typeof savedFeedback !== 'undefined' && savedFeedback)", $settingsPanel, 'values save button uses evo-ui disabled and saved class binding');
assertNotContains('x-bind:data-evo-form-saved=', $settingsPanel, 'values avoids root Alpine attr bindings that can fire before x-data exists');
assertContains('data-evo-form-saved="{{ $saved ? \'true\' : \'false\' }}"', $settingsPanel, 'values form exposes saved-clean marker');
assertContains("this.\$el.setAttribute('data-evo-form-saved', 'true')", $settingsPanel, 'values saved event marks form as clean for module guard');
assertContains("this.\$el.setAttribute('data-evo-form-saved', 'false')", $settingsPanel, 'values dirty event clears saved-clean marker');
assertNotContains('wire:model.blur', $settingsPanel, 'values form avoids blur/save races');
assertNotContains('wire:model.live', $settingsPanel, 'values form avoids live/save races');
assertContains('$catalog->parseOptions($field[\'options\'] ?? \'\')', $settingsPanel, 'values screen parses option definitions');
assertContains('$type === \'checkboxgroup\'', $settingsPanel, 'values screen renders checkbox groups');
assertContains('$type === \'radio\'', $settingsPanel, 'values screen renders radio options');
assertContains('$type === \'dropdown\' || $type === \'listbox\'', $settingsPanel, 'values screen renders select fields');
assertContains('$type === \'listboxmultiple\'', $settingsPanel, 'values screen renders multi select fields');
assertContains('multiple', $settingsPanel, 'values screen supports multi select');
assertContains('evo-ui-select--listbox', $settingsPanel, 'values listbox fields use EvoUI taller listbox styling');
assertContains('size="{{ min(max(count($options), 5), 10) }}"', $settingsPanel, 'values listboxes show at least five option rows');
assertContains('select.evo-ui-select--listbox', $evoStyles, 'EvoUI owns single-listbox select height');
assertContains('select.evo-ui-input.evo-ui-select--listbox:not([multiple])', $evoStyles, 'EvoUI listbox height overrides compact single-select height');
assertContains('select.evo-ui-select--listbox option:checked', $evoStyles, 'EvoUI owns selected single-listbox option styling');
assertContains('select.evo-ui-select--multiple option:checked', $evoStyles, 'EvoUI owns selected multi-listbox option styling');
assertContains('data-ssettings-richtext', $settingsPanel, 'values screen marks richtext fields');
assertContains("in_array(\$type, ['url', 'email', 'number', 'date'], true) ? \$type : 'text'", $settingsPanel, 'values screen maps HTML scalar input types');

$modulePanelView = (string) file_get_contents(dirname(__DIR__) . '/views/livewire/module-panel.blade.php');
assertContains('<livewire:ssettings.settings-panel :active-tab="$activeTab"', $modulePanelView, 'module nav passes selected schema tab to values panel');
assertContains('wire:key="ssettings-settings-panel-{{ $activeTab }}"', $modulePanelView, 'values panel remounts on top-level schema tab changes');
assertContains("activeTab === 'configure'", $modulePanelView, 'configure remains a primary module tab');

$indexView = (string) file_get_contents(dirname(__DIR__) . '/views/index.blade.php');
assertContains("@include('evo::partials.assets')", $indexView, 'manager shell loads EvoUI assets');
assertNotContains('sSettings.asset', $indexView, 'manager shell does not load a local sSettings stylesheet');
assertNotContains('<script>', $indexView, 'manager shell does not inject local inline scripts');

$modulePanelComponent = (string) file_get_contents(dirname(__DIR__) . '/src/Livewire/ModulePanel.php');
assertContains('SettingsSchemaRepository', $modulePanelComponent, 'module tabs are built from settings schema');
assertContains("'type' => 'settings'", $modulePanelComponent, 'schema tabs are primary settings tabs');
assertContains("'type' => 'configure'", $modulePanelComponent, 'configure tab is appended as the final primary tab');
assertContains("array_key_first(\$schemaTabs)", $modulePanelComponent, 'legacy settings tab route resolves to first schema tab');
assertContains("use Livewire\\Attributes\\On;", $modulePanelComponent, 'module panel can listen for saved schema refresh events');
assertContains("#[On('ssettings-schema-saved')]", $modulePanelComponent, 'module panel refreshes settings tabs after configure schema save');
assertContains('function refreshSchemaTabs', $modulePanelComponent, 'module panel exposes a schema tab refresh listener');

$configurePanel = (string) file_get_contents(dirname(__DIR__) . '/views/livewire/configure-panel.blade.php');
assertContains('evo-ui-form-surface--layout-settings', $configurePanel, 'configure root uses EvoUI settings form surface');
assertNotContains('<style', $configurePanel, 'configure screen does not contain inline style blocks');
assertContains('evo-ui-builder-toolbar', $configurePanel, 'configure uses EvoUI builder toolbar');
assertContains('class="evo-ui-form-toolbar evo-ui-builder-toolbar__right"', $configurePanel, 'configure save uses the EvoUI builder toolbar context');
assertContains('data-ssettings-add-tab-button', $configurePanel, 'left add-tab icon button');
assertNotContains('data-ssettings-add-tab-button-bottom', $configurePanel, 'configure does not duplicate the add-tab button at the bottom');
assertContains('data-evo-inline-create="ssettings-configure"', $configurePanel, 'configure delegates inline-create focus and overflow behavior to EvoUI');
assertNotContains('data-evo-inline-create-bottom', $configurePanel, 'configure has no bottom inline-create action');
assertNotContains('showBottomCreate', $configurePanel, 'configure does not keep module-local overflow state for inline create');
assertNotContains('updateBottomCreate()', $configurePanel, 'configure does not measure page overflow locally');
assertNotContains('x-show="showBottomCreate"', $configurePanel, 'bottom create action visibility is handled by EvoUI');
assertContains('data-evo-dnd', $configurePanel, 'compact configure uses shared evo-ui DnD root');
assertContains('data-evo-dnd-group-method="sortTabByUid"', $configurePanel, 'shared DnD tab reorder calls configure Livewire tab method');
assertContains('data-evo-dnd-item-method="sortFieldByUid"', $configurePanel, 'shared DnD field reorder calls configure Livewire field method');
assertContains('data-evo-dnd-group', $configurePanel, 'compact tab row uses shared DnD group marker');
assertContains('data-evo-dnd-item', $configurePanel, 'compact field row uses shared DnD item marker');
assertContains('data-evo-dnd-list', $configurePanel, 'compact field list uses shared DnD list marker');
assertContains('<x-evo::reorder-rail', $configurePanel, 'configure rows use shared evo-ui reorder rail');
assertContains('data-ssettings-field-settings-modal', $configurePanel, 'field settings modal');
assertContains('savedClean: @js($saved)', $configurePanel, 'configure save tracks saved-clean state separately from local dirty');
assertContains('submitSave()', $configurePanel, 'configure save uses a guarded single-flight submit');
assertContains('.then((saved) => {', $configurePanel, 'configure save clears local state from the successful Livewire promise instead of relying only on browser events');
assertContains('if (saved === false)', $configurePanel, 'configure save keeps dirty state when the server rejects persistence');
assertContains('completeSave()', $configurePanel, 'configure save uses one idempotent clean-state path');
assertContains("typeof localDirty !== 'undefined'", $configurePanel, 'configure save button guards Alpine state during Livewire morphs');
assertContains('wire:loading.class="is-disabled"', $configurePanel, 'configure save button looks disabled during Livewire save');
assertContains('structureDirty: false', $configurePanel, 'configure tracks structural dirty state for DnD and row actions');
assertContains('saveCleanupTimer: null', $configurePanel, 'configure save cleanup timer is tracked and reset idempotently');
assertContains('fieldSnapshot()', $configurePanel, 'configure dirty state compares a clean snapshot after persisted saves');
assertContains('trackedFields()', $configurePanel, 'configure dirty snapshot tracks non-modal form controls');
assertContains("event?.target?.closest?.('.evo-ui-modal')", $configurePanel, 'configure dirty state ignores modal-only field edit controls until modal save');
assertContains('data-dirty-key="tab-label-{{ $tab[\'key\'] ?? $tabIndex }}-{{ $tabIndex }}"', $configurePanel, 'configure tab labels keep schema-stable dirty metadata for future shared form helpers');
assertContains('data-evo-inline-created="{{ $tab[\'_uid\'] ?? \'\' }}"', $configurePanel, 'configure tab rows expose the shared created-target marker');
assertContains('data-evo-inline-created="{{ $field[\'_uid\'] ?? \'\' }}"', $configurePanel, 'configure field rows expose the shared created-target marker');
assertContains('data-evo-inline-create-id="{{ $tab[\'_uid\'] ?? \'\' }}"', $configurePanel, 'configure tab rows expose the shared inline-create id');
assertContains('data-evo-inline-create-id="{{ $field[\'_uid\'] ?? \'\' }}"', $configurePanel, 'configure field rows expose the shared inline-create id');
assertContains('data-evo-inline-focus', $configurePanel, 'configure marks the primary control for shared inline-create focus');
assertNotContains('data-ssettings-created-tab', $configurePanel, 'configure no longer uses module-local created tab markers');
assertNotContains('data-ssettings-created-field', $configurePanel, 'configure no longer uses module-local created field markers');
assertNotContains('data-ssettings-tab-label-input', $configurePanel, 'configure no longer uses module-local tab focus markers');
assertNotContains('data-ssettings-field-focus-input', $configurePanel, 'configure no longer uses module-local field focus markers');
assertNotContains('x-on:ssettings:item-created.window', $configurePanel, 'configure no longer listens for module-local created events');
assertNotContains('scrollIntoView', $configurePanel, 'created item scroll is delegated to EvoUI');
assertNotContains('focusCreated', $configurePanel, 'created item focus is delegated to EvoUI');
assertContains('suppressDirtyUntil: 0', $configurePanel, 'configure dirty state suppresses post-save blur changes');
assertContains('forceClean()', $configurePanel, 'configure save forces the DOM dirty marker clean');
assertContains("this.\$el.setAttribute('data-evo-form-dirty', this.localDirty ? 'true' : 'false');", $configurePanel, 'configure dirty sync updates the DOM guard marker');
assertContains("'is-saved': (typeof savedFeedback !== 'undefined' && savedFeedback)", $configurePanel, 'configure save button uses evo-ui disabled and saved class binding');
assertContains('x-on:ssettings-dirty.window="markStructureDirty()"', $configurePanel, 'configure save button listens to reorder dirty events');
assertContains('x-on:evo-ui:form-dirty="markStructureDirty()"', $configurePanel, 'configure DnD root listens to shared Alpine-safe evo-ui DnD dirty events');
assertNotContains('x-on:evo-ui:form.dirty', $configurePanel, 'configure must use the Alpine-safe DnD dirty alias instead of dotted event listeners');
assertContains('x-on:evo-ui:form.saved.window="markSaved()"', $configurePanel, 'configure persisted save clears stale dirty marker through the shared saved event');
assertContains("markSaved() {\n            this.completeSave();\n        }", $configurePanel, 'configure saved event reuses the same idempotent cleanup path as the save promise');
assertContains('@disabled(!$dirty || !$writable)', $configurePanel, 'configure save button also receives server-rendered disabled state');
assertContains('savedFeedback: false', $configurePanel, 'configure save uses in-button saved feedback state');
assertNotContains('evo-ui-save-toast evo-ui-save-toast--success', $configurePanel, 'configure save must not render a floating saved toast');
assertNotContains('showSavedToast', $configurePanel, 'configure save feedback is not a floating toast state');
assertContains('1600', $configurePanel, 'configure save button feedback auto-dismisses after a short delay');
assertNotContains('ssettings-saved', $configurePanel, 'configure save no longer renders local inline saved label');
assertContains('class="evo-ui-btn evo-ui-btn--primary evo-ui-btn--filled"', $configurePanel, 'configure save uses visible evo-ui button classes');
assertContains('name="circle-check"', $configurePanel, 'configure save switches icon for saved feedback without changing button width');
assertContains("<span class=\"evo-ui-btn__label\">@lang('evo::global.action_save')</span>", $configurePanel, 'configure save keeps visible label stable during saved feedback');
assertNotContains('x-text="savedFeedback ?', $configurePanel, 'configure save must not change visible text during saved feedback');
assertContains('data-evo-form-saved="{{ $saved ? \'true\' : \'false\' }}"', $configurePanel, 'configure form exposes saved-clean marker');
assertNotContains('x-bind:data-evo-form-saved=', $configurePanel, 'configure avoids root Alpine attr bindings that can fire before x-data exists');
assertContains("this.\$el.setAttribute('data-evo-form-saved', 'true')", $configurePanel, 'configure saved event marks form as clean for module guard');
assertContains("this.\$el.setAttribute('data-evo-form-saved', 'false')", $configurePanel, 'configure dirty event clears saved-clean marker');
assertContains('x-on:change="$wire.updateTabLabelByUid', $configurePanel, 'tab label updates by stable uid instead of draggable index');
assertContains('x-model="label"', $configurePanel, 'field modal keeps label locally until modal save');
assertContains('draftSnapshot: \'\'', $configurePanel, 'field modal stores a clean local draft snapshot');
assertContains('draftPayload()', $configurePanel, 'field modal compares current data to a stable draft payload');
assertContains('isDraftDirty()', $configurePanel, 'field modal can disable its primary action while unchanged');
assertContains('applyFieldEdit()', $configurePanel, 'field modal applies local draft edits before global save');
assertContains('this.$wire.updateFieldByUid', $configurePanel, 'field modal applies by stable field uid');
assertContains('x-bind:disabled="!isDraftDirty()"', $configurePanel, 'field modal primary action is disabled until the local draft changes');
assertContains('optionTypes: @js($optionTypes)', $configurePanel, 'field modal knows which types expose options');
assertContains('options: @js($fieldOptions)', $configurePanel, 'field modal hydrates option editor rows');
assertContains('evo-ui-dnd-option-list', $configurePanel, 'field modal renders EvoUI option editor');
assertContains('x-model="option.value"', $configurePanel, 'field modal edits option value inline');
assertContains('x-model="option.label"', $configurePanel, 'field modal edits option label inline');
assertContains('option._uid || this.optionUid()', $configurePanel, 'field modal gives option rows stable local keys');
assertContains(':key="option._uid || optionIndex"', $configurePanel, 'option rows keep stable Alpine keys after reorder');
assertContains('addOption(index = null)', $configurePanel, 'field modal can insert an option after the current row');
assertContains('this.options.splice(position, 0', $configurePanel, 'field modal inserts option rows in place');
assertContains('moveOption(index, direction)', $configurePanel, 'field modal exposes manual option reorder controls');
assertContains('moveOptionByUid(uid, position)', $configurePanel, 'field modal applies shared DnD reorder events by stable option uid');
assertNotContains('optionDragIndex: null', $configurePanel, 'field modal no longer tracks local option drag state');
assertNotContains('optionHandleIndex: null', $configurePanel, 'field modal no longer keeps module-owned handle state');
assertNotContains('optionPlaceholderHeight: 38', $configurePanel, 'field modal no longer owns placeholder height state');
assertNotContains('hoverOption(index, event)', $configurePanel, 'field modal no longer owns option hover positioning');
assertNotContains('dropOptionList(event)', $configurePanel, 'field modal no longer owns option list drop handling');
assertNotContains('moveDraggedOption(to)', $configurePanel, 'field modal no longer owns option drop helper logic');
assertNotContains('event.dataTransfer.setDragImage', $configurePanel, 'option drag preview is owned by EvoUI');
assertContains('data-evo-dnd-option-row-selector="[data-ssettings-parent-option-row-disabled]"', $configurePanel, 'parent Configure DnD ignores modal option rows so nested option DnD owns them');
assertContains('data-evo-dnd-option-list', $configurePanel, 'option editor uses the shared EvoUI option-list marker');
assertContains('data-evo-dnd-option-row', $configurePanel, 'option editor rows use the shared EvoUI option-row marker');
assertNotContains('x-on:dragstart.stop', $configurePanel, 'option editor must let EvoUI own modal dragstart events');
assertNotContains('x-on:dragover.stop', $configurePanel, 'option editor must let EvoUI own modal dragover events');
assertNotContains('x-on:drop.stop', $configurePanel, 'option editor must let EvoUI own modal drop events');
assertContains('x-on:evo-ui:dnd-option-changed="moveOptionByUid($event.detail.uid, $event.detail.position)"', $configurePanel, 'option editor applies shared EvoUI option reorder events to the Alpine option array');
assertContains('x-on:evo-ui:dnd-option-changed', $configurePanel, 'option editor receives shared EvoUI option reorder events through the Alpine-safe alias');
assertContains('moveOptionByUid(uid, position)', $configurePanel, 'field modal can reorder options by shared DnD uid');
assertContains('data-evo-dnd-option-value', $configurePanel, 'option value inputs expose the shared value marker');
assertContains('data-evo-dnd-option-label', $configurePanel, 'option label inputs expose the shared label marker');
assertNotContains('ssettings-option-dnd-placeholder', $configurePanel, 'option placeholder visuals are owned by EvoUI');
assertNotContains('ssettings-option-editor-item', $configurePanel, 'option wrapper styles are not module-owned');
assertNotContains('--ssettings-option-placeholder-height', $configurePanel, 'option placeholder sizing is not module-owned');
assertNotContains('height: var(--ssettings-option-placeholder-height, 38px)', $evoStyles, 'option placeholder sizing is owned by EvoUI without sSettings tokens');
assertNotContains('.ssettings-option-editor-row.is-drag-hidden', $evoStyles, 'dragged option row hidden state is owned by EvoUI');
assertNotContains('dropOption(index, event)', $configurePanel, 'option drop handling is delegated to EvoUI');
assertNotContains('data-ssettings-option-drag-handle', $configurePanel, 'option drag handles use shared EvoUI markers');
assertNotContains('data-ssettings-option-list', $configurePanel, 'option lists use shared EvoUI markers');
assertContains('data-evo-dnd-option-row', $configurePanel, 'option rows expose shared option row markers while the handle owns native drag');
assertNotContains('optionPlaceholderIndex', $configurePanel, 'option dragging state is not module-owned');
assertNotContains('optionDropIndex', $configurePanel, 'option drop target state is not module-owned');
assertNotContains('cancelOptionHandle', $configurePanel, 'option handle bookkeeping is not module-owned');
assertNotContains('dragOptionStart', $configurePanel, 'option dragstart is not module-owned');
assertNotContains('dragOptionEnd', $configurePanel, 'option dragend is not module-owned');
assertContains('data-evo-dnd-handle', $configurePanel, 'option drag handle uses the shared EvoUI DnD handle marker');
assertContains('draggable="false" data-evo-dnd-option-value', $configurePanel, 'option value inputs do not become drag sources');
assertContains('draggable="false" data-evo-dnd-option-label', $configurePanel, 'option label inputs do not become drag sources');
assertNotContains('data-ssettings-option-row', $configurePanel, 'option rows no longer expose module-local drop targets');
assertNotContains('window.__sSettingsOptionDrag', $configurePanel, 'option drag state is fully delegated to EvoUI');
assertContains('class="evo-ui-reorder-rail"', $configurePanel, 'option rows use the shared up/handle/down reorder rail pattern');
assertContains('draggable="false"' . PHP_EOL . '                                                                data-evo-drag-handle', $configurePanel, 'option drag handle must not start a parallel native drag path in modals');
assertContains('data-evo-drag-handle', $configurePanel, 'option drag handle remains the shared DnD pointer control');
assertContains('x-bind:data-evo-dnd-uid="option._uid"' . PHP_EOL . '                                                        draggable="false"', $configurePanel, 'option rows use EvoUI pointer-owned modal DnD instead of native HTML5 drag');
assertContains('x-bind:draggable="open ? \'false\' : \'true\'"', $configurePanel, 'field row drag is disabled while its settings modal is open');
assertNotContains("optionHandle: '[data-ssettings-option-drag-handle]'", $configurePanel, 'local parent DnD runtime no longer owns option drag handles');
assertNotContains("optionRow: '[data-ssettings-option-row]'", $configurePanel, 'local parent DnD runtime no longer owns option rows');
assertNotContains('acceptOptionDragOver', $configurePanel, 'option DnD is not routed through removed local parent runtime');
assertNotContains('x-on:drop.prevent.stop="dropOption(optionIndex, $event)"', $configurePanel, 'option rows no longer use module-local drop handlers');
assertContains('evo-ui-modal--lg', $configurePanel, 'field modal uses EvoUI large modal width');
assertContains('.evo-ui-modal__body', $evoStyles, 'EvoUI modal body owns scroll behavior');
assertContains('overflow-y: auto', $evoStyles, 'EvoUI modal body scrolls long option lists instead of overflowing');
assertContains('evo-ui-field__label', $configurePanel, 'option editor labels use EvoUI form field labels');
assertContains('text-align: right;', $evoStyles, 'EvoUI field labels right-align on desktop');
assertNotContains('ssettings-options-editor', $configurePanel, 'option editor must not use a module-local wrapper.');
assertContains('x-on:click="addOption(optionIndex)"', $configurePanel, 'option rows expose add-after action');
assertContains('class="evo-ui-dnd-actions evo-ui-row-actions evo-ui-row-actions--compact"', $configurePanel, 'option rows use shared grouped evo-ui row actions');
assertContains('class="evo-ui-drag-handle"', $configurePanel, 'option drag handle uses shared EvoUI styling');
assertContains('evo-ui-row-action evo-ui-row-action--success', $configurePanel, 'option add action uses evo-ui success tone');
assertContains('evo-ui-row-action evo-ui-row-action--danger', $configurePanel, 'option delete action uses evo-ui danger tone');
assertContains('evo-ui-dnd-actions evo-ui-row-actions evo-ui-row-actions--compact', $configurePanel, 'option actions use shared compact row-action palette');
assertNotContains('options_format_hint', $configurePanel, 'option editor hides technical storage format from UI');
assertContains('this.type, this.options', $configurePanel, 'field modal sends options to Livewire save method');
assertNotContains('wire:model.live.debounce.150ms', $configurePanel, 'configure form avoids live debounce races before save');
assertNotContains('wire:model.live=', $configurePanel, 'configure form avoids live update races before save');
assertNotContains('wire:model="tabs.{{ $tabIndex }}.fields.{{ $fieldIndex }}.label"', $configurePanel, 'field modal avoids draggable index-bound models');
$reorderRail = file_get_contents('/Users/dmi3yy/PhpstormProjects/Extras/evo-ui/views/components/reorder-rail.blade.php');
if ($reorderRail === false) {
    fwrite(STDERR, "Assertion failed: shared reorder rail file is readable\n");
    exit(1);
}
assertContains('data-evo-dnd-handle', $reorderRail, 'shared reorder rail exposes DnD handle marker');
assertContains('draggable="true"', $configurePanel, 'configure rows remain native draggable');
assertContains('window.EvoUI?.initDnd?.($el)', $configurePanel, 'configure initializes shared evo-ui DnD runtime');
assertNotContains('data-ssettings-drag-handle', $configurePanel, 'local drag handle marker removed from configure builder');
assertNotContains('data-ssettings-native-dnd', $configurePanel, 'local native dnd root removed from configure builder');
assertNotContains('data-ssettings-dropzone', $configurePanel, 'local insertion drop zones removed from configure builder');
assertNotContains('window.sSettingsNativeDnd', $configurePanel, 'local native dnd runtime removed from configure builder');
assertNotContains('window.sSettingsDragData', $configurePanel, 'local CareOffice-style drag state removed from configure builder');
assertNotContains("event.dataTransfer.setData('application/x-ssettings'", $configurePanel, 'configure builder no longer owns custom local payload type');
assertContains('wire:target="save,sortFieldByUid,sortTabByUid,moveFieldStep,moveTab,addField,addFieldAfter,addTab,addTabAfter,removeField,removeTab,commitFieldEdit,updateFieldByUid,updateTabLabelByUid,clearOpenField"', $configurePanel, 'save button is disabled while reorder and edit actions are pending');
assertNotContains('wire[method](...args)', $configurePanel, 'configure builder no longer owns local Livewire DnD proxy calls');
assertNotContains("document.addEventListener('dragover'", $configurePanel, 'configure builder no longer binds document-level dragover handlers');
assertNotContains("document.addEventListener('drop'", $configurePanel, 'configure builder no longer binds document-level drop handlers');
assertNotContains('ssettings-dnd-placeholder', $configurePanel, 'configure builder uses shared evo-ui placeholder styling');
assertNotContains('resolveFieldTarget', $configurePanel, 'coordinate-based field drop removed');
assertNotContains('resolveTabTarget', $configurePanel, 'coordinate-based tab drop removed');
assertContains('.evo-ui-dnd-row--with-badge', $evoStyles, 'EvoUI owns configure field row layout');
assertContains('.evo-ui-dnd-group-row--nested', $evoStyles, 'EvoUI owns configure nested group layout');
assertNotContains('.ssettings-compact-field.is-drag-hidden { display: none; }', $evoStyles, 'native dnd does not cancel browser drag by display-hiding the source row');
assertContains('width: min(100%, 100vw);', $evoStyles, 'mobile configure drag rows cannot overflow the viewport');
assertContains('.evo-ui-dnd-list.is-drag-over .evo-ui-dnd-empty', $evoStyles, 'empty field copy hides while shared placeholder is active');
assertNotContains('var(--evo-ui-success) 14%', $evoStyles, 'native dnd removes loud green placeholder fill');
assertNotContains('height: 5px; opacity: 1; background: var(--evo-ui-success)', $evoStyles, 'native dnd removes loud insertion stripe');
assertNotContains('sSettingsConfigureDnd', $configurePanel, 'custom native dnd runtime removed');
assertNotContains('wire:sort=', $configurePanel, 'Livewire wire:sort attributes removed');
assertNotContains('wire:sort:', $configurePanel, 'Livewire wire:sort modifiers removed');
assertContains('addTabAfter', $configurePanel, 'add tab after control');
assertContains('addFieldAfter', $configurePanel, 'add field after control');
assertContains(':move-up="\'moveTab(\' . $tabIndex . \', -1)\'"', $configurePanel, 'tab move up control is passed to shared reorder rail');
assertContains(':move-down="\'moveTab(\' . $tabIndex . \', 1)\'"', $configurePanel, 'tab move down control is passed to shared reorder rail');
assertContains(':move-up="\'moveFieldStep(\' . $tabIndex . \', \' . $fieldIndex . \', -1)\'"', $configurePanel, 'field move up control is passed to shared reorder rail');
assertContains(':move-down="\'moveFieldStep(\' . $tabIndex . \', \' . $fieldIndex . \', 1)\'"', $configurePanel, 'field move down control is passed to shared reorder rail');
assertContains('grid-template-areas: "reorder label actions"', $evoStyles, 'desktop tab grid areas');
assertContains('grid-template-areas: "reorder summary type actions"', $evoStyles, 'desktop field grid areas');
assertContains('.evo-ui-dnd-inline-field > span', $evoStyles, 'configure inline labels align toward controls');
assertNotContains('ssettings-configure-bottom-create', $configurePanel, 'bottom create action is removed with the duplicate button');
assertContains('evo-ui-dnd-badge', $configurePanel, 'compact field type badge');
assertContains('title="{{ $types[$fieldType] ?? ucfirst($fieldType) }}"', $configurePanel, 'field type badge exposes full type label');
assertContains('max-inline-size: min(100%, var(--evo-ui-chip-compact-max-inline-size));', $evoStyles, 'field type badge can clamp long type names');
assertContains('evo-ui-dnd-key', $configurePanel, 'system key chip uses EvoUI DnD key');
assertContains('evo-ui-row-action--primary', $configurePanel, 'edit action uses evo-ui primary tone');
assertNotContains('evo-ui-row-action--info', $configurePanel, 'add-tab action must not use copy/info tone');
assertNotContains('copy-plus', $configurePanel, 'add-tab action must use a plain plus icon');
assertContains('evo-ui-row-action--success', $configurePanel, 'add action uses evo-ui success tone');
assertContains('evo-ui-row-action--danger', $configurePanel, 'delete action uses evo-ui danger tone');
assertContains('addFieldAfter({{ $tabIndex }}, {{ $fieldIndex }})">
                                    <x-evo::icon name="plus" />
                                </button>
                                <button type="button" class="evo-ui-row-action evo-ui-row-action--primary"', $configurePanel, 'field actions are ordered add, edit, delete');
assertContains('<x-evo::icon name="edit" />', $configurePanel, 'field settings action uses edit icon');
assertNotContains("@lang('sSettings::global.tab_key')", $configurePanel, 'tab key label is hidden from compact UI');
assertNotContains('wire:model.blur="tabs.{{ $tabIndex }}.key"', $configurePanel, 'tab key input is hidden from compact UI');
assertContains("@lang('sSettings::global.tab_label')", $configurePanel, 'tab label used');
assertContains("@lang('evo::global.action_apply')", $configurePanel, 'modal primary action uses local apply copy instead of global save copy');
assertNotContains('ssettings-configure__field-grid', $configurePanel, 'old configure field grid removed');
assertNotContains('ssettings-compact-heading', $configurePanel, 'redundant configure heading removed');
assertNotContains('<x-evo::icon name="settings" />', $configurePanel, 'field settings gear icon removed');

$settingsComponent = (string) file_get_contents(dirname(__DIR__) . '/src/Livewire/SettingsPanel.php');
assertContains('public string $activeTab', $settingsComponent, 'values active tab state');
assertContains('function setActiveTab', $settingsComponent, 'values active tab method');

$configureComponent = (string) file_get_contents(dirname(__DIR__) . '/src/Livewire/ConfigurePanel.php');
assertContains('function addTabAfter', $configureComponent, 'configure add tab after method');
assertContains('function addFieldAfter', $configureComponent, 'configure add field after method');
assertContains('function commitFieldEdit', $configureComponent, 'configure field modal commit method');
assertContains('public string $openFieldUid', $configureComponent, 'configure tracks new field modal auto-open target');
assertContains('function updateFieldByUid', $configureComponent, 'configure field modal saves by uid');
assertContains('array $options = []', $configureComponent, 'configure field modal accepts option rows');
assertContains('$catalog->serializeOptions($options)', $configureComponent, 'configure serializes option rows before schema save');
assertContains('function updateTabLabelByUid', $configureComponent, 'configure tab labels update by uid');
assertContains('function clearOpenField', $configureComponent, 'configure can clear auto-open modal target');
assertContains('public function save(SettingsSchemaRepository $schema, SystemSettingsStore $store): bool', $configureComponent, 'configure save returns persistence success to Alpine');
assertContains('return true;', $configureComponent, 'configure save reports successful persistence to the browser');
assertContains('return false;', $configureComponent, 'configure save reports failed persistence to the browser');
assertContains("\$this->dispatch('ssettings-dirty');", $configureComponent, 'configure server actions notify Alpine dirty state');
assertContains("\$this->dispatch('ssettings-schema-saved');", $configureComponent, 'configure save notifies parent module tabs to refresh without reload');
assertContains('function fillDataFromSchema', $configureComponent, 'configure can hydrate from a just-saved schema without rereading stale config');
assertContains('$this->fillDataFromSchema($normalized);', $configureComponent, 'configure save redraws from normalized saved schema');
assertContains("\$this->dispatch('evo-ui:inline-create.created', root: 'ssettings-configure', id: \$tab['_uid'], uid: \$tab['_uid']);", $configureComponent, 'configure dispatches the created tab uid through the shared EvoUI inline-create event');
assertContains("\$this->dispatch('evo-ui:inline-create.created', root: 'ssettings-configure', id: \$field['_uid'], uid: \$field['_uid']);", $configureComponent, 'configure dispatches the created field uid through the shared EvoUI inline-create event');
assertNotContains("\$this->dispatch('ssettings:item-created'", $configureComponent, 'configure no longer dispatches module-local inline-create events');
assertContains('previousTabsByKey', $configureComponent, 'configure save preserves tab uids across normalized redraws');
assertContains('previousFieldsByName', $configureComponent, 'configure save preserves field uids across normalized redraws');
assertContains('function snapshotData', $configureComponent, 'configure dirty snapshots ignore volatile local uids');
assertNotContains('$this->fillData($schema);' . PHP_EOL . '            $this->saved = true;', $configureComponent, 'configure save must not reread config immediately after write');
assertContains('function reorderTabs', $configureComponent, 'configure reorder tabs method');
assertContains('function reorderFields', $configureComponent, 'configure reorder fields method');
assertContains('function moveFieldStep', $configureComponent, 'configure field boundary move method');
assertContains('function sortTabByUid', $configureComponent, 'configure Livewire tab sort method');
assertContains('function sortFieldByUid', $configureComponent, 'configure Livewire field sort method');
assertContains('function sortTabs', $configureComponent, 'configure delegated sort tabs method');
assertContains('function sortFields', $configureComponent, 'configure delegated sort fields method');
assertContains('function sortAllFields', $configureComponent, 'configure cross-tab field sort method');
assertContains('function sortByUid', $configureComponent, 'configure uid sorting helper');
assertContains('function pullFieldByUid', $configureComponent, 'configure field uid pull helper');
assertContains('function tabIndexByUid', $configureComponent, 'configure tab uid lookup helper');
assertContains('function uniqueFieldName', $configureComponent, 'configure generates globally unique new field names');
assertContains('function uniqueTabKey', $configureComponent, 'configure generates unique hidden tab keys');
assertContains('function displayText', $configureComponent, 'configure translates display labels before editing');

$configureReflection = new ReflectionMethod(Seiger\sSettings\Livewire\ConfigurePanel::class, 'displayText');
$configureReflection->setAccessible(true);
assertSame(__('sSettings::global.basicTab'), $configureReflection->invoke(new Seiger\sSettings\Livewire\ConfigurePanel(), 'sSettings::global.basicTab'), 'configure display text translation');

$schemaRepository = new Seiger\sSettings\Support\SettingsSchemaRepository(new Seiger\sSettings\Support\FieldCatalog());
$configure = new Seiger\sSettings\Livewire\ConfigurePanel();
$configure->mount($schemaRepository);
$toSchema = new ReflectionMethod(Seiger\sSettings\Livewire\ConfigurePanel::class, 'toSchema');
$toSchema->setAccessible(true);
$initialSchema = $toSchema->invoke($configure);
$configure->moveTab(0, 1);
$movedSchema = $toSchema->invoke($configure);
assertSame(array_reverse(array_column($initialSchema, 'key')), array_column($movedSchema, 'key'), 'hidden tab keys survive arrow reordering');
assertSame(true, $configure->dirty, 'arrow tab reorder marks configure form dirty');

$configure->tabs = [
    [
        '_uid' => 'tab-a',
        'key' => 'duplicate',
        'label' => 'First',
        'fields' => [
            ['_uid' => 'field-a', 'name' => 'same', 'label' => 'A', 'description' => '', 'type' => 'text'],
        ],
    ],
    [
        '_uid' => 'tab-b',
        'key' => 'duplicate',
        'label' => 'Second',
        'fields' => [
            ['_uid' => 'field-b', 'name' => 'same', 'label' => 'B', 'description' => '', 'type' => 'text'],
        ],
    ],
];
$duplicateSchema = $toSchema->invoke($configure);
assertSame(2, count($duplicateSchema), 'toSchema preserves duplicate-key tabs before normalization');
$normalizedDuplicates = $schemaRepository->normalize($duplicateSchema);
assertSame(['duplicate', 'duplicate_2'], array_keys($normalizedDuplicates), 'normalization makes duplicate tab keys unique without dropping tabs');
assertSame('same', $normalizedDuplicates['duplicate']['fields'][0]['name'], 'first duplicate field keeps base name');
assertSame('same_2', $normalizedDuplicates['duplicate_2']['fields'][0]['name'], 'second duplicate field gets unique name');

$configure->tabs = [
    [
        '_uid' => 'tab-a',
        'key' => 'first',
        'label' => 'First',
        'fields' => [
            ['_uid' => 'field-a', 'name' => 'new_field_1', 'label' => '', 'description' => '', 'type' => 'text'],
        ],
    ],
    [
        '_uid' => 'tab-b',
        'key' => 'second',
        'label' => 'Second',
        'fields' => [],
    ],
];
$configure->addField(1);
assertSame('new_field_2', $configure->tabs[1]['fields'][0]['name'], 'new fields are named uniquely across tabs');
assertSame($configure->tabs[1]['fields'][0]['_uid'], $configure->openFieldUid, 'new field is marked to auto-open its settings modal');
$configure->updateFieldByUid($configure->tabs[1]['fields'][0]['_uid'], 'new_field_2', 'Facebook2', 'Changed description', 'textarea');
$editedSchema = $schemaRepository->normalize($toSchema->invoke($configure));
assertSame('Facebook2', $editedSchema['second']['fields'][0]['label'], 'field modal label edit survives schema normalization');
assertSame('Changed description', $editedSchema['second']['fields'][0]['description'], 'field modal description edit survives schema normalization');
assertSame('textarea', $editedSchema['second']['fields'][0]['type'], 'field modal type edit survives schema normalization');
assertSame('', $configure->openFieldUid, 'saving field modal clears auto-open target');

$configure->updateFieldByUid($configure->tabs[1]['fields'][0]['_uid'], 'new_field_2', 'Facebook2', 'Changed description', 'listbox-multiple', [
    ['value' => 'a', 'label' => 'Alpha'],
    ['value' => 'b', 'label' => 'Beta'],
]);
$optionSchema = $schemaRepository->normalize($toSchema->invoke($configure));
assertSame('listboxmultiple', $optionSchema['second']['fields'][0]['type'], 'field modal normalizes Evolution multi listbox type');
assertSame('a==Alpha||b==Beta', $optionSchema['second']['fields'][0]['options'] ?? '', 'field modal option rows survive schema normalization');

$uidStableSchema = [
    'second' => [
        'label' => 'Second',
        'fields' => [
            ['name' => 'new_field_2', 'label' => 'Facebook2', 'description' => 'Changed description', 'type' => 'listboxmultiple', 'options' => 'a==Alpha||b==Beta'],
        ],
    ],
];
$fillDataFromSchema = new ReflectionMethod(Seiger\sSettings\Livewire\ConfigurePanel::class, 'fillDataFromSchema');
$fillDataFromSchema->setAccessible(true);
$tabUidBeforeSaveHydrate = $configure->tabs[1]['_uid'];
$fieldUidBeforeSaveHydrate = $configure->tabs[1]['fields'][0]['_uid'];
$fillDataFromSchema->invoke($configure, $uidStableSchema);
assertSame($tabUidBeforeSaveHydrate, $configure->tabs[0]['_uid'], 'configure normalized redraw preserves existing tab uid');
assertSame($fieldUidBeforeSaveHydrate, $configure->tabs[0]['fields'][0]['_uid'], 'configure normalized redraw preserves existing field uid');

$configure->tabs = [
    ['_uid' => 'tab-a', 'key' => 'first', 'label' => 'First', 'fields' => []],
    ['_uid' => 'tab-b', 'key' => 'second', 'label' => 'Second', 'fields' => []],
];
$configure->sortTabByUid('tab-b', 0);
$configure->updateTabLabelByUid('tab-b', 'Second renamed');
assertSame('Second renamed', $configure->tabs[0]['label'], 'tab label update follows stable uid after reorder');
assertSame('First', $configure->tabs[1]['label'], 'tab reorder does not duplicate labels');

$schemaRepositorySource = (string) file_get_contents(dirname(__DIR__) . '/src/Support/SettingsSchemaRepository.php');
assertContains('escapeshellarg(PHP_BINARY)', $schemaRepositorySource, 'settings config lint uses current PHP binary');
assertContains('function uniqueKey', $schemaRepositorySource, 'settings schema repository uniquifies duplicate keys');

$defaultNormalized = $schemaRepository->normalize(require dirname(__DIR__) . '/config/sSettingsSettings.php');
assertSame(['basicTab', 'socialsTab'], array_keys($defaultNormalized), 'default tab keys keep legacy camelCase names after save');

echo "sSettings smoke OK\n";

function assertSame(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "Assertion failed: {$label}\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

function assertContains(string $needle, string $haystack, string $label): void
{
    if (!str_contains($haystack, $needle)) {
        fwrite(STDERR, "Assertion failed: {$label}\nMissing: {$needle}\n");
        exit(1);
    }
}

function assertNotContains(string $needle, string $haystack, string $label): void
{
    if (str_contains($haystack, $needle)) {
        fwrite(STDERR, "Assertion failed: {$label}\nUnexpected: {$needle}\n");
        exit(1);
    }
}
