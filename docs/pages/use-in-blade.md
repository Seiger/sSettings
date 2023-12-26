---
layout: page
title: Use in Blade
description: Use sSettings code in Blade layouts
permalink: /use-in-blade/
---

You can use your parameters in the Blade template in the same way as the Evo kernel system variables.

## Show phone number

```php
<a class="phone" href="tel:{% raw %}{{Str::remove([' ', '-', '(', ')'], evo()->getConfig('sset_phone'))}}{% endraw %}">{% raw %}{{evo()->getConfig('sset_phone')}}{% endraw %}</a>
```
