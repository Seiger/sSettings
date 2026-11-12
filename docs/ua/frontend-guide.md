# Фронтенд-посібник

## Читання значень

sSettings зберігає поля як системні налаштування Evolution із префіксом
`sset_`. Читати їх можна через `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

Для тексту в HTML використовуйте `{{ $value }}` або `@evoConfig('sset_phone')`.
Обидва варіанти екранують значення.

## Приклади Blade

Посилання телефону:

```blade
@php($phone = evo()->getConfig('sset_phone', ''))

@if($phone !== '')
    <a href="tel:{{ Str::remove([' ', '-', '(', ')'], $phone) }}">
        {{ $phone }}
    </a>
@endif
```

Соціальне посилання:

```blade
@php($facebook = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_facebook', '')))

@if($facebook !== null)
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Скрипт відстеження:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Цей вивід виконує JavaScript у браузері відвідувача. Використовуйте його для
`sset_google_analytics`, лише якщо всі, хто має право `settings`, можуть
публікувати скрипти на сайті. Для звичайного тексту використовуйте `{{ }}`.

## Evolution теги

Теги в шаблонах і чанках Evolution підставляють значення без HTML-екранування.
Екрануйте текст і перевіряйте URL відповідно до місця виводу.

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Зображення і файли

`SettingUrl::resolve()` приймає абсолютні HTTP(S) URL. З `EVO_SITE_URL` другим
аргументом він також перетворює шлях відносно сайту на URL для `src` або `href`.

```blade
@php($logo = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_logo', ''), EVO_SITE_URL))

@if($logo !== null)
    <img src="{{ $logo }}" alt="">
@endif
```

## Checkbox

Checkbox зберігається як рядок:

```php
$enabled = evo()->getConfig('sset_show_contacts', '0') === '1';
```

## Кеш

sSettings очищає кеш Evolution після збереження значень або синхронізації
схеми. Якщо фронтенд має власний кеш, очищайте його окремо.
