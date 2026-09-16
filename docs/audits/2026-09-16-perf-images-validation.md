# Perf Images validation — 2026-09-16

**Branch:** `fix/perf-images`  
**Base (image baseline):** `7add574586a6b1c636881efd1b237a38cd81363d`  
**Package commit under test:** `def0c87b467a5d7ed77432153684f57cc7999e8b`  
**Worktree:** `E:/word pares/ghahghah-fix-perf-images`  
**Test URL:** `http://localhost:8898`  
**Main env (untouched):** `http://localhost:8888`

Related package report: [`2026-09-16-perf-images.md`](./2026-09-16-perf-images.md)

---

## Branch status (kept separate)

| Branch | Tip (at validation time) | Notes |
|--------|--------------------------|-------|
| `fix/perf-images` | `def0c87` (+ validation commits below) | WebP defaults; PNGs retained |
| `audit/performance-seo` | `75229ac` | **Not** merged / cherry-picked into `fix/perf-images` |
| Main clone / `:8888` | left running; not stopped or mutated by smoke |

---

## 1. Smoke fix + A–D on `:8898`

### Cause
Smoke previously resolved WP-CLI ambiguously (`ghahghah` + `head -n 1`) and/or wrong `wp-env` binary (`wp-env@1.0.1` via npx). With both envs up, that risked driving `:8888`.

### Fix (`scripts/smoke-wordpress.sh`)
- Require local `@wordpress/env@11.15.0` from `node_modules` (`npm ci --include=dev`; **`package.json` / `package-lock.json` unchanged**).
- Select CLI by worktree hint `ghahghah-fix-perf-images` (unique), else mount-path match; fail if ambiguous.
- Gate on theme mount ⊂ this worktree, host port, and `siteurl`/`home` (must be `:8898` when mapped there).
- Skip `wp-env start` if this worktree’s containers already exist (avoids a second hash / port clash).
- Capture initial theme/plugin; restore on EXIT (success or failure); delete temp product posts.
- Harden PHP yes/no asserts with trailing newline.

### Evidence (both envs up)
Artifact: `docs/audits/artifacts/perf-images/smoke-validation.txt`

| Check | Result |
|-------|--------|
| `@wordpress/env` | **11.15.0** |
| CLI container | `wp-env-ghahghah-fix-perf-images-c3316861-cli-1` |
| WP container ports | `0.0.0.0:8898` |
| Theme mount | `...\ghahghah-fix-perf-images\ghahghah-theme` |
| siteurl/home | `http://localhost:8898` |
| Scenario A–D | **ALL PASSED** (26 assertions) |
| Restore | theme `ghahghah-theme` + `ghahghah-core` active |
| `:8888` | still HTTP 200; not deactivated |

---

## 2. Wholesale TBT (limited) — interleaved Before/After

**Scope:** Wholesale only. Lighthouse **12.2.1**, mobile, simulate, same Chrome path. No concurrent LH.  
**Switch method:** checkout image PHP paths from `7add574` ↔ `def0c87` on the mounted worktree (HTML asset refs verified per run).

Artifacts: `docs/audits/artifacts/perf-images/tbt-wholesale/`

### Interleaved samples (simulated TBT vs long-task proxy)

| Phase | Pair | benchmarkIndex | **Simulated TBT (ms)** | Obs LCP (ms) | Long-task blocking proxy Σ max(0,dur−50) | Top long-task label |
|-------|------|----------------|------------------------|--------------|-------------------------------------------|---------------------|
| before | 1 | 2158.5 | **0** | 2817 | (see extract) | page URL |
| after | 1 | 1348 | **0** | 2973 | ~646 | **Unattributable** (365 ms) |
| before | 2 | 970 | **0** | 4551 | | Unattributable |
| after | 2 | 1352.5 | **0** | 4225 | | Unattributable |
| before | 3 | 1349.5 | **0** | 3810 | | Unattributable |
| after | 3 | 1505.5 | **0** | 4252 | | Unattributable |

Observed `observedTotalBlockingTime` is **not present** in LH 12 metrics for these runs; observed LCP/load are recorded in `*.extract.json`. Simulated TBT audit value is the Lantern `total-blocking-time` numeric.

### Prior package run (for context — not interleaved)

| Phase | Run TBT (sim) |
|-------|----------------|
| Before | 261 / 237 / 161 |
| After | ~136 / **1057** / **934** |

Prior After run2: long-task **Unattributable ~921 ms**; main-thread **Other** elevated (~1221 ms) alongside Style & Layout.

### Trace (After, `--save-assets`)
File: `tbt-wholesale/after-trace/report-0.trace.json.gz`  
Analysis: `tbt-wholesale/trace-analysis.json`

Largest events in this quieter re-capture: **RasterTask** (~167 ms), **RunTask** (~159 ms), **EvaluateScript** on `blob:` (~159 ms), **Decode LazyPixelRef** (~156/122/109 ms). No ~921 ms Unattributable task reproduced.

### Verdict
**UNRESOLVED** whether the prior After TBT spike was caused by the WebP image change.

- Interleaved retest does **not** show After simulated TBT worse than Before (all **0 ms**).
- Prior spike remains unexplained; Unattributable alone is insufficient attribution.
- No image-related code fix applied (regression not proven on retest).

---

## 3. Visual comparison (desktop Before/After)

| Page | Before desktop final | After desktop final |
|------|----------------------|---------------------|
| wholesale | `desktop/before/wholesale-desktop-final.jpg` | `desktop/wholesale-desktop-final.jpg` |
| agency | `desktop/before/agency-desktop-final.jpg` | `desktop/agency-desktop-final.jpg` |
| contact | `desktop/before/contact-desktop-final.jpg` | `desktop/contact-desktop-final.jpg` |
| article | `desktop/before/article-desktop-final.jpg` | `desktop/article-desktop-final.jpg` |

Mobile Before/After finals already existed under `before/` and `after/`. Desktop Before was missing; now captured with baseline image PHP on `:8898`, then After PHP restored.

Visual review: hero layout/transparency intact; pack text readable; no layout break observed in finals.

---

## 4. PHPCS (Base vs HEAD, changed PHP only)

Tool: PHP_CodeSniffer **3.13.6**, ruleset `phpcs.xml.dist` (WordPress).  
Clean logs (no ANSI): `phpcs-changed.txt`, `phpcs-base-full.txt`, `phpcs-head-full.txt`, `phpcs/compare-summary.json`.

| | Errors | Warnings |
|--|-------:|---------:|
| Base `7add574` (5 files) | 47 | 78 |
| HEAD `def0c87` (5 files) | 47 | 73 |

Diff-aware check (messages on **changed lines** whose sniff+message set is new vs Base): **0 new debt**.

**Overall PHPCS status:** **FAIL** — existing baseline debt (incl. historical issues such as EOL / alignment / docblocks).  
**New debt from this package:** **NONE**.

Equal totals alone were not used as proof; sniff + message vs diff hunks were compared.

---

## 5. Other checks

| Check | Result |
|-------|--------|
| `git diff --check` on changed package paths | **PASS** (exit 0) |
| PHPUnit | **OK** (7 tests, 13 assertions) |
| `php scripts/verify-structure.php` | **PASS** |
| `package.json` / `package-lock.json` | **unchanged** |
| `:8888` + `:8898` HTTP | both **200** after tests |

---

## 6. Resolved vs unresolved

### Resolved
- Smoke env selection for this worktree; A–D green with both Docker projects running.
- Desktop Before screenshots completed.
- PHPCS baseline-vs-HEAD with new-debt analysis (clean log).
- Interleaved Wholesale TBT dataset + After Chrome trace captured.

### Unresolved
- Causal link from WebP swap → prior Wholesale After TBT spike (**UNRESOLVED**).
- Exact producer of prior ~921 ms Unattributable long task (not reproduced).

---

## Artifact index

- `artifacts/perf-images/smoke-validation.txt`
- `artifacts/perf-images/tbt-wholesale/` (extracts, analysis, LHR `.json.gz`, trace)
- `artifacts/perf-images/desktop/before/`
- `artifacts/perf-images/phpcs-changed.txt` (+ base/head full + compare)
- `artifacts/perf-images/phpunit-validation.txt`
- `artifacts/perf-images/verify-structure.txt`
- `artifacts/perf-images/git-diff-check.txt`
