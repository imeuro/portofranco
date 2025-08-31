<?php
// Template archivio artisti
get_header();

// Query personalizzata per ottenere tutti gli artisti con ordine personalizzato
$artisti_query = new WP_Query(array(
    'post_type' => 'artisti',
    'posts_per_page' => -1, // Mostra tutti i post
    'post_status' => 'publish',
    'meta_key' => '_custom_order',
    'orderby' => array(
        'meta_value_num' => 'ASC',
        'title' => 'ASC'
    ),
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => '_custom_order',
            'compare' => 'EXISTS'
        ),
        array(
            'key' => '_custom_order',
            'compare' => 'NOT EXISTS'
        )
    )
));
?>
<!-- Template: archive-artisti.php -->
<main id="main" tabindex="-1" role="main">
  
  <?php if ( $artisti_query->have_posts() ) : ?>
    <div class="artisti-grid">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Artisti', 'portofranco'); ?></h1>

        <div class="side-content">
          <ul id="side-archive-list" class="side-content-inner" data-post-type="artisti">
            <?php while ( $artisti_query->have_posts() ) : $artisti_query->the_post(); ?>
              <li class="side-archive-item"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" data-artist-id="<?php the_ID(); ?>" data-artist-slug="<?php echo portofranco_get_post_slug(); ?>" ><?php the_title(); ?></a></li>
            <?php endwhile; ?>
          </ul>
        </div>

        <div id="main-textarea" class="entry-content big-text">
          <?php
          $description = portofranco_get_archive_description('artisti');
          if ($description) {
              echo wpautop($description);
          }
          ?>
        </div>

      </article>

    </div>
    
    <?php 
    // Reset post data
    wp_reset_postdata();
    ?>
    
  <?php else : ?>
    <div class="artisti-grid">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Artisti', 'portofranco'); ?></h1>
        <div class="entry-content big-text">
          <p><?php _e('Nessun artista trovato.', 'portofranco'); ?></p>
        </div>
      </article>
    </div>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 