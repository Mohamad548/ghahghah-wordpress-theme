# Design sources

Private design and photography sources for the قهقهه WordPress project.

This directory is **not** part of the distributable theme or plugin packages. Do not include `design-sources/` in theme/plugin ZIP releases.

## Layout

| Path | Purpose |
|---|---|
| `brand/` | Original brand files (logo sources, etc.) |
| `raw-products/` | Immutable product/package photographs |
| `temporary/` | Scratch / temporary working files |
| `ui-reference/components/` | UI component / design-system references |
| `ui-reference/desktop/` | Desktop page and section references |
| `ui-reference/mobile/` | Mobile page references |
| `ui-reference/superseded/` | Older alternatives kept for history |
| `ui-reference/review/` | Ambiguous files awaiting human classification |

See `image-inventory.md` for per-file hashes, classifications, and statuses.

## Important rules

- Screenshot and mockup text is **not** verified business data.
- Do not treat package labels as authoritative product metadata.
- Factory photographs in UI references may be **temporary**.
- Certificate cards in mockups are **placeholders** — never fabricate certificates or standards.
- Do not hard-code phone numbers, emails, addresses, capacities, ingredients, nutrition, weights, prices, or personal names from these images.
- Product images will ultimately be managed through the **WordPress Media Library**.
- Only later, separately optimized derivatives may be placed under theme assets.
- Raw product photographs, brand binaries, temporary files, and UI reference images under `brand/`, `raw-products/`, `temporary/`, and `ui-reference/` are **gitignored** and remain local for implementation work.
- Tracked documentation only: `README.md` and `image-inventory.md`.

## Source integrity

Source folders outside this repository were **copied only**. They were not deleted, moved, renamed, edited, resized, compressed, or overwritten.
