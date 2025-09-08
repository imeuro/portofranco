<?php
// Apertura documento HTML5 e head
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php wp_title('| ', true, 'right'); ?>Portofranco</title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> tabindex="0">
  <a class="skip-link screen-reader-text" href="#main" tabindex="1"><?php _e('Salta al contenuto', 'portofranco'); ?></a>
  <header id="masthead" role="banner">
    <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
        <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" class="menu-icon">
          <line y1="1" x2="24" y2="1" stroke="#000" stroke-width="2" class="top"/>
          <line y1="11" x2="24" y2="11" stroke="#000" stroke-width="2" class="middle"/>
          <line y1="21" x2="24" y2="21" stroke="#000" stroke-width="2" class="bottom"/>
      </svg>
    </button>
    <div class="site-branding">
      <h1 class="site-title">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
            <?php if(is_front_page()): ?>
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-portofranco.svg" width="360" height="352" alt="Portofranco">
            <?php else: ?>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-portofranco-blk.svg" width="360" height="352" alt="Portofranco">
            <?php endif; ?>
        </a>
      </h1>
    </div>
    
    <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Menu principale">
      <div class="menu-pre-mobile">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-portofranco-blk.svg" width="180" height="176" alt="Portofranco">
        </a>
      </div>
      <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'menu_id'        => 'primary-menu',
        ) );
      ?>

            <!-- Selettore lingua -->
    <?php if (function_exists('pll_the_languages')): ?>
    <div class="language-switcher">
      <?php
        pll_the_languages(array(
          'dropdown' => 0,
          'show_flags' => 0,
          'show_names' => 1,
          'hide_if_empty' => 0,
          'force_home' => 0,
          'hide_if_no_translation' => 0,
          'raw' => 0
        ));
      ?>
    </div>
    <?php endif; ?>
      <div class="menu-post-mobile">
          <h3 class="footer-label small-label"><?php _e('CONTATTI', 'portofranco'); ?></h3>
          <p>
              <a href="mailto:info@portofranco.eu">info@portofranco.eu</a>
          <br>
              <a href="https://wa.me/393317907368">+39 331 7907368</a>
          <br>
              <a href="https://www.instagram.com/_portofranco_/">Instagram</a>
          <br>
              <a href="<?php echo portofranco_get_page_link('about', 'about-en') ; ?>#newsletter"><?php _e('Iscriviti alla newsletter', 'portofranco'); ?></a>
          </p>
      </div>
    </nav>

  </header>
