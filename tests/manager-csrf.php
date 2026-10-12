<?php
// Isolated checks with the real manager middleware; no CMS bootstrap or site database.
if (PHP_SAPI !== 'cli') { exit(1); }
function csrf_token() { return $_SESSION['_token']; }
function __($key) { return $key === 'sSettings::global.module_icon' ? 'fas fa-cog' : $key; }
function route($name) { return 'https://example.test/ssettings/'; }
function evo() { return new class { public function getConfig($key) { return ''; } }; }

$core = getenv('SSETTINGS_DEMO_CORE');
if (!$core || !is_file($core . '/vendor/autoload.php')) { throw new RuntimeException('Set SSETTINGS_DEMO_CORE'); }
require $core . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/sSettings.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

$app = new Container();
Container::setInstance($app);
Facade::setFacadeApplication($app);
$events = new Dispatcher($app);
$app->instance('events', $events);
require dirname(__DIR__) . '/plugins/sSettingsPlugin.php';
$checks = 0;
$check = static function ($passed) use (&$checks) {
    if (!$passed) { throw new RuntimeException('CSRF check failed: ' . ($checks + 1)); }
    $checks++;
};
$middleware = new EvolutionCMS\Middleware\VerifyCsrfToken();
$matches = new ReflectionMethod($middleware, 'tokensMatch');
$required = new ReflectionMethod($middleware, 'requiresVerification');
$_SESSION = ['mgrValidated' => true, '_token' => 'first+token/value'];
$menuUrl = static function () use ($events) {
    $result = $events->dispatch('evolution.OnManagerMenuPrerender', [['menu' => []]]);
    return unserialize($result[0], ['allowed_classes' => false])['ssettings'][3];
};
$url = $menuUrl();
$request = Request::create('https://example.test/' . $url);
$check($required->invoke($middleware, $request));
$check($matches->invoke($middleware, $request));
$check($middleware->handle($request, static fn () => 'accepted') === 'accepted');
$check(str_contains($url, rawurlencode($_SESSION['_token'])));
$check($matches->invoke($middleware, Request::create((new Seiger\sSettings\sSettings())->route('sSettings.index'))));
$_SESSION['_token'] = 'replacement-token';
$check(!$matches->invoke($middleware, $request));
$check($matches->invoke($middleware, Request::create('https://example.test/' . $menuUrl())));
$check(!$matches->invoke($middleware, Request::create('https://example.test/index.php?a=112&id=settings')));
$_SESSION['mgrValidated'] = false;
$check(!str_contains((new Seiger\sSettings\sSettings())->route('sSettings.index'), '_token'));
echo "$checks manager CSRF checks passed\n";
