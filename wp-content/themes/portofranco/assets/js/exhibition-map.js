/**
 * Exhibition Map Frontend
 * Gestisce la navigazione tra i piani e il modal delle opere
 */

const ExhibitionMap = (() => {
  'use strict';

  let currentFloor = 0;
  let artworksByFloor = {};
  let isLoading = false;

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

    // Inizializza primo piano
    showFloor(0);
    
    // Inizializza accordion: espandi solo piano 0
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
  };

  const bindEvents = () => {
    // Accordion floor list
    const floorToggles = document.querySelectorAll('.floor-toggle');
    floorToggles.forEach(toggle => {
      toggle.addEventListener('click', (e) => {
        const floorItem = e.currentTarget.closest('.exhibition-floor');
        const floor = parseInt(floorItem.dataset.floor);
        toggleFloorAccordion(floor);
      });
    });

    // Modal
    if (elements.modalClose) {
      elements.modalClose.addEventListener('click', closeModal);
    }

    if (elements.modalOverlay) {
      elements.modalOverlay.addEventListener('click', closeModal);
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

        // Renderizza marker per tutti i piani
        for (let floor = 0; floor <= 3; floor++) {
          renderFloorMarkers(floor);
        }

        // Aggiorna la lista deigli artisti per ogni piano
        updateArtistList();
      }
    } catch (error) {
      console.error('Errore caricamento opere:', error);
    } finally {
      isLoading = false;
    }
  };

  const renderFloorMarkers = (floor) => {
    const container = document.querySelector(`.artwork-markers[data-floor="${floor}"]`);

    if (!container) return;

    const artworks = artworksByFloor[floor] || [];

    // Rimuovi marker esistenti
    container.innerHTML = '';

    artworks.forEach((artwork, index) => {
      const marker = createMarker(artwork, index);
      container.appendChild(marker);
    });
  };

  const createMarker = (artwork, index) => {
    const marker = document.createElement('button');
    marker.className = 'artwork-marker';
    marker.style.left = `${artwork.position_x}%`;
    marker.style.top = `${artwork.position_y}%`;
    marker.setAttribute('aria-label', `${artwork.artwork_title} - ${artwork.artist_name}`);
    marker.dataset.artist = `${artwork.artist_name}`;
    marker.setAttribute('tabindex', currentFloor === parseInt(artwork.floor) ? '0' : '-1');

    marker.addEventListener('click', () => openModal(artwork));

    return marker;
  };

  const showFloor = (floor) => {
    if (floor === currentFloor) return;

    const prevFloor = currentFloor;
    currentFloor = floor;

    // Nascondi piano precedente
    elements.floorMaps.forEach(map => {
      const mapFloor = parseInt(map.dataset.floor);

      if (mapFloor === prevFloor) {
        map.dataset.active = 'false';
        map.setAttribute('aria-hidden', 'true');
      } else if (mapFloor === currentFloor) {
        map.dataset.active = 'true';
        map.setAttribute('aria-hidden', 'false');
      }
    });

    // Aggiorna indicatore piano
    if (elements.currentFloorNumber) {
      elements.currentFloorNumber.textContent = currentFloor === 0 ? 'terra' : currentFloor;
    }

    // Aggiorna tabindex dei marker
    updateMarkersTabindex();

    // Sincronizza accordion con il piano corrente
    syncAccordionWithFloor(floor);
  };

  const updateMarkersTabindex = () => {
    document.querySelectorAll('.artwork-marker').forEach(marker => {
      const markerContainer = marker.closest('.artwork-markers');
      const markerFloor = parseInt(markerContainer.dataset.floor);

      marker.setAttribute('tabindex', markerFloor === currentFloor ? '0' : '-1');
    });
  };

  const updateArtistList = () => {
    const floorItems = document.querySelectorAll('.exhibition-floor');

    floorItems.forEach(floorItem => {
      const floor = parseInt(floorItem.dataset.floor);
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
      const itemFloor = parseInt(item.dataset.floor);
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
      const itemFloor = parseInt(item.dataset.floor);
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
        const markerFloor = parseInt(markerContainer.dataset.floor);
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
  
  const openModal = (artwork) => {
    if (!elements.modal) return;

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

    // Mostra modal
    elements.modal.setAttribute('aria-hidden', 'false');
    elements.modal.classList.add('is-open');

    // Focus sul modal content
    setTimeout(() => {
      if (elements.modalContent) {
        elements.modalContent.focus();
      }
    }, 100);

    // Blocca scroll body
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    if (!elements.modal) return;

    elements.modal.setAttribute('aria-hidden', 'true');
    elements.modal.classList.remove('is-open');

    // Ripristina scroll body
    document.body.style.overflow = '';
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

