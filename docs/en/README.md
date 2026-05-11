# sSettings Documentation

sSettings adds a compact settings workspace to the Evolution CMS manager. It is
used for site-wide values such as email, phone numbers, social links, tracking
scripts, file paths, images, and project-specific flags.

## Guides

- [User Guide](user-guide.md)
- [Developer Guide](developer-guide.md)
- [Frontend Guide](frontend-guide.md)

## Main Capabilities

- Edit project settings in a compact evo-ui manager screen.
- Organize fields into tabs such as Basic information and Social networks.
- Configure tabs and fields from the manager when the user has the `settings`
  permission.
- Store values as Evolution system settings with the `sset_` prefix.
- Use values from Blade or Evolution templates through `evo()->getConfig()`.
