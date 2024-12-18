# sSettings for Evolution CMS
![sSettings](https://repository-images.githubusercontent.com/627975404/56ba0688-1b24-4ea5-a58a-359fa4ef1be4)
[![Latest Stable Version](https://img.shields.io/packagist/v/seiger/ssettings?label=version)](https://packagist.org/packages/seiger/ssettings)
[![CMS Evolution](https://img.shields.io/badge/CMS-Evolution-brightgreen.svg)](https://github.com/evolution-cms/evolution)
![PHP version](https://img.shields.io/packagist/php-v/seiger/ssettings)
[![License](https://img.shields.io/packagist/l/seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)
[![Issues](https://img.shields.io/github/issues/Seiger/ssettings)](https://github.com/Seiger/ssettings/issues)
[![Stars](https://img.shields.io/packagist/stars/Seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)
[![Total Downloads](https://img.shields.io/packagist/dt/seiger/ssettings)](https://packagist.org/packages/seiger/ssettings)

**sSettings** is a powerful plugin meticulously crafted for Evolution CMS, providing an
extensive array of advanced settings to elevate your website's customization capabilities.
Tailored by Seiger, this plugin empowers website administrators and developers with a
seamless solution for configuring and fine-tuning various aspects of their online presence.

Designed to seamlessly integrate with Evolution CMS, **sSettings** is your go-to solution for
unlocking advanced customization options. Whether you are a seasoned developer or a
website administrator, this plugin offers an intuitive interface for configuring your
website's settings to align precisely with your vision. Experience the next level of
control and flexibility with **sSettings**.

## Features

- [x] Checkbox field type.
- [x] Image field type.
- [x] Text field type.
- [x] Textarea field type.
- [x] TextareaMini field type.

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

[See full documentation here](https://seiger.github.io/sSettings/)