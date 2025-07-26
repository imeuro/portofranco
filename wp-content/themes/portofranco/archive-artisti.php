<?php
// Template archivio artisti
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <header class="page-header">
    <h1><?php _e('Artisti', 'portofranco'); ?></h1>
    <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
  </header>
  
  <?php if ( have_posts() ) : ?>
    <div class="artisti-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('artista-card'); ?> tabindex="0">
          <div class="artista-thumbnail">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
              </a>
            <?php endif; ?>
          </div>
          
          <div class="artista-content">
            <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            <div class="entry-summary">
              <?php the_excerpt(); ?>
            </div>
            <a href="<?php the_permalink(); ?>" class="read-more" title="<?php _e('Leggi di più su', 'portofranco'); ?> <?php the_title_attribute(); ?>">
              <?php _e('Leggi di più', 'portofranco'); ?>
            </a>
          </div>
        </article>
      <?php endwhile; ?>
    </div>
    
    <?php the_posts_navigation(); ?>
    
  <?php else : ?>
    <p><?php _e('Nessun artista trovato.', 'portofranco'); ?></p>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 