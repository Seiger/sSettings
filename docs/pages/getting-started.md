---
layout: page
title: Getting started
description: Getting started with sSettings
permalink: /getting-started/
---

## Install by artisan package

Go to You /core/ folder

```console
cd core
```

Run php artisan commands

```console
php artisan package:installrequire seiger/ssettings "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSettings\sSettingsServiceProvider"
```

## Configuration in backend

Plugin settings are located at **Admin Panel -> Tools -> sSettings**.
{% include figure.html path="assets/img/ssettings.jpg" %}

## Extra

If you write your own code that can integrate with the sSettings plugin, you can check the presence of this plugin in the system through a configuration variable.

```php
if (evo()->getConfig('check_sSettings', false)) {
    // You code
}
```

If the plugin is installed, the result of ```evo()->getConfig('check_sSettings', false)``` will always be ```true```. Otherwise, you will get an ```false```.
