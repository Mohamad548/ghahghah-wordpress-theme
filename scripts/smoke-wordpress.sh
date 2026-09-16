#!/usr/bin/env bash
# Live WordPress smoke tests for ghahghah-theme + ghahghah-core via @wordpress/env.
# Does not destroy the database. Does not modify WordPress core.
# Targets the wp-env Docker project for THIS repository cwd only (safe with multiple envs).
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

# Saved at start; restored on EXIT (success or failure).
INITIAL_THEME=""
INITIAL_PLUGIN_ACTIVE=""
TEMP_POST_ID=""
RESTORE_DONE=0
# Theme-mod / hero option snapshots (taken with --skip-themes --skip-plugins).
INITIAL_THEME_MODS_FILE=""
INITIAL_HERO_BANNER_PACK_FILE=""
INITIAL_MEDIA_SYNC_VERSION_FILE=""

require_cmd() {
	command -v "$1" >/dev/null 2>&1 || fail "Required command not found: $1"
}

# Prefer local @wordpress/env binary from npm ci (never bare "wp-env" / wrong npx package).
resolve_wp_env_bin() {
	local local_bin="${ROOT}/node_modules/@wordpress/env/bin/wp-env"
	local local_shim="${ROOT}/node_modules/.bin/wp-env"
	if [[ -f "$local_bin" ]]; then
		printf '%s\n' "$local_bin"
		return 0
	fi
	if [[ -f "$local_shim" ]]; then
		printf '%s\n' "$local_shim"
		return 0
	fi
	fail "Local @wordpress/env not found. Run: npm ci --include=dev (expects @wordpress/env@11.x in node_modules)"
}

WP_ENV_BIN="$(resolve_wp_env_bin)"
WP_ENV_VERSION="$(
	node -e 'const p=require("path"); const j=require(p.join(process.argv[1],"node_modules","@wordpress","env","package.json")); process.stdout.write(String(j.version||""));' "$ROOT" 2>/dev/null || echo unknown
)"
log "Using wp-env binary: ${WP_ENV_BIN}"
log "Resolved @wordpress/env version: ${WP_ENV_VERSION}"
printf '%s\n' "$WP_ENV_VERSION" | grep -Eq '^11\.' || fail "Expected @wordpress/env 11.x, got ${WP_ENV_VERSION}"

# Run wp-env with a stable lowercase Windows cwd so it targets the same env hash.
wp_env() {
	# shellcheck disable=SC2068
	node -e '
const { spawnSync } = require("child_process");
const cwd = process.argv[1];
const bin = process.argv[2];
const args = process.argv.slice(3);
const result = spawnSync(process.execPath, [bin, ...args], {
  cwd,
  stdio: "inherit",
  shell: false,
  env: process.env,
});
process.exit(result.status === null ? 1 : result.status);
' "$WP_ENV_CWD" "$WP_ENV_BIN" "$@"
}

# Basename hint unique to this worktree (e.g. ghahghah-fix-perf-images).
WORKTREE_HINT="$(basename "$ROOT" | tr '[:upper:]' '[:lower:]' | tr -cd 'a-z0-9-')"
# Main clone folder often ends with "theme"; avoid matching both.
log "Worktree docker name hint: ${WORKTREE_HINT}"

# Resolve THIS worktree's development CLI container (not tests, not sibling envs).
cli_container() {
	local names matches=()
	names="$(docker ps --format '{{.Names}}' 2>/dev/null || true)"
	# Prefer containers whose name includes this worktree directory token.
	while IFS= read -r n; do
		[[ -z "$n" ]] && continue
		printf '%s' "$n" | grep -Eqi 'tests' && continue
		printf '%s' "$n" | grep -Eqi -- '-cli-1$' || continue
		printf '%s' "$n" | grep -Eqi -- 'wp-env-' || continue
		# Match worktree hint inside compose project name.
		if printf '%s' "$n" | grep -Eqi -- "$WORKTREE_HINT"; then
			matches+=("$n")
		fi
	done <<< "$names"

	if [[ ${#matches[@]} -eq 1 ]]; then
		printf '%s\n' "${matches[0]}"
		return 0
	fi
	if [[ ${#matches[@]} -gt 1 ]]; then
		log "Ambiguous CLI containers for hint '${WORKTREE_HINT}':"
		printf '%s\n' "${matches[@]}" | sed 's/^/  /' >&2
		return 1
	fi

	# Fallback: inspect mounts for this ROOT path (Windows/Git Bash tolerant).
	local root_norm root_alt
	root_norm="$(printf '%s' "$ROOT" | tr '\\' '/' | tr '[:upper:]' '[:lower:]')"
	root_alt="$(printf '%s' "$WP_ENV_CWD" | tr '\\' '/' | tr '[:upper:]' '[:lower:]')"
	while IFS= read -r n; do
		[[ -z "$n" ]] && continue
		printf '%s' "$n" | grep -Eqi 'tests' && continue
		printf '%s' "$n" | grep -Eqi -- '-cli-1$' || continue
		printf '%s' "$n" | grep -Eqi -- 'wp-env-' || continue
		local mounts
		mounts="$(docker inspect -f '{{range .Mounts}}{{.Source}}|{{end}}' "$n" 2>/dev/null | tr '\\' '/' | tr '[:upper:]' '[:lower:]' || true)"
		if printf '%s' "$mounts" | grep -Fq -- "$root_norm" || printf '%s' "$mounts" | grep -Fq -- "$root_alt"; then
			matches+=("$n")
		fi
	done <<< "$names"

	if [[ ${#matches[@]} -eq 1 ]]; then
		printf '%s\n' "${matches[0]}"
		return 0
	fi
	if [[ ${#matches[@]} -gt 1 ]]; then
		log "Ambiguous CLI containers by mount for ROOT:"
		printf '%s\n' "${matches[@]}" | sed 's/^/  /' >&2
		return 1
	fi
	return 1
}

wordpress_container_for_cli() {
	local cli="$1"
	# wp-env-...-cli-1 -> wp-env-...-wordpress-1
	printf '%s\n' "${cli%-cli-1}-wordpress-1"
}

# Fast, capturable WP-CLI via docker exec (avoids wp-env stdout chrome + quoting issues).
wpcli() {
	local container
	container="$(cli_container)" || fail "No unique wp-env CLI container for this worktree (hint=${WORKTREE_HINT})"
	docker exec "$container" wp --allow-root "$@"
}

# Option reads/writes that must not bootstrap themes/plugins (no hero/media side effects).
wpcli_opts() {
	local container
	container="$(cli_container)" || fail "No unique wp-env CLI container for this worktree (hint=${WORKTREE_HINT})"
	docker exec "$container" wp --allow-root --skip-themes --skip-plugins "$@"
}

docker_sh() {
	local container
	container="$(cli_container)" || fail "No unique wp-env CLI container for this worktree (hint=${WORKTREE_HINT})"
	docker exec "$container" sh -c "$1"
}

# Snapshot one option: writes value file + state file (present|empty|missing|error).
# Args: option_name value_outfile state_outfile [--json]
snapshot_option() {
	local opt="$1"
	local value_file="$2"
	local state_file="$3"
	local json_flag="${4:-}"
	local ec=0
	local raw=""
	local errf
	errf="$(mktemp)"

	set +e
	if [[ "$json_flag" == "--json" ]]; then
		raw="$(wpcli_opts option get "$opt" --format=json 2>"$errf")"
	else
		raw="$(wpcli_opts option get "$opt" 2>"$errf")"
	fi
	ec=$?
	set -e

	raw="$(printf '%s' "$raw" | tr -d '\r')"
	local errtxt
	errtxt="$(tr -d '\r' <"$errf" 2>/dev/null || true)"
	if [[ "$ec" -ne 0 ]] || printf '%s' "$errtxt" | grep -Eqi "Could not get .${opt}. option|Does it exist"; then
		if printf '%s' "$errtxt" | grep -Eqi "Could not get .${opt}. option|Does it exist|Could not get"; then
			printf '' >"$value_file"
			printf 'missing\n' >"$state_file"
			rm -f "$errf"
			log "Snapshot ${opt}: MISSING"
			return 0
		fi
		if [[ "$ec" -ne 0 ]]; then
			printf '' >"$value_file"
			printf 'error\n' >"$state_file"
			log "Snapshot ${opt}: ERROR (wp-cli exit ${ec})"
			log "$errtxt"
			rm -f "$errf"
			fail "Failed to snapshot option ${opt}"
		fi
	fi
	rm -f "$errf"

	if [[ -z "$raw" ]]; then
		printf '' >"$value_file"
		printf 'empty\n' >"$state_file"
		log "Snapshot ${opt}: EMPTY"
		return 0
	fi

	if [[ "$json_flag" == "--json" ]]; then
		if ! node -e 'JSON.parse(require("fs").readFileSync(0,"utf8"));' <<<"$raw" 2>/dev/null; then
			printf '%s' "$raw" >"$value_file"
			printf 'error\n' >"$state_file"
			fail "Snapshot ${opt}: invalid JSON"
		fi
	fi

	printf '%s' "$raw" >"$value_file"
	printf 'present\n' >"$state_file"
	log "Snapshot ${opt}: PRESENT ($(wc -c <"$value_file" | tr -d ' ') bytes)"
}

# Restore one option from snapshot state; verify by re-read. Fails the smoke on mismatch.
restore_option_verified() {
	local opt="$1"
	local value_file="$2"
	local state_file="$3"
	local json_flag="${4:-}"
	local state=""
	local container
	local after=""
	local ec=0

	[[ -f "$state_file" ]] || fail "Missing snapshot state for ${opt}: ${state_file}"
	state="$(tr -d '\r\n' <"$state_file")"

	container="$(cli_container)" || fail "No CLI container during restore of ${opt}"

	case "$state" in
		missing)
			set +e
			wpcli_opts option delete "$opt" >/dev/null 2>&1
			# Verify absent
			wpcli_opts option get "$opt" >/dev/null 2>&1
			ec=$?
			set -e
			if [[ "$ec" -eq 0 ]]; then
				fail "Restore ${opt}: expected MISSING after delete, but option still readable"
			fi
			log "Restored ${opt}: confirmed MISSING"
			;;
		empty)
			set +e
			if [[ "$json_flag" == "--json" ]]; then
				printf '""\n' | docker exec -i "$container" wp --allow-root --skip-themes --skip-plugins option update "$opt" --format=json >/dev/null 2>&1
				ec=$?
			else
				wpcli_opts option update "$opt" "" >/dev/null 2>&1
				ec=$?
			fi
			set -e
			[[ "$ec" -eq 0 ]] || fail "Restore ${opt}: failed to write EMPTY value (exit ${ec})"
			after="$(wpcli_opts option get "$opt" ${json_flag:+--format=json} 2>/dev/null | tr -d '\r' || true)"
			if [[ -n "$after" && "$after" != '""' && "$after" != '[]' && "$after" != '{}' ]]; then
				fail "Restore ${opt}: expected EMPTY, got non-empty after write"
			fi
			log "Restored ${opt}: confirmed EMPTY"
			;;
		present)
			[[ -f "$value_file" ]] || fail "Restore ${opt}: value file missing for PRESENT state"
			[[ -s "$value_file" ]] || fail "Restore ${opt}: value file empty for PRESENT state"
			if [[ "$json_flag" == "--json" ]]; then
				node -e 'JSON.parse(require("fs").readFileSync(process.argv[1],"utf8"));' "$value_file" \
					|| fail "Restore ${opt}: snapshot JSON invalid"
				set +e
				docker exec -i "$container" wp --allow-root --skip-themes --skip-plugins option update "$opt" --format=json <"$value_file" >/dev/null 2>&1
				ec=$?
				set -e
			else
				set +e
				wpcli_opts option update "$opt" "$(cat "$value_file")" >/dev/null 2>&1
				ec=$?
				set -e
			fi
			[[ "$ec" -eq 0 ]] || fail "Restore ${opt}: option update failed (exit ${ec})"
			if [[ "$json_flag" == "--json" ]]; then
				after="$(wpcli_opts option get "$opt" --format=json 2>/dev/null | tr -d '\r' || true)"
				node -e '
const fs=require("fs");
const a=JSON.parse(fs.readFileSync(process.argv[1],"utf8"));
const b=JSON.parse(process.argv[2]);
if (JSON.stringify(a)!==JSON.stringify(b)) process.exit(2);
' "$value_file" "$after" || fail "Restore ${opt}: re-read JSON does not match snapshot"
			else
				after="$(wpcli_opts option get "$opt" 2>/dev/null | tr -d '\r' || true)"
				[[ "$after" == "$(tr -d '\r' <"$value_file")" ]] || fail "Restore ${opt}: re-read value does not match snapshot"
			fi
			log "Restored ${opt}: verified PRESENT match"
			;;
		error)
			fail "Restore ${opt}: snapshot state was ERROR; cannot restore safely"
			;;
		*)
			fail "Restore ${opt}: unknown snapshot state '${state}'"
			;;
	esac
}

restore_initial_state() {
	[[ "$RESTORE_DONE" -eq 1 ]] && return 0
	RESTORE_DONE=1
	log "=== Restoring initial theme/plugin/options state ==="
	# Theme/plugin activation may need full bootstrap; option restore uses skip-*.
	set +e
	if [[ -n "${TEMP_POST_ID}" ]]; then
		wpcli post delete "$TEMP_POST_ID" --force --quiet 2>/dev/null
		log "Deleted temporary test product ID ${TEMP_POST_ID}"
		TEMP_POST_ID=""
	fi
	if [[ -n "${INITIAL_THEME}" ]]; then
		wpcli theme activate "$INITIAL_THEME" --quiet 2>/dev/null
		log "Restored theme activation attempt: ${INITIAL_THEME}"
	fi
	if [[ "${INITIAL_PLUGIN_ACTIVE}" == "yes" ]]; then
		wpcli plugin activate ghahghah-core --quiet 2>/dev/null
		log "Restored plugin activation attempt: ghahghah-core"
	elif [[ "${INITIAL_PLUGIN_ACTIVE}" == "no" ]]; then
		wpcli plugin deactivate ghahghah-core --quiet 2>/dev/null
		log "Restored plugin deactivation attempt: ghahghah-core"
	fi
	set -e

	# Verified option restores (fail smoke if mismatch).
	if [[ -n "${INITIAL_THEME_MODS_FILE}" ]]; then
		restore_option_verified "theme_mods_ghahghah-theme" \
			"${INITIAL_THEME_MODS_FILE}" \
			"${EVIDENCE_DIR}/initial-theme_mods_ghahghah-theme.state" \
			--json
	fi
	if [[ -n "${INITIAL_HERO_BANNER_PACK_FILE}" ]]; then
		restore_option_verified "ghahghah_hero_banner_pack" \
			"${INITIAL_HERO_BANNER_PACK_FILE}" \
			"${EVIDENCE_DIR}/initial-hero-banner-pack.state"
	fi
	if [[ -n "${INITIAL_MEDIA_SYNC_VERSION_FILE}" ]]; then
		restore_option_verified "ghahghah_theme_media_sync_version" \
			"${INITIAL_MEDIA_SYNC_VERSION_FILE}" \
			"${EVIDENCE_DIR}/initial-media-sync-version.state"
	fi
}

cleanup_temp_post() {
	if [[ -n "${TEMP_POST_ID}" ]]; then
		wpcli post delete "$TEMP_POST_ID" --force --quiet 2>/dev/null || true
		log "Deleted temporary test product ID ${TEMP_POST_ID}"
		TEMP_POST_ID=""
	fi
}

on_exit() {
	local ec=$?
	cleanup_temp_post
	restore_initial_state
	exit "$ec"
}
trap on_exit EXIT

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
	# Force a trailing newline so grep line anchors are reliable across shells.
	result="$(wpcli eval "${php_code}; echo PHP_EOL;" 2>/dev/null | tr -d '\r' | grep -E '^(yes|no)$' | tail -n 1 || true)"
	log "Assert PHP (${label}): => ${result}"
	if [[ "$result" != "yes" ]]; then
		fail "Assertion failed (${label}); expected yes, got '${result}'"
	fi
	pass "$label"
}

require_cmd docker
require_cmd curl
require_cmd node

if ! docker info >/dev/null 2>&1; then
	fail "Docker Engine is not reachable (docker info failed)"
fi

log "=== Starting wp-env (if needed) ==="
# If this worktree already has a running Docker project (by name hint), reuse it.
# Do not call `wp-env start` when status points at a different hash than the live containers.
EXISTING_CLI="$(cli_container 2>/dev/null || true)"
if [[ -n "$EXISTING_CLI" ]]; then
	log "Found running worktree CLI container: ${EXISTING_CLI}"
	log "Skipping wp-env start to avoid creating a second project hash"
	pass "wp-env containers already running for this worktree"
else
	STATUS_OUT="$(wp_env status 2>&1 || true)"
	log "$STATUS_OUT"
	if printf '%s' "$STATUS_OUT" | grep -Eqi 'status:[[:space:]]*running'; then
		log "wp-env already running; skipping start"
		pass "wp-env already running"
	else
		wp_env start
		pass "wp-env start completed"
	fi
fi

CLI_NAME="$(cli_container)" || fail "Could not resolve unique CLI container for this worktree"
WP_NAME="$(wordpress_container_for_cli "$CLI_NAME")"
log "Selected CLI container: ${CLI_NAME}"
log "Expected WordPress container: ${WP_NAME}"
printf '%s\n' "$CLI_NAME" >"${EVIDENCE_DIR}/selected-cli-container.txt"
printf '%s\n' "$WP_NAME" >"${EVIDENCE_DIR}/selected-wordpress-container.txt"

# Host port binding evidence (must not silently target :8888 when this env is :8898).
HOST_PORTS="$(docker port "$WP_NAME" 2>/dev/null || true)"
log "WordPress container ports: ${HOST_PORTS}"
printf '%s\n' "$HOST_PORTS" >"${EVIDENCE_DIR}/wordpress-ports.txt"
echo "$HOST_PORTS" | grep -Eq '8898|8888|->[0-9]+' || fail "Could not read host port mapping for ${WP_NAME}"

# Theme mount must point at this worktree theme directory.
THEME_MOUNT="$(docker inspect -f '{{range .Mounts}}{{println .Source "->" .Destination}}{{end}}' "$WP_NAME" 2>/dev/null | grep -i 'ghahghah-theme' || true)"
log "Theme-related mounts:"
log "${THEME_MOUNT:-"(none matched)"}"
printf '%s\n' "$THEME_MOUNT" >"${EVIDENCE_DIR}/theme-mounts.txt"
MOUNT_OK=0
printf '%s' "$THEME_MOUNT" | tr '\\' '/' | tr '[:upper:]' '[:lower:]' | grep -Fq "$(printf '%s' "$ROOT" | tr '\\' '/' | tr '[:upper:]' '[:lower:]')" && MOUNT_OK=1
printf '%s' "$THEME_MOUNT" | tr '\\' '/' | tr '[:upper:]' '[:lower:]' | grep -Fq "$(printf '%s' "$WP_ENV_CWD" | tr '\\' '/' | tr '[:upper:]' '[:lower:]')" && MOUNT_OK=1
[[ "$MOUNT_OK" -eq 1 ]] || fail "WordPress container theme mount does not include this worktree ROOT; refusing to mutate plugins/themes"

SITE_URL="$(wpcli_opts option get siteurl 2>/dev/null | tr -d '\r' | tail -n 1)"
HOME_URL="$(wpcli_opts option get home 2>/dev/null | tr -d '\r' | tail -n 1)"
WP_VERSION="$(wpcli_opts core version 2>/dev/null | tr -d '\r' | tail -n 1)"
log "WordPress version: ${WP_VERSION}"
log "siteurl=${SITE_URL}"
log "home=${HOME_URL}"
[[ -n "$SITE_URL" ]] || fail "Could not determine siteurl from wp-env"
[[ -n "$HOME_URL" ]] || HOME_URL="$SITE_URL"
printf '%s\n' "$SITE_URL" >"${EVIDENCE_DIR}/siteurl.txt"
printf '%s\n' "$HOME_URL" >"${EVIDENCE_DIR}/home.txt"

# If host maps 8898, URLs must reference 8898 (prevents driving the sibling :8888 site).
if printf '%s' "$HOST_PORTS" | grep -Eq ':8898'; then
	printf '%s' "$SITE_URL" | grep -Eq ':8898' || fail "Container is on host :8898 but siteurl is '${SITE_URL}'"
	printf '%s' "$HOME_URL" | grep -Eq ':8898' || fail "Container is on host :8898 but home is '${HOME_URL}'"
	pass "siteurl/home match isolated port 8898"
elif printf '%s' "$HOST_PORTS" | grep -Eq ':8888'; then
	printf '%s' "$SITE_URL" | grep -Eq ':8888' || fail "Container is on host :8888 but siteurl is '${SITE_URL}'"
	pass "siteurl/home match port 8888"
fi

# Capture initial state BEFORE any theme/plugin mutation (options via skip-themes/plugins).
INITIAL_THEME="$(wpcli_opts option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
# is-active needs plugin API; use option active_plugins without loading theme.
INITIAL_PLUGIN_ACTIVE="$(
	wpcli_opts eval 'echo in_array( "ghahghah-core/ghahghah-core.php", (array) get_option( "active_plugins", array() ), true ) ? "yes" : "no"; echo PHP_EOL;' 2>/dev/null | tr -d '\r' | grep -E '^(yes|no)$' | tail -n 1 || echo unknown
)"
log "Initial stylesheet: ${INITIAL_THEME}"
log "Initial ghahghah-core active: ${INITIAL_PLUGIN_ACTIVE}"
printf '%s\n' "$INITIAL_THEME" >"${EVIDENCE_DIR}/initial-theme.txt"
printf '%s\n' "$INITIAL_PLUGIN_ACTIVE" >"${EVIDENCE_DIR}/initial-plugin-active.txt"
[[ -n "$INITIAL_THEME" ]] || fail "Could not read initial stylesheet"
[[ "$INITIAL_PLUGIN_ACTIVE" == "yes" || "$INITIAL_PLUGIN_ACTIVE" == "no" ]] || fail "Could not determine initial plugin active state"

INITIAL_THEME_MODS_FILE="${EVIDENCE_DIR}/initial-theme_mods_ghahghah-theme.json"
INITIAL_HERO_BANNER_PACK_FILE="${EVIDENCE_DIR}/initial-hero-banner-pack.txt"
INITIAL_MEDIA_SYNC_VERSION_FILE="${EVIDENCE_DIR}/initial-media-sync-version.txt"
snapshot_option "theme_mods_ghahghah-theme" "${INITIAL_THEME_MODS_FILE}" "${EVIDENCE_DIR}/initial-theme_mods_ghahghah-theme.state" --json
snapshot_option "ghahghah_hero_banner_pack" "${INITIAL_HERO_BANNER_PACK_FILE}" "${EVIDENCE_DIR}/initial-hero-banner-pack.state"
snapshot_option "ghahghah_theme_media_sync_version" "${INITIAL_MEDIA_SYNC_VERSION_FILE}" "${EVIDENCE_DIR}/initial-media-sync-version.state"
pass "Captured option snapshots (skip-themes/skip-plugins) before mutations"

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
wpcli plugin deactivate ghahghah-core
wpcli theme activate ghahghah-theme
ACTIVE_THEME="$(wpcli option get stylesheet 2>/dev/null | tr -d '\r' | tail -n 1)"
log "Active stylesheet: ${ACTIVE_THEME}"
[[ "$ACTIVE_THEME" == "ghahghah-theme" ]] || fail "Expected active stylesheet ghahghah-theme, got ${ACTIVE_THEME}"
pass "Active stylesheet is ghahghah-theme"

PLUGIN_ACTIVE="$(wpcli plugin is-active ghahghah-core >/dev/null 2>&1 && echo yes || echo no)"
[[ "$PLUGIN_ACTIVE" == "no" ]] || fail "ghahghah-core should be inactive in Scenario A"
pass "ghahghah-core is inactive"

# Confirm CPT unregistered after deactivate (retry once for slow bootstrap).
CPT_GONE="$(wpcli eval 'echo ( post_type_exists( "ghahghah_product" ) === false ) ? "yes" : "no"; echo PHP_EOL;' 2>/dev/null | tr -d '\r' | grep -E '^(yes|no)$' | tail -n 1 || true)"
if [[ "$CPT_GONE" != "yes" ]]; then
	log "CPT still present after deactivate; re-checking once"
	sleep 1
	wpcli plugin deactivate ghahghah-core --quiet 2>/dev/null || true
	CPT_GONE="$(wpcli eval 'echo ( post_type_exists( "ghahghah_product" ) === false ) ? "yes" : "no"; echo PHP_EOL;' 2>/dev/null | tr -d '\r' | grep -E '^(yes|no)$' | tail -n 1 || true)"
fi
[[ "$CPT_GONE" == "yes" ]] || fail "CPT still registered after Core deactivation (got '${CPT_GONE}')"

http_check "${HOME_URL}/" "A-homepage"
assert_php_yes 'echo ( post_type_exists( "ghahghah_product" ) === false ) ? "yes" : "no"' "A: CPT absent without Core"
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
pass "Scenario D complete"

log ""
log "=== SMOKE SUMMARY ==="
log "WordPress: ${WP_VERSION}"
log "Home: ${HOME_URL}"
log "CLI container: ${CLI_NAME}"
log "Passed assertions: ${PASS_COUNT}"
log "ALL SMOKE SCENARIOS PASSED"
# EXIT trap restores initial theme/plugin state.
exit 0
