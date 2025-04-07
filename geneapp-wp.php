<?php
/**
 * Plugin Name: GeneApp WP
 * Description: Intégration de GeneApp avec template de page dédié
 * Version: 1.7.2
 * Author: geneapp-wp.fr
 */

// Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

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

        // Infos partenaire (idéalement à stocker dans les options)
        $partner_id = get_option('geneapp_partner_id', '');
$partner_secret = get_option('geneapp_partner_secret', '');

// Vérifier si les informations de partenaire sont configurées
if (empty($partner_id) || empty($partner_secret)) {
    return '<p>Veuillez configurer les informations de partenaire GeneApp dans les <a href="' . 
           admin_url('options-general.php?page=geneapp-wp-settings') . 
           '">paramètres du plugin</a>.</p>';
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
        wp_register_style('geneapp-wp-styles', plugins_url('assets/css/geneapp.css', __FILE__));
        wp_enqueue_style('geneapp-wp-styles');
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
        $post = get_post();
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ('geneapp-template.php' === $page_template) {
            $template = plugin_dir_path(__FILE__) . 'templates/geneapp-template.php';
        }
        
        return $template;
    }
    
    /**
     * Actions lors de l'activation du plugin
     */
    public function plugin_activation() {
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
        
        // Créer le répertoire d'assets s'il n'existe pas
        $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
            
            // Créer le fichier CSS
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
    }
}

// Initialiser le plugin
$geneapp_wp = new GeneApp_WP();

// Créer la structure de répertoires lors de l'activation
function geneapp_wp_create_directories() {
    // Créer le répertoire des templates
    $template_dir = plugin_dir_path(__FILE__) . 'templates';
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
        
        // Créer le fichier template
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
        file_put_contents($template_dir . '/geneapp-template.php', $template_content);
    }
    
    // Créer le répertoire includes s'il n'existe pas déjà
    $includes_dir = plugin_dir_path(__FILE__) . 'includes';
    if (!file_exists($includes_dir)) {
        wp_mkdir_p($includes_dir);
    }
}

// Action lors de l'activation du plugin
register_activation_hook(__FILE__, 'geneapp_wp_create_directories');