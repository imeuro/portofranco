/**
 * Exhibition Admin Map Positioner
 */
(function($) {
    'use strict';
    
    let artworkIndex = 0;
    
    const init = () => {
        // Set initial artwork index
        artworkIndex = $('.pf-artwork-item').length;
        
        // Add artwork button
        $('#pf-add-artwork').on('click', addArtwork);
        
        // Remove artwork buttons
        $(document).on('click', '.pf-remove-artwork', removeArtwork);
        
        // Floor select change
        $(document).on('change', '.pf-floor-select', handleFloorChange);
        
        // Map click for positioning
        $(document).on('click', '.pf-map-preview', handleMapClick);
        
        // Manual coordinate input
        $(document).on('input', '.pf-position-x, .pf-position-y', handleManualPosition);
        
        // Initialize existing maps
        $('.pf-map-preview').each(function() {
            const floor = $(this).data('floor');
            if (floor) {
                loadFloorMap($(this), floor);
            }
        });
    };
    
    const addArtwork = (e) => {
        e.preventDefault();
        
        const template = $('#pf-artwork-template').html();
        const newItem = template.replace(/\{\{INDEX\}\}/g, artworkIndex);
        
        $('#pf-artworks-list').append(newItem);
        
        // Update artwork number
        updateArtworkNumbers();
        
        artworkIndex++;
    };
    
    const removeArtwork = function(e) {
        e.preventDefault();
        
        if (confirm('Sei sicuro di voler rimuovere questa opera?')) {
            $(this).closest('.pf-artwork-item').slideUp(300, function() {
                $(this).remove();
                updateArtworkNumbers();
            });
        }
    };
    
    const handleFloorChange = function() {
        const floor = $(this).val();
        const $container = $(this).closest('.pf-artwork-item');
        const $mapPreview = $container.find('.pf-map-preview');
        
        if (floor) {
            loadFloorMap($mapPreview, floor);
            $mapPreview.data('floor', floor);
        } else {
            $mapPreview.html('<div class="pf-map-placeholder">Seleziona un piano per vedere la mappa</div>');
            $mapPreview.data('floor', '');
        }
        
        // Reset position
        $container.find('.pf-position-x').val('');
        $container.find('.pf-position-y').val('');
    };
    
    const loadFloorMap = ($mapPreview, floor) => {
        const mapUrl = pfExhibition.mapBaseUrl + 'piano-' + floor + '.jpg';
        
        // Check if image exists
        const img = new Image();
        img.onload = function() {
            $mapPreview.html('<img src="' + mapUrl + '" alt="Mappa Piano ' + floor + '">');
            
            // Restore marker if exists
            const $container = $mapPreview.closest('.pf-artwork-item');
            const posX = $container.find('.pf-position-x').val();
            const posY = $container.find('.pf-position-y').val();
            
            if (posX && posY) {
                updateMarker($mapPreview, posX, posY);
            }
        };
        img.onerror = function() {
            $mapPreview.html('<div class="pf-map-placeholder">Mappa Piano ' + floor + ' non trovata. Carica l\'immagine in /wp-content/uploads/exhibition-maps/piano-' + floor + '.jpg</div>');
        };
        img.src = mapUrl;
    };
    
    const handleMapClick = function(e) {
        // Don't handle click if clicking on marker
        if ($(e.target).hasClass('pf-marker')) {
            return;
        }
        
        const $mapPreview = $(this);
        const $img = $mapPreview.find('img');
        
        if ($img.length === 0) {
            return;
        }
        
        const rect = $img[0].getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const percentX = (x / rect.width * 100).toFixed(1);
        const percentY = (y / rect.height * 100).toFixed(1);
        
        // Update inputs
        const $container = $mapPreview.closest('.pf-artwork-item');
        $container.find('.pf-position-x').val(percentX);
        $container.find('.pf-position-y').val(percentY);
        
        // Update marker
        updateMarker($mapPreview, percentX, percentY);
    };
    
    const handleManualPosition = function() {
        const $container = $(this).closest('.pf-artwork-item');
        const $mapPreview = $container.find('.pf-map-preview');
        
        const posX = $container.find('.pf-position-x').val();
        const posY = $container.find('.pf-position-y').val();
        
        if (posX && posY) {
            updateMarker($mapPreview, posX, posY);
        }
    };
    
    const updateMarker = ($mapPreview, x, y) => {
        // Remove existing marker
        $mapPreview.find('.pf-marker').remove();
        
        // Add new marker
        const $marker = $('<div class="pf-marker"></div>');
        $marker.css({
            left: x + '%',
            top: y + '%'
        });
        
        $mapPreview.append($marker);
        
        // Make marker draggable
        makeMarkerDraggable($marker);
    };
    
    const makeMarkerDraggable = ($marker) => {
        let isDragging = false;
        
        $marker.on('mousedown', function(e) {
            e.preventDefault();
            isDragging = true;
            $(this).addClass('dragging');
        });
        
        $(document).on('mousemove', function(e) {
            if (!isDragging) return;
            
            const $mapPreview = $marker.closest('.pf-map-preview');
            const $img = $mapPreview.find('img');
            
            if ($img.length === 0) return;
            
            const rect = $img[0].getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Bounds checking
            if (x < 0 || x > rect.width || y < 0 || y > rect.height) return;
            
            const percentX = (x / rect.width * 100).toFixed(1);
            const percentY = (y / rect.height * 100).toFixed(1);
            
            // Update marker position
            $marker.css({
                left: percentX + '%',
                top: percentY + '%'
            });
            
            // Update inputs
            const $container = $mapPreview.closest('.pf-artwork-item');
            $container.find('.pf-position-x').val(percentX);
            $container.find('.pf-position-y').val(percentY);
        });
        
        $(document).on('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                $marker.removeClass('dragging');
            }
        });
    };
    
    const updateArtworkNumbers = () => {
        $('.pf-artwork-item').each(function(index) {
            $(this).find('.artwork-number').text(index + 1);
        });
    };
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);

