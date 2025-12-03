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
                  <?php _e('dettaglio periodo', 'portofranco'); ?>
            </li>
            <li class="left-column small-label"><?php _e('ORARI', 'portofranco'); ?></li>
            <li class="right-column big-label">
                <?php _e('dettaglio orari', 'portofranco'); ?><br>
                <a href="<?php echo portofranco_get_page_link('about', 'about-eng'); ?>#info" class="small-label"><svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" class="icon-link-info"><path d="M440-280H280q-83 0-141.5-58.5T80-480q0-83 58.5-141.5T280-680h160v80H280q-50 0-85 35t-35 85q0 50 35 85t85 35h160v80ZM320-440v-80h320v80H320Zm200 160v-80h160q50 0 85-35t35-85q0-50-35-85t-85-35H520v-80h160q83 0 141.5 58.5T880-480q0 83-58.5 141.5T680-280H520Z"/></svg>&nbsp;<?php _e('APERTURE STRAORDINARIE', 'portofranco'); ?></a><br><br>
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