# Baseline Validation Addendum — 2026-09-16

**Branch:** `audit/performance-seo`
**Docs commit parent (production baseline HEAD):** `924b087796c79b91dd153d7594530132800b5a6c`
**Initial audit commit:** `2ef8e0871d80fc755b3cfb53f1b9b6efe6810179`
**Scope:** Documentation / evidence only. No production PHP/CSS/JS/image/font changes in this commit.
**Primary corpus:** full LHR JSON in `%TEMP%/ghahghah-lh-raw-2026-09-16/` (not deleted).
**Extractor:** `docs/audits/artifacts/baseline-validation/extract-validity.cjs`

Related baseline report (preserved, numbers not rewritten):
`docs/audits/2026-09-16-performance-seo-baseline.md`

---

## 0. Preflight notes

| Check | Result |
|-------|--------|
| Branch | `audit/performance-seo` |
| Expected HEAD at start | `2ef8e08…` matched |
| Committed diff vs `924b087` | **only** `docs/audits/**` |
| AGENTS.md | **not present** in repo |
| Working tree at validation time | **DIRTY (preserved, not committed)** — local image WebP swaps + PHP edits under `ghahghah-theme/` unrelated to this docs commit |

Dirty paths observed (left untouched):

- Deleted PNGs under agency / wholesale / single-article
- New `*_optimized.webp` counterparts
- Modified: `functions.php`, `sync-theme-media.php`, `request-pages-settings.php`, `single-article-settings.php`, `single-post.php`

Validation re-processing used the **original TEMP LHRs**. Sequential Home-mobile diagnosis hit the live wp-env theme (may include dirty assets for non-home pages); Home hero path still reproduced `NO_LCP`.

---

## 1. Confirmed / Corrected / Unresolved

### Confirmed

- Lighthouse **12.2.1**, HeadlessChrome **152**, Node **v20.20.2** (LH13 would need Node ≥22.19 — not used; project Node unchanged).
- 16 page×device cells present; 3 runs each; batch was **sequential** (`overlapSuspect: false`).
- Home mobile **NO_LCP** is real in JSON (`scoreDisplayMode: error`, `errorMessage: NO_LCP`), not a median-math artifact.
- Page **does paint**: final screenshots show hero/content; FCP is valid.
- Form pages’ dominant bytes are theme PNGs (~1.5–2.2 MB transfer ≈ resource size).
- Single-article ~8 MB (mobile) / ~12 MB (desktop) is **transferSize** (network), nearly equal to resourceSize — uncompressed PNG weight, not inflated decoded-only accounting.
- Explicit HTML `fetchpriority="high"` on Home = **3** (2 logos + first hero slide) — not “8 attributes”.
- Network `priority: High` on many banners is **browser priority**, distinct from HTML attributes.
- No SEO plugin active → missing meta/OG/Schema is a **site SEO configuration gap**, not an automatic theme defect requiring parallel tags.
- Real 404 without extra `noindex` is **not** by itself a P0/P1 theme defect.

### Corrected (interpretation vs initial report)

| Topic | Initial framing | Validated framing |
|-------|-----------------|-------------------|
| Missing LH13 insight keys | Could read as gatherer failure | **Expected on LH 12.2.1**; insights arrive in LH13 ([Chrome blog](https://developer.chrome.com/blog/lighthouse-13-0)) |
| `lcpElement: null` in summary | “gatherer failed” broadly | Key `largest-contentful-paint-element` **exists** but errors with `RootCauses` / `frame_sequence` on Chrome 152 — tooling failure, not missing key |
| “Multiple hero banners fetchpriority=high” | Implied HTML attrs | **Only slide 0** has the attribute; others are lazy in markup but still downloaded with Network High |
| Home mobile NO_LCP as “LCP problem = slow” | Grouped with slow LCP pages | **Measurement blocker**; speed not proven by LCP metric (metric absent) |
| Article 8–12 MB | Ambiguous | **Transferred bytes** of PNGs |
| PHPCS as P3 style | Blanket | **PENDING** sniff-type security split — no detailed sniff triage in this pass |
| CSS file count alone | Implied bundling need | Count is signal only; need render-blocking / critical-path proof before bundling mandate |
| live-search/form-data “security” | Risk-ish wording | Public endpoints = **cost / abuse-control** concern, not proven vuln by openness alone |

### Unresolved

- **Single root cause of Home mobile NO_LCP** (see §3) — status `UNRESOLVED` with strong evidence of missing LCP candidates in trace.
- **Agency CLS culprits (selectors)** — `layout-shifts` audit also dies on `RootCauses`/`frame_sequence`; numeric CLS median **0.297** remains valid; element-level culprits not extractable from existing LHR.
- Exact LCP DOM selector for form pages — same TraceElements failure; **resource URL** inferred from largest High-priority image + screenshot.

---

## 2. Tooling & median policy

| Item | Value |
|------|--------|
| Lighthouse | 12.2.1 (all 48 baseline runs) |
| Chrome | HeadlessChrome/152.0.0.0 |
| Node (host / extraction) | v20.20.2 |
| Throttling | mobile: simulate (Lantern) + separate home-mobile-devtools; desktop: desktop preset |
| Concurrent batch? | **No** — fetch timeline shows sequential runs |
| Median policy (this validation) | Only **valid** numeric samples; `NO_LCP` / `scoreDisplayMode=error` excluded; never coerced to 0 |
| Units | LCP/FCP/TBT/SI: ms; CLS: unitless; transfer: bytes |

Insight keys `cls-culprits-insight`, `lcp-discovery-insight`, `lcp-phases-insight`, `image-delivery-insight`: **absent** (LH13). Present LH12 diagnostics include `largest-contentful-paint-element`, `layout-shifts`, `prioritize-lcp-image` — many error due to RootCauses.

---

## 3. Validity matrix (16 cells)

Full machine-readable table: `artifacts/baseline-validation/validity-matrix.json`.

| Cell | LCP valid n/3 | Median LCP (valid only) | Perf median (valid) | Notes |
|------|--------------:|------------------------:|--------------------:|-------|
| home:mobile | **0** | **null (NO_LCP)** | **null** | All 3 error `NO_LCP` |
| home:desktop | 3 | 2610 ms | 83 | OK |
| products_archive:mobile | 3 | 2909 ms | 91 | OK |
| products_archive:desktop | 3 | 828 ms | 94 | OK |
| single_product:mobile | 3 | 2023 ms | 94 | OK |
| single_product:desktop | 3 | 553 ms | 94 | OK |
| articles_archive:mobile | 3 | 3749 ms | 81 | OK |
| articles_archive:desktop | 3 | 951 ms | 94 | OK |
| single_article:mobile | 3 | 1865 ms | 92 | Heavy transfer |
| single_article:desktop | 3 | 539 ms | 94 | Heavy transfer |
| wholesale:mobile | 3 | 12821 ms | 69 | PNG hero |
| wholesale:desktop | 3 | 2305 ms | 82 | PNG hero |
| agency:mobile | 3 | 13115 ms | 54 | PNG + CLS 0.297 |
| agency:desktop | 3 | 2353 ms | 84 | PNG hero |
| contact:mobile | 3 | 9074 ms | 70 | PNG hero |
| contact:desktop | 3 | 1790 ms | 86 | PNG hero |

Home-mobile **devtools** corpus (3 extra TEMP files) also NO_LCP — not averaged into the table above as a 4th form-factor.

---

## 4. Home mobile NO_LCP

### Evidence

| Source | Result |
|--------|--------|
| Baseline TEMP `home-mobile-run{1,2,3}.json` | LCP error `NO_LCP`; FCP ok; screenshot shows cheese hero |
| Diagnosis 3× sequential simulate | Same NO_LCP; `observedLCP` null |
| Diagnosis 1× devtools + `--save-assets` | Same; trace kept (compact extract) |
| Trace compact | **0** `LargestContentfulPaint::Candidate`; multiple `NavStartToLargestContentfulPaint::Invalidate` |
| Console errors (baseline) | none |
| Hero CSS | inactive slides `opacity:0; visibility:hidden`; active `.is-active` visible |

### Verdict

**PARTIALLY_RESOLVED / root cause UNRESOLVED.**

We can state with evidence:

1. The lab metric fails because Chrome never commits an LCP candidate (Invalidate without Candidate).
2. The page is not a blank failure — users see content (screenshot + FCP).
3. Therefore NO_LCP is a **CWV measurement / candidate-eligibility** problem, not proof of “blank page” or a numeric slow LCP.

Hypotheses (not proven): opacity/visibility carousel model; HeadlessChrome 152 × LH 12.2.1 pipeline; early `inert`/`aria-hidden` mutations in `hero.js`.

Artifacts: `artifacts/baseline-validation/home-mobile-nolcp-diagnosis.json`, `home-mobile-diagnosis/*`.

---

## 5. Form pages — LCP resources & Agency CLS

Element selector from `largest-contentful-paint-element`: **unavailable** (RootCauses `frame_sequence`).

### Inferred LCP resource (network + screenshot)

| Page | Sim LCP (run2) | Observed LCP | Dominant image (transfer) | Path |
|------|---------------:|-------------:|---------------------------:|------|
| Wholesale mobile | 12824 ms | 4431 ms | 2 177 723 B | `…/wholesale/wholesale-hero-pizza-transparent.png` |
| Agency mobile | 13115 ms | 4165 ms | 2 224 079 B | `…/agency/parsley-onion-pack-composition-transparent.png` |
| Contact mobile | 9090 ms | (present in metrics) | 1 514 199 B | `…/contact/corn-isolated-transparent.png` |

Lantern phases (agency run2 metrics): TTFB ~3830 ms; `lcpLoadStart` ~12120; `lcpLoadEnd` ~12632 — **local TTFB dominates**; image decode/download still multi-MB.

Templates: `template-parts/request/page-wholesale.php`, `page-agency.php`, `template-parts/contact/page-contact.php` (each sets `fetchpriority="high"` on hero).

### Agency CLS

- Median CLS **0.297** (3/3 valid) — confirmed numeric failure vs 0.1 target.
- Culprit nodes: **UNRESOLVED** (`layout-shifts` audit error on RootCauses).
- Screenshot shows large hero PNG + chrome; fonts also load — culprits not asserted without working shift audit/trace.

---

## 6. Single article — heavy resources

| Metric (mobile run2) | Bytes |
|----------------------|------:|
| Sum **transferSize** | **8 211 896** (~7.8 MiB) |
| Sum **resourceSize** | **8 385 380** |

Top transfer images (all started before observed LCP on mobile run2):

1. `article-inline-hands-snack-corn.png` — 2 208 461
2. `article-inline-factory-line-real-snack.png` — 2 119 457
3. `article-hero-banner-real-snack.png` — 2 059 667
4. `corn-and-real-snacks-transparent.png` — 1 466 636

Theme directory: `ghahghah-theme/assets/images/single-article/`.
Related code: `single-post.php`, `inc/single-article-settings.php` (WIP dirty tree may already point at WebP — **not** part of baseline commit).

Desktop transfer total ~12.1 MB includes additional decorative PNGs (`decorative-snack-cluster-transparent.png`, `bowl-of-real-snacks-transparent.png`).

---

## 7. Hero fetchpriority (corrected)

| Signal | Measured |
|--------|----------|
| Explicit `fetchpriority="high"` in Home HTML | **3** (logo×2 + first slide) |
| Hero `<img class="ghahghah-hero__image">` count | 9 |
| First slide | cheese banner; has `fetchpriority="high"`; others `loading="lazy"` in PHP |
| Network High banner downloads (desktop run2) | **9** flavour WebPs still requested |

**Do not** treat Network High count as HTML attribute count.

---

## 8. Classification adjustments (for remediation planning)

| ID | Keep? | Adjusted priority / note |
|----|-------|---------------------------|
| SEO-001 Home H1=0 | Yes | P1 — still measured |
| PERF-002 Home mobile NO_LCP | Yes | P1 as **measurement blocker**; do not claim a ms LCP regression until candidates exist |
| PERF-001 multi-banner bytes | Yes | P1/P2 — reframe: lazy slides still download; opacity stack |
| PERF-003 Agency CLS | Yes | P1 numeric; culprits UNRESOLVED |
| PERF-004 form LCP | Yes | P1 lab; heavy PNG + local TTFB |
| SEO-002/003 meta/OG/Schema | Keep as product need | Prefer **one** SEO owner (plugin or theme) — no parallel theme Schema in first fix pack |
| SEO-005 404 noindex | Downgrade | Not a standalone defect |
| SEC-PERF live-search/form-data | Reframe | Cost / rate-limit / cache — not “vuln because public” |
| STYLE-001 PHPCS | PENDING | Do not label all as cosmetic |
| CSS count → bundle | Not sufficient alone | Gate on critical path evidence |

---

## 9. Proposed first remediation package (docs-only recommendation)

Ordered for validated P1s; implement later on a clean tree from `924b087` / audit branch tip **without** mixing dirty WIP unless intentionally rebased.

| Step | Change | Files | Before/after verification |
|------|--------|-------|-----------------------------|
| A | Always emit exactly one Home `<h1>` when sections render | `front-page.php` (+ hero/partial if visual H1) | HTML parse H1 count=1; LH SEO headings |
| B | Hero LCP eligibility: ensure first slide remains a stable LCP candidate (opacity/visibility model / reduce Invalidate); defer off-slide bytes | `hero.css`, `hero.js`, `site-hero.php` | Home mobile LH: LCP numeric present (n≥3); Candidate events in trace; banner request count before LCP ↓ |
| C | Replace/compress form + article PNGs (WebP/AVIF) **after** license/media workflow | theme image assets + request/contact/single-article templates/settings | transferSize of hero/article images ↓; form mobile LCP ↓; article total transfer ↓ |
| D | Logo `fetchpriority` policy (avoid competing High) | `inc/template-tags.php` | Network High count; LCP resource = intended hero |
| E | SEO ownership decision (plugin vs theme) for meta/canonical archives | config / optional plugin — **not** duplicate theme tags | Archive canonical present; meta description present once |

Out of first pack: PHPCS mass fix, CSS bundling-only, installing Yoast/WP Rocket (explicitly deferred).

---

## 10. Reproduction

```bash
# Reprocess TEMP LHRs (does not call network)
node docs/audits/artifacts/baseline-validation/extract-validity.cjs

# Home mobile diagnosis (cwd /tmp so package.json untouched)
npx --yes lighthouse@12.2.1 "http://localhost:8888/" \
  --output=json --output-path="E:/word pares/ghahghah-wordpress theme/docs/audits/artifacts/baseline-validation/home-mobile-diagnosis/manual.json" \
  --chrome-path="C:/Program Files/Google/Chrome/Application/chrome.exe" \
  --chrome-flags="--headless=new --no-sandbox --disable-gpu" \
  --only-categories=performance --form-factor=mobile --screenEmulation.mobile \
  --throttling-method=simulate
```

Checksums: `artifacts/baseline-validation/ARTIFACT_CHECKSUMS.json`.
Full raw LHR directory (local): `C:/Users/Mohamad/AppData/Local/Temp/ghahghah-lh-raw-2026-09-16/`.

---

## 11. Checks not re-run

- Full 48-run Lighthouse matrix (not required)
- PHPUnit / Smoke
- Query Monitor
- Field INP / CrUX
- Manual keyboard a11y
- PHPCS sniff-by-sniff security triage (PENDING)
