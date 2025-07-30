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
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_archive_settings_pages'));
        add_action('admin_init', array($this, 'register_archive_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add archive settings pages for each CPT
     */
    public function add_archive_settings_pages() {
        foreach ($this->supported_post_types as $post_type) {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj) {
                add_submenu_page(
                    "edit.php?post_type={$post_type}",
                    sprintf(__('Impostazioni %s', 'pf'), $post_type_obj->labels->name),
                    sprintf(__('Impostazioni %s', 'pf'), $post_type_obj->labels->name),
                    'manage_options',
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
            $option_name = "{$post_type}_archive_description";
            register_setting("{$post_type}_archive_settings", $option_name);
            
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
                
                add_settings_field(
                    $option_name,
                    __('Descrizione', 'pf'),
                    array($this, 'render_description_field'),
                    "{$post_type}_archive_settings",
                    "{$post_type}_archive_section",
                    array('post_type' => $post_type)
                );
            }
        }
    }
    
    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . __('Inserisci una descrizione per la pagina. Questo testo apparirà nella pagina principale.', 'pf') . '</p>';
    }
    
    /**
     * Render description field
     */
    public function render_description_field($args) {
        $post_type = $args['post_type'];
        $option_name = "{$post_type}_archive_description";
        $description = get_option($option_name, '');
        
        wp_editor(
            $description,
            $option_name,
            array(
                'textarea_name' => $option_name,
                'textarea_rows' => 8,
                'media_buttons' => false,
                'teeny' => true,
                'tinymce' => array(
                    'toolbar1' => 'bold,italic,underline,link,unlink',
                    'toolbar2' => '',
                    'toolbar3' => ''
                )
            )
        );
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
        
        $archive_url = get_post_type_archive_link($post_type);
        ?>
        <div class="wrap">
            <h1><?php printf(__('Impostazioni %s', 'pf'), $post_type_obj->labels->name); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields("{$post_type}_archive_settings");
                do_settings_sections("{$post_type}_archive_settings");
                submit_button();
                ?>
            </form>
            
            <div class="archive-preview" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd;">
                <h3><?php _e('Anteprima', 'pf'); ?></h3>
                <p><?php _e('Ecco come apparirà la descrizione nella pagina:', 'pf'); ?></p>
                <?php if ($archive_url): ?>
                    <p><strong><?php _e('URL Archivio:', 'pf'); ?></strong> <a href="<?php echo esc_url($archive_url); ?>" target="_blank"><?php echo esc_url($archive_url); ?></a></p>
                <?php endif; ?>
                <div class="preview-content" style="margin-top: 15px;">
                    <?php 
                    $description = get_option("{$post_type}_archive_description", '');
                    if ($description) {
                        echo wpautop($description);
                    } else {
                        echo '<p style="color: #666; font-style: italic;">' . __('Nessuna descrizione impostata.', 'pf') . '</p>';
                    }
                    ?>
                </div>
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
     * Get archive description
     */
    public static function get_archive_description($post_type = 'artisti') {
        $option_name = "{$post_type}_archive_description";
        return get_option($option_name, '');
    }
}
