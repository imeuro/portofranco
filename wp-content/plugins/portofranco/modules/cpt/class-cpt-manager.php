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
    }
    
    /**
     * Register all custom post types
     */
    public function register_post_types() {
        $this->register_lavori_post_type();
    }
    
    /**
     * Register all custom taxonomies
     */
    public function register_taxonomies() {
        // Add taxonomies here if needed
    }
    
    /**
     * Register 'lavori' custom post type
     */
    private function register_lavori_post_type() {
        $labels = array(
            'name'                  => _x('Lavori', 'Post type general name', 'pf'),
            'singular_name'         => _x('Lavoro', 'Post type singular name', 'pf'),
            'menu_name'             => _x('Lavori', 'Admin Menu text', 'pf'),
            'name_admin_bar'        => _x('Lavoro', 'Add New on Toolbar', 'pf'),
            'add_new'               => __('Aggiungi Nuovo', 'pf'),
            'add_new_item'          => __('Aggiungi Nuovo Lavoro', 'pf'),
            'new_item'              => __('Nuovo Lavoro', 'pf'),
            'edit_item'             => __('Modifica Lavoro', 'pf'),
            'view_item'             => __('Visualizza Lavoro', 'pf'),
            'all_items'             => __('Tutti i Lavori', 'pf'),
            'search_items'          => __('Cerca Lavori', 'pf'),
            'parent_item_colon'     => __('Lavori Padre:', 'pf'),
            'not_found'             => __('Nessun lavoro trovato.', 'pf'),
            'not_found_in_trash'    => __('Nessun lavoro trovato nel cestino.', 'pf'),
            'featured_image'        => _x('Immagine del Lavoro', 'Overrides the "Featured Image" phrase for this post type.', 'pf'),
            'set_featured_image'    => _x('Imposta immagine del lavoro', 'Overrides the "Set featured image" phrase for this post type.', 'pf'),
            'remove_featured_image' => _x('Rimuovi immagine del lavoro', 'Overrides the "Remove featured image" phrase for this post type.', 'pf'),
            'use_featured_image'    => _x('Usa come immagine del lavoro', 'Overrides the "Use as featured image" phrase for this post type.', 'pf'),
            'archives'              => _x('Archivio Lavori', 'The post type archive label used in nav menus.', 'pf'),
            'insert_into_item'      => _x('Inserisci nel lavoro', 'Overrides the "Insert into post" phrase (used when inserting media into a post).', 'pf'),
            'uploaded_to_this_item' => _x('Caricato in questo lavoro', 'Overrides the "Uploaded to this post" phrase (used when viewing media attached to a post).', 'pf'),
            'filter_items_list'     => _x('Filtra lista lavori', 'Screen reader text for the filter links.', 'pf'),
            'items_list_navigation' => _x('Navigazione lista lavori', 'Screen reader text for the pagination.', 'pf'),
            'items_list'            => _x('Lista lavori', 'Screen reader text for the items list.', 'pf'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'lavori'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-portfolio',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => false, // Non visibile in REST API
        );
        
        register_post_type('lavori', $args);
    }
} 