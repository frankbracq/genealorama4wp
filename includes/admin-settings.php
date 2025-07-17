<?php
/**
 * Page d'options pour le plugin GeneApp-WP
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
        
        // Ajouter les gestionnaires AJAX
        add_action('wp_ajax_geneapp_get_credentials', array($this, 'ajax_get_credentials'));
        add_action('wp_ajax_geneapp_validate_credentials', array($this, 'ajax_validate_credentials'));
    }
    
    /**
     * Valider les identifiants auprès de l'API GeneApp
     * Version temporaire simplifiée pour les tests
     */
    public function validate_credentials() {
        $partner_id = get_option('geneapp_partner_id');
        $partner_secret = get_option('geneapp_partner_secret');
        
        if (empty($partner_id) || empty($partner_secret)) {
            // error_log('GeneApp validation: Missing partner credentials');
            return false;
        }
        
        // Pour l'instant, si les identifiants sont présents et que l'iframe fonctionne,
        // on considère qu'ils sont valides
        // TODO: Implémenter la vraie validation quand l'endpoint sera prêt
        
        $is_valid = true; // Temporairement toujours valide si les identifiants existent
        
        // Mettre à jour les métadonnées
        update_option('geneapp_last_validation_date', current_time('timestamp'));
        update_option('geneapp_last_validation_status', $is_valid ? 'valid' : 'invalid');
        
        return $is_valid;
    }
    
    /**
     * Valider les identifiants auprès de l'API GeneApp (version complète)
     * À activer quand le debug sera fait
     */
    public function validate_credentials_full() {
        $partner_id = get_option('geneapp_partner_id');
        $partner_secret = get_option('geneapp_partner_secret');
        
        if (empty($partner_id) || empty($partner_secret)) {
            // error_log('GeneApp validation: Missing partner credentials');
            return false;
        }
        
        // Créer les paramètres de validation (comme pour l'iframe)
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->user_email) {
            // error_log('GeneApp validation: No current user or email');
            return false;
        }
        
        $test_uid = 'wp_validation_' . $current_user->ID;
        $test_email = $current_user->user_email;
        $test_timestamp = time();
        
        // Créer la chaîne à signer (format exact attendu par le Worker)
        $string_to_sign = "partner_id={$partner_id}&uid={$test_uid}&email={$test_email}&ts={$test_timestamp}";
        
        // Log pour debug
        // error_log('GeneApp validation - String to sign: ' . $string_to_sign);
        
        // Calculer la signature HMAC
        $signature = hash_hmac('sha256', $string_to_sign, $partner_secret);
        
        // Construire l'URL de validation avec les paramètres
        $validation_url = add_query_arg(array(
            'partner_id' => $partner_id,
            'uid' => $test_uid,
            'email' => $test_email,
            'ts' => $test_timestamp,
            'sig' => $signature
        ), 'https://genealogie.app/validate-partner');
        
        // Log l'URL de validation (sans la signature complète pour sécurité)
        // error_log('GeneApp validation URL: ' . str_replace($signature, substr($signature, 0, 10) . '...', $validation_url));
        
        // Appeler l'API de validation
        $response = wp_remote_get($validation_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));
        
        $is_valid = false;
        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            
            // error_log('GeneApp validation response code: ' . $response_code);
            // error_log('GeneApp validation response body: ' . $body);
            
            $data = json_decode($body, true);
            $is_valid = isset($data['valid']) && $data['valid'] === true;
            
            // Log pour debug
            if (!$is_valid && isset($data['error'])) {
                // error_log('GeneApp validation error: ' . $data['error']);
            }
        } else {
            // error_log('GeneApp validation error: ' . $response->get_error_message());
        }
        
        // Mettre à jour les métadonnées
        update_option('geneapp_last_validation_date', current_time('timestamp'));
        update_option('geneapp_last_validation_status', $is_valid ? 'valid' : 'invalid');
        
        return $is_valid;
    }
    
    /**
     * Gestionnaire AJAX pour valider les identifiants
     */
    public function ajax_validate_credentials() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'geneapp_validate_nonce')) {
            wp_send_json_error(array('message' => 'Erreur de sécurité'));
        }
        
        $is_valid = $this->validate_credentials();
        
        wp_send_json_success(array(
            'valid' => $is_valid,
            'message' => $is_valid ? 'Identifiants valides' : 'Identifiants invalides',
            'last_check' => $this->get_formatted_validation_date()
        ));
    }
    
    /**
     * Formater la date de validation
     */
    private function get_formatted_validation_date() {
        $timestamp = get_option('geneapp_last_validation_date');
        if (!$timestamp) {
            return 'Jamais';
        }
        
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        
        // Calculer la différence
        $diff = current_time('timestamp') - $timestamp;
        
        if ($diff < 60) {
            return 'Il y a quelques secondes';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return sprintf('Il y a %d minute%s', $minutes, $minutes > 1 ? 's' : '');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return sprintf('Il y a %d heure%s', $hours, $hours > 1 ? 's' : '');
        } else {
            return date_i18n($date_format . ' à ' . $time_format, $timestamp);
        }
    }
    
    /**
     * Gestionnaire AJAX pour récupérer automatiquement les identifiants
     */
    public function ajax_get_credentials() {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'geneapp_auto_credentials')) {
            wp_send_json_error(array('message' => 'Erreur de sécurité, veuillez rafraîchir la page.'));
        }
        
        // Vérifier les données
        if (empty($_POST['email']) || empty($_POST['domain'])) {
            wp_send_json_error(array('message' => 'Email ou domaine manquant.'));
        }
        
        $email = sanitize_email(wp_unslash($_POST['email']));
        $domain = sanitize_text_field(wp_unslash($_POST['domain']));
        
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
        
        // Sauvegarder les identifiants
        update_option('geneapp_partner_id', $body['partner_id']);
        update_option('geneapp_partner_secret', $body['partner_secret']);
        
        // Marquer comme validés maintenant
        update_option('geneapp_last_validation_date', current_time('timestamp'));
        update_option('geneapp_last_validation_status', 'valid');
        
        // Succès, retourner les identifiants
        wp_send_json_success(array(
            'partner_id' => $body['partner_id'],
            'partner_secret' => $body['partner_secret'],
            'validated' => true
        ));
    }
    
    /**
     * Ajouter le lien "Paramètres" dans la liste des plugins
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=geneapp-wp-settings') . '">' . __('Paramètres', 'geneapp-wp') . '</a>';
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
        register_setting('geneapp_wp_settings', 'geneapp_partner_id', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_partner_secret', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_iframe_auto_height', array(
            'type' => 'boolean',
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_last_validation_date', array(
            'sanitize_callback' => 'absint',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_last_validation_status', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        
        add_settings_section(
            'geneapp_wp_main_section',
            'Paramètres d\'intégration',
            array($this, 'main_section_callback'),
            'geneapp-wp-settings'
        );
        
        add_settings_field(
            'geneapp_partner_id',
            'Votre identifiant partenaire',
            array($this, 'partner_id_callback'),
            'geneapp-wp-settings',
            'geneapp_wp_main_section'
        );
        
        add_settings_field(
            'geneapp_partner_secret',
            'Votre clé secrète',
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
        
        // Vérifier si les identifiants sont déjà configurés
        $has_credentials = !empty(get_option('geneapp_partner_id')) && !empty(get_option('geneapp_partner_secret'));
        $last_validation = get_option('geneapp_last_validation_date');
        $validation_status = get_option('geneapp_last_validation_status');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="card" style="max-width: 800px; margin-bottom: 20px; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2>Configuration des identifiants GeneApp</h2>
                
                <?php
                // Afficher un message de succès si les paramètres viennent d'être mis à jour
                if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    echo '<div class="notice notice-success is-dismissible"><p><strong>Succès :</strong> Les paramètres ont été enregistrés avec succès.</p></div>';
                }
                ?>
                
                <?php if ($has_credentials): ?>
                <!-- Affichage du statut de validation -->
                <div id="geneapp-validation-status" class="notice notice-<?php echo esc_attr($validation_status === 'valid' ? 'success' : 'warning'); ?> inline" style="margin: 15px 0;">
                    <p>
                        <?php if ($last_validation): ?>
                            <strong>Dernière validation des identifiants :</strong> 
                            <?php echo esc_html($this->get_formatted_validation_date()); ?>
                            <?php if ($validation_status === 'valid'): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Valides
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: #ffb900;"></span> Invalides
                            <?php endif; ?>
                        <?php else: ?>
                            <strong>Les identifiants n'ont jamais été validés.</strong>
                        <?php endif; ?>
                        
                        <button type="button" id="geneapp-validate-now" class="button button-small" style="margin-left: 10px;">
                            <span class="dashicons dashicons-update" style="font-size: 16px; line-height: 28px;"></span>
                            Valider maintenant
                        </button>
                        <span class="spinner" id="geneapp-validate-spinner" style="float: none; margin: 0 5px;"></span>
                    </p>
                </div>
                <?php endif; ?>
                
                <form action="options.php" method="post" id="geneapp-settings-form">
                    <?php
                    settings_fields('geneapp_wp_settings');
                    ?>
                    
                    <div class="geneapp-integration-container">
                        <div class="geneapp-credentials-section">
                            <!-- Section récupération automatique -->
                            <div class="geneapp-auto-credentials">
                                <p>
                                    <label for="geneapp_email"><strong>Email associé à votre compte :</strong></label><br>
                                    <input type="email" id="geneapp_email" placeholder="votre@email.com" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" class="regular-text" required>
                                    <br><small style="color: #666;">Les identifiants sont liés au domaine <?php echo esc_html(isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : 'localhost'); ?>. Tout administrateur du site peut les récupérer.</small>
                                    <?php wp_nonce_field('geneapp_auto_credentials', 'geneapp_credentials_nonce'); ?>
                                    <?php wp_nonce_field('geneapp_validate_nonce', 'geneapp_validate_nonce_field'); ?>
                                </p>
                                
                                <div id="geneapp-auth-response" style="display: none; margin: 15px 0; padding: 10px; border-left: 4px solid #46b450; background: #f7f7f7;"></div>
                            </div>
                            
                            <!-- Section paramètres -->
                            <table class="form-table" role="presentation">
                                <tr>
                                    <th scope="row"><label for="geneapp_partner_id">Identifiant Partenaire</label></th>
                                    <td><?php $this->partner_id_callback(); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="geneapp_partner_secret">Clé Secrète</label></th>
                                    <td><?php $this->partner_secret_callback(); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Hauteur automatique</th>
                                    <td><?php $this->auto_height_callback(); ?></td>
                                </tr>
                            </table>
                            
                            <!-- Boutons d'action -->
                            <div class="geneapp-action-buttons" style="margin-top: 20px;">
                                <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'): // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                                    <!-- État juste après l'enregistrement -->
                                    <button type="button" class="button button-primary" id="geneapp-get-credentials">
                                        <span class="dashicons dashicons-update" style="margin-top: 4px;"></span> 
                                        Récupérer mes identifiants
                                    </button>
                                    <input type="submit" name="submit" id="geneapp-save-settings" class="button" value="Paramètres enregistrés" disabled>
                                <?php else: ?>
                                    <!-- État normal -->
                                    <button type="button" class="button <?php echo $has_credentials ? 'button-secondary' : 'button-primary'; ?>" id="geneapp-get-credentials">
                                        <span class="dashicons dashicons-update" style="margin-top: 4px;"></span> 
                                        Récupérer mes identifiants
                                    </button>
                                    <input type="submit" name="submit" id="geneapp-save-settings" class="button button-primary" value="Enregistrer les paramètres" <?php echo !$has_credentials ? 'disabled' : ''; ?>>
                                <?php endif; ?>
                                
                                <span class="spinner" id="geneapp-spinner" style="float: none; margin-top: 4px;"></span>
                            </div>
                        </div>
                    </div>
                </form>
                
                <script>
                jQuery(document).ready(function($) {
                    // État initial basé sur la présence d'identifiants
                    let hasCredentials = <?php echo $has_credentials ? 'true' : 'false'; ?>;
                    let isLoading = false;
                    
                    // Fonction pour mettre à jour l'état des boutons
                    function updateButtonsState() {
                        // Si chargement en cours, désactiver les deux boutons
                        if (isLoading) {
                            $('#geneapp-get-credentials').prop('disabled', true);
                            $('#geneapp-save-settings').prop('disabled', true);
                            return;
                        }
                        
                        // Si des identifiants existent déjà, activer l'enregistrement et configurer la récupération
                        if (hasCredentials) {
                            $('#geneapp-save-settings').prop('disabled', false);
                            $('#geneapp-get-credentials').removeClass('button-primary').addClass('button-secondary');
                        } else {
                            // Sinon, activer la récupération et désactiver l'enregistrement
                            $('#geneapp-save-settings').prop('disabled', true);
                            $('#geneapp-get-credentials').removeClass('button-secondary').addClass('button-primary');
                        }
                    }
                    
                    // Au chargement initial, mettre à jour l'état des boutons
                    updateButtonsState();
                    
                    // Gestionnaire pour le bouton de validation
                    $('#geneapp-validate-now').on('click', function(e) {
                        e.preventDefault();
                        
                        const $button = $(this);
                        const $spinner = $('#geneapp-validate-spinner');
                        const $status = $('#geneapp-validation-status');
                        
                        $button.prop('disabled', true);
                        $spinner.addClass('is-active');
                        
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'geneapp_validate_credentials',
                                nonce: $('#geneapp_validate_nonce_field').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Mettre à jour l'affichage
                                    $status.removeClass('notice-warning notice-error').addClass('notice-success');
                                    
                                    let statusHtml = '<p><strong>Dernière validation des identifiants :</strong> ' + 
                                        response.data.last_check;
                                    
                                    if (response.data.valid) {
                                        statusHtml += ' <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Valides';
                                    } else {
                                        statusHtml += ' <span class="dashicons dashicons-warning" style="color: #ffb900;"></span> Invalides';
                                        $status.removeClass('notice-success').addClass('notice-warning');
                                    }
                                    
                                    statusHtml += ' <button type="button" id="geneapp-validate-now" class="button button-small" style="margin-left: 10px;">' +
                                        '<span class="dashicons dashicons-update" style="font-size: 16px; line-height: 28px;"></span> Valider maintenant</button>' +
                                        '<span class="spinner" id="geneapp-validate-spinner" style="float: none; margin: 0 5px;"></span></p>';
                                    
                                    $status.html(statusHtml);
                                    
                                    // Réattacher l'événement au nouveau bouton
                                    $('#geneapp-validate-now').on('click', arguments.callee);
                                }
                            },
                            error: function() {
                                $status.removeClass('notice-success notice-warning').addClass('notice-error');
                                $status.find('p').append(' <em>Erreur lors de la validation</em>');
                            },
                            complete: function() {
                                $button.prop('disabled', false);
                                $spinner.removeClass('is-active');
                            }
                        });
                    });
                    
                    // Gérer le clic sur le bouton de récupération
                    $('#geneapp-get-credentials').on('click', function(e) {
                        e.preventDefault();
                        
                        // Validation de l'email
                        const email = $('#geneapp_email').val();
                        if (!email) {
                            $('#geneapp-auth-response')
                                .html('<p><strong>Erreur :</strong> Veuillez saisir votre email.</p>')
                                .css('border-left-color', '#dc3232')
                                .show();
                            return;
                        }
                        
                        // Mise à jour de l'état de chargement
                        isLoading = true;
                        $('#geneapp-spinner').addClass('is-active');
                        updateButtonsState();
                        
                        // Appel AJAX
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'geneapp_get_credentials',
                                nonce: $('#geneapp_credentials_nonce').val(),
                                email: email,
                                domain: window.location.hostname
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Remplir les champs avec les identifiants récupérés
                                    $('#geneapp_partner_id').val(response.data.partner_id);
                                    $('#geneapp_partner_secret').val(response.data.partner_secret);
                                    
                                    // Mettre à jour le message de réussite
                                    $('#geneapp-auth-response')
                                        .html('<p><strong>Identifiants récupérés avec succès!</strong> Vous pouvez maintenant enregistrer les paramètres.</p>')
                                        .css('border-left-color', '#46b450')
                                        .show();
                                    
                                    // Mettre à jour l'état (identifiants disponibles)
                                    hasCredentials = true;
                                    
                                    // Si la zone de validation existe, la mettre à jour
                                    if ($('#geneapp-validation-status').length === 0 && response.data.validated) {
                                        // Ajouter la zone de validation
                                        const validationHtml = '<div id="geneapp-validation-status" class="notice notice-success inline" style="margin: 15px 0;">' +
                                            '<p><strong>Dernière validation des identifiants :</strong> Il y a quelques secondes ' +
                                            '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Valides ' +
                                            '<button type="button" id="geneapp-validate-now" class="button button-small" style="margin-left: 10px;">' +
                                            '<span class="dashicons dashicons-update" style="font-size: 16px; line-height: 28px;"></span> Valider maintenant</button>' +
                                            '<span class="spinner" id="geneapp-validate-spinner" style="float: none; margin: 0 5px;"></span></p></div>';
                                        
                                        $('.card h2').after(validationHtml);
                                    }
                                } else {
                                    // Afficher le message d'erreur
                                    $('#geneapp-auth-response')
                                        .html('<p><strong>Erreur :</strong> ' + response.data.message + '</p>')
                                        .css('border-left-color', '#dc3232')
                                        .show();
                                }
                            },
                            error: function() {
                                $('#geneapp-auth-response')
                                    .html('<p><strong>Erreur :</strong> Impossible de contacter le serveur. Veuillez réessayer plus tard ou contacter le support.</p>')
                                    .css('border-left-color', '#dc3232')
                                    .show();
                            },
                            complete: function() {
                                // Réinitialiser l'état de chargement
                                isLoading = false;
                                $('#geneapp-spinner').removeClass('is-active');
                                updateButtonsState();
                            }
                        });
                    });
                    
                    // Validation automatique au chargement si les identifiants existent et n'ont jamais été validés
                    <?php if ($has_credentials && !$last_validation): ?>
                    setTimeout(function() {
                        $('#geneapp-validate-now').trigger('click');
                    }, 1000);
                    <?php endif; ?>
                    
                    // Détecter si on vient de sauvegarder des paramètres (via URL)
                    if (window.location.search.includes('settings-updated=true')) {
                        // Le bouton d'enregistrement est désactivé (déjà fait en PHP)
                        // Activer et mettre en évidence le bouton de récupération
                        $('#geneapp-get-credentials')
                            .prop('disabled', false)
                            .removeClass('button-secondary')
                            .addClass('button-primary');
                        
                        // Revenir à un état où l'enregistrement est possible après 3 secondes
                        setTimeout(function() {
                            // Réactiver le bouton d'enregistrement
                            $('#geneapp-save-settings')
                                .val('Enregistrer les paramètres')
                                .prop('disabled', false);
                            
                            // Mettre le bouton de récupération en secondaire
                            $('#geneapp-get-credentials')
                                .removeClass('button-primary')
                                .addClass('button-secondary');
                            
                            // Mettre à jour l'état global des boutons
                            updateButtonsState();
                        }, 3000);
                    }
                    
                    // Lors de la soumission du formulaire
                    $('#geneapp-settings-form').on('submit', function() {
                        $('#geneapp-save-settings').val('Enregistrement...');
                    });
                    
                    // Détection de changements dans les champs d'identifiants
                    $('#geneapp_partner_id, #geneapp_partner_secret').on('input', function() {
                        const hasId = $('#geneapp_partner_id').val().trim() !== '';
                        const hasSecret = $('#geneapp_partner_secret').val().trim() !== '';
                        
                        // Mettre à jour l'état des identifiants
                        hasCredentials = hasId && hasSecret;
                        updateButtonsState();
                    });
                });
                </script>
                
                <style>
                .geneapp-integration-container {
                    margin-top: 15px;
                }
                .geneapp-action-buttons {
                    display: flex;
                    gap: 10px;
                    align-items: center;
                }
                </style>
            </div>
            
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