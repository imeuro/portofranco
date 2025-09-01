<div class="about-end-content">
    <ul class="page-table">
        <li class="left-column"></li>
        <li class="right-column">
            <h3 class="footer-label small-label"><?php echo $lang == 'ita' ? 'CONTATTI' : 'CONTACTS'; ?></h3>
        </li>
        <li class="left-column small-label">mail</li>
        <li class="right-column mid-label">
            <a href="mailto:info@portofranco.eu">info@portofranco.eu</a>
        </li>
        <li class="left-column small-label">whatsapp</li>
        <li class="right-column mid-label"><a href="https://wa.me/393317907368">+39 331 7907368</a></li>
        <li class="left-column small-label"><?php echo $lang == 'ita' ? 'indirizzo' : 'address'; ?></li>
        <li class="right-column mid-label">Palazzo Soranzo Novello<br>Corso 29 Aprile, 23<br>31033 Castelfranco Veneto TV</li>
    </ul>

    <ul class="page-table">
        <li class="left-column"></li>
        <li class="right-column">
            <h3 class="footer-label small-label">TEAM</h3>
        </li>

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
        <li class="right-column mid-label">Alberto Iazzi</li>

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


    <ul class="page-table" id="newsletter">
        <li class="left-column small-label">Newsletter</li>
        <li class="right-column mid-label">
            <?php 
            // Mostra il form Contact Form 7 se disponibile
            if (function_exists('wpcf7_contact_form')) {
                // ID del form da sostituire con quello reale dopo la creazione
                echo do_shortcode('[contact-form-7 id="db66d6b" title="Newsletter"]');
            } else {
                // Fallback se Contact Form 7 non è attivo
                ?>
                <form class="newsletter-form">
                    <div class="form-group">
                        <input type="email" placeholder="<?php echo $lang == 'ita' ? 'La tua email' : 'Your email'; ?>" required>
                        <input type="hidden" name="language" value="<?php echo $lang; ?>">
                    </div>
                    <button type="submit"><?php echo $lang == 'ita' ? 'Iscriviti' : 'Subscribe'; ?></button>
                </form>
                <?php
            }
            ?>
        </li>
    </ul>
</div>