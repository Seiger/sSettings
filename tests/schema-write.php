<?php

// Isolated file-system regression: no CMS bootstrap, site database or real project config.
if (PHP_SAPI !== 'cli') { exit(1); }

function evo() {
    /**
     * Supply the locale required by schema normalization without bootstrapping the CMS.
     *
     * @since 2.1.0
     */
    return new class {
        /**
         * Keep translation lookup deterministic for this isolated file-system regression.
         *
         * @return string The locale used for untranslated test field labels.
         * @since 2.1.0
         */
        public function getLocale() { return 'en'; }
    };
}
function __($key) { return $key; }

$core = getenv('SSETTINGS_DEMO_CORE');
if (!$core || !is_file($core . '/vendor/autoload.php')) {
    throw new RuntimeException('Set SSETTINGS_DEMO_CORE');
}
require $core . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/Support/FieldCatalog.php';
require_once dirname(__DIR__) . '/src/Support/SettingsSchemaRepository.php';

// Reproduce an FPM/non-CLI binary, or a host with no usable CLI binary at all.
if (($binary = getenv('SSETTINGS_NON_CLI_BINARY')) !== false && $binary !== '') {
    define('Seiger\\sSettings\\Support\\PHP_BINARY', $binary);
}

$temporaryCore = sys_get_temp_dir().'/ssettings-schema-'.bin2hex(random_bytes(8)).' with spaces';
define('EVO_CORE_PATH', $temporaryCore.'/');
$repository = new Seiger\sSettings\Support\SettingsSchemaRepository(new Seiger\sSettings\Support\FieldCatalog());
$path = $repository->path();
$files = new Illuminate\Filesystem\Filesystem();
$files->makeDirectory(dirname($path), 0755, true);
$checks = 0;
$exitCode = 0;
$check = function (bool $passed, string $message) use (&$checks) {
    if (!$passed) { throw new RuntimeException($message); }
    $checks++;
};

try {
    $marker = $temporaryCore.'/must-not-execute';
    $description = "Quotes: ' \"; Unicode: Українська / Русский; newline:\nsecond line";
    $schema = ['basicTab' => ['label' => 'General', 'fields' => [
        ['name' => 'notice', 'label' => 'Notice', 'description' => $description, 'type' => 'textareamini', 'multilingual' => true],
        ['name' => 'literal', 'label' => "'); file_put_contents(".var_export($marker, true).", 'bad'); //", 'type' => 'text'],
    ]]];

    $original = "<?php return ['previous' => []];\n";
    file_put_contents($path, $original);
    $saved = $repository->save($schema);
    $written = require $path;
    $check($written === $saved, 'Persist the normalized schema as valid PHP');
    $check($written['basicTab']['fields'][0]['multilingual'] === true, 'Persist the multilingual flag');
    $check($written['basicTab']['fields'][0]['description'] === $description, 'Preserve quotes, Unicode and multiline text');
    $check(!is_file($marker), 'Field text must not execute as PHP');
    $check(!is_file($path.'.tmp'), 'A successful replacement leaves no temporary file');
    $check($repository->read() === $saved, 'Read back the newly saved schema');

    $schema['basicTab']['fields'][0]['label'] = 'Changed notice';
    $repository->save($schema);
    $check((require $path)['basicTab']['fields'][0]['label'] === 'Changed notice', 'Repeated saves replace the previous schema');

    unlink($path);
    $repository->save($schema);
    $check(is_file($path), 'Create a missing config in a writable parent directory');

    // Force a real rename failure without changing permissions or touching a site config.
    unlink($path);
    mkdir($path);
    file_put_contents($path.'/existing', 'preserve');
    set_error_handler(static fn () => true, E_WARNING);
    try {
        $repository->save($schema);
        throw new RuntimeException('A failed replacement was reported as successful');
    } catch (RuntimeException $exception) {
        $check($exception->getMessage() === 'Cannot replace settings config.', 'Report a failed atomic replacement');
    } finally {
        restore_error_handler();
    }
    $check(file_get_contents($path.'/existing') === 'preserve', 'A failed replacement retains the existing destination');
    $check(!is_file($path.'.tmp'), 'A failed replacement removes its temporary file');

    echo "Schema write checks passed: {$checks}\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'Schema write regression failed: '.$exception->getMessage()."\n");
    $exitCode = 1;
} finally {
    $files->deleteDirectory($temporaryCore);
}
exit($exitCode);
