/**
 * Exhibition Map Frontend
 * Gestisce la navigazione tra i piani e il modal delle opere
 */

const ExhibitionMap = (() => {
  'use strict';

  let currentFloor = 'anni70-0'; // Default al primo floor disponibile
  let artworksByFloor = {};
  let isLoading = false;
  let isClosing = false;
  let positionTimeout = null;

  const elements = {
    floorMaps: null,
    currentFloorNumber: null,
    modal: null,
    modalOverlay: null,
    modalClose: null,
    modalContent: null,
    modalTitle: null,
    modalArtistName: null,
    modalArtistLink: null,
    modalDescription: null,
    modalImage: null,
    modalImageContainer: null,
  };

  const init = () => {
    // Cache elementi DOM
    cacheElements();

    if (!elements.floorMaps || elements.floorMaps.length === 0) {
      console.warn('Exhibition Map: Nessuna mappa trovata');
      return;
    }

    // Carica i dati delle opere
    loadAllArtworks();

    // Event listeners
    bindEvents();

    // Inizializza primo piano (primo floor disponibile)
    const firstFloorMap = elements.floorMaps[0];
    if (firstFloorMap) {
      const firstFloor = firstFloorMap.dataset.floor;
      currentFloor = firstFloor || 'anni70-0';
      showFloor(currentFloor);
    }
    
    // Inizializza accordion: espandi solo primo piano
    initAccordion();
  };

  const cacheElements = () => {
    elements.floorMaps = document.querySelectorAll('.floor-map');
    elements.currentFloorNumber = document.querySelector('.current-floor-number');
    elements.modal = document.querySelector('.artwork-modal');
    elements.modalOverlay = document.querySelector('.modal-overlay');
    elements.modalClose = document.querySelector('.modal-close');
    elements.modalContent = document.querySelector('.modal-content');
    elements.modalTitle = document.querySelector('.modal-artwork-title');
    elements.modalArtistName = document.querySelector('.modal-artist-name');
    elements.modalArtistLink = document.querySelector('.modal-artist-link');
    elements.modalDescription = document.querySelector('.modal-artwork-description');
    elements.modalImage = document.querySelector('.modal-artwork-image-img');
    elements.modalImageContainer = document.querySelector('.modal-artwork-image');
  };

  const bindEvents = () => {
    // Accordion floor list
    const floorToggles = document.querySelectorAll('.floor-toggle');
    floorToggles.forEach(toggle => {
      toggle.addEventListener('click', (e) => {
        const floorItem = e.currentTarget.closest('.exhibition-floor');
        const floor = floorItem ? floorItem.dataset.floor : null;
        if (floor) {
          toggleFloorAccordion(floor);
        }
      });
    });

    // Modal
    if (elements.modalClose) {
      elements.modalClose.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeModal();
      });
    }

    if (elements.modalOverlay) {
      elements.modalOverlay.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeModal();
      });
    }

    // Chiudi modal con ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && elements.modal.getAttribute('aria-hidden') === 'false') {
        closeModal();
      }
    });
  };

  const loadAllArtworks = async () => {
    if (isLoading) return;

    isLoading = true;

    try {
      // Usa l'endpoint global definito in functions.php
      const apiBase = window.portofrancoAjax?.apiBase || '/wp-json/pf/v1/';
      const response = await fetch(apiBase + 'exhibition/all');

      if (!response.ok) {
        throw new Error('Errore nel caricamento delle opere');
      }

      const data = await response.json();

      if (data.success && data.artworks_by_floor) {
        artworksByFloor = data.artworks_by_floor;

        // Renderizza marker per tutti i piani disponibili
        Object.keys(artworksByFloor).forEach(floor => {
          renderFloorMarkers(floor);
        });

        // Aggiorna la lista degli artisti per ogni piano
        updateArtistList();
      }
    } catch (error) {
      console.error('Errore caricamento opere:', error);
    } finally {
      isLoading = false;
    }
  };

  const renderFloorMarkers = (floor) => {
    const container = document.querySelector(`.artwork-markers[data-floor="${CSS.escape(floor)}"]`);

    if (!container) return;

    const artworks = artworksByFloor[floor] || [];

    // Rimuovi marker esistenti
    container.innerHTML = '';

    artworks.forEach((artwork, index) => {
      const marker = createMarker(artwork, index, floor);
      container.appendChild(marker);
    });
  };

  const createMarker = (artwork, index, floor) => {
    const marker = document.createElement('button');
    marker.className = 'artwork-marker';
    marker.style.left = `${artwork.position_x}%`;
    marker.style.top = `${artwork.position_y}%`;
    marker.setAttribute('aria-label', `${artwork.artwork_title} - ${artwork.artist_name}`);
    marker.dataset.artist = `${artwork.artist_name}`;
    marker.setAttribute('tabindex', currentFloor === floor ? '0' : '-1');

    marker.addEventListener('click', (e) => openModal(artwork, e.currentTarget));

    return marker;
  };

  const showFloor = (floor) => {
    if (floor === currentFloor) return;

    const prevFloor = currentFloor;
    currentFloor = floor;

    // Nascondi piano precedente
    elements.floorMaps.forEach(map => {
      const mapFloor = map.dataset.floor;

      if (mapFloor === prevFloor) {
        map.dataset.active = 'false';
        map.setAttribute('aria-hidden', 'true');
      } else if (mapFloor === currentFloor) {
        map.dataset.active = 'true';
        map.setAttribute('aria-hidden', 'false');
      }
    });

    // Aggiorna indicatore piano (se presente)
    if (elements.currentFloorNumber) {
      // Trova il nome del floor dalla lista
      const floorItem = document.querySelector(`.exhibition-floor[data-floor="${CSS.escape(floor)}"]`);
      if (floorItem) {
        const floorTitle = floorItem.querySelector('h3');
        if (floorTitle) {
          elements.currentFloorNumber.textContent = floorTitle.textContent;
        }
      }
    }

    // Aggiorna tabindex dei marker
    updateMarkersTabindex();

    // Sincronizza accordion con il piano corrente
    syncAccordionWithFloor(floor);
  };

  const updateMarkersTabindex = () => {
    document.querySelectorAll('.artwork-marker').forEach(marker => {
      const markerContainer = marker.closest('.artwork-markers');
      const markerFloor = markerContainer ? markerContainer.dataset.floor : null;

      marker.setAttribute('tabindex', markerFloor === currentFloor ? '0' : '-1');
    });
  };

  const updateArtistList = () => {
    const floorItems = document.querySelectorAll('.exhibition-floor');

    floorItems.forEach(floorItem => {
      const floor = floorItem.dataset.floor;
      const artworks = artworksByFloor[floor] || [];
      const floorContent = floorItem.querySelector('.floor-content');

      if (!floorContent) return;

      // Rimuovi lista artisti esistente se presente
      const existingList = floorContent.querySelector('.artists-list');
      if (existingList) {
        existingList.remove();
      }

      // Estrai artisti unici dal piano corrente
      const uniqueArtists = [...new Set(artworks.map(artwork => artwork.artist_name))].filter(Boolean);

      // Crea lista artisti solo se ci sono artisti
      if (uniqueArtists.length > 0) {
        const artistsList = document.createElement('ul');
        artistsList.className = 'artists-list';

        uniqueArtists.forEach(artistName => {
          const artistItem = document.createElement('li');
          artistItem.className = 'artist-item';
          artistItem.textContent = artistName;
          artistItem.dataset.artist = artistName;
          artistItem.dataset.floor = floor;
          
          // Event listeners per evidenziare marker
          artistItem.addEventListener('mouseenter', () => {
            removeMarkerHighlight();
            artistItem.classList.add('current');
            highlightMarkersByArtist(artistName, floor);
          });

          artistItem.addEventListener('click', () => {
            // When an artist item is clicked, highlight relevant markers and animate them with a pulse effect
            removeMarkerHighlight();
            artistItem.classList.add('current');
            highlightMarkersByArtist(artistName, floor);

            // Add pulse animation to all current markers for this artist/floor
            const markersContainer = document.querySelector(`.artwork-markers[data-floor="${CSS.escape(floor)}"]`);
            if (markersContainer) {
              markersContainer.querySelectorAll(
                `.artwork-marker.current[data-artist="${CSS.escape(artistName)}"]`
              ).forEach(marker => {
                marker.classList.remove('pulse-anim'); // Reset in case
                // Trigger reflow to restart animation if needed
                void marker.offsetWidth;
                marker.classList.add('pulse-anim');
              });
            }
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          });
          
          // artistItem.addEventListener('mouseleave', () => {
          //   removeMarkerHighlight();
          // });
          
          artistsList.appendChild(artistItem);
        });

        floorContent.appendChild(artistsList);
      }
    });
  };

  const initAccordion = () => {
    const floorItems = document.querySelectorAll('.exhibition-floor');

    floorItems.forEach(item => {
      const toggle = item.querySelector('.floor-toggle');
      const content = item.querySelector('.floor-content');
      const isExpanded = item.dataset.expanded === 'true';

      if (isExpanded) {
        item.classList.add('is-expanded');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'true');
        }
        if (content) {
          content.setAttribute('aria-hidden', 'false');
        }
      } else {
        item.classList.remove('is-expanded');
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
        if (content) {
          content.setAttribute('aria-hidden', 'true');
        }
      }
    });
  };

  const syncAccordionWithFloor = (floor) => {
    const floorItems = document.querySelectorAll('.exhibition-floor');

    floorItems.forEach(item => {
      const itemFloor = item.dataset.floor;
      const toggle = item.querySelector('.floor-toggle');
      const content = item.querySelector('.floor-content');
      const isTargetFloor = itemFloor === floor;

      if (isTargetFloor) {
        // Apri il piano corrente
        item.dataset.expanded = 'true';
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'true');
        }
        if (content) {
          content.setAttribute('aria-hidden', 'false');
        }
        item.classList.add('is-expanded');
      } else {
        // Chiudi gli altri piani
        item.dataset.expanded = 'false';
        if (toggle) {
          toggle.setAttribute('aria-expanded', 'false');
        }
        if (content) {
          content.setAttribute('aria-hidden', 'true');
        }
        item.classList.remove('is-expanded');
      }
    });
  };

  const toggleFloorAccordion = (floor) => {
    // Sincronizza accordion
    syncAccordionWithFloor(floor);

    // Sincronizza con la mappa: mostra il piano selezionato
    showFloor(floor);
  };

  const highlightMarkersByArtist = (artistName, floor) => {
    // Trova tutti i marker del piano corrente con l'artista corrispondente
    const markers = document.querySelectorAll(`.artwork-marker[data-artist="${CSS.escape(artistName)}"]`);
    
    markers.forEach(marker => {
      // Verifica che il marker appartenga al piano corretto
      const markerContainer = marker.closest('.artwork-markers');
      if (markerContainer) {
        const markerFloor = markerContainer.dataset.floor;
        if (markerFloor === floor) {
          marker.classList.add('current');
        }
      }
    });
  };

  const removeMarkerHighlight = () => {
    // Rimuovi la classe current da tutti i marker
    const markers = document.querySelectorAll('.artwork-marker.current');
    markers.forEach(marker => {
      marker.classList.remove('current');
    });
    const artistItems = document.querySelectorAll('.artist-item.current');
    artistItems.forEach(artistItem => {
      artistItem.classList.remove('current');
    });
  };
  
  const openModal = (artwork, markerElement = null) => {
    if (!elements.modal) return;
    
    // Previeni l'apertura se la modale è in fase di chiusura
    if (isClosing) return;
    
    // Previeni l'apertura se la modale è già aperta
    if (elements.modal.getAttribute('aria-hidden') === 'false') return;

    // Popola il modal
    if (elements.modalTitle) {
      elements.modalTitle.textContent = artwork.artwork_title;
    }

    if (elements.modalArtistLink) {
      elements.modalArtistLink.textContent = artwork.artist_name;
      elements.modalArtistLink.href = artwork.artist_url;
    }

    if (elements.modalDescription) {
      elements.modalDescription.textContent = artwork.artwork_description || '';
    }

    // Mostra modal prima di posizionare (necessario per calcolare dimensioni)
    elements.modal.setAttribute('aria-hidden', 'false');
    elements.modal.classList.add('is-open');

    // Imposta opacity a 0 all'apertura
    if (elements.modalContent) {
      elements.modalContent.style.opacity = '0';
    }

    // Funzione per posizionare la modale
    const positionModal = () => {
      // Verifica che la modale sia ancora aperta e non in fase di chiusura
      if (isClosing || elements.modal.getAttribute('aria-hidden') === 'true') {
        return;
      }
      
      // Usa doppio requestAnimationFrame per assicurarsi che il rendering sia completo
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          // Verifica di nuovo prima di applicare gli stili
          if (isClosing || elements.modal.getAttribute('aria-hidden') === 'true') {
            return;
          }
          
          if (markerElement && window.innerWidth >= 1000) {
            positionModalNearMarker(markerElement);
          } else {
            // Reset posizione su mobile
            resetModalPosition();
          }
          
          // Imposta opacity a 1 dopo il posizionamento solo se la modale è ancora aperta
          if (!isClosing && elements.modal.getAttribute('aria-hidden') === 'false' && elements.modalContent) {
            elements.modalContent.style.opacity = '1';
          }
        });
      });
    };

    // Gestisci immagine
    if (elements.modalImage && elements.modalImageContainer) {
      if (artwork.image_url && artwork.image_url !== '') {
        // Rimuovi eventuali listener precedenti
        elements.modalImage.onload = null;
        elements.modalImage.onerror = null;
        
        // Aggiungi listener per quando l'immagine è caricata
        elements.modalImage.onload = () => {
          // Riposiziona la modale dopo che l'immagine è caricata
          positionModal();
        };
        
        // Se l'immagine fallisce il caricamento, posiziona comunque
        elements.modalImage.onerror = () => {
          positionModal();
        };
        
        elements.modalImage.src = artwork.image_url;
        elements.modalImage.alt = artwork.artwork_title || '';
        elements.modalImageContainer.style.display = '';
        
        // Se l'immagine è già in cache, onload potrebbe non scattare
        // Verifica se è già caricata
        if (elements.modalImage.complete && elements.modalImage.naturalHeight !== 0) {
          positionModal();
        }
      } else {
        elements.modalImage.src = '';
        elements.modalImage.alt = '';
        elements.modalImageContainer.style.display = 'none';
        // Posiziona dopo un breve delay per assicurarsi che la modale sia renderizzata
        positionTimeout = setTimeout(() => {
          positionModal();
        }, 50);
      }
    } else {
      // Posiziona dopo un breve delay per assicurarsi che la modale sia renderizzata
      positionTimeout = setTimeout(() => {
        positionModal();
      }, 50);
    }

    // Focus sul modal content
    setTimeout(() => {
      if (elements.modalContent) {
        elements.modalContent.focus();
      }
    }, 100);

    // Blocca scroll body
    document.body.style.overflow = 'hidden';
  };

  const positionModalNearMarker = (markerElement) => {
    if (!elements.modalContent || !markerElement) return;
    
    // Previeni il posizionamento se la modale è in fase di chiusura o già chiusa
    if (isClosing || elements.modal.getAttribute('aria-hidden') === 'true') {
      return;
    }

    const markerRect = markerElement.getBoundingClientRect();
    const modalContent = elements.modalContent;
    
    // Usa offsetWidth/offsetHeight per ottenere dimensioni anche se la modale è appena stata mostrata
    const modalWidth = modalContent.offsetWidth || modalContent.getBoundingClientRect().width;
    const modalHeight = modalContent.offsetHeight || modalContent.getBoundingClientRect().height;
    
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    // Calcola posizione del marker (centro del marker)
    const markerX = markerRect.left + markerRect.width / 2;
    const markerY = markerRect.top + markerRect.height / 2;

    // Offset per posizionare la modale vicino al marker
    const offsetX = 20;
    const offsetY = 20;

    // Calcola spazio disponibile a destra e a sinistra del marker
    const spaceRight = viewportWidth - markerX - offsetX;
    const spaceLeft = markerX - offsetX;
    
    // Determina posizione orizzontale
    let left;
    if (spaceRight >= modalWidth) {
      // C'è spazio a destra: posiziona a destra del marker
      left = markerX + offsetX;
    } else if (spaceLeft >= modalWidth) {
      // C'è spazio a sinistra: posiziona a sinistra del marker
      left = markerX - modalWidth - offsetX;
    } else {
      // Non c'è spazio sufficiente: posiziona il più vicino possibile al marker
      // Preferisci destra se c'è più spazio a destra
      if (spaceRight > spaceLeft) {
        left = viewportWidth - modalWidth;
      } else {
        left = 0;
      }
    }

    // Assicura che la modale non esca dal viewport orizzontalmente
    left = Math.max(0, Math.min(left, viewportWidth - modalWidth));

    // Calcola spazio disponibile sopra e sotto il marker
    const spaceAbove = markerY - offsetY;
    const spaceBelow = viewportHeight - markerY - offsetY;
    
    // Determina posizione verticale
    let top;
    if (spaceBelow >= modalHeight / 2 && spaceAbove >= modalHeight / 2) {
      // C'è spazio sopra e sotto: allinea il centro della modale con il marker
      top = markerY - modalHeight / 2;
    } else if (spaceBelow >= modalHeight) {
      // C'è spazio sotto: allinea il top della modale con il marker (con offset)
      top = markerY + offsetY;
    } else if (spaceAbove >= modalHeight) {
      // C'è spazio sopra: allinea il bottom della modale con il marker (con offset)
      top = markerY - modalHeight - offsetY;
    } else {
      // Non c'è spazio sufficiente: posiziona il più vicino possibile al marker
      // Preferisci sotto se c'è più spazio sotto
      if (spaceBelow > spaceAbove) {
        top = Math.max(0, viewportHeight - modalHeight);
      } else {
        top = 0;
      }
    }

    // Assicura che la modale non esca dal viewport verticalmente
    top = Math.max(0, Math.min(top, viewportHeight - modalHeight));

    // Applica posizione
    modalContent.style.position = 'absolute';
    modalContent.style.left = `${left}px`;
    modalContent.style.top = `${top}px`;
    modalContent.style.margin = '0';
    modalContent.style.transform = 'none';
  };

  const resetModalPosition = () => {
    if (!elements.modalContent) return;

    elements.modalContent.style.position = '';
    elements.modalContent.style.left = '';
    elements.modalContent.style.top = '';
    elements.modalContent.style.margin = '';
    elements.modalContent.style.transform = '';
    elements.modalContent.style.opacity = '';
  };

  const closeModal = () => {
    if (!elements.modal) return;
    
    // Previeni chiusure multiple
    if (isClosing) return;
    
    // Previeni la chiusura se la modale è già chiusa
    if (elements.modal.getAttribute('aria-hidden') === 'true') return;
    
    // Imposta flag di chiusura
    isClosing = true;

    // Cancella eventuali timeout di posizionamento in corso
    if (positionTimeout) {
      clearTimeout(positionTimeout);
      positionTimeout = null;
    }

    // Rimuovi listener sull'immagine per prevenire chiamate a positionModal
    if (elements.modalImage) {
      elements.modalImage.onload = null;
      elements.modalImage.onerror = null;
    }

    // Imposta opacity a 0 come prima cosa
    if (elements.modalContent) {
      elements.modalContent.style.opacity = '0';
    }

    // Aspetta che la transizione di opacity sia completata prima di rimuovere gli attributi
    setTimeout(() => {
      // Reset posizione modale e rimuovi tutti gli attributi inline
      resetModalPosition();
      
      elements.modal.setAttribute('aria-hidden', 'true');
      elements.modal.classList.remove('is-open');

      // Svuota immagine
      if (elements.modalImage) {
        elements.modalImage.src = '';
        elements.modalImage.alt = '';
      }
      
      // Svuota contenuti testuali
      if (elements.modalTitle) {
        elements.modalTitle.textContent = '';
      }
      if (elements.modalArtistLink) {
        elements.modalArtistLink.textContent = '';
        elements.modalArtistLink.href = '#';
      }
      if (elements.modalDescription) {
        elements.modalDescription.textContent = '';
      }
      
      // Ripristina scroll body
      document.body.style.overflow = '';
      
      // Reset flag di chiusura
      isClosing = false;
    }, 200); // Tempo della transizione CSS (0.2s)
  };

  // Inizializza quando il DOM è pronto
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return {
    init,
    showFloor,
    openModal,
    closeModal,
  };
})();

