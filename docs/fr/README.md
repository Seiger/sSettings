# Documentation sSettings

sSettings ajoute un espace de configuration compact dans le manager Evolution
CMS. Le module sert à gérer les valeurs globales du projet: e-mails, téléphones,
liens sociaux, scripts de suivi, fichiers, images et options spécifiques au
site.

## Guides

- [Guide utilisateur](user-guide.md)
- [Guide développeur](developer-guide.md)
- [Guide frontend](frontend-guide.md)

## Capacités principales

- Modifier les paramètres du projet dans une interface evo-ui compacte.
- Organiser les champs en onglets, par exemple Informations principales et
  Réseaux sociaux.
- Configurer les onglets et les champs depuis le manager si l'utilisateur a la
  permission `settings`.
- Enregistrer les valeurs comme paramètres système Evolution avec le préfixe
  `sset_`.
- Utiliser les valeurs dans Blade ou les templates Evolution via
  `evo()->getConfig()`.
