# Système de validation des identifiants GeneApp-WP

## Vue d'ensemble

Le plugin GeneApp-WP intègre maintenant un système de validation des identifiants partenaire qui permet de vérifier que les clés d'API sont toujours valides. Cette fonctionnalité utilise l'endpoint `/validate-partner` du worker Cloudflare.

## Fonctionnement de la validation

### 1. Endpoint de validation

L'endpoint utilisé est : `https://genealogie.app/validate-partner`

Il accepte les paramètres suivants en GET :
- `partner_id` : L'identifiant du partenaire
- `uid` : Un identifiant utilisateur (pour la validation, on utilise `wp_validation_{user_id}`)
- `email` : L'email de l'utilisateur effectuant la validation
- `ts` : Timestamp Unix actuel
- `sig` : Signature HMAC-SHA256 de la chaîne de validation

### 2. Processus de validation

1. **Génération de la signature** :
   ```php
   $string_to_sign = "partner_id={$partner_id}&uid={$test_uid}&email={$test_email}&ts={$test_timestamp}";
   $signature = hash_hmac('sha256', $string_to_sign, $partner_secret);
   ```

2. **Appel à l'API** :
   - Requête GET avec tous les paramètres
   - Timeout de 10 secondes
   - Réponse attendue en JSON

3. **Traitement de la réponse** :
   - Si `valid: true` : Les identifiants sont valides
   - Si `valid: false` : Les identifiants sont invalides
   - Le champ `error` contient le détail de l'erreur éventuelle

### 3. Stockage des résultats

Les résultats sont stockés dans les options WordPress :
- `geneapp_last_validation_date` : Timestamp de la dernière validation
- `geneapp_last_validation_status` : 'valid' ou 'invalid'

## Types de validation

### Validation manuelle
- Déclenchée par le bouton "Valider maintenant" dans l'interface admin
- Utilise l'email de l'administrateur WordPress actuel
- Résultat affiché immédiatement dans l'interface

### Validation automatique
- Exécutée quotidiennement via wp-cron
- Utilise les mêmes paramètres que la validation manuelle
- Met à jour silencieusement le statut

### Validation lors de la récupération
- Effectuée automatiquement après récupération de nouveaux identifiants
- Marque immédiatement les nouveaux identifiants comme valides

## Gestion des erreurs

Le worker peut retourner plusieurs types d'erreurs :

1. **missing_parameters** : Paramètres manquants dans la requête
2. **expired_link** : Le timestamp est trop ancien (> 5 minutes)
3. **unknown_partner** : L'identifiant partenaire n'existe pas
4. **partner_secret_not_found** : Le secret n'est pas configuré côté serveur
5. **invalid_signature** : La signature HMAC est incorrecte

## Interface utilisateur

### Indicateurs visuels
- ✓ Vert : Identifiants valides
- ⚠ Orange : Identifiants invalides
- Affichage de la date de dernière validation (format relatif)

### Messages d'erreur
- **Administrateurs** : Message détaillé avec lien vers les paramètres
- **Utilisateurs** : Message générique invitant à contacter l'admin

## Sécurité

1. **Protection HMAC** : Toutes les requêtes sont signées
2. **Validation temporelle** : Les requêtes expirent après 5 minutes
3. **CORS** : L'endpoint accepte les requêtes cross-origin
4. **Logs d'erreur** : Les erreurs sont loguées côté WordPress pour debug

## Limitations connues

1. La validation nécessite un utilisateur WordPress connecté (pour l'email)
2. Le délai maximum entre génération et validation est de 300 secondes
3. Les validations trop fréquentes peuvent être limitées par le rate limiting Cloudflare

## Debug

Pour activer les logs de debug :
1. Activer `WP_DEBUG_LOG` dans wp-config.php
2. Les erreurs seront visibles dans `/wp-content/debug.log`
3. Rechercher "GeneApp validation error" dans les logs