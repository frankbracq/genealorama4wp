#!/bin/bash

# Create ZIP for Secure Iframe Embed for Genealorama plugin
# Version is automatically extracted from genealorama.php
cd /Users/Frank/Documents/GitHub/geneapp-wp

# Extract version from genealorama.php
VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+" genealorama.php | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/')

# Create temporary directory
TEMP_DIR="/tmp/genealorama-build"
rm -rf $TEMP_DIR
mkdir -p $TEMP_DIR/secure-iframe-embed-for-genealorama
mkdir -p $TEMP_DIR/secure-iframe-embed-for-genealorama/includes
mkdir -p $TEMP_DIR/secure-iframe-embed-for-genealorama/assets
mkdir -p $TEMP_DIR/secure-iframe-embed-for-genealorama/templates

# Copy files
cp genealorama.php $TEMP_DIR/secure-iframe-embed-for-genealorama/
cp LICENSE $TEMP_DIR/secure-iframe-embed-for-genealorama/
cp CHANGELOG.md $TEMP_DIR/secure-iframe-embed-for-genealorama/
cp includes/admin-settings.php $TEMP_DIR/secure-iframe-embed-for-genealorama/includes/
cp includes/signature.php $TEMP_DIR/secure-iframe-embed-for-genealorama/includes/
cp -r assets $TEMP_DIR/secure-iframe-embed-for-genealorama/ 2>/dev/null || true

# Copy existing readme.txt file
cp readme.txt $TEMP_DIR/secure-iframe-embed-for-genealorama/

# Create ZIP
cd $TEMP_DIR
ZIP_NAME="secure-iframe-embed-for-genealorama-v${VERSION}.zip"
zip -r $ZIP_NAME secure-iframe-embed-for-genealorama/

# Copy ZIP to original folder
cp $ZIP_NAME /Users/Frank/Documents/GitHub/geneapp-wp/

# Display content
echo "✅ ZIP created: /Users/Frank/Documents/GitHub/geneapp-wp/$ZIP_NAME"
echo ""
echo "📦 ZIP contents:"
unzip -l $ZIP_NAME

# Clean up
rm -rf $TEMP_DIR