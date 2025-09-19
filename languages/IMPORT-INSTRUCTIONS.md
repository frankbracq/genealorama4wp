# Instructions pour l'import sur translate.wordpress.org

## Fichiers préparés

### 1. **genealorama-translations-fr.zip** (archive complète)
Contient tous les fichiers de traduction française :
- `secure-iframe-embed-for-genealorama.pot` - Template de traduction
- `secure-iframe-embed-for-genealorama-fr_FR.po` - Traductions françaises du plugin
- `secure-iframe-embed-for-genealorama-fr_FR.mo` - Version compilée binaire
- `wp-plugins-secure-iframe-embed-for-genealorama-stable-readme-fr.po` - Traduction du readme

## Étapes pour importer sur translate.wordpress.org

### 1. Accéder à translate.wordpress.org
1. Aller sur : https://translate.wordpress.org/projects/wp-plugins/secure-iframe-embed-for-genealorama/
2. Se connecter avec votre compte WordPress.org

### 2. Sélectionner French (France)
1. Cliquer sur "French (France)" ou "Français"
2. Vous verrez 3 sections :
   - **Development (trunk)**
   - **Stable (latest release)**
   - **Stable Readme (latest release)**

### 3. Importer les traductions

#### Pour Development (trunk) et Stable :
1. Cliquer sur "Import Translations" en bas de la page
2. Sélectionner : `secure-iframe-embed-for-genealorama-fr_FR.po`
3. Cliquer sur "Import"

#### Pour Stable Readme :
1. Dans la section "Stable Readme"
2. Cliquer sur "Import Translations"
3. Sélectionner : `wp-plugins-secure-iframe-embed-for-genealorama-stable-readme-fr.po`
4. Cliquer sur "Import"

### 4. Alternative : Import manuel

Si l'import direct ne fonctionne pas, vous pouvez :
1. Ouvrir chaque section
2. Traduire manuellement en copiant depuis les fichiers .po
3. Valider chaque traduction

### 5. Validation

Une fois importées, les traductions doivent être :
1. **Validées** par un PTE (Project Translation Editor)
2. Ou **approuvées automatiquement** après un certain temps

## Structure des fichiers

### secure-iframe-embed-for-genealorama-fr_FR.po
Contient toutes les chaînes traduisibles du plugin :
- Messages d'erreur
- Interface d'administration
- Messages utilisateur
- Boutons et labels

### wp-plugins-secure-iframe-embed-for-genealorama-stable-readme-fr.po
Contient les traductions pour la page WordPress.org :
- Nom du plugin
- Description courte
- FAQ
- Changelog

## Notes importantes

- Les traductions sont pour la version **2.2.4** du plugin
- Le text domain est : `secure-iframe-embed-for-genealorama`
- Les fichiers sont encodés en **UTF-8**
- Les pluriels français sont gérés (nplurals=2)

## Support

Si vous rencontrez des problèmes :
1. Vérifier que vous êtes connecté à WordPress.org
2. Vérifier que le plugin existe sur WordPress.org
3. Contacter l'équipe polyglots : https://make.wordpress.org/polyglots/