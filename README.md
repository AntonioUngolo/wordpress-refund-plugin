# Product Refund — Recesso art. 54-bis

Soluzioni per il diritto di recesso online conforme all'**art. 54-bis del Codice del Consumo** (in vigore dal 19/06/2026), per diverse piattaforme e-commerce.

## Struttura del repository

| Cartella | Contenuto |
|---|---|
| [`wordpress/`](wordpress/) | Workspace della versione **WordPress / WooCommerce** (plugin + strumenti di sviluppo). |
| [`wordpress/product-refund/`](wordpress/product-refund/) | **Il plugin vero e proprio** — questa è la cartella da zippare e caricare su WordPress. |
| [`prestashop/productrefund/`](prestashop/productrefund/) | Modulo per **PrestaShop 8.x** (scaffold + logica `Core/` testata). Vedi il suo [README](prestashop/productrefund/README.md). |
| [`docs/`](docs/) | Specifica e piano di implementazione condivisi (analisi normativa + design). |

## Versione per WordPress

### Creare il pacchetto da caricare
Zippa la cartella `wordpress/product-refund/` (contiene solo i file runtime del plugin) e caricala da *Plugin → Aggiungi nuovo → Carica plugin*. Il pacchetto viene generato in `wordpress/product-refund.zip` (ignorato da git). Esempio da `wordpress/`:

```bash
zip -r product-refund.zip product-refund -x '*.DS_Store'
```

Per installazione, shortcode e changelog vedi [`wordpress/product-refund/readme.txt`](wordpress/product-refund/readme.txt).

### Sviluppo
Gli strumenti di sviluppo (Composer, PHPUnit, test, `vendor/`) stanno in `wordpress/` — **fuori** dal plugin, così non finiscono mai nel pacchetto caricato (incluso PHPUnit, che non va mai spedito su un sito live). Dalla cartella `wordpress/`:

```bash
composer install         # dipendenze di sviluppo (PHPUnit)
vendor/bin/phpunit        # test della logica pura (product-refund/includes/Core/)
```

La logica pura e testabile è isolata in `wordpress/product-refund/includes/Core/` (nessuna dipendenza da WordPress); il resto è il "collante" WordPress/WooCommerce.
