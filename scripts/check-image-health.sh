#!/usr/bin/env bash
# QA: verify critical page images on the current worktree wp-env URL return HTTP 200 + image/*.
# Usage: bash scripts/check-image-health.sh [baseUrl]
# Default baseUrl: http://localhost:8898
set -euo pipefail

BASE="${1:-http://localhost:8898}"
PASS=0
FAIL=0
TMP="$(mktemp)"
URLS="$(mktemp)"

cleanup() { rm -f "$TMP" "$URLS"; }
trap cleanup EXIT

log() { printf '%s\n' "$*"; }
pass() { PASS=$((PASS + 1)); log "[PASS] $*"; }
fail() { FAIL=$((FAIL + 1)); log "[FAIL] $*"; }

check_url() {
	local url="$1"
	local label="$2"
	local out code ctype
	out="$(curl -sS -o /dev/null -w '%{http_code}|%{content_type}' --globoff "$url" || echo '000|')"
	code="${out%%|*}"
	ctype="${out#*|}"
	if [[ "$code" == "200" ]]; then
		case "$ctype" in
			image/*)
				pass "$label ($code $ctype)"
				return 0
				;;
		esac
	fi
	fail "$label => $code $ctype ($url)"
}

PAGES=(
	"${BASE}/"
	"${BASE}/products/"
	"${BASE}/?post_type=post"
	"${BASE}/wholesale-request-guide/"
)

log "Image health QA against ${BASE}"

: >"$URLS"
for page in "${PAGES[@]}"; do
	curl -sS -L --globoff "$page" -o "$TMP" || true
	grep -oE '(src|srcset|data-src|data-srcset)="[^"]+"' "$TMP" \
		| sed -E 's/^[^=]+="//;s/"$//' \
		| tr ',' '\n' \
		| awk '{print $1}' \
		| sed '/^$/d' >>"$URLS" || true
done

# Normalize + filter + unique
SORT_URLS="$(mktemp)"
while IFS= read -r u; do
	[[ -z "$u" ]] && continue
	[[ "$u" == http* ]] || u="${BASE}${u}"
	case "$u" in
		"${BASE}"*) ;;
		*) continue ;;
	esac
	case "$u" in
		*/uploads/*|*/themes/ghahghah-theme/assets/images/*) ;;
		*) continue ;;
	esac
	case "$u" in
		*.jpg*|*.jpeg*|*.png*|*.webp*|*.gif*|*.svg*) printf '%s\n' "$u" ;;
	esac
done <"$URLS" | sort -u >"$SORT_URLS"

COUNT="$(wc -l <"$SORT_URLS" | tr -d ' ')"
log "Unique asset URLs to check: ${COUNT}"

while IFS= read -r u; do
	[[ -z "$u" ]] && continue
	enc="$(node -e '
const raw=process.argv[1];
const x=new URL(raw);
// Decode each segment first to avoid double-encoding already-%-encoded HTML URLs.
x.pathname = x.pathname.split("/").map((seg) => encodeURIComponent(decodeURIComponent(seg))).join("/");
process.stdout.write(x.toString());
' "$u")"
	base="$(basename "${u%%\?*}")"
	check_url "$enc" "$base"
done <"$SORT_URLS"

KNOWN=(
	"cone-snacks-closeup.jpg"
	"light-triangle-pieces.jpg"
	"snack-sticks-closeup-640x480.jpg"
	"snack-serving-board-600x480.jpg"
	"ghahghah_cheese_flavour_banner_optimized.webp"
	"ghahghah_pepper_flavour_banner_optimized.webp"
	"01-cheese-flavour-banner-v2.webp"
	"products-archive-banner-desktop.webp"
	"articles-archive-banner-desktop.webp"
)
log "Known regression files:"
for f in "${KNOWN[@]}"; do
	check_url "${BASE}/wp-content/uploads/2026/09/${f}" "known:${f}"
done

rm -f "$SORT_URLS"
log "=== IMAGE HEALTH SUMMARY ==="
log "Passed: ${PASS}"
log "Failed: ${FAIL}"
[[ "$FAIL" -eq 0 ]]
