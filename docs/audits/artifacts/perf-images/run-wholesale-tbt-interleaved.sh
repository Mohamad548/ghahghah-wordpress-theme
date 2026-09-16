#!/usr/bin/env bash
# Interleaved Wholesale TBT: Before/After pairs on :8898 without touching :8888.
# Switches only image-related theme PHP (+ leaves WebP files on disk unused for Before).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../../../.." && pwd)"
cd "$ROOT"

BASELINE="7add574586a6b1c636881efd1b237a38cd81363d"
AFTER_REF="def0c87b467a5d7ed77432153684f57cc7999e8b"
OUT="docs/audits/artifacts/perf-images/tbt-wholesale"
URL_JSON="docs/audits/artifacts/perf-images/urls.json"
CHROME="${CHROME_PATH:-C:/Program Files/Google/Chrome/Application/chrome.exe}"
export CHROME_PATH="$CHROME"

mkdir -p "$OUT"
if command -v cygpath >/dev/null 2>&1; then
  OUT_DIR="$(cygpath -w "$(cd "$OUT" && pwd)")"
else
  OUT_DIR="$(cd "$OUT" && pwd)"
fi
echo "OUT_DIR for Lighthouse: $OUT_DIR"

URL="$(node -e "const u=require('./${URL_JSON}'); process.stdout.write(u.wholesale)")"
echo "Wholesale URL: $URL"
echo "Baseline: $BASELINE"
echo "After ref: $AFTER_REF"
echo "HEAD now: $(git rev-parse HEAD)"

IMAGE_PATHS=(
  ghahghah-theme/inc/contact-page-settings.php
  ghahghah-theme/inc/media/sync-theme-media.php
  ghahghah-theme/inc/request-pages-settings.php
  ghahghah-theme/inc/single-article-settings.php
  ghahghah-theme/single-post.php
)

restore_after_files() {
  echo "=== Restoring AFTER image PHP from ${AFTER_REF} ==="
  git checkout "$AFTER_REF" -- "${IMAGE_PATHS[@]}"
  # Ensure working-tree smoke script stays as-is (not in IMAGE_PATHS).
}

switch_before() {
  echo "=== Switching theme image PHP to BASELINE ${BASELINE} ==="
  git checkout "$BASELINE" -- "${IMAGE_PATHS[@]}"
}

run_one() {
  local phase="$1"
  local pair="$2"
  local save_trace="${3:-0}"
  echo ""
  echo "######## RUN phase=${phase} pair=${pair} saveTrace=${save_trace} ########"
  # Confirm which hero asset HTML references
  local html_snip
  html_snip="$(curl -sS "$URL" | tr '\n' ' ' | grep -oE 'wholesale/[^\"[:space:]]+\.(png|webp)' | head -n 3 || true)"
  echo "HTML hero asset refs: ${html_snip:-"(none)"}"
  printf '%s\n' "$html_snip" >"${OUT}/${phase}-pair${pair}-html-assets.txt"

  PHASE="$phase" PAIR="$pair" OUT_DIR="$OUT_DIR" URL="$URL" SAVE_TRACE="$save_trace" \
    node docs/audits/artifacts/perf-images/run-wholesale-tbt.mjs
}

# Warm-up (discard)
echo "=== Warm-up (discard) ==="
curl -sS -o /dev/null -w "warmup_http:%{http_code}\n" "$URL" || true
sleep 2

SUMMARY="${OUT}/summary.jsonl"
: >"$SUMMARY"

for pair in 1 2 3; do
  switch_before
  sleep 1
  run_one before "$pair" 0
  cat "${OUT}/before-pair${pair}.extract.json" >>"$SUMMARY"
  echo >>"$SUMMARY"

  restore_after_files
  sleep 1
  run_one after "$pair" 0
  cat "${OUT}/after-pair${pair}.extract.json" >>"$SUMMARY"
  echo >>"$SUMMARY"
done

# Always leave AFTER files restored
restore_after_files

echo "=== Selecting slowest AFTER simulated TBT for trace ==="
node <<'NODE'
const fs = require('fs');
const path = require('path');
const out = 'docs/audits/artifacts/perf-images/tbt-wholesale';
const after = [1,2,3].map((p) => {
  const j = JSON.parse(fs.readFileSync(path.join(out, `after-pair${p}.extract.json`), 'utf8'));
  return j;
});
after.sort((a,b) => (b.simulated.tbt_ms||0) - (a.simulated.tbt_ms||0));
const slow = after[0];
fs.writeFileSync(path.join(out, 'slowest-after.json'), JSON.stringify(slow, null, 2));
console.log('SLOWEST', slow.tag, 'simTBT=', slow.simulated.tbt_ms, 'obsTBT=', slow.observed.tbt_ms);
NODE

SLOW_PAIR="$(node -e "console.log(JSON.parse(require('fs').readFileSync('docs/audits/artifacts/perf-images/tbt-wholesale/slowest-after.json','utf8')).pair)")"
echo "Re-running AFTER pair ${SLOW_PAIR} with -GA artifacts/trace"
restore_after_files
sleep 1
run_one after "${SLOW_PAIR}" 1

# Analyze long tasks from extract + trace if present
node <<'NODE'
const fs = require('fs');
const path = require('path');
const out = 'docs/audits/artifacts/perf-images/tbt-wholesale';
const slow = JSON.parse(fs.readFileSync(path.join(out, 'slowest-after.json'), 'utf8'));
const pair = slow.pair;
const extract = JSON.parse(fs.readFileSync(path.join(out, `after-pair${pair}.extract.json`), 'utf8'));
const rows = [];
for (const phase of ['before','after']) {
  for (const p of [1,2,3]) {
    const j = JSON.parse(fs.readFileSync(path.join(out, `${phase}-pair${p}.extract.json`), 'utf8'));
    rows.push({
      phase: j.phase,
      pair: j.pair,
      benchmarkIndex: j.benchmarkIndex,
      sim_tbt_ms: j.simulated.tbt_ms,
      obs_tbt_ms: j.observed.tbt_ms,
      sim_lcp_ms: j.simulated.lcp_ms,
      obs_lcp_ms: j.observed.lcp_ms,
      performance: j.performance,
      runtimeError: j.runtimeError,
      topLongTask: (j.longTasksTop||[])[0] || null,
    });
  }
}
const analysis = {
  generatedAt: new Date().toISOString(),
  rows,
  slowestAfter: extract,
  traceNote: 'See after-pairN-assets/ for -GA chrome trace if present',
};
// Find trace file
const assetsDir = path.join(out, `after-pair${pair}-assets`);
let tracePath = null;
if (fs.existsSync(assetsDir)) {
  const walk = (d) => {
    for (const ent of fs.readdirSync(d, { withFileTypes: true })) {
      const p = path.join(d, ent.name);
      if (ent.isDirectory()) walk(p);
      else if (/\.trace\.json$/i.test(ent.name) || /trace/i.test(ent.name) && ent.name.endsWith('.json')) {
        if (!tracePath) tracePath = p;
      }
    }
  };
  walk(assetsDir);
}
analysis.tracePath = tracePath;
if (tracePath) {
  // Lightweight parse: find long tasks from DevTools trace events
  const raw = fs.readFileSync(tracePath, 'utf8');
  let events = [];
  try {
    const parsed = JSON.parse(raw);
    events = parsed.traceEvents || parsed || [];
  } catch (e) {
    analysis.traceParseError = String(e);
  }
  const longs = [];
  for (const ev of events) {
    if (!ev || ev.ph !== 'X') continue;
    const durMs = (ev.dur || 0) / 1000;
    if (durMs < 50) continue;
    if (ev.cat && /devtools\.timeline|blink\.user_timing|disabled-by-default-devtools\.timeline/.test(ev.cat) || ev.name === 'RunTask' || ev.name === 'EvaluateScript' || ev.name === 'FunctionCall' || ev.name === 'v8.compile' || ev.name === 'ParseHTML') {
      longs.push({
        name: ev.name,
        cat: ev.cat,
        dur_ms: Math.round(durMs * 100) / 100,
        ts: ev.ts,
        url: ev.args?.data?.url || ev.args?.data?.fileName || ev.args?.url || null,
        functionName: ev.args?.data?.functionName || null,
      });
    }
  }
  longs.sort((a,b) => b.dur_ms - a.dur_ms);
  analysis.traceLongEventsTop20 = longs.slice(0, 20);
}
fs.writeFileSync(path.join(out, 'analysis.json'), JSON.stringify(analysis, null, 2));
console.log(JSON.stringify({ rows, slowPair: pair, tracePath, topTrace: (analysis.traceLongEventsTop20||[]).slice(0,5) }, null, 2));
NODE

echo "TBT interleaved complete. AFTER files restored."
git status -sb -- ghahghah-theme/inc/contact-page-settings.php ghahghah-theme/inc/request-pages-settings.php ghahghah-theme/inc/single-article-settings.php ghahghah-theme/single-post.php ghahghah-theme/inc/media/sync-theme-media.php | head -20
