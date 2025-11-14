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
        
        // Image upload handlers
        $(document).on('click', '.pf-upload-image', handleImageUpload);
        
        // Initialize gallery drag & drop
        initGalleryDragDrop();
        
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
        
        const $newItem = $(newItem);
        $('#pf-artworks-list').append($newItem);
        
        // Initialize drag & drop for new gallery
        const $gallery = $newItem.find('.pf-images-gallery');
        if ($gallery.length) {
            $gallery.find('.pf-image-thumbnail').each(function() {
                initThumbnailDragDrop($(this));
            });
        }
        
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
        const mapUrl = pfExhibition.mapBaseUrl + floor + '.svg';
        
        // Check if image exists
        const img = new Image();
        img.onload = function() {
            const floorName = (floor === '0' || floor === 0) ? 'Piano terra' : 'Piano ' + floor;
            $mapPreview.html('<img src="' + mapUrl + '" alt="Mappa ' + floorName + '">');
            
            // Restore marker if exists
            const $container = $mapPreview.closest('.pf-artwork-item');
            const posX = $container.find('.pf-position-x').val();
            const posY = $container.find('.pf-position-y').val();
            
            if (posX && posY) {
                updateMarker($mapPreview, posX, posY);
            }
        };
        img.onerror = function() {
            const floorName = (floor === '0' || floor === 0) ? 'Piano terra' : 'Piano ' + floor;
            $mapPreview.html('<div class="pf-map-placeholder">Mappa ' + floorName + ' non trovata. Carica l\'immagine in /wp-content/plugins/portofranco/modules/exhibition/exhibition-maps/' + floor + '.svg</div>');
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
    
    const handleImageUpload = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $container = $button.closest('.pf-artwork-item');
        const $gallery = $container.find('.pf-images-gallery');
        const artworkIndex = $container.data('index');
        
        // Get existing image IDs to avoid duplicates
        const existingIds = [];
        $gallery.find('.pf-image-thumbnail').each(function() {
            const imgId = $(this).data('image-id');
            if (imgId) {
                existingIds.push(imgId);
            }
        });
        
        // Create media frame with multiple selection
        const mediaFrame = wp.media({
            title: 'Seleziona immagini opera',
            button: {
                text: 'Usa queste immagini'
            },
            multiple: true,
            library: {
                type: 'image'
            }
        });
        
        // Handle selection
        mediaFrame.on('select', function() {
            const selection = mediaFrame.state().get('selection');
            const attachments = selection.toJSON();
            
            attachments.forEach(function(attachment) {
                // Skip if already exists
                if (existingIds.indexOf(attachment.id) !== -1) {
                    return;
                }
                
                // Add to gallery
                addImageToGallery($gallery, attachment, artworkIndex);
            });
            
            // Update input indices
            updateImageInputIndices($gallery, artworkIndex);
        });
        
        // Open media frame
        mediaFrame.open();
    };
    
    const addImageToGallery = function($gallery, attachment, artworkIndex) {
        const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
        const imageId = attachment.id;
        
        const thumbnailHtml = `
            <div class="pf-image-thumbnail" data-image-id="${imageId}" draggable="true">
                <input type="hidden" name="pf_artworks[${artworkIndex}][image_ids][]" value="${imageId}">
                <img src="${imageUrl}" alt="${attachment.alt || ''}">
                <button type="button" class="button-link pf-remove-single-image" aria-label="Rimuovi immagine">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        `;
        
        $gallery.append(thumbnailHtml);
        
        // Initialize drag & drop for new thumbnail
        initThumbnailDragDrop($gallery.find('.pf-image-thumbnail').last());
    };
    
    const handleImageRemove = function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $thumbnail = $button.closest('.pf-image-thumbnail');
        const $gallery = $thumbnail.closest('.pf-images-gallery');
        const $container = $gallery.closest('.pf-artwork-item');
        const artworkIndex = $container.data('index');
        
        // Remove thumbnail
        $thumbnail.remove();
        
        // Update input indices
        updateImageInputIndices($gallery, artworkIndex);
    };
    
    const updateImageInputIndices = function($gallery, artworkIndex) {
        $gallery.find('.pf-image-thumbnail').each(function(index) {
            $(this).find('input[type="hidden"]').attr('name', `pf_artworks[${artworkIndex}][image_ids][${index}]`);
        });
    };
    
    const initThumbnailDragDrop = function($thumbnail) {
        $thumbnail.on('dragstart', function(e) {
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
        });
        
        $thumbnail.on('dragend', function() {
            $(this).removeClass('dragging');
        });
        
        $thumbnail.on('dragover', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('drag-over');
        });
        
        $thumbnail.on('dragleave', function() {
            $(this).removeClass('drag-over');
        });
        
        $thumbnail.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const $dragged = $('.pf-image-thumbnail.dragging');
            const $target = $(this);
            
            if ($dragged.length && $dragged[0] !== this) {
                const $gallery = $target.closest('.pf-images-gallery');
                const $container = $gallery.closest('.pf-artwork-item');
                const artworkIndex = $container.data('index');
                
                // Move element
                if ($dragged.index() < $target.index()) {
                    $target.after($dragged);
                } else {
                    $target.before($dragged);
                }
                
                // Update input indices
                updateImageInputIndices($gallery, artworkIndex);
            }
        });
    };
    
    const initGalleryDragDrop = function() {
        $(document).on('click', '.pf-remove-single-image', handleImageRemove);
        
        // Initialize drag & drop for existing thumbnails
        $('.pf-images-gallery .pf-image-thumbnail').each(function() {
            initThumbnailDragDrop($(this));
        });
    };
    
    // Initialize on document ready
    $(document).ready(init);
    
})(jQuery);

