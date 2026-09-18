# قالب قهقهه (`ghahghah-theme`)

Custom corporate WordPress theme for brand قهقهه.

- Text domain: `ghahghah`
- Requires PHP 8.1+ and WordPress 6.4+
- RTL-first, no page-builder dependency

## Responsibilities

- Theme supports (title tag, thumbnails, HTML5, custom logo, editor styles)
- Menu locations: `primary`, `footer`, `legal`
- Asset enqueue (vanilla CSS/JS)
- Image sizes: `ghahghah-card`, `ghahghah-hero`
- Safe fallbacks when menus or Ghahghah Core are unavailable

## Does not own

Product CPT, inquiry forms, SMS, or other business persistence — those belong in `ghahghah-core`.

## Structure

```
ghahghah-theme/
├── assets/css|js|images/
├── inc/           # setup, assets, template-tags
├── template-parts/
├── languages/
├── theme.json
└── classic templates (front-page, page, single, …)
```

## Activation notes

Works with Core inactive. The starter front page shows a status notice when Core is missing.
