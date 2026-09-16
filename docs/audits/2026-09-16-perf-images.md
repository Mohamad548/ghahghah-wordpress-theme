# Perf Images Remediation — 2026-09-16

**Branch:** `fix/perf-images`
**Base:** `7add574586a6b1c636881efd1b237a38cd81363d` (`docs: validate performance baseline and capture evidence`)
**Scope:** Theme default image WebP swap for wholesale / agency / contact / single-article heavy PNGs. PNGs retained on disk. No theme version bump. No SEO/H1/slider/priority/CSS changes.

Related audits (unchanged):

- `docs/audits/2026-09-16-performance-seo-baseline.md`
- `docs/audits/2026-09-16-baseline-validation.md`

---

## 1. Environment

| Item | Value |
|------|--------|
| Isolated worktree | `E:/word pares/ghahghah-fix-perf-images` |
| Isolated wp-env port | **8898** (separate Docker project from main `:8888`) |
| Main env | `:8888` preserved; not used as Before |
| Content | DB dump from main → import + `search-replace` 8888→8898 |
| Theme / plugin | `ghahghah-theme` + `ghahghah-core` active |
| Lighthouse | **12.2.1**, HeadlessChrome, mobile simulate, **3 sequential runs**, median of valid samples only |
| Node / Chrome host | same machine as baseline validation |
| `GHAHGHAH_THEME_VERSION` | remains **0.9.60** |

Before = worktree at Base (PNG defaults). After = same DB + WebP defaults in this package.

---

## 2. File size table (disk)

| Asset (under `ghahghah-theme/assets/images/`) | Before PNG | Dim | After WebP | Dim | Reduction |
|-----------------------------------------------|-----------:|-----|-----------:|-----|----------:|
| `wholesale/wholesale-hero-pizza-transparent.png` → `wholesale/ghahghah_pizza_packshot_optimized.webp` | 2 177 432 | 1122×1402 | 382 340 | 1122×1402 | **82.4%** |
| `agency/parsley-onion-pack-composition-transparent.png` → `agency/ghahghah_parsley_onion_pack_optimized.webp` | 2 223 788 | 1122×1402 | 358 514 | 1122×1402 | **83.9%** |
| `contact/corn-isolated-transparent.png` → `contact/corn-isolated-transparent-optimized.webp` | 1 513 908 | 1448×1086 | 182 220 | 1448×1086 | **88.0%** |
| `single-article/article-hero-banner-real-snack.png` → `…-optimized.webp` | 2 059 376 | 1672×941 | 142 948 | 1672×941 | **93.1%** |
| `single-article/article-inline-hands-snack-corn.png` → `…-optimized.webp` | 2 208 170 | 1448×1086 | 152 044 | 1448×1086 | **93.1%** |
| `single-article/article-inline-factory-line-real-snack.png` → `…-optimized.webp` | 2 119 166 | 1448×1086 | 150 406 | 1448×1086 | **92.9%** |
| `single-article/bowl-of-real-snacks-transparent.png` → `…-optimized.webp` | 1 930 127 | 1254×1254 | 281 338 | 1254×1254 | **85.4%** |
| `single-article/corn-and-real-snacks-transparent.png` → `…-optimized.webp` | 1 466 345 | 1254×1254 | 280 878 | 1254×1254 | **80.8%** |
| `single-article/decorative-snack-cluster-transparent.png` → `…-optimized.webp` | 1 943 454 | 1254×1254 | 305 718 | 1254×1254 | **84.3%** |
| **Sum** | **17 641 766** | — | **2 236 406** | — | **87.3%** |

Five WebPs reused from prior local WIP after dimension match (`DIM_OK`). Four encoded with `sharp` q=82, alpha preserved. **Original PNGs kept** for legacy paths.

---

## 3. Mobile Lighthouse medians (valid samples)

Artifacts: `docs/audits/artifacts/perf-images/{before,after}/summary.json`, `comparison.json`.

| Page | Phase | Perf | LCP ms (n=3) | CLS | TBT ms | Reqs | Transfer B |
|------|-------|-----:|-------------:|----:|-------:|-----:|-----------:|
| Wholesale | before | 65 | 12821 | 0.035 | 237 | 21 | 2 354 094 |
| Wholesale | after | 60 | **4109** | 0.004 | 934 | 21 | **559 008** |
| Agency | before | 49 | 13118 | 0.297 | 236 | 21 | 2 401 222 |
| Agency | after | 67 | **3955** | 0.276 | 180 | 21 | **535 943** |
| Contact | before | 65 | 9110 | 0.010 | 223 | 39 | 2 150 725 |
| Contact | after | 92 | **2604** | 0.007 | 97 | 39 | **819 081** |
| Article | before | 90 | 1584 | 0.001 | 224 | 27 | 8 332 903 |
| Article | after | 95 | 1559 | 0.001 | 194 | 26 | **1 062 899** |

Transfer deltas (page total): wholesale **−1.80 MB**, agency **−1.87 MB**, contact **−1.33 MB**, article **−7.27 MB**.

Caveats (measured):

- Local TTFB still inflates absolute LCP; **do not treat as production SLA**.
- Agency **CLS remains high (~0.28)** — **not claimed fixed**.
- Wholesale after TBT median rose (237→934) while transfer/LCP fell — lab variance / main-thread; not asserted as regression root-caused here.
- No claim about Home **NO_LCP**.

---

## 4. Network & visual evidence

**Network (after run2):** target heroes returned **200**, `image/webp`, no legacy PNG of the swapped set in those page traces (`legacyPngAfter: []`). Article loads multiple optimized WebPs (hero/inline/deco).

**Visual:** before/after mobile finals + after desktop finals under `artifacts/perf-images/`. Wholesale hero: transparency retained, pack text readable, layout unchanged. Same for agency/contact/article samples reviewed.

---

## 5. PHP wiring (defaults only)

Updated fallbacks / manifest entries only (admin attachment IDs still win where coded):

- `inc/request-pages-settings.php` — wholesale/agency hero WebP + intrinsic 1122×1402
- `inc/contact-page-settings.php` — corn WebP + 1448×1086
- `inc/single-article-settings.php` — hero/inline WebP + width/height
- `single-post.php` — deco + related fallback WebP
- `inc/media/sync-theme-media.php` — manifest paths to WebP (sync logic untouched)

---

## 6. Tests

| Check | Result | Notes |
|-------|--------|-------|
| PHP `php -l` on changed files | **PASS** | |
| `scripts/verify-structure.php` | **PASS** | exit 0 |
| PHPUnit | **PASS** | 7 tests, 13 assertions |
| PHPCS (5 changed files) | **PASS vs new debt** | After CRLF normalize: **47 errors / 73 warnings** — matches base error count (47); remaining = pre-existing debt, not expanded by this package |
| Full `scripts/smoke-wordpress.sh` | **NOT RUN** | worktree lacked usable `@wordpress/env` via npx without altering package-lock; blocked |
| Manual smoke on `:8898` | **PASS** | home/wholesale/agency/contact/article HTTP 200; theme+core active; live-search 200; WebP strings present in HTML |
| Media sync / version bump | **NOT RUN / N/A** | intentionally skipped |

---

## 7. Main path WIP status

Primary checkout (`E:/word pares/ghahghah-wordpress theme`) left on `audit/performance-seo` @ `75229ac` (already contained a related WebP commit that **deleted** some PNGs + bumped version + Jalali). **This branch is the clean Base-relative fix that keeps PNGs and does not bump version.** Main tree was not reset/cleaned.

---

## 8. Remaining issues (out of scope)

- Home mobile NO_LCP
- Agency CLS
- Logo `fetchpriority` / hero multi-download
- Contact maps / remaining decorative PNGs elsewhere
- SEO meta/canonical/H1
- Full smoke script wiring for secondary worktrees

---

## 9. Reproduction

```bash
git worktree add -b fix/perf-images ../ghahghah-fix-perf-images 7add574
# start wp-env with port 8898; import DB from main; then measure Before at clean HEAD
# apply WebP + PHP; measure After
```

Artifacts: `docs/audits/artifacts/perf-images/`.
