/**
 * Reclassify samples using provable execution windows only.
 * Does not delete samples. Archives non-final classes under invalid-kept / unknown-kept.
 *
 * Usage: node scripts/nav-mark-invalid-smoke-window.cjs [runDir] [runLog]
 */
const fs = require('fs');
const path = require('path');
const meta = require('./lib/nav-measure-meta.cjs');

const runDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/nav-perf/sitewide/runs/post-slug',
);
const runLogPath = path.resolve(
  process.argv[3] || path.join(runDir, '..', 'post-slug-run.txt'),
);

const smokeWindow = meta.DEFAULT_SMOKE_WINDOW;
const markedAt = new Date().toISOString();
const invalidKept = path.join(runDir, 'invalid-kept');
const unknownKept = path.join(runDir, 'unknown-kept');
fs.mkdirSync(invalidKept, { recursive: true });
fs.mkdirSync(unknownKept, { recursive: true });

let logWindows = new Map();
if (fs.existsSync(runLogPath)) {
  const parsed = meta.parseRunLogWindows(fs.readFileSync(runLogPath, 'utf8'));
  logWindows = parsed.windows;
}

function sampleKey(row) {
  return [row.mode, row.device, row.cacheMode, row.targetUrl, row.runIndex].join('|');
}

function loadDir(dir) {
  if (!fs.existsSync(dir)) return [];
  return fs
    .readdirSync(dir)
    .filter((f) => f.startsWith('post-slug__') && f.endsWith('.json'))
    .map((f) => ({ file: f, dir, full: path.join(dir, f) }));
}

const candidates = [...loadDir(runDir), ...loadDir(invalidKept), ...loadDir(unknownKept)];
const index = [];
const counts = { VALID: 0, INVALID: 0, FAILED: 0, UNKNOWN: 0 };

for (const c of candidates) {
  let row;
  try {
    row = JSON.parse(fs.readFileSync(c.full, 'utf8'));
  } catch (e) {
    counts.UNKNOWN++;
    index.push({ file: c.file, validity: 'UNKNOWN', reason: 'unreadable-json:' + e.message });
    continue;
  }

  // Attach run-log window only when sample lacks explicit startedAt/endedAt and log is stamped.
  if ((!row.startedAt || !row.endedAt) && logWindows.size) {
    const w = logWindows.get(sampleKey(row));
    if (w) {
      row.runWindow = w;
    }
  }

  const classified = meta.classifySampleValidity(row, smokeWindow);
  row.validity = classified.validity;
  row.validityReason = classified.reason;
  row.executionWindow = classified.window;
  row.smokeWindow = smokeWindow;
  row.markedAt = markedAt;
  // Never present mark time as execution time.
  delete row.codeHeadAtMark;
  // Keep fileBirth only as forensic hint, not as validity proof.
  if (!row.fileBirthHint && row.fileBirth) row.fileBirthHint = row.fileBirth;

  fs.writeFileSync(c.full, JSON.stringify(row, null, 2));
  counts[classified.validity]++;

  const destDir =
    classified.validity === 'VALID'
      ? runDir
      : classified.validity === 'UNKNOWN'
        ? unknownKept
        : invalidKept;

  if (path.resolve(c.dir) !== path.resolve(destDir)) {
    const dest = path.join(destDir, c.file);
    fs.copyFileSync(c.full, dest);
    fs.unlinkSync(c.full);
  }

  index.push({
    file: c.file,
    sampleId: row.sampleId || c.file.replace(/\.json$/, ''),
    validity: classified.validity,
    reason: classified.reason,
    executionWindow: classified.window,
    markedAt,
    mode: row.mode,
    device: row.device,
    cacheMode: row.cacheMode,
    targetUrl: row.targetUrl,
    ttfbMs: row.ttfbMs ?? null,
  });
}

const out = {
  at: markedAt,
  runDir,
  runLogPath: fs.existsSync(runLogPath) ? runLogPath : null,
  smokeWindow,
  note:
    'Validity uses sample startedAt/endedAt or stamped run-log windows only. File birthtime is not proof of VALID. markedAt is classification time, not execution time.',
  counts: {
    files: candidates.length,
    ...counts,
  },
  index,
};
fs.writeFileSync(path.join(runDir, 'sample-validity-index.json'), JSON.stringify(out, null, 2));
console.log(JSON.stringify(out.counts, null, 2));
