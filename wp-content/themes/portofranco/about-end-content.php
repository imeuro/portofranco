<div class="about-end-content">
    <ul class="page-table" id="item-info">
        <li class="left-column"><h3 class="footer-label small-label"><?php _e('CONTATTI', 'portofranco'); ?></h3></li>
        <li class="right-column"></li>
        <li class="left-column small-label">mail</li>
        <li class="right-column mid-label">
            <a href="mailto:info@portofranco.eu">info@portofranco.eu</a>
        </li>
        <li class="left-column small-label">whatsapp</li>
        <li class="right-column mid-label"><a href="https://wa.me/393317907368">+39 331 7907368</a></li>
        <li class="left-column small-label"><?php _e('indirizzo', 'portofranco'); ?></li>
        <li class="right-column mid-label"><a href="https://www.google.com/maps?q=Palazzo+Soranzo+Novello,+Corso+29+Aprile,+23,+31033+Castelfranco+Veneto+TV" target="_blank" rel="noopener noreferrer">Palazzo Soranzo Novello<br>Corso 29 Aprile, 23<br>31033 Castelfranco Veneto TV</a></li>
    </ul>

    <ul class="page-table" id="item-team">
        <li class="left-column"><h3 class="footer-label small-label">TEAM</h3></li>
        <li class="right-column"></li>

        <li class="left-column small-label">Artistic Director</li>
        <li class="right-column mid-label">Rossella Farinotti</li>

        <li class="left-column small-label">Project Manager</li>
        <li class="right-column mid-label">Eleonora Santin</li>

        <li class="left-column small-label">Project Manager</li>
        <li class="right-column mid-label">Lisa Rebellato</li>

        <li class="left-column small-label">Project Coordinator</li>
        <li class="right-column mid-label">Chiara Mantegazza</li>

        <li class="left-column small-label">Programme Coordinator</li>
        <li class="right-column mid-label">Daniele Costa</li>   

        <li class="left-column small-label">Communication Coordinator</li>
        <li class="right-column mid-label">Alessia Romano</li>

        <li class="left-column small-label">Production Coordinator</li>
        <li class="right-column mid-label">Crates</li>

        <li class="left-column small-label">Web Developer</li>
        <li class="right-column mid-label">Mauro Fioravanzi</li>

        <li class="left-column small-label">Visual Identity & Graphic Designer</li>
        <li class="right-column mid-label">Filippo Antonioli</li>

        <li class="left-column small-label">Press Office</li>
        <li class="right-column mid-label">Lara Facco</li>

        <li class="left-column small-label">Direttore Museo Casa Giorgione</li>
        <li class="right-column mid-label">Matteo Melchiorre</li>

        <li class="left-column small-label">Responsabile segreteria Museo Casa Giorgione</li>
        <li class="right-column mid-label">Marta Favaron</li>

    </ul>

    <?php
    // Query per ottenere la pagina "partners"
    $partners_page = get_page_by_path('partners');
    if ($partners_page) { ?>
    <ul class="page-table" id="item-partners" data-scroll="partners">
        <li class="left-column"><h3 class="footer-label small-label">In collaborazione con</h3></li>
        <li class="right-column"></li>
        <li class="right-column mid-label">
        <?php 
            // Ottiene il contenuto della pagina
            $content = apply_filters('the_content', $partners_page->post_content);
            echo $content;
        ?>
        </li>
    </ul>
    <?php } ?>

    <ul class="page-table" id="item-newsletter">
        <li>
            <h3 class="footer-label small-label"><?php _e('iscriviti alla newsletter', 'portofranco'); ?></h3>
            <?php 
            // Mostra il form Contact Form 7 se disponibile
            if (function_exists('wpcf7_contact_form')) {
                // Determina la lingua corrente
                $current_lang = function_exists('pll_current_language') ? pll_current_language() : (get_locale() == 'en_GB' ? 'en' : 'it');
                $is_english = ($current_lang === 'en' || is_page('about-eng') || get_locale() == 'en_GB');
                
                if ($is_english) {
                    // Form inglese
                    echo do_shortcode('[contact-form-7 id="a593dbf" title="Newsletter EN"]');
                } else {
                    // Form italiano
                    echo do_shortcode('[contact-form-7 id="db66d6b" title="Newsletter"]');
                }
            } else {
                // Fallback se Contact Form 7 non è attivo
                ?>
                <form class="newsletter-form">
                    <div class="form-group">
                        <input type="email" placeholder="<?php esc_attr_e('La tua email', 'portofranco'); ?>" required>
                        <input type="hidden" name="language" value="<?php echo function_exists('pll_current_language') ? pll_current_language() : (get_locale() == 'en_GB' ? 'en' : 'it'); ?>">
                    </div>
                    <button type="submit"><?php _e('Iscriviti', 'portofranco'); ?></button>
                </form>
                <?php
            }
            ?>
        </li>
    </ul>
</div>