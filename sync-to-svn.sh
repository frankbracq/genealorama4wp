#!/bin/bash

# Script de synchronisation GitHub vers SVN pour le plugin Genealorama
# Usage: ./sync-to-svn.sh

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Synchronisation GitHub vers SVN ===${NC}"

# Chemins
GITHUB_DIR="/Users/Frank/Documents/GitHub/genealorama4wp"
SVN_DIR="/Users/Frank/Documents/SVN/secure-iframe-embed-for-genealorama"

# Vérifier que les répertoires existent
if [ ! -d "$GITHUB_DIR" ]; then
    echo -e "${RED}Erreur: Le répertoire GitHub n'existe pas: $GITHUB_DIR${NC}"
    exit 1
fi

if [ ! -d "$SVN_DIR" ]; then
    echo -e "${RED}Erreur: Le répertoire SVN n'existe pas: $SVN_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Synchronisation des fichiers du plugin...${NC}"

# Copier les fichiers principaux vers trunk
cp -r "$GITHUB_DIR/includes" "$SVN_DIR/trunk/"
cp -r "$GITHUB_DIR/assets" "$SVN_DIR/trunk/"
cp "$GITHUB_DIR/genealorama.php" "$SVN_DIR/trunk/"
cp "$GITHUB_DIR/readme.txt" "$SVN_DIR/trunk/"

# Copier le dossier languages s'il existe
if [ -d "$GITHUB_DIR/languages" ]; then
    echo -e "${YELLOW}Copie des fichiers de traduction...${NC}"
    cp -r "$GITHUB_DIR/languages" "$SVN_DIR/trunk/"
fi

# Copier les assets pour WordPress.org (screenshots, banners)
if [ -d "$GITHUB_DIR/assets" ]; then
    cp -r "$GITHUB_DIR/assets/"* "$SVN_DIR/assets/" 2>/dev/null || true
fi

echo -e "${GREEN}Fichiers copiés avec succès!${NC}"

# Aller dans le répertoire SVN
cd "$SVN_DIR"

# Afficher le statut SVN
echo -e "${YELLOW}Statut SVN:${NC}"
svn status

# Ajouter les nouveaux fichiers
echo -e "${YELLOW}Ajout des nouveaux fichiers...${NC}"
svn add --force trunk/* 2>/dev/null || true
svn add --force assets/* 2>/dev/null || true

# Afficher le statut final
echo -e "${GREEN}Statut final:${NC}"
svn status

echo -e "${GREEN}=== Synchronisation terminée ===${NC}"
echo -e "${YELLOW}Pour commiter les changements, utilisez:${NC}"
echo "cd $SVN_DIR"
echo "svn commit -m \"Votre message de commit\" --username VOTRE_USERNAME"