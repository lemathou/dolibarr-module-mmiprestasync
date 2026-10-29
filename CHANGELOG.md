# Changelog — MMIPrestaSync

Toutes les évolutions notables, avec dates (ISO).

## [1.1.0] — 2026-10-29

### Modifié
- Migration Dolibarr 20.0 (RM3565).
- Documentation : README complet (fonctionnalités, prérequis, configuration) et CHANGELOG daté (RM3569).

## Historique daté

- 2026-10-29 — Merge branch '3580-hardening' into '20.0'
- 2026-10-29 — RM3580 mmiprestasync : durcissement SQL et curl
- 2026-10-29 — Merge branch '3580-security-dolcrypt' into '20.0'
- 2026-10-29 — RM3580 mmiprestasync : dechiffre dolcrypt pour l auth ws.php
- 2026-10-28 — Merge branch '3580-security' into '20.0'
- 2026-10-28 — RM3580 mmiprestasync : ws2.php verifie le droit et le jeton CSRF
- 2026-10-28 — RM3580 mmiprestasync : authentification obligatoire du webservice ws.php
- 2026-10-28 — Merge branch '3567-cleanup-mmiprestasync' into '20.0'
- 2026-10-28 — RM3567 mmiprestasync : retrait du debug, stubs morts et case trigger mort
- 2026-10-28 — Merge 19.0 into 20.0
- 2026-09-01 — Merge branch '2880-extrafields-catalogpro' into '19.0'
- 2026-08-27 — RM2560: extrafields « catalogue pro » sur les tiers
- 2025-12-18 — Fix extrafield, clean unused code
- 2025-12-18 — Adds synchronisation for Prestashop customer fields newsletter and optin into Dolibarr extrafields
- 2025-11-20 — Use new helper classes in module MMICommon : mmi_prices::price_format & co.
- 2025-09-23 — Merge branch '19_feat_update_propal_cost_price' into '19.0'
- 2025-09-23 — feat: add API endpoint to update cost price on propal per product
- 2025-07-01 — Remove redundant translations
- 2024-04-15 — Fix bad config param to activate synchro + Debug
- 2024-04-15 — Fix bad param type
- 2024-03-11 — Fix disable triggers if disabled
- 2024-03-11 — Trigger PRODUCT_SUBPRODUCT instead of PRODUCT_SOUSPRODUIT
- 2024-02-13 — Module depends update
- 2024-02-13 — Move fields : product:garantie to mmiproduct, supplier_order:recpt_ok & order:expe_ok to mmiworkflow
- 2023-11-20 — Fix translation DEPOSIT Acompte
