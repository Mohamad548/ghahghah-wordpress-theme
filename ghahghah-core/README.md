# افزونه Ghahghah Core (`ghahghah-core`)

Lightweight companion plugin for business features that must survive theme changes.

- Text domain: `ghahghah-core`
- Namespace: `Ghahghah\Core`
- Requires PHP 8.2+ and WordPress 6.4+

## This phase

Architecture + `ghahghah_product` custom post type:

- Persian admin labels
- Public archive (default slug `products`, filterable)
- REST API (`ghahghah-products`)
- Supports: title, editor, thumbnail, excerpt, revisions
- Custom capabilities mapped for Administrator & Editor
- Rewrite flush only on activate/deactivate

## Planned (later)

- Product catalogue metadata
- Wholesale inquiry form
- Representation request form
- Admin request management
- SMS integration

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
