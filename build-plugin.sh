#!/bin/bash

# Script pour créer le ZIP du plugin GeneApp-WP
# La version est extraite automatiquement depuis geneapp-wp.php

PLUGIN_NAME="geneapp-wp"
# Extraire la version depuis geneapp-wp.php
VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+" geneapp-wp.php | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/')
BUILD_DIR="/tmp/geneapp-wp-build"
ZIP_NAME="${PLUGIN_NAME}-v${VERSION}.zip"

echo "🔨 Construction du plugin GeneApp-WP v${VERSION}..."

# Nettoyer le répertoire de build s'il existe
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/$PLUGIN_NAME"

# Copier les fichiers nécessaires
echo "📁 Copie des fichiers..."

# Fichiers racine
cp geneapp-wp.php "$BUILD_DIR/$PLUGIN_NAME/"
cp LICENSE "$BUILD_DIR/$PLUGIN_NAME/"
cp CHANGELOG.md "$BUILD_DIR/$PLUGIN_NAME/"

# Convertir readme.md en readme.txt au format WordPress
echo "📝 Conversion du README au format WordPress..."
cat > "$BUILD_DIR/$PLUGIN_NAME/readme.txt" << EOF
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

# Ajouter le contenu du readme.md (sans l'en-tête)
echo "" >> "$BUILD_DIR/$PLUGIN_NAME/readme.txt"
echo "== Description ==" >> "$BUILD_DIR/$PLUGIN_NAME/readme.txt"
echo "" >> "$BUILD_DIR/$PLUGIN_NAME/readme.txt"
# Extraire le contenu après la ligne 11 du readme.md
tail -n +12 readme.md >> "$BUILD_DIR/$PLUGIN_NAME/readme.txt"

# Répertoire includes
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/includes"
cp includes/*.php "$BUILD_DIR/$PLUGIN_NAME/includes/"

# Créer les répertoires vides qui seront créés automatiquement
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/assets/css"
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/templates"

# Créer le ZIP
echo "📦 Création du fichier ZIP..."
cd "$BUILD_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_NAME"

# Copier le ZIP dans le répertoire actuel
mv "$ZIP_NAME" ~/Documents/GitHub/geneapp-wp/

# Nettoyer
rm -rf "$BUILD_DIR"

echo "✅ Plugin construit avec succès : $ZIP_NAME"
echo "📍 Emplacement : ~/Documents/GitHub/geneapp-wp/$ZIP_NAME"
echo ""
echo "📊 Contenu du ZIP :"
unzip -l ~/Documents/GitHub/geneapp-wp/$ZIP_NAME