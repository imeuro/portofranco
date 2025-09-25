/**
 * Newsletter Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Edit subscription modal
    $('.edit-subscription').on('click', function() {
        const subscriptionId = $(this).data('id');
        showEditModal(subscriptionId);
    });
    
    // Delete subscription
    $('.delete-subscription').on('click', function() {
        const subscriptionId = $(this).data('id');
        if (confirm('Sei sicuro di voler eliminare questa iscrizione?')) {
            deleteSubscription(subscriptionId);
        }
    });
    
    // Export for Mailchimp
    $('#export-mailchimp').on('click', function() {
        exportForMailchimp();
    });
    
    // Export for MailPoet
    $('#export-mailpoet').on('click', function() {
        exportForMailPoet();
    });
    
    // Close modal
    $(document).on('click', '.pf-modal-close, .pf-modal', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Save subscription changes
    $(document).on('click', '#save-subscription', function() {
        saveSubscription();
    });
    
    /**
     * Show edit modal
     */
    function showEditModal(subscriptionId) {
        // Get subscription data via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pf_get_subscription',
                subscription_id: subscriptionId,
                nonce: pf_newsletter_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    showModal('Modifica Iscrizione', getEditForm(data));
                } else {
                    alert('Errore nel caricamento dei dati');
                }
            },
            error: function() {
                alert('Errore di connessione');
            }
        });
    }
    
    /**
     * Get edit form HTML
     */
    function getEditForm(data) {
        return `
            <form id="edit-subscription-form">
                <input type="hidden" id="subscription-id" value="${data.id}">
                
                <div class="pf-form-group">
                    <label for="edit-email">Email:</label>
                    <input type="email" id="edit-email" value="${data.email}" required>
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-name">Nome:</label>
                    <input type="text" id="edit-name" value="${data.name}">
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-surname">Cognome:</label>
                    <input type="text" id="edit-surname" value="${data.surname}">
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-phone">Telefono:</label>
                    <input type="tel" id="edit-phone" value="${data.phone}">
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-language">Lingua:</label>
                    <select id="edit-language">
                        <option value="ITA" ${data.language === 'ITA' ? 'selected' : ''}>Italiano</option>
                        <option value="ENG" ${data.language === 'ENG' ? 'selected' : ''}>English</option>
                    </select>
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-status">Stato:</label>
                    <select id="edit-status">
                        <option value="active" ${data.status === 'active' ? 'selected' : ''}>Attivo</option>
                        <option value="inactive" ${data.status === 'inactive' ? 'selected' : ''}>Inattivo</option>
                        <option value="pending" ${data.status === 'pending' ? 'selected' : ''}>In attesa</option>
                    </select>
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-source">Fonte:</label>
                    <input type="text" id="edit-source" value="${data.source}">
                </div>
                
                <div class="pf-form-group">
                    <label for="edit-notes">Note:</label>
                    <textarea id="edit-notes">${data.notes || ''}</textarea>
                </div>
                
                <div class="pf-modal-footer">
                    <button type="button" class="button" onclick="closeModal()">Annulla</button>
                    <button type="button" class="button button-primary" id="save-subscription">Salva</button>
                </div>
            </form>
        `;
    }
    
    /**
     * Save subscription changes
     */
    function saveSubscription() {
        const subscriptionId = $('#subscription-id').val();
        const email = $('#edit-email').val();
        const name = $('#edit-name').val();
        const surname = $('#edit-surname').val();
        const phone = $('#edit-phone').val();
        const language = $('#edit-language').val();
        const status = $('#edit-status').val();
        const source = $('#edit-source').val();
        const notes = $('#edit-notes').val();
        
        if (!email) {
            alert('Email è obbligatoria');
            return;
        }
        
        $('#save-subscription').prop('disabled', true).html('<span class="pf-loading"></span>Salvataggio...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pf_update_subscription',
                subscription_id: subscriptionId,
                email: email,
                name: name,
                surname: surname,
                phone: phone,
                language: language,
                status: status,
                source: source,
                notes: notes,
                nonce: pf_newsletter_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    closeModal();
                    location.reload(); // Reload page to show changes
                } else {
                    alert('Errore nel salvataggio: ' + response.data.message);
                }
            },
            error: function() {
                alert('Errore di connessione');
            },
            complete: function() {
                $('#save-subscription').prop('disabled', false).html('Salva');
            }
        });
    }
    
    /**
     * Delete subscription
     */
    function deleteSubscription(subscriptionId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pf_delete_subscription',
                subscription_id: subscriptionId,
                nonce: pf_newsletter_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload page to show changes
                } else {
                    alert('Errore nell\'eliminazione: ' + response.data.message);
                }
            },
            error: function() {
                alert('Errore di connessione');
            }
        });
    }
    
    /**
     * Export for Mailchimp
     */
    function exportForMailchimp() {
        window.open(ajaxurl + '?action=pf_export_mailchimp&nonce=' + pf_newsletter_admin.nonce, '_blank');
    }
    
    /**
     * Export for MailPoet
     */
    function exportForMailPoet() {
        window.open(ajaxurl + '?action=pf_export_mailpoet&nonce=' + pf_newsletter_admin.nonce, '_blank');
    }
    
    /**
     * Show modal
     */
    function showModal(title, content) {
        const modalHtml = `
            <div class="pf-modal" id="pf-modal">
                <div class="pf-modal-content">
                    <div class="pf-modal-header">
                        <h2>${title}</h2>
                        <span class="pf-modal-close">&times;</span>
                    </div>
                    <div class="pf-modal-body">
                        ${content}
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
        $('#pf-modal').show();
    }
    
    /**
     * Close modal
     */
    function closeModal() {
        $('#pf-modal').remove();
    }
    
});

// Make closeModal globally available
function closeModal() {
    jQuery('#pf-modal').remove();
}
