---
sidebar_position: 3
---

# Використання в Blade

Читайте налаштування через `evo()->getConfig()` за ключем із префіксом
`sset_`. Для текстових значень використовуйте екранований вивід Blade.

```blade
{{ evo()->getConfig('sset_phone') }}
```
