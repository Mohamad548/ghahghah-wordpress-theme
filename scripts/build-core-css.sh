#!/usr/bin/env bash
# Rebuild ghahghah-theme/assets/css/core.css from source part files.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CSS="$ROOT/ghahghah-theme/assets/css"
OUT="$CSS/core.css"

{
	cat <<'EOF'
/*
 * Ghahghah global core bundle — do not edit by hand.
 * Sources: fonts.css, base.css, layout.css, header.css, footer.css, mobile-bottom-nav.css
 * Rebuild: bash scripts/build-core-css.sh
 */

EOF
	for part in fonts.css base.css layout.css header.css footer.css mobile-bottom-nav.css; do
		echo "/* === ${part} === */"
		cat "$CSS/$part"
		echo ""
	done
} > "$OUT"

echo "Wrote $OUT ($(wc -c < "$OUT") bytes)"
