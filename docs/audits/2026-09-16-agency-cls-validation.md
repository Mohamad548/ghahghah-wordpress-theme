# Agency CLS validation — font paint + image QA — 2026-09-16

**Branch:** `fix/perf-agency-cls`  
**Parent HEAD:** `08d9e3796031983dcb64828b815526d73152b5bd`  
**Worktree / env:** `E:/word pares/ghahghah-fix-perf-images` · `http://localhost:8898`  
**Before fonts (temporary checkout only):** `ef980de` `fonts.css` + `assets.php` (swap, no preload) — restored to HEAD after probes  
**`:8888` / uploads / 9 hero slides:** unchanged (`slides=9`, pack `flavour-optimized-1933x813-v1`)

Method notes:

- Painted font proof = Chrome CDP **`CSS.getPlatformFontsForNode`** (`familyName`, `isCustomFont`).  
- `computed font-family` and `document.fonts.check` recorded but **not** treated as paint proof.  
- Slow network (recorded): download/upload **64000 B/s**, latency **400 ms** (`slow-3g-ish`).  
- Cold = cache disabled; warm = second navigation with cache allowed.

---

## Measured

### 1) Design font paint (Agency / Home / Wholesale)

| Scenario | Before (`ef980de` swap) | After (HEAD `optional` + preload) |
|----------|-------------------------|-------------------------------------|
| Agency title/body cold+warm mobile & desktop | **Yekan Bakh FaNum** custom | **Yekan Bakh FaNum** custom |
| Agency title box (mobile cold) | 33.6px / 3 lines / **h=77.25** | identical |
| Agency lead box (mobile cold) | 16.8px / 2 lines / **h=62.125** | identical |
| Home & Wholesale text (cold/warm) | Yekan painted | Yekan painted |
| Mobile bottom menu (`.gg-bottom-nav__label`) | Yekan when resolved | Yekan when resolved |
| **Slow-cold mobile** (title/body/menu) | **Yekan still painted** (late swap) | **Tahoma fallback** for document lifetime |

**Agency Before vs After (fast path):** no change in font-size, line-box count, or title/lead height/width for cold mobile/desktop samples — design metrics match.

**Slow network (explicit):** with `font-display: optional`, glyphs that miss the short block window stay on **Tahoma** even though `font-family` still lists Yekan. Visual difference vs Before: Before still ends on Yekan (swap) but that path was the proven CLS source (0.276). After keeps layout metrics for Agency title (same 33.6px / 3 lines / h=77.25) while accepting Tahoma shapes under Slow 3G-ish. This is expected `optional` behavior, not a lab CLS regression.

Artifacts:

- `docs/audits/artifacts/agency-cls/font-validate/{before-ef980de,after}/font-render-report.json`
- `docs/audits/artifacts/agency-cls/font-validate/compare-summary.json`
- Screenshots under the same folders (`*-cold.png`, `*-slow-cold.png`, …)

### 2) Shared-change impact (Home / Wholesale)

| | Before | After |
|--|--------|-------|
| Home font requests (cold) | **2** | **2** |
| Wholesale font requests | **2** | **2** |
| `rel=preload as=font` | **0** | **2** (Regular + SemiBold once each) |
| Duplicate preloads | 0 | **0** |
| Unused preloads (preloaded URL never requested) | n/a | **0** |

No repeatable regression found (no duplicate/unused preload; request count unchanged). **No production follow-up.** Agency CLS ×3 not re-run (font production files unchanged in this validation pass).

Artifacts: `docs/audits/artifacts/agency-cls/font-impact/{before-ef980de,after}/impact.json`

### 3) Browser image health (after QA fix)

`scripts/browser-image-health.cjs` re-run on `:8898`:

- All listed pages mobile+desktop **ok**
- Home: **slides=9**, **distinct=9**, each slide DOM `loaded` via real next control
- No `loading`/`src`/`srcset` mutation; fetch/`createImageBitmap` only as `fileHealth` (does not promote DOM status)

Artifact: `docs/audits/artifacts/agency-cls/browser-health-fixed/browser-image-health.json`

---

## Fixed

1. **False-positive image QA** in `scripts/browser-image-health.cjs`:
   - Removed promoting stalled DOM images to `loaded` via fetch/bitmap.
   - Removed test-only `loading=eager` / `src` rewrite.
   - Home **requires** exactly 9 slides + 9 distinct visited srcs; missing slider ⇒ fail.
2. Added measurement helpers (not production): `scripts/font-render-validate.cjs`, `scripts/font-impact-probe.cjs`.

---

## Unresolved / out of scope

| Item | Status |
|------|--------|
| Wholesale **TBT** (older finding) | **UNRESOLVED** — not re-audited |
| Slow-network Tahoma under `optional` | **Documented expected tradeoff** — no production change without a design-breaking repro on normal/local networks (none found on cold/warm) |
| Full multi-page Lighthouse re-audit | Not required this pass |
| Changing `font-display: optional` back to `swap` | **Not done** — would reintroduce proven agency CLS |

---

## Production code

**No theme CSS/PHP changes in this validation pass.** HEAD font strategy (`optional` + single preload pair) retained.
