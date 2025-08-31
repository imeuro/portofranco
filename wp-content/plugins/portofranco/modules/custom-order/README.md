# Custom Order Manager

## Descrizione
Questo modulo gestisce l'ordinamento personalizzato dei post tramite drag & drop nell'interfaccia di amministrazione.

## Funzionalità
- Interfaccia drag & drop per riordinare i post
- Salvataggio automatico dell'ordine tramite AJAX
- Meta box per impostare l'ordine manualmente
- Integrazione con le query del frontend
- Fallback all'ordine alfabetico se non è impostato un ordine personalizzato

## Post Types Supportati
- `artisti` - Post type Artisti

## Utilizzo

### Nel Backend

#### Pagina di Ordinamento Drag & Drop
1. Vai su **Artisti > Ordina Artisti** nel menu di amministrazione
2. Trascina gli elementi nella lista per riordinarli
3. L'ordine viene salvato automaticamente quando rilasci un elemento
4. Visualizza feedback visivo durante il salvataggio

#### Meta Box Individuale
1. Modifica qualsiasi post artista
2. Nella sidebar destra, trova il meta box "Ordine Personalizzato"
3. Inserisci un numero per la posizione (1 = primo, 2 = secondo, ecc.)
4. Lascia vuoto per utilizzare l'ordine alfabetico

### Nel Frontend
Gli artisti appariranno automaticamente nell'ordine personalizzato impostato dall'editor. Se non è stato impostato un ordine personalizzato, viene utilizzato l'ordine alfabetico.

## Struttura File
```
modules/custom-order/
├── class-custom-order-manager.php
├── assets/
│   ├── css/
│   │   └── custom-order.css
│   └── js/
│       └── custom-order.js
└── README.md
```

## Hook e Filtri
- `admin_menu` - Aggiunge le pagine di ordinamento
- `admin_enqueue_scripts` - Carica CSS e JS per l'interfaccia
- `wp_ajax_save_custom_order` - Gestisce il salvataggio AJAX
- `pre_get_posts` - Modifica le query per utilizzare l'ordine personalizzato
- `add_meta_boxes` - Aggiunge meta box per ordine manuale
- `save_post` - Salva l'ordine dal meta box

## Opzioni Database
- `_custom_order` - Meta field che contiene la posizione nell'ordinamento

## Compatibilità
- Compatibile con il sistema di caricamento dinamico esistente
- Non interferisce con altre funzionalità del tema
- Responsive design per dispositivi mobili

## Note Tecniche
- Utilizza jQuery UI Sortable per il drag & drop
- Salvataggio tramite AJAX per esperienza utente fluida
- Meta query ottimizzate per le performance
- Fallback graceful per post senza ordine personalizzato
