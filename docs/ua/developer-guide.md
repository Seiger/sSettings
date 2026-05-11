# Посібник розробника

## Архітектура

sSettings - це evo-ui + Livewire модуль менеджера.

Основні частини:

- `Seiger\sSettings\sSettingsServiceProvider` реєструє переклади, plugin,
  manager module, routes, views, publish-файли і Livewire компоненти.
- `Seiger\sSettings\Livewire\ModulePanel` перемикає Налаштування і
  Конфігурацію.
- `Seiger\sSettings\Livewire\SettingsPanel` рендерить компактний редактор
  значень.
- `Seiger\sSettings\Livewire\ConfigurePanel` рендерить компактний schema
  builder.
- `Seiger\sSettings\Support\SettingsSchemaRepository` читає, нормалізує і
  безпечно записує схему.
- `Seiger\sSettings\Support\SystemSettingsStore` читає і записує системні
  налаштування `sset_*`.
- `Seiger\sSettings\Support\FieldCatalog` містить каталог типів полів.

## Встановлення

У директорії `core` Evolution CMS:

```console
php artisan package:installrequire seiger/ssettings "*"
php artisan vendor:publish --provider="Seiger\\sSettings\\sSettingsServiceProvider"
```

У локальному Extras-середовищі пакет можна підключати як path repository із
symlink у demo core.

## Файли конфігурації

Пакетна схема за замовчуванням:

```text
config/sSettingsSettings.php
```

Записувана схема проєкту:

```text
core/custom/config/seiger/settings/sSettings.php
```

Якщо проєктної схеми ще немає, sSettings читає пакетну схему.

## Форма схеми

```php
return [
    'basicTab' => [
        'label' => 'sSettings::global.basicTab',
        'fields' => [
            [
                'name' => 'email',
                'label' => 'sSettings::global.email',
                'description' => 'sSettings::global.email_description',
                'type' => 'text',
            ],
        ],
    ],
];
```

Відомі перекладені назви при збереженні нормалізуються назад у translation
keys. Кастомні назви без перекладу зберігаються як звичайний текст.

## Безпечний запис схеми

`SettingsSchemaRepository::save()` пише у тимчасовий файл, запускає `php -l`,
якщо доступний `exec()`, і тільки після цього замінює цільовий файл. Це захищає
від напівзаписаного PHP-конфіга.

Після збереження схема синхронізується через
`SystemSettingsStore::syncSchema()`: нові `sset_*` створюються, а старі
`sset_*`, яких уже немає в схемі, видаляються.

## Типи полів

`FieldCatalog` підтримує:

- `text`
- `textarea`
- `textareamini`
- `richtext`
- `dropdown`
- `listbox`
- `listboxmultiple`
- `radio`
- `image`
- `file`
- `url`
- `email`
- `number`
- `date`
- `checkbox`
- `checkboxgroup`
- `divider`

Усі типи, крім `divider`, зберігають значення. Checkbox нормалізується у `1`
або `0`, checkbox group і multi-listbox зберігають кілька значень через `||`.
Опції для dropdown/listbox/radio/checkbox group зберігаються у форматі
`value==Label||value2==Label 2`. Image і File зберігають вибраний шлях як
рядок.

## Routes і manager module

Routes завантажуються тільки в manager mode:

```text
GET  ssettings
GET  ssettings/configure
```

Manager module зареєстрований як hidden і відкривається через Tools menu.
Іконка меню використовує той самий Blade Tabler icon шлях, що й module
registration.

## evo-ui контракти

Інтерфейс має залишатися компактним:

- значення згруповані за табами схеми;
- у Конфігурації додавання таба зліва, збереження справа;
- порядок табів і полів змінюється drag handles;
- налаштування поля відкриваються у компактному modal;
- type chips і system key chips маленькі та theme-aware.

Поведінка, специфічна для sSettings, має лишатися в sSettings. У evo-ui варто
виносити тільки повторно використовувані manager primitives.

## Тести і smoke checks

```console
find src plugins module tests config lang -name "*.php" -print0 | xargs -0 -n1 php -l
```

```console
SSETTINGS_DEMO_CORE=/path/to/demo/core php tests/run.php
```

Release gate перед tag/commit:

```console
composer validate --strict --no-check-publish
composer test
php tests/run.php
```

Корисні demo-команди:

```console
php artisan package:discover
php artisan view:clear
php artisan route:list --path=ssettings
```
