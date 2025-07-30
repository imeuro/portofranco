<?php
/**
 * REST API Manager
 * Gestisce gli endpoint REST API personalizzati
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF REST API Manager Class
 */
class PF_REST_API_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register custom REST routes
     */
    public function register_routes() {
        // Endpoint per recuperare il contenuto di un post
        register_rest_route('pf/v1', '/post-content/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_post_content'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
    }
    
    /**
     * Get post content
     */
    public function get_post_content($request) {
        $post_id = $request->get_param('id');
        $post = get_post($post_id);
        
        if (!$post) {
            return new WP_Error('post_not_found', 'Post non trovato', array('status' => 404));
        }
        
        // Verifica che il post sia pubblicato
        if ($post->post_status !== 'publish') {
            return new WP_Error('post_not_public', 'Post non pubblico', array('status' => 403));
        }
        
        // Prepara la risposta
        $response = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => $post->post_excerpt,
            'slug' => $post->post_name,
            'post_type' => $post->post_type,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
        );
        
        // Aggiungi featured image se presente
        if (has_post_thumbnail($post->ID)) {
            $thumbnail_id = get_post_thumbnail_id($post->ID);
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
            $response['featured_image'] = $thumbnail_url;
        }
        
        return new WP_REST_Response($response, 200);
    }
}

// Initialize the REST API manager
new PF_REST_API_Manager();
