# قهقهه — WordPress Project

Corporate product-catalog WordPress monorepo for the Iranian food brand **قهقهه**.

Repository: [github.com/Mohamad548/ghahghah-wordpress-theme](https://github.com/Mohamad548/ghahghah-wordpress-theme)

This repository contains two independent installable packages:

| Package | Type | Text domain | Role |
|---|---|---|---|
| `ghahghah-theme` | Theme | `ghahghah` | Presentation, layout, assets |
| `ghahghah-core` | Plugin | `ghahghah-core` | Business logic that must survive theme swaps |

This is a **corporate product-catalog** website, not a WooCommerce shop: no prices, cart, checkout, or online purchasing.

## Requirements

- PHP **8.2+**
- WordPress **6.4+**
- Composer (for coding standards / tests; optional for runtime)
- Node.js / npm and Docker Desktop (optional; for local `wp-env` smoke tests)

## Installation

1. Copy `ghahghah-theme` into `wp-content/themes/`.
2. Copy `ghahghah-core` into `wp-content/plugins/`.
3. In **wp-admin → Plugins**, activate **Ghahghah Core**.
4. In **Appearance → Themes**, activate **قهقهه**.
5. Assign menus to **Primary**, **Footer**, and **Legal** locations.
6. Optionally set a custom logo under **Appearance → Customize**.

Theme and plugin activate independently. The theme works safely when Core is inactive (soft notice only; no fatal error). Core owns the product catalogue CPT and will later own forms/SMS.

## Local wp-env

```bash
npm install
npm run wp-env:start
npm run test:smoke
npm run wp-env:stop
```

Site URL after start is typically `http://localhost:8888`.

## Development checks

```bash
composer install
composer check
```

Individual scripts:

```bash
composer lint:php
composer phpcs
composer test
bash scripts/check-php.sh
php scripts/verify-structure.php
npm run test:smoke
```

## Architecture decisions

- **Theme / plugin split**: catalogue CPT and future forms/SMS live in Core; theme only renders.
- **No page builders / ACF / WooCommerce / Bootstrap / jQuery / Tailwind**.
- **RTL-first** CSS and Persian admin labels for the product CPT.
- **`theme.json` + CSS custom properties** share the brand color system.
- **PSR-4** under `Ghahghah\Core\` with a Composer-less autoload fallback.
- **Rewrite flush** only on Core activation/deactivation.
- **Custom product capabilities** granted to Administrator and Editor on activation.

### Color system

| Token | Hex |
|---|---|
| Brand red | `#D71920` |
| Corn yellow | `#F5B400` |
| Natural green | `#3A8F45` |
| Dark text | `#222222` |
| White | `#FFFFFF` |
| Neutral background | `#F7F7F5` |

## Design sources

`design-sources/README.md` and `design-sources/image-inventory.md` are tracked.

Original brand files, raw product photographs, and UI reference PNGs under `design-sources/brand/`, `raw-products/`, `temporary/`, and `ui-reference/` remain **local only** and are intentionally excluded from Git (they are large and not part of the distributable theme/plugin packages).

## Assumptions (starter phase)

- Front page is a **starter verification shell**, not the approved marketing design.
- Product metadata, forms, SMS, and custom DB tables are **out of scope** for the baseline.
- Default product archive slug is `products` (filter: `ghahghah_product_archive_slug`).

## Package docs

- [Theme README](ghahghah-theme/README.md)
- [Core plugin README](ghahghah-core/README.md)
