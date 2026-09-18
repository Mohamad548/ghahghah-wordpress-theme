# افزونه Ghahghah Core (`ghahghah-core`)

Lightweight companion plugin for business features that must survive theme changes.

- Text domain: `ghahghah-core`
- Namespace: `Ghahghah\Core`
- Requires PHP 8.1+ and WordPress 6.4+

## This phase

- `ghahghah_product` CPT + REST
- Inquiry forms: wholesale + agency (`ghahghah_inquiry` CPT)
- REST `ghahghah/v1/inquiries` submit + Melipayamak pattern SMS (theme settings / mobile-auth fallback)

## Planned (later)

- Product catalogue metadata
- Richer admin request workflows

## Structure

```
ghahghah-core/
├── ghahghah-core.php
├── composer.json
├── src/
│   ├── Plugin.php
│   ├── Activator.php
│   ├── Deactivator.php
│   ├── Capabilities.php
│   └── PostTypes/Product.php
└── languages/
```

## Composer

```bash
cd ghahghah-core
composer dump-autoload -o
```

A PSR-4 fallback autoloader is bundled so the plugin still loads if `vendor/` is absent.
