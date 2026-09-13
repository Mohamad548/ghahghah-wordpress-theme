#!/usr/bin/env bash
# Live WordPress smoke tests for ghahghah-theme + ghahghah-core via @wordpress/env.
# Does not destroy the database. Does not modify WordPress core.
set -euo pipefail

PASS_COUNT=0
FAIL_COUNT=0

log() { printf '%s\n' "$*"; }
pass() { PASS_COUNT=$((PASS_COUNT + 1)); log "[PASS] $*"; }
fail() { FAIL_COUNT=$((FAIL_COUNT + 1)); log "[FAIL] $*"; exit 1; }

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT" || fail "Could not cd to project root: ${ROOT}"

# wp-env hashes process.cwd() case-sensitively on Windows.
# Git Bash re-entry often yields E:\... while the running env was created as e:\...
if command -v cygpath >/dev/null 2>&1; then
	# Keep single backslashes; Node spawn cwd must be a real Windows path.
	WP_ENV_CWD="$(cygpath -w "$ROOT" | sed -E 's/^([A-Za-z]):/\L\1:/')"
else
	WP_ENV_CWD="$ROOT"
fi
log "Working directory: $(pwd)"
log "wp-env Node cwd: ${WP_ENV_CWD}"

EVIDENCE_DIR="${ROOT}/.smoke-evidence"
mkdir -p "$EVIDENCE_DIR"

require_cmd() {
	command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"
}

# Run wp-env with a stable lowercase Windows cwd so it targets the same env hash.
wp_env() {
	# shellcheck disable=SC2068
	node -e '
const { spawnSync } = require("child_process");
const cwd = process.argv[1];
const args = process.argv.slice(2);
const result = spawnSync("npx", ["--no-install", "wp-env", ...args], {
  cwd,
  stdio: "inherit",
  shell: true,
  env: process.env,
});
process.exit(result.status === null ? 1 : result.status);
' "$WP_ENV_CWD" $@
}

# Resolve the running development CLI container (not tests).
cli_container() {
	docker ps --format '{{.Names}}' | grep -E 'ghahghah.*-cli-1$' | grep -v tests | head -n 1
}

# Fast, capturable WP-CLI via docker exec (avoids wp-env stdout chrome + quoting issues).
wpcli() {
	local container
	container="$(cli_container)"
	[[ -n "$container" ]] || fail "No running wp-env CLI container found"
	docker exec "$container" wp --allow-root "$@"
}

docker_sh() {
	local container
	container="$(cli_container)"
	[[ -n "$container" ]] || fail "No running wp-env CLI container found"
	docker exec "$container" sh -c "$1"
}

# Truncate debug.log inside the container so scenario fatals are attributable.
reset_debug_log() {
	docker_sh 'mkdir -p /var/www/html/wp-content; : > /var/www/html/wp-content/debug.log' >/dev/null 2>&1 || true
}

# Returns debug log body (may be empty).
inspect_debug_log() {
	local label="$1"
	local outfile="${EVIDENCE_DIR}/${label}-debug.log"
	local status
	status="$(docker_sh '
if [ ! -f /var/www/html/wp-content/debug.log ]; then
  echo "MISSING"
elif [ ! -s /var/www/html/wp-content/debug.log ]; then
  echo "EMPTY"
else
  echo "PRESENT"
  cat /var/www/html/wp-content/debug.log
fi
' 2>/dev/null || true)"

	printf '%s\n' "$status" >"$outfile"
	log "Debug log ($label):"
	log "$status"

	if printf '%s' "$status" | grep -Eqi 'PHP Fatal error|Uncaught Error|Parse error|Caught Error'; then
		fail "Fatal/parse error found in debug log during ${label}"
	fi

	if printf '%s' "$status" | grep -Eqi 'PHP (Warning|Notice|Deprecated)'; then
		log "[WARN] Notices/warnings/deprecations present in debug log during ${label} (see ${outfile})"
	fi
}

http_check() {
	local url="$1"
	local label="$2"
	local out="${EVIDENCE_DIR}/${label}-http.txt"
	local code final redirects
	code="$(curl -sS -o /dev/null -w '%{http_code}' -L --max-redirs 5 "$url" || true)"
	final="$(curl -sS -o /dev/null -w '%{url_effective}' -L --max-redirs 5 "$url" || true)"
	redirects="$(curl -sS -o /dev/null -w '%{num_redirects}' -L --max-redirs 5 "$url" || true)"
	{
		echo "url=$url"
		echo "http_code=$code"
		echo "final_url=$final"
		echo "num_redirects=$redirects"
	} >"$out"
	log "HTTP ($label): url=$url status=$code redirects=$redirects final=$final"
	if [[ -z "$code" || "$code" == "000" ]]; then
		fail "HTTP connection failure for ${label}: ${url}"
	fi
	if [[ "$code" != "200" ]]; then
		fail "Expected HTTP 200 for ${label}, got ${code} (${url})"
	fi
	pass "HTTP 200 for ${label} (${url})"
}

# Evaluate PHP that prints yes/no; never abort before printing FAIL.
assert_php_yes() {
	local php_code="$1"
	local label="$2"
	local result=""
	result="$(wpcli eval "$php_code" 2>/dev/null | tr -d '\r' | grep -E '^(yes|no)$' | tail -n 1 || true)"
	log "Assert PHP (${label}): => ${result}"
	if [[ "$result" != "yes" ]]; then
		fail "Assertion failed (${label}); expected yes, got '${result}'"
	fi
	pass "$label"
}

require_cmd docker
require_cmd curl
require_cmd npx
require_cmd node

if ! docker info >/dev/null 2>&1; then
	fail "Docker Engine is not reachable (docker info failed)"
fi

log "=== Starting wp-env (if needed) ==="
STATUS_OUT="$(wp_env status 2>&1 || true)"
log "$STATUS_OUT"
if printf '%s' "$STATUS_OUT" | grep -Eqi 'status:[[:space:]]*running'; then
	log "wp-env already running; skipping start"
	pass "wp-env already running"
else
	wp_env start
	pass "wp-env start completed"
fi

SITE_URL="$(wpcli option get siteurl 2>/dev/null | tr -d '\r' | tail -n 1)"
HOME_URL="$(wpcli option get home 2>/dev/null | tr -d '\r' | tail -n 1)"
WP_VERSION="$(wpcli core version 2>/dev/null | tr -d '\r' | tail -n 1)"
log "WordPress version: ${WP_VERSION}"
log "siteurl=${SITE_URL}"
log "home=${HOME_URL}"
[[ -n "$SITE_URL" ]] || fail "Could not determine siteurl from wp-env"
[[ -n "$HOME_URL" ]] || HOME_URL="$SITE_URL"

# Discover a bundled standard theme (not ghahghah-theme).
BUNDLE_THEME="$(wpcli theme list --status=inactive --field=name 2>/dev/null | tr -d '\r' | grep -E '^(twentytwentyfive|twentytwentyfour|twentytwentythree|twentytwentytwo|twentytwentyone|twentytwenty)$' | head -n 1 || true)"
if [[ -z "$BUNDLE_THEME" ]]; then
	BUNDLE_THEME="$(wpcli theme list --field=name 2>/dev/null | tr -d '\r' | grep -E '^twenty' | head -n 1 || true)"
fi
[[ -n "$BUNDLE_THEME" ]] || fail "No bundled Twenty* theme found to use for Scenario B"
log "Bundled theme for Scenario B: ${BUNDLE_THEME}"

########################################
# Scenario A — Theme without Core
########################################
log ""
log "=== Scenario A: Theme without Core ==="
reset_debug_log
wpcli plugin deactivate ghahghah-core --quiet 2>/dev/null || true
wpcli theme activate ghahghah-theme
ACTIVE_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
log "Active stylesheet: ${ACTIVE_THEME}"
[[ "$ACTIVE_THEME" == "ghahghah-theme" ]] || fail "Expected active stylesheet ghahghah-theme, got ${ACTIVE_THEME}"
pass "Active stylesheet is ghahghah-theme"

PLUGIN_ACTIVE="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$PLUGIN_ACTIVE" == "no" ]] || fail "ghahghah-core should be inactive in Scenario A"
pass "ghahghah-core is inactive"

http_check "${HOME_URL}/" "A-homepage"
assert_php_yes 'echo ( post_type_exists( "ghahghah_product" ) === false ) ? "yes" : "no";' "A: CPT absent without Core"
inspect_debug_log "A"
pass "Scenario A complete"

########################################
# Scenario B — Core independently
########################################
log ""
log "=== Scenario B: Core independently ==="
reset_debug_log
wpcli theme activate "$BUNDLE_THEME"
ACTIVE_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
log "Active stylesheet: ${ACTIVE_THEME}"
[[ "$ACTIVE_THEME" == "$BUNDLE_THEME" ]] || fail "Expected bundled theme ${BUNDLE_THEME}, got ${ACTIVE_THEME}"
pass "Bundled theme ${BUNDLE_THEME} active"

wpcli plugin activate ghahghah-core
PLUGIN_ACTIVE="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$PLUGIN_ACTIVE" == "yes" ]] || fail "ghahghah-core failed to activate"
pass "ghahghah-core is active without ghahghah-theme"

THEME_IS_GHAHGHAH="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
[[ "$THEME_IS_GHAHGHAH" != "ghahghah-theme" ]] || fail "Scenario B unexpectedly still on ghahghah-theme"
pass "Plugin activation does not require ghahghah-theme"

assert_php_yes 'echo ( post_type_exists( "ghahghah_product" ) === true ) ? "yes" : "no";' "B: CPT present with Core"
inspect_debug_log "B"
pass "Scenario B complete"

########################################
# Scenario C — Theme + Core together
########################################
log ""
log "=== Scenario C: Theme and Core together ==="
reset_debug_log
wpcli plugin activate ghahghah-core
wpcli theme activate ghahghah-theme
ACTIVE_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
PLUGIN_ACTIVE="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$ACTIVE_THEME" == "ghahghah-theme" ]] || fail "Expected ghahghah-theme active"
[[ "$PLUGIN_ACTIVE" == "yes" ]] || fail "Expected ghahghah-core active"
pass "Theme and Core both active"

assert_php_yes 'echo ( post_type_exists( "ghahghah_product" ) === true ) ? "yes" : "no";' "C: CPT present"

# Inspect CPT object.
CPT_INFO="$(wpcli eval '
$pt = get_post_type_object("ghahghah_product");
if (!$pt) { echo "MISSING\n"; exit(1); }
echo "public=" . ($pt->public ? "1" : "0") . "\n";
echo "show_in_rest=" . (!empty($pt->show_in_rest) ? "1" : "0") . "\n";
$ha = $pt->has_archive;
if ($ha === false || $ha === "") { echo "has_archive=0\n"; }
else { echo "has_archive=1\n"; echo "has_archive_value=" . (is_string($ha) ? $ha : "true") . "\n"; }
echo "rest_base=" . (isset($pt->rest_base) ? $pt->rest_base : "") . "\n";
' 2>/dev/null | tr -d '\r')"
printf '%s\n' "$CPT_INFO" | tee "${EVIDENCE_DIR}/C-cpt.txt"
echo "$CPT_INFO" | grep -q 'public=1' || fail "CPT public is not true"
echo "$CPT_INFO" | grep -q 'show_in_rest=1' || fail "CPT show_in_rest is not true"
echo "$CPT_INFO" | grep -q 'has_archive=1' || fail "CPT has_archive is not enabled"
pass "CPT public/show_in_rest/has_archive verified"

# Flush rewrite rules once for this test.
wpcli rewrite flush --hard
pass "Rewrite rules flushed once"

ARCHIVE_SLUG="$(wpcli eval '
$slug = apply_filters("ghahghah_product_archive_slug", "products");
$pt = get_post_type_object("ghahghah_product");
if ($pt && is_string($pt->has_archive) && $pt->has_archive !== "") {
  $slug = $pt->has_archive;
} elseif ($pt && !empty($pt->rewrite["slug"])) {
  $slug = $pt->rewrite["slug"];
}
echo $slug;
' 2>/dev/null | tr -d '\r' | tail -n 1)"
log "Resolved product archive slug: ${ARCHIVE_SLUG}"
[[ -n "$ARCHIVE_SLUG" ]] || fail "Could not resolve product archive slug"
ARCHIVE_URL="${HOME_URL%/}/${ARCHIVE_SLUG}/"
printf '%s\n' "$ARCHIVE_SLUG" >"${EVIDENCE_DIR}/C-archive-slug.txt"

http_check "${HOME_URL}/" "C-homepage"

# Archive check — create temporary product if empty archive is non-200.
TEMP_POST_ID=""
cleanup_temp_post() {
	if [[ -n "${TEMP_POST_ID}" ]]; then
		wpcli post delete "$TEMP_POST_ID" --force --quiet 2>/dev/null || true
		log "Deleted temporary test product ID ${TEMP_POST_ID}"
		TEMP_POST_ID=""
	fi
}
trap cleanup_temp_post EXIT

ARCHIVE_CODE="$(curl -sS -o /dev/null -w '%{http_code}' -L --max-redirs 5 "$ARCHIVE_URL" || true)"
log "Initial archive HTTP status: ${ARCHIVE_CODE} (${ARCHIVE_URL})"
if [[ "$ARCHIVE_CODE" != "200" ]]; then
	log "Archive non-200 without products; creating temporary test product"
	TEMP_POST_ID="$(wpcli post create --post_type=ghahghah_product --post_title='Smoke Temp Product' --post_status=publish --porcelain 2>/dev/null | tr -d '\r' | tail -n 1)"
	[[ -n "$TEMP_POST_ID" && "$TEMP_POST_ID" =~ ^[0-9]+$ ]] || fail "Failed to create temporary test product"
	log "Created temporary product ID ${TEMP_POST_ID}"
	wpcli rewrite flush --hard >/dev/null
	http_check "$ARCHIVE_URL" "C-archive"
	cleanup_temp_post
	trap - EXIT
else
	http_check "$ARCHIVE_URL" "C-archive"
fi
pass "Product archive responded without fatal error"

# REST discovery.
REST_URL="${HOME_URL%/}/wp-json/wp/v2/types"
REST_BODY="$(curl -sS -L --max-redirs 5 "$REST_URL" || true)"
printf '%s\n' "$REST_BODY" >"${EVIDENCE_DIR}/C-rest-types.json"
log "REST types URL: ${REST_URL}"
printf '%s' "$REST_BODY" | grep -q 'ghahghah_product' || fail "REST types endpoint does not expose ghahghah_product"
pass "REST exposes ghahghah_product"

# Also hit rest collection if rest_base known.
REST_BASE="$(echo "$CPT_INFO" | sed -n 's/^rest_base=//p' | head -n 1)"
if [[ -n "$REST_BASE" ]]; then
	REST_COLLECTION="${HOME_URL%/}/wp-json/wp/v2/${REST_BASE}"
	REST_COL_CODE="$(curl -sS -o /dev/null -w '%{http_code}' -L --max-redirs 5 "$REST_COLLECTION" || true)"
	log "REST collection ${REST_COLLECTION} => ${REST_COL_CODE}"
	[[ "$REST_COL_CODE" == "200" ]] || fail "REST collection returned ${REST_COL_CODE}"
	pass "REST collection HTTP 200"
fi

inspect_debug_log "C"
pass "Scenario C complete"

########################################
# Scenario D — Deactivation safety
########################################
log ""
log "=== Scenario D: Core deactivation safety ==="
reset_debug_log
wpcli theme activate ghahghah-theme
wpcli plugin deactivate ghahghah-core
ACTIVE_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
PLUGIN_ACTIVE="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$ACTIVE_THEME" == "ghahghah-theme" ]] || fail "Theme should remain active"
[[ "$PLUGIN_ACTIVE" == "no" ]] || fail "Core should be inactive"
pass "Theme active, Core deactivated"

http_check "${HOME_URL}/" "D-homepage"
assert_php_yes 'echo ( post_type_exists( "ghahghah_product" ) === false ) ? "yes" : "no";' "D: CPT gone after Core deactivation"
inspect_debug_log "D"

# Restore final development state: both active.
wpcli plugin activate ghahghah-core
FINAL_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
FINAL_PLUGIN="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$FINAL_THEME" == "ghahghah-theme" ]] || fail "Final theme should be ghahghah-theme"
[[ "$FINAL_PLUGIN" == "yes" ]] || fail "Final plugin should be active"
pass "Final state restored: theme + core active"
pass "Scenario D complete"

log ""
log "=== SMOKE SUMMARY ==="
log "WordPress: ${WP_VERSION}"
log "Home: ${HOME_URL}"
log "Passed assertions: ${PASS_COUNT}"
log "ALL SMOKE SCENARIOS PASSED"
exit 0
