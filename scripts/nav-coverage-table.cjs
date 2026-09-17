/**
 * Build coverage table from scenario-plan + active/archived samples.
 * Usage: node scripts/nav-coverage-table.cjs [inventory] [runDir] [outJson]
 */
const fs = require('fs');
const path = require('path');
const meta = require('./lib/nav-measure-meta.cjs');

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
const planPath = path.join(runDir, 'scenario-plan.json');
if (!fs.existsSync(planPath)) {
  console.error('Missing scenario-plan.json — generate via nav-sitewide-measure or write plan first');
  process.exit(1);
}
const plan = JSON.parse(fs.readFileSync(planPath, 'utf8'));
const health = fs.existsSync(healthPath) ? JSON.parse(fs.readFileSync(healthPath, 'utf8')) : null;

const BASE = (inv.base || plan.base || 'http://localhost:8898').replace(/\/$/, '');
function norm(u) {
  try {
    const x = new URL(u, BASE);
    return x.origin + (x.pathname.replace(/\/$/, '') || '/') + '/' + (x.search || '');
  } catch {
    return String(u);
  }
}

function loadSamples(dir) {
  if (!fs.existsSync(dir)) return [];
  return fs
    .readdirSync(dir)
    .filter((f) => f.startsWith('post-slug__') && f.endsWith('.json'))
    .map((f) => {
      try {
        const row = JSON.parse(fs.readFileSync(path.join(dir, f), 'utf8'));
        row._archive = path.basename(dir);
        row._file = f;
        return row;
      } catch {
        return null;
      }
    })
    .filter(Boolean);
}

const active = loadSamples(runDir);
const archived = [
  ...loadSamples(path.join(runDir, 'invalid-kept')),
  ...loadSamples(path.join(runDir, 'unknown-kept')),
];

/** Prefer active (new) over archive for the same scenario key. */
function preferSamples(list) {
  const map = new Map();
  // Archive first, then active overwrites.
  for (const r of list) {
    const key = [r.mode, r.device, r.cacheMode, norm(r.targetUrl), r.runIndex].join('|');
    const prev = map.get(key);
    if (!prev) {
      map.set(key, r);
      continue;
    }
    const prevActive = prev._archive === path.basename(runDir) || prev._archive === '.';
    const nextActive = r._archive === path.basename(runDir) || r._archive === '.';
    if (nextActive && !prevActive) map.set(key, r);
    else if (nextActive === prevActive) {
      // Prefer VALID over others when both same tier.
      const rank = { VALID: 3, FAILED: 2, INVALID: 1, UNKNOWN: 0 };
      if ((rank[r.validity] || 0) > (rank[prev.validity] || 0)) map.set(key, r);
    }
  }
  return map;
}

const byKey = preferSamples([
  ...archived.map((r) => ({ ...r, _archive: r._archive })),
  ...active.map((r) => ({ ...r, _archive: path.basename(runDir) })),
]);

const devices = plan.devices || ['desktop', 'mobile'];
const caches = plan.cacheModes || ['cache-disabled', 'cache-enabled-prepared'];
const methods = plan.methods || ['click', 'goto', 'reload'];
const repeats = plan.repeats || 2;
const destinations = plan.destinations || [];
const navClicks = new Set((plan.navClicks || []).map(norm));
const plannedTotal = plan.plannedSamples || plan.planned?.total;
if (!plannedTotal) {
  console.error('scenario-plan missing plannedSamples');
  process.exit(1);
}

const rows = [];
let plannedCells = 0;
for (const url of destinations) {
  const n = norm(url);
  for (const mode of methods) {
    if (mode === 'click' && !navClicks.has(n)) {
      // click not planned for this destination — not a NOT RUN plan cell
      continue;
    }
    for (const device of devices) {
      for (const cacheMode of caches) {
        plannedCells++;
        const samples = [];
        for (let i = 1; i <= repeats; i++) {
          const key = [mode, device, cacheMode, n, i].join('|');
          if (byKey.has(key)) samples.push(byKey.get(key));
        }
        const valid = samples.filter((s) => s.validity === 'VALID' && s.ttfbMs != null);
        const invalid = samples.filter((s) => s.validity === 'INVALID');
        const failed = samples.filter((s) => s.validity === 'FAILED');
        const unknown = samples.filter((s) => s.validity === 'UNKNOWN');
        // UNKNOWN never counts toward final PASS/COMPLETE.
        const cell = meta.cellStatus({
          validCount: valid.length,
          failedCount: failed.length,
          invalidCount: invalid.length,
          unknownCount: unknown.length,
          plannedRepeats: repeats,
        });
        rows.push({
          url,
          mode,
          device,
          cacheMode,
          status: cell.status,
          reason: cell.reason,
          validCount: valid.length,
          invalidCount: invalid.length,
          failedCount: failed.length,
          unknownCount: unknown.length,
          plannedRepeats: repeats,
        });
      }
    }
  }
}

/** Samples on disk that are not in the current plan. */
const plannedKeys = new Set();
for (const url of destinations) {
  const n = norm(url);
  for (const mode of methods) {
    if (mode === 'click' && !navClicks.has(n)) continue;
    for (const device of devices) {
      for (const cacheMode of caches) {
        for (let i = 1; i <= repeats; i++) {
          plannedKeys.add([mode, device, cacheMode, n, i].join('|'));
        }
      }
    }
  }
}
const outOfPlan = [];
for (const [key, r] of byKey.entries()) {
  if (!plannedKeys.has(key)) {
    outOfPlan.push({
      key,
      validity: r.validity,
      targetUrl: r.targetUrl,
      mode: r.mode,
      device: r.device,
      cacheMode: r.cacheMode,
      runIndex: r.runIndex,
      archive: r._archive,
    });
  }
}

const scenarioRowCounts = {
  COMPLETE: rows.filter((r) => r.status === 'COMPLETE').length,
  PARTIAL: rows.filter((r) => r.status === 'PARTIAL').length,
  FAILED: rows.filter((r) => r.status === 'FAILED').length,
  INVALID: rows.filter((r) => r.status === 'INVALID').length,
  UNKNOWN: rows.filter((r) => r.status === 'UNKNOWN').length,
  NOT_RUN: rows.filter((r) => r.status === 'NOT RUN').length,
};

const validSamples = [...byKey.values()].filter((s) => s.validity === 'VALID').length;
const overall = meta.overallMeasureStatus({
  plannedSampleCount: plannedTotal,
  completeCells: scenarioRowCounts.COMPLETE,
  plannedCells,
  validSamples,
});

const sampleCounts = {
  VALID: [...byKey.values()].filter((s) => s.validity === 'VALID').length,
  INVALID: [...byKey.values()].filter((s) => s.validity === 'INVALID').length,
  FAILED: [...byKey.values()].filter((s) => s.validity === 'FAILED').length,
  UNKNOWN: [...byKey.values()].filter((s) => s.validity === 'UNKNOWN').length,
  activeOnDisk: active.length,
  archivedOnDisk: archived.length,
};

const failureHistory = archived
  .filter((s) => s.validity === 'FAILED' || s.validity === 'INVALID')
  .map((s) => ({
    file: s._file,
    archive: s._archive,
    validity: s.validity,
    reason: s.validityReason,
    targetUrl: s.targetUrl,
    mode: s.mode,
    device: s.device,
    cacheMode: s.cacheMode,
    runIndex: s.runIndex,
    startedAt: s.startedAt || null,
    endedAt: s.endedAt || null,
  }));

const out = {
  at: new Date().toISOString(),
  measureStatus: overall.status,
  measureStatusReason: overall.reason,
  note:
    'COMPLETE means planned VALID repeats are present — not a performance quality judgment. UNKNOWN samples are excluded from final PASS/COMPLETE.',
  plannedFromScenario: {
    plannedSamples: plannedTotal,
    plannedCells,
    repeats,
    devices,
    methods,
    cacheModes: caches,
    uniqueDestinations: destinations.length,
    navClickTargets: navClicks.size,
  },
  sampleCounts,
  scenarioRowCounts,
  outOfPlanCount: outOfPlan.length,
  outOfPlan,
  failureHistoryCount: failureHistory.length,
  failureHistory,
  healthClean: health?.counts || null,
  rows,
};

fs.writeFileSync(outPath, JSON.stringify(out, null, 2));
console.log(
  JSON.stringify(
    {
      measureStatus: out.measureStatus,
      measureStatusReason: out.measureStatusReason,
      sampleCounts,
      scenarioRowCounts,
      outOfPlanCount: outOfPlan.length,
      healthClean: out.healthClean,
    },
    null,
    2,
  ),
);
