<?php
// Template pagina 'EXHIBITION'
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post();  ?>


    <article id="post-<?php the_ID(); ?>" <?php post_class('exhibition-page'); ?> tabindex="0">

        <h1 class="page-title small-label"><?php the_title(); ?></h1>
     
        <?php
        $show_side_content = get_field('show_side_content');
        if ($show_side_content) { ?>
            <div class="side-content">
            <div class="side-content-inner">
                <?php the_field('side_content'); ?>
            </div>
            </div>
        <?php } ?>

        <div class="entry-content">
            <?php the_content(); ?>

            <header class="exhibition-header">
            
            <div class="floor-indicator" aria-live="polite" aria-atomic="true">
                <span class="current-floor-label"><?php _e('floor', 'portofranco'); ?></span>
                <span class="current-floor-number"><?php _e('number', 'portofranco'); ?></span>
            </div>
            </header>
            
            <div class="exhibition-map-wrapper">
                <div class="exhibition-map-container" role="region" aria-label="<?php _e('Mappa interattiva della mostra', 'portofranco'); ?>">
                
                <!-- Mappe dei 4 piani -->
                <?php
                $upload_dir = wp_upload_dir();
                $map_base_url = $upload_dir['baseurl'] . '/exhibition-maps/';
                
                for ($floor = 0; $floor <= 3; $floor++):
                    $map_url = $map_base_url . 'piano-' . $floor . '.jpg';
                    $map_path = $upload_dir['basedir'] . '/exhibition-maps/piano-' . $floor . '.jpg';
                    
                    // Check if map exists
                    if (!file_exists($map_path)) {
                    $floor_name = ($floor == 0) ? __('Piano terra', 'portofranco') : sprintf(__('Piano %s', 'portofranco'), $floor);
                    $map_url = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="1200" height="900"%3E%3Crect fill="%23f5f5f5" width="1200" height="900"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="24" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EMappa ' . esc_attr($floor_name) . '%3C/text%3E%3C/svg%3E';
                    }
                ?>
                
                <div class="floor-map" 
                    data-floor="<?php echo $floor; ?>" 
                    <?php echo $floor === 0 ? 'data-active="true"' : 'data-active="false"'; ?>
                    aria-hidden="<?php echo $floor === 0 ? 'false' : 'true'; ?>">
                    
                    <img src="<?php echo esc_url($map_url); ?>" 
                        alt="<?php echo ($floor == 0) ? __('Mappa Piano terra', 'portofranco') : sprintf(__('Mappa Piano %s', 'portofranco'), $floor); ?>"
                        loading="<?php echo $floor === 0 ? 'eager' : 'lazy'; ?>">
                    
                    <div class="artwork-markers" data-floor="<?php echo $floor; ?>">
                    <!-- I marker verranno aggiunti dinamicamente via JavaScript -->
                    </div>
                </div>
                
                <?php endfor; ?>
                
                </div>
            </div>
          
        </div>

    </article>
  <?php endwhile; endif; ?>
</main>

<!-- Lista dei piani e relativi artisti -->
<div id="exhibition-list" class="exhibition-list">
    <ul>
        <li class="exhibition-floor" data-floor="0" data-expanded="true">
            <button class="floor-toggle" type="button" aria-expanded="true" aria-controls="floor-content-0">
                <h3><?php _e('Piano terra', 'portofranco'); ?></h3>
            </button>
            <div class="floor-content" id="floor-content-0" aria-hidden="false"></div>
        </li>
        <li class="exhibition-floor" data-floor="1" data-expanded="false">
            <button class="floor-toggle" type="button" aria-expanded="false" aria-controls="floor-content-1">
                <h3><?php _e('Piano 1', 'portofranco'); ?></h3>
            </button>
            <div class="floor-content" id="floor-content-1" aria-hidden="true"></div>
        </li>
        <li class="exhibition-floor" data-floor="2" data-expanded="false">
            <button class="floor-toggle" type="button" aria-expanded="false" aria-controls="floor-content-2">
                <h3><?php _e('Piano 2', 'portofranco'); ?></h3>
            </button>
            <div class="floor-content" id="floor-content-2" aria-hidden="true"></div>
        </li>
        <li class="exhibition-floor" data-floor="3" data-expanded="false">
            <button class="floor-toggle" type="button" aria-expanded="false" aria-controls="floor-content-3">
                <h3><?php _e('Piano 3', 'portofranco'); ?></h3>
            </button>
            <div class="floor-content" id="floor-content-3" aria-hidden="true"></div>
        </li>
    </ul>
</div>


<!-- Modal per i dettagli dell'opera -->
<div class="artwork-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
  <div class="modal-overlay" aria-hidden="true"></div>
  <div class="modal-content" role="document" tabindex="0">
    <button class="modal-close" aria-label="<?php _e('Chiudi', 'portofranco'); ?>">
      <span aria-hidden="true">×</span>
    </button>
    
    <div class="modal-body">
      <h2 id="modal-title" class="modal-artwork-title"></h2>
      <h3 class="modal-artist-name">
        <a href="#" class="modal-artist-link"></a>
      </h3>
      <div class="modal-artwork-description"></div>
    </div>
  </div>
</div>

<?php get_footer(); ?>

