<?php
// Template archivio artisti
get_header();

// Query personalizzata per ottenere tutti gli artisti con ordine personalizzato
$query_artisti = array(
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
);
$description_artisti = portofranco_get_archive_description('artisti');
$query_special_proj = array(
    'post_type' => 'special-projects',
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
);

// Aggiungi filtro per lingua se Polylang è attivo
if (function_exists('pll_current_language')) {
    $current_lang = pll_current_language();
    if ($current_lang) {
        $query_artisti['lang'] = $current_lang;
        $query_special_proj['lang'] = $current_lang;
    }
}

// Fallback: rileva la lingua dall'URL se Polylang non la rileva
if (empty($query_args['lang']) && preg_match('/^\/(en)\//', $_SERVER['REQUEST_URI'], $matches)) {
    $query_artisti['lang'] = $matches[1];
    $query_special_proj['lang'] = $matches[1];
}

$artisti_query = new WP_Query($query_artisti);
$special_proj_query = new WP_Query($query_special_proj);
?>
<!-- Template: archive-artisti.php -->
<main id="main" tabindex="-1" role="main">
  
  
    <div class="artisti-grid">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Artisti', 'portofranco'); ?></h1>

        <div class="side-content">
          
          <ul id="side-archive-list" class="side-content-inner" data-post-type="artisti">
            
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-artisti"><?php _e('Artisti', 'portofranco'); ?></span>
              <ul id="item-artisti" class="accordion-list accordion-list">
                <?php if ( $artisti_query->have_posts() ) : ?>
                  <?php while ( $artisti_query->have_posts() ) : $artisti_query->the_post(); ?>
                    <li class="side-archive-item accordion-item"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" data-artist-id="<?php the_ID(); ?>" data-artist-slug="<?php echo portofranco_get_post_slug(); ?>" ><?php the_title(); ?></a></li>
                  <?php endwhile; ?>
                <?php else : ?>
                   <li class="side-archive-item accordion-item"><?php _e('Nessun artista trovato.', 'portofranco'); ?></li>
                <?php endif; ?>

              </ul>
            </li>
            

            
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-special_proj">Special Projects</span>
              <ul id="item-special_proj" class="accordion-list accordion-list">
                <?php if ( $special_proj_query->have_posts() ) : ?>
                  <?php while ( $special_proj_query->have_posts() ) : $special_proj_query->the_post(); ?>
                    <li class="<?php echo (portofranco_get_post_slug() == 'alice-ronchi' || portofranco_get_post_slug() == 'alice-ronchi-2') ? 'outsider-artist ' : ''; ?>side-archive-item accordion-item"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" data-artist-id="<?php the_ID(); ?>" data-artist-slug="<?php echo portofranco_get_post_slug(); ?>" ><?php the_title(); ?></a></li>
                  <?php endwhile; ?>
                <?php else : ?>
                  <li class="side-archive-item accordion-item"><?php _e('Nessuno special project trovato.', 'portofranco'); ?></li>
                <?php endif; ?>

              </ul>
            </li>
          </ul>
        </div>

        <div id="main-textarea" class="entry-content mid-text">
          <?php
          if ($description_artisti) {
              echo wpautop($description_artisti);
          }
          ?>
        </div>

      </article>

    </div>
    
    <?php 
    // Reset post data
    wp_reset_postdata();
    ?>
</main>
<?php get_footer(); ?> 