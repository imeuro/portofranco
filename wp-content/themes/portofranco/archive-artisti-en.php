<?php
// Template archivio artisti in inglese

get_header();

// Query personalizzata per ottenere tutti gli artisti in inglese
$query_args = array(
    'post_type' => 'artisti',
    'posts_per_page' => -1,
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
    ),
    'lang' => 'en' // Forza la lingua inglese
);

$artisti_query = new WP_Query($query_args);
?>

<!-- Template: archive-artisti-en.php -->
<main id="main" tabindex="-1" role="main">
  
  <?php if ( $artisti_query->have_posts() ) : ?>
    <div class="artisti-grid">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label">Artists</h1>

        <div class="side-content">
          <ul id="side-archive-list" class="side-content-inner" data-post-type="artisti">
            <?php while ( $artisti_query->have_posts() ) : $artisti_query->the_post(); ?>
              <li class="side-archive-item"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" data-artist-id="<?php the_ID(); ?>" data-artist-slug="<?php echo portofranco_get_post_slug(); ?>" ><?php the_title(); ?></a></li>
            <?php endwhile; ?>
          </ul>
        </div>

        <div id="main-textarea" class="entry-content mid-text">
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
        <h1 class="page-title small-label">Artists</h1>
        <div class="entry-content mid-text">
          <p>No artists found.</p>
        </div>
      </article>
    </div>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
