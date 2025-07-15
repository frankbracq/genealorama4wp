# Changelog - GeneApp WP

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
