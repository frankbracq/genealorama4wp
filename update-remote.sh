#!/bin/bash

# Script pour mettre à jour l'URL remote après renommage du repository GitHub
# À exécuter après avoir renommé geneapp-wp → genealorama4wp sur GitHub

echo "🔄 Mise à jour de l'URL remote Git..."

# Afficher l'URL actuelle
echo "URL actuelle :"
git remote -v

# Mettre à jour l'URL remote
git remote set-url origin https://github.com/frankbracq/genealorama4wp.git

# Vérifier la nouvelle URL
echo ""
echo "Nouvelle URL :"
git remote -v

# Tester la connexion
echo ""
echo "🧪 Test de connexion..."
if git ls-remote origin &> /dev/null; then
    echo "✅ Connexion au repository genealorama4wp réussie !"
else
    echo "❌ Erreur de connexion. Vérifiez que le repository a bien été renommé sur GitHub."
fi

echo ""
echo "✨ Mise à jour terminée !"