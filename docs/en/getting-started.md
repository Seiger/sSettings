---
sidebar_position: 2
---

# Getting started

From the Evolution CMS `core` directory, install the 1.x release line and publish
its configuration.

```bash
cd core
php artisan package:installrequire seiger/ssettings "^1.0"
php artisan vendor:publish --provider="Seiger\sSettings\sSettingsServiceProvider"
```

In the manager, open **Tools** and choose the sSettings module. If your code also
runs on sites without the package, check `evo()->getConfig('check_sSettings', false)`
before using its settings.
