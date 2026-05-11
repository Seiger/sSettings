# Przewodnik frontendowy

## Odczyt wartości

sSettings zapisuje pola jako ustawienia systemowe Evolution z prefiksem
`sset_`. Odczytuj je przez `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

## Przykłady Blade

Link telefonu:

```blade
@php($phone = evo()->getConfig('sset_phone', ''))

@if($phone !== '')
    <a href="tel:{{ Str::remove([' ', '-', '(', ')'], $phone) }}">
        {{ $phone }}
    </a>
@endif
```

Link społecznościowy:

```blade
@php($facebook = evo()->getConfig('sset_facebook', ''))

@if($facebook !== '')
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Skrypt śledzenia:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Renderuj textarea jako raw HTML tylko wtedy, gdy wartość kontrolują zaufani
managerowie.

## Tagi Evolution

W templates i chunks można używać tagów ustawień systemowych:

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Obrazy i pliki

Pola Image i File zapisują ścieżki. Użyj wartości jako `src`, `href` albo jako
wejścia do własnego helpera assetów.

```blade
@php($logo = evo()->getConfig('sset_logo', ''))

@if($logo !== '')
    <img src="{{ $logo }}" alt="">
@endif
```

## Checkbox

Checkbox jest zapisywany jako string:

```php
$enabled = evo()->getConfig('sset_show_contacts', '0') === '1';
```

## Cache

sSettings czyści cache Evolution po zapisaniu wartości lub synchronizacji
schematu. Jeśli frontend ma własny cache, wyczyść go osobno.
