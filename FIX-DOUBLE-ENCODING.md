# Correction du double encodage dans le plugin WordPress

## Problème identifié

Dans le fichier `geneapp-wp.php`, ligne ~84, l'email est encodé manuellement avant d'être passé à `http_build_query()` :

```php
$params = [
    'partner_id' => $partner_id,
    'uid'        => $user_data['id'],
    'email'      => urlencode($user_data['email']), // ❌ Double encodage !
    'ts'         => $user_data['timestamp'],
    'sig'        => $signature,
];
```

`http_build_query()` encode automatiquement toutes les valeurs, donc l'email est encodé deux fois :
1. `urlencode()` : `frank.bracq@gmail.com` → `frank.bracq%40gmail.com`
2. `http_build_query()` : `frank.bracq%40gmail.com` → `frank.bracq%2540gmail.com`

## Solution

Supprimer l'appel à `urlencode()` :

```php
$params = [
    'partner_id' => $partner_id,
    'uid'        => $user_data['id'],
    'email'      => $user_data['email'], // ✅ http_build_query encodera automatiquement
    'ts'         => $user_data['timestamp'],
    'sig'        => $signature,
];
```

## Étapes pour corriger

1. **Dans WordPress**, éditer le fichier du plugin :
   - Aller dans Plugins → Éditeur de fichiers
   - Sélectionner "GeneApp-WP"
   - Modifier la ligne `'email' => urlencode($user_data['email']),`
   - En : `'email' => $user_data['email'],`

2. **Ou via FTP/SSH** :
   - Éditer `/wp-content/plugins/geneapp-wp/geneapp-wp.php`
   - Faire la même modification

3. **Annuler les modifications du worker Cloudflare** :
   - Le worker devrait utiliser l'email décodé (comme prévu initialement)
   - Pas besoin de gérer le double encodage

## Avantages de cette approche

- ✅ Corrige le problème à la source
- ✅ Plus propre et maintenable
- ✅ Le worker Cloudflare reste simple
- ✅ Suit les bonnes pratiques PHP (ne pas encoder avant http_build_query)
