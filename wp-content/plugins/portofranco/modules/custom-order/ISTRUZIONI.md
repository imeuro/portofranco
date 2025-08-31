# Istruzioni per l'Utilizzo del Sistema di Ordinamento Personalizzato

## 🎯 Obiettivo
Questo modulo permette di ordinare i post di tipo 'artisti' secondo un criterio personalizzato deciso dall'Editor, tramite un'interfaccia drag & drop intuitiva.

## 🚀 Come Iniziare

### 1. Accesso alla Pagina di Ordinamento
1. Accedi al pannello di amministrazione WordPress
2. Nel menu laterale, vai su **Artisti**
3. Clicca su **Ordina Artisti** nel sottomenu

### 2. Utilizzo del Drag & Drop
1. **Visualizza la lista**: Vedrai tutti i post 'artisti' pubblicati in una lista
2. **Trascina per riordinare**: 
   - Clicca e tieni premuto sull'icona ⚡ (handle) accanto al titolo
   - Trascina l'elemento nella posizione desiderata
   - Rilascia per confermare la nuova posizione
3. **Salvataggio automatico**: L'ordine viene salvato automaticamente quando rilasci un elemento
4. **Feedback visivo**: Vedrai messaggi di conferma durante il salvataggio

### 3. Ordinamento Manuale (Alternativa)
Se preferisci impostare l'ordine manualmente:
1. Modifica qualsiasi post artista
2. Nella sidebar destra, trova il meta box **"Ordine Personalizzato"**
3. Inserisci un numero per la posizione:
   - `1` = primo posto
   - `2` = secondo posto
   - `3` = terzo posto
   - ecc.
4. Lascia vuoto per utilizzare l'ordine alfabetico
5. Salva il post

## 📱 Funzionalità Avanzate

### Responsive Design
- L'interfaccia funziona su desktop, tablet e mobile
- Su dispositivi mobili, l'interfaccia si adatta automaticamente

### Fallback Intelligente
- Se non è impostato un ordine personalizzato, viene utilizzato l'ordine alfabetico
- I post con ordine personalizzato appaiono sempre prima di quelli senza

### Compatibilità
- Funziona perfettamente con il sistema di caricamento dinamico esistente
- Non interferisce con altre funzionalità del sito

## 🔧 Test e Verifica

### Test del Sistema
Per verificare che tutto funzioni correttamente:
1. Aggiungi `?test_custom_order=1` all'URL del pannello di amministrazione
2. Vedrai un report dettagliato dello stato del sistema

### Verifica nel Frontend
1. Vai alla pagina degli artisti del sito
2. Verifica che gli artisti appaiano nell'ordine impostato
3. Se hai modificato l'ordine, potrebbe essere necessario svuotare la cache

## 🛠️ Risoluzione Problemi

### Problema: "Non riesco a trascinare gli elementi"
**Soluzione:**
- Assicurati di cliccare sull'icona ⚡ (handle) e non sul titolo
- Verifica che JavaScript sia abilitato nel browser
- Prova a ricaricare la pagina

### Problema: "L'ordine non viene salvato"
**Soluzione:**
- Verifica di avere i permessi di amministratore
- Controlla la console del browser per eventuali errori JavaScript
- Prova a utilizzare l'ordinamento manuale tramite meta box

### Problema: "Gli artisti non appaiono nell'ordine corretto nel frontend"
**Soluzione:**
- Svuota la cache del sito se utilizzata
- Verifica che il template `archive-artisti.php` sia aggiornato
- Controlla che non ci siano plugin che interferiscono con le query

## 📋 Note Importanti

### Sicurezza
- Solo gli utenti con permessi di amministratore possono modificare l'ordine
- Tutte le operazioni sono protette da nonce per prevenire attacchi CSRF

### Performance
- L'ordinamento viene salvato tramite AJAX per un'esperienza fluida
- Le query sono ottimizzate per le performance
- Il sistema utilizza meta query efficienti

### Backup
- L'ordine personalizzato è salvato nei meta dati dei post
- È sempre possibile tornare all'ordine alfabetico rimuovendo i valori personalizzati

## 🎨 Personalizzazione

### Aggiungere Altri Post Types
Per estendere il sistema ad altri post types:
1. Modifica l'array `$supported_post_types` nel file `class-custom-order-manager.php`
2. Aggiungi il nome del post type desiderato
3. Il sistema si adatterà automaticamente

### Modificare lo Stile
Per personalizzare l'aspetto dell'interfaccia:
1. Modifica il file `assets/css/custom-order.css`
2. I selettori CSS sono ben documentati e organizzati
3. Il design è mobile-first e responsive

## 📞 Supporto

Se hai problemi o domande:
1. Controlla prima questa documentazione
2. Esegui il test del sistema (`?test_custom_order=1`)
3. Verifica la console del browser per errori JavaScript
4. Controlla i log di WordPress per errori PHP

---

**Sviluppato per Portofranco**  
*Sistema di ordinamento personalizzato tramite drag & drop*
