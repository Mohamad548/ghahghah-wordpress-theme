# Navigation performance — Home → Products (2026-09-17)

**Branch:** `fix/perf-navigation`  
**Base:** `4f2a49158f242e66c56be864763268af70befdb1`  
**Worktree / env:** `E:/word pares/ghahghah-fix-perf-images` · `http://localhost:8898`  
**Preserved:** `:8888`, uploads, slider settings, Yoast Free 28.5, prior untracked artifacts (not staged)

Lab timings are not a production CWV guarantee. Absolute Document TTFB on this Docker/`wp-env` host stayed multi-second with high run-to-run variance; that layer was **not** “fixed” by speculative caching or prefetch.

---

## Verdict

| Question | Result |
|----------|--------|
| Are header vs footer/bottom Products links the same destination? | **Before: no.** Header → `/products/` (CPT archive). Footer + mobile bottom → `/محصولات/` (page ID 7). Both HTTP **200**, **no redirect** between them. |
| Extra redirect / wrong host (`:8888`) / slash / query? | **No** for these clicks. Host stayed `localhost:8898`. |
| Is pre-request JS the bottleneck? | **No.** Median click→Document request ≈ **12–36 ms**. |
| Where does time go? | **Document TTFB** (≈2.5–10+ s). FCP ≈ TTFB + ~100–200 ms. HTML body receive after headers is negligible. LCP = **INVALID** (not reported as another metric). |
| Does click feel slower than refresh of the *same* URL? | **Not proven.** For `/products/`, click / goto / soft-reload medians sit in the same noisy TTFB band. |
| What was fixed? | **Proven destination mismatch:** extend archive menu sync to `mobile_bottom` + `footer` (same menu ID 3). Legacy page `/محصولات/` remains published. |
| Absolute TTFB made fast? | **UNRESOLVED** (environment/PHP variance). No cache plugin / prefetch / architecture change added. |

---

## Environment snapshot

| Item | Value |
|------|--------|
| Active plugins | `ghahghah-core` 0.2.2, `wordpress-seo` 28.5 |
| Auth | logged-out |
| Throttling | none |
| Concurrent Lighthouse / heavy labs | none during measure suites |
| Server/object caches | not flushed between samples |
| `:8888` | still responding (preserved) |

Artifacts: `docs/audits/artifacts/nav-perf/env/`

### Out-of-Git env change (menu DB)

Deploying the theme code runs `ghahghah_products_archive_after_nav_sync` on `init` (priority 35) and rewrites the top-level «محصولات» item on locations `primary`, `mobile_bottom`, and `footer` to `post_type_archive` / `ghahghah_product` when it is still a page link.

**Reproduce on another `:8898`-like env:**

1. Pull this branch / deploy theme.
2. Load any front-end URL once (or wait for `init`).
3. Confirm: `wp menu item list 3 --fields=db_id,title,type,object,url` shows محصولات as `post_type_archive` → `/products/`.
4. Page ID 7 (`/محصولات/`) stays published — do not delete for this fix.

---

## 1) Real link paths (DOM)

Harness: `scripts/nav-perf-link-audit.cjs`  
Before log: `docs/audits/artifacts/nav-perf/links/link-audit-run.txt`  
After JSON: `docs/audits/artifacts/nav-perf/links/link-audit-after.json`

| Surface | Before href (Products parent) | After |
|---------|-------------------------------|--------|
| Desktop header | `http://localhost:8898/products/` | unchanged |
| Mobile drawer (primary) | `/products/` (+ flavor children) | unchanged |
| Mobile bottom nav | `…/%d9%85%d8%ad%d8%b5%d9%88%d9%84%d8%a7%d8%aa/` (`/محصولات/`) | `/products/` |
| Footer (same menu as bottom) | `/محصولات/` | `/products/` |

### Document chains (no redirect)

| URL | Status | Final | Template signal |
|-----|--------|-------|-----------------|
| `/products/` | 200 | same | `post-type-archive-ghahghah_product`, H1 «همه محصولات قهقهه» |
| `/محصولات/` (percent-encoded) | 200 | same | `page-id-7`, H1 «محصولات» |

`sameFinal: false` — **two different pages**, not one destination with a redirect.

Captured HTML: `links/products-archive.html`, `links/products-page.html`.

---

## 2) Method (reproduction)

Script: `scripts/nav-perf-measure.cjs`  
Suites: `docs/audits/artifacts/nav-perf/runs/before/`, `…/after/`

| Mode | How |
|------|-----|
| **Click** | Real mouse click on link bounding-box center (not `page.goto` as substitute). Desktop: header Products. Mobile: bottom-nav Products. |
| **Goto** | Direct `page.goto` of the click’s final destination URL. |
| **Reload** | Soft `page.reload` after a prior load of that URL (not Hard Reload). |

Conditions:

- **Cold:** new browser each sample; CDP `Network.setCacheDisabled=true`.
- **Warm:** cache enabled; measured goto after a warm-prep load; reload after prior load of same URL.
- **×3** sequential repeats per cell; medians reported.
- Timings from CDP Document events + Navigation Timing on the **destination** document (do not subtract `performance.now` across two documents).
- LCP recorded only when the browser reports a valid LCP entry; otherwise **INVALID**.

Goto/reload targets are derived from observed click `href`s. After the menu sync, only `/products/` remained in that set (legacy page still measurable via direct URL; before-suite already covered it).

---

## 3) Before medians (ms)

Source: `runs/before/summary.json`

| Cell | click→req | redirect | TTFB | FCP | LCP | Final |
|------|-----------|----------|------|-----|-----|-------|
| click desktop cold `/products/` | 18 | 0 | **6244** | 6408 | INVALID | archive |
| click desktop warm `/products/` | 19 | 0 | **3319** | 3416 | INVALID | archive |
| click mobile cold `/محصولات/` | 18 | 0 | **6100** | 6220 | INVALID | page-7 |
| click mobile warm `/محصولات/` | 12 | 0 | **2815** | 2868 | INVALID | page-7 |
| goto cold `/products/` | 9 | 0 | 3497 | 3676 | INVALID | archive |
| reload cold `/products/` | 7 | 0 | 6588 | 6748 | INVALID | archive |
| goto warm `/products/` | 13 | 0 | 10275 | 10448 | INVALID | archive |
| reload warm `/products/` | 12 | 0 | 6431 | 6544 | INVALID | archive |
| goto cold `/محصولات/` | 11 | 0 | 3562 | 3784 | INVALID | page-7 |
| reload cold `/محصولات/` | 10 | 0 | 5562 | 5704 | INVALID | page-7 |
| goto warm `/محصولات/` | 6 | 0 | 2698 | 2752 | INVALID | page-7 |
| reload warm `/محصولات/` | 8 | 0 | 2562 | 2612 | INVALID | page-7 |

**Read of the table:** pre-click delay is tens of ms; FCP tracks TTFB. Click is not systematically worse than goto/reload for the same URL. Mobile click before landed on the **lighter page**, which can feel “faster” on warm reload of that page while desktop click always hit the archive — a UX mismatch, not a click-handler bug.

Click destination dump: `links/before-click-destinations.json`.

---

## 4) Diagnosis (separated)

| Layer | Finding |
|-------|---------|
| **Menu / content** | Footer + bottom shared menu #3 still pointed at page 7 while primary already used CPT archive. Theme already synced **primary** only. |
| **Redirect** | Not involved for these two URLs. |
| **JS before request** | Not the delay (~20 ms). |
| **TTFB / PHP** | Dominant. Curl samples on the same host also show multi-second `time_starttransfer` with large spread (`env/curl-ttfb-samples.txt`). Consistent with Docker/`wp-env` PHP variance under load, not with a navigation-specific client path. |
| **Render after HTML** | Secondary; FCP closely follows TTFB. |
| **Speculative “fixes” rejected** | No page-cache plugin, no link prefetch, no SPA architecture — would not address a proven cause uniquely tied to click vs refresh. |

---

## 5) Code fix (minimal, proven)

**File:** `ghahghah-theme/inc/products-archive-settings.php`

- Generalize sync to `ghahghah_sync_products_archive_menu_link( $location )`.
- Call for `primary`, `mobile_bottom`, and `footer` on `init` 35.
- Keep `ghahghah_sync_primary_products_archive_link()` as a thin wrapper.
- **Do not** unpublish `/محصولات/` or remove any redirect that was not shown to be harmful.

### After medians (ms) — same harness

Source: `runs/after/summary.json`

| Cell | click→req | TTFB | FCP | Final |
|------|-----------|------|-----|-------|
| click desktop cold `/products/` | 28 | 8551 | 8788 | archive |
| click desktop warm `/products/` | 36 | 4674 | 4840 | archive |
| click mobile cold `/products/` | 25 | 6431 | 6612 | archive |
| click mobile warm `/products/` | 24 | 5727 | 5844 | archive |
| goto cold `/products/` | 10 | 8165 | 8416 | archive |
| reload cold `/products/` | 10 | 3036 | 3184 | archive |
| goto warm `/products/` | 7 | 6090 | 6156 | archive |
| reload warm `/products/` | 8 | 2901 | 2980 | archive |

**Before → After for the proven issue:** mobile bottom (and footer) now open the **same** CPT archive as the header (`/products/`, H1 «همه محصولات قهقهه»). TTFB numbers did **not** improve in a way that beats host noise; after cold medians are sometimes higher — expected variance, not a regression attributed to the menu type change alone.

### Functional health (not full smoke)

- `/products/` → 200, `post-type-archive-ghahghah_product`
- `/محصولات/` → 200, `page-id-7` still reachable
- After suite: 12/12 clicks landed on archive URL with archive H1
- `:8888` still 200

This HTTP spot-check is **not** a full Smoke suite.

---

## 6) Measurement limits

- n=3 per cell; TTFB CV is large on this host — medians for absolute speed are weak evidence.
- CDP sometimes marks Document `fromCache` while Navigation Timing still shows multi-second TTFB / non-zero `transferSize`; interpret cache flags cautiously.
- Soft reload ≠ Hard Reload; cold browser cache ≠ flushed opcode/object caches.
- LCP left INVALID; never aliased to FCP.
- Suite derives goto/reload targets from click hrefs; after sync, legacy page is only in the before suite + direct curl.

---

## Open SEO follow-ups (carry-forward)

Not in scope of this navigation fix; track separately:

1. **H1** when all front-page sections are off **and** the static front page body is empty.
2. **Portable** Yoast/settings configure script for non-`:8898` envs.
3. **Single** product/meta descriptions and **canonical** validation for archives.
4. **Full Smoke** suite (version detection / path coverage) — not claimed here.

---

## Deliverables

| Path | Role |
|------|------|
| `scripts/nav-perf-link-audit.cjs` | DOM href + Document chain audit |
| `scripts/nav-perf-measure.cjs` | click / goto / soft-reload timing |
| `ghahghah-theme/inc/products-archive-settings.php` | archive menu sync for bottom + footer |
| `docs/audits/artifacts/nav-perf/**` | evidence |
| `docs/audits/2026-09-17-navigation-performance.md` | this report |
