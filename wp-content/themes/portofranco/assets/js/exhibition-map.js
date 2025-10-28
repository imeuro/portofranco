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
    navPrev: null,
    navNext: null,
    floorDots: null,
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
  };

  const cacheElements = () => {
    elements.floorMaps = document.querySelectorAll('.floor-map');
    elements.navPrev = document.querySelector('.nav-prev');
    elements.navNext = document.querySelector('.nav-next');
    elements.floorDots = document.querySelectorAll('.floor-dot');
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
    // Navigazione piani
    if (elements.navPrev) {
      elements.navPrev.addEventListener('click', () => navigateFloor(-1));
    }

    if (elements.navNext) {
      elements.navNext.addEventListener('click', () => navigateFloor(1));
    }

    // Floor dots
    elements.floorDots.forEach(dot => {
      dot.addEventListener('click', (e) => {
        const floor = parseInt(e.currentTarget.dataset.floor);
        showFloor(floor);
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

    // Navigazione con tastiera
    document.addEventListener('keydown', (e) => {
      if (elements.modal.getAttribute('aria-hidden') === 'true') {
        if (e.key === 'ArrowLeft') {
          navigateFloor(-1);
        } else if (e.key === 'ArrowRight') {
          navigateFloor(1);
        }
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
    marker.setAttribute('tabindex', currentFloor === parseInt(artwork.floor) ? '0' : '-1');

    marker.addEventListener('click', () => openModal(artwork));

    return marker;
  };

  const navigateFloor = (direction) => {
    const newFloor = currentFloor + direction;

    if (newFloor < 0 || newFloor > 3) return;

    showFloor(newFloor);
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
      elements.currentFloorNumber.textContent = currentFloor;
    }

    // Aggiorna pulsanti navigazione
    updateNavigationButtons();

    // Aggiorna floor dots
    updateFloorDots();

    // Aggiorna tabindex dei marker
    updateMarkersTabindex();
  };

  const updateNavigationButtons = () => {
    if (elements.navPrev) {
      elements.navPrev.disabled = currentFloor === 0;
    }

    if (elements.navNext) {
      elements.navNext.disabled = currentFloor === 3;
    }
  };

  const updateFloorDots = () => {
    elements.floorDots.forEach(dot => {
      const dotFloor = parseInt(dot.dataset.floor);

      if (dotFloor === currentFloor) {
        dot.setAttribute('aria-selected', 'true');
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.setAttribute('aria-selected', 'false');
        dot.removeAttribute('aria-current');
      }
    });
  };

  const updateMarkersTabindex = () => {
    document.querySelectorAll('.artwork-marker').forEach(marker => {
      const markerContainer = marker.closest('.artwork-markers');
      const markerFloor = parseInt(markerContainer.dataset.floor);

      marker.setAttribute('tabindex', markerFloor === currentFloor ? '0' : '-1');
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

