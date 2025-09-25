<?php
/**
 * Database Migration Script
 * 
 * Questo script aggiorna la tabella newsletter esistente
 * aggiungendo i campi cognome e lingua
 * 
 * Eseguire questo script una sola volta dopo l'aggiornamento del modulo
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Migrate newsletter table to add surname and language fields
 */
function pf_newsletter_migrate_database() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'pf_newsletter_subscriptions';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        return false; // Table doesn't exist yet
    }
    
    // Check if surname column exists
    $surname_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'surname'");
    if (empty($surname_exists)) {
        // Add surname column
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN surname varchar(100) DEFAULT '' AFTER name");
    }
    
    // Check if phone column exists
    $phone_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'phone'");
    if (empty($phone_exists)) {
        // Add phone column
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN phone varchar(20) DEFAULT '' AFTER surname");
    }
    
    // Check if language column exists
    $language_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'language'");
    if (empty($language_exists)) {
        // Add language column
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN language varchar(5) DEFAULT 'ITA' AFTER phone");
        
        // Add index for language
        $wpdb->query("ALTER TABLE $table_name ADD INDEX language (language)");
    }
    
    return true;
}

// Auto-migrate on plugin activation
add_action('plugins_loaded', function() {
    // Only run migration if not already done
    if (!get_option('pf_newsletter_migrated_v2')) {
        if (pf_newsletter_migrate_database()) {
            update_option('pf_newsletter_migrated_v2', true);
        }
    }
});

// Manual migration function (can be called from admin)
function pf_newsletter_manual_migrate() {
    if (current_user_can('manage_options') && isset($_GET['pf_migrate_newsletter'])) {
        if (pf_newsletter_migrate_database()) {
            wp_die('Migrazione completata con successo!', 'Migrazione Newsletter', array('response' => 200));
        } else {
            wp_die('Errore durante la migrazione.', 'Errore Migrazione', array('response' => 500));
        }
    }
}
add_action('admin_init', 'pf_newsletter_manual_migrate');
