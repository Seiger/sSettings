# Frontend Guide

## Reading Values

sSettings stores value fields as Evolution system settings with the `sset_`
prefix. Read them with `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

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
@php($facebook = evo()->getConfig('sset_facebook', ''))

@if($facebook !== '')
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Tracking script:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Render textarea values as raw HTML only when trusted manager users control the
value.

## Evolution Tags

In Evolution templates and chunks you can use the system setting tag:

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Images And Files

Image and file fields store paths. Use the value as the `src`, `href`, or as
input to your own asset helper.

```blade
@php($logo = evo()->getConfig('sset_logo', ''))

@if($logo !== '')
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
