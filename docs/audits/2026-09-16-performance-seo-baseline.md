# Performance & SEO Baseline Audit — 2026-09-16

**Branch:** `audit/performance-seo`
**HEAD:** `924b087796c79b91dd153d7594530132800b5a6c`
**Scope:** Local wp-env baseline only (`http://localhost:8888`). Not production hosting.
**Method:** Measured HTTP/HTML parse + Lighthouse 12.2.1 (3 runs × mobile/desktop medians). No production code changed.

---

## 1. Executive Summary

Measured local baseline shows a **structurally complete RTL Persian site** with strong Accessibility scores (~96–100) but clear SEO and LCP risks:

1. **Home has zero `<h1>`** while sections render (Hero/Featured/…). Fallback H1 only appears when all sections are off — **SEO-001 P1 (measured)**.
2. **No meta description / Open Graph / Twitter / JSON-LD** on any tested page; **no SEO plugin** active — Core/theme only — **SEO-002/003 P1 (measured)**.
3. **Product & articles archives lack `rel=canonical`** — **SEO-004 P1 (measured)**.
4. **Home hero downloads many High-priority WebP banners** (~8 desktop slides) — request/byte competition with LCP — **PERF-001 P1 (measured)**.
5. **Home mobile Lighthouse cannot compute LCP (`NO_LCP`)** across simulate + 3× devtools runs; Performance score null — **PERF-002 P1 (measured)**.
6. **Agency mobile CLS median ~0.297** (target ≤0.1) — **PERF-003 P1 (measured)**.
7. **Wholesale/Agency/Contact mobile lab LCP ≫ 2.5s** (often 9–13s Lantern) — **PERF-004 P1 (measured, local TTFB inflated)**.
8. Multiple PNGs **1.4–2.2 MB** still shipped in theme assets — **PERF-005 P2 (measured on disk)**.
9. **Live-search** is public, min length 2, no rate limit/cache headers — **SEC-PERF-001 P2 (measured)**.
10. **PHPCS baseline debt:** 942 errors / 426 warnings — reference only (not fixed).

**INP:** NOT AVAILABLE — requires field data. Lab responsiveness proxy: **TBT**.

---

## 2. Audit Scope

| In scope | Out of scope |
|----------|--------------|
| Theme + Core as at `924b087` | Production hosting / CDN / HTTP/2 edge |
| Local wp-env HTTP, HTML, Lighthouse | Code remediations |
| Asset sizes on disk + network transfer in LH | Image conversion / font subsetting |
| REST live-search & form-data probes | Destructive load tests |
| PHPCS counts as debt | PHPCBF / style fixes |

---

## 3. Environment

| Item | Value |
|------|--------|
| Host PHP | 8.2.12 |
| Node | v20.20.2 |
| npm | 10.8.2 |
| Docker | 28.5.1 |
| wp-env | 11.15.0 |
| wp-env status | running, port 8888 |
| WordPress (container) | **7.1** |
| PHP (container) | **8.3.33** |
| Site URL / Home | `http://localhost:8888` |
| Permalink | `/%postname%/` |
| show_on_front | `page` |
| page_on_front | `6` |
| blog_public | `1` |
| Published products | **9** |
| Published posts | **4** |
| Lighthouse | **12.2.1** via `npx --yes` (cwd `/tmp`, no package.json change) |
| Chrome | `C:\Program Files\Google\Chrome\Application\chrome.exe` |
| Session | Logged-out curl + headless Chrome |

---

## 4. Active Theme and Plugins

| Component | Status | Version |
|-----------|--------|---------|
| Theme `ghahghah-theme` | active | (theme version 0.9.60 in assets) |
| Plugin `ghahghah-core` | **active** | 0.2.2 |
| `hello` | inactive | 1.7.2 |
| Yoast / Rank Math / AIOSEO | **not installed / not active** | — |

**Implication:** Canonical, meta description, OG, and Schema come only from WordPress Core (+ theme HTML). No SEO plugin output to conflict with.

---

## 5. Tested URLs

Source: WP-CLI discovery → `docs/audits/artifacts/lighthouse/discovered-urls.json`.

| Page | Final URL | HTTP (median of 3) | Redirects | HTML size (bytes) | TTFB median (s) |
|------|-----------|--------------------:|----------:|------------------:|----------------:|
| Home | `/` | 200 | 0 | 128507 | 1.20 |
| Products archive | `/products/` | 200 | 0 | 105759 | 0.81 |
| Single product | `/products/…فلفلی/` | 200 | 0 | 94629 | 1.79 |
| Articles archive | `/مقالات/` | 200 | 0 | 87290 | 1.76 |
| Single article | `/wholesale-request-guide/` | 200 | 0 | 110460 | 1.97 |
| Wholesale | `/درخواست-خرید-عمده/` | 200 | 0 | 109900 | 2.15 |
| Agency | `/درخواست-نمایندگی/` | 200 | 0 | 110236 | 1.58 |
| Contact | `/تماس-با-ما/` | 200 | 0 | 92242 | 1.40 |
| Factory | `/کارخانه/` | 200 | 0 | 89582 | 1.24 |
| Privacy | `/privacy-policy/` | 200 | 0 | 84708 | 1.10 |
| FAQ | `/faq/` | 200 | 0 | 92477 | 1.19 |
| Search | `/?s=اسنک` | 200 | 0 | 91115 | 1.78 |
| 404 | `/this-page-definitely-does-not-exist-404-audit/` | **404** | 0 | 76264 | 1.08 |

**Headers (observed):** No `Content-Encoding` / weak cache headers on HTML in wp-env — **not attributed to theme**; localhost Apache/nginx in env. `X-Robots-Tag` present on REST (`noindex`).

---

## 6. Lighthouse Median Results

Artifacts: `docs/audits/artifacts/lighthouse/summary-medians.json`, `runs-extracted.json`.
Full LHR JSON kept locally at `%TEMP%/ghahghah-lh-raw-2026-09-16/` (~28MB; not committed).

**Conditions:** warm-up discarded; 3 runs; mobile = form-factor mobile + Lantern simulate; desktop = `--preset=desktop`. Home mobile also retested with `--throttling-method=devtools` (still `NO_LCP`).

| Page | FF | Perf | A11y | BP | SEO | FCP ms | LCP ms | CLS | SI ms | TBT ms | Reqs | Transfer KB |
|------|----|-----:|-----:|---:|----:|-------:|-------:|----:|------:|-------:|-----:|------------:|
| Home | mobile | **null** | ~97 | — | ~85 | ~1282* | **NO_LCP** | 0.006 | ~6085* | null / ~682† | 44 | ~1195 |
| Home | desktop | **83** | — | — | — | ~415 | **2610** | 0.001 | — | 0 | 48 | ~2407 |
| Products | mobile | **91** | — | — | — | — | **2909** | 0.008 | — | ~11 | 29 | ~620 |
| Products | desktop | **94** | — | — | — | — | **828** | 0.006 | — | 0 | 29 | ~419 |
| Single product | mobile | **94** | — | — | — | — | **2023** | 0.002 | — | ~55 | 24 | ~242 |
| Single product | desktop | **94** | — | — | — | — | **553** | 0.007 | — | 0 | 25 | ~343 |
| Articles | mobile | **81** | — | — | — | — | **3749** | 0.091 | — | 175 | 24 | ~571 |
| Articles | desktop | **94** | — | — | — | — | **951** | 0.003 | — | 0 | 24 | ~609 |
| Single article | mobile | **92** | — | — | — | — | **1865** | 0.001 | — | ~32 | 27 | ~8019 |
| Single article | desktop | **94** | — | — | — | — | **539** | 0.004 | — | 0 | 31 | ~11845 |
| Wholesale | mobile | **69** | — | — | — | — | **12821** | 0.036 | — | 0 | 21 | ~2286 |
| Wholesale | desktop | **82** | — | — | — | — | **2305** | 0.007 | — | 0 | 21 | ~2306 |
| Agency | mobile | **54** | — | — | — | — | **13115** | **0.297** | — | 0 | 21 | ~2332 |
| Agency | desktop | **84** | — | — | — | — | **2353** | 0.015 | — | 0 | 21 | ~2352 |
| Contact | mobile | **70** | — | — | — | — | **9074** | 0.010 | — | 0 | 39 | ~2088 |
| Contact | desktop | **86** | — | — | — | — | **1790** | 0.007 | — | 0 | 84 | ~2274 |

\* From metrics object when category score null.
† Home mobile **devtools** TBT median.

**INP:** NOT AVAILABLE — requires field data.
**Lab responsiveness proxy:** TBT.

**Lighthouse SEO audit notes (home):** `meta-description` score 0; `robots-txt` score 0 (env); title/canonical crawlable OK where present.

---

## 7. Core Web Vitals Baseline (lab)

| Target | Home desk | Products mob | Agency mob | Wholesale mob |
|--------|-----------|--------------|------------|---------------|
| LCP ≤ 2.5s | 2.61s (borderline) | 2.91s | **13.1s** | **12.8s** |
| CLS ≤ 0.1 | 0.001 OK | 0.008 OK | **0.297 FAIL** | 0.036 OK |
| INP ≤ 200ms | Field N/A | Field N/A | Field N/A | Field N/A |

**Caveat:** Local TTFB often 1–4s in metrics (`timeToFirstByte`) — inflates LCP on localhost. Treat absolute LCP as comparative, not production SLA.

---

## 8. Network and Asset Budget

### Home (measured HTML + LH network)

| Resource | Count (HTML link/script) | Raw on disk (approx) | Gzip est. (level 9) |
|----------|-------------------------:|---------------------:|--------------------:|
| CSS (theme front bundle) | **13** stylesheets | ~91 KB | ~22 KB |
| JS (theme front bundle) | **9** scripts | ~48 KB | ~15 KB |
| Fonts | 2 WOFF faces (4 `@font-face`) | ~75 KB | — |
| Images (LH desktop) | many; **8+ High priority hero WebPs** | — | ~1.6MB+ transfer for heroes alone |

**Hypothesis (supported by `inc/assets.php`):** On `is_front_page()`, CSS/JS for featured/factory/steps/collab/forms/articles enqueue **without** `ghahghah_should_render_*` gates (unlike Hero). Measured: home CSS=13 vs product page CSS=7.

**HTTP/2 note:** Combining small CSS/JS helps HTTP/1.1 more than HTTP/2/3; still reduces parse/priority contention. Not remediated here.

### Other pages (HTML parse)

| Page | CSS links | JS scripts |
|------|----------:|-----------:|
| Products / articles / single product | 7 | 4 |
| Wholesale / agency / contact | 8 | 4 |
| Factory / privacy / faq / search / 404 | 7 | 3 |

---

## 9. CSS/JavaScript Loading

**File:** `ghahghah-theme/inc/assets.php`

| Observation | Type |
|-------------|------|
| Global: fonts, base, layout, header, footer, mobile-bottom-nav | Measured |
| Front-page section CSS/JS loaded as a block when `is_front_page()` | Measured in source + CSS count |
| Hero CSS/JS gated by `ghahghah_should_render_hero()` | Measured in source |
| Scripts use `defer` + footer strategy | Measured in source |
| No jQuery/Bootstrap enqueue | Measured earlier verification |

---

## 10. Images and LCP

### LCP element gatherer

Lighthouse 12 TraceElements/RootCauses failed (`frame_sequence` undefined) — **LCP element snippet often unavailable**. Inference from network:

| Page | Inference |
|------|-----------|
| Home desktop | Largest High-priority images = hero WebPs (`ghahghah_*_flavour_banner_optimized.webp`, ~200–233 KB each); **all slides High priority** |
| Home mobile | **NO_LCP** — no qualifying LCP in lab (simulate + devtools) |
| Products archive | Likely hero/banner (archive CSS/hero uses `fetchpriority="high"` on corn/banner) |
| Single product | Gallery primary image likely; logo also `fetchpriority="high"` |
| Agency / Wholesale | Large transparent PNG heroes (~2.1–2.2 MB on disk) dominate transfer |

### Logo

`inc/template-tags.php`: logo always `fetchpriority="high"` (~21–41 KB WebP). **Measured:** 2–4 elements with `fetchpriority="high"` per page HTML — logo competes with heroes (observed count).

### Hero

| Item | Finding | Type |
|------|---------|------|
| Bundled flavors | ~9 slides (`GHAHGHAH_HERO_SLIDES_MAX` 12) | Source |
| `getimagesize()` per bundled mobile+desktop file | Up to **~18 FS image probes** per home request when bundled | Source (hypothesis of cost; not micro-benchmarked) |
| First slide `fetchpriority="high"`; others `loading="lazy"` in markup | Source — but LH still shows **many High priority downloads** | Measured conflict |
| No font/image preload in HTML | Measured (`preloadCount` 0 on home) | Measured |

### Large theme images (on disk)

See `large-images.txt`. Highlights **>1 MB:**

- agency / wholesale / single-article / factory / blog / products-archive / contact PNGs **1.4–2.2 MB**
- Duplicate checksum previously noted: `contact/corn-isolated-transparent.png` ≡ `products-archive/corn-hero-transparent.png`

**AVIF:** 0 files. **WebP:** 38 under theme images.

---

## 11. Fonts

| File | Bytes |
|------|------:|
| `YekanBakhFaNum-Regular.woff` | 37636 |
| `YekanBakhFaNum-SemiBold.woff` | 37772 |

| Topic | Finding |
|-------|---------|
| Format | WOFF only — **no WOFF2** |
| `font-display` | `swap` on all faces | Measured |
| Weights | 400 → Regular; **500/600/700 → same SemiBold file** (3 `@font-face` → one URL) | Source |
| Browser requests | Typically **one request per unique URL** (SemiBold shared) — hypothesis; cache should coalesce | Hypothesis |
| unicode-range | Not set | Source |
| Preload | Not present | Measured HTML |
| License | Must be confirmed before convert/subset/redistribute | Process note |

---

## 12. PHP and WordPress Runtime

| Topic | Finding | Type |
|-------|---------|------|
| Frontend includes | `functions.php` loads **~25** always-on `inc/*.php` (+ admin only in admin) | Source |
| Hero `getimagesize` | Bundled slides call `ghahghah_hero_file_image_data()` → `@getimagesize` per path | Source |
| Query volume | Not instrumented with Query Monitor (plugin not present) | **NOT RUN** |
| Autoload options | Not dumped (avoid secrets/noise) | **NOT RUN** detail |
| HTML TTFB | 0.8–2.2s median local | Measured |

---

## 13. REST Endpoint Findings

### `GET /wp-json/ghahghah/v1/live-search?q=`

| q | Status | Bytes | Items | Notes |
|---|-------:|------:|------:|-------|
| `ا` (1 char) | 200 | 52 | 0 | Min length 2 short-circuit |
| `اس` | 200 | 6792 | 8 | Full WP_Query |
| `اسنک` | 200 | 6789 | 8 | same |

- `permission_callback` → `__return_true` (public)
- **No Cache-Control** on response (beyond WP defaults)
- **No rate limiting** in code
- `X-Robots-Tag: noindex`
- `found_posts` exposed as `total`

### `GET /wp-json/ghahghah/v1/form-data`

- **14020 bytes**; keys: `provinces`, `cities` (**145**), `products` (9), `activities`
- Entire city list transferred each load — measured

Inquiry submit uses nonce (`InquiryRest`) — good; not load-tested.

---

## 14. Technical SEO Matrix

| Page | lang | dir | H1 | Title | Meta desc | Canonical | robots meta | OG | Twitter | JSON-LD |
|------|------|-----|---:|-------|-----------|-----------|-------------|----------------|---------|---------|---------|
| Home | fa-IR | rtl | **0** | yes | **0** | 1 | max-image-preview:large | 0 | 0 | **0** |
| Products | fa-IR | rtl | 1 | yes | 0 | **0** | max-image-preview | 0 | 0 | 0 |
| Single product | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Articles | fa-IR | rtl | 1 | yes | 0 | **0** | max-image-preview | 0 | 0 | 0 |
| Single article | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Wholesale | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Agency | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Contact | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Factory | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Privacy | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| FAQ | fa-IR | rtl | 1 | yes | 0 | 1 | max-image-preview | 0 | 0 | 0 |
| Search | fa-IR | rtl | 1 | yes | 0 | 0 | **noindex, follow**, max-image-preview | 0 | 0 | 0 |
| 404 | fa-IR | rtl | 1 | yes | 0 | 0 | max-image-preview (**no explicit noindex**) | 0 | 0 | 0 |

**Home H1 texts:** `[]` — confirmed empty.
**Source:** `front-page.php` H1 only in `elseif` when Hero+Featured+Factory+Steps+Collab+Articles all disabled.

---

## 15. Structured Data

- **No JSON-LD** blocks parsed on any page.
- Validity: N/A (absent).
- Recommendation phase must avoid duplicating Schema if an SEO plugin is added later.

---

## 16. Accessibility

| Check | Result | Type |
|-------|--------|------|
| Lighthouse A11y | Typically high (~97 home) | Measured |
| `lang` / `dir` | fa-IR / rtl | Measured |
| Landmarks | main=1; multiple header/nav counts (nested/header chrome) | Measured counts |
| Skip-like link | Pattern present in HTML heuristic | Observed |
| Image alt | LH image-alt pass on home | Measured |
| Empty links | 0 on sampled pages | Measured |
| Duplicate IDs | 0 on sampled pages | Measured |
| Drawer focus trap / Escape / reduced motion | **NOT RUN** (manual browser) | NOT RUN |
| Slider keyboard / announcements | **NOT RUN** | NOT RUN |
| Contrast / touch targets | Partial via LH only | Partial |

---

## 17. Security-Adjacent Performance Risks

| ID | Risk | Evidence |
|----|------|----------|
| SEC-PERF-001 | Public live-search → DB queries from 2+ char input, no rate limit | Code + probes |
| SEC-PERF-002 | form-data public full cities payload | 14 KB JSON |
| SEC-PERF-003 | SMS credentials options-backed (empty in repo) — OK for Git; protect admin | Prior audit |

---

## 18. PHPCS Baseline Reference

Executed earlier on same HEAD:

- **942 errors**, **426 warnings**, **165 files**
- Exit code 2
- **Not fixed** in this audit (explicit debt)

---

## 19. Findings by Priority

### P1

**SEO-001 — Home missing H1**
- Evidence: HTML parse H1 count 0; `front-page.php` gated fallback
- URL: `/`
- Impact: Weak document outline / SEO signal
- Proposed: Always expose exactly one H1 (e.g. visually styled in Hero or sr-only brand+intent) without waiting for all sections off
- Risk: Duplicate H1 if sections also add H1 later — verify
- Verify: re-parse HTML; LH SEO heading audits

**SEO-002 — Missing meta descriptions sitewide**
- Evidence: metaDescCount=0 all pages; LH meta-description=0
- Proposed: Theme `document_title`/`wp_head` descriptions **or** SEO plugin — not both
- Risk: Duplicate tags if plugin added carelessly

**SEO-003 — No Open Graph / Twitter / JSON-LD**
- Evidence: parse counts 0
- Proposed: Minimal Organization/WebSite + per-type schema via one owner (theme **or** plugin)

**SEO-004 — Archives without canonical**
- Evidence: products & articles archives `canonicalCount=0`
- Proposed: `rel_canonical` for CPT/blog archives

**PERF-001 — Home hero multi-banner High priority downloads**
- Evidence: LH network 8 High-priority hero WebPs
- File: `template-parts/hero/site-hero.php`, `hero.js`
- Proposed: Ensure only first active viewport source is high priority; defer offscreen slides’ bytes
- Verify: LH network priority + LCP

**PERF-002 — Home mobile NO_LCP**
- Evidence: 3× simulate + 3× devtools
- Impact: Cannot score/optimize LCP on primary mobile entry
- Proposed: Stabilize LCP element (single hero image with dimensions, no competing high-priority logos/banners); re-run LH

**PERF-003 — Agency mobile CLS 0.297**
- Evidence: median CLS
- Proposed: Reserve space for hero PNG / fonts; check late-loading imagery

**PERF-004 — Wholesale/Agency/Contact mobile lab LCP ≫ 2.5s**
- Evidence: medians 9–13s (Lantern) + multi-MB PNGs on disk
- Proposed: Compress/convert heroes; priority hints; reduce TTFB in prod separately

### P2

**PERF-005** — Multiple 1–2MB PNG assets in theme
**PERF-006** — Front-page CSS/JS not gated by section render flags
**PERF-007** — Hero `getimagesize` per request for bundled banners
**PERF-008** — WOFF without WOFF2 / no unicode-range
**PERF-009** — Logo always `fetchpriority=high` (2–4 highs/page)
**SEO-005** — 404 lacks explicit `noindex` (only max-image-preview)
**SEC-PERF-001/002** — live-search / form-data public cost

### P3

**PERF-010** — Duplicate corn PNG paths
**PERF-011** — robots.txt LH failure in wp-env (env, not theme)
**STYLE-001** — PHPCS 942/426 debt

---

## 20. Proposed Remediation Phases (no code in this commit)

1. **SEO correctness:** Home H1; archive canonicals; meta description strategy; decide Schema owner.
2. **LCP package:** Hero priority/bytes; home mobile LCP stability; agency/wholesale image weight; logo fetchpriority policy.
3. **Request hygiene:** Gate front CSS/JS; font WOFF2 (after license); optional critical CSS.
4. **Runtime:** Memoize hero dimensions; live-search rate limit/cache.
5. **Quality debt:** PHPCS backlog separately.

---

## 21. Checks Not Run

| Check | Reason |
|-------|--------|
| Query Monitor / SQL count | Plugin not installed; no install allowed |
| Manual keyboard/slider a11y | Timebox; LH only |
| Field INP / CrUX | No field data |
| Production CDN/cache headers | Local wp-env only |
| Full LHR JSON in Git | ~28MB; extracts committed; raw in `%TEMP%/ghahghah-lh-raw-2026-09-16` |
| JS/CSS lint npm scripts | Not defined |

---

## 22. Reproduction Commands

```bash
# URLs
# (use WP-CLI eval as in artifacts/discovered-urls.json)

# HTTP timing
curl.exe -sS -L -o NUL -w "%{http_code}|%{size_download}|%{time_starttransfer}|%{time_total}" http://localhost:8888/

# Lighthouse example (does not touch package.json if run from /tmp)
cd /tmp
npx --yes lighthouse@12.2.1 "http://localhost:8888/" \
  --output=json --output-path=./out.json \
  --chrome-path="C:/Program Files/Google/Chrome/Application/chrome.exe" \
  --chrome-flags="--headless=new --no-sandbox --disable-gpu" \
  --only-categories=performance,accessibility,best-practices,seo \
  --form-factor=mobile --screenEmulation.mobile --throttling-method=simulate

# REST
curl.exe -sS "http://localhost:8888/wp-json/ghahghah/v1/live-search?q=%D8%A7%D8%B3"
curl.exe -sS "http://localhost:8888/wp-json/ghahghah/v1/form-data"
```

---

*End of baseline. Next: remediation package only for confirmed P1s after review.*
