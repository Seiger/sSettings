# Guide développeur

## Architecture

sSettings est un module manager basé sur evo-ui et Livewire.

Éléments principaux:

- `Seiger\sSettings\sSettingsServiceProvider` enregistre les traductions, le
  plugin, le module manager, les routes, les vues, les fichiers publiés et les
  composants Livewire.
- `Seiger\sSettings\Livewire\ModulePanel` bascule entre Paramètres et
  Configuration.
- `Seiger\sSettings\Livewire\SettingsPanel` rend l'éditeur compact des valeurs.
- `Seiger\sSettings\Livewire\ConfigurePanel` rend le builder compact du schéma.
- `Seiger\sSettings\Support\SettingsSchemaRepository` lit, normalise et écrit
  le schéma de manière sûre.
- `Seiger\sSettings\Support\SystemSettingsStore` lit et écrit les paramètres
  système `sset_*`.
- `Seiger\sSettings\Support\FieldCatalog` contient le catalogue des types de
  champs.

## Installation

Dans le dossier `core` d'Evolution CMS:

```console
php artisan package:installrequire seiger/ssettings "*"
php artisan vendor:publish --provider="Seiger\\sSettings\\sSettingsServiceProvider"
```

Dans les environnements Extras locaux, le paquet peut aussi être installé comme
path repository avec un symlink vers le demo core.

## Fichiers de configuration

Schéma par défaut du paquet:

```text
config/sSettingsSettings.php
```

Schéma projet modifiable:

```text
core/custom/config/seiger/settings/sSettings.php
```

Si le schéma projet n'existe pas, sSettings utilise le schéma par défaut du
paquet.

## Forme du schéma

```php
return [
    'basicTab' => [
        'label' => 'sSettings::global.basicTab',
        'fields' => [
            [
                'name' => 'email',
                'label' => 'sSettings::global.email',
                'description' => 'sSettings::global.email_description',
                'type' => 'text',
            ],
        ],
    ],
];
```

Les libellés traduits connus sont normalisés vers leurs clés de traduction lors
de l'enregistrement. Les libellés personnalisés sans traduction sont conservés
comme texte.

## Écriture sûre du schéma

`SettingsSchemaRepository::save()` écrit dans un fichier temporaire, exécute
`php -l` si `exec()` est disponible, puis remplace le fichier cible. Cela évite
un fichier de configuration PHP partiellement écrit.

Après l'enregistrement, `SystemSettingsStore::syncSchema()` synchronise les
paramètres système: les nouveaux `sset_*` sont créés, et les anciens `sset_*`
absents du schéma sont supprimés.

## Types de champs

`FieldCatalog` prend en charge:

- `text`
- `textarea`
- `textareamini`
- `image`
- `file`
- `checkbox`
- `divider`

Tous les types sauf `divider` stockent une valeur. Checkbox est normalisé en
`1` ou `0`; les autres valeurs deviennent des chaînes trimées. Image et File
stockent le chemin sélectionné comme chaîne.

## Routes et module manager

Les routes sont chargées uniquement en manager mode:

```text
GET  ssettings
GET  ssettings/configure
```

Le module manager est enregistré comme hidden et ouvert depuis le menu Tools.
L'icône du menu utilise le même chemin Blade Tabler icon que l'enregistrement du
module.

## Contrats evo-ui

L'interface doit rester compacte:

- les valeurs sont groupées par onglets du schéma;
- dans Configuration, l'ajout d'onglet est à gauche et l'enregistrement à
  droite;
- Configuration est un staged editor: les modals de champ appliquent un draft
  local, et le bouton Save principal est la seule action de persistance schema;
- l'action primaire du modal de champ utilise `evo::global.action_apply`, reste
  inactive tant que le draft est propre, puis marque Configure comme dirty;
- les onglets et champs se réordonnent par poignées de déplacement;
- les options dans les modals de champ utilisent EvoUI
  `data-evo-dnd-option-list` et `data-evo-dnd-option-row`; EvoUI possede le
  chemin pointer reorder, tandis que les lignes, handles et inputs restent
  `draggable="false"` afin d'eviter les races entre modale et native DnD.
  Alpine ecoute `evo-ui:dnd-option-changed`; ne pas utiliser de noms
  d'evenements `x-on` avec des points pour ce chemin;
- le DnD des onglets et champs Configure ecoute EvoUI `evo-ui:form-dirty` afin
  d'activer immediatement le bouton Save principal avant la fin du redraw
  Livewire;
- apres un Configure Save reussi, `ssettings-schema-saved` rafraichit les
  settings tabs superieurs depuis le schema normalise sans reload;
- les paramètres de champ s'ouvrent dans un modal compact;
- les type chips et system key chips sont petits et compatibles avec le thème.

Le comportement propre à sSettings doit rester dans sSettings. Seuls les
manager primitives réutilisables doivent être déplacés vers evo-ui.

## Tests et smoke checks

```console
find src plugins module tests config lang -name "*.php" -print0 | xargs -0 -n1 php -l
```

```console
SSETTINGS_DEMO_CORE=/path/to/demo/core php tests/run.php
```

Commandes utiles dans le demo core:

```console
php artisan package:discover
php artisan view:clear
php artisan route:list --path=ssettings
```
