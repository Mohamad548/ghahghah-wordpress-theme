# Agency page CLS — 2026-09-16 (`:8898`)

**Branch:** `fix/perf-agency-cls`  
**Base:** `ef980de49384058d41c8a31da2c16a5473c5dcbf`  
**Worktree:** `E:/word pares/ghahghah-fix-perf-images`  
**Env:** `http://localhost:8898` (Compose `…c3316861…`) — not `:8888`  
**URL:** `http://localhost:8898/درخواست-نمایندگی/`  
**Tooling commit (separate):** `7c8db90` — smoke option snapshot/restore + browser image health states  

**Lab method (fixed Before/After):** Lighthouse **12.2.1**, mobile, `throttlingMethod=simulate`, Chrome headless new, cold cache per run, ×3 sequential. Artifacts: `docs/audits/artifacts/agency-cls/{before,after}/`.

Local lab results are **not** a production CWV guarantee.

---

## Verdict

| Metric (median ×3 mobile) | Before (this branch, recovered media) | After |
|---------------------------|----------------------------------------|-------|
| **CLS** | **0.276** | **0.000** |
| LCP (ms) | 4112 | 4135 |
| TBT (ms) | 0 | 0 |
| Performance score | 64 | 86 |

**Target CLS ≤ 0.1:** met in this lab (all three After samples = 0).

Wholesale **TBT** remains **UNRESOLVED** (no new evidence in this pass).

---

## Proven cause

Direct Chrome trace evidence (Before), not a CSS guess:

1. `RemoteFontLoaded` for Yekan Bakh FaNum Regular / SemiBold.
2. Hundreds of `LayoutInvalidationTracking` events with **`reason: "Fonts changed"`** (sample count ≈ 434), including agency content nodes.
3. One `LayoutShift` with **score / weighted_score = 0.276256…** matching Lighthouse CLS on all three Before runs.
4. **Impacted nodes** (moved — not the causal agent):
   - `div.ghahghah-agency-page__visual` — previousRect ≈ `(0,348,412×475)` → currentRect ≈ `(0,310,412×513)`
   - `p.ghahghah-agency-page__lead` — y `266 → 228` (height unchanged)
   - Additional nearby text nodes shifted with the same font reflow
5. Product `<img>` already had `width`/`height` (1122×1402); Lighthouse `unsized-images` was empty. Image health on agency: loaded OK (mobile/desktop).

**Causal agent:** late application of webfonts via `font-display: swap` in `ghahghah-theme/assets/css/fonts.css`, which reflows text metrics after first paint.

**Not used as blockers:**

- Lighthouse `layout-shifts` gatherer failed (`RootCauses` / `frame_sequence`) — browser trace + PerformanceObserver still usable.
- PerformanceObserver with `hadRecentInput` filtered out showed **CLS total 0** on a scrolled navigation; Lighthouse still reported observed CLS **0.276** from the font-swap shift in the lab trace. Trace, not the PO alone, drove the fix.

Before CLS **0.276** here is measured **after** uploads recovery on `:8898`. Do not treat older pre-recovery numbers as this stage’s Before.

---

## Fix (minimal)

| File | Change |
|------|--------|
| `ghahghah-theme/assets/css/fonts.css` | `font-display: swap` → **`optional`** on all four `@font-face` rules (proven shared file). |
| `ghahghah-theme/inc/assets.php` | Preload Regular + SemiBold `.woff` in `wp_head` so optional can still apply Yekan when files arrive in time. |

**Not done (by design):** fixed pixel heights, hiding content, delaying paint, deleting sections, Home slider / SEO / plugins / theme version bumps, agency PHP redesign.

After trace: **0** `LayoutShift` events, **0** `Fonts changed` invalidations.

---

## Numbers

### Before

| Run | CLS | LCP (ms) | TBT (ms) | Perf |
|-----|-----|----------|----------|------|
| 1 | 0.276256 | 4116.15 | 0 | 64 |
| 2 | 0.276256 | 4112.45 | 0 | 64 |
| 3 | 0.276256 | 4108.33 | 0 | 68 |
| **Median** | **0.276256** | **4112.45** | **0** | **64** |

### After

| Run | CLS | LCP (ms) | TBT (ms) | Perf |
|-----|-----|----------|----------|------|
| 1 | 0 | 4133.44 | 0 | 86 |
| 2 | 0 | 4135.20 | 0 | 86 |
| 3 | 0 | 4168.16 | 17.23 | 82 |
| **Median** | **0** | **4135.20** | **0** | **86** |

**Deltas (median):** CLS **−0.276**; LCP **+23 ms** (noise); TBT unchanged at 0.

---

## Validation

| Check | Result |
|-------|--------|
| Agency image health (browser) | mobile/desktop `ok`, failed=0 |
| Visual 390 / 768 / 1440 | RTL, Persian copy, product image decoded, form present (`visual-*-.png`) |
| Province → city (no SMS submit) | province change grew city options 1 → 6 |
| `scripts/smoke-wordpress.sh` | **27 PASS**; theme_mods / hero pack / media sync **verified PRESENT match** after restore |
| `git diff --check` | clean |
| `:8888` / `audit/performance-seo` / `fix/perf-images` | not modified |
| Uploads on `:8898` | still present (~515 files under uploads); home hero banners still served |

Compact evidence: `docs/audits/artifacts/agency-cls/shift-evidence.json`, `*/summary.json`, `*/trace-layout-shifts.json`. Full Chrome traces kept locally under `artifacts/agency-cls/*/report-0.trace.json` (large; not required in git).

---

## Tooling (prior commit on this branch)

1. **Smoke:** snapshot options with `--skip-themes --skip-plugins` before first theme load; distinguish missing / empty / read error; verify JSON + restore by re-read compare; no unverified “Restored” print; preserve initially-absent options.
2. **Browser image health:** pending / hidden / loaded / failed; scroll + wait for load/decode; real failures fail the process; other-viewport hidden images not counted as broken; no theme lazy-load changes.

---

## Limitations

- Lab-only (`localhost:8898`, simulated mobile throttling). Field CLS may differ.
- `font-display: optional` can keep the fallback font for the document lifetime if the face misses the short block window; preload reduces that risk on this env.
- LCP remains ~4.1s under simulate — out of scope for this CLS pass.
- Wholesale TBT: **UNRESOLVED**.
