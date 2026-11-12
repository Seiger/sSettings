# Frontend Guide

## Reading Values

sSettings stores value fields as Evolution system settings with the `sset_`
prefix. Read them with `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

Use `{{ $value }}` or `@evoConfig('sset_phone')` for text in HTML.
Both escape the value.

## Blade Examples

Phone link:

```blade
@php($phone = evo()->getConfig('sset_phone', ''))

@if($phone !== '')
    <a href="tel:{{ Str::remove([' ', '-', '(', ')'], $phone) }}">
        {{ $phone }}
    </a>
@endif
```

Social link:

```blade
@php($facebook = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_facebook', '')))

@if($facebook !== null)
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Tracking script:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

This output runs JavaScript in visitors' browsers. Use it for
`sset_google_analytics` only when everyone with the `settings` permission can
publish scripts on the site. Use `{{ }}` for ordinary text.

## Evolution Tags

Evolution tags in templates and chunks insert values without HTML escaping.
Escape text and validate URLs for the destination where you use them.

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Images And Files

`SettingUrl::resolve()` accepts absolute HTTP(S) URLs. Pass `EVO_SITE_URL` as
the second argument to resolve site-relative paths for `src` or `href`.

```blade
@php($logo = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_logo', ''), EVO_SITE_URL))

@if($logo !== null)
    <img src="{{ $logo }}" alt="">
@endif
```

## Checkboxes

Checkbox values are stored as strings:

```php
$enabled = evo()->getConfig('sset_show_contacts', '0') === '1';
```

## Cache Notes

sSettings clears the Evolution cache after saving values or syncing schema. If
custom frontend code has its own cache layer, clear that cache separately.
