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
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Plugin initialization code here
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
        
        // Load Pages module
        //$this->load_module('pages');
        
        // Load Hero module
        //$this->load_module('hero');
    }
    
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
