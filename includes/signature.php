<?php
/**
 * Fonctions de signature pour GeneApp WP
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Génère une signature HMAC SHA-256 pour l'authentification
 * 
 * @param string $partner_id Identifiant du partenaire
 * @param array $user_data Données utilisateur avec clés 'id', 'email', 'timestamp'
 * @param string $partner_secret Clé secrète du partenaire
 * @return string Signature en hexadécimal
 */
function geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret) {
    // Utiliser l'email non encodé pour la signature
    $email = $user_data['email'];
    
    // Chaîne à signer (format exact attendu par le Worker)
    $stringToSign = "partner_id={$partner_id}&uid={$user_data['id']}&email={$email}&ts={$user_data['timestamp']}";
    
    // Calcul de la signature HMAC
    $signature = hash_hmac('sha256', $stringToSign, $partner_secret);
    
    return $signature;
}

/**
 * Génère une URL complète avec paramètres signés
 * 
 * @param string $base_url URL de base
 * @param string $partner_id Identifiant du partenaire
 * @param string $partner_secret Clé secrète du partenaire
 * @param string $uid ID utilisateur
 * @param string $email Email utilisateur
 * @return string URL complète avec signature
 */
function geneapp_generate_signed_url($base_url, $partner_id, $partner_secret, $uid, $email) {
    $ts = time();

    // Utilisation de la fonction principale pour la cohérence
    $user_data = [
        'id' => $uid,
        'email' => $email, // Email non encodé pour la signature
        'timestamp' => $ts
    ];
    
    // Générer la signature avec l'email non encodé
    $sig = geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret);

    // Construction de l'URL avec les paramètres signés
    // Important: encoder manuellement l'email pour l'URL après avoir calculé la signature
    $params = [
        'partner_id' => $partner_id,
        'uid' => $uid,
        'email' => urlencode($email), // Encodage pour l'URL
        'ts' => $ts,
        'sig' => $sig
    ];

    return $base_url . '?' . http_build_query($params);
}