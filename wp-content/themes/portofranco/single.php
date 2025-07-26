<?php
// Riga 1: Template singolo post
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
      <h1><?php the_title(); ?></h1>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
      <footer class="entry-footer">
        <span class="posted-on"><?php the_time( get_option( 'date_format' ) ); ?></span>
        <span class="byline"> <?php _e('di', 'portofranco'); ?> <?php the_author(); ?></span>
      </footer>
    </article>
    
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?> 