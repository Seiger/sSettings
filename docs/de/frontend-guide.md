# Frontend-Handbuch

## Werte lesen

sSettings speichert Wertfelder als Evolution Systemeinstellungen mit dem Präfix
`sset_`. Lesen Sie sie mit `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

Geben Sie Text in HTML mit `{{ $value }}` oder `@evoConfig('sset_phone')` aus.
Beide Varianten maskieren HTML-Sonderzeichen.

## Blade Beispiele

Telefonlink:

```blade
@php($phone = evo()->getConfig('sset_phone', ''))

@if($phone !== '')
    <a href="tel:{{ Str::remove([' ', '-', '(', ')'], $phone) }}">
        {{ $phone }}
    </a>
@endif
```

Social Link:

```blade
@php($facebook = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_facebook', '')))

@if($facebook !== null)
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Tracking-Skript:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Diese Ausgabe führt JavaScript im Browser der Besucher aus. Verwenden Sie
sie für `sset_google_analytics` nur dann, wenn alle Benutzer mit der Berechtigung
`settings` Skripte auf der Website veröffentlichen dürfen. Geben Sie normalen
Text mit `{{ }}` aus.

## Evolution Tags

Evolution-Tags in Templates und Chunks fügen Werte ohne HTML-Maskierung ein.
Maskieren Sie Text und prüfen Sie URLs passend zum Ausgabekontext.

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Bilder und Dateien

`SettingUrl::resolve()` akzeptiert absolute HTTP(S)-URLs. Übergeben Sie
`EVO_SITE_URL` als zweites Argument, um relative Website-Pfade für `src` oder
`href` aufzulösen.

```blade
@php($logo = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_logo', ''), EVO_SITE_URL))

@if($logo !== null)
    <img src="{{ $logo }}" alt="">
@endif
```

## Checkboxen

Checkboxen werden als Strings gespeichert:

```php
$enabled = evo()->getConfig('sset_show_contacts', '0') === '1';
```

## Cache

sSettings leert den Evolution Cache nach dem Speichern von Werten oder dem
Synchronisieren des Schemas. Wenn das Frontend einen eigenen Cache hat, leeren
Sie ihn separat.
