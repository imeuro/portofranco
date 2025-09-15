<?php
// Template archivio agenda
get_header();

// Rileva lingua corrente (Polylang/WPML + fallback URL)
$portofranco_current_lang = function_exists('portofranco_get_current_language')
    ? portofranco_get_current_language()
    : (function_exists('pll_current_language') ? pll_current_language() : (strpos($_SERVER['REQUEST_URI'], '/en/') !== false ? 'en' : 'it'));

/**
 * Funzione helper per recuperare gli anni unici dal custom field inizio_evento_anno
 */
function portofranco_get_agenda_years($lang = null) {
    global $wpdb;
    
    // Se Polylang è attivo e ho una lingua valida, filtro tramite tassonomia "language"
    if (function_exists('pll_current_language') && !empty($lang)) {
        $query = $wpdb->prepare(
            "SELECT DISTINCT pm.meta_value as year
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
             INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
             INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'language'
             INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug = %s
             WHERE pm.meta_key = 'inizio_evento_anno'
             AND p.post_type = %s
             AND p.post_status = 'publish'
             AND pm.meta_value IS NOT NULL
             AND pm.meta_value != ''
             ORDER BY year ASC",
            $lang,
            'agenda'
        );
        $years = $wpdb->get_col($query);
    } else {
        $years = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_value as year 
             FROM {$wpdb->postmeta} pm 
             INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
             WHERE pm.meta_key = 'inizio_evento_anno' 
             AND p.post_type = %s 
             AND p.post_status = 'publish' 
             AND pm.meta_value IS NOT NULL 
             AND pm.meta_value != '' 
             ORDER BY year ASC",
            'agenda'
        ));
    }
    
    return $years;
}

/**
 * Funzione helper per verificare se esistono post per un anno/mese specifico
 */
function portofranco_has_agenda_posts_for_month($year, $month, $lang = null) {
    global $wpdb;
    
    if (function_exists('pll_current_language') && !empty($lang)) {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM {$wpdb->postmeta} pm_anno
             INNER JOIN {$wpdb->postmeta} pm_mese ON pm_anno.post_id = pm_mese.post_id
             INNER JOIN {$wpdb->posts} p ON pm_anno.post_id = p.ID
             INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
             INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'language'
             INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id AND t.slug = %s
             WHERE pm_anno.meta_key = 'inizio_evento_anno'
             AND pm_mese.meta_key = 'inizio_evento_mese'
             AND p.post_type = %s
             AND p.post_status = 'publish'
             AND pm_anno.meta_value = %d
             AND pm_mese.meta_value = %d",
            $lang,
            'agenda',
            $year,
            $month
        ));
    } else {
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} pm_anno 
             INNER JOIN {$wpdb->postmeta} pm_mese ON pm_anno.post_id = pm_mese.post_id 
             INNER JOIN {$wpdb->posts} p ON pm_anno.post_id = p.ID 
             WHERE pm_anno.meta_key = 'inizio_evento_anno' 
             AND pm_mese.meta_key = 'inizio_evento_mese' 
             AND p.post_type = %s 
             AND p.post_status = 'publish' 
             AND pm_anno.meta_value = %d 
             AND pm_mese.meta_value = %d",
            'agenda',
            $year,
            $month
        ));
    }
    
    return $count > 0;
}

/**
 * Funzione helper per generare URL dell'archivio per mese
 */
function portofranco_get_agenda_month_archive_url($year, $month) {
    return add_query_arg(array(
        'anno' => $year,
        'mese' => $month
    ), get_post_type_archive_link('agenda'));
}

// Recupera gli anni disponibili
$years = portofranco_get_agenda_years($portofranco_current_lang);

// Array mesi localizzato (IT/EN)
if ($portofranco_current_lang === 'en') {
    $months = array(
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    );
} else {
    $months = array(
        1 => 'Gennaio',
        2 => 'Febbraio',
        3 => 'Marzo',
        4 => 'Aprile',
        5 => 'Maggio',
        6 => 'Giugno',
        7 => 'Luglio',
        8 => 'Agosto',
        9 => 'Settembre',
        10 => 'Ottobre',
        11 => 'Novembre',
        12 => 'Dicembre'
    );
}
?>
<!-- Template: archive-agenda.php -->
<main id="main" tabindex="-1" role="main">
  
  <?php if ( !empty($years) ) : ?>
    <div class="agenda-grid">

      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>

        <div class="side-content">
          <ul id="side-archive-list" class="side-content-inner" data-post-type="agenda">
            <?php foreach ( $years as $year ) : ?>              
              <li class="accordion-item">
                <span class="accordion-item-label" tabindex="0" role="button" aria-expanded="false" aria-controls="item-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></span>
                <ul id="item-<?php echo esc_attr($year); ?>" class="accordion-list accordion-list">
                  <?php foreach ( $months as $month_num => $month_name ) : ?>
                    <li class="side-archive-item accordion-item">
                      <?php if ( portofranco_has_agenda_posts_for_month($year, $month_num, $portofranco_current_lang) ) : ?>
                        <a href="<?php echo esc_url(portofranco_get_agenda_month_archive_url($year, $month_num)); ?>" 
                           rel="bookmark" 
                           title="<?php echo esc_attr(sprintf(__('Eventi di %s %d', 'portofranco'), $month_name, $year)); ?>"
                           data-year="<?php echo esc_attr($year); ?>"
                           data-month="<?php echo esc_attr($month_num); ?>">
                          <?php echo esc_html($month_name); ?>
                        </a>
                      <?php else : ?>
                        <span class="month-no-events"><?php echo esc_html($month_name); ?></span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div id="main-textarea" class="entry-content mid-text">
          <?php
          $description = portofranco_get_archive_description('agenda');
          if ($description) {
              echo wpautop($description);
          }
          ?>
        </div>

      </article>

    </div>
    
  <?php else : ?>
    <div class="agenda-grid">
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> tabindex="0">
        <h1 class="page-title small-label"><?php _e('Agenda', 'portofranco'); ?></h1>
        <div class="entry-content mid-text">
          <p><?php _e('Nessun evento trovato.', 'portofranco'); ?></p>
        </div>
      </article>
    </div>
  <?php endif; ?>
</main>

<?php get_footer(); ?> 