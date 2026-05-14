# Developer Guide

## Architecture

sSettings is an evo-ui + Livewire manager module.
Composer describes it as an "Evocms package for work with Advanced settings for your website."

Core pieces:

- `Seiger\sSettings\sSettingsServiceProvider` registers translations, plugin,
  manager module, routes, views, published files, and Livewire components.
- `Seiger\sSettings\Livewire\ModulePanel` switches between Settings and
  Configuration.
- `Seiger\sSettings\Livewire\SettingsPanel` renders the compact value editor.
- `Seiger\sSettings\Livewire\ConfigurePanel` renders the compact schema builder.
- `Seiger\sSettings\Support\SettingsSchemaRepository` reads, normalizes, and
  safely writes the schema.
- `Seiger\sSettings\Support\SystemSettingsStore` reads and writes `sset_*`
  Evolution system settings.
- `Seiger\sSettings\Support\FieldCatalog` owns the field type catalog.

Legacy package wiring stays intentionally small:

- `sSettingsController` renders only the manager entrypoint; it no longer serves
  module-local CSS or JavaScript.
- `sSettingsAlias` is published only as the optional facade alias binding.
- `sSettingsCheck` is merged into `cms.settings` for Evolution package checks.
- All manager UI styling and interaction primitives come from evo-ui assets.

## Installation

Run inside the Evolution CMS `core` directory:

```console
php artisan package:installrequire seiger/ssettings "*"
php artisan vendor:publish --provider="Seiger\\sSettings\\sSettingsServiceProvider"
```

In Extras-based development environments the package can also be installed as a
local path repository and symlinked into the demo core.

## Configuration Files

Package defaults live in:

```text
config/sSettingsSettings.php
```

The writable project schema lives in:

```text
core/custom/config/seiger/settings/sSettings.php
```

If the writable project schema does not exist, sSettings reads the package
default schema.

## Schema Shape

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

Known translated labels are normalized back to translation keys when the schema
is saved. Custom labels without translations are stored as plain text.

## Safe Schema Writes

`SettingsSchemaRepository::save()` writes through a temporary file, runs `php -l`
when `exec()` is available, then replaces the target schema file. This prevents
half-written PHP config files.

Schema save also calls `SystemSettingsStore::syncSchema()`, which creates new
`sset_*` system settings and removes old `sset_*` settings that are no longer in
the schema.

## Field Types

Field types are normalized by `FieldCatalog`:

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

All types except `divider` store values. Checkbox values are normalized to `1`
or `0`; checkbox groups and multi-listboxes serialize multiple selected values
with `||`. Option-based field definitions store options as
`value==Label||value2==Label 2`. Image and file fields store the selected path
as a string.

## Routes And Manager Module

Routes are loaded only in manager mode:

```text
GET  ssettings
GET  ssettings/configure
```

The manager module is registered as hidden and is opened from the Tools menu.
The menu icon uses the same Blade Tabler icon path as the registered module.

## evo-ui Contracts

The manager UI should stay compact:

- Settings values are grouped by schema tabs.
- Configuration has add-tab on the left and save on the right.
- Configure is a staged editor: field modals apply local draft changes, while
  the main Configure Save is the only schema persistence action.
- A field modal primary action uses `evo::global.action_apply`, stays disabled
  while the modal draft is unchanged, and marks Configure dirty after apply.
- Tabs and fields use drag handles for reorder.
- Option rows in field modals use EvoUI `data-evo-dnd-option-list` and
  `data-evo-dnd-option-row`; EvoUI owns the pointer reorder path, while option
  rows, handles and inputs stay `draggable="false"` to avoid modal/native DnD
  timing races. Alpine listens to EvoUI through `evo-ui:dnd-option-changed`;
  do not use dotted `x-on` event names for this path.
- Configure tab and field DnD listens to EvoUI `evo-ui:form-dirty` so reorders
  immediately enable the main Save button before the Livewire redraw completes.
- After a successful Configure Save, `ssettings-schema-saved` refreshes the
  top-level settings tabs from the normalized schema without a page reload.
- Field settings open in a compact modal.
- Type chips and system key chips are small and theme-aware.

Keep package-specific behavior in sSettings. Put only reusable manager primitives
in evo-ui.

## Tests And Smoke Checks

Run syntax checks:

```console
find src plugins module tests config lang -name "*.php" -print0 | xargs -0 -n1 php -l
```

Run the package smoke runner against the shared demo core:

```console
SSETTINGS_DEMO_CORE=/path/to/demo/core php tests/run.php
```

Run the release gate before tagging:

```console
composer validate --strict --no-check-publish
composer test
php tests/run.php
```

Useful demo checks:

```console
php artisan package:discover
php artisan view:clear
php artisan route:list --path=ssettings
```
