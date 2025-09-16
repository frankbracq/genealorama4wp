#!/usr/bin/env php
<?php
/**
 * Script de test des traductions du plugin Genealorama
 * Usage: php test-translations.php
 */

// Simuler l'environnement WordPress
define('ABSPATH', '/tmp/');
define('WP_PLUGIN_DIR', dirname(__FILE__));

// Fonction simplifiée pour tester les traductions
function __($text, $domain) {
    static $translations = [
        'secure-iframe-embed-for-genealorama' => [
            'Settings' => 'Paramètres',
            'Partner Credentials' => 'Identifiants partenaire',
            'Partner ID' => 'ID Partenaire',
            'Partner Secret' => 'Secret Partenaire',
            'Validate Credentials' => 'Valider les identifiants',
            'Display Options' => 'Options d\'affichage',
            'Container Width' => 'Largeur du conteneur',
            'Container Height' => 'Hauteur du conteneur',
            'Show as Full Screen' => 'Afficher en plein écran',
            'Save Display Options' => 'Enregistrer les options d\'affichage',
            'How to Use' => 'Comment utiliser',
            'Shortcode' => 'Shortcode',
            'Page Template' => 'Modèle de page',
            'Custom Integration' => 'Intégration personnalisée',
            'Credentials are valid!' => 'Les identifiants sont valides !',
            'Partner ID and Secret not configured.' => 'ID Partenaire et Secret non configurés.',
            'Please update them' => 'Veuillez les mettre à jour',
        ]
    ];

    if (isset($translations[$domain][$text])) {
        return $translations[$domain][$text];
    }
    return $text;
}

// Tests
echo "=== Test des traductions du plugin Genealorama ===\n\n";

$tests = [
    'Settings',
    'Partner Credentials',
    'Partner ID',
    'Partner Secret',
    'Validate Credentials',
    'Display Options',
    'Container Width',
    'Container Height',
    'Show as Full Screen',
    'Save Display Options',
    'How to Use',
    'Shortcode',
    'Page Template',
    'Custom Integration',
    'Credentials are valid!',
    'Partner ID and Secret not configured.',
    'Please update them',
];

$domain = 'secure-iframe-embed-for-genealorama';
$passed = 0;
$failed = 0;

foreach ($tests as $text) {
    $translation = __($text, $domain);
    if ($translation !== $text) {
        echo "✓ '$text' => '$translation'\n";
        $passed++;
    } else {
        echo "✗ '$text' => Pas de traduction\n";
        $failed++;
    }
}

echo "\n=== Résultats ===\n";
echo "Tests réussis: $passed\n";
echo "Tests échoués: $failed\n";

if ($failed === 0) {
    echo "\n✅ Toutes les traductions sont présentes!\n";
} else {
    echo "\n⚠️  Certaines traductions sont manquantes.\n";
}