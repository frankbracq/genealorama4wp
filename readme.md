# WP GeneApp

[![Latest Release](https://img.shields.io/github/v/release/frankbracq/wp-geneapp?label=Latest%20Release)](https://github.com/frankbracq/wp-geneapp/releases/latest)
[![Build](https://github.com/frankbracq/wp-geneapp/actions/workflows/release.yml/badge.svg)](https://github.com/frankbracq/wp-geneapp/actions)
[![Download Plugin](https://img.shields.io/github/downloads/frankbracq/wp-geneapp/total?label=Download%20Plugin)](https://github.com/frankbracq/wp-geneapp/releases/latest)

**WP GeneApp** est un plugin WordPress conçu pour intégrer l’application [genealogie.app](https://genealogie.app) dans n’importe quel site WordPress, sous forme d’iframe interactive et sécurisée.

Il permet aux utilisateurs connectés de **consulter, visualiser ou interagir avec les résultats d'une recherche généalogique**, tout en conservant les droits d'accès sécurisés.
Idéal pour les portails généalogiques, les membres de clubs d'histoire familiale ou les plateformes collaboratives.

---

## ✨ Cas d’usage typique

- Intégrer genealogie.app dans un espace membre WordPress
- Afficher des arbres ou résultats généalogiques dynamiques
- Personnaliser l'expérience selon l'utilisateur WP connecté
- Conserver les droits d’accès via vérification côté app

---

## 🔧 Fonctionnalités

- ✅ Intégration via iframe : `[geneapp_embed src="https://app.genealogie.app" auto_height="true"]`
- ✅ Transmission sécurisée de l’ID utilisateur et de l’email
- ✅ Signature HMAC vérifiable côté app intégrée
- ✅ Interface admin WordPress pour gérer la clé HMAC
- ✅ Redimensionnement automatique de l’iframe (`auto_height="true"`)

---

## 🚀 Installation

1. [Téléchargez la dernière version ici](https://github.com/frankbracq/wp-geneapp/releases/latest)
2. Téléversez le fichier `.zip` dans `Extensions > Ajouter > Téléverser une extension`
3. Activez le plugin
4. Configurez votre clé HMAC dans **Réglages > WP GeneApp**
5. Intégrez votre app avec le shortcode :

```plaintext
[geneapp_embed src="https://app.genealogie.app" auto_height="true"]
```

---

## 📜 Licence

Ce plugin est distribué sous licence [GPL v2 ou ultérieure](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
