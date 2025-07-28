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

// Riga X: Includo CSS custom portofranco
function portofranco_enqueue_styles() {
  // Carica Google Fonts (Inter)
  wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null );
  
  // Carica prima lo style.css del tema
  wp_enqueue_style( 'portofranco-style', get_stylesheet_uri() );
  // Carica il CSS custom
  wp_enqueue_style( 'portofranco-custom', get_template_directory_uri() . '/assets/css/portofranco.css', array('portofranco-style'), null );
}
add_action( 'wp_enqueue_scripts', 'portofranco_enqueue_styles' );

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

 