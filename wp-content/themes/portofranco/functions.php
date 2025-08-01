<?php
// Funzioni base tema portofranco

if ( ! function_exists( 'portofranco_setup' ) ) {
  function portofranco_setup() {
    // Supporto per il title tag
    add_theme_support( 'title-tag' );
    // Supporto per le immagini in evidenza
    add_theme_support( 'post-thumbnails' );
    // Supporto per i menu
    register_nav_menus( array(
      'primary' => __( 'Menu Principale', 'portofranco' ),
    ) );
    // Supporto per traduzioni
    load_theme_textdomain( 'portofranco', get_template_directory() . '/languages' );
  }
}
add_action( 'after_setup_theme', 'portofranco_setup' );

// Riga 20: Includo CSS custom portofranco
function portofranco_enqueue_styles() {
  // Carica Google Fonts (Inter)
  wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null );
  
  // Carica prima lo style.css del tema
  wp_enqueue_style( 'portofranco-style', get_stylesheet_uri() );
  // Carica il CSS custom
  wp_enqueue_style( 'portofranco-custom', get_template_directory_uri() . '/assets/css/portofranco.css', array('portofranco-style'), null );
}
add_action( 'wp_enqueue_scripts', 'portofranco_enqueue_styles' );

// Riga 30: Includo JavaScript custom portofranco
function portofranco_enqueue_scripts() {
  // Carica il JavaScript per la gestione dello scroll dell'header
  wp_enqueue_script( 'portofranco-header-scroll', get_template_directory_uri() . '/assets/js/header-scroll.js', array(), null, true );
  
  // Carica il JavaScript per il caricamento dinamico del contenuto degli archivi
  wp_enqueue_script( 'portofranco-archive-content-loader', get_template_directory_uri() . '/assets/js/archive-content-loader.js', array(), null, true );
  
  // Aggiungi variabile globale per l'URL base dell'API
  wp_localize_script( 'portofranco-archive-content-loader', 'portofrancoAjax', array(
    'apiBase' => get_rest_url( null, 'pf/v1/post-content/' ),
    'nonce' => wp_create_nonce( 'wp_rest' )
  ) );
}
add_action( 'wp_enqueue_scripts', 'portofranco_enqueue_scripts' );

// Forza l'uso dei template specifici per i custom post types
function portofranco_custom_archive_template( $template ) {
    if ( is_post_type_archive( 'artisti' ) ) {
        $custom_template = locate_template( 'archive-artisti.php' );
        if ( $custom_template ) {
            return $custom_template;
        }
    }
    
    if ( is_post_type_archive( 'agenda' ) ) {
        $custom_template = locate_template( 'archive-agenda.php' );
        if ( $custom_template ) {
            return $custom_template;
        }
    }
    
    return $template;
}
add_filter( 'archive_template', 'portofranco_custom_archive_template' );

 
// Riga 60: Funzione helper per recuperare la descrizione dell'archivio
function portofranco_get_archive_description($post_type = null) {
    // Se non specificato, usa il post type corrente
    if (!$post_type) {
        $post_type = get_post_type();
    }
    
    // Se siamo in un archivio, usa il post type dell'archivio
    if (is_post_type_archive()) {
        $post_type = get_post_type();
    }
    
    // Recupera la descrizione dal plugin
    if (class_exists('PF_Archive_Fields_Manager')) {
        return PF_Archive_Fields_Manager::get_archive_description($post_type);
    }
    
    // Fallback: cerca l'opzione direttamente
    $option_name = "{$post_type}_archive_description";
    return get_option($option_name, '');
}

// Riga 70: Funzione helper per ottenere lo slug del post
function portofranco_get_post_slug($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    return get_post_field('post_name', $post_id);
}
