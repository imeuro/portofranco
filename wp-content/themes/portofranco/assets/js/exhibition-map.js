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
  let carouselInterval = null;
  let currentCarouselIndex = 0;

  const elements = {
    floorMaps: null,
    currentFloorNumber: null,
    floorMapDescription: null,
    floorMapDescriptionContent: null,
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
    modalCarousel: null,
    modalCarouselTrack: null,
    modalCarouselPrev: null,
    modalCarouselNext: null,
    modalCarouselIndicators: null,
  };

  /**
   * Converte i newline (\r\n, \n) in <br /> preservando gli a capo
   * @param {string} text - Testo da convertire
   * @returns {string} - Testo con newline convertiti in <br />
   */
  const convertNewlinesToBr = (text) => {
    if (!text) return '';
    // Escape HTML per sicurezza
    const div = document.createElement('div');
    div.textContent = text;
    const escaped = div.innerHTML;
    // Converti \r\n e \n in <br />
    return escaped.replace(/\r\n/g, '<br />').replace(/\n/g, '<br />');
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
      // Aggiorna anche la descrizione del primo piano
      updateFloorDescription(currentFloor);
    }
    
    // Inizializza accordion: espandi solo primo piano
    initAccordion();
  };

  const cacheElements = () => {
    elements.floorMaps = document.querySelectorAll('.floor-map');
    elements.currentFloorNumber = document.querySelector('.current-floor-number');
    elements.floorMapDescription = document.querySelector('.floor-map-description');
    elements.floorMapDescriptionContent = document.querySelector('.floor-map-description-content');
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
    elements.modalCarousel = document.querySelector('.modal-artwork-carousel');
    elements.modalCarouselTrack = document.querySelector('.modal-artwork-carousel-track');
    elements.modalCarouselPrev = document.querySelector('.modal-carousel-prev');
    elements.modalCarouselNext = document.querySelector('.modal-carousel-next');
    elements.modalCarouselIndicators = document.querySelector('.modal-carousel-indicators');
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

    // Carousel navigation
    if (elements.modalCarouselPrev) {
      elements.modalCarouselPrev.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        navigateCarousel(-1);
      });
    }

    if (elements.modalCarouselNext) {
      elements.modalCarouselNext.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        navigateCarousel(1);
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
    console.debug('artwork', artwork);
    console.debug('currentFloor', currentFloor);
    console.debug('floor', floor);

    marker.addEventListener('click', (e) => openModal(artwork, currentFloor, e.currentTarget));

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

    // Aggiorna descrizione del piano
    updateFloorDescription(floor);

    // Aggiorna tabindex dei marker
    updateMarkersTabindex();

    // Sincronizza accordion con il piano corrente
    syncAccordionWithFloor(floor);
  };

  const updateFloorDescription = (floor) => {
    if (!elements.floorMapDescription) return;

    // Verifica se esistono i testi descrittivi
    const descriptions = window.portofrancoFloorDescriptions;
    if (!descriptions || !descriptions[floor]) {
      return;
    }

    const description = descriptions[floor];
    const titleElement = elements.floorMapDescription.querySelector('h3');
    const contentElement = elements.floorMapDescription.querySelector('.floor-map-description-content');

    // Aggiorna il titolo
    if (titleElement && description.title) {
      titleElement.innerHTML = description.title;
    }

    // Aggiorna il contenuto
    if (contentElement && description.content) {
      contentElement.innerHTML = description.content;
    }
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
  
  const openModal = (artwork, currentFloor, markerElement = null) => {
    if (!elements.modal) return;
    
    // Previeni l'apertura se la modale è in fase di chiusura
    if (isClosing) return;
    
    // Previeni l'apertura se la modale è già aperta
    if (elements.modal.getAttribute('aria-hidden') === 'false') return;

    // Popola il modal
    elements.modalContent.dataset.floor = currentFloor;
    if (elements.modalTitle) {
      elements.modalTitle.textContent = artwork.artwork_title;
    }

    if (elements.modalArtistLink) {
      elements.modalArtistLink.textContent = artwork.artist_name;
      elements.modalArtistLink.href = artwork.artist_url;
    }

    if (elements.modalDescription) {
      const description = artwork.artwork_description || '';
      elements.modalDescription.innerHTML = convertNewlinesToBr(description);
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

    // Gestisci immagini - carousel se multiple, singola se una sola
    if (elements.modalCarouselTrack && elements.modalCarousel) {
      // Ferma eventuale carousel precedente
      stopCarousel();
      
      // Gestione retrocompatibilità: supporta sia array images che image_url singolo
      let images = [];
      if (artwork.images && Array.isArray(artwork.images) && artwork.images.length > 0) {
        images = artwork.images;
      } else if (artwork.image_url) {
        // Retrocompatibilità: supporta image_url singolo
        images = [{ image_url: artwork.image_url }];
      }
      
      // Pulisci il carousel
      elements.modalCarouselTrack.innerHTML = '';
      
      if (images.length > 0) {
        // Crea gli elementi del carousel
        images.forEach((img, index) => {
          if (img.image_url) {
            const carouselItem = document.createElement('div');
            carouselItem.className = 'modal-artwork-carousel-item' + (index === 0 ? ' active' : '');
            const imgElement = document.createElement('img');
            imgElement.src = img.image_url;
            imgElement.alt = artwork.artwork_title || '';
            imgElement.loading = 'lazy';
            
            // Listener per quando l'immagine è caricata (solo per la prima)
            if (index === 0) {
              imgElement.onload = () => {
                positionModal();
              };
              imgElement.onerror = () => {
                positionModal();
              };
              // Se l'immagine è già in cache
              if (imgElement.complete && imgElement.naturalHeight !== 0) {
                positionModal();
              }
            }
            
            carouselItem.appendChild(imgElement);
            elements.modalCarouselTrack.appendChild(carouselItem);
          }
        });
        
        // Mostra/nascondi controlli carousel in base al numero di immagini
        if (images.length > 1) {
          // Mostra controlli carousel
          if (elements.modalCarouselPrev) {
            elements.modalCarouselPrev.style.display = 'flex';
          }
          if (elements.modalCarouselNext) {
            elements.modalCarouselNext.style.display = 'flex';
          }
          if (elements.modalCarouselIndicators) {
            elements.modalCarouselIndicators.style.display = 'flex';
            createCarouselIndicators(images.length);
          }
          
          // Inizia il carousel automatico
          currentCarouselIndex = 0;
          startCarousel();
        } else {
          // Nascondi controlli carousel
          if (elements.modalCarouselPrev) {
            elements.modalCarouselPrev.style.display = 'none';
          }
          if (elements.modalCarouselNext) {
            elements.modalCarouselNext.style.display = 'none';
          }
          if (elements.modalCarouselIndicators) {
            elements.modalCarouselIndicators.style.display = 'none';
          }
        }
      } else {
        // Nessuna immagine
        if (elements.modalCarouselPrev) {
          elements.modalCarouselPrev.style.display = 'none';
        }
        if (elements.modalCarouselNext) {
          elements.modalCarouselNext.style.display = 'none';
        }
        if (elements.modalCarouselIndicators) {
          elements.modalCarouselIndicators.style.display = 'none';
        }
        positionTimeout = setTimeout(() => {
          positionModal();
        }, 50);
      }
    } else {
      // Fallback se il carousel non è disponibile
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

  const startCarousel = () => {
    stopCarousel(); // Assicurati che non ci siano altri interval attivi
    
    const items = elements.modalCarouselTrack ? elements.modalCarouselTrack.querySelectorAll('.modal-artwork-carousel-item') : [];
    if (items.length <= 1) return;
    
    // carouselInterval = setInterval(() => {
    //   navigateCarousel(1);
    // }, 5000); // Cambia ogni 5 secondi
  };

  const stopCarousel = () => {
    if (carouselInterval) {
      clearInterval(carouselInterval);
      carouselInterval = null;
    }
  };

  const navigateCarousel = (direction) => {
    const items = elements.modalCarouselTrack ? elements.modalCarouselTrack.querySelectorAll('.modal-artwork-carousel-item') : [];
    if (items.length <= 1) return;
    
    // Rimuovi classe active dall'elemento corrente
    items[currentCarouselIndex].classList.remove('active');
    
    // Calcola nuovo indice
    currentCarouselIndex += direction;
    if (currentCarouselIndex < 0) {
      currentCarouselIndex = items.length - 1;
    } else if (currentCarouselIndex >= items.length) {
      currentCarouselIndex = 0;
    }
    
    // Aggiungi classe active al nuovo elemento
    items[currentCarouselIndex].classList.add('active');
    
    // Aggiorna indicatori
    updateCarouselIndicators();
    
    // Riavvia il carousel automatico (resetta il timer)
    stopCarousel();
    startCarousel();
  };

  const createCarouselIndicators = (count) => {
    if (!elements.modalCarouselIndicators) return;
    
    elements.modalCarouselIndicators.innerHTML = '';
    
    for (let i = 0; i < count; i++) {
      const indicator = document.createElement('button');
      indicator.className = 'modal-carousel-indicator' + (i === 0 ? ' active' : '');
      indicator.setAttribute('aria-label', `Vai all'immagine ${i + 1}`);
      indicator.addEventListener('click', () => {
        const items = elements.modalCarouselTrack ? elements.modalCarouselTrack.querySelectorAll('.modal-artwork-carousel-item') : [];
        if (items.length > 0 && i !== currentCarouselIndex) {
          // Rimuovi classe active dall'elemento corrente
          items[currentCarouselIndex].classList.remove('active');
          currentCarouselIndex = i;
          items[currentCarouselIndex].classList.add('active');
          updateCarouselIndicators();
          stopCarousel();
          startCarousel();
        }
      });
      elements.modalCarouselIndicators.appendChild(indicator);
    }
  };

  const updateCarouselIndicators = () => {
    if (!elements.modalCarouselIndicators) return;
    
    const indicators = elements.modalCarouselIndicators.querySelectorAll('.modal-carousel-indicator');
    indicators.forEach((indicator, index) => {
      if (index === currentCarouselIndex) {
        indicator.classList.add('active');
      } else {
        indicator.classList.remove('active');
      }
    });
  };

  const closeModal = () => {
    if (!elements.modal) return;
    
    // Previeni chiusure multiple
    if (isClosing) return;
    
    // Previeni la chiusura se la modale è già chiusa
    if (elements.modal.getAttribute('aria-hidden') === 'true') return;
    
    // Imposta flag di chiusura
    isClosing = true;

    // Ferma il carousel
    stopCarousel();

    // Cancella eventuali timeout di posizionamento in corso
    if (positionTimeout) {
      clearTimeout(positionTimeout);
      positionTimeout = null;
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

      // Svuota carousel
      if (elements.modalCarouselTrack) {
        elements.modalCarouselTrack.innerHTML = '';
      }
      if (elements.modalCarouselIndicators) {
        elements.modalCarouselIndicators.innerHTML = '';
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
      currentCarouselIndex = 0;
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

