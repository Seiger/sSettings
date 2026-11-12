---
sidebar_position: 3
---

# Use in Blade

Read settings with `evo()->getConfig()` and use the `sset_` prefix. For a text
value, use Blade's escaped output.

```blade
{{ evo()->getConfig('sset_phone') }}
```
