# Nav measure validity / resume / coverage fix

**Date:** 2026-09-17  
**HEAD (post-fix):** see git  
**Env:** `:8898` unchanged (no Smoke re-run; theme/plugins/DB not modified)

## Tool changes

1. **`scripts/lib/nav-measure-meta.cjs`**  
   - Records `toolVersion`, `effectiveCodeSha` (measure scripts only), `settingsFingerprint`  
   - Sample validity from **provable** `startedAt`/`endedAt` (or stamped run-log windows)  
   - Full sample-window overlap with Smoke disruption → `INVALID`  
   - Unprovable windows → `UNKNOWN` (not final results)  
   - **File birthtime is not VALID proof**  
   - `markedAt` is classification time only  

2. **`nav-sitewide-measure.cjs`**  
   - Logs `ISO-UTC kind url device cacheMode #n`  
   - Writes `startedAt` / `endedAt` on every sample  
   - Resume only via `isResumeCompatible` (explicit `VALID` + matching tool/settings/scenario)  
   - Doc-only edits do not change `settingsFingerprint` / `effectiveCodeSha`  

3. **`nav-mark-invalid-smoke-window.cjs`**  
   - Reclassifies with the rules above; archives to `invalid-kept/` / `unknown-kept/`  

4. **`nav-coverage-table.cjs`**  
   - Active VALID overrides archive for the same scenario key  
   - Failure history kept separately  
   - Cell `COMPLETE` only when VALID repeats ≥ plan; else `PARTIAL` / `FAILED` / `INVALID` / `UNKNOWN` / `NOT RUN`  
   - Overall status + counts from **scenario-plan** (no hardcoded 424 / PARTIAL)  
   - Out-of-plan samples listed separately from `NOT RUN`  
   - `COMPLETE` ≠ good performance  

## Unit tests

`node scripts/tests/nav-measure-meta.test.cjs` — **11 PASS**

## Reclassification result (post-slug)

| Class | Count | Notes |
|-------|------:|-------|
| VALID | 0 | Prior “VALID” lacked provable execution windows |
| INVALID | 0 | None with provable overlap after re-rule |
| FAILED | 17 | Archived under `invalid-kept/` |
| UNKNOWN | 46 | Archived under `unknown-kept/`; excluded from final PASS |

Smoke window (unchanged): `2026-09-17T09:57:49.803Z` → `2026-09-17T10:07:21.697Z`

## Coverage (from scenario-plan)

| measureStatus | PARTIAL |
|---------------|---------|
| Reason | `completeCells=0/212; validSamples=0/424` |
| Cell COMPLETE | 0 |
| Cell NOT RUN | 181 |
| Cell UNKNOWN | 23 |
| Cell FAILED | 8 |
| Out of plan | 0 |

Measure process was **stopped**; completed files preserved. Full suite not re-started in this step — resume will only keep future explicit VALID samples compatible with `nav-sitewide-measure/2.1.0`.
