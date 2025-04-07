<?php
/**
 * Plugin Name: GeneApp WP
 * Description: Intégration de GeneApp avec template de page dédié
 * Version: 1.7.5
 * Author: geneapp-wp.fr
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
            'email'     => $current_user->user_email,
            'timestamp' => time(),
        ];

        $atts = shortcode_atts([
            'src'         => 'https://genealogie.app/wp-embed/',
            'width'       => '100%',
            'height'      => 'auto',
            'auto_height' => 'true',
            'fullscreen'  => 'false',
        ], $atts);

        // Récupérer les infos partenaire depuis les options
        $partner_id = get_option('geneapp_partner_id', '');
        $partner_secret = get_option('geneapp_partner_secret', '');

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

        // Génération de la signature
        $signature = geneapp_wp_generate_signature($partner_id, $user_data, $partner_secret);

        // Construction de l'URL
        $iframe_url = add_query_arg([
            'partner_id' => $partner_id,
            'uid'        => $user_data['id'],
            'email'      => urlencode($user_data['email']),
            'ts'         => $user_data['timestamp'],
            'sig'        => $signature,
        ], $atts['src']);

        $iframe_id = 'wpGeneappIframe_' . uniqid();
        
        // Classe CSS pour le conteneur
        $container_class = 'geneapp-container';
        if ($atts['fullscreen'] === 'true') {
            $container_class .= ' geneapp-fullscreen';
        }

        ob_start();
            ?>
            <div class="<?php echo esc_attr($container_class); ?>">
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
                    if (!event.origin.includes("genealogie.app")) return;
                    
                    // Gestion de la hauteur automatique
                    if (event.data.geneappHeight && !isNaN(event.data.geneappHeight)) {
                      iframe.style.height = event.data.geneappHeight + "px";
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
        
        wp_register_style('geneapp-wp-styles', plugins_url('assets/css/geneapp.css', __FILE__));
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
        
        $css_content = <<<CSS
/* Styles pour l'intégration GeneApp */
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
CSS;
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
        $template_content = <<<TEMPLATE
<?php
/**
 * Template pour l'affichage pleine page de GeneApp
 * 
 * Ce template supprime l'en-tête et le pied de page pour une expérience immersive
 */

// Désactiver l'affichage de l'en-tête et du pied de page
remove_action('get_header', 'wp_enqueue_scripts');
remove_action('wp_head', '_wp_render_title_tag', 1);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class('geneapp-template-page'); ?>>
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
</html>
TEMPLATE;
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
        $css_content = <<<CSS
/* Styles pour l'intégration GeneApp */
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
CSS;
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
        $signature_content = <<<PHP
<?php
/**
 * Fonctions de signature pour GeneApp WP
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Génère une signature sécurisée pour l'authentification avec GeneApp
 *
 * @param string \$partner_id Identifiant du partenaire
 * @param array \$user_data Données utilisateur (id, email, timestamp)
 * @param string \$partner_secret Clé secrète du partenaire
 * @return string Signature générée
 */
function geneapp_wp_generate_signature(\$partner_id, \$user_data, \$partner_secret) {
    // Construire la chaîne à signer
    \$string_to_sign = \$partner_id . \$user_data['id'] . \$user_data['email'] . \$user_data['timestamp'];
    
    // Générer la signature avec HMAC SHA256
    \$signature = hash_hmac('sha256', \$string_to_sign, \$partner_secret);
    
    return \$signature;
}
PHP;
        file_put_contents($signature_file, $signature_content);
    }
    
    // Vérifier l'existence du fichier admin-settings.php
    $admin_file = $includes_dir . '/admin-settings.php';
    if (!file_exists($admin_file)) {
        $admin_content = <<<PHP
<?php
/**
 * Page de configuration admin pour GeneApp WP
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Ajouter le menu des options
add_action('admin_menu', 'geneapp_wp_add_admin_menu');
add_action('admin_init', 'geneapp_wp_settings_init');

/**
 * Ajouter la page de menu dans les paramètres
 */
function geneapp_wp_add_admin_menu() {
    add_options_page(
        'GeneApp WP', 
        'GeneApp WP', 
        'manage_options', 
        'geneapp-wp-settings', 
        'geneapp_wp_options_page'
    );
}

/**
 * Initialiser les paramètres
 */
function geneapp_wp_settings_init() {
    register_setting('geneapp_wp_settings', 'geneapp_partner_id');
    register_setting('geneapp_wp_settings', 'geneapp_partner_secret');
    
    add_settings_section(
        'geneapp_wp_settings_section', 
        'Paramètres de connexion GeneApp', 
        'geneapp_wp_settings_section_callback', 
        'geneapp_wp_settings'
    );
    
    add_settings_field(
        'geneapp_partner_id', 
        'Identifiant Partenaire', 
        'geneapp_partner_id_render', 
        'geneapp_wp_settings', 
        'geneapp_wp_settings_section'
    );
    
    add_settings_field(
        'geneapp_partner_secret', 
        'Clé Secrète Partenaire', 
        'geneapp_partner_secret_render', 
        'geneapp_wp_settings', 
        'geneapp_wp_settings_section'
    );
}

/**
 * Rendu du champ Identifiant Partenaire
 */
function geneapp_partner_id_render() {
    \$value = get_option('geneapp_partner_id');
    echo '<input type="text" name="geneapp_partner_id" value="' . esc_attr(\$value) . '" />';
}

/**
 * Rendu du champ Clé Secrète Partenaire
 */
function geneapp_partner_secret_render() {
    \$value = get_option('geneapp_partner_secret');
    echo '<input type="password" name="geneapp_partner_secret" value="' . esc_attr(\$value) . '" />';
}

/**
 * Callback de la section de paramètres
 */
function geneapp_wp_settings_section_callback() {
    echo '<p>Entrez vos informations de partenaire GeneApp. Ces informations sont nécessaires pour établir une connexion sécurisée avec le service GeneApp.</p>';
}

/**
 * Rendu de la page d'options
 */
function geneapp_wp_options_page() {
    ?>
    <div class="wrap">
        <h1>Paramètres GeneApp WP</h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('geneapp_wp_settings');
            do_settings_sections('geneapp_wp_settings');
            submit_button();
            ?>
        </form>
        
        <div class="geneapp-wp-info">
            <h2>Informations</h2>
            <p>Le plugin GeneApp WP crée une page dédiée à l'intégration de GeneApp dans votre site WordPress.</p>
            <p>Pour utiliser ce plugin :</p>
            <ol>
                <li>Entrez vos informations de partenaire ci-dessus</li>
                <li>Utilisez le shortcode <code>[geneapp_embed]</code> sur n'importe quelle page</li>
                <li>Ou utilisez la page "Généalogie" créée automatiquement avec le template pleine page</li>
            </ol>
            <p>Options du shortcode :</p>
            <ul>
                <li><code>auto_height="true|false"</code> - Ajustement automatique de la hauteur</li>
                <li><code>fullscreen="true|false"</code> - Affichage plein écran</li>
            </ul>
        </div>
    </div>
    <style>
        .geneapp-wp-info {
            background: #fff;
            padding: 15px 20px;
            margin-top: 20px;
            border-left: 4px solid #0073aa;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
    </style>
    <?php
}
PHP;
        file_put_contents($admin_file, $admin_content);
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