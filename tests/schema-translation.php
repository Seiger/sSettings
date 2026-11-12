<?php

declare(strict_types=1);

namespace Livewire {
    // The editor's schema round trip does not need a Livewire request.
    class Component {}
}

namespace {
    use Illuminate\Container\Container;
    use Illuminate\Translation\ArrayLoader;
    use Illuminate\Translation\Translator;
    use Seiger\sSettings\Livewire\ConfigurePanel;
    use Seiger\sSettings\Support\FieldCatalog;

    if (PHP_SAPI !== 'cli') {
        exit(1);
    }

    function app($key) { return Container::getInstance()->make($key); }
    function __($key) { return app('translator')->get($key); }

    $core = getenv('SSETTINGS_DEMO_CORE');
    if (!$core || !is_file($core . '/vendor/autoload.php')) {
        throw new RuntimeException('Set SSETTINGS_DEMO_CORE');
    }
    require $core . '/vendor/autoload.php';
    require_once dirname(__DIR__) . '/src/Support/FieldCatalog.php';
    require_once dirname(__DIR__) . '/src/Livewire/ConfigurePanel.php';

    $loader = new ArrayLoader();
    $loader->addMessages('en', 'global', [
        'tab' => 'Project settings',
        'label' => 'Project label',
        'description' => 'Project description',
        'group' => ['option' => 'An option'],
    ], 'project');
    $loader->addMessages('uk', 'global', ['tab' => 'Налаштування проєкту'], 'project');
    $loader->addMessages('en', 'global', ['tab' => 'Basic settings'], 'sSettings');
    $translator = new Translator($loader, 'en');
    Container::getInstance()->instance('translator', $translator);

    $count = 0;
    $check = static function (mixed $expected, mixed $actual, string $message) use (&$count): void {
        if ($expected !== $actual) {
            throw new RuntimeException($message . ': ' . var_export($actual, true));
        }
        $count++;
    };

    $catalog = new FieldCatalog();
    $check('Basic settings', $catalog->displayText('sSettings::global.tab'), 'package translation');
    $check('Project settings', $catalog->displayText('project::global.tab'), 'project translation');
    $check('Український підпис', $catalog->displayText('Український підпис'), 'literal label');
    $check('project::global.missing', $catalog->displayText('project::global.missing'), 'missing key fallback');
    $check(['option' => 'An option'], __('project::global.group'), 'the real translator can return an array');
    $check('project::global.group', $catalog->displayText('project::global.group'), 'group fallback');
    $check('An option', $catalog->displayText('project::global.group.option'), 'individual option translation');

    $panel = new ConfigurePanel();
    $load = new ReflectionMethod($panel, 'fillDataFromSchema');
    $serialize = new ReflectionMethod($panel, 'toSchema');
    $options = 'a==project::global.group.option||b==project::global.group';
    $load->invoke($panel, ['main' => ['label' => 'project::global.tab', 'fields' => [[
        'name' => 'topic', 'label' => 'project::global.label', 'description' => 'project::global.description',
        'type' => 'dropdown', 'options' => $options,
    ]]]]);
    $check('Project settings', $panel->tabs[0]['label'], 'the editor displays a localized tab');
    $check('Project label', $panel->tabs[0]['fields'][0]['label'], 'the editor displays a localized field');
    $panel->tabs[0]['fields'][0]['name'] = 'new_topic';
    $translator->setLocale('uk');
    $saved = $serialize->invoke($panel)[0];
    $check('project::global.tab', $saved['label'], 'saving an unrelated edit preserves the tab key');
    $check('project::global.label', $saved['fields'][0]['label'], 'saving an unrelated edit preserves the field key');
    $check('project::global.description', $saved['fields'][0]['description'], 'saving an unrelated edit preserves the description key');
    $check($options, $saved['fields'][0]['options'], 'option keys survive the round trip');
    $check('Налаштування проєкту', $catalog->displayText($saved['label']), 'the saved key can still change locale');
    $check(false, isset($saved['fields'][0]['_sourceText']), 'display metadata stays out of the schema');

    $panel->tabs[0]['label'] = 'New tab';
    $panel->tabs[0]['fields'][0]['label'] = 'New label';
    $panel->tabs[0]['fields'][0]['description'] = '';
    $edited = $serialize->invoke($panel)[0];
    $check('New tab', $edited['label'], 'save an explicit tab edit');
    $check('New label', $edited['fields'][0]['label'], 'save an explicit field edit');
    $check('', $edited['fields'][0]['description'], 'save an explicitly cleared description');

    echo "Schema translation checks passed: {$count}\n";
}
