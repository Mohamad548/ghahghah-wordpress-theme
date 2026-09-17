# Navigation / URL / Performance — follow-up (stabilize + coverage)

**Date:** 2026-09-17  
**Env:** `http://localhost:8898` (worktree `ghahghah-fix-perf-images`)  
**Branch:** `fix/perf-navigation`  
**Base:** `a8a3025569763b8923fb612eceb0ac871cc8df5d`  
**:8888:** not touched  

## Verdict

**PARTIAL — sitewide success is not declared.**

Timing suite is incomplete; INVALID/FAILED samples are preserved and excluded from PASS; Health/Redirect (clean run) and independent Smoke passed with proven restore.

## 1. Concurrent jobs stopped

Stopped overlapping `nav-sitewide-measure` PIDs (including a stray `pre-slug` process). Completed sample JSON kept on disk.

## 2. Env vs pre-Smoke snapshot

Compared live `:8898` to `.smoke-evidence/initial-*` captured before the disruptive Smoke:

| Check | Result |
|-------|--------|
| stylesheet | `ghahghah-theme` |
| ghahghah-core | active |
| wordpress-seo (Yoast) | active |
| `theme_mods_ghahghah-theme` | **equal** to pre-Smoke |
| `ghahghah_hero_banner_pack` | `flavour-optimized-1933x813-v1` (equal) |
| `ghahghah_theme_media_sync_version` | `0.9.60` (equal) |
| Hero slides | **9** |
| Menu locations | primary=2, mobile_bottom=3, footer=4, … (equal) |
| Redirect map | 53 entries (superset of apply-repair-rerun; no missing keys) |

**Restore actions:** none required (no Smoke-induced diffs vs snapshot). No general reset.

Artifact: `docs/audits/artifacts/nav-perf/env-stabilize/env-compare.json`

## 3. Smoke disruption window (contaminated measure)

| | UTC | Local (+0330) |
|--|-----|----------------|
| Start | `2026-09-17T09:57:49.803Z` | ~13:27:50 |
| End (theme restore confirmed) | `2026-09-17T10:07:21.697Z` | ~13:37:21 |

Sources: terminals 629806/629807 (Smoke) + 629809 (theme activate).

Samples classified by **file birthtime** (mtime was rewritten by later stamps):

| Class | Count | Handling |
|-------|------:|----------|
| **VALID** | 17 | Remain in `runs/post-slug/`; eligible for resume |
| **INVALID** | 29 | Marked `overlap-smoke-disruption-window`; copied to `invalid-kept/` (not deleted, not PASS) |
| **FAILED** | 15 | Measured errors; kept in `invalid-kept/`; must re-run |
| **NOT RUN** | (see coverage) | Never executed yet |

Index: `runs/post-slug/sample-validity-index.json`

**Query PASS note:** the two query-preservation checks in Health are valid **only for those redirect+query cases**, not a whole-site endorsement.

## 4. Health + Redirect (clean, alone)

After stabilize, ran `nav-url-health.cjs` → `docs/audits/artifacts/url-migration/health-clean/`:

| Suite | Pass | Fail |
|-------|-----:|-----:|
| Public coverage (22) | 22 | 0 |
| Redirect encodings (40) | 40 | 0 |
| Query preserve (2) | 2 | 0 |

Earlier contaminated Health (`health/`) is **not** used as evidence.

## 5. Scenario plan (before resume)

| Dimension | Value |
|-----------|-------|
| Unique destinations | 22 (sample-page/hello-world excluded) |
| Nav click targets | 9 |
| Devices | desktop, mobile |
| Methods | click, goto, reload |
| Cache modes | `cache-disabled`, `cache-enabled-prepared` |
| Repeats | 2 |
| Planned samples | **424** (72 click + 176 goto + 176 reload) |
| Duplicates removed | 0 at destination/nav URL level |

`scenario-plan.json` written under the run dir. Resume skips only `validity===VALID`; INVALID/FAILED re-queued.

## 6. Independent Smoke (separate window)

| | |
|--|--|
| Window | `2026-09-17T10:38:02Z` → `2026-09-17T10:46:15Z` |
| Result | **ALL SMOKE SCENARIOS PASSED** (27 assertions) |
| Restore | theme_mods / hero pack / media sync **verified PRESENT match** |
| Post-proof | stylesheet=ghahghah-theme; plugins=core+yoast; hero pack ok; **9 slides**; menu locs unchanged |

HTTP Health ≠ Smoke. Artifacts: `docs/audits/artifacts/nav-perf/smoke-independent/`

## Coverage counts (do not claim sitewide PASS)

From `coverage-table.json` at report time (measure resume may still be running):

### Sample-level

| VALID | INVALID | FAILED | active on disk | kept archive |
|------:|--------:|-------:|---------------:|-------------:|
| 17 | 29 | 15 | 17 | 44 |

### Scenario-cell level (dest × mode × device × cache)

| PASS | FAILED | INVALID | NOT RUN |
|-----:|-------:|--------:|--------:|
| 9 | 8 | 14 | 194 |

**Why PARTIAL:** planned 424 samples not all VALID; large NOT RUN for goto/reload matrix; INVALID/FAILED await re-run after Smoke window.

## TTFB (unchanged attribution)

Still **UNRESOLVED** for a single code fix: static ~13ms, minimal PHP ~1.4s, full WP ~4.5s, SQL ~33ms / ~69 queries. See `docs/audits/artifacts/nav-perf/ttfb-probe/`.

## Open SEO / Performance (preserved)

- Front H1 empty  
- Portable Yoast / meta / canonical  
- Full Performance final pass  
- Wholesale TBT / mobile NO_LCP (prior)  

This stage does not close those.

## Commits expected

1. Tooling: resume-VALID-only, smoke-window marker, coverage table  
2. This report (+ selected JSON summaries; no SQL / unrelated agency-cls)
