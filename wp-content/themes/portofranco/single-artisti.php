<?php
// Template singolo artista
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <div class="artisti-grid">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">

        <div class="side-content">
          <h1 class="page-title small-label"><?php the_title(); ?></h1>
        </div>
        
        <div id="main-textarea" class="entry-content mid-text">
          <?php the_content(); ?>
        </div>
        
      </article>
      
    <?php endwhile; endif; ?>
  </div>
</main>
<?php get_footer(); ?> 