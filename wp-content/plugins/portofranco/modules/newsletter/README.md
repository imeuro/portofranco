# Modulo Newsletter - Plugin Portofranco

## Descrizione
Questo modulo gestisce le iscrizioni alla newsletter del sito Portofranco, salvando i dati nel database WordPress e fornendo funzionalità di esportazione per piattaforme esterne.

## Funzionalità

### 🎯 **Salvataggio Iscrizioni**
- Salvataggio automatico nel database WordPress
- Validazione email lato client e server
- Prevenzione duplicati
- Tracciamento IP e User Agent
- Supporto multilingua (IT/EN)

### 📊 **Pannello Admin**
- Visualizzazione di tutte le iscrizioni
- Statistiche in tempo reale
- Modifica/eliminazione iscrizioni
- Filtri per stato e data

### 📤 **Esportazione**
- **CSV Generico**: Per qualsiasi piattaforma
- **Mailchimp**: Formato ottimizzato per import (include cognome, telefono e lingua)
- **MailPoet**: Formato compatibile (include cognome, telefono e lingua)
- **Export automatico** con timestamp

### 🔧 **Integrazione**
- Compatibile con Contact Form 7 esistente
- Fallback per form HTML nativi
- AJAX per esperienza utente fluida
- Messaggi di feedback personalizzati

## Struttura Database

```sql
CREATE TABLE wp_pf_newsletter_subscriptions (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    email varchar(100) NOT NULL,
    name varchar(100) DEFAULT '',
    surname varchar(100) DEFAULT '',
    phone varchar(20) DEFAULT '',
    language varchar(5) DEFAULT 'ITA',
    status varchar(20) DEFAULT 'active',
    source varchar(50) DEFAULT 'website',
    ip_address varchar(45) DEFAULT '',
    user_agent text DEFAULT '',
    subscribed_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes text DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    KEY status (status),
    KEY language (language),
    KEY subscribed_at (subscribed_at)
);
```

## Utilizzo

### 1. **Accesso Admin**
Vai in **Strumenti → Newsletter** per accedere al pannello di gestione.

### 2. **Esportazione Dati**
- **CSV Generico**: Clicca "Esporta CSV"
- **Mailchimp**: Clicca "Esporta per Mailchimp"
- **MailPoet**: Clicca "Esporta per MailPoet"

### 3. **Gestione Iscrizioni**
- Visualizza tutte le iscrizioni nella tabella
- Clicca "Modifica" per modificare un'iscrizione
- Clicca "Elimina" per rimuovere un'iscrizione

## Integrazione con Form Esistenti

Il modulo si integra automaticamente con:
- **Contact Form 7**: Intercetta l'invio del form
- **Form HTML nativi**: Gestisce il fallback
- **Multilingua**: Supporta IT/EN automaticamente

## Hook e Filtri

### Azioni Disponibili
```php
// Dopo iscrizione completata
do_action('pf_newsletter_subscribed', $email, $name, $source);

// Prima dell'invio email di conferma
do_action('pf_newsletter_before_confirmation_email', $email, $name);
```

### Filtri Disponibili
```php
// Personalizza messaggio di conferma
add_filter('pf_newsletter_confirmation_message', function($message, $name) {
    return "Ciao {$name}, benvenuto nella nostra newsletter!";
}, 10, 2);

// Personalizza email di conferma
add_filter('pf_newsletter_confirmation_subject', function($subject) {
    return "Benvenuto in Portofranco!";
});
```

## Sicurezza

- **Nonce verification** per tutte le richieste AJAX
- **Capability check** per operazioni admin
- **Sanitizzazione** di tutti gli input
- **Validazione email** lato server
- **Prevenzione SQL injection** con prepared statements

## Compatibilità

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **MySQL**: 5.6+
- **Plugin**: Contact Form 7, Polylang

## Supporto

Per supporto tecnico o segnalazione bug, contatta il team di sviluppo.

---

**Versione**: 1.0.0  
**Autore**: Meuro  
**Licenza**: GPL v2+
