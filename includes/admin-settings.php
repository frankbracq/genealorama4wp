<?php
/**
 * Page d'options pour le plugin GeneApp-WP
 * Version avec interface réorganisée et moderne
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
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // Ajouter le lien "Paramètres" dans la liste des plugins
        $plugin_basename = plugin_basename(dirname(dirname(__FILE__)) . '/geneapp-wp.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_settings_link'));
        
        // Ajouter les gestionnaires AJAX
        add_action('wp_ajax_geneapp_get_credentials', array($this, 'ajax_get_credentials'));
        add_action('wp_ajax_geneapp_validate_credentials', array($this, 'ajax_validate_credentials'));
        add_action('wp_ajax_geneapp_save_display_options', array($this, 'ajax_save_display_options'));
    }
    
    /**
     * Enqueue les styles et scripts admin
     */
    public function enqueue_admin_styles($hook) {
        // Seulement sur notre page d'admin
        if ($hook !== 'settings_page_geneapp-wp-settings') {
            return;
        }
        
        // Utiliser Dashicons au lieu de Font Awesome (déjà inclus dans WordPress)
        wp_enqueue_style('dashicons');
        
        // Ajouter nos styles personnalisés
        wp_add_inline_style('wp-admin', $this->get_admin_styles());
    }
    
    /**
     * Obtenir les icônes Dashicons équivalentes
     */
    private function get_icon_class($icon) {
        $icon_map = array(
            'fa-tree' => 'dashicons-admin-site-alt3',
            'fa-plug' => 'dashicons-admin-plugins',
            'fa-check-circle' => 'dashicons-yes-alt',
            'fa-times-circle' => 'dashicons-dismiss',
            'fa-exclamation-circle' => 'dashicons-warning',
            'fa-envelope' => 'dashicons-email',
            'fa-fingerprint' => 'dashicons-id',
            'fa-key' => 'dashicons-privacy',
            'fa-link' => 'dashicons-admin-links',
            'fa-eye' => 'dashicons-visibility',
            'fa-eye-slash' => 'dashicons-hidden',
            'fa-sync-alt' => 'dashicons-update',
            'fa-sliders-h' => 'dashicons-admin-settings',
            'fa-save' => 'dashicons-cloud-saved',
            'fa-rocket' => 'dashicons-star-filled',
            'fa-code' => 'dashicons-editor-code',
            'fa-file-alt' => 'dashicons-media-text',
            'fa-puzzle-piece' => 'dashicons-admin-plugins',
            'fa-external-link-alt' => 'dashicons-external',
            'fa-lightbulb' => 'dashicons-lightbulb',
            'fa-info-circle' => 'dashicons-info',
            'fa-laptop-code' => 'dashicons-desktop',
            'fa-exclamation-triangle' => 'dashicons-warning',
            'fa-globe' => 'dashicons-admin-site-alt',
        );
        
        return isset($icon_map[$icon]) ? $icon_map[$icon] : 'dashicons-marker';
    }
    
    /**
     * Styles CSS personnalisés pour l'admin
     */
    private function get_admin_styles() {
        return '
        /* Variables CSS */
        :root {
            --geneapp-primary: #667eea;
            --geneapp-primary-dark: #5a67d8;
            --geneapp-secondary: #764ba2;
            --geneapp-success: #10b981;
            --geneapp-warning: #f59e0b;
            --geneapp-error: #ef4444;
            --geneapp-info: #3b82f6;
        }
        
        /* Reset et base */
        .geneapp-admin-wrap {
            max-width: 1200px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        /* En-tête principal */
        .geneapp-header {
            background: linear-gradient(135deg, var(--geneapp-primary) 0%, var(--geneapp-secondary) 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        .geneapp-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 50%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(35deg);
        }
        
        .geneapp-header h1 {
            color: white;
            margin: 0;
            font-size: 2.5em;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            z-index: 1;
        }
        
        .geneapp-header p {
            color: rgba(255,255,255,0.9);
            margin-top: 10px;
            font-size: 1.1em;
            position: relative;
            z-index: 1;
        }
        
        /* Layout en grille */
        .geneapp-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 968px) {
            .geneapp-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Cartes */
        .geneapp-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .geneapp-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .geneapp-card.full-width {
            grid-column: 1 / -1;
        }
        
        .geneapp-card h2 {
            color: #1f2937;
            font-size: 1.4em;
            margin: 0 0 25px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .geneapp-card h2 .dashicons {
            color: var(--geneapp-primary);
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        
        /* États de connexion */
        .geneapp-connection-status {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
        }
        
        .geneapp-connection-status.connected {
            background: #d1fae5;
            border-color: #6ee7b7;
        }
        
        .geneapp-connection-status.disconnected {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        
        .geneapp-connection-status.pending {
            background: #fef3c7;
            border-color: #fcd34d;
        }
        
        .geneapp-status-icon {
            font-size: 40px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .geneapp-status-content {
            flex: 1;
        }
        
        .geneapp-status-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .geneapp-status-description {
            color: #6b7280;
            font-size: 0.9em;
        }
        
        /* Affichage du domaine */
        .geneapp-domain-display {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #d1d5db;
            position: relative;
        }
        
        .geneapp-domain-display::before {
            content: "\\f319";
            font-family: "dashicons";
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            color: rgba(0,0,0,0.05);
        }
        
        .geneapp-domain-display .domain {
            font-size: 1.5em;
            font-weight: 700;
            color: #1f2937;
            font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        
        .geneapp-domain-display .description {
            color: #6b7280;
            font-size: 0.9em;
        }
        
        .geneapp-badge {
            background: var(--geneapp-warning);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Formulaires */
        .geneapp-form-group {
            margin-bottom: 25px;
        }
        
        .geneapp-form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .geneapp-form-group input[type="text"],
        .geneapp-form-group input[type="email"],
        .geneapp-form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: #f9fafb;
        }
        
        .geneapp-form-group input:focus {
            outline: none;
            border-color: var(--geneapp-primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .geneapp-form-group input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        .geneapp-form-group .description {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
        }
        
        /* Groupe d\'input avec bouton */
        .geneapp-input-group {
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        
        .geneapp-input-group input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }
        
        .geneapp-input-group button {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            padding: 0 16px;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .geneapp-input-group button:hover {
            background: #e5e7eb;
        }
        
        /* Boutons */
        .geneapp-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .geneapp-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.3s;
        }
        
        .geneapp-btn:hover::before {
            left: 100%;
        }
        
        .geneapp-btn-primary {
            background: var(--geneapp-primary);
            color: white;
        }
        
        .geneapp-btn-primary:hover {
            background: var(--geneapp-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .geneapp-btn-success {
            background: var(--geneapp-success);
            color: white;
        }
        
        .geneapp-btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .geneapp-btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        
        .geneapp-btn-secondary:hover {
            background: #d1d5db;
        }
        
        .geneapp-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .geneapp-btn-lg {
            padding: 16px 32px;
            font-size: 16px;
        }
        
        /* Bannières d\'alerte */
        .geneapp-alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .geneapp-alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .geneapp-alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        .geneapp-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .geneapp-alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        /* Spinner */
        .geneapp-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid #e5e7eb;
            border-top-color: var(--geneapp-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .geneapp-spinner.active {
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Switch/Toggle */
        .geneapp-switch {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 15px 0;
        }
        
        .geneapp-switch input[type="checkbox"] {
            display: none;
        }
        
        .geneapp-switch-label {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            background: #e5e7eb;
            border-radius: 24px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .geneapp-switch-label::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .geneapp-switch input:checked + .geneapp-switch-label {
            background: var(--geneapp-primary);
        }
        
        .geneapp-switch input:checked + .geneapp-switch-label::after {
            transform: translateX(26px);
        }
        
        .geneapp-switch-text {
            color: #374151;
            font-size: 14px;
            user-select: none;
        }
        
        /* Section utilisation */
        .geneapp-usage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .geneapp-usage-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .geneapp-usage-card .dashicons {
            font-size: 40px;
            width: 40px;
            height: 40px;
            color: var(--geneapp-primary);
            margin: 0 auto 15px;
        }
        
        .geneapp-usage-card h4 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 1.1em;
        }
        
        .geneapp-usage-card code {
            background: #374151;
            color: #f3f4f6;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            font-size: 13px;
            margin-top: 10px;
        }
        
        /* Info box */
        .geneapp-info-box {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border: 1px solid #c4b5fd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .geneapp-info-box h4 {
            color: #5b21b6;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .geneapp-info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #6b21a8;
        }
        
        .geneapp-info-box li {
            margin-bottom: 8px;
        }
        
        /* Actions footer */
        .geneapp-actions-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #f3f4f6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .geneapp-admin-wrap {
                margin: 10px;
            }
            
            .geneapp-header {
                padding: 25px;
            }
            
            .geneapp-header h1 {
                font-size: 1.8em;
            }
            
            .geneapp-card {
                padding: 20px;
            }
            
            .geneapp-connection-status {
                flex-direction: column;
                text-align: center;
            }
            
            .geneapp-actions-footer {
                flex-direction: column;
                gap: 15px;
            }
        }
        ';
    }
    
    /**
     * Récupérer le domaine du site WordPress
     */
    private function get_site_domain() {
        $site_url = get_site_url();
        $parsed_url = wp_parse_url($site_url);
        $domain = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        
        // Retirer www. si présent pour normaliser
        $domain = preg_replace('/^www\./', '', $domain);
        
        return $domain;
    }
    
    /**
     * Vérifier si c'est un environnement de développement
     */
    private function is_development_environment() {
        $domain = $this->get_site_domain();
        return ($domain === 'localhost' || strpos($domain, '.local') !== false || strpos($domain, '.test') !== false);
    }
    
    /**
     * Valider les identifiants auprès de l'API GeneApp
     */
    public function validate_credentials() {
        $partner_id = get_option('geneapp_partner_id');
        $partner_secret = get_option('geneapp_partner_secret');
        
        if (empty($partner_id) || empty($partner_secret)) {
            return false;
        }
        
        // Pour l'instant, validation simplifiée
        $is_valid = true;
        
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
            return date_i18n(get_option('date_format') . ' à ' . get_option('time_format'), $timestamp);
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
        
        // Récupérer automatiquement le domaine
        $domain = $this->get_site_domain();
        
        if ($this->is_development_environment()) {
            wp_send_json_error(array('message' => 'Les sites en développement local ne peuvent pas être enregistrés. Veuillez déployer votre site sur un domaine public.'));
        }
        
        // Vérifier l'email
        if (empty($_POST['email'])) {
            wp_send_json_error(array('message' => 'Email manquant.'));
        }
        
        $email = sanitize_email(wp_unslash($_POST['email']));
        $partner_id = $domain;
        
        // Contacter l'API Cloudflare Worker
        $response = wp_remote_post('https://partner.genealogie.app/register', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Origin' => get_site_url(),
            ),
            'body' => json_encode(array(
                'email' => $email,
                'domain' => $domain,
                'partner_id' => $partner_id,
                'source' => 'wordpress_plugin',
                'site_url' => get_site_url(),
                'wp_version' => get_bloginfo('version'),
                'plugin_version' => '1.9.1',
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
        update_option('geneapp_partner_domain', $domain);
        
        // Marquer comme validés
        update_option('geneapp_last_validation_date', current_time('timestamp'));
        update_option('geneapp_last_validation_status', 'valid');
        
        // Succès
        wp_send_json_success(array(
            'partner_id' => $body['partner_id'],
            'partner_secret' => $body['partner_secret'],
            'domain' => $domain,
            'status' => isset($body['status']) ? $body['status'] : 'active',
            'validated' => true
        ));
    }
    
    /**
     * Gestionnaire AJAX pour sauvegarder les options d'affichage
     */
    public function ajax_save_display_options() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'geneapp_display_options')) {
            wp_send_json_error(array('message' => 'Erreur de sécurité'));
        }
        
        // Sauvegarder l'option hauteur automatique
        $auto_height = isset($_POST['auto_height']) && $_POST['auto_height'] === 'true';
        update_option('geneapp_iframe_auto_height', $auto_height);
        
        wp_send_json_success(array(
            'message' => 'Options d\'affichage enregistrées',
            'auto_height' => $auto_height
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
        register_setting('geneapp_wp_settings', 'geneapp_partner_domain', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_iframe_auto_height', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_last_validation_date', array(
            'sanitize_callback' => 'absint',
        ));
        register_setting('geneapp_wp_settings', 'geneapp_last_validation_status', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }
    
    /**
     * Obtenir l'état de connexion
     */
    private function get_connection_status() {
        $has_credentials = !empty(get_option('geneapp_partner_id')) && !empty(get_option('geneapp_partner_secret'));
        $validation_status = get_option('geneapp_last_validation_status');
        $saved_domain = get_option('geneapp_partner_domain', '');
        $current_domain = $this->get_site_domain();
        $domain_changed = !empty($saved_domain) && $saved_domain !== $current_domain;
        
        if (!$has_credentials || $domain_changed) {
            return 'disconnected';
        } elseif ($validation_status === 'valid') {
            return 'connected';
        } else {
            return 'pending';
        }
    }
    
    /**
     * Affichage de la page de paramètres
     */
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Récupérer les données
        $current_domain = $this->get_site_domain();
        $is_dev = $this->is_development_environment();
        $has_credentials = !empty(get_option('geneapp_partner_id')) && !empty(get_option('geneapp_partner_secret'));
        $last_validation = get_option('geneapp_last_validation_date');
        $validation_status = get_option('geneapp_last_validation_status');
        $saved_domain = get_option('geneapp_partner_domain', '');
        $domain_changed = !empty($saved_domain) && $saved_domain !== $current_domain;
        $connection_status = $this->get_connection_status();
        $auto_height = get_option('geneapp_iframe_auto_height', true);
        ?>
        
        <div class="wrap geneapp-admin-wrap">
            <!-- En-tête -->
            <div class="geneapp-header">
                <h1><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-tree')); ?>"></span> GeneApp pour WordPress</h1>
                <p>Avec GeneApp, vous offrez aux visiteurs de votre site une expérience généalogique interactive unique basée sur leurs fichiers GEDCOM.</p>
            </div>
            
            <?php
            // Message de succès après action
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
                echo '<div class="geneapp-alert geneapp-alert-success">
                    <span class="dashicons ' . esc_attr($this->get_icon_class('fa-check-circle')) . '"></span>
                    <div>
                        <strong>Succès !</strong> Vos paramètres ont été enregistrés.
                    </div>
                </div>';
            }
            ?>
            
            <!-- Grille principale -->
            <div class="geneapp-grid">
                <!-- Carte État de connexion -->
                <div class="geneapp-card">
                    <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-plug')); ?>"></span> État de connexion</h2>
                    
                    <?php if ($connection_status === 'connected'): ?>
                    <div class="geneapp-connection-status connected">
                        <div class="geneapp-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-check-circle')); ?>" style="color: var(--geneapp-success);"></span>
                        </div>
                        <div class="geneapp-status-content">
                            <div class="geneapp-status-title">Connecté à GeneApp</div>
                            <div class="geneapp-status-description">
                                Dernière vérification : <?php echo esc_html($this->get_formatted_validation_date()); ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($connection_status === 'pending'): ?>
                    <div class="geneapp-connection-status pending">
                        <div class="geneapp-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-exclamation-circle')); ?>" style="color: var(--geneapp-warning);"></span>
                        </div>
                        <div class="geneapp-status-content">
                            <div class="geneapp-status-title">En attente de validation</div>
                            <div class="geneapp-status-description">
                                Les identifiants doivent être validés
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="geneapp-connection-status disconnected">
                        <div class="geneapp-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-times-circle')); ?>" style="color: var(--geneapp-error);"></span>
                        </div>
                        <div class="geneapp-status-content">
                            <div class="geneapp-status-title">Non connecté</div>
                            <div class="geneapp-status-description">
                                <?php echo $domain_changed ? 'Le domaine a changé' : 'Configuration requise'; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Domaine -->
                    <div class="geneapp-domain-display">
                        <div class="domain">
                            <?php echo esc_html($current_domain); ?>
                            <?php if ($is_dev): ?>
                            <span class="geneapp-badge">Dev</span>
                            <?php endif; ?>
                        </div>
                        <div class="description">
                            Domaine détecté automatiquement
                        </div>
                    </div>
                    
                    <?php if ($domain_changed): ?>
                    <div class="geneapp-alert geneapp-alert-error">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-exclamation-triangle')); ?>"></span>
                        <div>
                            <strong>Domaine modifié !</strong><br>
                            Ancien : <code><?php echo esc_html($saved_domain); ?></code><br>
                            Nouveau : <code><?php echo esc_html($current_domain); ?></code>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($is_dev): ?>
                    <div class="geneapp-alert geneapp-alert-warning">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-laptop-code')); ?>"></span>
                        <div>
                            <strong>Environnement de développement</strong><br>
                            Les sites locaux ne peuvent pas être connectés à GeneApp.
                        </div>
                    </div>
                    <?php elseif (!$has_credentials || $domain_changed): ?>
                    <!-- Formulaire de connexion -->
                    <div class="geneapp-form-group">
                        <label for="geneapp_email">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-envelope')); ?>"></span> Email administrateur
                        </label>
                        <input type="email" 
                               id="geneapp_email" 
                               placeholder="votre@email.com" 
                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                        <div class="description">Utilisé pour créer votre compte partenaire</div>
                    </div>
                    
                    <button type="button" 
                            class="geneapp-btn geneapp-btn-primary geneapp-btn-lg" 
                            id="geneapp-connect-btn"
                            style="width: 100%;">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-link')); ?>"></span>
                        Connecter à GeneApp
                    </button>
                    <?php else: ?>
                    <!-- Identifiants actuels -->
                    <div class="geneapp-form-group">
                        <label>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-fingerprint')); ?>"></span> Identifiant partenaire
                        </label>
                        <input type="text" value="<?php echo esc_attr(get_option('geneapp_partner_id')); ?>" readonly>
                    </div>
                    
                    <div class="geneapp-form-group">
                        <label>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-key')); ?>"></span> Clé secrète
                        </label>
                        <div class="geneapp-input-group">
                            <input type="password" 
                                   id="geneapp_partner_secret_display" 
                                   value="<?php echo esc_attr(get_option('geneapp_partner_secret')); ?>" 
                                   readonly>
                            <button type="button" id="toggle-secret">
                                <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-eye')); ?>"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="geneapp-actions-footer">
                        <button type="button" 
                                class="geneapp-btn geneapp-btn-secondary" 
                                id="geneapp-validate-btn">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-sync-alt')); ?>"></span>
                            Valider la connexion
                        </button>
                        <div class="geneapp-spinner" id="validate-spinner"></div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Spinner et messages -->
                    <div id="connection-message" style="margin-top: 20px;"></div>
                    <div class="geneapp-spinner" id="connection-spinner" style="margin: 20px auto; display: none;"></div>
                </div>
                
                <!-- Carte Options d'affichage -->
                <div class="geneapp-card">
                    <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-sliders-h')); ?>"></span> Options d'affichage</h2>
                    
                    <p style="color: #6b7280; margin-bottom: 20px;">
                        Personnalisez l'affichage de GeneApp sur votre site
                    </p>
                    
                    <div class="geneapp-switch">
                        <input type="checkbox" 
                               id="auto_height_option" 
                               <?php checked($auto_height); ?>>
                        <label for="auto_height_option" class="geneapp-switch-label"></label>
                        <label for="auto_height_option" class="geneapp-switch-text">
                            Ajustement automatique de la hauteur
                        </label>
                    </div>
                    
                    <p style="color: #6b7280; font-size: 13px; margin-top: 10px;">
                        Permet à l'iframe de s'adapter automatiquement au contenu
                    </p>
                    
                    <div class="geneapp-info-box">
                        <h4><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-lightbulb')); ?>"></span> Options du shortcode</h4>
                        <p style="margin: 10px 0;">Vous pouvez personnaliser chaque intégration :</p>
                        <ul style="margin: 0;">
                            <li><code>auto_height="true|false"</code></li>
                            <li><code>fullscreen="true|false"</code></li>
                        </ul>
                    </div>
                    
                    <div class="geneapp-actions-footer">
                        <button type="button" 
                                class="geneapp-btn geneapp-btn-success" 
                                id="save-display-options"
                                <?php echo !$has_credentials ? 'disabled' : ''; ?>>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-save')); ?>"></span>
                            Enregistrer les options
                        </button>
                        <div class="geneapp-spinner" id="options-spinner"></div>
                    </div>
                </div>
            </div>
            
            <!-- Section Utilisation -->
            <div class="geneapp-card full-width">
                <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-rocket')); ?>"></span> Comment utiliser GeneApp</h2>
                
                <div class="geneapp-usage-grid">
                    <div class="geneapp-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-code')); ?>"></span>
                        <h4>Shortcode</h4>
                        <p>Insérez GeneApp n'importe où</p>
                        <code>[geneapp_embed]</code>
                    </div>
                    
                    <div class="geneapp-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-file-alt')); ?>"></span>
                        <h4>Page dédiée</h4>
                        <p>Une page a été créée automatiquement</p>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('geneapp'))); ?>" 
                           target="_blank" 
                           class="geneapp-btn geneapp-btn-secondary" 
                           style="margin-top: 10px;">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-external-link-alt')); ?>"></span> Voir la page
                        </a>
                    </div>
                    
                    <div class="geneapp-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-puzzle-piece')); ?>"></span>
                        <h4>Widget</h4>
                        <p>Ajoutez GeneApp dans vos widgets</p>
                        <small style="color: #9ca3af;">Bientôt disponible</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Nonces WordPress -->
        <?php wp_nonce_field('geneapp_auto_credentials', 'geneapp_credentials_nonce'); ?>
        <?php wp_nonce_field('geneapp_validate_nonce', 'geneapp_validate_nonce_field'); ?>
        <?php wp_nonce_field('geneapp_display_options', 'geneapp_display_nonce'); ?>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle secret visibility
            $('#toggle-secret').on('click', function() {
                const input = $('#geneapp_partner_secret_display');
                const icon = $(this).find('span.dashicons');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('<?php echo esc_js($this->get_icon_class('fa-eye')); ?>').addClass('<?php echo esc_js($this->get_icon_class('fa-eye-slash')); ?>');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('<?php echo esc_js($this->get_icon_class('fa-eye-slash')); ?>').addClass('<?php echo esc_js($this->get_icon_class('fa-eye')); ?>');
                }
            });
            
            // Connexion à GeneApp
            $('#geneapp-connect-btn').on('click', function() {
                const email = $('#geneapp_email').val();
                if (!email) {
                    showMessage('connection-message', 'error', 'Veuillez saisir votre email.');
                    return;
                }
                
                const $btn = $(this);
                const $spinner = $('#connection-spinner');
                
                $btn.prop('disabled', true);
                $spinner.show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'geneapp_get_credentials',
                        nonce: $('#geneapp_credentials_nonce').val(),
                        email: email
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('connection-message', 'success', 
                                'Connexion réussie ! Rechargement de la page...');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showMessage('connection-message', 'error', response.data.message);
                        }
                    },
                    error: function() {
                        showMessage('connection-message', 'error', 
                            'Erreur de connexion au serveur.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.hide();
                    }
                });
            });
            
            // Validation des identifiants
            $('#geneapp-validate-btn').on('click', function() {
                const $btn = $(this);
                const $spinner = $('#validate-spinner');
                
                $btn.prop('disabled', true);
                $spinner.addClass('active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'geneapp_validate_credentials',
                        nonce: $('#geneapp_validate_nonce_field').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('connection-message', 'success', 
                                'Connexion validée avec succès !');
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('active');
                    }
                });
            });
            
            // Sauvegarde des options d'affichage
            $('#save-display-options').on('click', function() {
                const $btn = $(this);
                const $spinner = $('#options-spinner');
                
                $btn.prop('disabled', true);
                $spinner.addClass('active');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'geneapp_save_display_options',
                        nonce: $('#geneapp_display_nonce').val(),
                        auto_height: $('#auto_height_option').is(':checked')
                    },
                    success: function(response) {
                        if (response.success) {
                            showMessage('connection-message', 'success', response.data.message);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.removeClass('active');
                    }
                });
            });
            
            // Fonction pour afficher les messages
            function showMessage(containerId, type, message) {
                const iconMap = {
                    'success': '<?php echo esc_js($this->get_icon_class('fa-check-circle')); ?>',
                    'error': '<?php echo esc_js($this->get_icon_class('fa-times-circle')); ?>',
                    'warning': '<?php echo esc_js($this->get_icon_class('fa-exclamation-triangle')); ?>',
                    'info': '<?php echo esc_js($this->get_icon_class('fa-info-circle')); ?>'
                };
                
                const html = `
                    <div class="geneapp-alert geneapp-alert-${type}">
                        <span class="dashicons ${iconMap[type]}"></span>
                        <div>${message}</div>
                    </div>
                `;
                
                $('#' + containerId).html(html);
            }
            
            // Validation auto au chargement si nécessaire
            <?php if ($has_credentials && !$last_validation && !$domain_changed): ?>
            setTimeout(function() {
                $('#geneapp-validate-btn').trigger('click');
            }, 1000);
            <?php endif; ?>
        });
        </script>
        <?php
    }
}

// Initialiser la classe d'administration
$geneapp_wp_admin = new GeneApp_WP_Admin();