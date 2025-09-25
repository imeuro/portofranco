<?php
/**
 * Newsletter Manager Class
 * 
 * Gestisce le iscrizioni alla newsletter, salvataggio nel database
 * e funzionalità di esportazione per piattaforme esterne
 *
 * @package portofranco
 * @subpackage newsletter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class PF_Newsletter_Manager {
    
    /**
     * Table name for newsletter subscriptions
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'pf_newsletter_subscriptions';
        
        // Load migration script
        require_once PF_PLUGIN_DIR . 'modules/newsletter/migrate-database.php';
        
        $this->init_hooks();
        $this->create_table();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // AJAX hooks for frontend
        add_action('wp_ajax_pf_newsletter_subscribe', array($this, 'handle_newsletter_subscription'));
        add_action('wp_ajax_nopriv_pf_newsletter_subscribe', array($this, 'handle_newsletter_subscription'));
        
        // AJAX hooks for admin
        add_action('wp_ajax_pf_get_subscription', array($this, 'get_subscription'));
        add_action('wp_ajax_pf_update_subscription', array($this, 'update_subscription'));
        add_action('wp_ajax_pf_delete_subscription', array($this, 'delete_subscription'));
        add_action('wp_ajax_pf_export_mailchimp', array($this, 'export_for_mailchimp'));
        add_action('wp_ajax_pf_export_mailpoet', array($this, 'export_for_mailpoet'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_init', array($this, 'handle_export_actions'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // Activation hook
        register_activation_hook(PF_PLUGIN_FILE, array($this, 'create_table'));
    }
    
    /**
     * Create database table for newsletter subscriptions
     */
    public function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            name varchar(100) DEFAULT '',
            surname varchar(100) DEFAULT '',
            phone varchar(20) DEFAULT '',
            language varchar(5) DEFAULT 'ITA',
            status varchar(20) DEFAULT 'active',
            source varchar(50) DEFAULT 'website',
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            notes text DEFAULT '',
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY language (language),
            KEY subscribed_at (subscribed_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Handle newsletter subscription via AJAX
     */
    public function handle_newsletter_subscription() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'pf_newsletter_nonce')) {
            wp_die('Security check failed');
        }
        
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $surname = sanitize_text_field($_POST['surname'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $language = sanitize_text_field($_POST['language'] ?? 'ITA');
        $source = sanitize_text_field($_POST['source'] ?? 'website_form');
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => 'Email non valida'
            ));
        }
        
        // Check if email already exists
        if ($this->email_exists($email)) {
            wp_send_json_error(array(
                'message' => 'Email già iscritta alla newsletter'
            ));
        }
        
        // Save subscription
        $result = $this->save_subscription($email, $name, $surname, $phone, $language, $source);
        
        if ($result) {
            // Send confirmation email (optional)
            $this->send_confirmation_email($email, $name);
            
            wp_send_json_success(array(
                'message' => 'Iscrizione completata con successo!'
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Errore durante l\'iscrizione. Riprova.'
            ));
        }
    }
    
    /**
     * Save subscription to database
     */
    private function save_subscription($email, $name = '', $surname = '', $phone = '', $language = 'ITA', $source = 'website_form') {
        global $wpdb;
        
        $user_ip = $this->get_user_ip();
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        return $wpdb->insert(
            $this->table_name,
            array(
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'phone' => $phone,
                'language' => $language,
                'status' => 'active',
                'source' => $source,
                'ip_address' => $user_ip,
                'user_agent' => $user_agent,
                'subscribed_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Check if email already exists
     */
    private function email_exists($email) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE email = %s",
            $email
        ));
        
        return $count > 0;
    }
    
    /**
     * Get user IP address
     */
    private function get_user_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    /**
     * Send confirmation email
     */
    private function send_confirmation_email($email, $name = '') {
        $subject = 'Conferma iscrizione newsletter - Portofranco';
        $message = sprintf(
            'Ciao %s,\n\nGrazie per esserti iscritto/a alla nostra newsletter!\n\nRiceverai aggiornamenti su eventi, mostre e novità di Portofranco.\n\nCordiali saluti,\nIl team di Portofranco',
            $name ?: 'Gentile utente'
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($email, $subject, $message, $headers);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            __('Newsletter Portofranco', 'portofranco'),
            __('Newsletter', 'portofranco'),
            'manage_options',
            'pf-newsletter',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'tools_page_pf-newsletter') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('pf-newsletter-admin', PF_PLUGIN_URL . 'modules/newsletter/assets/js/admin.js', array('jquery'), PF_PLUGIN_VERSION, true);
        wp_enqueue_style('pf-newsletter-admin', PF_PLUGIN_URL . 'modules/newsletter/assets/css/admin.css', array(), PF_PLUGIN_VERSION);
        
        // Localize script
        wp_localize_script('pf-newsletter-admin', 'pf_newsletter_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pf_newsletter_admin_nonce')
        ));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function frontend_enqueue_scripts() {
        // Solo su pagine che potrebbero avere il form newsletter
        if (is_page() || is_front_page() || is_home()) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('pf-newsletter-frontend', PF_PLUGIN_URL . 'modules/newsletter/assets/js/frontend.js', array('jquery'), PF_PLUGIN_VERSION, true);
            wp_enqueue_style('pf-newsletter-frontend', PF_PLUGIN_URL . 'modules/newsletter/assets/css/frontend.css', array(), PF_PLUGIN_VERSION);
            
            // Localize script
            wp_localize_script('pf-newsletter-frontend', 'pf_newsletter', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pf_newsletter_nonce')
            ));
        }
    }
    
    /**
     * Handle export actions before any output
     */
    public function handle_export_actions() {
        if (isset($_POST['export_csv']) && wp_verify_nonce($_POST['_wpnonce'], 'pf_newsletter_export')) {
            $this->export_to_csv();
        }
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        global $wpdb;
        
        // Get subscriptions
        $subscriptions = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY subscribed_at DESC"
        );
        
        $total_subscriptions = count($subscriptions);
        $active_subscriptions = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'active'"
        );
        
        ?>
        <div class="wrap">
            <h1><?php _e('Newsletter Portofranco', 'portofranco'); ?></h1>
            
            <div class="pf-newsletter-stats">
                <div class="stat-box">
                    <h3><?php _e('Totale Iscrizioni', 'portofranco'); ?></h3>
                    <span class="stat-number"><?php echo $total_subscriptions; ?></span>
                </div>
                <div class="stat-box">
                    <h3><?php _e('Iscrizioni Attive', 'portofranco'); ?></h3>
                    <span class="stat-number"><?php echo $active_subscriptions; ?></span>
                </div>
            </div>
            
            <div class="pf-newsletter-actions">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pf_newsletter_export'); ?>
                    <button type="submit" name="export_csv" class="button button-primary">
                        Esporta CSV
                    </button>
                </form>
                
                <button type="button" id="export-mailchimp" class="button">
                    Esporta per Mailchimp
                </button>
                
                <button type="button" id="export-mailpoet" class="button">
                    Esporta per MailPoet
                </button>
                
                <a href="<?php echo admin_url('admin.php?page=pf-newsletter&pf_migrate_newsletter=1'); ?>" 
                   class="button" onclick="return confirm('Sei sicuro di voler eseguire la migrazione del database?');">
                    Migra Database
                </a>
            </div>
            
            <div class="pf-newsletter-table">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Telefono</th>
                            <th>Lingua</th>
                            <th>Stato</th>
                            <th>Fonte</th>
                            <th>Data Iscrizione</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscriptions)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">
                                    Nessuna iscrizione trovata.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subscriptions as $subscription): ?>
                                <tr>
                                    <td><?php echo esc_html($subscription->email); ?></td>
                                    <td><?php echo esc_html($subscription->name); ?></td>
                                    <td><?php echo esc_html($subscription->surname); ?></td>
                                    <td><?php echo esc_html($subscription->phone); ?></td>
                                    <td>
                                        <span class="language-<?php echo esc_attr($subscription->language); ?>">
                                            <?php echo strtoupper(esc_html($subscription->language)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-<?php echo esc_attr($subscription->status); ?>">
                                            <?php echo esc_html(ucfirst($subscription->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($subscription->source); ?></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($subscription->subscribed_at)); ?></td>
                                    <td>
                                        <button type="button" class="button button-small edit-subscription" 
                                                data-id="<?php echo $subscription->id; ?>">
                                            Modifica
                                        </button>
                                        <button type="button" class="button button-small delete-subscription" 
                                                data-id="<?php echo $subscription->id; ?>">
                                            Elimina
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * Export subscriptions to CSV
     */
    private function export_to_csv() {
        global $wpdb;
        
        $subscriptions = $wpdb->get_results(
            "SELECT email, name, surname, phone, language, status, source, subscribed_at FROM {$this->table_name} ORDER BY subscribed_at DESC"
        );
        
        $filename = 'newsletter-portofranco-' . date('Y-m-d') . '.csv';
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'Email',
            'Nome',
            'Cognome',
            'Telefono',
            'Lingua',
            'Stato',
            'Fonte',
            'Data Iscrizione'
        ));
        
        // CSV data
        foreach ($subscriptions as $subscription) {
            fputcsv($output, array(
                $subscription->email,
                $subscription->name,
                $subscription->surname,
                $subscription->phone,
                $subscription->language,
                $subscription->status,
                $subscription->source,
                $subscription->subscribed_at
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Get all active subscriptions
     */
    public function get_active_subscriptions() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE status = 'active' ORDER BY subscribed_at DESC"
        );
    }
    
    /**
     * Get subscription count
     */
    public function get_subscription_count($status = 'active') {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
            $status
        ));
    }
    
    /**
     * Get single subscription for editing
     */
    public function get_subscription() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'pf_newsletter_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $subscription_id = intval($_POST['subscription_id']);
        
        global $wpdb;
        $subscription = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $subscription_id
        ));
        
        if ($subscription) {
            wp_send_json_success($subscription);
        } else {
            wp_send_json_error(array('message' => 'Iscrizione non trovata'));
        }
    }
    
    /**
     * Update subscription
     */
    public function update_subscription() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'pf_newsletter_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $subscription_id = intval($_POST['subscription_id']);
        $email = sanitize_email($_POST['email']);
        $name = sanitize_text_field($_POST['name']);
        $surname = sanitize_text_field($_POST['surname']);
        $phone = sanitize_text_field($_POST['phone']);
        $language = sanitize_text_field($_POST['language']);
        $status = sanitize_text_field($_POST['status']);
        $source = sanitize_text_field($_POST['source']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Email non valida'));
        }
        
        global $wpdb;
        $result = $wpdb->update(
            $this->table_name,
            array(
                'email' => $email,
                'name' => $name,
                'surname' => $surname,
                'phone' => $phone,
                'language' => $language,
                'status' => $status,
                'source' => $source,
                'notes' => $notes,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $subscription_id),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Iscrizione aggiornata con successo'));
        } else {
            wp_send_json_error(array('message' => 'Errore nell\'aggiornamento'));
        }
    }
    
    /**
     * Delete subscription
     */
    public function delete_subscription() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'pf_newsletter_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $subscription_id = intval($_POST['subscription_id']);
        
        global $wpdb;
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $subscription_id),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success(array('message' => 'Iscrizione eliminata con successo'));
        } else {
            wp_send_json_error(array('message' => 'Errore nell\'eliminazione'));
        }
    }
    
    /**
     * Export for Mailchimp format
     */
    public function export_for_mailchimp() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_GET['nonce'], 'pf_newsletter_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $subscriptions = $wpdb->get_results(
            "SELECT email, name, surname, phone, language, subscribed_at FROM {$this->table_name} WHERE status = 'active' ORDER BY subscribed_at DESC"
        );
        
        $filename = 'mailchimp-import-' . date('Y-m-d') . '.csv';
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Mailchimp CSV headers
        fputcsv($output, array('Email Address', 'First Name', 'Last Name', 'Phone', 'Language', 'Signup Date'));
        
        // CSV data
        foreach ($subscriptions as $subscription) {
            $first_name = $subscription->name;
            $last_name = $subscription->surname;
            
            fputcsv($output, array(
                $subscription->email,
                $first_name,
                $last_name,
                $subscription->phone,
                strtoupper($subscription->language),
                date('m/d/Y', strtotime($subscription->subscribed_at))
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export for MailPoet format
     */
    public function export_for_mailpoet() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_GET['nonce'], 'pf_newsletter_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $subscriptions = $wpdb->get_results(
            "SELECT email, name, surname, phone, language, subscribed_at FROM {$this->table_name} WHERE status = 'active' ORDER BY subscribed_at DESC"
        );
        
        $filename = 'mailpoet-import-' . date('Y-m-d') . '.csv';
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // MailPoet CSV headers
        fputcsv($output, array('email', 'first_name', 'last_name', 'phone', 'language', 'created_at'));
        
        // CSV data
        foreach ($subscriptions as $subscription) {
            $first_name = $subscription->name;
            $last_name = $subscription->surname;
            
            fputcsv($output, array(
                $subscription->email,
                $first_name,
                $last_name,
                $subscription->phone,
                $subscription->language,
                date('Y-m-d H:i:s', strtotime($subscription->subscribed_at))
            ));
        }
        
        fclose($output);
        exit;
    }
}
