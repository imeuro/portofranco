<?php
// Template archivio agenda
get_header();

/**
 * Funzione helper per recuperare gli anni unici dal custom field inizio_evento_anno
 */
function portofranco_get_agenda_years() {
    global $wpdb;
    
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
    
    return $years;
}

/**
 * Funzione helper per verificare se esistono post per un anno/mese specifico
 */
function portofranco_has_agenda_posts_for_month($year, $month) {
    global $wpdb;
    
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
$years = portofranco_get_agenda_years();

// Debug temporaneo - rimuovere dopo il test
if (empty($years)) {
    // Verifica se ci sono post agenda
    $test_query = new WP_Query(array(
        'post_type' => 'agenda',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ));
    
    if ($test_query->have_posts()) {
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">';
        echo '<h3>Debug: Post Agenda trovati</h3>';
        echo '<p>Numero post: ' . $test_query->found_posts . '</p>';
        
        while ($test_query->have_posts()) {
            $test_query->the_post();
            $anno = get_field('inizio_evento_anno');
            $mese = get_field('inizio_evento_mese');
            echo '<p>Post ID: ' . get_the_ID() . ' - Titolo: ' . get_the_title() . ' - Anno: ' . ($anno ? $anno : 'NULL') . ' - Mese: ' . ($mese ? $mese : 'NULL') . '</p>';
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">';
        echo '<h3>Debug: Nessun post agenda trovato</h3>';
        echo '</div>';
    }
}

// Array dei mesi in italiano
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
              <!--li class="side-archive-year side-archive-year-mobile">
                <span class="year-label" tabindex="0" role="button" aria-expanded="<?php echo ($year === reset($years)) ? 'true' : 'false'; ?>" aria-controls="months-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></span>
                <ul id="months-<?php echo esc_attr($year); ?>" class="months-list <?php if ($year == '2025') { echo 'expanded'; } ?>">
                  <?php foreach ( $months as $month_num => $month_name ) : ?>
                    <li class="side-archive-month side-archive-item">
                      <?php if ( portofranco_has_agenda_posts_for_month($year, $month_num) ) : ?>
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
              </li--> 


              
              <li class="side-archive-year side-archive-year-desktop">
                <span class="year-label" tabindex="0" role="button" aria-expanded="<?php echo ($year === reset($years)) ? 'true' : 'false'; ?>" aria-controls="months-<?php echo esc_attr($year); ?>"><?php echo esc_html($year); ?></span>
                <ul id="months-<?php echo esc_attr($year); ?>" class="months-list" style="display: <?php echo ($year === reset($years)) ? 'block' : 'none'; ?>;">
                  <?php foreach ( $months as $month_num => $month_name ) : ?>
                    <li class="side-archive-month side-archive-item">
                      <?php if ( portofranco_has_agenda_posts_for_month($year, $month_num) ) : ?>
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

<script>
// Gestione toggle anni/mesi nell'archivio agenda
document.addEventListener('DOMContentLoaded', function() {
    const yearLabels = document.querySelectorAll('.side-archive-year .year-label');
    
    yearLabels.forEach(function(yearLabel) {
        yearLabel.addEventListener('click', function() {
            const yearItem = this.closest('.side-archive-year');
            const monthsList = yearItem.querySelector('.months-list');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Chiudi tutti gli anni prima
            const allYearLabels = document.querySelectorAll('.side-archive-year .year-label');
            const allMonthsLists = document.querySelectorAll('.side-archive-year .months-list');
            
            allYearLabels.forEach(function(label) {
                label.setAttribute('aria-expanded', 'false');
            });
            
            allMonthsLists.forEach(function(list) {
                list.style.display = 'none';
            });
            
            // Se l'anno cliccato era chiuso, aprilo
            if (!isExpanded) {
                monthsList.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
            }
            // Se l'anno cliccato era aperto, rimane chiuso (comportamento toggle)
        });
        
        // Gestione tastiera per accessibilità
        yearLabel.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<?php get_footer(); ?> 