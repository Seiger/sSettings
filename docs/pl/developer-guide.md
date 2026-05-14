# Przewodnik developera

## Architektura

sSettings jest modułem managera opartym o evo-ui i Livewire.

Główne elementy:

- `Seiger\sSettings\sSettingsServiceProvider` rejestruje tłumaczenia, plugin,
  manager module, routes, views, pliki publikowane i komponenty Livewire.
- `Seiger\sSettings\Livewire\ModulePanel` przełącza Ustawienia i Konfigurację.
- `Seiger\sSettings\Livewire\SettingsPanel` renderuje kompaktowy edytor
  wartości.
- `Seiger\sSettings\Livewire\ConfigurePanel` renderuje kompaktowy builder
  schematu.
- `Seiger\sSettings\Support\SettingsSchemaRepository` czyta, normalizuje i
  bezpiecznie zapisuje schemat.
- `Seiger\sSettings\Support\SystemSettingsStore` czyta i zapisuje ustawienia
  systemowe `sset_*`.
- `Seiger\sSettings\Support\FieldCatalog` zawiera katalog typów pól.

## Instalacja

W katalogu `core` Evolution CMS:

```console
php artisan package:installrequire seiger/ssettings "*"
php artisan vendor:publish --provider="Seiger\\sSettings\\sSettingsServiceProvider"
```

W lokalnym środowisku Extras pakiet może być podłączony jako path repository z
symlinkiem do demo core.

## Pliki konfiguracji

Domyślny schemat pakietu:

```text
config/sSettingsSettings.php
```

Zapisywalny schemat projektu:

```text
core/custom/config/seiger/settings/sSettings.php
```

Jeśli schemat projektu nie istnieje, sSettings używa schematu domyślnego.

## Kształt schematu

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

Znane przetłumaczone etykiety są przy zapisie normalizowane z powrotem do
kluczy tłumaczeń. Własne etykiety bez tłumaczenia są zapisywane jako tekst.

## Bezpieczny zapis schematu

`SettingsSchemaRepository::save()` zapisuje dane do pliku tymczasowego,
uruchamia `php -l`, jeśli dostępne jest `exec()`, a potem zamienia plik
docelowy. Chroni to przed częściowo zapisanym plikiem PHP.

Po zapisie schemat jest synchronizowany przez
`SystemSettingsStore::syncSchema()`: nowe `sset_*` są tworzone, a stare
`sset_*`, których nie ma już w schemacie, są usuwane.

## Typy pól

`FieldCatalog` obsługuje:

- `text`
- `textarea`
- `textareamini`
- `image`
- `file`
- `checkbox`
- `divider`

Wszystkie typy poza `divider` zapisują wartości. Checkbox jest normalizowany do
`1` albo `0`, pozostałe wartości do przyciętego stringa. Image i File zapisują
wybraną ścieżkę jako string.

## Routes i manager module

Routes są ładowane tylko w manager mode:

```text
GET  ssettings
GET  ssettings/configure
```

Manager module jest zarejestrowany jako hidden i otwierany z Tools menu. Ikona
menu używa tej samej ścieżki Blade Tabler icon co rejestracja modułu.

## Kontrakty evo-ui

Interfejs powinien pozostać kompaktowy:

- wartości są grupowane według zakładek schematu;
- w Konfiguracji dodawanie zakładki jest po lewej, zapis po prawej;
- Konfiguracja jest staged editor: modal pola stosuje lokalny draft, a glowny
  przycisk Save jest jedyna akcja persistence dla schematu;
- primary action w modalu pola uzywa `evo::global.action_apply`, jest wylaczona
  gdy draft sie nie zmienil i po apply oznacza Configure jako dirty;
- zakładki i pola zmieniają kolejność przez drag handles;
- opcje w modalach pol uzywaja EvoUI `data-evo-dnd-option-list` i
  `data-evo-dnd-option-row`; EvoUI posiada pointer reorder path, a option row,
  handle i inputy pozostaja `draggable="false"`, zeby uniknac race miedzy
  modalem i native DnD; Alpine slucha przez `evo-ui:dnd-option-changed`, bez
  dotted `x-on` event names dla tej sciezki;
- DnD zakladek i pol Configure slucha EvoUI `evo-ui:form-dirty`, zeby reorder
  od razu wlaczal glowny przycisk Save przed zakonczeniem redraw Livewire;
- po udanym Configure Save event `ssettings-schema-saved` odswieza gorne
  settings tabs z normalizowanego schematu bez reload strony;
- ustawienia pola otwierają się w kompaktowym modalu;
- type chips i system key chips są małe i zgodne z motywem.

Logika specyficzna dla sSettings powinna zostać w sSettings. Do evo-ui należy
wynosić tylko wielokrotnego użytku manager primitives.

## Testy i smoke checks

```console
find src plugins module tests config lang -name "*.php" -print0 | xargs -0 -n1 php -l
```

```console
SSETTINGS_DEMO_CORE=/path/to/demo/core php tests/run.php
```

Przydatne komendy demo:

```console
php artisan package:discover
php artisan view:clear
php artisan route:list --path=ssettings
```
