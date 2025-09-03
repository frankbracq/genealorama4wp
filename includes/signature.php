<?php
/**
 * Signature functions for Secure Iframe Embed for Genealorama
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates HMAC SHA-256 signature for authentication
 * 
 * @param string $partner_id Partner identifier
 * @param array $user_data User data with keys 'id', 'email', 'timestamp'
 * @param string $partner_secret Partner secret key
 * @return string Signature in hexadecimal
 */
function genealorama_generate_signature($partner_id, $user_data, $partner_secret) {
    // Use non-encoded email for signature
    $email = $user_data['email'];
    
    // String to sign (exact format expected by Worker)
    $stringToSign = "partner_id={$partner_id}&uid={$user_data['id']}&email={$email}&ts={$user_data['timestamp']}";
    
    // HMAC signature calculation
    $signature = hash_hmac('sha256', $stringToSign, $partner_secret);
    
    return $signature;
}

/**
 * Generates complete URL with signed parameters
 * 
 * @param string $base_url Base URL
 * @param string $partner_id Partner identifier
 * @param string $partner_secret Partner secret key
 * @param string $uid User ID
 * @param string $email User email
 * @return string Complete URL with signature
 */
function genealorama_generate_signed_url($base_url, $partner_id, $partner_secret, $uid, $email) {
    $ts = time();

    // Use main function for consistency
    $user_data = [
        'id' => $uid,
        'email' => $email, // Non-encoded email for signature
        'timestamp' => $ts
    ];
    
    // Generate signature with non-encoded email
    $sig = genealorama_generate_signature($partner_id, $user_data, $partner_secret);

    // Build URL with signed parameters
    // Important: manually encode email for URL after calculating signature
    $params = [
        'partner_id' => $partner_id,
        'uid' => $uid,
        'email' => urlencode($email), // URL encoding
        'ts' => $ts,
        'sig' => $sig
    ];

    return $base_url . '?' . http_build_query($params);
}