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
        
        // Endpoint per recuperare i post dell'agenda per anno/mese
        register_rest_route('pf/v1', '/agenda-posts/(?P<year>\d{4})/(?P<month>\d{1,2})', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_agenda_posts'),
            'permission_callback' => '__return_true',
            'args' => array(
                'year' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param) && strlen($param) === 4;
                    }
                ),
                'month' => array(
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param) && $param >= 1 && $param <= 12;
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
    
    /**
     * Get agenda posts for a specific year/month
     */
    public function get_agenda_posts($request) {
        $year = $request->get_param('year');
        $month = $request->get_param('month');
        
        // Query per recuperare i post dell'agenda
        // Prima prova con meta fields diretti
        $args = array(
            'post_type' => 'agenda',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'inizio_evento_anno',
                    'value' => $year,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                ),
                array(
                    'key' => 'inizio_evento_mese',
                    'value' => $month,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'inizio_evento_anno',
            'order' => 'ASC'
        );
        
        $query = new WP_Query($args);
        
        // Se non ci sono risultati, prova con una query più ampia e filtra per ACF
        if (!$query->have_posts() && function_exists('get_field')) {
            $args = array(
                'post_type' => 'agenda',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'ASC'
            );
            
            $query = new WP_Query($args);
            
            // Filtra i risultati per anno e mese usando i campi ACF
            if ($query->have_posts()) {
                $filtered_posts = array();
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    
                    $inizio_evento = get_field('inizio_evento', $post_id);
                    $anno = null;
                    $mese = null;
                    
                    if ($inizio_evento) {
                        // Prova diverse strutture possibili per anno
                        if (is_array($inizio_evento)) {
                            if (isset($inizio_evento['anno'])) {
                                $anno = $inizio_evento['anno'];
                            } elseif (isset($inizio_evento['year'])) {
                                $anno = $inizio_evento['year'];
                            } elseif (isset($inizio_evento['data']['anno'])) {
                                $anno = $inizio_evento['data']['anno'];
                            }
                            
                            // Prova diverse strutture possibili per mese
                            if (isset($inizio_evento['mese'])) {
                                $mese = $inizio_evento['mese'];
                            } elseif (isset($inizio_evento['month'])) {
                                $mese = $inizio_evento['month'];
                            } elseif (isset($inizio_evento['data']['mese'])) {
                                $mese = $inizio_evento['data']['mese'];
                            }
                            
                            // Se il mese è una stringa (es. "novembre"), convertilo in numero
                            if (is_string($mese)) {
                                $mesi_nomi = array(
                                    'gennaio' => 1, 'febbraio' => 2, 'marzo' => 3, 'aprile' => 4,
                                    'maggio' => 5, 'giugno' => 6, 'luglio' => 7, 'agosto' => 8,
                                    'settembre' => 9, 'ottobre' => 10, 'novembre' => 11, 'dicembre' => 12
                                );
                                $mese_lower = strtolower($mese);
                                if (isset($mesi_nomi[$mese_lower])) {
                                    $mese = $mesi_nomi[$mese_lower];
                                }
                            }
                        } elseif (is_string($inizio_evento)) {
                            // Se è una stringa, potrebbe essere una data
                            $date = DateTime::createFromFormat('Y-m-d', $inizio_evento);
                            if ($date) {
                                $anno = $date->format('Y');
                                $mese = $date->format('n'); // Mese senza zero iniziale
                            }
                        }
                    }
                    
                    // Se non troviamo anno/mese nel campo inizio_evento, prova altri campi
                    if (!$anno) {
                        $anno = get_field('anno', $post_id);
                    }
                    if (!$anno) {
                        $anno = get_field('year', $post_id);
                    }
                    if (!$mese) {
                        $mese = get_field('mese', $post_id);
                    }
                    if (!$mese) {
                        $mese = get_field('month', $post_id);
                    }
                    
                    if ($anno == $year && $mese == $month) {
                        $filtered_posts[] = $post_id;
                    }
                }
                wp_reset_postdata();
                
                // Ricrea la query con i post filtrati
                if (!empty($filtered_posts)) {
                    $args = array(
                        'post_type' => 'agenda',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'post__in' => $filtered_posts,
                        'orderby' => 'date',
                        'order' => 'ASC'
                    );
                    $query = new WP_Query($args);
                }
            }
        }
        
        $query = new WP_Query($args);
        $posts = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Recupera anno e mese dell'evento - prima prova meta fields diretti, poi ACF
                $event_year = get_post_meta($post_id, 'inizio_evento_anno', true);
                $event_month = get_post_meta($post_id, 'inizio_evento_mese', true);
                
                // Se non ci sono meta fields diretti, prova con ACF
                if (empty($event_year) || empty($event_month)) {
                    $inizio_evento = function_exists('get_field') ? get_field('inizio_evento', $post_id) : null;
                    
                    if ($inizio_evento) {
                        // Prova diverse strutture possibili per anno
                        if (is_array($inizio_evento)) {
                            if (isset($inizio_evento['anno'])) {
                                $event_year = $inizio_evento['anno'];
                            } elseif (isset($inizio_evento['year'])) {
                                $event_year = $inizio_evento['year'];
                            } elseif (isset($inizio_evento['data']['anno'])) {
                                $event_year = $inizio_evento['data']['anno'];
                            }
                            
                            // Prova diverse strutture possibili per mese
                            if (isset($inizio_evento['mese'])) {
                                $event_month = $inizio_evento['mese'];
                            } elseif (isset($inizio_evento['month'])) {
                                $event_month = $inizio_evento['month'];
                            } elseif (isset($inizio_evento['data']['mese'])) {
                                $event_month = $inizio_evento['data']['mese'];
                            }
                            
                            // Se il mese è una stringa (es. "novembre"), convertilo in numero
                            if (is_string($event_month)) {
                                $mesi_nomi = array(
                                    'gennaio' => 1, 'febbraio' => 2, 'marzo' => 3, 'aprile' => 4,
                                    'maggio' => 5, 'giugno' => 6, 'luglio' => 7, 'agosto' => 8,
                                    'settembre' => 9, 'ottobre' => 10, 'novembre' => 11, 'dicembre' => 12
                                );
                                $mese_lower = strtolower($event_month);
                                if (isset($mesi_nomi[$mese_lower])) {
                                    $event_month = $mesi_nomi[$mese_lower];
                                }
                            }
                        } elseif (is_string($inizio_evento)) {
                            // Se è una stringa, potrebbe essere una data
                            $date = DateTime::createFromFormat('Y-m-d', $inizio_evento);
                            if ($date) {
                                $event_year = $date->format('Y');
                                $event_month = $date->format('n'); // Mese senza zero iniziale
                            }
                        }
                    }
                    
                    // Se ancora non troviamo anno/mese, prova altri campi
                    if (empty($event_year)) {
                        $event_year = get_field('anno', $post_id);
                    }
                    if (empty($event_year)) {
                        $event_year = get_field('year', $post_id);
                    }
                    if (empty($event_month)) {
                        $event_month = get_field('mese', $post_id);
                    }
                    if (empty($event_month)) {
                        $event_month = get_field('month', $post_id);
                    }
                    
                    // Se il mese è una stringa (es. "novembre"), convertilo in numero
                    if (is_string($event_month)) {
                        $mesi_nomi = array(
                            'gennaio' => 1, 'febbraio' => 2, 'marzo' => 3, 'aprile' => 4,
                            'maggio' => 5, 'giugno' => 6, 'luglio' => 7, 'agosto' => 8,
                            'settembre' => 9, 'ottobre' => 10, 'novembre' => 11, 'dicembre' => 12
                        );
                        $mese_lower = strtolower($event_month);
                        if (isset($mesi_nomi[$mese_lower])) {
                            $event_month = $mesi_nomi[$mese_lower];
                        }
                    }
                }
                
                // Recupera start_date e end_date (campi ACF)
                $start_date = function_exists('get_field') ? get_field('inizio_evento', $post_id) : get_post_meta($post_id, 'start_date', true);
                $end_date = function_exists('get_field') ? get_field('fine_evento', $post_id) : get_post_meta($post_id, 'end_date', true);
                
                // Costruisci la data formattata
                $formatted_date = $start_date['giorno'] . ' ' . $start_date['mese']['label'] . ' ' . $start_date['anno'] . ' dalle ' . $start_date['ora'];
                

                if ($end_date && $end_date['ora']) {
                    $formatted_date .= ' alle ' . $end_date['ora'];
                    if ($end_date['giorno'] && $end_date['giorno'] != $start_date['giorno']) {
                        $formatted_date .= ' del ' . $end_date['giorno'] . ' ' . $end_date['mese']['label'] . ' ' . $end_date['anno'];
                    }
                }
                
                // Recupera featured image se presente
                $featured_image = '';
                if (has_post_thumbnail($post_id)) {
                    $thumbnail_id = get_post_thumbnail_id($post_id);
                    $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'medium_large');
                    $featured_image = $thumbnail_url;
                }
                
                $posts[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'link' => get_permalink(),
                    'date' => $event_date ?? '',
                    'formatted_date' => $formatted_date,
                    'content' => apply_filters('the_content', get_the_content()),
                    'year' => $event_year,
                    'month' => $event_month,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'featured_image' => $featured_image
                );
            }
            wp_reset_postdata();
        }
        
        // Nome del mese in italiano
        $month_names = array(
            '1' => 'Gennaio', '2' => 'Febbraio', '3' => 'Marzo', '4' => 'Aprile',
            '5' => 'Maggio', '6' => 'Giugno', '7' => 'Luglio', '8' => 'Agosto',
            '9' => 'Settembre', '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'
        );
        
        $month_name = isset($month_names[$month]) ? $month_names[$month] : $month;
        
        // Prepara la risposta
        $response = array(
            'year' => $year,
            'month' => $month,
            'month_name' => $month_name,
            'posts' => $posts,
            'total_posts' => count($posts)
        );
        
        return new WP_REST_Response($response, 200);
    }
}

// Initialize the REST API manager
new PF_REST_API_Manager();
