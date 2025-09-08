<?php
/**
 * Custom Post Types Manager
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF CPT Manager Class
 */
class PF_CPT_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('admin_menu', array($this, 'hide_post_type_from_admin'));
        add_action('pre_get_posts', array($this, 'exclude_posts_from_frontend'));
        add_action('admin_menu', array($this, 'hide_comments_menu'));
        add_action('wp_dashboard_setup', array($this, 'remove_comments_dashboard_widget'));
    }
    
    /**
     * Register all custom post types
     */
    public function register_post_types() {
        $this->register_artisti_post_type();
        $this->register_special_projects_post_type();
        $this->register_agenda_post_type();
    }
    
    /**
     * Register all custom taxonomies
     */
    public function register_taxonomies() {
        // Add taxonomies here if needed
    }

    /**
     * Register 'artisti' custom post type
     */
    private function register_artisti_post_type() {
        $labels = array(
            'name'                  => _x('Artisti', 'Post type general name', 'pf'),
            'singular_name'         => _x('Artista', 'Post type singular name', 'pf'),
            'menu_name'             => _x('Artisti', 'Admin Menu text', 'pf'),
            'name_admin_bar'        => _x('Artista', 'Add New on Toolbar', 'pf'),
            'add_new'               => __('Aggiungi Nuovo', 'pf'),
            'add_new_item'          => __('Aggiungi Nuovo Artista', 'pf'),
            'new_item'              => __('Nuovo Artista', 'pf'),
            'edit_item'             => __('Modifica Artista', 'pf'),
            'view_item'             => __('Visualizza Artista', 'pf'),
            'all_items'             => __('Tutti gli Artisti', 'pf'),
            'search_items'          => __('Cerca Artisti', 'pf'),
            'parent_item_colon'     => __('Artisti Padre:', 'pf'),
            'not_found'             => __('Nessun artista trovato.', 'pf'),
            'not_found_in_trash'    => __('Nessun artista trovato nel cestino.', 'pf'),
            'featured_image'        => _x('Immagine dell\'Artista', 'Overrides the "Featured Image" phrase for this post type.', 'pf'),
            'set_featured_image'    => _x('Imposta immagine dell\'artista', 'Overrides the "Set featured image" phrase for this post type.', 'pf'),
            'remove_featured_image' => _x('Rimuovi immagine dell\'artista', 'Overrides the "Remove featured image" phrase for this post type.', 'pf'),
            'use_featured_image'    => _x('Usa come immagine dell\'artista', 'Overrides the "Use as featured image" phrase for this post type.', 'pf'),
            'archives'              => _x('Archivio Artisti', 'The post type archive label used in nav menus.', 'pf'),
            'insert_into_item'      => _x('Inserisci nell\'artista', 'Overrides the "Insert into post" phrase (used when inserting media into a post).', 'pf'),
            'uploaded_to_this_item' => _x('Caricato in questo artista', 'Overrides the "Uploaded to this post" phrase (used when viewing media attached to a post).', 'pf'),
            'filter_items_list'     => _x('Filtra lista artisti', 'Screen reader text for the filter links.', 'pf'),
            'items_list_navigation' => _x('Navigazione lista artisti', 'Screen reader text for the pagination.', 'pf'),
            'items_list'            => _x('Lista artisti', 'Screen reader text for the items list.', 'pf'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => 'artisti',
            'rewrite'            => array(
                'slug' => $this->get_rewrite_slug('artisti'),
                'with_front' => false,
                'feeds' => true,
                'pages' => true
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-admin-users',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true, // Abilitato per REST API
        );
        
        register_post_type('artisti', $args);
    }

    /**
     * Register 'special-projects' custom post type
     */
    private function register_special_projects_post_type() {
        $labels = array(
            'name'                  => _x('Special Projects', 'Post type general name', 'pf'),
            'singular_name'         => _x('Special Project', 'Post type singular name', 'pf'),
            'menu_name'             => _x('Special Projects', 'Admin Menu text', 'pf'),
            'name_admin_bar'        => _x('Special Project', 'Add New on Toolbar', 'pf'),
            'add_new'               => __('Aggiungi Nuovo', 'pf'),
            'add_new_item'          => __('Aggiungi Nuovo Special Project', 'pf'),
            'new_item'              => __('Nuovo Special Project', 'pf'),
            'edit_item'             => __('Modifica Special Project', 'pf'),
            'view_item'             => __('Visualizza Special Project', 'pf'),
            'all_items'             => __('Tutti gli Special Projects', 'pf'),
            'search_items'          => __('Cerca Special Projects', 'pf'),
            'parent_item_colon'     => __('Special Projects Padre:', 'pf'),
            'not_found'             => __('Nessuno special project trovato.', 'pf'),
            'not_found_in_trash'    => __('Nessuno special project trovato nel cestino.', 'pf'),
            'featured_image'        => _x('Immagine del Special Project', 'Overrides the "Featured Image" phrase for this post type.', 'pf'),
            'set_featured_image'    => _x('Imposta immagine del special project', 'Overrides the "Set featured image" phrase for this post type.', 'pf'),
            'remove_featured_image' => _x('Rimuovi immagine del special project', 'Overrides the "Remove featured image" phrase for this post type.', 'pf'),
            'use_featured_image'    => _x('Usa come immagine del special project', 'Overrides the "Use as featured image" phrase for this post type.', 'pf'),
            'archives'              => _x('Archivio Special Projects', 'The post type archive label used in nav menus.', 'pf'),
            'insert_into_item'      => _x('Inserisci nel special project', 'Overrides the "Insert into post" phrase (used when inserting media into a post).', 'pf'),
            'uploaded_to_this_item' => _x('Caricato in questo special project', 'Overrides the "Uploaded to this post" phrase (used when viewing media attached to a post).', 'pf'),
            'filter_items_list'     => _x('Filtra lista special projects', 'Screen reader text for the filter links.', 'pf'),
            'items_list_navigation' => _x('Navigazione lista special projects', 'Screen reader text for the pagination.', 'pf'),
            'items_list'            => _x('Lista special projects', 'Screen reader text for the items list.', 'pf'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => 'special-projects',
            'rewrite'            => array(
                'slug' => $this->get_rewrite_slug('special-projects'),
                'with_front' => false,
                'feeds' => true,
                'pages' => true
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 7,
            'menu_icon'          => 'dashicons-star-filled',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true, // Abilitato per REST API
        );
        
        register_post_type('special-projects', $args);
    }

    /**
     * Register 'agenda' custom post type
     */
    private function register_agenda_post_type() {
        $labels = array(
            'name'                  => _x('Agenda', 'Post type general name', 'pf'),
            'singular_name'         => _x('Evento', 'Post type singular name', 'pf'),
            'menu_name'             => _x('Agenda', 'Admin Menu text', 'pf'),
            'name_admin_bar'        => _x('Evento', 'Add New on Toolbar', 'pf'),
            'add_new'               => __('Aggiungi Nuovo', 'pf'),
            'add_new_item'          => __('Aggiungi Nuovo Evento', 'pf'),
            'new_item'              => __('Nuovo Evento', 'pf'),
            'edit_item'             => __('Modifica Evento', 'pf'),
            'view_item'             => __('Visualizza Evento', 'pf'),
            'all_items'             => __('Tutti gli Eventi', 'pf'),
            'search_items'          => __('Cerca Eventi', 'pf'),
            'parent_item_colon'     => __('Eventi Padre:', 'pf'),
            'not_found'             => __('Nessun evento trovato.', 'pf'),
            'not_found_in_trash'    => __('Nessun evento trovato nel cestino.', 'pf'),
            'featured_image'        => _x('Immagine dell\'Evento', 'Overrides the "Featured Image" phrase for this post type.', 'pf'),
            'set_featured_image'    => _x('Imposta immagine dell\'evento', 'Overrides the "Set featured image" phrase for this post type.', 'pf'),
            'remove_featured_image' => _x('Rimuovi immagine dell\'evento', 'Overrides the "Remove featured image" phrase for this post type.', 'pf'),
            'use_featured_image'    => _x('Usa come immagine dell\'evento', 'Overrides the "Use as featured image" phrase for this post type.', 'pf'),
            'archives'              => _x('Archivio Eventi', 'The post type archive label used in nav menus.', 'pf'),
            'insert_into_item'      => _x('Inserisci nell\'evento', 'Overrides the "Insert into post" phrase (used when inserting media into a post).', 'pf'),
            'uploaded_to_this_item' => _x('Caricato in questo evento', 'Overrides the "Uploaded to this post" phrase (used when viewing media attached to a post).', 'pf'),
            'filter_items_list'     => _x('Filtra lista eventi', 'Screen reader text for the filter links.', 'pf'),
            'items_list_navigation' => _x('Navigazione lista eventi', 'Screen reader text for the pagination.', 'pf'),
            'items_list'            => _x('Lista eventi', 'Screen reader text for the items list.', 'pf'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => 'agenda',
            'rewrite'            => array(
                'slug' => $this->get_rewrite_slug('agenda'),
                'with_front' => false,
                'feeds' => true,
                'pages' => true
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true, // Abilitato per REST API
        );
        
        register_post_type('agenda', $args);
    }

    /**
     * Hide 'post' post type from admin menu
     */
    public function hide_post_type_from_admin() {
        remove_menu_page('edit.php');
    }
    
    /**
     * Exclude 'post' post type from frontend queries
     */
    public function exclude_posts_from_frontend($query) {
        // Solo per query principali del frontend
        if (!is_admin() && $query->is_main_query()) {
            // Non applicare questo filtro agli archive di custom post types
            if (is_post_type_archive()) {
                return;
            }
            
            // Ottieni tutti i post types pubblici
            $post_types = get_post_types(array('public' => true));
            
            // Rimuovi solo 'post' dall'array
            $post_types = array_diff($post_types, array('post'));
            
            // Imposta i post types escludendo 'post'
            $query->set('post_type', $post_types);
        }
    }

    /**
     * Hide comments menu from admin
     */
    public function hide_comments_menu() {
        remove_menu_page('edit-comments.php');
    }
    
    /**
     * Remove comments dashboard widget
     */
    public function remove_comments_dashboard_widget() {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
    
    /**
     * Get rewrite slug based on current language
     */
    private function get_rewrite_slug($post_type) {
        // Controlla se Polylang è attivo
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
            
            switch ($post_type) {
                case 'artisti':
                    return $current_lang === 'en' ? 'artists' : 'artisti';
                case 'special-projects':
                    return $current_lang === 'en' ? 'special-projects' : 'special-projects';
                case 'agenda':
                    return $current_lang === 'en' ? 'events' : 'agenda';
                default:
                    return $post_type;
            }
        }
        
        return $post_type;
    }
} 