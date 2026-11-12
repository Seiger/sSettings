---
sidebar_position: 4
---

# Przewodnik frontendowy

## Odczyt wartości

sSettings zapisuje pola jako ustawienia systemowe Evolution z prefiksem
`sset_`. Odczytuj je przez `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

Tekst w HTML wyświetlaj przez `{{ $value }}` lub `@evoConfig('sset_phone')`.
Oba zapisy kodują znaki specjalne HTML.

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
@php($facebook = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_facebook', '')))

@if($facebook !== null)
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Skrypt śledzenia:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Ten zapis uruchamia JavaScript w przeglądarce odwiedzającego. Używaj go dla
`sset_google_analytics`, jeśli wszyscy użytkownicy z uprawnieniem `settings`
mogą publikować skrypty na stronie. Zwykły tekst wyświetlaj przez `{{ }}`.

## Tagi Evolution

Tagi w szablonach i fragmentach Evolution wstawiają wartości bez kodowania
HTML. Koduj tekst i sprawdzaj adresy URL odpowiednio do miejsca użycia.

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Obrazy i pliki

`SettingUrl::resolve()` przyjmuje bezwzględne adresy HTTP(S). Przekaż
`EVO_SITE_URL` jako drugi argument, aby zamienić ścieżkę względem witryny
na adres URL do użycia w `src` lub `href`.

```blade
@php($logo = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_logo', ''), EVO_SITE_URL))

@if($logo !== null)
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
