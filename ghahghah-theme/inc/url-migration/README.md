# URL slug migration (theme)

## What ships in Git

| Path | Purpose |
|------|---------|
| `migrate-public-slugs.php` | WP-CLI dry-run / apply; resolves by title/slug/catalog key |
| `redirects.php` | Front-end 301 from option `ghahghah_url_redirects` |

Slug/menu DB changes and the redirect option **do not travel with `git push`**. Re-run the migrator on each environment.

## Backup (local only)

```bash
./node_modules/.bin/wp-env run cli wp db export /tmp/url-slug-pre.sql
# copy out of container to a gitignored path, e.g.:
# docs/audits/artifacts/url-migration/backups/url-slug-pre.sql
```

## Dry-run then apply

```bash
./node_modules/.bin/wp-env run cli wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php -- --dry-run
./node_modules/.bin/wp-env run cli wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php -- --apply --flush
# Re-run apply: should report mostly skipped/already (idempotent).
```

## Rollback

1. Restore SQL backup.
2. Or clear `wp option delete ghahghah_url_redirects` and restore `post_name` values from the migration report JSON.
