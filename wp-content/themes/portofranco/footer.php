<?php
// Footer e chiusura documento HTML
?>

  <footer id="colophon" role="contentinfo">
    <div class="footer-content">
        
        <ul class="footer-contacts">
            <li class="left-column"></li>
            <li class="right-column">
                <h3 class="footer-label small-label"><?php _e('APERTURA', 'portofranco'); ?></h3>
            </li>

            <li class="left-column small-label"><?php _e('PERIODO', 'portofranco'); ?></li>
            <li class="right-column big-label">
                <p>
                  <?php _e('dal 15 novembre 2025 al 14 febbraio 2026', 'portofranco'); ?>
                </p>
            </li>
            <li class="left-column small-label"><?php _e('ORARI', 'portofranco'); ?></li>
            <li class="right-column big-label">
                <p>
                  <?php _e('lunedì-martedì-mercoledì: chiuso<br>', 'portofranco'); ?>
                  <?php _e('giovedì - venerdì - sabato - domenica: 10:00-19:00', 'portofranco'); ?>
                </p>
            </li>

            <li class="left-column"></li>
            <li class="right-column">
                <h3 class="footer-label small-label"><?php _e('CONTATTI', 'portofranco'); ?></h3>
            </li>
            <li class="left-column small-label">mail</li>
            <li class="right-column big-label">
                <a href="mailto:info@portofranco.eu">info@portofranco.eu</a>
            </li>
            <li class="left-column small-label"><?php _e('indirizzo', 'portofranco'); ?></li>
            <li class="right-column big-label">Palazzo Soranzo Novello<br>Corso 29 Aprile, 23<br>31033 Castelfranco Veneto TV</li>
            <li class="left-column small-label">social</li>
            <li class="right-column big-label space-bottom">
                <a href="https://www.instagram.com/_portofranco_/">instagram</a><br>
                <a href="<?php echo portofranco_get_page_link('about', 'about-eng'); ?>#newsletter"><?php _e('iscriviti alla newsletter', 'portofranco'); ?></a>
            </li>
            <li class="left-column small-label"></li>
            <li class="right-column small-label space-bottom">
                <a href="<?php echo portofranco_get_page_link('termini-condizioni', 'terms-conditions-en'); ?>"><?php _e('Termini & Condizioni', 'portofranco'); ?></a><br>
                <a href="<?php echo portofranco_get_page_link('privacy-policy', 'privacy-policy-en'); ?>">Privacy Policy</a><br>
                <a href="<?php echo portofranco_get_page_link('cookies', 'cookies-en'); ?>">Cookies</a><br><br>
                &copy; Portofranco <?php echo date('Y'); ?>
            </li>
        </ul>
    </div>
  </footer>
  <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-65H72CQ48V"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-65H72CQ48V');
</script>
  <?php wp_footer(); ?>
</body>
</html> 