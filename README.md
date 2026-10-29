# MMIPrestaSync — Synchronisation PrestaShop ↔ Dolibarr : web service, files d'attente et résilience.

> Module Dolibarr **MMIPrestaSync** (version 1.1.0), branche `20.0`.

## Fonctionnalités

### Fonctionnalités principales

- Synchronisation produits, clients, adresses, commandes, factures, paiements et stocks.
- Web service `ws.php` authentifié par secret partagé (`MMIPRESTASYNC_WS_SYNC_PASS`, déchiffrement `dolcrypt`) et `ws2.php` pour les resynchronisations manuelles.
- File de synchro (`_sync`), notificateur PrestaShop, triggers et API.
- Configuration : URL PrestaShop/web service, types d'objets synchronisés, résilience.

### Pages

- `admin/about.php`
- `admin/myobject_extrafields.php`
- `admin/setup.php`
- `ws.php`
- `ws2.php`

### Permissions

- `mmiprestasync.resync_button.all` — Resynchroniser manuellement

### Configuration

| Constante | Type | Rôle |
|---|---|---|
| `MMIPRESTASYNC_PS_URL` | string | Prestashop URL |
| `MMIPRESTASYNC_WS_SYNC_URL` | string | Prestashop Webservice Sync URL |
| `MMIPRESTASYNC_WS_SYNC_PASS` | securekey | Prestashop Webservice Sync Key/Pass |
| `MMIPRESTASYNC_SYNC` | yesno | Synchronisation active |
| `MMIPRESTASYNC_CUSTOMER_SYNC` | yesno | Synchronisation Clients |
| `MMIPRESTASYNC_SUPPLIER_SYNC` | yesno | Synchronisation Fournisseurs |
| `MMIPRESTASYNC_ADDRESS_SYNC` | yesno | Synchronisation Afdresses client |
| `MMIPRESTASYNC_PRODUCT_SYNC` | yesno | Synchronisation des produits |
| `MMIPRESTASYNC_PRODUCT_LOT_SYNC` | yesno | Synchronisation des lots produit |
| `MMIPRESTASYNC_SUPPLIER_PRICE_SYNC` | yesno | Synchronisation des Prix Fournisseurs |
| `MMIPRESTASYNC_STOCK_SYNC` | string | Synchronisation du stock |
| `MMIPRESTASYNC_ENTREPOT_IDS` | string | Entrepot(s) Dolibarr à synchroniser (IDs) |
| `MMIPRESTASYNC_ORDER_SYNC` | yesno | Synchronisation Commandes |
| `MMIPRESTASYNC_ORDER_DETAIL_SYNC` | yesno | Synchronisation Lignes de commandes |
| `MMIPRESTASYNC_INVOICE_SYNC` | yesno | Synchronisation Factures et avoirs |
| `MMIPRESTASYNC_PAYMENT_SYNC` | yesno | Synchronisation Réglements client |
| `MMIPRESTASYNC_SHIPPING_SYNC` | yesno | Synchronisation Expéditions client |
| `MMIPRESTASYNC_COSTPRICE_UPDATE` | yesno | Mise à jour des prix de reviens sur les devis au changement de cout de reviens (Appel API) |
| `MMIPRESTASYNC_FIELD_KIT_UNSYNC` | yesno | Ajoute un champ Produit spécifier pour un kit sans synchro vers un kit presta (utile pour des déclinaisons, ou si on ne veut pas afficher le détail du kit) |

### Modèles de documents

- `core/modules/mmiprestasync/doc/doc_generic_myobject_odt.modules.php`
- `core/modules/mmiprestasync/doc/pdf_standard_myobject.modules.php`
- `core/modules/mmiprestasync/mod_myobject_advanced.php`
- `core/modules/mmiprestasync/mod_myobject_standard.php`
- `core/modules/mmiprestasync/modules_myobject.php`

### Hooks

- Contextes : `pdfgeneration`, `ordercard`, `productcard`, `productlotcard`, `stockproductcard`, `pricesuppliercard`, `thirdpartysupplier`, `thirdpartycomm`, `contactcard`
- Callbacks : `MMI_PS_ajaxnotify`, `beforePDFCreation`

### Triggers

- `core/triggers/interface_99_modMMIPrestaSync_PrestaSync.class.php`

### Classes et composants

- `class/actions_mmiprestasync.class.php`
- `class/api_mmiprestasync.class.php`
- `class/mmi_notification.class.php`
- `class/mmi_prestasync.class.php`
- `lib/expe.lib.php`
- `lib/invoice.lib.php`
- `lib/mmiprestasync.lib.php`
- `lib/order.lib.php`
- `lib/payment.lib.php`
- `lib/product.lib.php`
- `lib/product_lot.lib.php`
- `lib/propal.lib.php`
- `lib/stock.lib.php`
- `lib/tiers.lib.php`
- `lib/ws.lib.php`

## Prérequis

- Dolibarr >= 19.0 (branche `20.0` : Dolibarr 20.0).
- Modules MMI : `mmicommon`, `mmiproduct`, `mmifournisseurprice`, `mmipayments`, `mmiworkflow`. **`mmicommon` doit être activé en premier.**

## Installation

1. Copier le module dans `htdocs/custom/mmiprestasync` :

   ```sh
   cd htdocs/custom
   git clone <dépôt> mmiprestasync
   ```

2. Dans Dolibarr : **Configuration → Modules**, activer d'abord `mmicommon`, puis ce module.
3. Vérifier les permissions dans la fiche des utilisateurs concernés.

## Licence

GPLv3 ou ultérieure — voir `COPYING`. Documentation sous GFDL.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) (évolutions datées).
