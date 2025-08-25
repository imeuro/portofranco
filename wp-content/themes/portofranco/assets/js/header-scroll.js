
/**
 * Header Scroll Management
 * Gestisce lo spostamento dell'header quando il contenuto principale o footer si avvicina
 */

const headerScrollManager = (() => {
    // Elementi DOM
    const header = document.getElementById('masthead');
    const pcontent = document.getElementById('main');
    const footer = document.getElementById('colophon');
    
    // Configurazione
    const config = {
        minDistance: 100, // Distanza minima tra header e elemento di riferimento in px
        scrollThreshold: 50, // Soglia per iniziare lo scroll
        animationDuration: 500, // Durata animazione in ms
        mobileScrollThreshold: 250 // Pixel di scroll per spostare l'header su mobile
    };
    
    // Stato corrente
    let isHeaderMoving = false;
    let currentOffset = 0;
    let isInitialized = false; // Flag per tracciare se è stato fatto il primo scroll
    
    /**
     * Verifica se il dispositivo è mobile
     */
    const isMobile = () => {
        return window.innerWidth <= 768;
    };
    
    /**
     * Calcola la distanza tra header e l'elemento di riferimento (pcontent o footer)
     */
    const calculateDistance = () => {
        if (!header) return Infinity;
        
        const headerRect = header.getBoundingClientRect();
        
        // // Se pcontent è presente, usa quello come riferimento
        // if (pcontent) {
        //     const pcontentRect = pcontent.getBoundingClientRect();
        //     return pcontentRect.top - headerRect.bottom;
        // }
        
        // Altrimenti usa il footer come fallback
        if (footer) {
            const footerRect = footer.getBoundingClientRect();
            return footerRect.top - headerRect.bottom;
        }
        
        return Infinity;
    };
    
    /**
     * Applica trasformazione all'header
     */
    const applyHeaderTransform = (offset) => {
        if (!header) return;
        
        header.style.transform = `translateY(${offset}px)`;
        currentOffset = offset;
    };
    
    /**
     * Gestisce lo scroll dell'header
     */
    const handleHeaderScroll = () => {
        // Se non è ancora stato fatto il primo scroll, attiva il sistema
        if (!isInitialized) {
            isInitialized = true;
            console.log('Header Scroll Manager: Primo scroll rilevato, sistema attivato');
        }
        
        // Logica diversa per mobile e desktop
        if (isMobile()) {
            // Comportamento mobile: sposta l'header fuori campo dopo 250px di scroll
            const scrollTop = window.pageYOffset;
            const maxOffset = header.offsetHeight + 20; // Altezza header + margine
            
            if (scrollTop > config.mobileScrollThreshold) {
                // Sposta l'header fuori campo
                if (currentOffset === 0) {
                    header.style.transition = `transform ${config.animationDuration}ms ease-out`;
                    applyHeaderTransform(-maxOffset);
                    isHeaderMoving = true;
                }
            } else {
                // Riporta l'header alla posizione originale
                if (currentOffset !== 0) {
                    header.style.transition = `transform ${config.animationDuration}ms ease-out`;
                    applyHeaderTransform(0);
                    isHeaderMoving = false;
                }
            }
        } else {
            // Comportamento desktop: logica originale basata sulla distanza
            const distance = calculateDistance();
            
            // Se l'elemento di riferimento è troppo vicino
            if (distance < config.minDistance) {
                const neededOffset = config.minDistance - distance;
                const maxOffset = header.offsetHeight + 20; // Altezza header + margine
                
                // Calcola l'offset necessario
                const targetOffset = Math.min(neededOffset, maxOffset);
                
                // Applica la trasformazione con animazione
                if (Math.abs(targetOffset - currentOffset) > config.scrollThreshold) {
                    header.style.transition = `transform ${config.animationDuration}ms ease-out`;
                    applyHeaderTransform(-targetOffset);
                    isHeaderMoving = true;
                }
            } else {
                // Riporta l'header alla posizione originale
                if (currentOffset !== 0) {
                    header.style.transition = `transform ${config.animationDuration}ms ease-out`;
                    applyHeaderTransform(0);
                    isHeaderMoving = false;
                }
            }
        }
    };
    
    /**
     * Gestisce il resize della finestra
     */
    const handleResize = () => {
        // Riporta l'header alla posizione originale quando cambia la dimensione della finestra
        if (currentOffset !== 0) {
            header.style.transition = `transform ${config.animationDuration}ms ease-out`;
            applyHeaderTransform(0);
            isHeaderMoving = false;
        }
    };
    
    /**
     * Inizializza il gestore
     */
    const init = () => {
        if (!header) {
            console.warn('Header Scroll Manager: Elemento header non trovato');
            return;
        }
        
        // Verifica che almeno un elemento di riferimento sia presente
        if (!pcontent && !footer) {
            console.warn('Header Scroll Manager: Nessun elemento di riferimento (pcontent o footer) trovato');
            return;
        }
        
        // Aggiungi listener per lo scroll
        window.addEventListener('scroll', handleHeaderScroll, { passive: true });
        
        // Aggiungi listener per il resize
        window.addEventListener('resize', handleResize, { passive: true });
        
        const referenceElement = pcontent ? 'pcontent' : 'footer';
        const deviceType = isMobile() ? 'mobile' : 'desktop';
        console.log(`Header Scroll Manager inizializzato con riferimento a: ${referenceElement} - Dispositivo: ${deviceType} - Attivazione al primo scroll`);
    };
    
    /**
     * Pulisci i listener
     */
    const destroy = () => {
        window.removeEventListener('scroll', handleHeaderScroll);
        window.removeEventListener('resize', handleResize);
        if (header) {
            header.style.transform = '';
            header.style.transition = '';
        }
    };
    
    // API pubblica
    return {
        init,
        destroy,
        handleHeaderScroll
    };
})();



/**
 * Menu Toggle Management
 * Gestisce l'apertura e chiusura del menu mobile
 */

const menuToggleManager = (() => {
    // Elementi DOM
    const menuToggle = document.querySelector('.menu-toggle');
    const siteNavigation = document.getElementById('site-navigation');
    
    // Configurazione
    const config = {
        activeClass: 'menu-active',
        animationDuration: 300
    };
    
    // Stato corrente
    let isMenuOpen = false;
    
    /**
     * Apre il menu
     */
    const openMenu = () => {
        if (!siteNavigation) return;
        
        siteNavigation.parentElement.classList.add(config.activeClass);
        isMenuOpen = true;
        
        // Aggiorna l'attributo aria-expanded del bottone
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'true');
        }
        
        // Blocca lo scroll del body
        document.body.style.overflow = 'hidden';
    };
    
    /**
     * Chiude il menu
     */
    const closeMenu = () => {
        if (!siteNavigation) return;
        
        siteNavigation.parentElement.classList.remove(config.activeClass);
        isMenuOpen = false;
        
        // Aggiorna l'attributo aria-expanded del bottone
        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
        }
        
        // Ripristina lo scroll del body
        document.body.style.overflow = '';
    };
    
    /**
     * Toggle del menu
     */
    const toggleMenu = () => {
        if (isMenuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    };
    
    /**
     * Gestisce il click sul bottone menu
     */
    const handleMenuToggle = (event) => {
        event.preventDefault();
        toggleMenu();
    };
    
    /**
     * Gestisce la chiusura del menu con ESC
     */
    const handleKeydown = (event) => {
        if (event.key === 'Escape' && isMenuOpen) {
            closeMenu();
        }
    };
    
    /**
     * Gestisce la chiusura del menu al click fuori
     */
    const handleClickOutside = (event) => {
        if (isMenuOpen && 
            !siteNavigation?.contains(event.target) && 
            !menuToggle?.contains(event.target)) {
            closeMenu();
        }
    };
    
    /**
     * Inizializza il gestore del menu
     */
    const init = () => {
        if (!menuToggle || !siteNavigation) {
            console.warn('Menu Toggle Manager: Elementi menu non trovati');
            return;
        }
        
        // Aggiungi listener per il click sul bottone
        menuToggle.addEventListener('click', handleMenuToggle);
        
        // Aggiungi listener per la tastiera (ESC)
        document.addEventListener('keydown', handleKeydown);
        
        // Aggiungi listener per click fuori dal menu
        document.addEventListener('click', handleClickOutside);
        
        console.log('Menu Toggle Manager inizializzato');
    };
    
    /**
     * Pulisci i listener
     */
    const destroy = () => {
        if (menuToggle) {
            menuToggle.removeEventListener('click', handleMenuToggle);
        }
        document.removeEventListener('keydown', handleKeydown);
        document.removeEventListener('click', handleClickOutside);
        
        // Chiudi il menu se aperto
        if (isMenuOpen) {
            closeMenu();
        }
    };
    
    // API pubblica
    return {
        init,
        destroy,
        openMenu,
        closeMenu,
        toggleMenu
    };
})();

// Inizializza quando il DOM è pronto
document.addEventListener('DOMContentLoaded', () => {
    headerScrollManager.init();
    menuToggleManager.init();
});

// Esporta per uso globale se necessario
window.headerScrollManager = headerScrollManager;
window.menuToggleManager = menuToggleManager; 