# Entwicklerhandbuch

## Architektur

sSettings ist ein evo-ui + Livewire Manager-Modul.

Kernbestandteile:

- `Seiger\sSettings\sSettingsServiceProvider` registriert Übersetzungen,
  Plugin, Manager-Modul, Routen, Views, veröffentlichte Dateien und Livewire
  Komponenten.
- `Seiger\sSettings\Livewire\ModulePanel` wechselt zwischen Einstellungen und
  Konfiguration.
- `Seiger\sSettings\Livewire\SettingsPanel` rendert den kompakten Werteeditor.
- `Seiger\sSettings\Livewire\ConfigurePanel` rendert den kompakten Schema
  Builder.
- `Seiger\sSettings\Support\SettingsSchemaRepository` liest, normalisiert und
  schreibt das Schema sicher.
- `Seiger\sSettings\Support\SystemSettingsStore` liest und schreibt `sset_*`
  Systemeinstellungen.
- `Seiger\sSettings\Support\FieldCatalog` verwaltet die Feldtypen.

## Installation

Im Evolution CMS Verzeichnis `core`:

```console
php artisan package:installrequire seiger/ssettings "*"
php artisan vendor:publish --provider="Seiger\\sSettings\\sSettingsServiceProvider"
```

In lokalen Extras-Umgebungen kann das Paket auch als Path Repository mit
Symlink in den Demo-Core eingebunden werden.

## Konfigurationsdateien

Paketstandard:

```text
config/sSettingsSettings.php
```

Schreibbares Projektschema:

```text
core/custom/config/seiger/settings/sSettings.php
```

Wenn das Projektschema nicht existiert, verwendet sSettings das Standardschema
des Pakets.

## Schemaformat

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

Bekannte übersetzte Labels werden beim Speichern wieder zu Übersetzungsschlüsseln
normalisiert. Eigene Labels ohne Übersetzung werden als Klartext gespeichert.

## Sicheres Schreiben des Schemas

`SettingsSchemaRepository::save()` schreibt zuerst in eine temporäre Datei,
führt `php -l` aus, wenn `exec()` verfügbar ist, und ersetzt danach die
Zieldatei. So wird eine teilweise geschriebene PHP-Konfiguration vermieden.

Nach dem Speichern synchronisiert `SystemSettingsStore::syncSchema()` die
Systemeinstellungen: neue `sset_*` werden erstellt, alte nicht mehr im Schema
vorhandene `sset_*` werden entfernt.

## Feldtypen

`FieldCatalog` unterstützt:

- `text`
- `textarea`
- `textareamini`
- `image`
- `file`
- `checkbox`
- `divider`

Alle Typen außer `divider` speichern Werte. Checkbox wird zu `1` oder `0`
normalisiert, andere Werte zu getrimmten Strings. Image und File speichern den
ausgewählten Pfad als String.

## Routen und Manager-Modul

Routen werden nur im Manager Mode geladen:

```text
GET  ssettings
GET  ssettings/configure
```

Das Manager-Modul ist als hidden registriert und wird über das Tools-Menü
geöffnet. Das Menüicon nutzt denselben Blade Tabler Icon Pfad wie die
Modulregistrierung.

## evo-ui Verträge

Die Oberfläche soll kompakt bleiben:

- Werte sind nach Schema-Tabs gruppiert.
- In der Konfiguration ist Tab hinzufügen links und Speichern rechts.
- Tabs und Felder werden über Drag Handles sortiert.
- Feldeinstellungen öffnen sich in einem kompakten Modal.
- Type Chips und System Key Chips sind klein und theme-aware.

sSettings-spezifisches Verhalten bleibt in sSettings. Nur wiederverwendbare
Manager-Primitives gehören nach evo-ui.

## Tests und Smoke Checks

```console
find src plugins module tests config lang -name "*.php" -print0 | xargs -0 -n1 php -l
```

```console
SSETTINGS_DEMO_CORE=/path/to/demo/core php tests/run.php
```

Nützliche Demo-Kommandos:

```console
php artisan package:discover
php artisan view:clear
php artisan route:list --path=ssettings
```
