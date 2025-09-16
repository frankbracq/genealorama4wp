# Secure Iframe Embed for Genealorama

**Intégration sécurisée par iframe pour incorporer l'application web Genealorama dans les sites WordPress avec des modèles de page dédiés et validation des identifiants.**

## Description

Ce plugin permet d'intégrer de manière sécurisée l'application web Genealorama dans votre site WordPress via une iframe. Il offre une expérience généalogique interactive unique basée sur les fichiers GEDCOM de vos visiteurs.

### Fonctionnalités principales

- 🔐 **Authentification sécurisée** : Validation des identifiants partenaires avec l'API Genealorama
- 📄 **Modèles de page dédiés** : Templates personnalisés pour une intégration optimale
- 🎨 **Options d'affichage flexibles** : Personnalisez la largeur, hauteur et mode plein écran
- 🔧 **Shortcode personnalisable** : `[genealorama_embed]` avec paramètres optionnels
- 🌍 **Support multilingue** : Interface admin disponible en français et anglais
- ⚡ **Performance optimisée** : Chargement asynchrone et mise en cache intelligente

## Installation

1. Téléchargez le plugin depuis le répertoire WordPress ou uploadez les fichiers dans `/wp-content/plugins/secure-iframe-embed-for-genealorama/`
2. Activez le plugin via le menu 'Extensions' dans WordPress
3. Configurez vos identifiants partenaire dans Réglages → Secure Iframe Embed
4. Créez une nouvelle page en utilisant le modèle 'Genealorama Page Template' ou utilisez le shortcode

## Configuration

### Obtenir vos identifiants partenaire

1. Allez dans **Réglages → Secure Iframe Embed**
2. Entrez votre email administrateur
3. Cliquez sur "Se connecter à Genealorama"
4. Vos identifiants seront générés automatiquement

### Options d'affichage

- **Largeur du conteneur** : Définissez une largeur personnalisée ou utilisez 100%
- **Hauteur du conteneur** : Ajustez la hauteur selon vos besoins
- **Mode plein écran** : Activez pour une expérience immersive

## Utilisation

### Méthode 1 : Modèle de page

1. Créez une nouvelle page
2. Dans les attributs de page, sélectionnez "Modèle de page Genealorama"
3. Publiez la page

### Méthode 2 : Shortcode

Utilisez le shortcode dans n'importe quelle page ou article :

```
[genealorama_embed]
```

Avec paramètres personnalisés :

```
[genealorama_embed width="100%" height="800px" auto_height="true" fullscreen="false"]
```

### Méthode 3 : Widget

Ajoutez Genealorama à vos zones de widgets depuis **Apparence → Widgets**.

## Questions fréquentes

### Le plugin est-il gratuit ?

Oui, le plugin est entièrement gratuit. Cependant, l'utilisation de l'application Genealorama peut nécessiter un compte partenaire.

### Comment obtenir un compte partenaire ?

Le plugin génère automatiquement vos identifiants partenaire lors de la première connexion via l'interface d'administration.

### Puis-je personnaliser l'apparence ?

Oui, vous pouvez ajuster la largeur, hauteur, et activer le mode plein écran. Des options CSS personnalisées peuvent être ajoutées via votre thème.

### Le plugin est-il compatible avec mon thème ?

Le plugin est conçu pour fonctionner avec tous les thèmes WordPress standards. En cas de problème, utilisez le shortcode plutôt que le modèle de page.

## Changelog

### 2.2.1
- Ajout du support complet des traductions françaises
- Correction de la fonction load_plugin_textdomain
- Synchronisation automatique des versions

### 2.1.7
- Amélioration de la validation des identifiants
- Optimisation des performances
- Correction de bugs mineurs

### 2.0.0
- Refonte complète de l'interface d'administration
- Nouveau système d'authentification
- Support des modèles de page personnalisés

## Support

Pour obtenir de l'aide ou signaler un bug :
- [Forum de support WordPress](https://wordpress.org/support/plugin/secure-iframe-embed-for-genealorama/)
- [GitHub Issues](https://github.com/frankbracq/genealorama4wp/issues)

## Développement

Le code source est disponible sur [GitHub](https://github.com/frankbracq/genealorama4wp).

Les contributions sont les bienvenues ! N'hésitez pas à :
- Signaler des bugs
- Suggérer des améliorations
- Soumettre des pull requests

## Licence

GPL-2.0+ - Voir le fichier LICENSE pour plus de détails.

## Crédits

Développé par [genealorama.com](https://genealorama.com)

---

**Note :** Ce plugin nécessite PHP 7.4+ et WordPress 5.0+