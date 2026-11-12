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
    public array $cacheClears = [];

    public function getConfig($key, $default = '') {
        return EvolutionCMS\Models\SystemSetting::query()->find($key)?->setting_value ?? $default;
    }
    public function setConfig($key, $value) {}
    public function clearCache($type) { $this->cacheClears[] = $type; }
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
if (!class_exists(\Seiger\sLang\sLang::class)) {
    class_alias($fakeLang::class, \Seiger\sLang\sLang::class);
}
$container->instance('sLang', $fakeLang);
$check(['uk', 'en'], $languages->locales(), 'configured languages');
$check(['notice_uk', 'notice_en'], array_column($store->fieldDefinitions($definition), 'name'), 'localized keys');
EvolutionCMS\Models\SystemSetting::query()->create(['setting_name' => 'sset_notice', 'setting_value' => 'Initial']);
$store->syncSchema($definition);
$check('Initial', $store->values($definition)['sset_notice_uk'], 'seed default language');
$check('', $store->values($definition)['sset_notice_en'], 'other language starts empty');
evo()->cacheClears = [];
$store->saveValues($definition, ['sset_notice_uk' => 'Привіт', 'sset_notice_en' => 'Hello']);
$check('Привіт', evo()->getConfig('sset_notice'), 'legacy key tracks default');
$check(['full'], evo()->cacheClears, 'multiple changed values clear the full cache once');

evo()->cacheClears = [];
$store->saveValues($definition, ['sset_notice_uk' => ' Привіт ', 'sset_notice_en' => 'Hello']);
$check([], evo()->cacheClears, 'values unchanged after normalization preserve the cache');
$store->saveValues($definition, []);
$store->saveValues($definition, ['sset_unknown' => 'ignored']);
$check([], evo()->cacheClears, 'empty or ignored submissions preserve the cache');

EvolutionCMS\Models\SystemSetting::query()->where('setting_name', 'sset_notice')->update(['setting_value' => 'Stale']);
$store->saveValues($definition, ['sset_notice_uk' => 'Привіт']);
$check('Привіт', evo()->getConfig('sset_notice'), 'an unchanged locale repairs its stale base value');
$check(['full'], evo()->cacheClears, 'a base value change alone clears the full cache');

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
$check('Hello', evo()->getConfig('sset_notice_en'), 'an empty sync preserves all settings');

$temporaryCore = sys_get_temp_dir() . '/ssettings-pruning-' . bin2hex(random_bytes(8));
if (defined('EVO_CORE_PATH')) {
    throw new RuntimeException('The pruning fixture requires an isolated core path');
}
define('EVO_CORE_PATH', $temporaryCore . '/');
$path = $schema->path();
mkdir(dirname($path), 0700, true);
try {
    foreach (['sset_notice_fr', 'sset_external', 'ssetXoutside'] as $key) {
        EvolutionCMS\Models\SystemSetting::query()->create(['setting_name' => $key, 'setting_value' => 'External']);
    }

    $keep = ['name' => 'keep', 'type' => 'text'];
    $external = ['name' => 'external', 'type' => 'text'];
    $complete = ['basic' => ['fields' => [$field, $keep, $external]]];
    $remaining = ['basic' => ['fields' => [$keep, $external]]];
    $save = static fn (array $tabs, array $retired = []) => $store->saveSchema($schema, $tabs, $schema->readSnapshot()['revision'], $retired);
    $save($complete);

    $store->syncSchema($remaining);
    $check('Hello', evo()->getConfig('sset_notice_en'), 'a partial sync without explicit removal preserves settings');

    $save(['basic' => ['fields' => []]], ['notice' => true, 'keep' => true, 'external' => true]);
    $check('Hello', evo()->getConfig('sset_notice_en'), 'explicit removal of every field preserves all values');
    $save($complete);

    $save(['basic' => ['fields' => [$keep]]], ['notice' => true]);
    $check('Hello', evo()->getConfig('sset_notice_en'), 'a second unexplained omission prevents all pruning');
    $save($complete);

    $revision = $schema->readSnapshot()['revision'];
    $schema->save(['basic' => ['label' => 'Edited elsewhere', 'fields' => [$field, $keep, $external]]]);
    $current = $schema->readSnapshot();
    $outdated = ['basic' => ['fields' => [$keep, $external, ['name' => 'outdated', 'type' => 'text']]]];
    evo()->cacheClears = [];
    foreach ([$revision, null] as $staleRevision) {
        $message = null;
        try {
            $store->saveSchema($schema, $outdated, $staleRevision, ['notice' => true]);
        } catch (RuntimeException $error) {
            $message = $error->getMessage();
        }
        $check('sSettings::global.schema_changed', $message, 'an outdated editor must reload before saving');
        $check($current, $schema->readSnapshot(), 'an outdated editor preserves the latest schema');
    }
    $check('Hello', evo()->getConfig('sset_notice_en'), 'a stale editor cannot prune');
    $check(null, evo()->getConfig('sset_outdated', null), 'a stale editor cannot seed new keys');
    $check([], evo()->cacheClears, 'a stale editor does not clear the cache');
    $save($complete);

    $schema->save(['basic' => ['fields' => [$field, $keep]]]);
    $save(['basic' => ['fields' => [$keep]]], ['notice' => true]);
    $check('Hello', evo()->getConfig('sset_notice_en'), 'an incomplete persisted schema prevents all pruning');
    $save($complete);

    foreach (['<?php return [', '<?php return [];', '<?php return ["basic" => ["fields" => [["type" => "text"]]]];'] as $invalidSchema) {
        file_put_contents($path, $invalidSchema);
        $check(null, $schema->readSnapshot()['revision'], 'an unusable schema has no revision for pruning');
        $save($remaining, ['notice' => true]);
        $check('Hello', evo()->getConfig('sset_notice_en'), 'a fallback schema cannot prune');
        $save($complete);
    }

    $fakeLang->languages = ['uk'];
    $save(['basic' => ['fields' => [$keep]]], ['notice' => true, 'external' => true]);
    $check(null, evo()->getConfig('sset_notice_uk', null), 'explicit removal prunes a registered active locale');
    $check(null, evo()->getConfig('sset_notice_en', null), 'explicit removal prunes a registered archived locale');
    $check('Новий', evo()->getConfig('sset_notice'), 'an existing base key is never claimed retroactively');
    foreach (['sset_notice_fr', 'sset_external', 'ssetXoutside'] as $key) {
        $check('External', evo()->getConfig($key), 'preserve key created outside sSettings: ' . $key);
    }
    $check('', evo()->getConfig('sset_keep', null), 'preserve the remaining field');

    $written = ['name' => 'written', 'type' => 'text'];
    $withWritten = ['basic' => ['fields' => [$keep, $written]]];
    evo()->cacheClears = [];
    $store->saveValues($withWritten, ['sset_written' => 'Created by the values panel']);
    $check(['full'], evo()->cacheClears, 'creating a setting clears the full cache');
    $save($withWritten);
    $save(['basic' => ['fields' => [$keep]]], ['written' => true]);
    $check(null, evo()->getConfig('sset_written', null), 'explicit removal prunes a key created by saving values');
} finally {
    if (is_file($path)) {
        unlink($path);
    }
    for ($directory = dirname($path); $directory !== dirname($temporaryCore); $directory = dirname($directory)) {
        rmdir($directory);
    }
}
echo "Multilingual checks passed: {$checks}\n";
