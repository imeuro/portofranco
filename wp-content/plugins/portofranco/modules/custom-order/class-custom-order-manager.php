<?php
/**
 * Custom Order Manager
 * Gestisce l'ordinamento personalizzato dei post tramite drag & drop
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Custom Order Manager Class
 */
class PF_Custom_Order_Manager {
    
    /**
     * Supported post types
     */
    private $supported_post_types = array('artisti');
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_order_management_pages'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_save_custom_order', array($this, 'save_custom_order'));
        add_action('pre_get_posts', array($this, 'modify_queries_for_custom_order'));
        add_action('add_meta_boxes', array($this, 'add_order_meta_box'));
        add_action('save_post', array($this, 'save_order_meta_box'));
        
        // Carica il file di test se richiesto
        if (isset($_GET['test_custom_order']) && current_user_can('manage_options')) {
            require_once PF_PLUGIN_DIR . 'modules/custom-order/test-custom-order.php';
        }
    }
    
    /**
     * Add order management pages for each CPT
     */
    public function add_order_management_pages() {
        foreach ($this->supported_post_types as $post_type) {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj) {
                add_submenu_page(
                    "edit.php?post_type={$post_type}",
                    sprintf(__('Ordina %s', 'pf'), $post_type_obj->labels->name),
                    sprintf(__('Ordina %s', 'pf'), $post_type_obj->labels->name),
                    'edit_posts',
                    "{$post_type}-order",
                    array($this, 'render_order_page')
                );
            }
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Carica solo nelle pagine di ordinamento
        if (strpos($hook, '-order') !== false) {
            wp_enqueue_script(
                'pf-custom-order',
                PF_PLUGIN_URL . 'modules/custom-order/assets/js/custom-order.js',
                array('jquery', 'jquery-ui-sortable'),
                PF_PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'pf-custom-order',
                PF_PLUGIN_URL . 'modules/custom-order/assets/css/custom-order.css',
                array(),
                PF_PLUGIN_VERSION
            );
            
            // Localize script
            wp_localize_script('pf-custom-order', 'pfCustomOrder', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pf_custom_order_nonce'),
                'strings' => array(
                    'saving' => __('Salvando...', 'pf'),
                    'saved' => __('Ordinamento salvato!', 'pf'),
                    'error' => __('Errore nel salvataggio', 'pf')
                )
            ));
        }
    }
    
    /**
     * Render order management page
     */
    public function render_order_page() {
        $post_type = str_replace('-order', '', $_GET['page']);
        $post_type_obj = get_post_type_object($post_type);
        
        if (!$post_type_obj) {
            wp_die(__('Post type non valido', 'pf'));
        }
        
        // Ottieni tutti i post del tipo specificato (solo in italiano se Polylang è attivo)
        $query_args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => array(
                'meta_value_num' => 'ASC',
                'title' => 'ASC'
            ),
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_custom_order',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => '_custom_order',
                    'compare' => 'NOT EXISTS'
                )
            )
        );
        
        // Filtra per lingua italiana se Polylang è attivo
        if (function_exists('pll_current_language')) {
            $query_args['lang'] = 'it';
        }
        
        $posts = get_posts($query_args);
        
        ?>
        <div class="wrap">
            <h1><?php printf(__('Ordina %s', 'pf'), $post_type_obj->labels->name); ?></h1>
            
            <div class="order-instructions">
                <p><?php _e('Trascina gli elementi per riordinarli. L\'ordine verrà salvato automaticamente.', 'pf'); ?></p>
            </div>
            
            <div id="custom-order-container" data-post-type="<?php echo esc_attr($post_type); ?>">
                <ul id="sortable-list" class="sortable-list">
                    <?php foreach ($posts as $post) : ?>
                        <li class="sortable-item" data-post-id="<?php echo esc_attr($post->ID); ?>">
                            <div class="item-handle">
                                <span class="dashicons dashicons-menu"></span>
                            </div>
                            <div class="item-content">
                                <div class="item-title"><?php echo esc_html($post->post_title); ?></div>
                                <div class="item-meta">
                                    <?php 
                                    $custom_order = get_post_meta($post->ID, '_custom_order', true);
                                    if ($custom_order) {
                                        echo sprintf(__('Ordine: %d', 'pf'), $custom_order);
                                    } else {
                                        echo __('Ordine alfabetico', 'pf');
                                    }
                                    
                                    // Mostra informazioni sulle traduzioni se Polylang è attivo
                                    if (function_exists('pll_get_post_translations')) {
                                        $translations = pll_get_post_translations($post->ID);
                                        $current_lang = pll_get_post_language($post->ID);
                                        $available_langs = array();
                                        
                                        foreach ($translations as $lang => $post_id) {
                                            if ($lang !== $current_lang) {
                                                $lang_name = $lang === 'en' ? 'Inglese' : ucfirst($lang);
                                                $available_langs[] = $lang_name;
                                            }
                                        }
                                        
                                        if (!empty($available_langs)) {
                                            echo '<br><small>' . sprintf(__('Disponibile in: %s', 'pf'), implode(', ', $available_langs)) . '</small>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="item-actions">
                                <a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-small">
                                    <?php _e('Modifica', 'pf'); ?>
                                </a>
                                <a href="<?php echo get_permalink($post->ID); ?>" class="button button-small" target="_blank">
                                    <?php _e('Visualizza', 'pf'); ?>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div id="order-status" class="order-status"></div>
        </div>
        <?php
    }
    
    /**
     * Save custom order via AJAX
     */
    public function save_custom_order() {
        // Verifica nonce
        if (!wp_verify_nonce($_POST['nonce'], 'pf_custom_order_nonce')) {
            wp_die(__('Nonce non valido', 'pf'));
        }
        
        // Verifica permessi
        if (!current_user_can('edit_posts')) {
            wp_die(__('Permessi insufficienti', 'pf'));
        }
        
        $post_type = sanitize_text_field($_POST['post_type']);
        $order = $_POST['order'];
        
        if (!is_array($order)) {
            wp_send_json_error(__('Dati non validi', 'pf'));
        }
        
        // Salva l'ordine per ogni post
        foreach ($order as $position => $post_id) {
            $post_id = intval($post_id);
            $position = intval($position) + 1; // Inizia da 1 invece che da 0
            
            // Verifica che il post esista e sia del tipo corretto
            $post = get_post($post_id);
            if ($post && $post->post_type === $post_type) {
                update_post_meta($post_id, '_custom_order', $position);
                
                // Se Polylang è attivo, applica l'ordine anche alle traduzioni
                if (function_exists('pll_get_post_translations')) {
                    $translations = pll_get_post_translations($post_id);
                    foreach ($translations as $lang => $translated_post_id) {
                        if ($translated_post_id != $post_id) {
                            update_post_meta($translated_post_id, '_custom_order', $position);
                        }
                    }
                }
            }
        }
        
        wp_send_json_success(__('Ordinamento salvato con successo', 'pf'));
    }
    
    /**
     * Modify queries to use custom order
     */
    public function modify_queries_for_custom_order($query) {
        // Solo per query principali e non in admin
        if (!is_admin() && $query->is_main_query()) {
            foreach ($this->supported_post_types as $post_type) {
                if (is_post_type_archive($post_type)) {
                    // Non interferire se Polylang ha già impostato la lingua
                    // Il template archive-artisti.php gestisce già la query personalizzata
                    return;
                }
            }
        }
    }
    
    /**
     * Add meta box for manual order input
     */
    public function add_order_meta_box() {
        foreach ($this->supported_post_types as $post_type) {
            add_meta_box(
                'custom_order_meta_box',
                __('Ordine Personalizzato', 'pf'),
                array($this, 'render_order_meta_box'),
                $post_type,
                'side',
                'high'
            );
        }
    }
    
    /**
     * Render order meta box
     */
    public function render_order_meta_box($post) {
        wp_nonce_field('pf_custom_order_meta_box', 'pf_custom_order_nonce');
        
        $custom_order = get_post_meta($post->ID, '_custom_order', true);
        ?>
        <p>
            <label for="custom_order"><?php _e('Posizione nell\'ordinamento:', 'pf'); ?></label>
            <input type="number" id="custom_order" name="custom_order" value="<?php echo esc_attr($custom_order); ?>" min="1" step="1" class="widefat" />
        </p>
        <p class="description">
            <?php _e('Lascia vuoto per utilizzare l\'ordine alfabetico. I numeri più bassi appaiono per primi.', 'pf'); ?>
        </p>
        
        <?php
        // Mostra informazioni sulle traduzioni se Polylang è attivo
        if (function_exists('pll_get_post_translations')) {
            $translations = pll_get_post_translations($post->ID);
            if (count($translations) > 1) {
                echo '<p class="description" style="color: #0073aa;">';
                echo '<strong>' . __('Nota:', 'pf') . '</strong> ';
                echo sprintf(__('Questo ordine verrà applicato anche alle %d traduzioni di questo post.', 'pf'), count($translations) - 1);
                echo '</p>';
            }
        }
        ?>
        <?php
    }
    
    /**
     * Save order meta box
     */
    public function save_order_meta_box($post_id) {
        // Verifica nonce
        if (!isset($_POST['pf_custom_order_nonce']) || !wp_verify_nonce($_POST['pf_custom_order_nonce'], 'pf_custom_order_meta_box')) {
            return;
        }
        
        // Verifica permessi
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verifica autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Salva l'ordine personalizzato
        if (isset($_POST['custom_order'])) {
            $custom_order = sanitize_text_field($_POST['custom_order']);
            if ($custom_order === '') {
                delete_post_meta($post_id, '_custom_order');
                
                // Se Polylang è attivo, rimuovi l'ordine anche dalle traduzioni
                if (function_exists('pll_get_post_translations')) {
                    $translations = pll_get_post_translations($post_id);
                    foreach ($translations as $lang => $translated_post_id) {
                        if ($translated_post_id != $post_id) {
                            delete_post_meta($translated_post_id, '_custom_order');
                        }
                    }
                }
            } else {
                $order_value = intval($custom_order);
                update_post_meta($post_id, '_custom_order', $order_value);
                
                // Se Polylang è attivo, applica l'ordine anche alle traduzioni
                if (function_exists('pll_get_post_translations')) {
                    $translations = pll_get_post_translations($post_id);
                    foreach ($translations as $lang => $translated_post_id) {
                        if ($translated_post_id != $post_id) {
                            update_post_meta($translated_post_id, '_custom_order', $order_value);
                        }
                    }
                }
            }
        }
    }
}
