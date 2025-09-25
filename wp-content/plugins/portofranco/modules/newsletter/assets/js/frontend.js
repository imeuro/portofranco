/**
 * Newsletter Frontend JavaScript
 * Integra il form di iscrizione con il sistema di salvataggio
 * Vanilla JavaScript - No jQuery
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Ascolta l'evento submit di qualsiasi form che contenga un campo email
    document.addEventListener('submit', function(event) {
        const form = event.target;
        const emailField = form.querySelector('input[name="newsletter-email"]');
        
        // Verifica se è un form newsletter (contiene campo email)
        if (!emailField) {
            return; // Non è un form newsletter
        }
        
        console.debug('[NEWSLETTER] Form submit intercettato', form);
        
        // Salva i valori dei campi PRIMA che Contact Form 7 li resetti
        const nameField = form.querySelector('input[name="newsletter-name"]');
        const surnameField = form.querySelector('input[name="newsletter-surname"]');
        const phoneField = form.querySelector('input[name="newsletter-phone"]');
        const languageField = form.querySelector('input[name="language"]');
        
        // Estrai i valori SUBITO, prima che Contact Form 7 processi il form
        const email = emailField.value;
        const name = nameField ? nameField.value : '';
        const surname = surnameField ? surnameField.value : '';
        const phone = phoneField ? phoneField.value : '';
        const language = languageField ? languageField.value : 'ITA';
        const source = 'website_form';
        
        console.debug('[NEWSLETTER] Dati estratti SUBITO:', {email, name, surname, phone, language, source});
        
        // Verifica che l'email non sia vuota
        if (!email || email.trim() === '') {
            console.debug('[NEWSLETTER] Email vuota, salto il salvataggio');
            return;
        }
        
        // Aspetta che Contact Form 7 processi il form
        setTimeout(function() {
            console.debug('[NEWSLETTER] Verifico validazione dopo submit');
            
            // Verifica se il form è stato validato correttamente
            const responseOutput = form.querySelector('.wpcf7-response-output');
            console.debug('[NEWSLETTER] Response output:', responseOutput);
            console.debug('[NEWSLETTER] Classi response:', responseOutput ? responseOutput.className : 'null');
            
            // Controlla se ci sono errori di validazione
            if (responseOutput && (responseOutput.classList.contains('wpcf7-validation-errors') || responseOutput.classList.contains('wpcf7-spam-blocked'))) {
                console.debug('[NEWSLETTER] Form non validato, salto il salvataggio');
                return;
            }
            
            // Se non ci sono errori di validazione, procedi con il salvataggio
            console.debug('[NEWSLETTER] Form validato (nessun errore), procedo con il salvataggio');
            
            // Salva i dati nel nostro database
            const formData = new FormData();
            formData.append('action', 'pf_newsletter_subscribe');
            formData.append('email', email);
            formData.append('name', name);
            formData.append('surname', surname);
            formData.append('phone', phone);
            formData.append('language', language);
            formData.append('source', source);
            formData.append('nonce', pf_newsletter.nonce);
            
            fetch(pf_newsletter.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.debug('[NEWSLETTER] Risposta AJAX:', data);
                if (data.success) {
                    // Aggiungi un messaggio personalizzato dopo la validazione
                    setTimeout(function() {
                        showMessage('Iscrizione completata con successo.', 'success');
                    }, 500);
                } else {
                    // Mostra errore se il salvataggio nel database fallisce
                    setTimeout(function() {
                        showMessage('Errore nel salvataggio dati: ' + data.data.message, 'error');
                    }, 500);
                }
            })
            .catch(error => {
                console.debug('[NEWSLETTER] Errore AJAX:', error);
                setTimeout(function() {
                    showMessage('Errore di connessione al server.', 'error');
                }, 500);
            });
        }, 1000); // Aspetta 1 secondo per permettere a Contact Form 7 di processare
    });
    
    // Gestione form fallback (senza Contact Form 7) - Vanilla JS
    document.addEventListener('submit', function(event) {
        const form = event.target;
        
        // Verifica se è un form newsletter fallback (non Contact Form 7)
        if (!form.classList.contains('newsletter-form') || form.classList.contains('wpcf7-form')) {
            return;
        }
        
        event.preventDefault();
        
        const email = form.querySelector('input[type="email"]').value;
        const name = form.querySelector('input[name="name"]')?.value || '';
        const surname = form.querySelector('input[name="surname"]')?.value || '';
        const phone = form.querySelector('input[name="phone"], input[type="tel"]')?.value || '';
        const language = form.querySelector('input[name="language"], select[name="language"]')?.value || 'ITA';
        const source = 'website_form';
        
        if (!isValidEmail(email)) {
            showMessage('Email non valida', 'error');
            return;
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Invio...';
        
        const formData = new FormData();
        formData.append('action', 'pf_newsletter_subscribe');
        formData.append('email', email);
        formData.append('name', name);
        formData.append('surname', surname);
        formData.append('phone', phone);
        formData.append('language', language);
        formData.append('source', source);
        formData.append('nonce', pf_newsletter.nonce);
        
        fetch(pf_newsletter.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.data.message, 'success');
                form.reset();
            } else {
                showMessage(data.data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Errore di connessione. Riprova.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    });
    
    /**
     * Validazione email
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Mostra messaggio di feedback
     */
    function showMessage(message, type) {
        // Rimuovi messaggi esistenti
        const existingMessages = document.querySelectorAll('.newsletter-message');
        existingMessages.forEach(msg => msg.remove());
        
        const messageClass = type === 'success' ? 'success' : 'error';
        const messageHtml = `
            <div class="newsletter-message ${messageClass}">
                ${message}
            </div>
        `;
        
        // Inserisci messaggio dopo il form
        const forms = document.querySelectorAll('.newsletter-form, .wpcf7-form');
        if (forms.length > 0) {
            forms[0].insertAdjacentHTML('afterend', messageHtml);
        }
        
        // Auto-rimuovi dopo 5 secondi
        setTimeout(function() {
            const messageElement = document.querySelector('.newsletter-message');
            if (messageElement) {
                messageElement.style.opacity = '0';
                messageElement.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    messageElement.remove();
                }, 300);
            }
        }, 5000);
    }
    
});
