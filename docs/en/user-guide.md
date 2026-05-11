# User Guide

## Opening sSettings

Open the Evolution CMS manager and choose **Tools -> User settings**. The module
has two manager tabs:

- **Settings** - edit the actual values.
- **Configuration** - edit tabs, fields, labels, descriptions, and field types.

The interface uses evo-ui and Livewire, so tab switching and saves refresh only
the module area.

## Settings Screen

The Settings screen shows one configuration tab at a time. Each field row has:

- the field label;
- the system key, for example `[(sset_phone)]`;
- the input control;
- the field description below the input.

Use the descriptions to understand where a value appears on the site. Press
**Save** after changing values.

## Field Types

Available field types:

| Type | Use |
| --- | --- |
| Text | Short values such as email, phone, URL, or ID. |
| Textarea | Large text, HTML snippets, or tracking scripts. |
| TextareaMini | Short multi-line values. |
| Image | Image path selected through the manager helper. |
| File | File path selected through the manager helper. |
| Checkbox | Enabled or disabled flags. |
| Divider | A visual separator; it does not store a value. |

Checkbox values are saved as `1` or `0`.

## Configuration Screen

The Configuration screen changes the schema used by the Settings screen.

Tab rows contain:

- a drag handle;
- a tab key;
- a translated tab label;
- compact actions to add another tab, add a field, or remove the tab.

Field rows contain:

- a drag handle;
- the field label;
- the system key chip;
- the field description;
- the type chip;
- actions for settings, add-after, and delete.

Click the settings icon on a field to open the compact field settings modal.

## Reordering

Use the drag handle to reorder tabs or fields. Fields are reordered inside their
current tab.

## System Keys

Every value field creates an Evolution system setting:

```text
field name: phone
system key: sset_phone
template tag: [(sset_phone)]
```

Changing a field name changes the system key after saving Configuration.

## Saving And Permissions

The Settings screen saves values. The Configuration screen saves the schema and
syncs system settings. Configuration requires the Evolution `settings`
permission.

If the schema file cannot be written, sSettings shows a writable-file error.

## Troubleshooting

- If a field is missing, check the Configuration tab and save the schema.
- If a value is not visible on the frontend, clear the Evolution cache after
  saving.
- If Configuration is unavailable, confirm that your manager role has the
  `settings` permission.
- If a label looks like `sSettings::global.email`, it is a translation key that
  has no matching translation in the active manager language.
