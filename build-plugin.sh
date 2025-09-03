#!/bin/bash

# Script to create ZIP for Secure Iframe Embed for Genealorama plugin
# Version is automatically extracted from genealorama.php

PLUGIN_NAME="secure-iframe-embed-for-genealorama"
# Extract version from genealorama.php
VERSION=$(grep -E "^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*[0-9]+\.[0-9]+\.[0-9]+" genealorama.php | sed -E 's/.*Version:[[:space:]]*([0-9.]+).*/\1/')
BUILD_DIR="/tmp/genealorama-build"
ZIP_NAME="${PLUGIN_NAME}-v${VERSION}.zip"

echo "🔨 Building Secure Iframe Embed for Genealorama v${VERSION}..."

# Clean build directory if it exists
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/$PLUGIN_NAME"

# Copy necessary files
echo "📁 Copying files..."

# Root files
cp genealorama.php "$BUILD_DIR/$PLUGIN_NAME/"
cp LICENSE "$BUILD_DIR/$PLUGIN_NAME/"
cp CHANGELOG.md "$BUILD_DIR/$PLUGIN_NAME/"

# Copy existing readme.txt file
echo "📝 Copying WordPress readme..."
cp readme.txt "$BUILD_DIR/$PLUGIN_NAME/"

# Includes directory
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/includes"
cp includes/*.php "$BUILD_DIR/$PLUGIN_NAME/includes/"

# Copy assets directory if it exists
if [ -d "assets" ]; then
    cp -r assets "$BUILD_DIR/$PLUGIN_NAME/"
fi

# Create empty directories that will be created automatically
mkdir -p "$BUILD_DIR/$PLUGIN_NAME/templates"

# Create ZIP
echo "📦 Creating ZIP file..."
cd "$BUILD_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_NAME"

# Copier le ZIP dans le répertoire actuel
mv "$ZIP_NAME" ~/Documents/GitHub/geneapp-wp/

# Nettoyer
rm -rf "$BUILD_DIR"

echo "✅ Plugin built successfully: $ZIP_NAME"
echo "📍 Location: ~/Documents/GitHub/geneapp-wp/$ZIP_NAME"
echo ""
echo "📊 ZIP contents:"
unzip -l ~/Documents/GitHub/geneapp-wp/$ZIP_NAME