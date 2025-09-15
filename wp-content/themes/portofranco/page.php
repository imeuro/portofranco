<?php
// Riga 1: Template pagina
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); 
  $post_class = is_page('about') || is_page('about-eng') ? 'about-page' : '';
  ?>


    <article id="post-<?php the_ID(); ?>" <?php post_class($post_class); ?> tabindex="0">
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

      <?php if (is_page('about') || is_page('about-eng')) { ?>
        <div class="side-content">
          <ul id="side-archive-list" class="side-content-inner" data-post-type="about">
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-team">Team</span>
            </li>
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-partners">Partners</span>
            </li>
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-info">Info</span>
            </li>
            <li class="accordion-item">
              <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-newsletter">Newsletter</span>
            </li>

          </ul>
        </div>

        <div class="entry-content big-text" id="about-page">
          <?php 
          // Se siamo nella pagina about-eng, forza il caricamento delle traduzioni inglesi
          if (is_page('about-eng')) {
            // Forza il caricamento del textdomain inglese
            unload_textdomain('portofranco');
            load_textdomain('portofranco', get_template_directory() . '/languages/portofranco-en_GB.mo');
          }
          echo '<div class="about-page-content">';
          the_content();
          echo '</div>';
          $lang = is_page('about') ? 'ita' : 'eng';
          include(get_template_directory() . '/about-end-content.php');
          ?>
        </div>
      <?php } else { ?>
        <div class="entry-content mid-text">
          <?php the_content(); ?>
        </div>
      <?php } ?>
    </article>
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?> 