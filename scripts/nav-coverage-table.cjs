/**
 * Build coverage table from inventory + measure validity index.
 * Usage: node scripts/nav-coverage-table.cjs [inventory] [runDir] [outJson]
 */
const fs = require('fs');
const path = require('path');

const invPath = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/nav-perf/sitewide/inventory-post-slug/inventory.json',
);
const runDir = path.resolve(
  process.argv[3] || 'docs/audits/artifacts/nav-perf/sitewide/runs/post-slug',
);
const outPath = path.resolve(process.argv[4] || path.join(runDir, 'coverage-table.json'));
const healthPath = path.resolve(
  process.argv[5] || 'docs/audits/artifacts/url-migration/health-clean/health.json',
);

const inv = JSON.parse(fs.readFileSync(invPath, 'utf8'));
const plan = fs.existsSync(path.join(runDir, 'scenario-plan.json'))
  ? JSON.parse(fs.readFileSync(path.join(runDir, 'scenario-plan.json'), 'utf8'))
  : null;
const validity = fs.existsSync(path.join(runDir, 'sample-validity-index.json'))
  ? JSON.parse(fs.readFileSync(path.join(runDir, 'sample-validity-index.json'), 'utf8'))
  : null;
const health = fs.existsSync(healthPath) ? JSON.parse(fs.readFileSync(healthPath, 'utf8')) : null;

const BASE = (inv.base || 'http://localhost:8898').replace(/\/$/, '');
function norm(u) {
  try {
    const x = new URL(u, BASE);
    return x.origin + (x.pathname.replace(/\/$/, '') || '/') + '/' + (x.search || '');
  } catch {
    return String(u);
  }
}

const active = fs
  .readdirSync(runDir)
  .filter((f) => f.startsWith('post-slug__') && f.endsWith('.json'))
  .map((f) => {
    try {
      return JSON.parse(fs.readFileSync(path.join(runDir, f), 'utf8'));
    } catch {
      return null;
    }
  })
  .filter(Boolean);

const keptDir = path.join(runDir, 'invalid-kept');
const kept = fs.existsSync(keptDir)
  ? fs
      .readdirSync(keptDir)
      .filter((f) => f.endsWith('.json'))
      .map((f) => {
        try {
          return JSON.parse(fs.readFileSync(path.join(keptDir, f), 'utf8'));
        } catch {
          return null;
        }
      })
      .filter(Boolean)
  : [];

const byKey = new Map();
for (const r of [...active, ...kept]) {
  const key = [r.mode, r.device, r.cacheMode, norm(r.targetUrl), r.runIndex].join('|');
  byKey.set(key, r);
}

const destinations = [...new Set((inv.uniqueDestinations || []).map((d) => d.url).filter((u) => !/sample-page|hello-world/i.test(u)))];
const navSet = new Set((inv.navClickHrefs || []).map((d) => norm(d.url)));
const devices = ['desktop', 'mobile'];
const caches = ['cache-disabled', 'cache-enabled-prepared'];
const modes = ['click', 'goto', 'reload'];
const repeats = plan?.repeats || 2;

const rows = [];
for (const url of destinations) {
  const n = norm(url);
  for (const mode of modes) {
    if (mode === 'click' && !navSet.has(n)) {
      rows.push({
        url,
        mode,
        device: '—',
        cacheMode: '—',
        status: 'NOT RUN',
        reason: 'not a nav-click candidate',
      });
      continue;
    }
    for (const device of devices) {
      for (const cacheMode of caches) {
        const samples = [];
        for (let i = 1; i <= repeats; i++) {
          const key = [mode, device, cacheMode, n, i].join('|');
          if (byKey.has(key)) samples.push(byKey.get(key));
        }
        const valid = samples.filter((s) => s.validity === 'VALID' && s.ttfbMs != null);
        const invalid = samples.filter((s) => s.validity === 'INVALID');
        const failed = samples.filter((s) => s.validity === 'FAILED' || s.measured === false);
        let status = 'NOT RUN';
        let reason = 'no samples yet';
        if (valid.length >= 1) {
          status = 'PASS';
          reason = `VALID n=${valid.length}/${repeats}`;
        } else if (invalid.length >= 1 && failed.length === 0 && valid.length === 0) {
          status = 'INVALID';
          reason = 'smoke-window overlap only; awaiting re-run';
        } else if (failed.length >= 1) {
          status = 'FAILED';
          reason = failed.map((s) => s.error || s.validityReason).filter(Boolean).slice(0, 2).join('; ');
        }
        rows.push({
          url,
          mode,
          device,
          cacheMode,
          status,
          reason,
          validCount: valid.length,
          invalidCount: invalid.length,
          failedCount: failed.length,
        });
      }
    }
  }
}

const counts = {
  PASS: rows.filter((r) => r.status === 'PASS').length,
  FAILED: rows.filter((r) => r.status === 'FAILED').length,
  INVALID: rows.filter((r) => r.status === 'INVALID').length,
  NOT_RUN: rows.filter((r) => r.status === 'NOT RUN').length,
};

const sampleCounts = {
  VALID: active.filter((r) => r.validity === 'VALID').length,
  INVALID: kept.filter((r) => r.validity === 'INVALID').length,
  FAILED: kept.filter((r) => r.validity === 'FAILED').length,
  activeOnDisk: active.length,
  keptOnDisk: kept.length,
};

const out = {
  at: new Date().toISOString(),
  measureStatus: 'PARTIAL',
  partialReason:
    'Sitewide timing suite not complete: only a subset of planned 424 samples are VALID on disk; INVALID/FAILED preserved in invalid-kept and scheduled for re-run; NOT RUN remains for unfinished goto/reload matrix.',
  plannedTotal: plan?.planned?.total || 424,
  sampleCounts,
  scenarioRowCounts: counts,
  healthClean: health?.counts || null,
  validityIndex: validity?.counts || null,
  note: 'Do not declare sitewide success while NOT_RUN or INVALID remain for required goto/reload coverage.',
  rows,
};

fs.writeFileSync(outPath, JSON.stringify(out, null, 2));
console.log(JSON.stringify({ measureStatus: out.measureStatus, sampleCounts, scenarioRowCounts: counts, healthClean: out.healthClean }, null, 2));
