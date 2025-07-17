# Changelog - GeneApp-WP

## Version 1.9.0 - 2025-07-17
### Nouvelles fonctionnalités
- **Système de validation des identifiants** : Ajout d'un système complet de validation des identifiants partenaire
  - Affichage de la date de dernière validation dans l'interface d'administration
  - Bouton "Valider maintenant" pour vérifier manuellement les identifiants
  - Validation automatique quotidienne des identifiants via tâche cron WordPress
  - Validation automatique lors de la récupération de nouveaux identifiants
  - Indicateurs visuels (icônes et couleurs) pour l'état de validation

- **Gestion améliorée des erreurs d'authentification** :
  - Détection des erreurs d'authentification via messages postMessage de l'iframe
  - Messages d'avertissement différenciés pour les administrateurs et utilisateurs
  - Lien direct vers les paramètres pour les administrateurs en cas d'erreur
  - Affichage d'un avertissement si les identifiants sont marqués comme invalides

### Améliorations techniques
- Ajout des gestionnaires AJAX pour la validation des identifiants
- Nouvelles options WordPress pour stocker l'état et la date de validation
- Styles CSS pour les messages d'avertissement
- Meilleure gestion des erreurs avec messages contextuels

### Impact
- Les administrateurs ont maintenant une visibilité complète sur l'état de leurs identifiants
- Détection rapide des problèmes d'authentification
- Meilleure expérience utilisateur avec des messages d'erreur clairs

## Version 1.8.2 - 2025-07-15
### Modifications majeures
- **Migration de l'URL iframe vers genealogie.app** : L'URL de base pour l'intégration iframe passe de `https://familystory.live/iframe-entry/` à `https://genealogie.app/iframe-entry/` pour une meilleure séparation des responsabilités entre les domaines.
- **GitHub Action automatisée** : Le workflow de build se déclenche maintenant automatiquement lors de modifications du fichier `geneapp-wp.php` sur la branche main.

### Impact
- Les nouvelles installations utiliseront automatiquement genealogie.app
- Les installations existantes devront être mises à jour manuellement
- Amélioration de la clarté architecturale : familystory.live reste dédié au site vitrine

## Version 1.8.1 - 2025-07-15
### Corrections
- **Correction du double encodage de l'email** : Suppression de l'appel à `urlencode()` avant `http_build_query()` qui causait un double encodage de l'email dans l'URL iframe. La fonction `http_build_query()` encode automatiquement les valeurs, donc l'encodage manuel n'était pas nécessaire et causait des problèmes de validation de signature.

### Impact
- L'intégration iframe fonctionne maintenant correctement avec la validation de signature
- Les emails sont encodés une seule fois comme prévu par le standard

## Version 1.8.0
- Version précédente avec double encodage de l'email