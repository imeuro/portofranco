<?php
// Template archivio agenda
get_header();

// Query personalizzata per ottenere tutti gli agenda ordinati alfabeticamente
$agenda_query = new WP_Query(array(
    'post_type' => 'agenda',
    'posts_per_page' => -1, // Mostra tutti i post
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC' // Ordine alfabetico A-Z
));
?>
<!-- Template: archive-agenda.php -->
<main id="main" tabindex="-1" role="main">
  
  <?php if ( $agenda_query->have_posts() ) : ?>
    <div class="agenda-grid">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>

        <div class="side-content">
          <ul id="side-archive-list" class="side-content-inner" data-post-type="agenda">
            <?php while ( $agenda_query->have_posts() ) : $agenda_query->the_post(); ?>
              <li class="side-archive-item"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" data-agenda-id="<?php the_ID(); ?>" data-agenda-slug="<?php echo portofranco_get_post_slug(); ?>" ><?php the_title(); ?></a></li>
            <?php endwhile; ?>
          </ul>
        </div>

        <div id="main-textarea" class="entry-content big-text">
          <?php
          $description = portofranco_get_archive_description('agenda');
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
    <div class="agenda-grid">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>
        <div class="entry-content big-text">
          <p><?php _e('Nessun evento trovato.', 'portofranco'); ?></p>
        </div>
      </article>
    </div>
  <?php endif; ?>
</main>
<?php get_footer(); ?> 