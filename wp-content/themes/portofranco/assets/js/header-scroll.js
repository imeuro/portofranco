
/**
 * Header Scroll Management
 * Gestisce lo spostamento dell'header quando il footer si avvicina
 */

const headerScrollManager = (() => {
    // Elementi DOM
    const header = document.getElementById('masthead');
    const footer = document.getElementById('colophon');
    
    // Configurazione
    const config = {
        minDistance: 100, // Distanza minima tra header e footer in px
        scrollThreshold: 50, // Soglia per iniziare lo scroll
        animationDuration: 300 // Durata animazione in ms
    };
    
    // Stato corrente
    let isHeaderMoving = false;
    let currentOffset = 0;
    
    /**
     * Calcola la distanza tra header e footer
     */
    const calculateDistance = () => {
        if (!header || !footer) return Infinity;
        
        const headerRect = header.getBoundingClientRect();
        const footerRect = footer.getBoundingClientRect();
        
        return footerRect.top - headerRect.bottom;
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
        const distance = calculateDistance();
        
        // Se il footer è troppo vicino
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
    };
    
    /**
     * Inizializza il gestore
     */
    const init = () => {
        if (!header || !footer) {
            console.warn('Header Scroll Manager: Elementi header o footer non trovati');
            return;
        }
        
        // Aggiungi listener per lo scroll
        window.addEventListener('scroll', handleHeaderScroll, { passive: true });
        
        // Esegui controllo iniziale
        handleHeaderScroll();
        
        console.log('Header Scroll Manager inizializzato');
    };
    
    /**
     * Pulisci i listener
     */
    const destroy = () => {
        window.removeEventListener('scroll', handleHeaderScroll);
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