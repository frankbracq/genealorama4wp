<?php
/**
 * Fonctions de génération de signature pour GeneApp WP
 */

/**
 * Génère une signature HMAC SHA-256 pour l'authentification
 * 
 * @param string $partner_id Identifiant du partenaire
 * @param array $user_data Données utilisateur avec clés 'id', 'email', 'timestamp'
 * @param string $partner_secret Clé secrète du partenaire
 * @return string Signature en hexadécimal
 */
function geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret) {
    // Chaîne à signer (identique au middleware)
    $stringToSign = "partner_id={$partner_id}&uid={$user_data['id']}&email={$user_data['email']}&ts={$user_data['timestamp']}";
    
    // Calcul de la signature HMAC
    return hash_hmac('sha256', $stringToSign, $partner_secret);
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
        'email' => $email,
        'timestamp' => $ts
    ];
    $sig = geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret);

    // Construction de l'URL avec les paramètres signés
    $params = http_build_query([
        'partner_id' => $partner_id,
        'uid' => $uid,
        'email' => $email,
        'ts' => $ts,
        'sig' => $sig
    ]);

    return "$base_url?$params";
}