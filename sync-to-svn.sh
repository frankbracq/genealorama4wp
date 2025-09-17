#!/bin/bash

# Script de synchronisation automatique Git vers SVN
# Synchronise le dépôt Git genealorama4wp vers le dépôt SVN secure-iframe-embed-for-genealorama

set -e

# Configuration des chemins
GIT_REPO_PATH="/Users/Frank/Documents/GitHub/genealorama4wp"
SVN_REPO_PATH="/Users/Frank/Documents/SVN/secure-iframe-embed-for-genealorama"
SVN_TRUNK_PATH="${SVN_REPO_PATH}/trunk"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🔄 Début de la synchronisation Git vers SVN${NC}"

# Vérifier que nous sommes dans le bon répertoire Git
cd "$GIT_REPO_PATH"
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Erreur: Le répertoire Git n'est pas valide${NC}"
    exit 1
fi

# Vérifier que le dépôt SVN existe
if [ ! -d "$SVN_REPO_PATH/.svn" ]; then
    echo -e "${RED}❌ Erreur: Le répertoire SVN n'est pas valide${NC}"
    exit 1
fi

# Mettre à jour le dépôt SVN
echo -e "${YELLOW}📥 Mise à jour du dépôt SVN...${NC}"
cd "$SVN_REPO_PATH"
svn update

# Liste des fichiers à synchroniser (exclure les fichiers spécifiques à Git)
FILES_TO_SYNC=(
    "genealorama.php"
    "readme.txt"
    "assets/"
    "includes/"
)

echo -e "${YELLOW}📋 Synchronisation des fichiers...${NC}"

# Supprimer les anciens fichiers dans SVN trunk (sauf .svn)
cd "$SVN_TRUNK_PATH"
find . -maxdepth 1 ! -name '.' ! -name '..' ! -name '.svn' ! -name '.DS_Store' -exec rm -rf {} +

# Vérifier la synchronisation des versions
echo -e "${YELLOW}🔍 Vérification de la synchronisation des versions...${NC}"
cd "$GIT_REPO_PATH"

# Extraire la version du fichier principal PHP
PHP_VERSION=$(grep "^ \* Version:" genealorama.php | sed 's/^ \* Version: //')

# Extraire la version du readme.txt
README_VERSION=$(grep "^Stable tag:" readme.txt | sed 's/^Stable tag: //')

echo "  Version dans genealorama.php: $PHP_VERSION"
echo "  Version dans readme.txt: $README_VERSION"

# Vérifier si les versions sont synchronisées
if [ "$PHP_VERSION" != "$README_VERSION" ]; then
    echo -e "${RED}❌ Erreur: Les versions ne sont pas synchronisées!${NC}"
    echo -e "${YELLOW}💡 Synchronisation automatique des versions...${NC}"

    # Utiliser la version du fichier PHP comme référence
    sed -i '' "s/^Stable tag: .*/Stable tag: $PHP_VERSION/" readme.txt
    echo -e "${GREEN}✅ Version mise à jour dans readme.txt: $PHP_VERSION${NC}"

    # Commit automatique de la synchronisation des versions
    git add readme.txt
    git commit -m "sync: update readme.txt version to match genealorama.php ($PHP_VERSION)"
    echo -e "${GREEN}✅ Commit Git automatique pour la synchronisation des versions${NC}"
else
    echo -e "${GREEN}✅ Les versions sont synchronisées: $PHP_VERSION${NC}"
fi

# Copier les fichiers depuis Git vers SVN
cd "$GIT_REPO_PATH"
for item in "${FILES_TO_SYNC[@]}"; do
    if [ -e "$item" ]; then
        echo "  ✓ Copie de $item"
        if [ -d "$item" ]; then
            # Pour les dossiers, s'assurer de préserver la structure
            cp -R "$item" "$SVN_TRUNK_PATH/"
            # Vérifier que le dossier existe bien à la destination
            if [ ! -d "$SVN_TRUNK_PATH/$item" ]; then
                echo -e "${YELLOW}  ⚠️  Structure de dossier corrigée pour $item${NC}"
                mkdir -p "$SVN_TRUNK_PATH/$item"
                cp -R "$item"/* "$SVN_TRUNK_PATH/$item/"
            fi
        else
            # Pour les fichiers
            cp "$item" "$SVN_TRUNK_PATH/"
        fi
    else
        echo -e "${YELLOW}  ⚠️  $item n'existe pas dans le dépôt Git${NC}"
    fi
done

# Aller dans le répertoire SVN trunk
cd "$SVN_TRUNK_PATH"

# Ajouter les nouveaux fichiers à SVN
echo -e "${YELLOW}📝 Ajout des nouveaux fichiers à SVN...${NC}"
svn add --force . 2>/dev/null || true

# Supprimer les fichiers qui n'existent plus
echo -e "${YELLOW}🗑️  Suppression des fichiers supprimés...${NC}"
svn status | grep '^!' | awk '{print $2}' | xargs -r svn remove 2>/dev/null || true

# Vérifier s'il y a des changements
if svn status | grep -q '^[AM!D]'; then
    echo -e "${YELLOW}📄 Changements détectés:${NC}"
    svn status

    # Obtenir le dernier commit Git pour le message
    cd "$GIT_REPO_PATH"
    LAST_COMMIT_MSG=$(git log -1 --pretty=format:"%s")
    LAST_COMMIT_HASH=$(git log -1 --pretty=format:"%h")

    # Demander confirmation
    echo -e "${YELLOW}💬 Message de commit SVN: ${NC}$LAST_COMMIT_MSG (Git: $LAST_COMMIT_HASH)"
    read -p "Confirmer le commit SVN? [y/N]: " -n 1 -r
    echo

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        cd "$SVN_TRUNK_PATH"
        svn commit -m "$LAST_COMMIT_MSG (Git: $LAST_COMMIT_HASH)"
        echo -e "${GREEN}✅ Synchronisation terminée avec succès!${NC}"
    else
        echo -e "${YELLOW}⏸️  Synchronisation annulée${NC}"
        cd "$SVN_TRUNK_PATH"
        svn revert -R .
    fi
else
    echo -e "${GREEN}✅ Aucun changement à synchroniser${NC}"
fi

echo -e "${GREEN}🏁 Fin de la synchronisation${NC}"