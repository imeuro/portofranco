<?php
// Template archivio artisti
get_header();
?>
<!-- Template: archive-artisti.php -->
<main id="main" tabindex="-1" role="main">
  
  <?php if ( have_posts() ) : ?>
    <div class="artisti-grid">


      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>

        <div class="side-content">
          <ul class="side-content-inner">
            <?php while ( have_posts() ) : the_post(); ?>
              <li><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></li>
            <?php endwhile; ?>
          </ul>
        </div>

          
        <div class="entry-content big-text">
          <?php the_content(); ?>
        </div>

      </article>

    </div>
    
    
  <?php else : ?>
    <div class="agenda-grid">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>
        <div class="side-content">
          <div class="side-content-inner">
            <p><?php _e('Nessun evento trovato.', 'portofranco'); ?></p>
          </div>
        </div>
        <div class="entry-content big-text"></div>
      </article>
    </div>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 