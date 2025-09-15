# Guide de test des traductions - Plugin Genealorama

## 📋 Prérequis
- Site WordPress de test (local ou staging)
- Accès administrateur

## 🔧 Installation du plugin avec traductions

### 1. Copier les fichiers
```bash
# Depuis le répertoire GitHub
cp -r /Users/Frank/Documents/GitHub/genealorama4wp/* /chemin/vers/wordpress/wp-content/plugins/secure-iframe-embed-for-genealorama/
```

### 2. Structure des fichiers de traduction
Vérifiez que ces fichiers sont présents :
```
secure-iframe-embed-for-genealorama/
├── languages/
│   ├── secure-iframe-embed-for-genealorama.pot (template)
│   ├── secure-iframe-embed-for-genealorama-fr_FR.po (traduction française)
│   └── secure-iframe-embed-for-genealorama-fr_FR.mo (fichier compilé)
├── genealorama.php (avec load_textdomain ajouté)
├── includes/
└── assets/
```

## 🌍 Test des traductions

### 1. Activer le plugin
- Allez dans WordPress Admin > Extensions
- Activez "Secure Iframe Embed for Genealorama"

### 2. Changer la langue de WordPress
- Allez dans Réglages > Général
- Changez "Langue du site" en "Français"
- Enregistrez

### 3. Vérifier les traductions
Allez dans Réglages > Secure Iframe Embed et vérifiez que :

| Anglais (original) | Français (attendu) |
|-------------------|-------------------|
| Settings | Paramètres |
| Partner Credentials | Identifiants partenaire |
| Partner ID | ID Partenaire |
| Partner Secret | Secret Partenaire |
| Validate Credentials | Valider les identifiants |
| Display Options | Options d'affichage |
| Container Width | Largeur du conteneur |
| Container Height | Hauteur du conteneur |
| Show as Full Screen | Afficher en plein écran |
| Save Display Options | Enregistrer les options d'affichage |
| How to Use | Comment utiliser |
| Shortcode | Shortcode |
| Page Template | Modèle de page |
| Custom Integration | Intégration personnalisée |

## 🔍 Dépannage

### Les traductions ne s'affichent pas ?

1. **Vérifiez le fichier .mo**
   ```bash
   # Le fichier .mo doit exister et ne pas être vide
   ls -la languages/*.mo
   ```

2. **Régénérer le fichier .mo**
   ```bash
   cd languages/
   msgfmt -o secure-iframe-embed-for-genealorama-fr_FR.mo secure-iframe-embed-for-genealorama-fr_FR.po
   ```

3. **Vérifiez le Text Domain**
   - Dans `genealorama.php`, ligne 9 : `Text Domain: secure-iframe-embed-for-genealorama`
   - Dans le code : `load_plugin_textdomain('secure-iframe-embed-for-genealorama', ...)`

4. **Videz le cache**
   - Cache WordPress (si plugin de cache installé)
   - Cache navigateur (Ctrl+Shift+R)

5. **Debug WordPress**
   Ajoutez dans `wp-config.php` :
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', true);
   ```

## ✅ Checklist de validation

- [ ] Plugin activé sans erreur
- [ ] Pas d'erreur PHP dans les logs
- [ ] Traductions visibles en français
- [ ] Fonctionnalités du plugin opérationnelles
- [ ] Shortcode `[genealorama_embed]` fonctionne
- [ ] Page template disponible

## 📦 Déploiement vers SVN

Une fois les tests validés :
```bash
# Synchroniser avec SVN
cd /Users/Frank/Documents/GitHub/genealorama4wp/
./sync-to-svn.sh

# Commiter vers WordPress.org
cd /Users/Frank/Documents/SVN/secure-iframe-embed-for-genealorama/
svn commit -m "Add French translation support" --username fbracq
```