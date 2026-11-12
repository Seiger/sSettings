---
sidebar_position: 2
---

# Швидкий старт

У каталозі `core` Evolution CMS встановіть sSettings 1.x і опублікуйте його
конфігурацію.

```bash
cd core
php artisan package:installrequire seiger/ssettings "^1.0"
php artisan vendor:publish --provider="Seiger\sSettings\sSettingsServiceProvider"
```

У менеджері відкрийте меню інструментів і виберіть модуль sSettings. Якщо
ваш код також працює на сайтах без цього пакета, перед читанням налаштувань
перевірте `evo()->getConfig('check_sSettings', false)`.
