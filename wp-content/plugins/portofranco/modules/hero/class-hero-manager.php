<?php
/**
 * Hero Manager
 *
 * @package PF
 */

if (!defined('ABSPATH')) {
    exit;
}

class PF_Hero_Manager {
    public function __construct() {
        add_action('init', array($this, 'register_hero_slide_cpt'));
        add_shortcode('pf_hero_carousel', array($this, 'render_hero_carousel'));
        
        // Admin columns
        add_filter('manage_hero-slide_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_hero-slide_posts_custom_column', array($this, 'render_admin_columns'), 10, 2);
        add_filter('manage_edit-hero-slide_sortable_columns', array($this, 'make_columns_sortable'));
        
        // Meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        
        // Regenerate hero carousel images when a hero slide is updated
        add_action('save_post_hero-slide', array($this, 'regenerate_hero_image'), 10, 2);
        
        // Add admin page actions
        add_action('admin_notices', array($this, 'add_regenerate_button'));
    }

    public function register_hero_slide_cpt() {
        $labels = array(
            'name'               => _x('Hero Slides', 'post type general name', 'pf'),
            'singular_name'      => _x('Hero Slide', 'post type singular name', 'pf'),
            'menu_name'          => _x('Hero Slides', 'admin menu', 'pf'),
            'name_admin_bar'     => _x('Hero Slide', 'add new on admin bar', 'pf'),
            'add_new'            => _x('Aggiungi Nuova', 'hero slide', 'pf'),
            'add_new_item'       => __('Aggiungi Nuova Slide', 'pf'),
            'new_item'           => __('Nuova Slide', 'pf'),
            'edit_item'          => __('Modifica Slide', 'pf'),
            'view_item'          => __('Visualizza Slide', 'pf'),
            'all_items'          => __('Tutte le Slides', 'pf'),
            'search_items'       => __('Cerca Slides', 'pf'),
            'not_found'          => __('Nessuna slide trovata.', 'pf'),
            'not_found_in_trash' => __('Nessuna slide trovata nel cestino.', 'pf'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-images-alt2',
            'supports'           => array('title', 'thumbnail', 'page-attributes'),
            'hierarchical'       => false,
            'exclude_from_search'=> true,
            'publicly_queryable' => false,
            'show_in_nav_menus'  => false,
        );
        register_post_type('hero-slide', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'hero_slide_video',
            __('Video Hero', 'pf'),
            array($this, 'render_video_meta_box'),
            'hero-slide',
            'normal',
            'high'
        );
    }

    public function render_video_meta_box($post) {
        wp_nonce_field('hero_slide_video_nonce', 'hero_slide_video_nonce');
        
        $video_id = get_post_meta($post->ID, '_hero_slide_video_id', true);
        $video_url = get_post_meta($post->ID, '_hero_slide_video_url', true);
        $video_type = get_post_meta($post->ID, '_hero_slide_video_type', true);
        
        // Se abbiamo un video_id, ottieni l'URL
        if ($video_id) {
            $video_url = wp_get_attachment_url($video_id);
        }
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="hero_slide_video_id"><?php _e('Video MP4', 'pf'); ?></label>
                </th>
                <td>
                    <input type="hidden" 
                           id="hero_slide_video_id" 
                           name="hero_slide_video_id" 
                           value="<?php echo esc_attr($video_id); ?>" />
                    
                    <div id="hero_video_preview" style="margin-bottom: 10px;">
                        <?php if ($video_url): ?>
                            <video controls style="max-width: 100%; height: auto; max-height: 200px;">
                                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                <?php _e('Il tuo browser non supporta il tag video.', 'pf'); ?>
                            </video>
                            <p><strong><?php _e('Video selezionato:', 'pf'); ?></strong> <?php echo esc_html(basename($video_url)); ?></p>
                        <?php else: ?>
                            <p style="color: #666; font-style: italic;"><?php _e('Nessun video selezionato', 'pf'); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="select_video_btn" class="button">
                        <?php echo $video_url ? __('Cambia Video', 'pf') : __('Seleziona Video', 'pf'); ?>
                    </button>
                    
                    <?php if ($video_url): ?>
                        <button type="button" id="remove_video_btn" class="button button-link-delete" style="margin-left: 10px;">
                            <?php _e('Rimuovi Video', 'pf'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <p class="description">
                        <?php _e('Seleziona un video MP4 dalla libreria media. Il video sostituirà l\'immagine in evidenza.', 'pf'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="hero_slide_video_type"><?php _e('Tipo Media', 'pf'); ?></label>
                </th>
                <td>
                    <select id="hero_slide_video_type" name="hero_slide_video_type">
                        <option value="image" <?php selected($video_type, 'image'); ?>>
                            <?php _e('Immagine', 'pf'); ?>
                        </option>
                        <option value="video" <?php selected($video_type, 'video'); ?>>
                            <?php _e('Video', 'pf'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Scegli se utilizzare l\'immagine in evidenza o il video.', 'pf'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <script>
        jQuery(document).ready(function($) {
            var mediaUploader;
            
            $('#select_video_btn').click(function(e) {
                e.preventDefault();
                
                // Se l'uploader esiste già, riutilizzalo
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                // Crea l'uploader
                mediaUploader = wp.media({
                    title: '<?php _e('Seleziona Video MP4', 'pf'); ?>',
                    button: {
                        text: '<?php _e('Usa questo video', 'pf'); ?>'
                    },
                    multiple: false,
                    library: {
                        type: 'video'
                    }
                });
                
                // Quando viene selezionato un video
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    
                    // Verifica che sia un video MP4
                    if (attachment.subtype !== 'mp4') {
                        alert('<?php _e('Seleziona solo file MP4.', 'pf'); ?>');
                        return;
                    }
                    
                    // Aggiorna i campi
                    $('#hero_slide_video_id').val(attachment.id);
                    
                    // Aggiorna il preview
                    var preview = $('#hero_video_preview');
                    preview.html('<video controls style="max-width: 100%; height: auto; max-height: 200px;">' +
                                '<source src="' + attachment.url + '" type="video/mp4">' +
                                '<?php _e('Il tuo browser non supporta il tag video.', 'pf'); ?>' +
                                '</video>' +
                                '<p><strong><?php _e('Video selezionato:', 'pf'); ?></strong> ' + attachment.filename + '</p>');
                    
                    // Aggiorna il pulsante
                    $('#select_video_btn').text('<?php _e('Cambia Video', 'pf'); ?>');
                    
                    // Aggiungi pulsante rimuovi se non esiste
                    if ($('#remove_video_btn').length === 0) {
                        $('#select_video_btn').after('<button type="button" id="remove_video_btn" class="button button-link-delete" style="margin-left: 10px;"><?php _e('Rimuovi Video', 'pf'); ?></button>');
                    }
                });
                
                mediaUploader.open();
            });
            
            // Rimuovi video
            $(document).on('click', '#remove_video_btn', function(e) {
                e.preventDefault();
                
                $('#hero_slide_video_id').val('');
                $('#hero_video_preview').html('<p style="color: #666; font-style: italic;"><?php _e('Nessun video selezionato', 'pf'); ?></p>');
                $('#select_video_btn').text('<?php _e('Seleziona Video', 'pf'); ?>');
                $(this).remove();
            });
        });
        </script>
        <?php
    }

    public function save_meta_boxes($post_id) {
        // Verifica nonce
        if (!isset($_POST['hero_slide_video_nonce']) || 
            !wp_verify_nonce($_POST['hero_slide_video_nonce'], 'hero_slide_video_nonce')) {
            return;
        }

        // Verifica permessi
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Salva ID video
        if (isset($_POST['hero_slide_video_id'])) {
            $video_id = intval($_POST['hero_slide_video_id']);
            update_post_meta($post_id, '_hero_slide_video_id', $video_id);
            
            // Aggiorna anche l'URL per compatibilità
            if ($video_id) {
                $video_url = wp_get_attachment_url($video_id);
                update_post_meta($post_id, '_hero_slide_video_url', $video_url);
            } else {
                delete_post_meta($post_id, '_hero_slide_video_url');
            }
        }

        // Salva tipo media
        if (isset($_POST['hero_slide_video_type'])) {
            $video_type = sanitize_text_field($_POST['hero_slide_video_type']);
            update_post_meta($post_id, '_hero_slide_video_type', $video_type);
        }
    }

    public function add_admin_columns($columns) {
        $new_columns = array();
        
        // Aggiungi checkbox
        $new_columns['cb'] = $columns['cb'];
        
        // Aggiungi colonna thumbnail
        $new_columns['thumbnail'] = __('Media', 'pf');
        
        // Aggiungi titolo
        $new_columns['title'] = $columns['title'];
        
        // Aggiungi colonna ordine
        $new_columns['menu_order'] = __('Ordine', 'pf');
        
        // Aggiungi data
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    public function render_admin_columns($column, $post_id) {
        switch ($column) {
            case 'thumbnail':
                $video_type = get_post_meta($post_id, '_hero_slide_video_type', true);
                $video_id = get_post_meta($post_id, '_hero_slide_video_id', true);
                $video_url = '';
                
                if ($video_id) {
                    $video_url = wp_get_attachment_url($video_id);
                }
                
                if ($video_type === 'video' && $video_url) {
                    echo '<div style="display: flex; align-items: center; gap: 10px;">';
                    echo '<span style="color: #0073aa; font-weight: bold;">🎥 Video</span>';
                    echo '<span style="color: #666; font-size: 12px;">' . esc_html(basename($video_url)) . '</span>';
                    echo '</div>';
                } elseif (has_post_thumbnail($post_id)) {
                    $thumbnail_id = get_post_thumbnail_id($post_id);
                    $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
                    echo '<img src="' . esc_url($thumbnail_url) . '" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;" alt="Thumbnail">';
                } else {
                    echo '<span style="color: #999; font-style: italic;">Nessun media</span>';
                }
                break;
                
            case 'menu_order':
                $post = get_post($post_id);
                echo '<strong>' . esc_html($post->menu_order) . '</strong>';
                break;
        }
    }

    public function make_columns_sortable($columns) {
        $columns['menu_order'] = 'menu_order';
        return $columns;
    }

    public function render_hero_carousel($atts) {
        $args = array(
            'post_type'      => 'hero-slide',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        );
        $slides = get_posts($args);
        if (empty($slides)) return '';

        ob_start();
        ?>
        <div class="pf-hero-carousel">
            <?php foreach ($slides as $i => $slide) :
                $video_type = get_post_meta($slide->ID, '_hero_slide_video_type', true);
                $video_id = get_post_meta($slide->ID, '_hero_slide_video_id', true);
                $video_url = '';
                
                // Ottieni l'URL del video dall'ID
                if ($video_id) {
                    $video_url = wp_get_attachment_url($video_id);
                }
                
                $img_id = get_post_thumbnail_id($slide->ID);
                $img_url = '';
                
                if ($video_type === 'video' && $video_url) {
                    // Renderizza video
                    ?>
                    <div class="pf-hero-slide<?php echo $i === 0 ? ' active' : ''; ?> pf-hero-video-slide" 
                         data-title="<?php echo esc_attr($slide->post_title); ?>">
                        <video class="pf-hero-video" 
                               autoplay 
                               muted 
                               playsinline
                               poster="<?php echo $img_id ? wp_get_attachment_image_url($img_id, 'full') : ''; ?>">
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                        </video>
                    </div>
                    <?php
                } elseif ($img_id) {
                    // Renderizza immagine
                    $img_url = wp_get_attachment_image_url($img_id, 'pf25-hero-carousel');
                    
                    // Fallback to full size if custom size doesn't exist
                    if (!$img_url) {
                        $img_url = wp_get_attachment_image_url($img_id, 'full');
                    }
                    
                    if ($img_url) {
                        ?>
                        <div class="pf-hero-slide<?php echo $i === 0 ? ' active' : ''; ?>" 
                             style="background-image:url('<?php echo esc_url($img_url); ?>');"
                             data-title="<?php echo esc_attr($slide->post_title); ?>">
                        </div>
                        <?php
                    }
                }
            endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function regenerate_hero_image($post_id, $post) {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        
        // Only regenerate if the post has a thumbnail
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            
            // Force regeneration of the hero carousel size
            $image_data = wp_get_attachment_metadata($thumbnail_id);
            if ($image_data) {
                // Remove existing hero carousel size if it exists
                if (isset($image_data['sizes']['pf25-hero-carousel'])) {
                    unset($image_data['sizes']['pf25-hero-carousel']);
                }
                
                // Regenerate the image sizes
                $new_image_data = wp_generate_attachment_metadata($thumbnail_id, get_attached_file($thumbnail_id));
                if ($new_image_data) {
                    wp_update_attachment_metadata($thumbnail_id, $new_image_data);
                }
            }
        }
    }

    public function add_regenerate_button() {
        global $pagenow, $post_type;
        
        // Only show on hero-slide admin pages
        if ($pagenow === 'edit.php' && $post_type === 'hero-slide') {
            $regenerate_url = wp_nonce_url(
                admin_url('edit.php?post_type=hero-slide&action=regenerate_hero_images'),
                'regenerate_hero_images'
            );
            
            echo '<div class="notice notice-info is-dismissible">';
            echo '<p><strong>Hero Carousel:</strong> Se le immagini non si visualizzano correttamente, ';
            echo '<a href="' . esc_url($regenerate_url) . '" class="button button-secondary">Rigenera Immagini Carousel</a></p>';
            echo '</div>';
        }
    }
} 