<?php
// Isolated in-memory database: never bootstrap or modify the site's database/config.
if (PHP_SAPI !== 'cli') { exit(1); }
function evo() { return $GLOBALS['testEvo']; }
function __($key) { return $key; }
function app($key = null) {
    $container = \Illuminate\Container\Container::getInstance();
    return $key === null ? $container : $container->make($key);
}
$core = getenv('SSETTINGS_DEMO_CORE');
if (!$core || !is_file($core . '/vendor/autoload.php')) { throw new RuntimeException('Set SSETTINGS_DEMO_CORE'); }
require $core . '/vendor/autoload.php';
foreach (['FieldCatalog', 'SettingsLanguages', 'SettingsSchemaRepository', 'SystemSettingsStore'] as $class) {
    require_once dirname(__DIR__) . '/src/Support/' . $class . '.php';
}
$container = new Illuminate\Container\Container();
Illuminate\Container\Container::setInstance($container);
$db = new Illuminate\Database\Capsule\Manager($container);
$db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
$db->bootEloquent();
$db->getConnection()->getSchemaBuilder()->create('system_settings', function ($table) {
    $table->string('setting_name')->primary();
    $table->text('setting_value')->nullable();
});
$GLOBALS['testEvo'] = new class {
    public function getConfig($key, $default = '') {
        return EvolutionCMS\Models\SystemSetting::query()->find($key)?->setting_value ?? $default;
    }
    public function setConfig($key, $value) {}
    public function clearCache($type) {}
    public function getLocale() { return 'uk'; }
};
$checks = 0;
$check = function ($expected, $actual, $message) use (&$checks) {
    if ($expected !== $actual) { throw new RuntimeException($message . ': ' . var_export($actual, true)); }
    $checks++;
};
$catalog = new Seiger\sSettings\Support\FieldCatalog();
$languages = new Seiger\sSettings\Support\SettingsLanguages();
$schema = new Seiger\sSettings\Support\SettingsSchemaRepository($catalog);
$store = new Seiger\sSettings\Support\SystemSettingsStore($catalog);
$field = ['name' => 'notice', 'label' => 'Notice', 'type' => 'textarea', 'multilingual' => true];
$definition = ['basic' => ['fields' => [$field]]];
$check(false, $languages->available(), 'sLang optional');
$check(['notice'], array_column($store->fieldDefinitions($definition), 'name'), 'single value without sLang');
$fakeLang = new class {
    public array $languages = ['uk', 'en'];
    public function langDefault() { return 'uk'; }
    public function langConfig() { return $this->languages; }
};
$container->instance('sLang', $fakeLang);
$check(['uk', 'en'], $languages->locales(), 'configured languages');
$check(['notice_uk', 'notice_en'], array_column($store->fieldDefinitions($definition), 'name'), 'localized keys');
EvolutionCMS\Models\SystemSetting::query()->create(['setting_name' => 'sset_notice', 'setting_value' => 'Initial']);
$store->syncSchema($definition);
$check('Initial', $store->values($definition)['sset_notice_uk'], 'seed default language');
$check('', $store->values($definition)['sset_notice_en'], 'other language starts empty');
$store->saveValues($definition, ['sset_notice_uk' => 'Привіт', 'sset_notice_en' => 'Hello']);
$check('Привіт', evo()->getConfig('sset_notice'), 'legacy key tracks default');
$fakeLang->languages = ['uk'];
$store->syncSchema($definition);
$check('Hello', evo()->getConfig('sset_notice_en'), 'removed language retained');
$fakeLang->languages = ['uk', 'en', 'pl'];
$store->saveValues($definition, ['sset_notice_uk' => 'Новий']);
$check('Hello', evo()->getConfig('sset_notice_en'), 'unsubmitted language not overwritten');
$store->syncSchema($definition);
$check(['sset_notice_uk', 'sset_notice_en', 'sset_notice_pl'], array_keys($store->values($definition)), 'added language automatic');
$check('Hello', $store->values($definition)['sset_notice_en'], 're-added language restored');
$fakeLang->languages = ['uk', 'en', 'pl', ' en ', 'invalid/locale'];
$check(['uk', 'en', 'pl'], $languages->locales(), 'deduplicate and validate locale suffixes');
$single = ['basic' => ['fields' => [array_replace($field, ['multilingual' => false])]]];
$store->syncSchema($single);
$check('Hello', evo()->getConfig('sset_notice_en'), 'disabled multilingual retained');
$check(true, $schema->normalize($definition)['basic']['fields'][0]['multilingual'], 'schema flag round trip');
$number = ['basic' => ['fields' => [array_replace($field, ['type' => 'number'])]]];
$check(false, isset($schema->normalize($number)['basic']['fields'][0]['multilingual']), 'unsupported type rejected');
foreach (['text', 'textarea', 'textareamini', 'richtext'] as $type) {
    $check(true, $catalog->supportsMultilingual($type), 'supported text type: ' . $type);
}
$container->offsetUnset('sLang');
$store->syncSchema($definition);
$check('Hello', evo()->getConfig('sset_notice_en'), 'optional integration removal retains translations');
$check(true, $schema->normalize($definition)['basic']['fields'][0]['multilingual'], 'optional integration removal retains schema flag');
$container->instance('sLang', $fakeLang);
$conflict = ['basic' => ['fields' => [$field, ['name' => 'notice_uk', 'type' => 'text']]]];
try { $store->fieldDefinitions($conflict); throw new RuntimeException('Collision accepted'); }
catch (RuntimeException $error) { $check('sSettings::global.multilingual_key_conflict', $error->getMessage(), 'collision rejected'); }
$conflict['basic']['fields'][1]['name'] = 'notice_de';
try { $store->fieldDefinitions($conflict); throw new RuntimeException('Future collision accepted'); }
catch (RuntimeException $error) { $check('sSettings::global.multilingual_key_conflict', $error->getMessage(), 'future language collision rejected'); }
$store->syncSchema(['basic' => ['fields' => []]]);
$check(0, EvolutionCMS\Models\SystemSetting::query()->count(), 'removed field cleans owned values');
echo "Multilingual checks passed: {$checks}\n";
