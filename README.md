# sSettings for Evolution CMS 3
![sSettings](https://github.com/Seiger/ssettings/releases/download/v1.0.0/sLang.jpg)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/ssettings?label=version)](https://packagist.org/packages/seiger/ssettings)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/ssettings)
[![License](https://img.shields.io/packagist/l/seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)
[![Issues](https://img.shields.io/github/issues/Seiger/ssettings)](https://github.com/Seiger/ssettings/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)

**sSettings** Seiger advanced settings for your website.

## Install by artisan package installer

Go to You /core/ folder:

```console
cd core
```

Run php artisan command

```console
php artisan package:installrequire seiger/ssettings "*"
```

```console
php artisan vendor:publish --provider="Seiger\sSettings\sSettingsServiceProvider"
```

Run make DB structure with command:

```console
php artisan migrate
```

[See full documentation here](https://seiger.github.io/ssettings/)