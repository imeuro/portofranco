<?php
// Template homepage statica personalizzabile
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
      <?php
      // Ottieni i custom fields immagine
      $immagine_1_id = get_field('immagine_sx');
      $immagine_2_id = get_field('immagine_dx');
      
      // Mostra le immagini se esistono
      if ($immagine_1_id || $immagine_2_id) : ?>
        <div class="homepage-images" style="display: flex; width: 100vw; height: 100vh; overflow: hidden;">
          <?php if ($immagine_1_id) : 
            $immagine_1_full = wp_get_attachment_image_src($immagine_1_id, 'full');
          ?>
            <div class="homepage-image" style="width: 50vw; height: 100vh; overflow: hidden;">
              <img src="<?php echo esc_url($immagine_1_full[0]); ?>" 
                   alt="<?php echo esc_attr(get_the_title()); ?> - Immagine 1"
                   style="width: 100%; height: 100%; object-fit: cover;"
                   loading="eager">
            </div>
          <?php endif; ?>
          
          <?php if ($immagine_2_id) : 
            $immagine_2_full = wp_get_attachment_image_src($immagine_2_id, 'full');
          ?>
            <div class="homepage-image" style="width: 50vw; height: 100vh; overflow: hidden;">
              <img src="<?php echo esc_url($immagine_2_full[0]); ?>" 
                   alt="<?php echo esc_attr(get_the_title()); ?> - Immagine 2"
                   style="width: 100%; height: 100%; object-fit: cover;"
                   loading="eager">
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </div>
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?> 