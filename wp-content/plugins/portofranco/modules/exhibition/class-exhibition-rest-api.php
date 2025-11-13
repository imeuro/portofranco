<?php
/**
 * Exhibition REST API
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Exhibition REST API Class
 */
class PF_Exhibition_REST_API {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('pf/v1', '/exhibition/floor/(?P<floor>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_floor_artworks'),
            'permission_callback' => '__return_true',
            'args' => array(
                'floor' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param >= 0 && $param <= 3;
                    },
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        register_rest_route('pf/v1', '/exhibition/all', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_artworks'),
            'permission_callback' => '__return_true',
        ));
    }
    
    /**
     * Get artworks for a specific floor
     */
    public function get_floor_artworks($request) {
        $floor = $request['floor'];
        
        if (!class_exists('PF_Exhibition_Manager')) {
            return new WP_Error('class_not_found', __('Exhibition Manager class not found', 'pf'), array('status' => 500));
        }
        
        $artworks = PF_Exhibition_Manager::get_artworks_by_floor($floor);
        
        return rest_ensure_response(array(
            'success' => true,
            'floor' => $floor,
            'count' => count($artworks),
            'artworks' => $artworks,
        ));
    }
    
    /**
     * Get all artworks grouped by floor
     */
    public function get_all_artworks($request) {
        if (!class_exists('PF_Exhibition_Manager')) {
            return new WP_Error('class_not_found', __('Exhibition Manager class not found', 'pf'), array('status' => 500));
        }
        
        $args = array(
            'post_type' => array('artisti', 'special-projects'),
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
        $all_artworks = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $artworks = get_post_meta(get_the_ID(), '_pf_artworks', true);
                
                if (is_array($artworks)) {
                    foreach ($artworks as $artwork) {
                        $floor = isset($artwork['floor']) ? $artwork['floor'] : '';
                        
                        if ($floor === '') {
                            continue;
                        }
                        
                        // Initialize floor array if it doesn't exist
                        if (!isset($all_artworks[$floor])) {
                            $all_artworks[$floor] = array();
                        }
                        
                        $image_id = isset($artwork['image_id']) ? intval($artwork['image_id']) : 0;
                        $image_url = '';
                        
                        if ($image_id > 0) {
                            $image_url = wp_get_attachment_image_url($image_id, 'medium_large');
                            if (!$image_url) {
                                $image_url = '';
                            }
                        }
                        
                        $all_artworks[$floor][] = array(
                            'artist_id' => get_the_ID(),
                            'artist_name' => html_entity_decode(get_the_title(), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'artist_url' => get_permalink(),
                            'artwork_title' => html_entity_decode($artwork['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'artwork_description' => html_entity_decode($artwork['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'image_id' => $image_id,
                            'image_url' => $image_url,
                            'position_x' => $artwork['position_x'],
                            'position_y' => $artwork['position_y'],
                        );
                    }
                }
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'artworks_by_floor' => $all_artworks,
        ));
    }
}

// Initialize the REST API
new PF_Exhibition_REST_API();

