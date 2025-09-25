# 📧 Istruzioni Modulo Newsletter Portofranco

## 🚀 **Attivazione**

1. **Vai in WordPress Admin** → **Plugin**
2. **Attiva il plugin "Portofranco"** (se non già attivo)
3. Il modulo newsletter si attiverà automaticamente

## 📊 **Accesso al Pannello**

1. **Vai in Strumenti** → **Newsletter**
2. Qui troverai:
   - **Statistiche** delle iscrizioni
   - **Lista completa** degli iscritti
   - **Funzioni di esportazione**

## 📤 **Esportazione Dati**

### **CSV Generico**
- Clicca **"Esporta CSV"**
- File compatibile con Excel, Google Sheets, ecc.

### **Mailchimp**
- Clicca **"Esporta per Mailchimp"**
- Formato ottimizzato per import diretto
- Include: Email, Nome, Cognome, Telefono, Lingua, Data iscrizione

### **MailPoet**
- Clicca **"Esporta per MailPoet"**
- Formato compatibile con il plugin MailPoet
- Include: Email, Nome, Cognome, Telefono, Lingua, Data creazione

## 🔧 **Gestione Iscrizioni**

### **Visualizzazione**
- **Tabella completa** con tutti i dati
- **Filtri** per stato e data
- **Ricerca** per email o nome

### **Modifica**
- Clicca **"Modifica"** su una riga
- **Modifica** email, nome, stato, note
- **Salva** le modifiche

### **Eliminazione**
- Clicca **"Elimina"** su una riga
- **Conferma** l'eliminazione

## 🌐 **Integrazione Frontend**

Il modulo si integra **automaticamente** con:

### **Contact Form 7**
- ✅ **Intercetta** l'invio del form
- ✅ **Salva** nel database
- ✅ **Mostra** messaggi di feedback
- ✅ **Previene** duplicati

### **Form HTML Nativi**
- ✅ **Gestisce** il fallback
- ✅ **Validazione** email
- ✅ **Messaggi** di successo/errore

## 📱 **Funzionalità Avanzate**

### **Tracciamento**
- **IP Address** dell'utente
- **User Agent** del browser
- **Data e ora** di iscrizione
- **Fonte** dell'iscrizione

### **Sicurezza**
- **Nonce verification** per tutte le richieste
- **Capability check** per operazioni admin
- **Sanitizzazione** di tutti gli input
- **Prevenzione SQL injection**

### **Multilingua**
- **Supporto IT/EN** automatico
- **Messaggi** localizzati
- **Form** multilingua

## 🎯 **Utilizzo Pratico**

### **Scenario 1: Esportare per Mailchimp**
1. Vai in **Strumenti** → **Newsletter**
2. Clicca **"Esporta per Mailchimp"**
3. **Scarica** il file CSV
4. Vai su **Mailchimp** → **Audience** → **Import**
5. **Carica** il file scaricato
6. **Mappa** i campi (Email, First Name, Last Name)
7. **Importa** gli iscritti

### **Scenario 2: Esportare per MailPoet**
1. Vai in **Strumenti** → **Newsletter**
2. Clicca **"Esporta per MailPoet"**
3. **Scarica** il file CSV
4. Vai su **MailPoet** → **Subscribers** → **Import**
5. **Carica** il file scaricato
6. **Seleziona** la lista di destinazione
7. **Importa** gli iscritti

### **Scenario 3: Gestire Iscrizioni**
1. Vai in **Strumenti** → **Newsletter**
2. **Visualizza** tutte le iscrizioni
3. **Modifica** dati se necessario
4. **Elimina** iscrizioni non valide
5. **Esporta** quando necessario

## 🔍 **Risoluzione Problemi**

### **Il modulo non si attiva**
- ✅ Verifica che il plugin "Portofranco" sia attivo
- ✅ Controlla i log di errore WordPress
- ✅ Verifica i permessi dei file

### **Le iscrizioni non vengono salvate**
- ✅ Verifica che la tabella database sia stata creata
- ✅ Controlla la connessione al database
- ✅ Verifica i permessi di scrittura

### **L'esportazione non funziona**
- ✅ Verifica i permessi di scrittura
- ✅ Controlla la memoria PHP disponibile
- ✅ Verifica che ci siano iscrizioni da esportare

## 📞 **Supporto**

Per problemi tecnici o domande:
- **Contatta** il team di sviluppo
- **Invia** i log di errore
- **Specifica** la versione WordPress e PHP

---

**Versione**: 1.0.0  
**Compatibilità**: WordPress 5.0+, PHP 7.4+  
**Ultimo aggiornamento**: Settembre 2024
