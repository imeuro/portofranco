<?php
/**
 * Exhibition Manager
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Exhibition Manager Class
 */
class PF_Exhibition_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_artwork_meta_box'));
        add_action('save_post_artisti', array($this, 'save_artwork_meta'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Load REST API
        $this->load_rest_api();
    }
    
    /**
     * Load REST API
     */
    private function load_rest_api() {
        $rest_api_file = PF_PLUGIN_DIR . 'modules/exhibition/class-exhibition-rest-api.php';
        if (file_exists($rest_api_file)) {
            require_once $rest_api_file;
        }
    }
    
    /**
     * Add meta box for artworks
     */
    public function add_artwork_meta_box() {
        add_meta_box(
            'pf_artworks_meta_box',
            __('Opere in Exhibition', 'pf'),
            array($this, 'render_artwork_meta_box'),
            'artisti',
            'normal',
            'high'
        );
    }
    
    /**
     * Render artwork meta box
     */
    public function render_artwork_meta_box($post) {
        wp_nonce_field('pf_save_artworks', 'pf_artworks_nonce');
        
        $artworks = get_post_meta($post->ID, '_pf_artworks', true);
        if (!is_array($artworks)) {
            $artworks = array();
        }
        
        ?>
        <div id="pf-artworks-container">
            <div id="pf-artworks-list">
                <?php
                if (!empty($artworks)) {
                    foreach ($artworks as $index => $artwork) {
                        $this->render_artwork_item($index, $artwork);
                    }
                }
                ?>
            </div>
            
            <button type="button" class="button button-secondary" id="pf-add-artwork">
                <?php _e('+ Aggiungi Opera', 'pf'); ?>
            </button>
        </div>
        
        <template id="pf-artwork-template">
            <?php $this->render_artwork_item('{{INDEX}}', array()); ?>
        </template>
        <?php
    }
    
    /**
     * Render single artwork item
     */
    private function render_artwork_item($index, $artwork = array()) {
        $floor = isset($artwork['floor']) ? $artwork['floor'] : '';
        $title = isset($artwork['title']) ? $artwork['title'] : '';
        $description = isset($artwork['description']) ? $artwork['description'] : '';
        $position_x = isset($artwork['position_x']) ? $artwork['position_x'] : '';
        $position_y = isset($artwork['position_y']) ? $artwork['position_y'] : '';
        
        ?>
        <div class="pf-artwork-item" data-index="<?php echo esc_attr($index); ?>">
            <div class="pf-artwork-header">
                <h4><?php _e('Opera', 'pf'); ?> #<span class="artwork-number"><?php echo is_numeric($index) ? $index + 1 : ''; ?></span></h4>
                <button type="button" class="button-link-delete pf-remove-artwork">
                    <?php _e('Rimuovi', 'pf'); ?>
                </button>
            </div>
            
            <div class="pf-artwork-fields">
                <div class="pf-field-group">
                    <label>
                        <?php _e('Piano', 'pf'); ?>
                        <select name="pf_artworks[<?php echo esc_attr($index); ?>][floor]" class="pf-floor-select" required>
                            <option value=""><?php _e('Seleziona piano...', 'pf'); ?></option>
                            <option value="0" <?php selected($floor, '0'); ?>><?php _e('Piano terra', 'pf'); ?></option>
                            <option value="1" <?php selected($floor, '1'); ?>><?php _e('Piano 1', 'pf'); ?></option>
                            <option value="2" <?php selected($floor, '2'); ?>><?php _e('Piano 2', 'pf'); ?></option>
                            <option value="3" <?php selected($floor, '3'); ?>><?php _e('Piano 3', 'pf'); ?></option>
                        </select>
                    </label>
                </div>
                
                <div class="pf-field-group">
                    <label>
                        <?php _e('Titolo Opera', 'pf'); ?>
                        <input type="text" 
                               name="pf_artworks[<?php echo esc_attr($index); ?>][title]" 
                               value="<?php echo esc_attr($title); ?>" 
                               class="widefat" 
                               required>
                    </label>
                </div>
                
                <div class="pf-field-group">
                    <label>
                        <?php _e('Descrizione Opera', 'pf'); ?>
                        <textarea name="pf_artworks[<?php echo esc_attr($index); ?>][description]" 
                                  class="widefat" 
                                  rows="3"><?php echo esc_textarea($description); ?></textarea>
                    </label>
                </div>
                
                <div class="pf-position-fields">
                    <h5><?php _e('Posiziona sulla mappa', 'pf'); ?></h5>
                    
                    <div class="pf-map-container">
                        <div class="pf-map-preview" data-floor="<?php echo esc_attr($floor); ?>">
                            <?php if ($floor): ?>
                                <img src="<?php echo $this->get_floor_map_url($floor); ?>" alt="<?php echo sprintf(__('Mappa %s', 'pf'), $this->get_floor_name($floor)); ?>">
                                <?php if ($position_x && $position_y): ?>
                                    <div class="pf-marker" style="left: <?php echo esc_attr($position_x); ?>%; top: <?php echo esc_attr($position_y); ?>%;"></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="pf-map-placeholder">
                                    <?php _e('Seleziona un piano per vedere la mappa', 'pf'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="pf-coordinates">
                        <div class="pf-field-inline">
                            <label>
                                X (%)
                                <input type="number" 
                                       name="pf_artworks[<?php echo esc_attr($index); ?>][position_x]" 
                                       value="<?php echo esc_attr($position_x); ?>" 
                                       step="0.1" 
                                       min="0" 
                                       max="100" 
                                       class="small-text pf-position-x">
                            </label>
                        </div>
                        <div class="pf-field-inline">
                            <label>
                                Y (%)
                                <input type="number" 
                                       name="pf_artworks[<?php echo esc_attr($index); ?>][position_y]" 
                                       value="<?php echo esc_attr($position_y); ?>" 
                                       step="0.1" 
                                       min="0" 
                                       max="100" 
                                       class="small-text pf-position-y">
                            </label>
                        </div>
                        <p class="description">
                            <?php _e('Clicca sulla mappa per posizionare l\'opera, oppure inserisci manualmente le coordinate.', 'pf'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get floor display name
     */
    private function get_floor_name($floor) {
        if ($floor === '0' || $floor === 0) {
            return __('Piano terra', 'pf');
        }
        return sprintf(__('Piano %s', 'pf'), $floor);
    }
    
    /**
     * Get floor map URL
     */
    private function get_floor_map_url($floor) {
        $upload_dir = wp_upload_dir();
        $map_url = $upload_dir['baseurl'] . '/exhibition-maps/piano-' . $floor . '.jpg';
        
        // Fallback to placeholder if map doesn't exist
        $map_path = $upload_dir['basedir'] . '/exhibition-maps/piano-' . $floor . '.jpg';
        if (!file_exists($map_path)) {
            $floor_name = $this->get_floor_name($floor);
            return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="800" height="600"%3E%3Crect fill="%23f0f0f0" width="800" height="600"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="20" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EMappa ' . $floor_name . ' non trovata%3C/text%3E%3C/svg%3E';
        }
        
        return $map_url;
    }
    
    /**
     * Save artwork meta
     */
    public function save_artwork_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['pf_artworks_nonce']) || !wp_verify_nonce($_POST['pf_artworks_nonce'], 'pf_save_artworks')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save artworks
        if (isset($_POST['pf_artworks'])) {
            $artworks = array();
            
            foreach ($_POST['pf_artworks'] as $artwork) {
                // Validate and sanitize
                // Check floor explicitly (can be '0', so we can't use empty())
                $floor = isset($artwork['floor']) ? trim($artwork['floor']) : '';
                if ($floor === '' || empty($artwork['title'])) {
                    continue;
                }
                
                $artworks[] = array(
                    'floor' => sanitize_text_field($artwork['floor']),
                    'title' => sanitize_text_field($artwork['title']),
                    'description' => sanitize_textarea_field($artwork['description']),
                    'position_x' => floatval($artwork['position_x']),
                    'position_y' => floatval($artwork['position_y']),
                );
            }
            
            update_post_meta($post_id, '_pf_artworks', $artworks);
        } else {
            delete_post_meta($post_id, '_pf_artworks');
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on artisti edit screen
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        global $post_type;
        if ('artisti' !== $post_type) {
            return;
        }
        
        wp_enqueue_style(
            'pf-exhibition-admin',
            PF_PLUGIN_URL . 'modules/exhibition/assets/css/admin-exhibition.css',
            array(),
            PF_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            'pf-exhibition-admin',
            PF_PLUGIN_URL . 'modules/exhibition/assets/js/admin-map-positioner.js',
            array('jquery'),
            PF_PLUGIN_VERSION,
            true
        );
        
        // Pass data to JavaScript
        $upload_dir = wp_upload_dir();
        wp_localize_script('pf-exhibition-admin', 'pfExhibition', array(
            'mapBaseUrl' => $upload_dir['baseurl'] . '/exhibition-maps/',
            'placeholderSvg' => 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="800" height="600"%3E%3Crect fill="%23f0f0f0" width="800" height="600"/%3E%3C/svg%3E',
        ));
    }
    
    /**
     * Get artworks by floor
     */
    public static function get_artworks_by_floor($floor) {
        $args = array(
            'post_type' => 'artisti',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_pf_artworks',
                    'compare' => 'EXISTS',
                ),
            ),
        );
        
        // Add language filter if Polylang is active
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
            if ($current_lang) {
                $args['lang'] = $current_lang;
            }
        }
        
        $query = new WP_Query($args);
        $result = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $artworks = get_post_meta(get_the_ID(), '_pf_artworks', true);
                
                if (is_array($artworks)) {
                    foreach ($artworks as $artwork) {
                        if ($artwork['floor'] == $floor) {
                            $result[] = array(
                                'artist_id' => get_the_ID(),
                                'artist_name' => get_the_title(),
                                'artist_url' => get_permalink(),
                                'artwork_title' => $artwork['title'],
                                'artwork_description' => $artwork['description'],
                                'position_x' => $artwork['position_x'],
                                'position_y' => $artwork['position_y'],
                            );
                        }
                    }
                }
            }
            wp_reset_postdata();
        }
        
        return $result;
    }
}

// Initialize the manager
new PF_Exhibition_Manager();

