<?php
// Riga 1: Template pagina
get_header();
?>
<main id="main" tabindex="-1" role="main">
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>


    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
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

      <div class="entry-content mid-text">
        <?php the_content(); ?>
        
        <?php
        if (is_page('about') || is_page('about-eng')) {
          $lang = is_page('about') ? 'ita' : 'eng';
          include(get_template_directory() . '/about-end-content.php');
        } 
        ?>
      </div>

    </article>
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?> 