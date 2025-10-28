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
        
        $all_artworks = array();
        
        for ($floor = 0; $floor <= 3; $floor++) {
            $all_artworks[$floor] = PF_Exhibition_Manager::get_artworks_by_floor($floor);
        }
        
        return rest_ensure_response(array(
            'success' => true,
            'artworks_by_floor' => $all_artworks,
        ));
    }
}

// Initialize the REST API
new PF_Exhibition_REST_API();

