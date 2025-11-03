<?php
/**
 * Plugin Name: Portofranco
 * Plugin URI: https://meuro.dev
 * Description: Plugin personalizzato per Portofranco. Gestisce Custom Post Types, funzionalità specifiche e moduli aggiuntivi.
 * Version: 1.0.0
 * Author: Meuro
 * License: GPL v2 or later
 * Text Domain: portofranco
 * Domain Path: /languages
 *
 * @package portofranco
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PF_PLUGIN_FILE', __FILE__);
define('PF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PF_PLUGIN_VERSION', '1.0.0');

/**
 * Main portofranco Plugin Class
 */
class PF_Plugin {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Modules array
     */
    private $modules = array();
    
    /**
     * Get single instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_modules();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Abilita i campi ACF per agenda agli editor
        add_filter('acf/get_field_groups', array($this, 'allow_editors_acf_fields'), 10, 2);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Plugin initialization code here
    }
    
    /**
     * Permetti agli editor di visualizzare i campi ACF per il post type agenda
     * 
     * @param array $field_groups Array di field groups
     * @param array $options Opzioni di contesto (potrebbe includere post_type)
     * @return array Field groups modificati
     */
    public function allow_editors_acf_fields($field_groups, $options = array()) {
        // Se ACF non è attivo, non fare nulla
        if (!function_exists('acf_get_location_rule_types')) {
            return $field_groups;
        }
        
        // Verifica se siamo nella pagina di modifica di un post agenda
        $screen = get_current_screen();
        $post_type = '';
        
        if ($screen && isset($screen->post_type)) {
            $post_type = $screen->post_type;
        } elseif (isset($options['post_type'])) {
            $post_type = $options['post_type'];
        } elseif (isset($_GET['post_type'])) {
            $post_type = sanitize_text_field($_GET['post_type']);
        } elseif (isset($_POST['post_type'])) {
            $post_type = sanitize_text_field($_POST['post_type']);
        } elseif (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $post = get_post($post_id);
            if ($post) {
                $post_type = $post->post_type;
            }
        }
        
        // Se non siamo sul post type agenda, non fare nulla
        if ($post_type !== 'agenda') {
            return $field_groups;
        }
        
        // Verifica se l'utente è admin o editor
        if (!current_user_can('edit_others_posts')) {
            return $field_groups;
        }
        
        // Rimuovi le restrizioni di ruolo dai field groups esistenti
        foreach ($field_groups as &$field_group) {
            if (!isset($field_group['location']) || !is_array($field_group['location'])) {
                continue;
            }
            
            // Itera attraverso i gruppi di location
            foreach ($field_group['location'] as &$location_group) {
                if (!is_array($location_group)) {
                    continue;
                }
                
                // Rimuovi le regole che filtrano per user_role == administrator
                $location_group = array_filter($location_group, function($rule) {
                    if (!is_array($rule)) {
                        return true;
                    }
                    // Rimuovi solo le regole che limitano agli administrator
                    return !(isset($rule['param']) && $rule['param'] === 'user_role' && 
                            isset($rule['operator']) && $rule['operator'] === '==' && 
                            isset($rule['value']) && $rule['value'] === 'administrator');
                });
                
                // Reindirizza l'array per rimuovere le chiavi vuote
                $location_group = array_values($location_group);
            }
        }
        
        return $field_groups;
    }
    
    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain('mrc', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Load modules
     */
    private function load_modules() {
        // Load Custom Post Types module
        $this->load_module('cpt');
        
        // Load Archive Fields module
        $this->load_module('archive-fields');
        
        // Load migration script for archive descriptions
        // $this->load_migration_script();
        
        // Load REST API module
        $this->load_module('rest-api');
        
        // Load Custom Order module
        $this->load_module('custom-order');
        
        // Load Newsletter module
        $this->load_module('newsletter');
    }
    
    /**
     * Load migration script
     
    private function load_migration_script() {
        $migration_file = PF_PLUGIN_DIR . 'modules/archive-fields/migrate-descriptions.php';
        
        if (file_exists($migration_file)) {
            require_once $migration_file;
        }
        
        // Carica anche il file di test in modalità debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $test_file = PF_PLUGIN_DIR . 'modules/archive-fields/test-multilingual.php';
            if (file_exists($test_file)) {
                require_once $test_file;
            }
        }
    }
     */
    
    /**
     * Load a specific module
     */
    private function load_module($module_name) {
        $module_file = PF_PLUGIN_DIR . 'modules/' . $module_name . '/class-' . $module_name . '-manager.php';
        
        if (file_exists($module_file)) {
            require_once $module_file;
            $class_name = 'PF_' . strtoupper($module_name) . '_Manager';
            
            // Correzione per il modulo CPT
            if ($module_name === 'cpt') {
                $class_name = 'PF_CPT_Manager';
            }
            
            // Correzione per il modulo Archive Fields
            if ($module_name === 'archive-fields') {
                $class_name = 'PF_Archive_Fields_Manager';
            }
            
            // Correzione per il modulo REST API
            if ($module_name === 'rest-api') {
                $class_name = 'PF_REST_API_Manager';
            }
            
            // Correzione per il modulo Custom Order
            if ($module_name === 'custom-order') {
                $class_name = 'PF_Custom_Order_Manager';
            }
            
            // Correzione per il modulo Newsletter
            if ($module_name === 'newsletter') {
                $class_name = 'PF_Newsletter_Manager';
            }
            
            if (class_exists($class_name)) {
                $this->modules[$module_name] = new $class_name();
            }
        }
    }
    
    /**
     * Get a module instance
     */
    public function get_module($module_name) {
        return isset($this->modules[$module_name]) ? $this->modules[$module_name] : null;
    }
    
    /**
     * Activation hook
     */
    public static function activate() {
        // Activation code here
        flush_rewrite_rules();
    }
    
    /**
     * Deactivation hook
     */
    public static function deactivate() {
        // Deactivation code here
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function PF_init() {
    return PF_Plugin::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'PF_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('PF_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('PF_Plugin', 'deactivate')); 
