/**
 * Mark post-slug samples overlapping the Smoke disruption window as INVALID.
 * Does not delete samples or rewrite measured metrics into PASS.
 *
 * Usage: node scripts/nav-mark-invalid-smoke-window.cjs [runDir]
 */
const fs = require('fs');
const path = require('path');

const runDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/nav-perf/sitewide/runs/post-slug',
);
const SMOKE_START = Date.parse('2026-09-17T09:57:49.803Z');
const SMOKE_END = Date.parse('2026-09-17T10:07:21.697Z');
const HEAD = 'e11598457d01c91aade2274190cf27d0377a9a96';

const files = fs.readdirSync(runDir).filter((f) => f.startsWith('post-slug__') && f.endsWith('.json'));
const index = [];
let invalid = 0;
let valid = 0;
let failed = 0;
let unknown = 0;

for (const f of files) {
  const p = path.join(runDir, f);
  const st = fs.statSync(p);
  let row;
  try {
    row = JSON.parse(fs.readFileSync(p, 'utf8'));
  } catch (e) {
    index.push({ file: f, validity: 'INVALID', reason: 'unreadable-json:' + e.message });
    invalid++;
    continue;
  }
  // Prefer birthtime: later validity stamps rewrite mtime.
  const mtime = st.birthtimeMs || st.ctimeMs || st.mtimeMs;
  const overlap = mtime >= SMOKE_START && mtime <= SMOKE_END;
  let validity;
  let reason;
  if (overlap) {
    validity = 'INVALID';
    reason = 'overlap-smoke-disruption-window';
    invalid++;
  } else if (row.measured === false || row.error) {
    validity = 'FAILED';
    reason = row.error || 'measured=false';
    failed++;
  } else if (row.ttfbMs != null && row.measured !== false) {
    validity = 'VALID';
    reason = 'outside-smoke-window-with-ttfb';
    valid++;
  } else {
    validity = 'UNKNOWN';
    reason = 'no-ttfb';
    unknown++;
  }

  row.validity = validity;
  row.validityReason = reason;
  row.smokeWindow = {
    start: '2026-09-17T09:57:49.803Z',
    end: '2026-09-17T10:07:21.697Z',
  };
  row.fileMtime = new Date(mtime).toISOString();
  row.codeHeadAtMark = HEAD;
  fs.writeFileSync(p, JSON.stringify(row, null, 2));

  index.push({
    file: f,
    sampleId: row.sampleId || f.replace(/\.json$/, ''),
    validity,
    reason,
    measured: row.measured !== false,
    ttfbMs: row.ttfbMs ?? null,
    mode: row.mode,
    device: row.device,
    cacheMode: row.cacheMode,
    targetUrl: row.targetUrl,
    mtime: row.fileMtime,
  });
}

const out = {
  at: new Date().toISOString(),
  runDir,
  smokeWindow: {
    start: '2026-09-17T09:57:49.803Z',
    end: '2026-09-17T10:07:21.697Z',
    sources: [
      'terminals/629806 started_at (earliest smoke)',
      'terminals/629807 smoke killed exit 1',
      'terminals/629809 theme activate confirmed',
    ],
  },
  counts: {
    files: files.length,
    VALID: valid,
    INVALID: invalid,
    FAILED: failed,
    UNKNOWN: unknown,
  },
  index,
};
fs.writeFileSync(path.join(runDir, 'sample-validity-index.json'), JSON.stringify(out, null, 2));
console.log(JSON.stringify(out.counts, null, 2));
