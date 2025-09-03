<?php
/**
 * Plugin Name: Secure Iframe Embed for Genealorama
 * Description: Secure iframe integration to embed the Genealorama web application into WordPress sites with dedicated page templates and credential validation
 * Version: 2.1.0
 * Author: genealorama.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: geneapp-wp
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier et créer les répertoires nécessaires au fonctionnement du plugin
genealorama_check_directories();

// Inclure les fichiers nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/signature.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

class Secure_Iframe_Embed_For_Genealorama {
    
    /**
     * Constructeur - initialise le plugin
     */
    public function __construct() {
        // Enregistrer le shortcode
        add_shortcode('genealorama_embed', array($this, 'genealorama_shortcode'));
        
        // Ajouter les templates de page
        add_filter('theme_page_templates', array($this, 'add_page_template'));
        add_filter('template_include', array($this, 'load_page_template'));
        
        // Enregistrer les styles CSS
        add_action('wp_enqueue_scripts', array($this, 'register_styles'));
        
        // Créer la page lors de l'activation du plugin
        register_activation_hook(__FILE__, array($this, 'plugin_activation'));
        
        // Planifier la validation quotidienne
        register_activation_hook(__FILE__, array($this, 'schedule_daily_validation'));
        register_deactivation_hook(__FILE__, array($this, 'unschedule_daily_validation'));
        add_action('genealorama_daily_validation', array($this, 'perform_daily_validation'));
    }
    
    /**
     * Planifier la validation quotidienne des identifiants
     */
    public function schedule_daily_validation() {
        if (!wp_next_scheduled('genealorama_daily_validation')) {
            wp_schedule_event(time(), 'daily', 'genealorama_daily_validation');
        }
    }
    
    /**
     * Supprimer la tâche planifiée lors de la désactivation
     */
    public function unschedule_daily_validation() {
        wp_clear_scheduled_hook('genealorama_daily_validation');
    }
    
    /**
     * Effectuer la validation quotidienne
     */
    public function perform_daily_validation() {
        if (class_exists('Secure_Iframe_Embed_For_Genealorama_Admin')) {
            $admin = new Secure_Iframe_Embed_For_Genealorama_Admin();
            $admin->validate_credentials();
        }
    }
    
/**
 * Shortcode callback for Genealorama integration
 */
public function genealorama_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    $current_user = wp_get_current_user();
    $user_data = [
        'id'        => $current_user->ID,
        'email'     => $current_user->user_email, // Email non encodé pour la signature
        'timestamp' => time(),
    ];

    $atts = shortcode_atts([
        'src'         => 'https://app.genealorama.com/iframe-entry/',
        'width'       => '100%',
        'height'      => 'auto',
        'auto_height' => 'true',
        'fullscreen'  => 'false',
    ], $atts);

    // Récupérer les infos partenaire depuis les options
    $partner_id = get_option('genealorama_partner_id', '');
    $partner_secret = get_option('genealorama_partner_secret', '');
    $validation_status = get_option('genealorama_last_validation_status', '');

    // Vérifier si les informations de partenaire sont configurées
    if (empty($partner_id) || empty($partner_secret)) {
        if (current_user_can('manage_options')) {
            return '<p>Please configure the Genealorama partner information in the <a href="' . 
                admin_url('options-general.php?page=secure-iframe-embed-for-genealorama-settings') . 
                '">plugin settings</a>.</p>';
        } else {
            return '<p>This feature is not yet configured. Please contact the site administrator.</p>';
        }
    }

    // Génération de la signature avec l'email non encodé
    $signature = genealorama_generate_signature($partner_id, $user_data, $partner_secret);

    // *** CORRECTION DU DOUBLE ENCODAGE ***
    // http_build_query encode automatiquement les valeurs, donc on ne doit pas
    // encoder l'email manuellement avant
    $params = [
        'partner_id' => $partner_id,
        'uid'        => $user_data['id'],
        'email'      => $user_data['email'], // NE PAS encoder ici, http_build_query le fera
        'ts'         => $user_data['timestamp'],
        'sig'        => $signature,
    ];
    
    // Assemblage de l'URL
    $iframe_url = $atts['src'];
    $iframe_url .= (strpos($iframe_url, '?') === false) ? '?' : '&';
    $iframe_url .= http_build_query($params);

    $iframe_id = 'wpGenearamaIframe_' . uniqid();
    
    // Classe CSS pour le conteneur
    $container_class = 'genealorama-container';
    if ($atts['fullscreen'] === 'true') {
        $container_class .= ' genealorama-fullscreen';
    }
    
    // URL de la page des paramètres pour les admins
    $settings_url = admin_url('options-general.php?page=secure-iframe-embed-for-genealorama-settings');
    $is_admin = current_user_can('manage_options');

    ob_start();
    ?>
    <div class="<?php echo esc_attr($container_class); ?>">
        <?php if ($validation_status === 'invalid' && $is_admin): ?>
        <div class="genealorama-auth-warning" style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
            <strong>Warning:</strong> Genealorama credentials appear to be invalid. 
            <a href="<?php echo esc_url($settings_url); ?>">Please update them</a>.
        </div>
        <?php endif; ?>
        
        <iframe id="<?php echo esc_attr($iframe_id); ?>"
                src="<?php echo esc_url($iframe_url); ?>"
                style="width: 100%; min-height: 700px; border: none; display: block;"
                loading="lazy"
                allowfullscreen></iframe>
    </div>

    <?php
    return ob_get_clean();
}	
    /**
     * Enregistrer les styles CSS du plugin
     */
    public function register_styles() {
        // Vérifier si le fichier CSS existe, sinon le recréer
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/genealorama.css';
        if (!file_exists($css_file)) {
            $this->create_css_file();
        }
        
        wp_register_style('genealorama-embed-styles', plugins_url('assets/css/genealorama.css', __FILE__), array(), '1.9.1');
        wp_enqueue_style('genealorama-embed-styles');
        
        // Only enqueue script if genealorama shortcode is used
        if (has_shortcode(get_post()->post_content, 'genealorama_embed')) {
            wp_enqueue_script('genealorama-embed-js', plugins_url('assets/js/genealorama.js', __FILE__), array(), '1.9.1', true);
            
            // Localize script with admin status and settings URL
            wp_localize_script('genealorama-embed-js', 'genealoramaEmbed', array(
                'isAdmin' => current_user_can('manage_options'),
                'settingsUrl' => admin_url('options-general.php?page=secure-iframe-embed-for-genealorama-settings'),
                'homeUrl' => home_url()
            ));
        }
    }
    
    /**
     * Créer le fichier CSS s'il n'existe pas
     */
    private function create_css_file() {
        $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        $css_content = '/* Styles for Genealorama integration */
.genealorama-container {
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  position: relative;
  overflow: hidden;
}

.genealorama-container iframe {
  width: 100%;
  border: none;
  display: block;
  transition: height 0.3s ease;
}

/* Styles pour le template pleine page */
.genealorama-full-page {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body.genealorama-template-page {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}

/* Styles pour les messages d\'avertissement */
.genealorama-auth-warning {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.genealorama-auth-warning a {
  color: #533f03;
  text-decoration: underline;
}';
        file_put_contents($css_dir . '/genealorama.css', $css_content);
    }
    
    /**
     * Ajouter le template à la liste des templates disponibles
     */
    public function add_page_template($templates) {
        $templates['genealorama-template.php'] = 'Genealorama Full Page';
        return $templates;
    }
    
    /**
     * Charger le template personnalisé si sélectionné
     */
    public function load_page_template($template) {
        // Vérifier si le fichier template existe, sinon le recréer
        $template_file = plugin_dir_path(__FILE__) . 'templates/genealorama-template.php';
        if (!file_exists($template_file)) {
            genealorama_create_template_file();
        }
        
        $post = get_post();
        if (!$post) {
            return $template;
        }
        
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ('genealorama-template.php' === $page_template) {
            $template = $template_file;
        }
        
        return $template;
    }
    
    /**
     * Actions lors de l'activation du plugin
     */
    public function plugin_activation() {
        // S'assurer que tous les répertoires et fichiers nécessaires existent
        genealorama_check_directories();
        
        // Créer la page Genealorama si elle n'existe pas déjà
        $genealorama_page = get_page_by_path('genealorama');
        
        if (!$genealorama_page) {
            // Créer une nouvelle page
            $page_data = array(
                'post_title'    => 'Genealorama',
                'post_name'     => 'genealorama',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '[genealorama_embed auto_height="true"]',
                'post_author'   => 1,
                'menu_order'    => 0,
                'comment_status' => 'closed'
            );
            
            // Insérer la page
            $page_id = wp_insert_post($page_data);
            
            // Définir le template de la page
            if ($page_id) {
                update_post_meta($page_id, '_wp_page_template', 'genealorama-template.php');
            }
        }
    }
}

// Initialiser le plugin
$genealorama_embed = new Secure_Iframe_Embed_For_Genealorama();

// Initialiser l'administration si on est dans l'admin
if (is_admin()) {
    $genealorama_admin = new Secure_Iframe_Embed_For_Genealorama_Admin();
}

/**
 * Fonction pour vérifier et créer les répertoires nécessaires
 * Cette fonction est appelée à chaque chargement du plugin
 */
function genealorama_check_directories() {
    // Vérifier et créer les répertoires et fichiers
    genealorama_create_template_directory();
    genealorama_create_assets_directory();
    genealorama_create_includes_directory();
}

/**
 * Créer le répertoire des templates et son fichier
 */
function genealorama_create_template_directory() {
    $template_dir = plugin_dir_path(__FILE__) . 'templates';
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
    }
    
    genealorama_create_template_file();
}

/**
 * Créer le fichier template
 */
function genealorama_create_template_file() {
    $template_file = plugin_dir_path(__FILE__) . 'templates/genealorama-template.php';
    if (!file_exists($template_file)) {
        $template_content = '<?php
/**
 * Template for Genealorama full page display
 * 
 * This template removes header and footer for an immersive experience
 */

// Désactiver l\'affichage de l\'en-tête et du pied de page
remove_action(\'get_header\', \'wp_enqueue_scripts\');
remove_action(\'wp_head\', \'_wp_render_title_tag\', 1);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo(\'charset\'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(\'genealorama-template-page\'); ?>>
    <div class="genealorama-full-page">
        <?php 
        // Contenu de la page
        while (have_posts()) : the_post();
            the_content();
        endwhile;
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>';
        file_put_contents($template_file, $template_content);
    }
}

/**
 * Créer le répertoire des assets et ses fichiers
 */
function genealorama_create_assets_directory() {
    $assets_dir = plugin_dir_path(__FILE__) . 'assets';
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
    
    $css_file = $css_dir . '/genealorama.css';
    if (!file_exists($css_file)) {
        $css_content = '/* Styles for Genealorama integration */
.genealorama-container {
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  position: relative;
  overflow: hidden;
}

.genealorama-container iframe {
  width: 100%;
  border: none;
  display: block;
  transition: height 0.3s ease;
}

/* Styles pour le template pleine page */
.genealorama-full-page {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body.genealorama-template-page {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}

/* Styles pour les messages d\'avertissement */
.genealorama-auth-warning {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.genealorama-auth-warning a {
  color: #533f03;
  text-decoration: underline;
}';
        file_put_contents($css_file, $css_content);
    }
}

/**
 * Créer le répertoire des includes et ses fichiers
 */
function genealorama_create_includes_directory() {
    $includes_dir = plugin_dir_path(__FILE__) . 'includes';
    if (!file_exists($includes_dir)) {
        wp_mkdir_p($includes_dir);
    }
    
    // Vérifier l'existence du fichier signature.php
    $signature_file = $includes_dir . '/signature.php';
    if (!file_exists($signature_file)) {
        $signature_content = '<?php
/**
 * Signature functions for Secure Iframe Embed for Genealorama
 */

// Empêcher l\'accès direct au fichier
if (!defined(\'ABSPATH\')) {
    exit;
}

/**
 * Generates a secure signature for authentication with Genealorama
 *
 * @param string $partner_id Identifiant du partenaire
 * @param array $user_data Données utilisateur (id, email, timestamp)
 * @param string $partner_secret Clé secrète du partenaire
 * @return string Signature générée
 */
function genealorama_generate_signature($partner_id, $user_data, $partner_secret) {
    // Assurez-vous que l\'email est raw (non encodé pour l\'URL)
    $email = $user_data[\'email\'];
    
    // Chaîne à signer (format exact attendu par le Worker)
    $stringToSign = "partner_id={$partner_id}&uid={$user_data[\'id\']}&email={$email}&ts={$user_data[\'timestamp\']}";
    
    // Log pour debug (à retirer en production)
    // error_log("String to sign: " . $stringToSign);
    
    // Calcul de la signature HMAC
    $signature = hash_hmac(\'sha256\', $stringToSign, $partner_secret);
    
    // Log pour debug (à retirer en production)
    // error_log("Generated signature: " . $signature);
    
    return $signature;
}';
        file_put_contents($signature_file, $signature_content);
    }
    
    // Vérifier l'existence du fichier admin-settings.php
    $admin_file = $includes_dir . '/admin-settings.php';
    if (!file_exists($admin_file)) {
        // Créer le fichier admin-settings.php s'il n'existe pas
        // Note: Ce fichier devrait normalement exister car il est inclus au début du plugin
        // Cette fonction de création ne devrait donc jamais être exécutée
        // error_log('Warning: admin-settings.php was missing and needs to be recreated');
    }
}

// Execute on plugin activation
register_activation_hook(__FILE__, 'genealorama_check_directories');

// Add check during updates
add_action('upgrader_process_complete', 'genealorama_upgrade_check', 10, 2);

/**
 * Function executed after a plugin update
 */
function genealorama_upgrade_check($upgrader, $options) {
    if ($options['type'] == 'plugin' && $options['action'] == 'update') {
        // Vérifier si notre plugin est concerné
        foreach($options['plugins'] as $plugin) {
            if($plugin == plugin_basename(__FILE__)) {
                // Recreate missing directories
                genealorama_check_directories();
                break;
            }
        }
    }
}