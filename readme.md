# GeneApp-WP

[![Latest Release](https://img.shields.io/github/v/release/frankbracq/geneapp-wp?label=Latest%20Release)](https://github.com/frankbracq/geneapp-wp/releases/latest)
[![Build](https://github.com/frankbracq/geneapp-wp/actions/workflows/tag-and-build.yml/badge.svg)](https://github.com/frankbracq/geneapp-wp/actions)
[![Download Plugin](https://img.shields.io/github/downloads/frankbracq/geneapp-wp/total?label=Download%20Plugin)](https://github.com/frankbracq/geneapp-wp/releases/latest)

**GeneApp-WP** est un plugin WordPress conçu pour intégrer l'application GeneApp dans n'importe quel site WordPress, sous forme d'iframe interactive et sécurisée. Une démo de cette intégration est disponible sur [geneapp-wp.fr](https://geneapp-wp.fr)

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
- 🆕 **Validation des identifiants** : Vérification automatique et manuelle de la validité des clés

---

## 🚀 Installation

### Installation rapide

1. [Téléchargez la dernière version ici](https://github.com/frankbracq/geneapp-wp/releases/latest)
2. Téléversez le fichier `.zip` dans `Extensions > Ajouter > Téléverser une extension`
3. Activez le plugin
4. **C'est prêt !** Visitez `/geneapp/` sur votre site

### Configuration des clés d'API

Pour activer l'intégration avec app.genealorama.com :

1. Allez dans **Réglages > GeneApp-WP**
2. Entrez votre **Email** et cliquez sur "Récupérer mes identifiants"
3. Les identifiants sont automatiquement remplis et validés
4. Cliquez sur "Enregistrer les paramètres"

#### 🆕 Validation des identifiants

Le plugin affiche maintenant :
- **Date de dernière validation** : Savoir quand vos identifiants ont été vérifiés
- **Statut** : Indicateur visuel (✓ Valides ou ⚠ Invalides)
- **Validation manuelle** : Bouton "Valider maintenant" pour vérifier à tout moment
- **Validation automatique** : Vérification quotidienne en arrière-plan

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
| `src` | `https://app.genealorama.com/iframe-entry/` | URL de l'application |
| `auto_height` | `true` | Ajustement automatique de la hauteur |
| `fullscreen` | `false` | Mode plein écran |

---

## 🔒 Sécurité

- **Authentification requise** : Les utilisateurs doivent être connectés à WordPress
- **Signature HMAC** : Chaque requête est signée cryptographiquement
- **Validation temporelle** : Protection contre les attaques par rejeu
- **Isolation iframe** : Communication sécurisée via postMessage
- 🆕 **Détection d'erreurs** : Alerte automatique si les identifiants sont invalides

### Gestion des erreurs d'authentification

Si les identifiants deviennent invalides :
- **Administrateurs** : Message d'avertissement avec lien vers les paramètres
- **Utilisateurs** : Message générique les invitant à contacter l'administrateur
- **Validation proactive** : Vérification quotidienne pour détecter les problèmes

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
│   └── admin-settings.php       # Page de configuration avec validation
├── templates/
│   └── geneapp-template.php     # Template pleine page
└── geneapp-wp.php              # Fichier principal avec gestion d'erreurs
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

**Dernière version** : v1.9.1 - Interface d'administration moderne et corrections techniques