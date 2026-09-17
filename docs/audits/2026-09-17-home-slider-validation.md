# Home slider validation — 2026-09-17 (`:8898`)

**Branch:** `fix/perf-home-lcp`  
**Base:** `b512525a0adb2acd3b2317bdafb8959a494c13fa`  
**Worktree / env:** `E:/word pares/ghahghah-fix-perf-images` · `http://localhost:8898`  
**Preserved:** `:8888`, uploads, settings, untracked artifacts — no merge, no new env

Lab results are not a production CWV guarantee.

---

## Verdict

| Item | Result |
|------|--------|
| Failed destination must not activate | **Fixed** — error / decode reject / timeout return failure; healthy slide stays |
| Overlapping requests (last destination wins) | **Fixed** — request token; stale completions ignored |
| `prefers-reduced-motion` under `.is-ready` | **Fixed** — computed `transition-duration` is `0s` |
| Initial banner download budget | **Preserved** — still slide 0 + ≤1 prefetch |
| Compatible PO LCP method label | **نمای باریک دسکتاپ بدون throttling** (390×844, `isMobile:false`, no lab throttle) |
| Mobile LH / PO `NO_LCP` | **Still unresolved tooling interaction** — not claimed fixed; not named as a definitive root cause |

---

## Production fixes

### 1–2. `assets/js/hero.js`

- `waitDecoded` returns `Promise<boolean>` — success only when load + decode succeed with `naturalWidth > 0`.
- `error`, decode rejection, and 4s timeout resolve **false** (not success).
- Temporary `load`/`error` listeners and timeout are cleared on settle.
- Separated **displayed** `index` from **requested** `requestedIndex`.
- Monotonic `requestToken`: only the latest request may change classes, dots, live region, or restart the autoplay timer.
- Autoplay timer cleared while a destination is pending (no stacked timers / mid-wait auto-advance).
- On failure: keep healthy active slide; reset `requestedIndex` to displayed so controls stay usable.

### 3. `assets/css/hero.css`

- Reduced-motion rule now includes `.ghahghah-hero.is-ready .ghahghah-hero__slide` (and `will-change: auto`) so it wins over the `.is-ready` transition block.

---

## Regression evidence (required races)

Harness: `scripts/hero-slider-regression.cjs`  
Artifacts: `docs/audits/artifacts/home-lcp/slider-regression/`

| Phase | Hero JS | Passed |
|-------|---------|--------|
| **Before** (`HEAD` hero at Base) | buggy `waitDecoded` + early `index` | **7 / 11** |
| **After** (this fix) | token + boolean decode | **11 / 11** |

### Failures before fix (then green after)

| Case | Before | After |
|------|--------|-------|
| Destination load &gt; 4s | activated mid-wait (`mid=1`) | stayed on 0 |
| HTTP 404 destination | activated (`after404=1`) | stayed on 0; healthy dot later OK |
| Invalid body / decode fail | activated (`idx=1`) | stayed on 0; healthy dot later OK |
| Two Next; image1 finishes while 2 pending | **activated slide 2** (`mid=2`, live «اسلاید 3») | **stayed on 0** until 2 ready (`mid=0`, `final=2`) |

Also green after (and mostly already green before): Next/Prev/dots/keyboard/swipe; 9 distinct slides mobile+desktop; initial ≤2 banner keys; noscript first slide visible; autoplay advances without reduced-motion.

### reduced-motion CSS probe

`docs/audits/artifacts/home-lcp/slider-regression/reduced-motion-css-probe.json`

| CSS | computed `transition-duration` on `.is-ready` slide |
|-----|-----------------------------------------------------|
| Old RM selectors (no `.is-ready`) | `0.75s, 0.75s, 0.75s` |
| Fixed RM selectors | `0s` |

Autoplay hold under RM waits `data-interval + 2000ms` (here 5000+2000); index held.

---

## Measurement notes (PO)

**Method label:** نمای باریک دسکتاپ بدون throttling — Puppeteer 390×844, `isMobile: false`, default DPR, cache disabled, no throttling, LCP+CLS observers before navigation, no scroll/click.

**`bannerTransferBytes` correction**

- Prior values in `po-compatible/{before,after}/summary.json` used `Network.responseReceived.encodedDataLength` → **not final download size**. Numbers left in place for audit trail; both summaries now carry `bannerTransferBytesNote: INVALID…`.
- Harness `scripts/home-lcp-po-compatible.cjs` now records `Network.loadingFinished.encodedDataLength` matched by `requestId`.
- Fresh check (same method, after-transfer-check): median **160274** bytes, **2** banner requests, median LCP **3488 ms** (lab noise; not a new Before/After claim). Artifact: `docs/audits/artifacts/home-lcp/po-compatible/after-transfer-check/`.

Historical compatible LCP comparison from Base report remains: median **4016 → 3344 ms**, banner requests **9 → 2** (request counts still valid; byte totals from that era are invalid as absolute sizes).

**NO_LCP (mobile LH / `isMobile:true` PO):** still unresolved; treat as tooling/emulation interaction — do not cite as closed by this slider fix.

---

## Scope / non-goals

- No full Lighthouse re-batch; no invalid LH LCP medians claimed.
- No uploads / `:8888` / settings changes; no merge; no force push.
- Wholesale TBT still out of scope.

---

## Files

| Path | Role |
|------|------|
| `ghahghah-theme/assets/js/hero.js` | Decode truth + request token |
| `ghahghah-theme/assets/css/hero.css` | RM × `.is-ready` |
| `scripts/hero-slider-regression.cjs` | Controlled-request regressions |
| `scripts/home-lcp-po-compatible.cjs` | `loadingFinished` transfer bytes |
| `docs/audits/artifacts/home-lcp/slider-regression/` | Before/after JSON + RM probe |
| `docs/audits/artifacts/home-lcp/po-compatible/after-transfer-check/` | Corrected byte sample |
