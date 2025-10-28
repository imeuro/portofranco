<?php
/**
 * Template Name: Exhibition
 * Template per la pagina Exhibition con mappe interattive
 */
get_header();
?>

<!-- Riga 8: Template: page-exhibition.php -->
<main id="main" class="exhibition-page" tabindex="-1" role="main">
  
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    
    <div class="exhibition-container">
      
      <header class="exhibition-header">
        <h1><?php the_title(); ?></h1>
        
        <?php if (get_the_content()): ?>
          <div class="exhibition-intro">
            <?php the_content(); ?>
          </div>
        <?php endif; ?>
        
        <div class="floor-indicator" aria-live="polite" aria-atomic="true">
          <span class="current-floor-label"><?php _e('Piano', 'portofranco'); ?></span>
          <span class="current-floor-number">0</span>
          <span class="floor-separator"><?php _e('di', 'portofranco'); ?></span>
          <span class="total-floors">3</span>
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
              $map_url = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="1200" height="900"%3E%3Crect fill="%23f5f5f5" width="1200" height="900"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="24" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EMappa Piano ' . $floor . '%3C/text%3E%3C/svg%3E';
            }
          ?>
          
          <div class="floor-map" 
               data-floor="<?php echo $floor; ?>" 
               <?php echo $floor === 0 ? 'data-active="true"' : 'data-active="false"'; ?>
               aria-hidden="<?php echo $floor === 0 ? 'false' : 'true'; ?>">
            
            <img src="<?php echo esc_url($map_url); ?>" 
                 alt="<?php echo sprintf(__('Mappa Piano %s', 'portofranco'), $floor); ?>"
                 loading="<?php echo $floor === 0 ? 'eager' : 'lazy'; ?>">
            
            <div class="artwork-markers" data-floor="<?php echo $floor; ?>">
              <!-- I marker verranno aggiunti dinamicamente via JavaScript -->
            </div>
          </div>
          
          <?php endfor; ?>
          
        </div>
      </div>
      
      <nav class="exhibition-navigation" aria-label="<?php _e('Navigazione tra i piani', 'portofranco'); ?>">
        <button class="nav-btn nav-prev" 
                aria-label="<?php _e('Piano precedente', 'portofranco'); ?>"
                disabled>
          <span aria-hidden="true">←</span>
          <span class="nav-label"><?php _e('Precedente', 'portofranco'); ?></span>
        </button>
        
        <div class="floor-dots" role="tablist" aria-label="<?php _e('Selezione piano', 'portofranco'); ?>">
          <?php for ($i = 0; $i <= 3; $i++): ?>
            <button class="floor-dot" 
                    data-floor="<?php echo $i; ?>"
                    role="tab"
                    aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                    aria-label="<?php echo sprintf(__('Piano %s', 'portofranco'), $i); ?>"
                    <?php echo $i === 0 ? 'aria-current="true"' : ''; ?>>
              <span class="sr-only"><?php echo sprintf(__('Piano %s', 'portofranco'), $i); ?></span>
            </button>
          <?php endfor; ?>
        </div>
        
        <button class="nav-btn nav-next" 
                aria-label="<?php _e('Piano successivo', 'portofranco'); ?>">
          <span class="nav-label"><?php _e('Successivo', 'portofranco'); ?></span>
          <span aria-hidden="true">→</span>
        </button>
      </nav>
      
    </div>
    
  <?php endwhile; endif; ?>
  
</main>

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

