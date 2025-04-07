<?php
/**
 * Page d'options pour le plugin GeneApp WP
 */

if (!defined('ABSPATH')) {
    exit;
}

class GeneApp_WP_Admin {
    
    /**
     * Initialisation de la page d'administration
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        
        // Ajouter le lien "Paramètres" dans la liste des plugins
        $plugin_basename = plugin_basename(dirname(dirname(__FILE__)) . '/geneapp-wp.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_settings_link'));
        
        // Ajouter le gestionnaire AJAX
        add_action('wp_ajax_geneapp_get_credentials', array($this, 'ajax_get_credentials'));
    }
    
    /**
     * Gestionnaire AJAX pour récupérer automatiquement les identifiants
     */
    public function ajax_get_credentials() {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'geneapp_auto_credentials')) {
            wp_send_json_error(array('message' => 'Erreur de sécurité, veuillez rafraîchir la page.'));
        }
        
        // Vérifier les données
        if (empty($_POST['email']) || empty($_POST['domain'])) {
            wp_send_json_error(array('message' => 'Email ou domaine manquant.'));
        }
        
        $email = sanitize_email($_POST['email']);
        $domain = sanitize_text_field($_POST['domain']);
        
        // Contacter l'API Cloudflare Worker avec des données JSON
        $response = wp_remote_post('https://partner.genealogie.app/register', array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode(array(
                'email' => $email,
                'domain' => $domain,
                'source' => 'wordpress_plugin',
            )),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Erreur de connexion : ' . $response->get_error_message()));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (wp_remote_retrieve_response_code($response) !== 200 && wp_remote_retrieve_response_code($response) !== 201) {
            $error_message = isset($body['message']) ? $body['message'] : 'Erreur inconnue lors de la récupération des identifiants.';
            wp_send_json_error(array('message' => $error_message));
        }
        
        // Succès, retourner les identifiants
        wp_send_json_success(array(
            'partner_id' => $body['partner_id'],
            'partner_secret' => $body['partner_secret'],
        ));
    }
    
    /**
     * Ajouter le lien "Paramètres" dans la liste des plugins
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=geneapp-wp-settings') . '">' . __('Paramètres') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Ajouter le menu dans l'administration
     */
    public function add_admin_menu() {
        add_options_page(
            'Paramètres GeneApp',
            'GeneApp',
            'manage_options',
            'geneapp-wp-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enregistrer les paramètres
     */
    public function register_settings() {
        register_setting('geneapp_wp_settings', 'geneapp_partner_id');
        register_setting('geneapp_wp_settings', 'geneapp_partner_secret');
        register_setting('geneapp_wp_settings', 'geneapp_iframe_auto_height', array(
            'type' => 'boolean',
            'default' => true,
        ));
        
        add_settings_section(
            'geneapp_wp_main_section',
            'Paramètres d\'intégration',
            array($this, 'main_section_callback'),
            'geneapp-wp-settings'
        );
        
        add_settings_field(
            'geneapp_partner_id',
            'Identifiant Partenaire',
            array($this, 'partner_id_callback'),
            'geneapp-wp-settings',
            'geneapp_wp_main_section'
        );
        
        add_settings_field(
            'geneapp_partner_secret',
            'Clé Secrète',
            array($this, 'partner_secret_callback'),
            'geneapp-wp-settings',
            'geneapp_wp_main_section'
        );
        
        add_settings_field(
            'geneapp_iframe_auto_height',
            'Hauteur automatique',
            array($this, 'auto_height_callback'),
            'geneapp-wp-settings',
            'geneapp_wp_main_section'
        );
    }
    
    /**
     * Callback pour la section principale
     */
    public function main_section_callback() {
        echo '<p>Configurez les paramètres de connexion à GeneApp :</p>';
    }
    
    /**
     * Callback pour l'ID partenaire
     */
    public function partner_id_callback() {
        $value = get_option('geneapp_partner_id', '');
        echo '<input type="text" id="geneapp_partner_id" name="geneapp_partner_id" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">L\'identifiant fourni par GeneApp ou obtenu automatiquement ci-dessus.</p>';
    }
    
    /**
     * Callback pour la clé secrète
     */
    public function partner_secret_callback() {
        $value = get_option('geneapp_partner_secret', '');
        echo '<div style="position: relative; display: inline-block;">';
        echo '<input type="password" id="geneapp_partner_secret" name="geneapp_partner_secret" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<button type="button" id="geneapp_toggle_secret" class="button button-secondary" style="position: absolute; right: 2px; top: 1px; height: 30px; border: none; background: transparent; cursor: pointer;" aria-label="Afficher/masquer la clé">';
        echo '<span class="dashicons dashicons-visibility"></span>';
        echo '</button>';
        echo '</div>';
        echo '<p class="description">La clé secrète fournie par GeneApp ou obtenue automatiquement ci-dessus.</p>';
        
        // Script JavaScript pour gérer l'affichage/masquage
        ?>
        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('geneapp_toggle_secret');
            const secretInput = document.getElementById('geneapp_partner_secret');
            const toggleIcon = toggleButton.querySelector('.dashicons');
            
            toggleButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Changer le type de l'input
                const type = secretInput.getAttribute('type') === 'password' ? 'text' : 'password';
                secretInput.setAttribute('type', type);
                
                // Changer l'icône
                if (type === 'text') {
                    toggleIcon.classList.remove('dashicons-visibility');
                    toggleIcon.classList.add('dashicons-hidden');
                } else {
                    toggleIcon.classList.remove('dashicons-hidden');
                    toggleIcon.classList.add('dashicons-visibility');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Callback pour l'option hauteur automatique
     */
    public function auto_height_callback() {
        $value = get_option('geneapp_iframe_auto_height', true);
        echo '<input type="checkbox" id="geneapp_iframe_auto_height" name="geneapp_iframe_auto_height" value="1" ' . checked(1, $value, false) . '>';
        echo '<label for="geneapp_iframe_auto_height">Activer l\'ajustement automatique de la hauteur de l\'iframe</label>';
    }
    
    /**
     * Affichage de la page de paramètres
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card" style="max-width: 800px; margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2>Récupération automatique des identifiants</h2>
                <p>Vous pouvez obtenir automatiquement vos identifiants de partenaire depuis le service GeneApp :</p>
                
                <form id="geneapp-auto-credentials-form">
                    <?php wp_nonce_field('geneapp_auto_credentials', 'geneapp_credentials_nonce'); ?>
                    
                    <p>
                        <label for="geneapp_email">Email associé à votre compte :</label><br>
                        <input type="email" id="geneapp_email" placeholder="votre@email.com" class="regular-text" required>
                    </p>
                    
                    <div id="geneapp-auth-response" style="display: none; margin: 15px 0; padding: 10px; border-left: 4px solid #46b450; background: #f7f7f7;"></div>
                    
                    <p>
                        <button type="submit" class="button button-primary" id="geneapp-get-credentials">
                            <span class="dashicons dashicons-update" style="margin-top: 4px;"></span> 
                            Récupérer mes identifiants
                        </button>
                        <span class="spinner" id="geneapp-spinner" style="float: none; margin-top: 4px;"></span>
                    </p>
                </form>
                
                <script>
                jQuery(document).ready(function($) {
                    $('#geneapp-auto-credentials-form').on('submit', function(e) {
                        e.preventDefault();
                        
                        $('#geneapp-spinner').addClass('is-active');
                        $('#geneapp-get-credentials').prop('disabled', true);
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'geneapp_get_credentials',
                                nonce: $('#geneapp_credentials_nonce').val(),
                                email: $('#geneapp_email').val(),
                                domain: window.location.hostname
                            },
                            success: function(response) {
                                if (response.success) {
                                    $('#geneapp_partner_id').val(response.data.partner_id);
                                    $('#geneapp_partner_secret').val(response.data.partner_secret);
                                    
                                    $('#geneapp-auth-response').html('<p><strong>Identifiants récupérés avec succès!</strong> Enregistrement automatique en cours...</p>')
                                        .css('border-left-color', '#46b450')
                                        .show();
                                    
                                    // Soumettre automatiquement le formulaire principal
                                    setTimeout(function() {
                                        $('form[action="options.php"]').submit();
                                    }, 1500);
                                } else {
                                    $('#geneapp-auth-response').html('<p><strong>Erreur :</strong> ' + response.data.message + '</p>')
                                        .css('border-left-color', '#dc3232')
                                        .show();
                                }
                            },
                            error: function() {
                                $('#geneapp-auth-response').html('<p><strong>Erreur :</strong> Impossible de contacter le serveur. Veuillez réessayer plus tard ou contacter le support.</p>')
                                    .css('border-left-color', '#dc3232')
                                    .show();
                            },
                            complete: function() {
                                $('#geneapp-spinner').removeClass('is-active');
                                $('#geneapp-get-credentials').prop('disabled', false);
                            }
                        });
                    });
                });
                </script>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('geneapp_wp_settings');
                do_settings_sections('geneapp-wp-settings');
                submit_button('Enregistrer les paramètres');
                ?>
            </form>
            
            <hr>
            
            <h2>Utilisation</h2>
            <p>Vous pouvez utiliser le shortcode <code>[geneapp_embed]</code> sur n'importe quelle page.</p>
            <p>Une page <a href="<?php echo esc_url(get_permalink(get_page_by_path('genealogie'))); ?>">Généalogie</a> a été automatiquement créée lors de l'activation du plugin.</p>
            
            <h3>Options du shortcode</h3>
            <ul>
                <li><code>auto_height="true|false"</code> : Active l'ajustement automatique de la hauteur (par défaut: <?php echo get_option('geneapp_iframe_auto_height', true) ? 'true' : 'false'; ?>)</li>
                <li><code>fullscreen="true|false"</code> : Affiche en plein écran (par défaut: false)</li>
            </ul>
        </div>
        <?php
    }
}

// Initialiser la classe d'administration
$geneapp_wp_admin = new GeneApp_WP_Admin();