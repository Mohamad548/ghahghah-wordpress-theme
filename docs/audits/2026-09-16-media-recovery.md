# Media recovery — 2026-09-16 (`:8898`)

**Branch:** `fix/perf-images`  
**Worktree:** `E:/word pares/ghahghah-fix-perf-images`  
**Env fixed:** `http://localhost:8898` (Compose `…c3316861…`)  
**Read-only source:** `http://localhost:8888` (not mutated)

---

## Proven root cause

**Incomplete `wp-content/uploads` on the isolated `:8898` WordPress volume**, while the database still referenced those attachment paths.

- Attachment posts and `_wp_attached_file` metadata existed on `:8898`.
- **143 files** present under `:8888` uploads `2026/09/` were **missing** on `:8898`.
- Reported 404s (`cone-snacks-closeup.jpg`, `light-triangle-pieces.jpg`, `snack-sticks-closeup-640x480.jpg`, `snack-serving-board-600x480.jpg`) were among those missing files; after copy they return `200 image/jpeg`.
- Additional gaps included optimized WebPs referenced by media library (e.g. `article-hero-banner-real-snack-optimized.webp`, `ghahghah_pizza_packshot_optimized.webp`) while older PNGs remained only on `:8898`.

This is an **environment/data** problem (uploads volume out of sync with DB), not a CSS hide and not a redesign of theme assets.

---

## Slider settings (separate from the four 404s)

Compared without theme bootstrap (`wp --skip-plugins --skip-themes`):

| Option | `:8898` | `:8888` |
|--------|---------|---------|
| `ghahghah_hero_banner_pack` | `flavour-optimized-1933x813-v1` | same |
| `ghahghah_theme_media_sync_version` | `0.9.60` | `0.9.66` |
| `theme_mods_ghahghah-theme` → `ghahghah_hero_slides` | **9 slides**, desktop IDs 195–203 / mobile 114…146 | same IDs |
| Slide link hosts | `localhost:8898` | `localhost:8888` (only material theme_mods diff) |

Hero desktop attachment files (`ghahghah_*_flavour_banner_optimized.webp`) already existed on disk on **both** envs before the uploads copy.

**Conclusion:** Smoke theme switching did **not** wipe slider settings on this env. Re-activating the theme alone would not fix missing upload bytes. `ghahghah_maybe_refresh_hero_banner_pack()` only clears slides when the pack string changes; pack already matched. Version `0.9.60` vs `0.9.66` reflects different codebases (fix vs audit), not a cleared pack on `:8898`.

Browser check: **all 9 hero slides** advanced via `[data-ghahghah-hero-next]` on mobile and desktop — each active slide decoded with `naturalWidth > 0`.

---

## Fix applied

1. **Backup (local only, not committed):** DB dump + theme_mods / option snapshots under `docs/audits/artifacts/media-recovery/backups/` (gitignored).
2. **Copy only missing files** from `:8888` uploads `2026/09/` → `:8898` (tar of the 143-name list). No blind overwrite of existing files. `:8888` unchanged.
3. **Smoke hardening** (`scripts/smoke-wordpress.sh`): snapshot `theme_mods_ghahghah-theme`, `ghahghah_hero_banner_pack`, and `ghahghah_theme_media_sync_version` before mutations; restore them on EXIT after theme/plugin restore.
4. **QA:** `scripts/check-image-health.sh` — discovers image URLs from key pages + known regression files; requires HTTP 200 + `image/*`.

No theme PHP redesign, no forced media sync, no slide deletion, no audit-branch merge.

---

## Verification

| Check | Result |
|-------|--------|
| Known 4 JPGs + packshot/article optimized WebP | `200` + image type |
| `scripts/check-image-health.sh` on `:8898` | **147 passed / 0 failed** |
| Hero slides mobile/desktop (browser) | **9/9 OK** (`allSlidesOk=true`) |
| Products / articles archive banners | HTTP OK (incl. Persian-named products banner when correctly encoded) |
| `wholesale-request-guide` + wholesale page images | HTTP OK after copy |
| `:8888` | left running; sample upload still `200` |

Screenshots: `docs/audits/artifacts/media-recovery/screenshots/*-{mobile,desktop}.png`  
Browser report: `docs/audits/artifacts/media-recovery/browser-image-health.json`

---

## Damaged → recovered (examples)

| Asset | Before `:8898` | After |
|-------|----------------|-------|
| `cone-snacks-closeup.jpg` (+ sizes) | 404 | 200 |
| `light-triangle-pieces.jpg` (+ sizes) | 404 | 200 |
| `snack-sticks-closeup*.jpg` | 404 | 200 |
| `snack-serving-board*.jpg` | 404 | 200 |
| `article-hero-banner-real-snack-optimized.webp` | 404 | 200 |
| `ghahghah_pizza_packshot_optimized.webp` | 404 | 200 |
| Hero flavour banners | already 200 | still 200; all slides visible |

---

## Note on “environmental only”

The display fix for missing images was **uploads file restoration on `:8898`**. Repo commits add recovery report, QA tooling, smoke restore of hero-related options, and evidence artifacts — not a new image pipeline or audit merge.
