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

// Inizializza quando il DOM è pronto
document.addEventListener('DOMContentLoaded', headerScrollManager.init);

// Esporta per uso globale se necessario
window.headerScrollManager = headerScrollManager; 