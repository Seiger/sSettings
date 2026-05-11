# Frontend-Handbuch

## Werte lesen

sSettings speichert Wertfelder als Evolution Systemeinstellungen mit dem Präfix
`sset_`. Lesen Sie sie mit `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

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
@php($facebook = evo()->getConfig('sset_facebook', ''))

@if($facebook !== '')
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Tracking-Skript:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Geben Sie Textarea-Werte nur dann als raw HTML aus, wenn vertrauenswürdige
Manager diese Werte kontrollieren.

## Evolution Tags

In Templates und Chunks können System Setting Tags verwendet werden:

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Bilder und Dateien

Image- und File-Felder speichern Pfade. Verwenden Sie den Wert als `src`,
`href` oder als Eingabe für eigene Asset-Helfer.

```blade
@php($logo = evo()->getConfig('sset_logo', ''))

@if($logo !== '')
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
