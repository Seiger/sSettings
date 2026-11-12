<?php

declare(strict_types=1);

use Seiger\sSettings\Support\SettingUrl;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require_once dirname(__DIR__) . '/src/Support/SettingUrl.php';

$site = 'https://example.test/site/';
$cases = [
    ['http://example.test/contact', null, 'http://example.test/contact'],
    [' HTTPS://cdn.example.test/logo.png?size=2&fit=contain ', $site, 'HTTPS://cdn.example.test/logo.png?size=2&fit=contain'],
    ['assets/logo.png', $site, $site . 'assets/logo.png'],
    ["/assets/Кав'ярня logo.png", $site, $site . "assets/Кав'ярня logo.png"],
    ['assets/logo.png', null, null],
    ['//cdn.example.test/logo.png', $site, null],
    ['ftp://example.test/logo.png', $site, null],
    ['mailto:office@example.test', null, null],
    ['https:logo.png', $site, null],
    ['https://example test/logo.png', $site, null],
    ["https://example.test/logo\n.png", $site, null],
    ['assets\\logo.png', $site, null],
    ['?name=logo', $site, null],
    [' ', $site, null],
    [['assets/logo.png'], $site, null],
];

foreach ($cases as [$value, $base, $expected]) {
    $actual = SettingUrl::resolve($value, $base);
    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected result for ' . var_export($value, true) . ': ' . var_export($actual, true));
    }
}

echo 'Setting URL checks passed: ' . count($cases) . PHP_EOL;
