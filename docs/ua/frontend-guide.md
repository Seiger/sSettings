# Фронтенд-посібник

## Читання значень

sSettings зберігає поля як системні налаштування Evolution із префіксом
`sset_`. Читати їх можна через `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

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
@php($facebook = evo()->getConfig('sset_facebook', ''))

@if($facebook !== '')
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Скрипт відстеження:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Виводьте textarea як raw HTML тільки тоді, коли значення контролюють довірені
менеджери.

## Evolution теги

У templates і chunks можна використовувати системний тег:

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Зображення і файли

Image і File поля зберігають шлях. Використовуйте значення як `src`, `href` або
передавайте у власний asset helper.

```blade
@php($logo = evo()->getConfig('sset_logo', ''))

@if($logo !== '')
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
