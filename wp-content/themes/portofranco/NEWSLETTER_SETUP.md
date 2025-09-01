# Configurazione Newsletter - Contact Form 7

## Passi per la Configurazione

### 1. Installazione Plugin
1. Vai in **WordPress Admin → Plugin → Aggiungi nuovo**
2. Cerca "Contact Form 7" e installalo
3. Cerca "Flamingo" e installalo
4. Attiva entrambi i plugin

### 2. Creazione Form Newsletter
1. Vai in **Contact → Aggiungi nuovo**
2. Titolo: "Newsletter"
3. Sostituisci il contenuto con questo codice:

```html
<div class="newsletter-form">
    <div class="form-group">
        [email* newsletter-email placeholder "La tua email"]
        [hidden language default:get "lang"]
    </div>
    [submit "Iscriviti"]
</div>
```

4. **Messaggio di successo** (IT):
```
Grazie per esserti iscritto alla newsletter!
```

5. **Messaggio di successo** (EN):
```
Thank you for subscribing to our newsletter!
```

6. **Messaggio di errore** (IT):
```
Si è verificato un errore. Riprova.
```

7. **Messaggio di errore** (EN):
```
An error occurred. Please try again.
```

8. Salva il form e copia l'ID (es: 123)

### 3. Aggiornamento Template
1. Apri `wp-content/themes/portofranco/about-end-content.php`
2. Sostituisci `NEWSLETTER_FORM_ID` con l'ID reale del form (riga 58)
3. Esempio: `[contact-form-7 id="123" title="Newsletter"]`

### 4. Configurazione Flamingo
1. Vai in **Flamingo → Incoming Messages**
2. Qui vedrai tutte le submission del form
3. Puoi esportare i dati in CSV
4. Filtrare per data, email, ecc.

### 5. Personalizzazione Messaggi Multilingua
Per supportare IT/EN, aggiungi questo codice nel form:

```html
<div class="newsletter-form">
    <div class="form-group">
        [email* newsletter-email placeholder "La tua email"]
        [hidden language default:get "lang"]
    </div>
    [submit "Iscriviti"]
</div>
```

**IMPORTANTE**: I messaggi vanno configurati manualmente nel pannello admin di Contact Form 7:

1. Vai nel tab "Messages"
2. Configura i messaggi per entrambe le lingue
3. Per messaggi condizionali, usa la sintassi:
   ```
   [if language equals "en"]Thank you for subscribing![else]Grazie per esserti iscritto![/if]
   ```

## Funzionalità Disponibili

### Backend (Flamingo):
- ✅ Visualizzazione tutte le submission
- ✅ Export CSV
- ✅ Filtri per data/email
- ✅ Statistiche
- ✅ Gestione spam

### Frontend:
- ✅ Validazione email automatica
- ✅ Messaggi multilingua
- ✅ Design responsive
- ✅ Anti-spam integrato

## Note Importanti

1. **ID Form**: Sostituisci sempre `NEWSLETTER_FORM_ID` con l'ID reale
2. **Lingua**: Il campo `language` viene passato automaticamente dal template
3. **Stili**: Gli stili CSS sono già configurati nel tema
4. **Backup**: I dati vengono salvati nel database WordPress

## Troubleshooting

- Se il form non appare, verifica che Contact Form 7 sia attivo
- Se i messaggi non funzionano, controlla la configurazione del form
- Per problemi di stile, verifica che il CSS sia caricato correttamente
