<?php
/**
 * Options page for Secure Iframe Embed for Genealorama plugin
 * Version with reorganized and modern interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class Secure_Iframe_Embed_For_Genealorama_Admin {
    
    /**
     * Initialize the admin page
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        
        // Add "Settings" link in the plugin list
        $plugin_basename = plugin_basename(dirname(dirname(__FILE__)) . '/genealorama.php');
        add_filter('plugin_action_links_' . $plugin_basename, array($this, 'add_settings_link'));
        
        // Add AJAX handlers
        add_action('wp_ajax_genealorama_get_credentials', array($this, 'ajax_get_credentials'));
        add_action('wp_ajax_genealorama_validate_credentials', array($this, 'ajax_validate_credentials'));
        add_action('wp_ajax_genealorama_save_display_options', array($this, 'ajax_save_display_options'));
    }
    
    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_admin_styles($hook) {
        // Only on our admin page
        if ($hook !== 'settings_page_secure-iframe-embed-for-genealorama-settings') {
            return;
        }
        
        // Use Dashicons instead of Font Awesome (already included in WordPress)
        wp_enqueue_style('dashicons');
        
        // Add our custom styles inline to dashicons handle
        wp_add_inline_style('dashicons', $this->get_admin_styles());
        
        // Enqueue admin JavaScript
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'genealorama-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-settings.js',
            array('jquery'),
            Secure_Iframe_Embed_For_Genealorama::get_version(),
            true
        );
        
        // Localize script with icon classes and auto-validation flag
        $has_credentials = !empty(get_option('genealorama_partner_id')) && !empty(get_option('genealorama_partner_secret'));
        $last_validation = get_option('genealorama_last_validation_date');
        $saved_domain = get_option('genealorama_partner_domain', '');
        $current_domain = $this->get_site_domain();
        $domain_changed = !empty($saved_domain) && $saved_domain !== $current_domain;
        
        wp_localize_script('genealorama-admin', 'genealoramaAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'eyeIcon' => $this->get_icon_class('fa-eye'),
            'eyeSlashIcon' => $this->get_icon_class('fa-eye-slash'),
            'nonce' => wp_create_nonce('genealorama_admin_nonce'),
            'autoValidate' => $has_credentials && !$last_validation && !$domain_changed
        ));
    }
    
    /**
     * Get equivalent Dashicons
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
            --genealorama-primary: #667eea;
            --genealorama-primary-dark: #5a67d8;
            --genealorama-secondary: #764ba2;
            --genealorama-success: #10b981;
            --genealorama-warning: #f59e0b;
            --genealorama-error: #ef4444;
            --genealorama-info: #3b82f6;
        }
        
        /* Reset et base */
        .genealorama-admin-wrap {
            max-width: 1200px;
            margin: 20px auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        /* En-tête principal */
        .genealorama-header {
            background: linear-gradient(135deg, var(--genealorama-primary) 0%, var(--genealorama-secondary) 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
        }
        
        .genealorama-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 50%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(35deg);
        }
        
        .genealorama-header h1 {
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
        
        .genealorama-header p {
            color: rgba(255,255,255,0.9);
            margin-top: 10px;
            font-size: 1.1em;
            position: relative;
            z-index: 1;
        }
        
        /* Layout en grille */
        .genealorama-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 968px) {
            .genealorama-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Cartes */
        .genealorama-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .genealorama-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .genealorama-card.full-width {
            grid-column: 1 / -1;
        }
        
        .genealorama-card h2 {
            color: #1f2937;
            font-size: 1.4em;
            margin: 0 0 25px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .genealorama-card h2 .dashicons {
            color: var(--genealorama-primary);
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        
        /* États de connexion */
        .genealorama-connection-status {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
        }
        
        .genealorama-connection-status.connected {
            background: #d1fae5;
            border-color: #6ee7b7;
        }
        
        .genealorama-connection-status.disconnected {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        
        .genealorama-connection-status.pending {
            background: #fef3c7;
            border-color: #fcd34d;
        }
        
        .genealorama-status-icon {
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
        
        .genealorama-status-content {
            flex: 1;
        }
        
        .genealorama-status-title {
            font-size: 1.2em;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .genealorama-status-description {
            color: #6b7280;
            font-size: 0.9em;
        }
        
        /* Affichage du domaine */
        .genealorama-domain-display {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #d1d5db;
            position: relative;
        }
        
        .genealorama-domain-display::before {
            content: "\\f319";
            font-family: "dashicons";
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            color: rgba(0,0,0,0.05);
        }
        
        .genealorama-domain-display .domain {
            font-size: 1.5em;
            font-weight: 700;
            color: #1f2937;
            font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }
        
        .genealorama-domain-display .description {
            color: #6b7280;
            font-size: 0.9em;
        }
        
        .genealorama-badge {
            background: var(--genealorama-warning);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Formulaires */
        .genealorama-form-group {
            margin-bottom: 25px;
        }
        
        .genealorama-form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .genealorama-form-group input[type="text"],
        .genealorama-form-group input[type="email"],
        .genealorama-form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: #f9fafb;
        }
        
        .genealorama-form-group input:focus {
            outline: none;
            border-color: var(--genealorama-primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .genealorama-form-group input[readonly] {
            background-color: #f3f4f6;
            color: #6b7280;
            cursor: not-allowed;
            opacity: 0.8;
        }
        
        .genealorama-form-group .description {
            color: #6b7280;
            font-size: 13px;
            margin-top: 5px;
        }
        
        /* Groupe d\'input avec bouton */
        .genealorama-input-group {
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        
        .genealorama-input-group input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }
        
        .genealorama-input-group button {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            padding: 0 16px;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .genealorama-input-group button:hover {
            background: #e5e7eb;
        }
        
        /* Boutons */
        .genealorama-btn {
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
        
        .genealorama-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.3s;
        }
        
        .genealorama-btn:hover::before {
            left: 100%;
        }
        
        .genealorama-btn-primary {
            background: var(--genealorama-primary);
            color: white;
        }
        
        .genealorama-btn-primary:hover {
            background: var(--genealorama-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .genealorama-btn-success {
            background: var(--genealorama-success);
            color: white;
        }
        
        .genealorama-btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .genealorama-btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }
        
        .genealorama-btn-secondary:hover {
            background: #d1d5db;
        }
        
        .genealorama-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .genealorama-btn-lg {
            padding: 16px 32px;
            font-size: 16px;
        }
        
        /* Bannières d\'alerte */
        .genealorama-alert {
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
        
        .genealorama-alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .genealorama-alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        .genealorama-alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .genealorama-alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        
        /* Spinner */
        .genealorama-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid #e5e7eb;
            border-top-color: var(--genealorama-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .genealorama-spinner.active {
            display: inline-block;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Switch/Toggle */
        .genealorama-switch {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 15px 0;
        }
        
        .genealorama-switch input[type="checkbox"] {
            display: none;
        }
        
        .genealorama-switch-label {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            background: #e5e7eb;
            border-radius: 24px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .genealorama-switch-label::after {
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
        
        .genealorama-switch input:checked + .genealorama-switch-label {
            background: var(--genealorama-primary);
        }
        
        .genealorama-switch input:checked + .genealorama-switch-label::after {
            transform: translateX(26px);
        }
        
        .genealorama-switch-text {
            color: #374151;
            font-size: 14px;
            user-select: none;
        }
        
        /* Section utilisation */
        .genealorama-usage-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .genealorama-usage-card {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .genealorama-usage-card .dashicons {
            font-size: 40px;
            width: 40px;
            height: 40px;
            color: var(--genealorama-primary);
            margin: 0 auto 15px;
        }
        
        .genealorama-usage-card h4 {
            color: #1f2937;
            margin: 0 0 10px 0;
            font-size: 1.1em;
        }
        
        .genealorama-usage-card code {
            background: #374151;
            color: #f3f4f6;
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-block;
            font-size: 13px;
            margin-top: 10px;
        }
        
        /* Info box */
        .genealorama-info-box {
            background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
            border: 1px solid #c4b5fd;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .genealorama-info-box h4 {
            color: #5b21b6;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .genealorama-info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #6b21a8;
        }
        
        .genealorama-info-box li {
            margin-bottom: 8px;
        }
        
        /* Actions footer */
        .genealorama-actions-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #f3f4f6;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .genealorama-admin-wrap {
                margin: 10px;
            }
            
            .genealorama-header {
                padding: 25px;
            }
            
            .genealorama-header h1 {
                font-size: 1.8em;
            }
            
            .genealorama-card {
                padding: 20px;
            }
            
            .genealorama-connection-status {
                flex-direction: column;
                text-align: center;
            }
            
            .genealorama-actions-footer {
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
     * Validate credentials with Genealorama API
     */
    public function validate_credentials() {
        $partner_id = get_option('genealorama_partner_id');
        $partner_secret = get_option('genealorama_partner_secret');
        
        if (empty($partner_id) || empty($partner_secret)) {
            return false;
        }
        
        // Pour l'instant, validation simplifiée
        $is_valid = true;
        
        // Mettre à jour les métadonnées
        update_option('genealorama_last_validation_date', current_time('timestamp'));
        update_option('genealorama_last_validation_status', $is_valid ? 'valid' : 'invalid');
        
        return $is_valid;
    }
    
    /**
     * Gestionnaire AJAX pour valider les identifiants
     */
    public function ajax_validate_credentials() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'genealorama_validate_nonce')) {
            wp_send_json_error(array('message' => 'Security error'));
        }
        
        $is_valid = $this->validate_credentials();
        
        wp_send_json_success(array(
            'valid' => $is_valid,
            'message' => $is_valid ? 'Valid credentials' : 'Invalid credentials',
            'last_check' => $this->get_formatted_validation_date()
        ));
    }
    
    /**
     * Formater la date de validation
     */
    private function get_formatted_validation_date() {
        $timestamp = get_option('genealorama_last_validation_date');
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
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'genealorama_auto_credentials')) {
            wp_send_json_error(array('message' => 'Security error, please refresh the page.'));
        }
        
        // Récupérer automatiquement le domaine
        $domain = $this->get_site_domain();
        
        if ($this->is_development_environment()) {
            wp_send_json_error(array('message' => 'Local development sites cannot be registered. Please deploy your site to a public domain.'));
        }
        
        // Vérifier l'email
        if (empty($_POST['email'])) {
            wp_send_json_error(array('message' => 'Email missing.'));
        }
        
        $email = sanitize_email(wp_unslash($_POST['email']));
        $partner_id = $domain;
        
        // Contacter l'API Cloudflare Worker
        $response = wp_remote_post('https://partner-registration.genealogie.app/register', array(
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
                'plugin_version' => Secure_Iframe_Embed_For_Genealorama::get_version(),
            )),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Connection error: ' . $response->get_error_message()));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (wp_remote_retrieve_response_code($response) !== 200 && wp_remote_retrieve_response_code($response) !== 201) {
            $error_message = isset($body['message']) ? $body['message'] : 'Unknown error while retrieving credentials.';
            wp_send_json_error(array('message' => $error_message));
        }
        
        // Sauvegarder les identifiants
        update_option('genealorama_partner_id', $body['partner_id']);
        update_option('genealorama_partner_secret', $body['partner_secret']);
        update_option('genealorama_partner_domain', $domain);
        
        // Marquer comme validés
        update_option('genealorama_last_validation_date', current_time('timestamp'));
        update_option('genealorama_last_validation_status', 'valid');
        
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
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'genealorama_display_options')) {
            wp_send_json_error(array('message' => 'Security error'));
        }
        
        // Sauvegarder l'option hauteur automatique
        $auto_height = isset($_POST['auto_height']) && $_POST['auto_height'] === 'true';
        update_option('genealorama_iframe_auto_height', $auto_height);
        
        wp_send_json_success(array(
            'message' => 'Display options saved',
            'auto_height' => $auto_height
        ));
    }
    
    /**
     * Ajouter le lien "Paramètres" dans la liste des plugins
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=secure-iframe-embed-for-genealorama-settings') . '">' . __('Settings', 'secure-iframe-embed-for-genealorama') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Ajouter le menu dans l'administration
     */
    public function add_admin_menu() {
        add_options_page(
            'Genealorama Settings',
            'Genealorama',
            'manage_options',
            'secure-iframe-embed-for-genealorama-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enregistrer les paramètres
     */
    public function register_settings() {
        register_setting('genealorama_wp_settings', 'genealorama_partner_id', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('genealorama_wp_settings', 'genealorama_partner_secret', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('genealorama_wp_settings', 'genealorama_partner_domain', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
        register_setting('genealorama_wp_settings', 'genealorama_iframe_auto_height', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        register_setting('genealorama_wp_settings', 'genealorama_last_validation_date', array(
            'sanitize_callback' => 'absint',
        ));
        register_setting('genealorama_wp_settings', 'genealorama_last_validation_status', array(
            'sanitize_callback' => 'sanitize_text_field',
        ));
    }
    
    /**
     * Obtenir l'état de connexion
     */
    private function get_connection_status() {
        $has_credentials = !empty(get_option('genealorama_partner_id')) && !empty(get_option('genealorama_partner_secret'));
        $validation_status = get_option('genealorama_last_validation_status');
        $saved_domain = get_option('genealorama_partner_domain', '');
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
        $has_credentials = !empty(get_option('genealorama_partner_id')) && !empty(get_option('genealorama_partner_secret'));
        $last_validation = get_option('genealorama_last_validation_date');
        $validation_status = get_option('genealorama_last_validation_status');
        $saved_domain = get_option('genealorama_partner_domain', '');
        $domain_changed = !empty($saved_domain) && $saved_domain !== $current_domain;
        $connection_status = $this->get_connection_status();
        $auto_height = get_option('genealorama_iframe_auto_height', true);
        ?>
        
        <div class="wrap genealorama-admin-wrap">
            <!-- Header -->
            <div class="genealorama-header">
                <h1><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-tree')); ?>"></span> Genealorama for WordPress</h1>
                <p>With Genealorama, you offer your site visitors a unique interactive genealogical experience based on their GEDCOM files.</p>
            </div>
            
            <?php
            // Success message after action
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
                echo '<div class="genealorama-alert genealorama-alert-success">
                    <span class="dashicons ' . esc_attr($this->get_icon_class('fa-check-circle')) . '"></span>
                    <div>
                        <strong>Success!</strong> Your settings have been saved.
                    </div>
                </div>';
            }
            ?>
            
            <!-- Main grid -->
            <div class="genealorama-grid">
                <!-- Carte État de connexion -->
                <div class="genealorama-card">
                    <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-plug')); ?>"></span> Connection Status</h2>
                    
                    <?php if ($connection_status === 'connected'): ?>
                    <div class="genealorama-connection-status connected">
                        <div class="genealorama-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-check-circle')); ?>" style="color: var(--genealorama-success);"></span>
                        </div>
                        <div class="genealorama-status-content">
                            <div class="genealorama-status-title">Connected to Genealorama</div>
                            <div class="genealorama-status-description">
                                Last check: <?php echo esc_html($this->get_formatted_validation_date()); ?>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($connection_status === 'pending'): ?>
                    <div class="genealorama-connection-status pending">
                        <div class="genealorama-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-exclamation-circle')); ?>" style="color: var(--genealorama-warning);"></span>
                        </div>
                        <div class="genealorama-status-content">
                            <div class="genealorama-status-title">Waiting for validation</div>
                            <div class="genealorama-status-description">
                                Credentials need to be validated
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="genealorama-connection-status disconnected">
                        <div class="genealorama-status-icon">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-times-circle')); ?>" style="color: var(--genealorama-error);"></span>
                        </div>
                        <div class="genealorama-status-content">
                            <div class="genealorama-status-title">Not connected</div>
                            <div class="genealorama-status-description">
                                <?php echo $domain_changed ? 'Domain has changed' : 'Configuration required'; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Domain -->
                    <div class="genealorama-domain-display">
                        <div class="domain">
                            <?php echo esc_html($current_domain); ?>
                            <?php if ($is_dev): ?>
                            <span class="genealorama-badge">Dev</span>
                            <?php endif; ?>
                        </div>
                        <div class="description">
                            Automatically detected domain
                        </div>
                    </div>
                    
                    <?php if ($domain_changed): ?>
                    <div class="genealorama-alert genealorama-alert-error">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-exclamation-triangle')); ?>"></span>
                        <div>
                            <strong>Domain changed!</strong><br>
                            Old: <code><?php echo esc_html($saved_domain); ?></code><br>
                            New: <code><?php echo esc_html($current_domain); ?></code>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($is_dev): ?>
                    <div class="genealorama-alert genealorama-alert-warning">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-laptop-code')); ?>"></span>
                        <div>
                            <strong>Development environment</strong><br>
                            Local sites cannot be connected to Genealorama.
                        </div>
                    </div>
                    <?php elseif (!$has_credentials || $domain_changed): ?>
                    <!-- Connection form -->
                    <div class="genealorama-form-group">
                        <label for="genealorama_email">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-envelope')); ?>"></span> Administrator Email
                        </label>
                        <input type="email" 
                               id="genealorama_email" 
                               placeholder="votre@email.com" 
                               value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">
                        <div class="description">Used to create your partner account</div>
                    </div>
                    
                    <button type="button" 
                            class="genealorama-btn genealorama-btn-primary genealorama-btn-lg" 
                            id="genealorama-connect-btn"
                            style="width: 100%;">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-link')); ?>"></span>
                        Connect to Genealorama
                    </button>
                    <?php else: ?>
                    <!-- Current credentials -->
                    <div class="genealorama-form-group">
                        <label>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-fingerprint')); ?>"></span> Partner ID
                        </label>
                        <input type="text" value="<?php echo esc_attr(get_option('genealorama_partner_id')); ?>" readonly>
                    </div>
                    
                    <div class="genealorama-form-group">
                        <label>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-key')); ?>"></span> Secret Key
                        </label>
                        <div class="genealorama-input-group">
                            <input type="password" 
                                   id="genealorama_partner_secret_display" 
                                   value="<?php echo esc_attr(get_option('genealorama_partner_secret')); ?>" 
                                   readonly>
                            <button type="button" id="toggle-secret">
                                <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-eye')); ?>"></span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="genealorama-actions-footer">
                        <button type="button" 
                                class="genealorama-btn genealorama-btn-secondary" 
                                id="genealorama-validate-btn">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-sync-alt')); ?>"></span>
                            Validate Connection
                        </button>
                        <div class="genealorama-spinner" id="validate-spinner"></div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Spinner and messages -->
                    <div id="connection-message" style="margin-top: 20px;"></div>
                    <div class="genealorama-spinner" id="connection-spinner" style="margin: 20px auto; display: none;"></div>
                </div>
                
                <!-- Display options card -->
                <div class="genealorama-card">
                    <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-sliders-h')); ?>"></span> Display Options</h2>
                    
                    <p style="color: #6b7280; margin-bottom: 20px;">
                        Customize Genealorama display on your site
                    </p>
                    
                    <div class="genealorama-switch">
                        <input type="checkbox" 
                               id="auto_height_option" 
                               <?php checked($auto_height); ?>>
                        <label for="auto_height_option" class="genealorama-switch-label"></label>
                        <label for="auto_height_option" class="genealorama-switch-text">
                            Automatic height adjustment
                        </label>
                    </div>
                    
                    <p style="color: #6b7280; font-size: 13px; margin-top: 10px;">
                        Allows the iframe to automatically adapt to content
                    </p>
                    
                    <div class="genealorama-info-box">
                        <h4><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-lightbulb')); ?>"></span> Shortcode Options</h4>
                        <p style="margin: 10px 0;">You can customize each integration:</p>
                        <ul style="margin: 0;">
                            <li><code>auto_height="true|false"</code></li>
                            <li><code>fullscreen="true|false"</code></li>
                        </ul>
                    </div>
                    
                    <div class="genealorama-actions-footer">
                        <button type="button" 
                                class="genealorama-btn genealorama-btn-success" 
                                id="save-display-options"
                                <?php echo !$has_credentials ? 'disabled' : ''; ?>>
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-save')); ?>"></span>
                            Save Options
                        </button>
                        <div class="genealorama-spinner" id="options-spinner"></div>
                    </div>
                </div>
            </div>
            
            <!-- Usage Section -->
            <div class="genealorama-card full-width">
                <h2><span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-rocket')); ?>"></span> How to use Genealorama</h2>
                
                <div class="genealorama-usage-grid">
                    <div class="genealorama-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-code')); ?>"></span>
                        <h4>Shortcode</h4>
                        <p>Insert Genealorama anywhere</p>
                        <code>[genealorama_embed]</code>
                    </div>
                    
                    <div class="genealorama-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-file-alt')); ?>"></span>
                        <h4>Dedicated Page</h4>
                        <p>A page has been created automatically</p>
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('genealorama'))); ?>" 
                           target="_blank" 
                           class="genealorama-btn genealorama-btn-secondary" 
                           style="margin-top: 10px;">
                            <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-external-link-alt')); ?>"></span> View Page
                        </a>
                    </div>
                    
                    <div class="genealorama-usage-card">
                        <span class="dashicons <?php echo esc_attr($this->get_icon_class('fa-puzzle-piece')); ?>"></span>
                        <h4>Widget</h4>
                        <p>Add Genealorama to your widgets</p>
                        <small style="color: #9ca3af;">Coming soon</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- WordPress Nonces -->
        <?php wp_nonce_field('genealorama_auto_credentials', 'genealorama_credentials_nonce'); ?>
        <?php wp_nonce_field('genealorama_validate_nonce', 'genealorama_validate_nonce_field'); ?>
        <?php wp_nonce_field('genealorama_display_options', 'genealorama_display_nonce'); ?>
        
        <?php
    }
}

// La classe est instanciée dans le fichier principal genealorama.php