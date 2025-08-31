<?php
/**
 * Test file per il modulo Custom Order Manager
 * 
 * Questo file può essere utilizzato per testare le funzionalità del modulo
 * quando il sistema WordPress è attivo.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test class per il Custom Order Manager
 */
class PF_Custom_Order_Test {
    
    /**
     * Test della funzionalità di ordinamento
     */
    public static function test_custom_order_functionality() {
        echo "<h2>Test Custom Order Manager</h2>";
        
        // Test 1: Verifica che la classe esista
        if (class_exists('PF_Custom_Order_Manager')) {
            echo "<p style='color: green;'>✓ Classe PF_Custom_Order_Manager trovata</p>";
        } else {
            echo "<p style='color: red;'>✗ Classe PF_Custom_Order_Manager non trovata</p>";
            return;
        }
        
        // Test 2: Verifica che il post type 'artisti' esista
        if (post_type_exists('artisti')) {
            echo "<p style='color: green;'>✓ Post type 'artisti' trovato</p>";
        } else {
            echo "<p style='color: red;'>✗ Post type 'artisti' non trovato</p>";
        }
        
        // Test 3: Verifica che ci siano post di tipo 'artisti'
        $artisti_posts = get_posts(array(
            'post_type' => 'artisti',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
        
        if (!empty($artisti_posts)) {
            echo "<p style='color: green;'>✓ Trovati " . count($artisti_posts) . " post di tipo 'artisti'</p>";
            
            // Test 4: Verifica che alcuni post abbiano ordine personalizzato
            $posts_with_order = 0;
            foreach ($artisti_posts as $post) {
                $custom_order = get_post_meta($post->ID, '_custom_order', true);
                if ($custom_order) {
                    $posts_with_order++;
                }
            }
            
            echo "<p style='color: blue;'>ℹ " . $posts_with_order . " post hanno ordine personalizzato</p>";
            
        } else {
            echo "<p style='color: orange;'>⚠ Nessun post di tipo 'artisti' trovato</p>";
        }
        
        // Test 5: Verifica che i file CSS e JS esistano
        $css_file = PF_PLUGIN_URL . 'modules/custom-order/assets/css/custom-order.css';
        $js_file = PF_PLUGIN_URL . 'modules/custom-order/assets/js/custom-order.js';
        
        $css_exists = file_exists(PF_PLUGIN_DIR . 'modules/custom-order/assets/css/custom-order.css');
        $js_exists = file_exists(PF_PLUGIN_DIR . 'modules/custom-order/assets/js/custom-order.js');
        
        if ($css_exists) {
            echo "<p style='color: green;'>✓ File CSS trovato</p>";
        } else {
            echo "<p style='color: red;'>✗ File CSS non trovato</p>";
        }
        
        if ($js_exists) {
            echo "<p style='color: green;'>✓ File JavaScript trovato</p>";
        } else {
            echo "<p style='color: red;'>✗ File JavaScript non trovato</p>";
        }
        
        // Test 6: Verifica che la pagina di ordinamento sia accessibile
        $order_page_url = admin_url('edit.php?post_type=artisti&page=artisti-order');
        echo "<p><a href='{$order_page_url}' target='_blank'>🔗 Apri pagina di ordinamento</a></p>";
        
        // Test 7: Mostra alcuni post di esempio con il loro ordine
        if (!empty($artisti_posts)) {
            echo "<h3>Post di esempio:</h3>";
            echo "<ul>";
            foreach (array_slice($artisti_posts, 0, 5) as $post) {
                $custom_order = get_post_meta($post->ID, '_custom_order', true);
                $order_text = $custom_order ? " (Ordine: {$custom_order})" : " (Ordine alfabetico)";
                echo "<li>{$post->post_title}{$order_text}</li>";
            }
            echo "</ul>";
        }
        
        echo "<hr>";
        echo "<p><strong>Note:</strong></p>";
        echo "<ul>";
        echo "<li>Per testare il drag & drop, vai alla pagina di ordinamento</li>";
        echo "<li>Per impostare l'ordine manualmente, modifica un post artista e usa il meta box</li>";
        echo "<li>L'ordine personalizzato viene applicato automaticamente nel frontend</li>";
        echo "</ul>";
    }
    
    /**
     * Test della query di ordinamento
     */
    public static function test_order_query() {
        echo "<h3>Test Query di Ordinamento:</h3>";
        
        // Query con ordine personalizzato
        $ordered_query = new WP_Query(array(
            'post_type' => 'artisti',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'meta_key' => '_custom_order',
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
        ));
        
        if ($ordered_query->have_posts()) {
            echo "<p style='color: green;'>✓ Query di ordinamento funzionante</p>";
            echo "<p>Primi 5 post nell'ordine personalizzato:</p>";
            echo "<ol>";
            while ($ordered_query->have_posts()) {
                $ordered_query->the_post();
                $custom_order = get_post_meta(get_the_ID(), '_custom_order', true);
                $order_text = $custom_order ? " (Ordine: {$custom_order})" : " (Alfabetico)";
                echo "<li>" . get_the_title() . $order_text . "</li>";
            }
            echo "</ol>";
            wp_reset_postdata();
        } else {
            echo "<p style='color: red;'>✗ Query di ordinamento non funzionante</p>";
        }
    }
}

// Se questo file viene chiamato direttamente, mostra i test
if (isset($_GET['test_custom_order']) && current_user_can('manage_options')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-info">';
        PF_Custom_Order_Test::test_custom_order_functionality();
        PF_Custom_Order_Test::test_order_query();
        echo '</div>';
    });
}
