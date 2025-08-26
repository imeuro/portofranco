/**
 * Archive Content Loader
 * Gestisce il caricamento dinamico del contenuto dei post negli archivi
 */

const archiveContentLoader = (() => {
    /**
     * Determina se siamo su dispositivo mobile
     */
    const isMobile = () => {
        return window.innerWidth <= 768;
    };

    /**
     * Determina l'URL base dell'API in base all'ambiente
     */
    const getApiBaseUrl = () => {
        // Se è definita una variabile globale, usala
        if (window.PORTOFRANCO_API_BASE) {
            return window.PORTOFRANCO_API_BASE;
        }
        
        // Se è disponibile la variabile localizzata da WordPress, usala
        if (window.portofrancoAjax && window.portofrancoAjax.apiBase) {
            return window.portofrancoAjax.apiBase;
        }
        
        const hostname = window.location.hostname;
        const pathname = window.location.pathname;
        
        // Estrai il path del progetto dal pathname corrente
        const projectPath = pathname.split('/')[1] || '';
        
        // Se siamo in localhost o meuro.dev, aggiungi il path del progetto
        if (hostname === 'localhost' || hostname === 'meuro.dev' || hostname.includes('localhost')) {
            return `/${projectPath}/wp-json/pf/v1/post-content/`;
        }
        
        // Per altri ambienti, usa il path standard
        return '/wp-json/pf/v1/post-content/';
    };

    /**
     * Costruisce l'URL completo per la richiesta API
     */
    const buildApiUrl = (postId) => {
        const baseUrl = config.apiBase;
        
        // Se l'URL base è relativo, costruisci l'URL completo
        if (baseUrl.startsWith('/')) {
            return `${window.location.origin}${baseUrl}${postId}`;
        }
        
        return `${baseUrl}${postId}`;
    };

    // Configurazione
    const config = {
        apiBase: getApiBaseUrl(),
        selectors: {
            list: '#side-archive-list',
            items: '.side-archive-item',
            contentArea: '#main-textarea',
            mobileContentArea: '#mob-main-textarea',
            links: '.side-archive-item a'
        },
        classes: {
            inactive: 'inactive',
            loading: 'loading',
            error: 'error'
        },
        animation: {
            duration: 300,
            easing: 'ease-out'
        }
    };

    // Stato dell'applicazione
    let state = {
        currentPostId: null,
        originalContent: null,
        isLoading: false,
        isMobile: false
    };

    // Elementi DOM
    let elements = {};

    /**
     * Inizializza il loader
     */
    const init = () => {
        console.log('Archive Content Loader: Inizializzazione...');
        
        // Aggiorna lo stato mobile
        state.isMobile = isMobile();
        
        // Trova gli elementi DOM
        elements.list = document.querySelector(config.selectors.list);
        
        if (state.isMobile) {
            console.log('Archive Content Loader: Modalità mobile rilevata');
            // Su mobile non cerchiamo #main-textarea inizialmente
        } else {
            elements.contentArea = document.querySelector(config.selectors.contentArea);
            if (!elements.contentArea) {
                console.warn('Archive Content Loader: Elemento #main-textarea non trovato');
                return;
            }
            // Salva il contenuto originale solo su desktop
            state.originalContent = elements.contentArea.innerHTML;
        }
        
        if (!elements.list) {
            console.warn('Archive Content Loader: Elemento lista non trovato');
            return;
        }

        // Ottieni il post type dall'attributo data
        const postType = elements.list.dataset.postType;
        console.log('Archive Content Loader: Post type rilevato:', postType);
        console.log('Archive Content Loader: Elementi lista trovati:', elements.list.querySelectorAll(config.selectors.items).length);
        console.log('Archive Content Loader: API Base URL:', config.apiBase);
        console.log('Archive Content Loader: Modalità:', state.isMobile ? 'Mobile' : 'Desktop');
        
        // Aggiungi event listeners
        addEventListeners();
        
        console.log('Archive Content Loader: Inizializzazione completata');
    };

    /**
     * Aggiunge gli event listeners
     */
    const addEventListeners = () => {
        // Event delegation per i link
        elements.list.addEventListener('click', handleItemClick);
        
        // Gestione navigazione browser
        window.addEventListener('popstate', handlePopState);
        
        // Gestione resize per cambiare modalità
        window.addEventListener('resize', handleResize);
    };

    /**
     * Gestisce il resize della finestra
     */
    const handleResize = () => {
        const wasMobile = state.isMobile;
        state.isMobile = isMobile();
        
        // Se è cambiata la modalità, ripristina lo stato originale
        if (wasMobile !== state.isMobile) {
            console.log('Archive Content Loader: Cambio modalità da', wasMobile ? 'Mobile' : 'Desktop', 'a', state.isMobile ? 'Mobile' : 'Desktop');
            
            if (state.isMobile) {
                // Passaggio a mobile: rimuovi contenuto da desktop
                if (elements.contentArea) {
                    elements.contentArea.innerHTML = state.originalContent || '';
                }
            } else {
                // Passaggio a desktop: rimuovi contenuto mobile
                const mobileContent = document.querySelector(config.selectors.mobileContentArea);
                if (mobileContent) {
                    mobileContent.remove();
                }
                // Ripristina contenuto originale su desktop
                if (elements.contentArea && state.originalContent) {
                    elements.contentArea.innerHTML = state.originalContent;
                }
            }
            
            // Reset dello stato
            state.currentPostId = null;
            state.isLoading = false;
        }
    };

    /**
     * Crea il contenitore mobile dopo il link cliccato
     */
    const createMobileContentArea = (clickedLink) => {
        // Rimuovi eventuali contenitori mobile esistenti
        const existingMobileContent = document.querySelector(config.selectors.mobileContentArea);
        if (existingMobileContent) {
            existingMobileContent.remove();
        }
        
        // Crea il nuovo contenitore
        const mobileContentArea = document.createElement('div');
        mobileContentArea.id = 'mob-main-textarea';
        mobileContentArea.className = 'mobile-content-area';
        
        // Inserisci dopo il link cliccato
        clickedLink.parentNode.insertBefore(mobileContentArea, clickedLink.nextSibling);
        
        return mobileContentArea;
    };

    /**
     * Ottiene l'ID del post dall'attributo data
     */
    const getPostIdFromData = (link, postType) => {
        // Mappa dei post types ai loro attributi data
        const dataAttributeMap = {
            'artisti': 'artist',
            'agenda': 'agenda',
            'post': 'post'
        };
        
        const dataAttribute = dataAttributeMap[postType] || postType;
        return link.dataset[`${dataAttribute}Id`];
    };

    /**
     * Gestisce il click sugli elementi della lista
     */
    const handleItemClick = (event) => {
        const link = event.target.closest('a');
        if (!link) return;
        
        event.preventDefault();
        link.classList.toggle('active');
        link.closest('.side-archive-item').classList.toggle('active');
        
        // Ottieni l'ID del post dall'attributo data
        const postType = elements.list.dataset.postType;
        const postId = getPostIdFromData(link, postType);
        
        if (!postId) {
            console.error('Archive Content Loader: ID post non trovato');
            console.log('Archive Content Loader: Post type:', postType);
            console.log('Archive Content Loader: Available data attributes:', Object.keys(link.dataset));
            console.log('Archive Content Loader: Link HTML:', link.outerHTML);
            return;
        }
        
        // Carica il contenuto
        loadPostContent(postId, link);
    };

    /**
     * Gestisce la navigazione del browser (back/forward)
     */
    const handlePopState = (event) => {
        if (event.state && event.state.postId) {
            loadPostContent(event.state.postId);
        } else {
            restoreOriginalContent();
        }
    };

    /**
     * Carica il contenuto di un post
     */
    const loadPostContent = async (postId, clickedLink = null) => {
        if (state.isLoading || state.currentPostId === postId) return;
        
        state.isLoading = true;
        state.currentPostId = postId;
        
        // Aggiorna UI
        updateUI(clickedLink);
        
        try {
            console.log('Archive Content Loader: Caricamento post ID:', postId);
            const apiUrl = buildApiUrl(postId);
            console.log('Archive Content Loader: URL richiesta:', apiUrl);
            
            // Effettua la richiesta API
            const response = await fetch(apiUrl);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Aggiorna il contenuto
            updateContent(data, clickedLink);
            
            // Aggiorna l'URL del browser
            updateBrowserURL(postId, data.title);
            
            console.log('Archive Content Loader: Contenuto caricato con successo');
            
        } catch (error) {
            console.error('Archive Content Loader: Errore nel caricamento:', error);
            handleError(error, clickedLink);
        } finally {
            state.isLoading = false;
        }
    };

    /**
     * Aggiorna l'interfaccia utente
     */
    const updateUI = (clickedLink) => {
        // Aggiungi classe inactive a tutti gli elementi
        document.querySelectorAll(config.selectors.items).forEach(item => {
            item.classList.add(config.classes.inactive);
        });
        
        // Rimuovi classe inactive all'elemento cliccato
        if (clickedLink) {
            clickedLink.closest(config.selectors.items).classList.remove(config.classes.inactive);
        }
        
        // Aggiungi classe loading all'area contenuto appropriata
        if (state.isMobile) {
            // Su mobile, la classe loading verrà aggiunta dopo la creazione del contenitore
        } else {
            elements.contentArea.classList.add(config.classes.loading);
        }
    };

    /**
     * Aggiorna il contenuto dell'area principale
     */
    const updateContent = (data, clickedLink) => {
        let targetContentArea;
        
        if (state.isMobile) {
            // Su mobile, crea il contenitore dopo il link cliccato
            targetContentArea = createMobileContentArea(clickedLink);
        } else {
            // Su desktop, usa il contenitore esistente
            targetContentArea = elements.contentArea;
        }
        
        // Rimuovi classe loading
        targetContentArea.classList.remove(config.classes.loading);
        
        // Crea il nuovo contenuto
        const newContent = `
            <div class="dynamic-content">
                ${data.content}
            </div>
        `;
        
        // Aggiorna il contenuto con animazione
        targetContentArea.style.opacity = '0';
        targetContentArea.innerHTML = newContent;
        
        // Anima l'apparizione
        setTimeout(() => {
            targetContentArea.style.transition = `opacity ${config.animation.duration}ms ${config.animation.easing}`;
            targetContentArea.style.opacity = '1';
        }, 10);
    };

    /**
     * Gestisce gli errori
     */
    const handleError = (error, clickedLink) => {
        let targetContentArea;
        
        if (state.isMobile) {
            // Su mobile, crea il contenitore per l'errore
            targetContentArea = createMobileContentArea(clickedLink);
        } else {
            targetContentArea = elements.contentArea;
        }
        
        targetContentArea.classList.remove(config.classes.loading);
        targetContentArea.classList.add(config.classes.error);
        
        const errorContent = `
            <div class="error-message">
                <h3>Errore nel caricamento</h3>
                <p>Impossibile caricare il contenuto richiesto. Riprova più tardi.</p>
            </div>
        `;
        
        targetContentArea.innerHTML = errorContent;
    };

    /**
     * Aggiorna l'URL del browser
     */
    const updateBrowserURL = (postId, title) => {
        const url = new URL(window.location);
        url.searchParams.set('post', postId);
        url.searchParams.set('title', encodeURIComponent(title));
        
        window.history.pushState({ postId }, title, url);
    };

    /**
     * Ripristina il contenuto originale
     */
    const restoreOriginalContent = () => {
        state.currentPostId = null;
        
        if (state.isMobile) {
            // Su mobile, rimuovi tutti i contenitori mobile
            const mobileContents = document.querySelectorAll(config.selectors.mobileContentArea);
            mobileContents.forEach(content => content.remove());
        } else {
            // Su desktop, ripristina il contenuto originale
            if (elements.contentArea) {
                elements.contentArea.classList.remove(config.classes.loading, config.classes.error);
                
                elements.contentArea.style.opacity = '0';
                elements.contentArea.innerHTML = state.originalContent;
                
                setTimeout(() => {
                    elements.contentArea.style.opacity = '1';
                }, 10);
            }
        }
        
        // Rimuovi classe active da tutti gli elementi
        document.querySelectorAll(config.selectors.items).forEach(item => {
            item.classList.remove(config.classes.inactive);
        });
        
        // Aggiorna l'URL del browser
        const url = new URL(window.location);
        url.searchParams.delete('post');
        url.searchParams.delete('title');
        window.history.pushState({}, document.title, url);
    };

    /**
     * Pubblica l'API pubblica
     */
    return {
        init,
        loadPostContent,
        restoreOriginalContent,
        config
    };
})();

// Inizializza quando il DOM è pronto
document.addEventListener('DOMContentLoaded', archiveContentLoader.init);

// Esponi globalmente per debug
window.archiveContentLoader = archiveContentLoader;
