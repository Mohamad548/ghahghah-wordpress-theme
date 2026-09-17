# URL slug migration (theme)

## What ships in Git

| Path | Purpose |
|------|---------|
| `migrate-public-slugs.php` | WP-CLI dry-run / apply; resolves by title/slug/catalog key |
| `redirects.php` | Front-end 301 from option `ghahghah_url_redirects` (preserves `gh_flavor`, `gh_sort`, `gh_q`, `paged`, `page`) |
| `repair-footer-menu-location.php` | One-shot footer≠bottom menu location repair (not on front-end `init`) |

Slug/menu DB changes and the redirect option **do not travel with `git push`**. Re-run the migrator on each environment.

Legacy listing/flavor pages use explicit English slugs (`products-legacy`, `legacy-all-products`, `legacy-flavor-pizza`, `legacy-flavor-lemon`), are set `private`, and 301 to the CPT archive (no `sanitize_title('legacy-' . Persian)`). Intermediate paths also 301 to the final destination (flattened map).

## Backup (local only)

```bash
./node_modules/.bin/wp-env run cli wp db export wp-content/uploads/url-slug-pre-repair-STAMP.sql
# Record path in docs/audits/artifacts/url-migration/backups/LATEST_BACKUP.txt
# Restore:
./node_modules/.bin/wp-env run cli wp db import wp-content/uploads/url-slug-pre-repair-STAMP.sql
```

## Dry-run then apply

```bash
./node_modules/.bin/wp-env run cli bash -lc 'GHAHGHAH_MIGRATE_MODE=dry-run wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php'
./node_modules/.bin/wp-env run cli bash -lc 'GHAHGHAH_MIGRATE_MODE=apply GHAHGHAH_MIGRATE_FLUSH=1 wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php'
# Second apply must report slug_changes=0, option_delta=0 (idempotent).
```

## Footer menu repair (explicit)

```bash
./node_modules/.bin/wp-env run cli wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/repair-footer-menu-location.php
```

## Rollback

1. Restore SQL backup.
2. Or clear `wp option delete ghahghah_url_redirects` and restore `post_name` values from the migration report JSON.
