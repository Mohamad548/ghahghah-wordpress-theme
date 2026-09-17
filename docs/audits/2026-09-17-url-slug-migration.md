# URL slug migration — English public paths (2026-09-17)

**Branch:** `fix/perf-navigation`  
**Env:** `http://localhost:8898` (worktree `ghahghah-fix-perf-images`)  
**Note:** Slug/menu/redirect **DB changes do not travel with `git push`**. Re-run the migrator on each environment after deploying theme code.

---

## Why it looked unfinished

Migration tooling was prepared earlier (dry-run + backup), but **apply** had not been run on `:8898` yet — so the browser still showed `/مقالات/` and other Persian page paths. That apply is now done on this env.

Jalali display dates lived on the main checkout (`inc/jalali-date.php`) but were **missing from this worktree**; they are now included and hooked.

---

## Applied mapping (titles unchanged)

| Content | New path | Old path → 301 |
|---------|----------|----------------|
| مقالات (posts page) | `/articles/` | `/مقالات/` |
| درخواست خرید عمده | `/wholesale/` | `/درخواست-خرید-عمده/` |
| درخواست نمایندگی | `/agency/` | `/درخواست-نمایندگی/` |
| تماس با ما | `/contact/` | `/تماس-با-ما/` |
| کارخانه | `/factory/` | `/کارخانه/` |
| پرسش‌های متداول | `/faq/` | (already English) |
| حریم خصوصی | `/privacy-policy/` | (already English) |
| CPT archive | `/products/` | `/محصولات/` (legacy page → archive) |
| Product singles | `/products/corn-pellet-{flavor}/` | Persian title slugs under `/products/…` |

Verified on `:8898`: new paths **200**; old paths **301** to English targets; unknown slug still **404**.

---

## How to reproduce on another env

1. Deploy theme (includes `inc/url-migration/redirects.php` + migrator).
2. Backup DB (local only; gitignored):

```bash
./node_modules/.bin/wp-env run cli bash -lc 'wp db export /tmp/url-slug-pre.sql'
```

3. Dry-run then apply:

```bash
./node_modules/.bin/wp-env run cli bash -lc \
  'GHAHGHAH_MIGRATE_MODE=dry-run wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php'
./node_modules/.bin/wp-env run cli bash -lc \
  'GHAHGHAH_MIGRATE_MODE=apply GHAHGHAH_MIGRATE_FLUSH=1 wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/migrate-public-slugs.php'
```

4. Re-run apply → mostly `already` / no extra changes (idempotent).

Resolver uses **title / alt slug / `_ghahghah_catalog_key`** — not hardcoded `:8898` IDs.

Local backup on this env: `docs/audits/artifacts/url-migration/backups/url-slug-pre.sql` (**not in Git**).

---

## Code that prevents Persian slugs from coming back

- Product import sets `post_name` from catalog `key`.
- Setup scripts for wholesale/agency/contact/factory align existing pages to English `post_name` when found via Persian alt slugs.
- Agency seed slug is `agency` (alt: `representation`, Persian).

---

## Jalali dates

`ghahghah-theme/inc/jalali-date.php` filters `get_the_date` / `get_the_modified_date` for front-end human formats (keeps `datetime` / machine formats Gregorian).

Verified on `/articles/`: visible label like «۲۳ شهریور ۱۴۰۵» with `datetime="2026-09-14T…"`.

---

## Open follow-ups (unchanged / not closed)

- Full Smoke suite
- H1 when all front sections off + empty static page
- Portable Yoast settings transfer
- Single meta / archive canonical validation
- NO_LCP mobile / Wholesale TBT
- Sitewide nav timing suite may still be incomplete from earlier run; not claimed PASS here
