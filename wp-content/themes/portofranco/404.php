<?php
// Riga 1: Template 404
get_header();
?>
<main id="main" tabindex="-1">
  <section class="error-404 not-found">
    <h1><?php _e( 'Pagina non trovata', 'portofranco' ); ?></h1>
    <p><?php _e( 'Spiacenti, la pagina che cerchi non esiste.', 'portofranco' ); ?></p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button">&larr; <?php _e( 'Torna alla home', 'portofranco' ); ?></a>
  </section>
</main>
<?php get_footer(); ?> 