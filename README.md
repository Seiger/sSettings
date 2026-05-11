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
- [x] File field type.
- [x] Image field type.
- [x] Listbox and dropdown option field types.
- [x] Radio and checkbox group field types.
- [x] RichText field type.
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

## Documentation

File-first dDocs documentation lives in `docs/`:

- [English](docs/en/README.md)
- [Ukrainian](docs/ua/README.md)
- [Polish](docs/pl/README.md)
- [German](docs/de/README.md)
- [French](docs/fr/README.md)

Legacy public-site documentation is still available at
[seiger.github.io/sSettings](https://seiger.github.io/sSettings/).

## Release Checks

Run the release gate from the package root before tagging or committing a
manager UI release:

```console
composer validate --strict --no-check-publish
composer test
php tests/run.php
```
