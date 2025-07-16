# WP GeneApp

[![Latest Release](https://img.shields.io/github/v/release/frankbracq/geneapp-wp?label=Latest%20Release)](https://github.com/frankbracq/geneapp-wp/releases/latest)
[![Build](https://github.com/frankbracq/geneapp-wp/actions/workflows/tag-and-build.yml/badge.svg)](https://github.com/frankbracq/geneapp-wp/actions)
[![Download Plugin](https://img.shields.io/github/downloads/frankbracq/geneapp-wp/total?label=Download%20Plugin)](https://github.com/frankbracq/geneapp-wp/releases/latest)

**WP GeneApp** est un plugin WordPress conçu pour intégrer l'application [genealogie.app](https://genealogie.app) dans n'importe quel site WordPress, sous forme d'iframe interactive et sécurisée.

Il permet aux utilisateurs connectés de **consulter, visualiser ou interagir avec les résultats d'une recherche généalogique**, tout en conservant les droits d'accès sécurisés.

---

## ✨ Installation automatique

Lors de l'activation, le plugin :
- ✅ **Crée automatiquement une page "Généalogie"** accessible à `/geneapp/`
- ✅ Applique un template pleine page (sans header/footer WordPress)
- ✅ Configure l'intégration avec le shortcode `[geneapp_embed auto_height="true"]`

**Aucune configuration manuelle nécessaire** pour commencer !

---

## 🔧 Fonctionnalités

- ✅ **Page dédiée automatique** : Une page `/geneapp/` créée à l'activation
- ✅ **Template immersif** : Affichage pleine page sans distractions
- ✅ **Shortcode flexible** : `[geneapp_embed]` utilisable sur n'importe quelle page
- ✅ **Sécurité renforcée** : Signature HMAC-SHA256 pour l'authentification
- ✅ **Réservé aux membres** : Accès limité aux utilisateurs connectés
- ✅ **Hauteur adaptative** : Redimensionnement automatique de l'iframe
- ✅ **Communication bidirectionnelle** : Support des messages entre iframe et site parent

---

## 🚀 Installation

### Installation rapide

1. [Téléchargez la dernière version ici](https://github.com/frankbracq/geneapp-wp/releases/latest)
2. Téléversez le fichier `.zip` dans `Extensions > Ajouter > Téléverser une extension`
3. Activez le plugin
4. **C'est prêt !** Visitez `/geneapp/` sur votre site

### Configuration des clés d'API

Pour activer l'intégration avec genealogie.app :

1. Allez dans **Réglages > GeneApp WP**
2. Entrez votre **Identifiant Partenaire**
3. Entrez votre **Clé Secrète Partenaire**
4. Enregistrez

---

## 📖 Utilisation

### Option 1 : Page automatique (Recommandé)

Utilisez simplement la page créée automatiquement :
```
https://votre-site.com/geneapp/
```

Cette page utilise un template immersif spécialement conçu pour l'application généalogique.

### Option 2 : Intégration personnalisée

Utilisez le shortcode sur n'importe quelle page ou article :

```
[geneapp_embed]
```

Avec options :
```
[geneapp_embed auto_height="true" fullscreen="true"]
```

### Paramètres du shortcode

| Paramètre | Valeur par défaut | Description |
|-----------|-------------------|-------------|
| `src` | `https://genealogie.app/iframe-entry/` | URL de l'application |
| `auto_height` | `true` | Ajustement automatique de la hauteur |
| `fullscreen` | `false` | Mode plein écran |

---

## 🔒 Sécurité

- **Authentification requise** : Les utilisateurs doivent être connectés à WordPress
- **Signature HMAC** : Chaque requête est signée cryptographiquement
- **Validation temporelle** : Protection contre les attaques par rejeu
- **Isolation iframe** : Communication sécurisée via postMessage

---

## 🛠️ Structure technique

Le plugin crée automatiquement :

```
/wp-content/plugins/geneapp-wp/
├── assets/
│   └── css/
│       └── geneapp.css          # Styles de l'intégration
├── includes/
│   ├── signature.php            # Génération HMAC
│   └── admin-settings.php       # Page de configuration
├── templates/
│   └── geneapp-template.php     # Template pleine page
└── geneapp-wp.php              # Fichier principal
```

---

## 📋 Cas d'usage

- **Associations généalogiques** : Espace membre avec accès aux recherches
- **Sites familiaux** : Partage sécurisé de l'arbre généalogique
- **Portails historiques** : Intégration des données généalogiques
- **Clubs d'histoire** : Accès réservé aux membres cotisants

---

## 🤝 Support

- **Documentation** : [Wiki du projet](https://github.com/frankbracq/geneapp-wp/wiki)
- **Issues** : [Signaler un bug](https://github.com/frankbracq/geneapp-wp/issues)
- **Contact** : admin@geneapp-wp.fr

---

## 📜 Licence

Ce plugin est distribué sous licence [GPL v2 ou ultérieure](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

---

## 🔄 Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour l'historique complet des versions.

**Dernière version** : v1.8.2 - Migration vers genealogie.app
