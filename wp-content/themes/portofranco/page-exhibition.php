<?php
/**
 * Template Name: Exhibition
 * Description: Template pagina per le pagine exhibition.
 */
get_header();

// Definizione e ordine dei floor con i loro nomi
$floors = array(
    'anni70-0' => __('Anni 70 - Piano terra', 'portofranco'),
    'anni70-1' => __('Anni 70 - Piano 1', 'portofranco'),
    'anni70-2' => __('Anni 70 - Piano 2', 'portofranco'),
    'anni70-3' => __('Anni 70 - Piano 3', 'portofranco'),
    'settecento-3' => __('Settecento - Piano 3', 'portofranco'),
    'settecento-2' => __('Settecento - Piano 2', 'portofranco'),
    'settecento-0' => __('Settecento - Piano rialzato', 'portofranco'),
    'settecento-1-SE' => __('Settecento - Piano 1 - SE', 'portofranco'),
    'settecento-1-SO' => __('Settecento - Piano 1 - SO', 'portofranco'),
    'cortile' => __('Cortile', 'portofranco'),
    'museo' => __('Museo', 'portofranco'),
);

// Funzione per convertire markdown semplice in HTML
function convertMarkdownToHtml($text) {
    // Prima converti **bold** in <strong> (usa non-greedy per evitare problemi)
    $text = preg_replace('/\*\*([^*]+?)\*\*/', '<strong>$1</strong>', $text);
    // Poi converti *italic* in <em> (solo se non è già dentro **)
    // Usa lookbehind e lookahead negativi per evitare di matchare dentro i bold
    $text = preg_replace('/(?<!\*)\*([^*\n]+?)\*(?!\*)/', '<em>$1</em>', $text);
    // Converti <br> in <br /> per validità HTML
    $text = str_replace('<br>', '<br />', $text);
    return $text;
}

// Rileva la lingua corrente
$current_lang = 'it'; // Default italiano
if (function_exists('portofranco_get_current_language')) {
    $current_lang = portofranco_get_current_language();
} elseif (function_exists('pll_current_language')) {
    $current_lang = pll_current_language();
} elseif (strpos($_SERVER['REQUEST_URI'], '/en/') !== false) {
    $current_lang = 'en';
}

// Carica i testi descrittivi dal file JSON appropriato in base alla lingua
$floor_descriptions = array();
$json_filename = ($current_lang === 'en') ? 'floor-descriptions-en.json' : 'floor-descriptions.json';
$json_file_path = get_template_directory() . '/data/' . $json_filename;

if (file_exists($json_file_path)) {
    $json_content = file_get_contents($json_file_path);
    $floor_descriptions = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Se c'è un errore nel parsing JSON, usa array vuoto
        $floor_descriptions = array();
        error_log('Errore nel parsing del file JSON ' . $json_filename . ': ' . json_last_error_msg());
    }
} else {
    error_log('File ' . $json_filename . ' non trovato in: ' . $json_file_path);
}

// Fallback: se il file non esiste o è vuoto, usa array vuoto
if (!is_array($floor_descriptions)) {
    $floor_descriptions = array();
}
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

        <div class="entry-content mid-text">
            <?php the_content(); ?>
            
            <div class="exhibition-map-wrapper">
                <div class="exhibition-map-container" role="region" aria-label="<?php _e('Mappa interattiva della mostra', 'portofranco'); ?>">
                
                <!-- Mappe dei vari piani disponibili -->
                <?php
                $map_base_url = PF_PLUGIN_URL . 'modules/exhibition/assets/exhibition-maps/';
                $map_base_path = PF_PLUGIN_DIR . 'modules/exhibition/assets/exhibition-maps/';
                                
                $first_floor = true;
                foreach ($floors as $floor_key => $floor_name):
                    $map_url = $map_base_url . $floor_key . '.svg';
                    $map_path = $map_base_path . $floor_key . '.svg';

                    // Check if map exists
                    if (!file_exists($map_path)) {
                        $map_url = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="1200" height="900"%3E%3Crect fill="%23f5f5f5" width="1200" height="900"/%3E%3Ctext x="50%25" y="50%25" font-family="Arial" font-size="24" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EMappa ' . esc_attr($floor_name) . '%3C/text%3E%3C/svg%3E';
                    }
                ?>
                
                <div class="floor-map" 
                    data-floor="<?php echo esc_attr($floor_key); ?>" 
                    <?php echo $first_floor ? 'data-active="true"' : 'data-active="false"'; ?>
                    aria-hidden="<?php echo $first_floor ? 'false' : 'true'; ?>">
                    
                    <img src="<?php echo esc_url($map_url); ?>" 
                        alt="<?php echo esc_attr(sprintf(__('Mappa %s', 'portofranco'), $floor_name)); ?>"
                        loading="<?php echo $first_floor ? 'eager' : 'lazy'; ?>">
                    
                    <div class="artwork-markers" data-floor="<?php echo esc_attr($floor_key); ?>">
                        <!-- I marker verranno aggiunti dinamicamente via JavaScript -->
                    </div>
                </div>
                
                <?php 
                    $first_floor = false;
                endforeach; ?>
                
                </div>
                
                <!-- Descrizione del piano corrente -->
                <div class="floor-map-description">
                    <?php 
                    $first_floor_key = array_key_first($floors);
                    $first_description = isset($floor_descriptions[$first_floor_key]) ? $floor_descriptions[$first_floor_key] : null;
                    if ($first_description): 
                        $title_html = convertMarkdownToHtml($first_description['title']);
                        $content_html = convertMarkdownToHtml($first_description['content']);
                    ?>
                        <h3><?php echo wp_kses_post($title_html); ?></h3>
                        <div class="floor-map-description-content"><?php echo wp_kses_post($content_html); ?></div>
                    <?php endif; ?>
                </div>
            </div>
          
        </div>

    </article>
  <?php endwhile; endif; ?>
</main>

<script type="text/javascript">
// Passa i testi descrittivi al JavaScript (convertiti in HTML)
window.portofrancoFloorDescriptions = <?php 
$descriptions_html = array();
foreach ($floor_descriptions as $key => $desc) {
    $descriptions_html[$key] = array(
        'title' => convertMarkdownToHtml($desc['title']),
        'content' => convertMarkdownToHtml($desc['content'])
    );
}
echo json_encode($descriptions_html, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); 
?>;
</script>

<!-- Lista dei piani e relativi artisti -->
<div id="exhibition-list" class="exhibition-list">
    <ul>
        <?php
        $first_floor_list = true;
        foreach ($floors as $floor_key => $floor_name):
            $floor_id = 'floor-content-' . esc_attr($floor_key);
        ?>
        <li class="exhibition-floor" data-floor="<?php echo esc_attr($floor_key); ?>" data-expanded="<?php echo $first_floor_list ? 'true' : 'false'; ?>">
            <button class="floor-toggle" type="button" aria-expanded="<?php echo $first_floor_list ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($floor_id); ?>">
                <h3><?php echo esc_html($floor_name); ?></h3>
            </button>
            <div class="floor-content" id="<?php echo esc_attr($floor_id); ?>" aria-hidden="<?php echo $first_floor_list ? 'false' : 'true'; ?>"></div>
        </li>
        <?php 
            $first_floor_list = false;
        endforeach; ?>
    </ul>
</div>


<!-- Modal per i dettagli dell'opera -->
<div class="artwork-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true">
  <div class="modal-overlay" aria-hidden="true"></div>
  <div class="modal-content" role="document" tabindex="0">
    
    <div class="modal-body">
      <div class="modal-artwork-image">
        <img src="" alt="" class="modal-artwork-image-img" loading="lazy">
      </div>
      <div class="modal-artwork-content">
        <h3 class="modal-artist-name small-label">
            <a href="#" class="modal-artist-link"></a>
        </h3>
        <h2 id="modal-title" class="modal-artwork-title"></h2>
        <div class="modal-artwork-description"></div>
      </div>
    </div>

    <button class="modal-close" aria-label="<?php _e('Chiudi', 'portofranco'); ?>">
      <span aria-hidden="true"><?php _e('Chiudi', 'portofranco'); ?></span>
    </button>

  </div>
</div>

<?php get_footer(); ?>

