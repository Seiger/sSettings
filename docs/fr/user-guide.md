# Guide utilisateur

## Ouvrir sSettings

Dans le manager Evolution CMS, ouvrez **Outils -> Paramètres utilisateur**. Le
module contient deux onglets:

- **Paramètres** - modifier les valeurs.
- **Configuration** - modifier les onglets, champs, libellés, descriptions et
  types de champs.

L'interface utilise evo-ui et Livewire. Les changements d'onglet et les
enregistrements ne rafraîchissent que la zone du module.

## Écran Paramètres

L'écran Paramètres affiche un onglet de configuration à la fois. Chaque ligne de
champ contient:

- le libellé du champ;
- la clé système, par exemple `[(sset_phone)]`;
- le contrôle de saisie;
- la description sous le contrôle.

La description indique où la valeur est utilisée sur le site. Cliquez sur
**Enregistrer** après les modifications.

## Types de champs

| Type | Usage |
| --- | --- |
| Text | Valeurs courtes comme e-mail, téléphone, URL ou ID. |
| Textarea | Texte long, fragments HTML ou scripts de suivi. |
| TextareaMini | Valeurs courtes sur plusieurs lignes. |
| RichText | Contenu formate dans le manager rich editor. |
| DropDown List Menu | Une valeur choisie dans un dropdown compact. |
| Listbox | Une ou plusieurs valeurs dans une liste plus haute. |
| Radio Options | Une valeur parmi des options visibles. |
| Checkbox Group | Plusieurs valeurs parmi des options visibles. |
| Image | Chemin d'image choisi via l'aide du manager. |
| File | Chemin de fichier choisi via l'aide du manager. |
| Checkbox | Option activée ou désactivée. |
| Divider | Séparateur visuel; ne stocke pas de valeur. |

Les checkbox sont enregistrées comme `1` ou `0`. Les champs avec options
utilisent des lignes value/label. Le plus ajoute une option apres la ligne
courante, la corbeille supprime, les fleches reordonnent manuellement et le
drag handle reordonne la liste.

## Écran Configuration

L'écran Configuration modifie le schéma utilisé par l'écran Paramètres.

Une ligne d'onglet contient:

- une poignée de déplacement;
- la clé de l'onglet;
- le libellé traduit de l'onglet;
- des actions compactes pour ajouter un onglet, ajouter un champ ou supprimer
  l'onglet.

Une ligne de champ contient:

- une poignée de déplacement;
- le libellé du champ;
- la puce de clé système;
- la description du champ;
- la puce de type;
- les actions paramètres, ajouter après et supprimer.

Cliquez sur l'icône des paramètres d'un champ pour ouvrir le modal compact.

## Réordonner

Utilisez la poignée de déplacement pour réordonner les onglets ou les champs.
Les champs peuvent bouger dans un onglet ou entre onglets. Les options dans une
modal de champ utilisent le meme EvoUI drag handle et gardent les fleches pour
un ordre manuel precis.

## Clés système

Chaque champ de valeur crée un paramètre système Evolution:

```text
nom du champ: phone
clé système: sset_phone
tag template: [(sset_phone)]
```

Changer le nom du champ change la clé système après l'enregistrement de la
Configuration.

## Enregistrement et permissions

L'écran Paramètres enregistre les valeurs. L'écran Configuration enregistre le
schéma et synchronise les paramètres système. La Configuration nécessite la
permission Evolution `settings`.

Si le fichier de schéma n'est pas accessible en écriture, sSettings affiche une
erreur d'écriture.

## Dépannage

- Si un champ manque, vérifiez l'onglet Configuration et enregistrez le schéma.
- Si une valeur n'apparaît pas sur le frontend, videz le cache Evolution après
  l'enregistrement.
- Si la Configuration est indisponible, vérifiez la permission `settings` du
  rôle manager.
- Si un libellé ressemble à `sSettings::global.email`, il manque une traduction
  pour la langue active du manager.
