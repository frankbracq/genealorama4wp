#!/bin/bash

# Script pour synchroniser la version entre genealorama.php et readme.txt

echo "🔄 Synchronisation des numéros de version..."

# Extraire la version depuis genealorama.php (uniquement le numéro)
VERSION=$(grep "^\s*\*\s*Version:" genealorama.php | sed 's/.*Version:\s*//' | tr -d ' ')

if [ -z "$VERSION" ]; then
    echo "❌ Impossible de trouver la version dans genealorama.php"
    exit 1
fi

echo "📌 Version trouvée dans genealorama.php: $VERSION"

# Mettre à jour readme.txt
if [ -f "readme.txt" ]; then
    # Remplacer la ligne Stable tag
    sed -i '' "s/^Stable tag:.*/Stable tag: $VERSION/" readme.txt
    echo "✅ readme.txt mis à jour avec la version $VERSION"
else
    echo "⚠️  readme.txt non trouvé"
fi

# Vérifier la synchronisation
README_VERSION=$(grep "^Stable tag:" readme.txt | sed 's/Stable tag: //')

if [ "$VERSION" == "$README_VERSION" ]; then
    echo "✅ Les versions sont synchronisées: $VERSION"
else
    echo "❌ Erreur de synchronisation!"
    echo "   genealorama.php: $VERSION"
    echo "   readme.txt: $README_VERSION"
    exit 1
fi

echo ""
echo "✨ Synchronisation terminée avec succès!"