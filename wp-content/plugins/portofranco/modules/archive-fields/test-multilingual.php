<?php
/**
 * Test Script for Multilingual Archive Descriptions
 * Verifica il funzionamento del sistema multilingua
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Archive Descriptions Test Class
 */
class PF_Archive_Descriptions_Test {
    
    /**
     * Run all tests
     */
    public static function run_tests() {
        $test = new self();
        
        echo '<div style="background: #f5f5f5; padding: 20px; margin: 20px; border: 1px solid #ddd;">';
        echo '<h2>Test Sistema Multilingua Descrizioni Archivi</h2>';
        
        $test->test_migration_status();
        $test->test_language_detection();
        $test->test_description_retrieval();
        $test->test_admin_interface();
        
        echo '</div>';
    }
    
    /**
     * Test migration status
     */
    private function test_migration_status() {
        echo '<h3>1. Stato Migrazione</h3>';
        
        if (class_exists('PF_Archive_Descriptions_Migration')) {
            $status = PF_Archive_Descriptions_Migration::get_migration_status();
            
            foreach ($status as $post_type => $info) {
                echo '<p><strong>' . ucfirst($post_type) . ':</strong></p>';
                echo '<ul>';
                echo '<li>Formato vecchio presente: ' . ($info['has_old_format'] ? 'Sì' : 'No') . '</li>';
                echo '<li>Formato nuovo IT: ' . ($info['has_new_format_it'] ? 'Sì' : 'No') . '</li>';
                echo '<li>Formato nuovo EN: ' . ($info['has_new_format_en'] ? 'Sì' : 'No') . '</li>';
                echo '<li>Necessita migrazione: ' . ($info['needs_migration'] ? 'Sì' : 'No') . '</li>';
                echo '</ul>';
            }
        } else {
            echo '<p style="color: red;">Classe di migrazione non trovata!</p>';
        }
    }
    
    /**
     * Test language detection
     */
    private function test_language_detection() {
        echo '<h3>2. Rilevamento Lingua</h3>';
        
        // Test Polylang
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
            echo '<p><strong>Polylang lingua corrente:</strong> ' . ($current_lang ? $current_lang : 'Non rilevata') . '</p>';
        } else {
            echo '<p><strong>Polylang:</strong> Non attivo</p>';
        }
        
        // Test fallback URL
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        echo '<p><strong>URL corrente:</strong> ' . esc_html($current_url) . '</p>';
        
        if (strpos($current_url, '/en/') !== false) {
            echo '<p><strong>Rilevamento URL:</strong> Inglese</p>';
        } else {
            echo '<p><strong>Rilevamento URL:</strong> Italiano</p>';
        }
    }
    
    /**
     * Test description retrieval
     */
    private function test_description_retrieval() {
        echo '<h3>3. Recupero Descrizioni</h3>';
        
        if (class_exists('PF_Archive_Fields_Manager')) {
            $post_types = array('artisti', 'agenda');
            
            foreach ($post_types as $post_type) {
                echo '<h4>' . ucfirst($post_type) . '</h4>';
                
                // Test descrizione corrente
                $current_desc = PF_Archive_Fields_Manager::get_archive_description($post_type);
                echo '<p><strong>Descrizione corrente:</strong> ' . (empty($current_desc) ? 'Vuota' : 'Presente (' . strlen($current_desc) . ' caratteri)') . '</p>';
                
                // Test descrizioni per lingua specifica
                $desc_it = PF_Archive_Fields_Manager::get_archive_description_by_language($post_type, 'it');
                $desc_en = PF_Archive_Fields_Manager::get_archive_description_by_language($post_type, 'en');
                
                echo '<p><strong>Descrizione IT:</strong> ' . (empty($desc_it) ? 'Vuota' : 'Presente (' . strlen($desc_it) . ' caratteri)') . '</p>';
                echo '<p><strong>Descrizione EN:</strong> ' . (empty($desc_en) ? 'Vuota' : 'Presente (' . strlen($desc_en) . ' caratteri)') . '</p>';
            }
        } else {
            echo '<p style="color: red;">Classe Archive Fields Manager non trovata!</p>';
        }
    }
    
    /**
     * Test admin interface
     */
    private function test_admin_interface() {
        echo '<h3>4. Interfaccia Admin</h3>';
        
        // Test se siamo nella pagina admin corretta
        $current_page = $_GET['page'] ?? '';
        $is_archive_settings = strpos($current_page, '-archive-settings') !== false;
        
        echo '<p><strong>Pagina corrente:</strong> ' . esc_html($current_page) . '</p>';
        echo '<p><strong>È pagina impostazioni archivio:</strong> ' . ($is_archive_settings ? 'Sì' : 'No') . '</p>';
        
        if ($is_archive_settings) {
            $post_type = str_replace('-archive-settings', '', $current_page);
            echo '<p><strong>Post type rilevato:</strong> ' . esc_html($post_type) . '</p>';
            
            // Test opzioni registrate
            $option_it = get_option("{$post_type}_archive_description_it", '');
            $option_en = get_option("{$post_type}_archive_description_en", '');
            
            echo '<p><strong>Opzione IT registrata:</strong> ' . (empty($option_it) ? 'Vuota' : 'Presente') . '</p>';
            echo '<p><strong>Opzione EN registrata:</strong> ' . (empty($option_en) ? 'Vuota' : 'Presente') . '</p>';
        }
    }
}

// Aggiungi un hook per mostrare i test solo agli amministratori
add_action('admin_notices', function() {
    if (current_user_can('manage_options') && isset($_GET['test_multilingual']) && $_GET['test_multilingual'] === '1') {
        PF_Archive_Descriptions_Test::run_tests();
    }
});
