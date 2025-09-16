#!/bin/bash

# Script de release automatisé pour Genealorama Plugin
# Usage: ./release.sh [version]
# Exemple: ./release.sh 2.2.2

echo "🚀 Script de release pour Genealorama Plugin"
echo "============================================"

# Vérifier si une version est fournie
if [ $# -eq 1 ]; then
    NEW_VERSION=$1
    echo "📌 Nouvelle version spécifiée: $NEW_VERSION"

    # Mettre à jour genealorama.php
    sed -i '' "s/\* Version: .*/\* Version: $NEW_VERSION/" genealorama.php
    echo "✅ genealorama.php mis à jour"
else
    # Extraire la version actuelle
    NEW_VERSION=$(grep "^\s*\*\s*Version:" genealorama.php | sed 's/.*Version:\s*//' | tr -d ' ')
    echo "📌 Version actuelle: $NEW_VERSION"
fi

# Synchroniser readme.txt
echo ""
echo "🔄 Synchronisation de readme.txt..."
./sync-version.sh

# Vérifier les modifications
echo ""
echo "📝 Modifications à committer:"
git diff --name-only

# Demander confirmation
echo ""
read -p "📤 Voulez-vous committer et pusher vers GitHub? (o/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Oo]$ ]]; then
    # Ajouter les fichiers modifiés
    git add genealorama.php readme.txt languages/

    # Committer
    git commit -m "Release version $NEW_VERSION

- Updated version numbers
- French translations included
- Auto-sync readme.txt"

    # Pusher vers GitHub (déclenchera la GitHub Action)
    echo "🚀 Push vers GitHub..."
    git push origin main

    echo ""
    echo "✅ Release $NEW_VERSION déclenchée!"
    echo ""
    echo "📊 Prochaines étapes automatiques:"
    echo "1. ⏳ GitHub Action va créer le tag v$NEW_VERSION"
    echo "2. 📦 GitHub Action va créer le .zip"
    echo "3. 🎉 Une release sera créée sur GitHub"
    echo ""
    echo "📍 Suivez le progrès sur:"
    echo "   https://github.com/frankbracq/genealorama4wp/actions"
    echo ""
    echo "🔄 Pour synchroniser avec SVN après:"
    echo "   ./sync-to-svn.sh"
    echo "   cd /Users/Frank/Documents/SVN/secure-iframe-embed-for-genealorama"
    echo "   svn commit -m \"Release version $NEW_VERSION\" --username fbracq"
else
    echo "❌ Release annulée"
fi