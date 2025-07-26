<?php
// Template singolo artista
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
      <header class="entry-header">
        <h1><?php the_title(); ?></h1>
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="artista-image">
            <?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
          </div>
        <?php endif; ?>
      </header>
      
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
      
      <footer class="entry-footer">
        <span class="posted-on"><?php _e('Pubblicato il', 'portofranco'); ?> <?php the_time( get_option( 'date_format' ) ); ?></span>
      </footer>
    </article>
    
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?> 