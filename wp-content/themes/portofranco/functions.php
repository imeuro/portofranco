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
  // Carica Helvetica Neue
  wp_enqueue_style( 'helvetica-neue-cdn-font', 'https://fonts.cdnfonts.com/css/helvetica-neue-55', array(), null );
  // Carica Google Fonts (Inter)
  // wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap', array(), null );
  
  // Carica prima lo style.css del tema
  wp_enqueue_style( 'portofranco-style', get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . '/style.css') );
  // Carica il CSS custom
  wp_enqueue_style( 'portofranco-custom', get_template_directory_uri() . '/assets/css/portofranco.css', array('portofranco-style'), filemtime(get_stylesheet_directory() . '/assets/css/portofranco.css') );
}
add_action( 'wp_enqueue_scripts', 'portofranco_enqueue_styles' );

// Riga 30: Includo JavaScript custom portofranco
function portofranco_enqueue_scripts() {
  // Carica il JavaScript per la gestione dello scroll dell'header
  wp_enqueue_script( 'portofranco-header-scroll', get_template_directory_uri() . '/assets/js/header-scroll.js', array(), filemtime(get_stylesheet_directory() . '/assets/js/header-scroll.js'), true );
  
  // Carica il JavaScript per il caricamento dinamico del contenuto degli archivi
  wp_enqueue_script( 'portofranco-archive-content-loader', get_template_directory_uri() . '/assets/js/archive-content-loader.js', array(), filemtime(get_stylesheet_directory() . '/assets/js/archive-content-loader.js'), true );
  
  // Aggiungi variabile globale per l'URL base dell'API
  wp_localize_script( 'portofranco-archive-content-loader', 'portofrancoAjax', array(
    'apiBase' => get_rest_url( null, 'pf/v1/' ),
    'nonce' => wp_create_nonce( 'wp_rest' )
  ) );
}
add_action( 'wp_enqueue_scripts', 'portofranco_enqueue_scripts' );

// Forza l'uso dei template specifici per i custom post types
function portofranco_custom_archive_template( $template ) {
    // Controlla se siamo nell'archivio artisti in inglese
    if ( preg_match('/\/en\/artisti/', $_SERVER['REQUEST_URI']) ) {
        $custom_template = locate_template( 'archive-artisti-en.php' );
        if ( $custom_template ) {
            return $custom_template;
        }
    }
    
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

// Riga 80: Supporto per traduzioni avanzato
function portofranco_load_textdomain() {
  load_theme_textdomain( 'portofranco', get_template_directory() . '/languages' );
  
  // Carica anche le traduzioni del plugin
  if (function_exists('load_plugin_textdomain')) {
    load_plugin_textdomain( 'pf', false, dirname(plugin_basename(__FILE__)) . '/languages' );
  }
}
add_action( 'after_setup_theme', 'portofranco_load_textdomain' );

// Riga 90: Funzione per ottenere URL corretto per lingua
function portofranco_get_language_url($url = '') {
  if (function_exists('pll_home_url')) {
    return pll_home_url();
  }
  return home_url($url);
}

// Riga 100: Aggiungi hreflang tags per SEO multilingua
function portofranco_add_hreflang_tags() {
  if (!function_exists('pll_the_languages')) {
    return;
  }
  
  $languages = pll_the_languages(array('raw' => 1, 'hide_if_empty' => 0));
  
  foreach ($languages as $lang) {
    if (!empty($lang['url'])) {
      echo '<link rel="alternate" hreflang="' . esc_attr($lang['slug']) . '" href="' . esc_url($lang['url']) . '" />' . "\n";
    }
  }
  
  // Aggiungi hreflang x-default
  $default_lang = pll_default_language();
  if ($default_lang && isset($languages[$default_lang]['url'])) {
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($languages[$default_lang]['url']) . '" />' . "\n";
  }
}
add_action('wp_head', 'portofranco_add_hreflang_tags');

// Riga 120: Modifica i permalink per supportare le traduzioni
function portofranco_modify_permalinks($permalink, $post) {
  if (function_exists('pll_get_post_language') && function_exists('pll_get_post_translations')) {
    $lang = pll_get_post_language($post->ID);
    if ($lang) {
      // Per i custom post types, gestisci gli slug diversi
      if ($post->post_type === 'artisti' && $lang === 'en') {
        $permalink = str_replace('/artisti/', '/artists/', $permalink);
      } elseif ($post->post_type === 'agenda' && $lang === 'en') {
        $permalink = str_replace('/agenda/', '/events/', $permalink);
      }
    }
  }
  return $permalink;
}
add_filter('post_link', 'portofranco_modify_permalinks', 10, 2);
add_filter('post_type_link', 'portofranco_modify_permalinks', 10, 2);

// Riga 140: Aggiungi meta tags per lingua corrente
function portofranco_add_language_meta_tags() {
  if (function_exists('pll_current_language')) {
    $current_lang = pll_current_language();
    if ($current_lang) {
      echo '<meta property="og:locale" content="' . esc_attr($current_lang) . '" />' . "\n";
      echo '<meta property="og:locale:alternate" content="' . esc_attr($current_lang === 'it' ? 'en' : 'it') . '" />' . "\n";
    }
  }
}
add_action('wp_head', 'portofranco_add_language_meta_tags');

// Riga 150: Funzioni per la gestione della newsletter
function portofranco_get_current_language() {
    // Supporto per Polylang
    if (function_exists('pll_current_language')) {
        return pll_current_language();
    }
    
    // Supporto per WPML
    if (defined('ICL_LANGUAGE_CODE')) {
        return ICL_LANGUAGE_CODE;
    }
    
    // Fallback: controlla l'URL per determinare la lingua
    $current_url = $_SERVER['REQUEST_URI'];
    if (strpos($current_url, '/en/') !== false) {
        return 'en';
    }
    
    return 'it'; // Default italiano
}

// Riga 170: Aggiungi supporto per Contact Form 7 multilingua
function portofranco_cf7_language_support($tag, $unused) {
    if (isset($tag['name']) && $tag['name'] === 'language') {
        $current_lang = portofranco_get_current_language();
        if (is_array($tag)) {
            $tag['values'] = array($current_lang);
            if (class_exists('WPCF7_Pipes')) {
                $tag['pipes'] = new WPCF7_Pipes(array($current_lang));
            }
        }
    }
    return $tag;
}
add_filter('wpcf7_form_tag_data_option', 'portofranco_cf7_language_support', 10, 2);

// Riga 180: Personalizza messaggi Contact Form 7 in base alla lingua
// NOTA: I messaggi vanno configurati manualmente nel pannello admin di Contact Form 7
// per evitare conflitti con il funzionamento interno del plugin

// Riga 200: Aggiungi stili personalizzati per Contact Form 7
function portofranco_cf7_enqueue_styles() {
    if (function_exists('wpcf7_enqueue_scripts')) {
        // Assicurati che Contact Form 7 carichi i suoi stili
        wpcf7_enqueue_scripts();
    }
}
add_action('wp_enqueue_scripts', 'portofranco_cf7_enqueue_styles');
