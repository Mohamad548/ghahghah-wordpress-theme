#!/usr/bin/env bash
# Build marketplace-ready theme zip (theme only; Core is bundled inside).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/dist"
VERSION="$(grep -E '^Version:' "$ROOT/ghahghah-theme/style.css" | awk '{print $2}')"
NAME="ghahghah-theme-${VERSION}"

mkdir -p "$OUT"
rm -f "$OUT/${NAME}.zip" "$OUT/ghahghah-package-${VERSION}.zip"

# Theme zip for WP uploader
(
	cd "$ROOT"
	zip -r -q "$OUT/${NAME}.zip" ghahghah-theme \
		-x "ghahghah-theme/.git/*" \
		-x "**/node_modules/*" \
		-x "**/.DS_Store"
)

# Marketplace folder layout
STAGE="$OUT/ghahghah-package-${VERSION}"
rm -rf "$STAGE"
mkdir -p "$STAGE/1-Theme"
cp "$OUT/${NAME}.zip" "$STAGE/1-Theme/ghahghah-theme.zip"
cp "$ROOT/docs/راهنمای-نصب-قالب.md" "$STAGE/راهنمای-نصب.md"

(
	cd "$OUT"
	zip -r -q "ghahghah-package-${VERSION}.zip" "ghahghah-package-${VERSION}"
)

echo "Wrote:"
echo "  $OUT/${NAME}.zip"
echo "  $OUT/ghahghah-package-${VERSION}.zip"
