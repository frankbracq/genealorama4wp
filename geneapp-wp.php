<?php
/**
 * Plugin Name: GeneApp-WP
 * Description: Intégration de GeneApp avec template de page dédié et validation des identifiants
 * Version: 1.9.1
 * Author: geneapp-wp.fr
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: geneapp-wp
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier et créer les répertoires nécessaires au fonctionnement du plugin
geneapp_wp_check_directories();

// Inclure les fichiers nécessaires
require_once plugin_dir_path(__FILE__) . 'includes/signature.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

class GeneApp_WP {
    
    /**
     * Constructeur - initialise le plugin
     */
    public function __construct() {
        // Enregistrer le shortcode
        add_shortcode('geneapp_embed', array($this, 'geneapp_shortcode'));
        
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
        add_action('geneapp_daily_validation', array($this, 'perform_daily_validation'));
    }
    
    /**
     * Planifier la validation quotidienne des identifiants
     */
    public function schedule_daily_validation() {
        if (!wp_next_scheduled('geneapp_daily_validation')) {
            wp_schedule_event(time(), 'daily', 'geneapp_daily_validation');
        }
    }
    
    /**
     * Supprimer la tâche planifiée lors de la désactivation
     */
    public function unschedule_daily_validation() {
        wp_clear_scheduled_hook('geneapp_daily_validation');
    }
    
    /**
     * Effectuer la validation quotidienne
     */
    public function perform_daily_validation() {
        if (class_exists('GeneApp_WP_Admin')) {
            $admin = new GeneApp_WP_Admin();
            $admin->validate_credentials();
        }
    }
    
/**
 * Callback du shortcode pour l'intégration de GeneApp
 */
public function geneapp_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Veuillez vous connecter pour accéder à cette fonctionnalité.</p>';
    }

    $current_user = wp_get_current_user();
    $user_data = [
        'id'        => $current_user->ID,
        'email'     => $current_user->user_email, // Email non encodé pour la signature
        'timestamp' => time(),
    ];

    $atts = shortcode_atts([
        'src'         => 'https://genealogie.app/iframe-entry/',
        'width'       => '100%',
        'height'      => 'auto',
        'auto_height' => 'true',
        'fullscreen'  => 'false',
    ], $atts);

    // Récupérer les infos partenaire depuis les options
    $partner_id = get_option('geneapp_partner_id', '');
    $partner_secret = get_option('geneapp_partner_secret', '');
    $validation_status = get_option('geneapp_last_validation_status', '');

    // Vérifier si les informations de partenaire sont configurées
    if (empty($partner_id) || empty($partner_secret)) {
        if (current_user_can('manage_options')) {
            return '<p>Veuillez configurer les informations de partenaire GeneApp dans les <a href="' . 
                admin_url('options-general.php?page=geneapp-wp-settings') . 
                '">paramètres du plugin</a>.</p>';
        } else {
            return '<p>Cette fonctionnalité n\'est pas encore configurée. Veuillez contacter l\'administrateur du site.</p>';
        }
    }

    // Génération de la signature avec l'email non encodé
    $signature = geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret);

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

    $iframe_id = 'wpGeneappIframe_' . uniqid();
    
    // Classe CSS pour le conteneur
    $container_class = 'geneapp-container';
    if ($atts['fullscreen'] === 'true') {
        $container_class .= ' geneapp-fullscreen';
    }
    
    // URL de la page des paramètres pour les admins
    $settings_url = admin_url('options-general.php?page=geneapp-wp-settings');
    $is_admin = current_user_can('manage_options');

    ob_start();
    ?>
    <div class="<?php echo esc_attr($container_class); ?>">
        <?php if ($validation_status === 'invalid' && $is_admin): ?>
        <div class="geneapp-auth-warning" style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; margin-bottom: 10px; border-radius: 4px;">
            <strong>Attention :</strong> Les identifiants GeneApp semblent invalides. 
            <a href="<?php echo esc_url($settings_url); ?>">Veuillez les mettre à jour</a>.
        </div>
        <?php endif; ?>
        
        <iframe id="<?php echo esc_attr($iframe_id); ?>"
                src="<?php echo esc_url($iframe_url); ?>"
                style="width: 100%; min-height: 700px; border: none; display: block;"
                loading="lazy"
                allowfullscreen></iframe>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        const iframe = document.getElementById('<?php echo esc_js($iframe_id); ?>');
        if (iframe) {
          // Hauteur initiale basée sur la fenêtre
          function setInitialHeight() {
            const windowHeight = window.innerHeight;
            const offsetTop = iframe.getBoundingClientRect().top;
            const newHeight = windowHeight - offsetTop - 40;
            iframe.style.height = Math.max(700, newHeight) + "px";
          }
          
          setInitialHeight();
          
          <?php if ($atts['auto_height'] === 'true') : ?>
          // Écouter les messages de l'iframe
          window.addEventListener("message", (event) => {
            // Vérification de l'origine (sécurité)
            if (!event.origin.includes("familystory.live") && !event.origin.includes("genealogie.app")) return;
            
            // Gestion de la hauteur automatique
            if (event.data.geneappHeight && !isNaN(event.data.geneappHeight)) {
              iframe.style.height = event.data.geneappHeight + "px";
            }
            
            // Gestion des erreurs d'authentification
            if (event.data.error === 'invalid_signature' || event.data.error === 'authentication_failed') {
              console.error('Erreur d\'authentification GeneApp:', event.data.error);
              
              <?php if ($is_admin): ?>
              // Pour les admins, afficher un message avec lien vers les paramètres
              const container = iframe.parentElement;
              const warningDiv = container.querySelector('.geneapp-auth-warning');
              
              if (!warningDiv) {
                const warning = document.createElement('div');
                warning.className = 'geneapp-auth-warning';
                warning.style.cssText = 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin-bottom: 10px; border-radius: 4px;';
                warning.innerHTML = '<strong>Erreur d\'authentification :</strong> Les identifiants GeneApp sont invalides. ' +
                                  '<a href="<?php echo esc_js($settings_url); ?>">Mettre à jour les paramètres</a>.';
                container.insertBefore(warning, iframe);
              }
              <?php else: ?>
              // Pour les utilisateurs non-admins, afficher un message générique
              const container = iframe.parentElement;
              container.innerHTML = '<p style="padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">' +
                                  'Une erreur est survenue lors du chargement. Veuillez contacter l\'administrateur du site.</p>';
              <?php endif; ?>
            }
            
            // Gestion du bouton d'accueil - retour à la page d'accueil WordPress
            if (event.data.action === 'returnToHome' && event.data.source === 'geneafan') {
              console.log('Navigation: retour à l\'accueil demandé par GeneaFan');
              window.location.href = '<?php echo esc_js(home_url()); ?>';
            }
          });
          <?php endif; ?>
          
          window.addEventListener("resize", setInitialHeight);
        }
      });
    </script>
    <?php
    return ob_get_clean();
}	
    /**
     * Enregistrer les styles CSS du plugin
     */
    public function register_styles() {
        // Vérifier si le fichier CSS existe, sinon le recréer
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/geneapp.css';
        if (!file_exists($css_file)) {
            $this->create_css_file();
        }
        
        wp_register_style('geneapp-wp-styles', plugins_url('assets/css/geneapp.css', __FILE__), array(), '1.9.1');
        wp_enqueue_style('geneapp-wp-styles');
    }
    
    /**
     * Créer le fichier CSS s'il n'existe pas
     */
    private function create_css_file() {
        $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        $css_content = '/* Styles pour l\'intégration GeneApp */
.geneapp-container {
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  position: relative;
  overflow: hidden;
}

.geneapp-container iframe {
  width: 100%;
  border: none;
  display: block;
  transition: height 0.3s ease;
}

/* Styles pour le template pleine page */
.geneapp-full-page {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body.geneapp-template-page {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}

/* Styles pour les messages d\'avertissement */
.geneapp-auth-warning {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.geneapp-auth-warning a {
  color: #533f03;
  text-decoration: underline;
}';
        file_put_contents($css_dir . '/geneapp.css', $css_content);
    }
    
    /**
     * Ajouter le template à la liste des templates disponibles
     */
    public function add_page_template($templates) {
        $templates['geneapp-template.php'] = 'GeneApp Pleine Page';
        return $templates;
    }
    
    /**
     * Charger le template personnalisé si sélectionné
     */
    public function load_page_template($template) {
        // Vérifier si le fichier template existe, sinon le recréer
        $template_file = plugin_dir_path(__FILE__) . 'templates/geneapp-template.php';
        if (!file_exists($template_file)) {
            geneapp_wp_create_template_file();
        }
        
        $post = get_post();
        if (!$post) {
            return $template;
        }
        
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ('geneapp-template.php' === $page_template) {
            $template = $template_file;
        }
        
        return $template;
    }
    
    /**
     * Actions lors de l'activation du plugin
     */
    public function plugin_activation() {
        // S'assurer que tous les répertoires et fichiers nécessaires existent
        geneapp_wp_check_directories();
        
        // Créer la page GeneApp si elle n'existe pas déjà
        $geneapp_page = get_page_by_path('geneapp');
        
        if (!$geneapp_page) {
            // Créer une nouvelle page
            $page_data = array(
                'post_title'    => 'Généalogie',
                'post_name'     => 'geneapp',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '[geneapp_embed auto_height="true"]',
                'post_author'   => 1,
                'menu_order'    => 0,
                'comment_status' => 'closed'
            );
            
            // Insérer la page
            $page_id = wp_insert_post($page_data);
            
            // Définir le template de la page
            if ($page_id) {
                update_post_meta($page_id, '_wp_page_template', 'geneapp-template.php');
            }
        }
    }
}

// Initialiser le plugin
$geneapp_wp = new GeneApp_WP();

/**
 * Fonction pour vérifier et créer les répertoires nécessaires
 * Cette fonction est appelée à chaque chargement du plugin
 */
function geneapp_wp_check_directories() {
    // Vérifier et créer les répertoires et fichiers
    geneapp_wp_create_template_directory();
    geneapp_wp_create_assets_directory();
    geneapp_wp_create_includes_directory();
}

/**
 * Créer le répertoire des templates et son fichier
 */
function geneapp_wp_create_template_directory() {
    $template_dir = plugin_dir_path(__FILE__) . 'templates';
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
    }
    
    geneapp_wp_create_template_file();
}

/**
 * Créer le fichier template
 */
function geneapp_wp_create_template_file() {
    $template_file = plugin_dir_path(__FILE__) . 'templates/geneapp-template.php';
    if (!file_exists($template_file)) {
        $template_content = '<?php
/**
 * Template pour l\'affichage pleine page de GeneApp
 * 
 * Ce template supprime l\'en-tête et le pied de page pour une expérience immersive
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

<body <?php body_class(\'geneapp-template-page\'); ?>>
    <div class="geneapp-full-page">
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
function geneapp_wp_create_assets_directory() {
    $assets_dir = plugin_dir_path(__FILE__) . 'assets';
    if (!file_exists($assets_dir)) {
        wp_mkdir_p($assets_dir);
    }
    
    $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
    
    $css_file = $css_dir . '/geneapp.css';
    if (!file_exists($css_file)) {
        $css_content = '/* Styles pour l\'intégration GeneApp */
.geneapp-container {
  margin: 0 !important;
  padding: 0 !important;
  width: 100% !important;
  position: relative;
  overflow: hidden;
}

.geneapp-container iframe {
  width: 100%;
  border: none;
  display: block;
  transition: height 0.3s ease;
}

/* Styles pour le template pleine page */
.geneapp-full-page {
  width: 100%;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}

body.geneapp-template-page {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
}

/* Styles pour les messages d\'avertissement */
.geneapp-auth-warning {
  background: #fff3cd;
  border: 1px solid #ffeeba;
  color: #856404;
  padding: 10px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.geneapp-auth-warning a {
  color: #533f03;
  text-decoration: underline;
}';
        file_put_contents($css_file, $css_content);
    }
}

/**
 * Créer le répertoire des includes et ses fichiers
 */
function geneapp_wp_create_includes_directory() {
    $includes_dir = plugin_dir_path(__FILE__) . 'includes';
    if (!file_exists($includes_dir)) {
        wp_mkdir_p($includes_dir);
    }
    
    // Vérifier l'existence du fichier signature.php
    $signature_file = $includes_dir . '/signature.php';
    if (!file_exists($signature_file)) {
        $signature_content = '<?php
/**
 * Fonctions de signature pour GeneApp-WP
 */

// Empêcher l\'accès direct au fichier
if (!defined(\'ABSPATH\')) {
    exit;
}

/**
 * Génère une signature sécurisée pour l\'authentification avec GeneApp
 *
 * @param string $partner_id Identifiant du partenaire
 * @param array $user_data Données utilisateur (id, email, timestamp)
 * @param string $partner_secret Clé secrète du partenaire
 * @return string Signature générée
 */
function geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret) {
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

// Exécuter lors de l'activation du plugin
register_activation_hook(__FILE__, 'geneapp_wp_check_directories');

// Ajouter une vérification lors des mises à jour
add_action('upgrader_process_complete', 'geneapp_wp_upgrade_check', 10, 2);

/**
 * Fonction exécutée après une mise à jour de plugin
 */
function geneapp_wp_upgrade_check($upgrader, $options) {
    if ($options['type'] == 'plugin' && $options['action'] == 'update') {
        // Vérifier si notre plugin est concerné
        foreach($options['plugins'] as $plugin) {
            if($plugin == plugin_basename(__FILE__)) {
                // Recréer les répertoires manquants
                geneapp_wp_check_directories();
                break;
            }
        }
    }
}