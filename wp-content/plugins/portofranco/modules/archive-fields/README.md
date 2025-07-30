# Archive Fields Manager

## Descrizione
Questo modulo gestisce i custom fields per le descrizioni degli archivi dei Custom Post Types.

## Funzionalità
- Aggiunge pagine di impostazioni per ogni CPT supportato
- Permette di inserire descrizioni personalizzate per gli archivi
- Editor WYSIWYG per la formattazione del testo
- Anteprima in tempo reale delle descrizioni

## CPT Supportati
- `artisti` - Archivio Artisti
- `agenda` - Archivio Agenda

## Utilizzo

### Nel Backend
1. Vai su **Artisti > Impostazioni Artisti** o **Agenda > Impostazioni Agenda**
2. Inserisci la descrizione desiderata nell'editor
3. Salva le impostazioni

### Nel Frontend
Usa la funzione helper nel tema:

```php
// Recupera la descrizione dell'archivio corrente
$description = portofranco_get_archive_description();

// Oppure specifica il post type
$description = portofranco_get_archive_description('artisti');

// Mostra la descrizione
if ($description) {
    echo wpautop($description);
}
```

## Struttura File
```
modules/archive-fields/
├── class-archive-fields-manager.php
└── README.md
```

## Hook e Filtri
- `admin_menu` - Aggiunge le pagine di impostazioni
- `admin_init` - Registra le impostazioni
- `admin_enqueue_scripts` - Carica l'editor WYSIWYG

## Opzioni Database
- `artisti_archive_description` - Descrizione archivio artisti
- `agenda_archive_description` - Descrizione archivio agenda
