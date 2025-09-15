<?php
/**
 * Archive Fields Manager
 * Gestisce i custom fields per le descrizioni degli archivi CPT
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Archive Fields Manager Class
 */
class PF_Archive_Fields_Manager {
    
    /**
     * Supported post types
     */
    private $supported_post_types = array('artisti', 'agenda');
    
    /**
     * Supported languages
     */
    private $supported_languages = array('it', 'en');
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_archive_settings_pages'));
        add_action('admin_init', array($this, 'register_archive_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        // Allinea le capability per la pagina di salvataggio options.php
        foreach ($this->supported_post_types as $post_type) {
            add_filter("option_page_capability_{$post_type}_archive_settings", array($this, 'filter_option_page_capability'));
        }
    }
    
    /**
     * Add archive settings pages for each CPT
     */
    public function add_archive_settings_pages() {
        foreach ($this->supported_post_types as $post_type) {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj) {
                // Personalizza il titolo in base al post type
                $page_title = '';
                $menu_title = '';
                
                switch ($post_type) {
                    case 'artisti':
                        $page_title = __('Descrizione Artisti', 'pf');
                        $menu_title = __('Descrizione Artisti', 'pf');
                        break;
                    case 'agenda':
                        $page_title = __('Descrizione Agenda', 'pf');
                        $menu_title = __('Descrizione Agenda', 'pf');
                        break;
                    default:
                        $page_title = sprintf(__('Descrizione %s', 'pf'), $post_type_obj->labels->name);
                        $menu_title = sprintf(__('Descrizione %s', 'pf'), $post_type_obj->labels->name);
                }
                
                add_submenu_page(
                    "edit.php?post_type={$post_type}",
                    $page_title,
                    $menu_title,
                    'edit_posts',
                    "{$post_type}-archive-settings",
                    array($this, 'render_archive_settings_page')
                );
            }
        }
    }
    
    /**
     * Register archive settings
     */
    public function register_archive_settings() {
        foreach ($this->supported_post_types as $post_type) {
            // Registra le opzioni per ogni lingua
            foreach ($this->supported_languages as $lang) {
                $option_name = "{$post_type}_archive_description_{$lang}";
                register_setting(
                    "{$post_type}_archive_settings",
                    $option_name,
                    array(
                        'type' => 'string',
                        'capability' => 'edit_posts',
                        'sanitize_callback' => 'wp_kses_post',
                        'default' => ''
                    )
                );
            }
            
            // Ottieni l'oggetto post type
            $post_type_obj = get_post_type_object($post_type);
            
            // Verifica che l'oggetto post type esista
            if ($post_type_obj) {
                add_settings_section(
                    "{$post_type}_archive_section",
                    sprintf(__('Descrizione %s', 'pf'), $post_type_obj->labels->name),
                    array($this, 'render_section_description'),
                    "{$post_type}_archive_settings"
                );
                
                // Aggiungi campi per ogni lingua
                foreach ($this->supported_languages as $lang) {
                    $option_name = "{$post_type}_archive_description_{$lang}";
                    $lang_label = $lang === 'it' ? 'Italiano' : 'English';
                    
                    add_settings_field(
                        $option_name,
                        sprintf(__('Descrizione (%s)', 'pf'), $lang_label),
                        array($this, 'render_description_field'),
                        "{$post_type}_archive_settings",
                        "{$post_type}_archive_section",
                        array(
                            'post_type' => $post_type,
                            'language' => $lang,
                            'option_name' => $option_name
                        )
                    );
                }
            }
        }
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . __('Inserisci una descrizione per la pagina per ogni lingua. Questo testo apparirà nella pagina principale.', 'pf') . '</p>';
    }
    
    /**
     * Render description field
     */
    public function render_description_field($args) {
        $post_type = $args['post_type'];
        $language = $args['language'];
        $option_name = $args['option_name'];
        $description = get_option($option_name, '');
        
        // Aggiungi un wrapper per ogni campo lingua
        echo '<div class="language-field-wrapper" style="margin-bottom: 20px;">';
        echo '<h4 style="margin-bottom: 10px;">' . ($language === 'it' ? 'Italiano' : 'English') . '</h4>';
        
        wp_editor(
            $description,
            $option_name,
            array(
                'textarea_name' => $option_name,
                'textarea_rows' => 6,
                'media_buttons' => false,
                'teeny' => true,
                'tinymce' => array(
                    'toolbar1' => 'bold,italic,underline,link,unlink',
                    'toolbar2' => '',
                    'toolbar3' => ''
                )
            )
        );
        
        echo '</div>';
    }
    
    /**
     * Render archive settings page
     */
    public function render_archive_settings_page() {
        $current_page = $_GET['page'] ?? '';
        $post_type = str_replace('-archive-settings', '', $current_page);
        
        if (!in_array($post_type, $this->supported_post_types)) {
            wp_die(__('Post type non supportato.', 'pf'));
        }
        
        $post_type_obj = get_post_type_object($post_type);
        
        // Verifica che l'oggetto post type esista
        if (!$post_type_obj) {
            wp_die(__('Post type non trovato.', 'pf'));
        }
        
        // Personalizza il titolo della pagina in base al post type
        $page_title = '';
        switch ($post_type) {
            case 'artisti':
                $page_title = __('Descrizione Artisti', 'pf');
                break;
            case 'agenda':
                $page_title = __('Descrizione Agenda', 'pf');
                break;
            default:
                $page_title = sprintf(__('Descrizione %s', 'pf'), $post_type_obj->labels->name);
        }
        
        $archive_url = get_post_type_archive_link($post_type);
        ?>
        <div class="wrap">
            <h1><?php echo $page_title; ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields("{$post_type}_archive_settings");
                do_settings_sections("{$post_type}_archive_settings");
                submit_button();
                ?>
            </form>
            
            <div class="archive-preview" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                <h3><?php _e('Anteprima', 'pf'); ?></h3>
                <p><?php _e('Ecco come apparirà la descrizione nella pagina per ogni lingua:', 'pf'); ?></p>
                <?php if ($archive_url): ?>
                    <p><strong><?php _e('URL Archivio:', 'pf'); ?></strong> <a href="<?php echo esc_url($archive_url); ?>" target="_blank"><?php echo esc_url($archive_url); ?></a></p>
                <?php endif; ?>
                
                <?php foreach ($this->supported_languages as $lang): ?>
                    <div class="preview-content" style="margin-top: 15px; padding: 15px; background: white; border: 1px solid #e5e5e5;">
                        <h4><?php echo $lang === 'it' ? 'Italiano' : 'English'; ?></h4>
                        <?php 
                        $description = get_option("{$post_type}_archive_description_{$lang}", '');
                        if ($description) {
                            echo wpautop($description);
                        } else {
                            echo '<p style="color: #666; font-style: italic;">' . __('Nessuna descrizione impostata.', 'pf') . '</p>';
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, '-archive-settings') !== false) {
            wp_enqueue_editor();
        }
    }
    
    /**
     * Forza la capability corretta per il salvataggio via options.php
     */
    public function filter_option_page_capability($capability) {
        return 'edit_posts';
    }
    
    /**
     * Get archive description for current language
     */
    public static function get_archive_description($post_type = 'artisti') {
        // Determina la lingua corrente
        $current_lang = 'it'; // Default italiano
        
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
        }
        
        // Fallback: rileva la lingua dall'URL se Polylang non la rileva
        if ($current_lang === false || empty($current_lang)) {
            $current_url = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($current_url, '/en/') !== false) {
                $current_lang = 'en';
            } else {
                $current_lang = 'it';
            }
        }
        
        // Assicurati che la lingua sia supportata
        if (!in_array($current_lang, array('it', 'en'))) {
            $current_lang = 'it';
        }
        
        $option_name = "{$post_type}_archive_description_{$current_lang}";
        return get_option($option_name, '');
    }
    
    /**
     * Get archive description for specific language
     */
    public static function get_archive_description_by_language($post_type = 'artisti', $language = 'it') {
        // Assicurati che la lingua sia supportata
        if (!in_array($language, array('it', 'en'))) {
            $language = 'it';
        }
        
        $option_name = "{$post_type}_archive_description_{$language}";
        return get_option($option_name, '');
    }
}
