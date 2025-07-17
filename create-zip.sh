#!/bin/bash

# Créer le ZIP du plugin GeneApp-WP
# La version est extraite automatiquement depuis geneapp-wp.php
cd /Users/Frank/Documents/GitHub/geneapp-wp

# Extraire la version depuis geneapp-wp.php
VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+" geneapp-wp.php | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/')

# Créer un répertoire temporaire
TEMP_DIR="/tmp/geneapp-wp-build"
rm -rf $TEMP_DIR
mkdir -p $TEMP_DIR/geneapp-wp
mkdir -p $TEMP_DIR/geneapp-wp/includes
mkdir -p $TEMP_DIR/geneapp-wp/assets/css
mkdir -p $TEMP_DIR/geneapp-wp/templates

# Copier les fichiers
cp geneapp-wp.php $TEMP_DIR/geneapp-wp/
cp LICENSE $TEMP_DIR/geneapp-wp/
cp CHANGELOG.md $TEMP_DIR/geneapp-wp/
cp includes/admin-settings.php $TEMP_DIR/geneapp-wp/includes/
cp includes/signature.php $TEMP_DIR/geneapp-wp/includes/

# Convertir readme.md en readme.txt au format WordPress
cat > $TEMP_DIR/geneapp-wp/readme.txt << EOF
=== GeneApp-WP ===
Contributors: fbracq
Tags: genealogy, iframe, integration, family tree, geneapp
Requires at least: 5.0
Tested up to: 6.7
Stable tag: $VERSION
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Intégration sécurisée de GeneApp dans WordPress avec authentification et page dédiée automatique.

EOF

# Ajouter le contenu du readme.md
echo "" >> $TEMP_DIR/geneapp-wp/readme.txt
echo "== Description ==" >> $TEMP_DIR/geneapp-wp/readme.txt
echo "" >> $TEMP_DIR/geneapp-wp/readme.txt
tail -n +12 readme.md >> $TEMP_DIR/geneapp-wp/readme.txt

# Créer le ZIP
cd $TEMP_DIR
ZIP_NAME="geneapp-wp-v${VERSION}.zip"
zip -r $ZIP_NAME geneapp-wp/

# Copier le ZIP dans le dossier d'origine
cp $ZIP_NAME /Users/Frank/Documents/GitHub/geneapp-wp/

# Afficher le contenu
echo "✅ ZIP créé : /Users/Frank/Documents/GitHub/geneapp-wp/$ZIP_NAME"
echo ""
echo "📦 Contenu du ZIP :"
unzip -l $ZIP_NAME

# Nettoyer
rm -rf $TEMP_DIR