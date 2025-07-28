<?php
// Template archivio eventi
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <header class="page-header">
    <h1><?php _e('Agenda Eventi', 'portofranco'); ?></h1>
    <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
  </header>
  
  <?php if ( have_posts() ) : ?>
    <div class="eventi-grid">
      <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('evento-card'); ?> tabindex="0">
          <div class="evento-thumbnail">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
              </a>
            <?php endif; ?>
          </div>
          
          <div class="evento-content">
            <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            <div class="evento-meta">
              <span class="evento-date"><?php the_time( get_option( 'date_format' ) ); ?></span>
            </div>
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
    <p><?php _e('Nessun evento trovato.', 'portofranco'); ?></p>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 