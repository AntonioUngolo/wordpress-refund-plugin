=== Product Refund — Recesso art. 54-bis ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.5.1
License: GPL-2.0-or-later

Funzione di recesso online conforme all'art. 54-bis del Codice del Consumo
(in vigore 19/06/2026). Richiede WooCommerce attivo.

== Installazione ==
1. Plugin > Aggiungi nuovo > Carica plugin > seleziona product-refund.zip > Installa > Attiva.
   All'attivazione vengono create automaticamente due pagine pubblicate:
   "Richiesta di recesso" ([recesso_form]) e "Stato della richiesta di recesso" ([recesso_stato]).
2. Vai su Impostazioni > Permalink e premi "Salva" una volta (registra l'endpoint account "Rimborsi").
3. (Opzionale) Recessi > Impostazioni: regola giorni lavorativi stimati, email admin, e -
   se vuoi - cambia le pagine del form/stato. Inserisci [recesso_pulsante] dove vuoi il bottone.

== Shortcode ==
[recesso_form]      Form di recesso in 2 passaggi (pubblico).
[recesso_stato]     Pagina di stato della richiesta (accesso via link con token).
[recesso_pulsante]  Bottone che rimanda alla pagina del form.

== Changelog ==

= 0.5.1 =
* Corretto: lo stile configurato del pulsante ora si applica anche ai pulsanti di
  invio del form ("Continua", "Conferma invio"), non solo al pulsante [recesso_pulsante].

= 0.5.0 =
* Aggiunto: opzioni di stile per il pulsante [recesso_pulsante] nelle impostazioni —
  colore sfondo, colore testo, padding e border radius (applicati come stile inline).

= 0.4.0 =
* Aggiunto: checkbox obbligatoria di dichiarazione sulle condizioni del prodotto
  (integro, non utilizzato, sigillatura intatta) nel form di recesso. Testo
  configurabile dalle impostazioni; l'accettazione viene registrata nella richiesta
  (testo + momento) e mostrata in admin e nella pagina di stato.

= 0.3.1 =
* Migliorato: stile dedicato per la tabella del dettaglio ordine nella pagina di
  stato (allineamento colonne, bordi, importi a destra), indipendente dal tema.

= 0.3.0 =
* Aggiunto: la pagina di stato mostra il dettaglio dell'ordine (data, stato, righe
  prodotto con quantità e subtotale, totale) quando la richiesta è collegata a un
  ordine WooCommerce reale.

= 0.2.4 =
* Modificato: lo slug canonico ("richiesta-recesso" per la pagina di stato) viene
  ora applicato anche alle pagine già esistenti, non solo a quelle nuove. Così i
  link di stato già generati puntano al permalink breve dopo l'aggiornamento.

= 0.2.3 =
* Corretto: la vista "Tutti" della lista richieste in admin ora mostra tutte le
  richieste (prima erano visibili solo filtrando per stato, es. "Ricevuta").

= 0.2.2 =
* Modificato: la pagina di stato viene creata con permalink breve "richiesta-recesso"
  (anziché derivato dal titolo). Vale per le nuove creazioni; le pagine già esistenti
  non vengono rinominate.

= 0.2.1 =
* Corretto: la creazione delle pagine form/stato ora è auto-riparante (gira a ogni
  caricamento, una volta per versione) — non serve più disattivare/riattivare dopo
  un aggiornamento dei file perché il link di stato punti alla pagina corretta.

= 0.2.0 =
* Aggiunto: creazione automatica delle pagine "Richiesta di recesso" e "Stato della
  richiesta di recesso" all'attivazione, con collegamento nelle impostazioni.
* Corretto: il link "Dettaglio"/email ora punta sempre a una pagina di stato valida
  invece di ripiegare sulla home quando la pagina non era configurata.

= 0.1.0 =
* Prima versione: CPT registro recessi, form pubblico in 2 passaggi con verifica
  ordine WooCommerce, email di conferma su supporto durevole, pagina di stato con
  token, tab "Rimborsi" in area account, campo data consegna sull'ordine
  (HPOS-compatibile), calcolo termine 14 giorni, gestione stati
  (Ricevuta/Gestita/Rifiutata), impostazioni e hook privacy GDPR.
