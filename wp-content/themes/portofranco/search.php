<?php
// Riga 1: Template risultati ricerca
get_header();
?>
<main id="main" tabindex="-1">
  <header class="page-header">
    <h1><?php printf( __( 'Risultati per: %s', 'portofranco' ), get_search_query() ); ?></h1>
  </header>
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
        <div class="entry-summary">
          <?php the_excerpt(); ?>
        </div>
      </article>
    <?php endwhile; ?>
    <?php the_posts_navigation(); ?>
  <?php else : ?>
    <p><?php _e( 'Nessun contenuto trovato.', 'portofranco' ); ?></p>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 