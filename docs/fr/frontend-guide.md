# Guide frontend

## Lire les valeurs

sSettings stocke les champs comme paramètres système Evolution avec le préfixe
`sset_`. Lisez-les avec `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

## Exemples Blade

Lien téléphone:

```blade
@php($phone = evo()->getConfig('sset_phone', ''))

@if($phone !== '')
    <a href="tel:{{ Str::remove([' ', '-', '(', ')'], $phone) }}">
        {{ $phone }}
    </a>
@endif
```

Lien social:

```blade
@php($facebook = evo()->getConfig('sset_facebook', ''))

@if($facebook !== '')
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Script de suivi:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

N'affichez les textarea en raw HTML que si des managers de confiance contrôlent
ces valeurs.

## Tags Evolution

Dans les templates et chunks Evolution, utilisez les tags de paramètres système:

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Images et fichiers

Les champs Image et File stockent des chemins. Utilisez la valeur comme `src`,
`href` ou comme entrée pour votre propre helper d'assets.

```blade
@php($logo = evo()->getConfig('sset_logo', ''))

@if($logo !== '')
    <img src="{{ $logo }}" alt="">
@endif
```

## Checkbox

Les checkbox sont stockées comme chaînes:

```php
$enabled = evo()->getConfig('sset_show_contacts', '0') === '1';
```

## Cache

sSettings vide le cache Evolution après l'enregistrement des valeurs ou la
synchronisation du schéma. Si le frontend a son propre cache, videz-le
séparément.
