---
sidebar_position: 4
---

# Guide frontend

## Lire les valeurs

sSettings stocke les champs comme paramètres système Evolution avec le préfixe
`sset_`. Lisez-les avec `evo()->getConfig()`.

```php
$phone = evo()->getConfig('sset_phone', '');
```

Pour afficher du texte dans du HTML, utilisez `{{ $value }}` ou
`@evoConfig('sset_phone')`. Les deux formes échappent la valeur.

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
@php($facebook = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_facebook', '')))

@if($facebook !== null)
    <a href="{{ $facebook }}" rel="noopener" target="_blank">Facebook</a>
@endif
```

Script de suivi:

```blade
{!! evo()->getConfig('sset_google_analytics', '') !!}
```

Cette sortie exécute du JavaScript dans le navigateur des visiteurs. Utilisez-la
pour `sset_google_analytics` uniquement si toutes les personnes qui disposent
de la permission `settings` peuvent publier des scripts sur le site. Pour le
texte ordinaire, utilisez `{{ }}`.

## Tags Evolution

Les balises Evolution insèrent les valeurs sans échappement HTML dans les
modèles et les chunks. Échappez le texte et validez les URL selon leur contexte
d’utilisation.

```text
[(sset_email)]
[(sset_phone)]
[(sset_facebook)]
```

## Images et fichiers

`SettingUrl::resolve()` accepte les URL HTTP(S) absolues. Passez `EVO_SITE_URL`
en second argument pour résoudre les chemins relatifs au site destinés à `src`
ou `href`.

```blade
@php($logo = \Seiger\sSettings\Support\SettingUrl::resolve(evo()->getConfig('sset_logo', ''), EVO_SITE_URL))

@if($logo !== null)
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
