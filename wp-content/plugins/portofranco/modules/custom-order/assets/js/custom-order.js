/**
 * Custom Order Manager JavaScript
 * Gestisce il drag & drop per l'ordinamento dei post
 */

(function($) {
    'use strict';
    
    const CustomOrderManager = {
        
        /**
         * Inizializza il manager
         */
        init: function() {
            this.container = $('#custom-order-container');
            this.sortableList = $('#sortable-list');
            this.statusElement = $('#order-status');
            this.postType = this.container.data('post-type');
            
            if (!this.container.length || !this.sortableList.length) {
                return;
            }
            
            this.initSortable();
            this.bindEvents();
        },
        
        /**
         * Inizializza il sortable
         */
        initSortable: function() {
            this.sortableList.sortable({
                handle: '.item-handle',
                placeholder: 'ui-sortable-placeholder',
                helper: function(e, item) {
                    // Crea un clone dell'elemento per il drag
                    const helper = item.clone();
                    helper.css({
                        'width': item.width(),
                        'height': item.height()
                    });
                    return helper;
                },
                start: function(e, ui) {
                    ui.item.addClass('dragging');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('dragging');
                    CustomOrderManager.saveOrder();
                }
            });
        },
        
        /**
         * Bind degli eventi
         */
        bindEvents: function() {
            // Eventi per feedback visivo
            this.sortableList.on('sortstart', function() {
                CustomOrderManager.showStatus('loading', pfCustomOrder.strings.saving);
            });
        },
        
        /**
         * Salva l'ordine corrente
         */
        saveOrder: function() {
            const order = [];
            
            this.sortableList.find('.sortable-item').each(function(index) {
                order.push($(this).data('post-id'));
            });
            
            $.ajax({
                url: pfCustomOrder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'save_custom_order',
                    nonce: pfCustomOrder.nonce,
                    post_type: this.postType,
                    order: order
                },
                success: function(response) {
                    if (response.success) {
                        CustomOrderManager.showStatus('success', response.data);
                    } else {
                        CustomOrderManager.showStatus('error', response.data);
                    }
                },
                error: function() {
                    CustomOrderManager.showStatus('error', pfCustomOrder.strings.error);
                }
            });
        },
        
        /**
         * Mostra lo stato del salvataggio
         */
        showStatus: function(type, message) {
            this.statusElement
                .removeClass('success error loading')
                .addClass(type)
                .text(message)
                .show();
            
            // Nascondi il messaggio dopo 3 secondi
            setTimeout(() => {
                this.statusElement.fadeOut();
            }, 3000);
        }
    };
    
    // Inizializza quando il DOM è pronto
    $(document).ready(function() {
        CustomOrderManager.init();
    });
    
})(jQuery);
