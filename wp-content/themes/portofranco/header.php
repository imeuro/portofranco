<?php
// Apertura documento HTML5 e head
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php wp_title('|', true, 'right'); ?></title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> tabindex="0">
  <a class="skip-link screen-reader-text" href="#main" tabindex="1"><?php _e('Salta al contenuto', 'portofranco'); ?></a>
  <header id="masthead" role="banner">
    <div class="site-branding">
      <h1 class="site-title">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
            <?php if(is_front_page()): ?>
              <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-portofranco.png" width="360" height="352" alt="Portofranco">
            <?php else: ?>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-portofranco-blk.png" width="360" height="352" alt="Portofranco">
            <?php endif; ?>
        </a>
      </h1>
    </div>
    <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Menu principale">
      <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'menu_id'        => 'primary-menu',
        ) );
      ?>
    </nav>
  </header> 