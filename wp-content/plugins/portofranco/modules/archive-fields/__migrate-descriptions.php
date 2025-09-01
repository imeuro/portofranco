<?php
/**
 * Migration Script for Archive Descriptions
 * Converte le descrizioni esistenti dal formato vecchio al nuovo formato multilingua
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Archive Descriptions Migration Class
 */
class PF_Archive_Descriptions_Migration {
    
    /**
     * Supported post types
     */
    private $supported_post_types = array('artisti', 'agenda');
    
    /**
     * Run migration
     */
    public static function run_migration() {
        $migration = new self();
        $migration->migrate_descriptions();
    }
    
    /**
     * Migrate descriptions from old format to new multilingual format
     */
    public function migrate_descriptions() {
        $migrated_count = 0;
        $errors = array();
        
        foreach ($this->supported_post_types as $post_type) {
            $old_option_name = "{$post_type}_archive_description";
            $new_option_name_it = "{$post_type}_archive_description_it";
            $new_option_name_en = "{$post_type}_archive_description_en";
            
            // Controlla se esiste l'opzione vecchia
            $old_description = get_option($old_option_name, '');
            
            if (!empty($old_description)) {
                // Migra la descrizione esistente come italiano (lingua di default)
                $migrated = update_option($new_option_name_it, $old_description);
                
                if ($migrated) {
                    // Rimuovi l'opzione vecchia
                    delete_option($old_option_name);
                    $migrated_count++;
                    
                    error_log("PF Migration: Migrata descrizione per {$post_type} (italiano)");
                } else {
                    $errors[] = "Errore durante la migrazione della descrizione per {$post_type}";
                }
            } else {
                // Se non esiste una descrizione vecchia, controlla se esiste già una nuova
                $existing_it = get_option($new_option_name_it, '');
                $existing_en = get_option($new_option_name_en, '');
                
                if (empty($existing_it) && empty($existing_en)) {
                    error_log("PF Migration: Nessuna descrizione esistente trovata per {$post_type}");
                }
            }
        }
        
        // Log del risultato della migrazione
        if ($migrated_count > 0) {
            error_log("PF Migration: Migrate {$migrated_count} descrizioni con successo");
        }
        
        if (!empty($errors)) {
            error_log("PF Migration: Errori durante la migrazione: " . implode(', ', $errors));
        }
        
        return array(
            'migrated_count' => $migrated_count,
            'errors' => $errors
        );
    }
    
    /**
     * Check if migration is needed
     */
    public static function needs_migration() {
        $migration = new self();
        
        foreach ($migration->supported_post_types as $post_type) {
            $old_option_name = "{$post_type}_archive_description";
            $old_description = get_option($old_option_name, '');
            
            if (!empty($old_description)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get migration status
     */
    public static function get_migration_status() {
        $migration = new self();
        $status = array();
        
        foreach ($migration->supported_post_types as $post_type) {
            $old_option_name = "{$post_type}_archive_description";
            $new_option_name_it = "{$post_type}_archive_description_it";
            $new_option_name_en = "{$post_type}_archive_description_en";
            
            $old_description = get_option($old_option_name, '');
            $new_description_it = get_option($new_option_name_it, '');
            $new_description_en = get_option($new_option_name_en, '');
            
            $status[$post_type] = array(
                'has_old_format' => !empty($old_description),
                'has_new_format_it' => !empty($new_description_it),
                'has_new_format_en' => !empty($new_description_en),
                'needs_migration' => !empty($old_description) && empty($new_description_it)
            );
        }
        
        return $status;
    }
}

// Esegui la migrazione automatica se necessario
add_action('admin_init', function() {
    if (PF_Archive_Descriptions_Migration::needs_migration()) {
        PF_Archive_Descriptions_Migration::run_migration();
    }
});
