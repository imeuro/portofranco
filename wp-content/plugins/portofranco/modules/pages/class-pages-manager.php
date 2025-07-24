<?php
/**
 * Pages Manager
 *
 * @package PF
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PF Pages Manager Class
 */
class PF_Pages_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'check_and_create_pages'));
    }
    
    /**
     * Check if pages exist and create them if needed
     */
    public function check_and_create_pages() {
        // Only run once
        if (get_option('pf_pages_created')) {
            return;
        }
        
        $this->create_default_pages();
        update_option('pf_pages_created', true);
    }
    
    /**
     * Create default pages
     */
    private function create_default_pages() {
        $pages = array(
            'home' => array(
                'title' => 'Home',
                'content' => $this->get_home_content(),
                'slug' => 'home',
                'template' => 'page-home.php'
            ),
            'attivita' => array(
                'title' => 'Attività',
                'content' => $this->get_attivita_content(),
                'slug' => 'attivita',
                'template' => 'page-attivita.php'
            ),
            'portfolio' => array(
                'title' => 'Portfolio Lavori',
                'content' => $this->get_portfolio_content(),
                'slug' => 'portfolio-lavori',
                'template' => 'page-portfolio.php'
            ),
            'contatti' => array(
                'title' => 'Contatti',
                'content' => $this->get_contatti_content(),
                'slug' => 'contatti',
                'template' => 'page-contatti.php'
            )
        );
        
        foreach ($pages as $key => $page_data) {
            $this->create_page($page_data);
        }
        
        // Set Home page as front page
        $home_page = get_page_by_path('home');
        if ($home_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home_page->ID);
        }
    }
    
    /**
     * Create a single page
     */
    private function create_page($page_data) {
        // Check if page already exists
        $existing_page = get_page_by_path($page_data['slug']);
        if ($existing_page) {
            return $existing_page->ID;
        }
        
        $page_args = array(
            'post_title'    => $page_data['title'],
            'post_content'  => $page_data['content'],
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => $page_data['slug'],
            'page_template' => $page_data['template']
        );
        
        $page_id = wp_insert_post($page_args);
        
        if ($page_id) {
            // Add custom meta if needed
            update_post_meta($page_id, '_wp_page_template', $page_data['template']);
        }
        
        return $page_id;
    }
    
    /**
     * Get Home page content
     */
    private function get_home_content() {
        return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1>Benvenuti in Milano RE Costruzioni</h1>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"fontSize":"large"} -->
    <p class="has-large-font-size">Specialisti nella ristrutturazione e costruzione di immobili a Milano e provincia.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p>Offriamo servizi completi di costruzione, ristrutturazione e manutenzione per privati e aziende. La nostra esperienza pluriennale e la passione per il lavoro ci permettono di garantire risultati eccellenti e la massima soddisfazione dei nostri clienti.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
        <!-- wp:button -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/attivita">Scopri i nostri servizi</a></div>
        <!-- /wp:button -->
        
        <!-- wp:button {"style":{"color":{"background":"#f0f0f0","text":"#333333"}}} -->
        <div class="wp-block-button"><a class="wp-block-button__link wp-element-button has-text-color has-background" style="background-color:#f0f0f0;color:#333333" href="/portfolio-lavori">Vedi i nostri lavori</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->';
    }
    
    /**
     * Get Attività page content
     */
    private function get_attivita_content() {
        return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1>Le Nostre Attività</h1>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"fontSize":"large"} -->
    <p class="has-large-font-size">Milano RE Costruzioni è specializzata in diversi ambiti della costruzione e ristrutturazione.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3>Ristrutturazioni</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p>Ristrutturazioni complete di appartamenti, uffici e locali commerciali. Dalla demolizione alla finitura, gestiamo ogni fase del progetto con professionalità e attenzione ai dettagli.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3>Costruzioni</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p>Costruzioni ex novo di edifici residenziali e commerciali. Progetti personalizzati che rispettano le normative vigenti e le esigenze specifiche del cliente.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3>Manutenzione</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p>Servizi di manutenzione ordinaria e straordinaria per mantenere in perfetto stato i vostri immobili. Interventi rapidi e professionali.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
    
    <!-- wp:paragraph -->
    <p>Contattaci per un preventivo gratuito e personalizzato per il tuo progetto.</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->';
    }
    
    /**
     * Get Portfolio page content
     */
    private function get_portfolio_content() {
        return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1>Portfolio Lavori</h1>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"fontSize":"large"} -->
    <p class="has-large-font-size">Scopri alcuni dei nostri lavori più significativi.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p>Di seguito trovate una selezione dei progetti che abbiamo realizzato. Ogni lavoro rappresenta la nostra passione per la qualità e l\'attenzione ai dettagli che caratterizzano Milano RE Costruzioni.</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:shortcode -->
    [lavori_grid]
    <!-- /wp:shortcode -->
</div>
<!-- /wp:group -->';
    }
    
    /**
     * Get Contatti page content
     */
    private function get_contatti_content() {
        return '<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":1} -->
    <h1>Contatti</h1>
    <!-- /wp:heading -->
    
    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3>Informazioni di Contatto</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p><strong>Milano RE Costruzioni</strong><br>
            Via Example, 123<br>
            20100 Milano (MI)<br>
            Italia</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:paragraph -->
            <p><strong>Telefono:</strong> +39 02 1234567<br>
            <strong>Email:</strong> info@milanorecostruzioni.it</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:paragraph -->
            <p><strong>Orari di lavoro:</strong><br>
            Lun-Ven: 8:00 - 18:00<br>
            Sab: 8:00 - 12:00<br>
            Dom: Chiuso</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:heading {"level":3} -->
            <h3>Richiedi un Preventivo</h3>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p>Compila il form qui sotto per richiedere un preventivo gratuito e personalizzato per il tuo progetto.</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:shortcode -->
            [contact_form]
            <!-- /wp:shortcode -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->';
    }
} 