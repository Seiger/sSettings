---
slug: /
sidebar_position: 1
---

# sSettings Documentation

sSettings adds a compact settings workspace to the Evolution CMS manager. It is
used for site-wide values such as email, phone numbers, social links, tracking
scripts, file paths, images, and project-specific flags.

## Settings screen

![sSettings manager with project settings and configuration tabs](../assets/img/ssettings-manager.png)

[![PHP supported: 8.4 and 8.5](https://img.shields.io/badge/PHP_supported-8.4_%7C_8.5-777BB4?logo=php&logoColor=white)](releases.md)

## Guides

- [User Guide](user-guide.md)
- [Developer Guide](developer-guide.md)
- [Frontend Guide](frontend-guide.md)
- [Releases and compatibility](releases.md)

For installations on sSettings 1.x, see the [archived 1.x guides](https://seiger.github.io/sSettings/1.x/).

## Main Capabilities

- Edit project settings in a compact evo-ui manager screen.
- Organize fields into tabs such as Basic information and Social networks.
- Configure tabs and fields from the manager when the user has the `settings`
  permission.
- Store values as Evolution system settings with the `sset_` prefix.
- Use values from Blade or Evolution templates through `evo()->getConfig()`.
