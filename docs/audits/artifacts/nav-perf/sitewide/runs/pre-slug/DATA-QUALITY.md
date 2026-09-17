# pre-slug run — DATA QUALITY (do not treat as Before)

**Status:** DAMAGED / INCOMPLETE — not a usable pre-migration baseline.

## Defects recorded 2026-09-17

1. **Sample filename collisions:** IDs used Base64URL of the full URL truncated with `slice(0,40)`, so distinct URLs sharing a long common prefix could overwrite each other’s JSON samples (observed around `privacy-policy` / similar prefixes).
2. **No durable summary:** Suite crashed / restarted before a trustworthy `summary.json`; only a partial `pre-slug-run.txt` and a handful of sample files remain.
3. **Device mixing:** Viewport/device was not part of the median grouping key; mobile and desktop samples could be pooled incorrectly.
4. **Cache labeling:** Cache-enabled runs were labeled `warm` without guaranteeing destination preparation.
5. **Click method:** Hidden-link `element.click` was accepted as a fallback — not equivalent to a user click.

## Policy

- Do **not** rewrite or delete these files (history preserved).
- Do **not** present this directory as the authoritative Before for URL migration or sitewide TTFB.
- Re-measure with the fixed harness under a new phase directory (`post-slug`, `coverage-YYYYMMDD`, etc.).
- Any “Before” claim must use a complete `summary.json` with `status: COMPLETE` from the fixed tool, or an explicitly labeled reconstructed inventory — never this incomplete pre-slug set.
