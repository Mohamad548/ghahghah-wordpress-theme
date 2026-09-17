/**
 * Shared metadata / validity helpers for sitewide nav measure.
 * Pure functions — safe for unit tests without Puppeteer or WP.
 */
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

/** Bump when measure semantics change (not docs). */
const TOOL_VERSION = 'nav-sitewide-measure/2.1.0';

const DEFAULT_SMOKE_WINDOW = {
  start: '2026-09-17T09:57:49.803Z',
  end: '2026-09-17T10:07:21.697Z',
  sources: [
    'terminals/629806 started_at (earliest smoke)',
    'terminals/629807 smoke killed exit 1',
    'terminals/629809 theme activate confirmed',
  ],
};

function sha256Text(text) {
  return crypto.createHash('sha256').update(String(text)).digest('hex');
}

function sha256File(filePath) {
  if (!fs.existsSync(filePath)) return null;
  return sha256Text(fs.readFileSync(filePath));
}

function gitHead(cwd) {
  try {
    const r = spawnSync('git', ['rev-parse', 'HEAD'], {
      cwd: cwd || process.cwd(),
      encoding: 'utf8',
      shell: false,
    });
    if (r.status === 0) return String(r.stdout || '').trim();
  } catch (_) {}
  return null;
}

/**
 * Hash of scripts that affect measurement semantics (not docs/reports).
 */
function effectiveCodeFingerprint(repoRoot) {
  const root = repoRoot || process.cwd();
  const files = [
    'scripts/nav-sitewide-measure.cjs',
    'scripts/lib/nav-measure-meta.cjs',
  ];
  const parts = files.map((rel) => {
    const abs = path.join(root, rel);
    return `${rel}:${sha256File(abs) || 'MISSING'}`;
  });
  return sha256Text(parts.join('\n'));
}

/**
 * Settings that affect which scenarios run / how cache+device are labeled.
 * Docs-only edits must not change this fingerprint.
 */
function settingsFingerprint({
  base,
  phase,
  repeats,
  devices,
  skipClick,
  inventorySha,
  smokeWindow,
}) {
  const payload = {
    base: String(base || ''),
    phase: String(phase || ''),
    repeats: Number(repeats),
    devices: [...(devices || [])].map(String).sort(),
    skipClick: !!skipClick,
    inventorySha: inventorySha || null,
    smokeStart: smokeWindow?.start || null,
    smokeEnd: smokeWindow?.end || null,
  };
  return sha256Text(JSON.stringify(payload));
}

function inventoryFileSha(inventoryPath) {
  return sha256File(inventoryPath);
}

function buildRunContext(opts) {
  const smokeWindow = opts.smokeWindow || DEFAULT_SMOKE_WINDOW;
  const inventorySha = opts.inventoryPath ? inventoryFileSha(opts.inventoryPath) : null;
  return {
    toolVersion: TOOL_VERSION,
    gitHead: gitHead(opts.repoRoot),
    effectiveCodeSha: effectiveCodeFingerprint(opts.repoRoot),
    settingsFingerprint: settingsFingerprint({
      base: opts.base,
      phase: opts.phase,
      repeats: opts.repeats,
      devices: opts.devices,
      skipClick: opts.skipClick,
      inventorySha,
      smokeWindow,
    }),
    inventorySha,
    smokeWindow,
  };
}

function rangesOverlap(aStart, aEnd, bStart, bEnd) {
  const as = Date.parse(aStart);
  const ae = Date.parse(aEnd);
  const bs = Date.parse(bStart);
  const be = Date.parse(bEnd);
  if ([as, ae, bs, be].some((n) => Number.isNaN(n))) return false;
  return as <= be && bs <= ae;
}

/**
 * Resolve sample execution window.
 * Prefer explicit startedAt/endedAt. Never treat mark time or file birth as proof of execution.
 */
function resolveSampleWindow(row) {
  if (row?.startedAt && row?.endedAt) {
    return {
      start: row.startedAt,
      end: row.endedAt,
      source: 'sample-fields',
      provable: true,
    };
  }
  if (row?.runWindow?.start && row?.runWindow?.end && row.runWindow.source === 'run-log') {
    return {
      start: row.runWindow.start,
      end: row.runWindow.end,
      source: 'run-log',
      provable: true,
    };
  }
  return { start: null, end: null, source: null, provable: false };
}

function classifySampleValidity(row, smokeWindow = DEFAULT_SMOKE_WINDOW) {
  const window = resolveSampleWindow(row);
  if (row?.measured === false || row?.error) {
    // Still check smoke overlap when window known → INVALID takes precedence for contamination.
    if (window.provable && rangesOverlap(window.start, window.end, smokeWindow.start, smokeWindow.end)) {
      return {
        validity: 'INVALID',
        reason: 'overlap-smoke-disruption-window',
        window,
      };
    }
    return {
      validity: 'FAILED',
      reason: row.error || 'measured=false',
      window,
    };
  }
  if (!window.provable) {
    return {
      validity: 'UNKNOWN',
      reason: 'execution-window-not-provable',
      window,
    };
  }
  if (rangesOverlap(window.start, window.end, smokeWindow.start, smokeWindow.end)) {
    return {
      validity: 'INVALID',
      reason: 'overlap-smoke-disruption-window',
      window,
    };
  }
  if (row.ttfbMs == null) {
    return { validity: 'UNKNOWN', reason: 'no-ttfb', window };
  }
  return { validity: 'VALID', reason: 'provable-window-outside-smoke', window };
}

/**
 * Resume only explicit VALID with compatible tool/settings/scenario.
 * Doc-only changes leave settingsFingerprint + effectiveCodeSha unchanged.
 */
function isResumeCompatible(existing, expected) {
  if (!existing || existing.validity !== 'VALID') {
    return { ok: false, reason: 'not-explicit-VALID' };
  }
  if (existing.measured === false || existing.ttfbMs == null) {
    return { ok: false, reason: 'missing-ttfb-or-failed' };
  }
  if (expected.sampleId && existing.sampleId && existing.sampleId !== expected.sampleId) {
    return { ok: false, reason: 'sampleId-mismatch' };
  }
  for (const key of ['mode', 'device', 'cacheMode', 'targetUrl']) {
    if (expected[key] != null && existing[key] != null && existing[key] !== expected[key]) {
      return { ok: false, reason: `${key}-mismatch` };
    }
  }
  if (expected.runIndex != null && existing.runIndex != null && existing.runIndex !== expected.runIndex) {
    return { ok: false, reason: 'runIndex-mismatch' };
  }
  if (existing.toolVersion !== expected.toolVersion) {
    return { ok: false, reason: 'toolVersion-mismatch' };
  }
  if (existing.effectiveCodeSha !== expected.effectiveCodeSha) {
    return { ok: false, reason: 'effectiveCodeSha-mismatch' };
  }
  if (existing.settingsFingerprint !== expected.settingsFingerprint) {
    return { ok: false, reason: 'settingsFingerprint-mismatch' };
  }
  return { ok: true, reason: 'compatible' };
}

/**
 * Parse run log lines of form:
 *   2026-09-17T10:00:00.000Z click http://... desktop cache-disabled #1
 * Older logs without timestamps cannot prove windows.
 */
function parseRunLogWindows(logText) {
  const lines = String(logText || '').split(/\r?\n/);
  const events = [];
  const tsRe =
    /^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z)\s+(click|goto|reload)\s+(\S+)\s+(\S+)\s+(\S+)\s+#(\d+)\s*$/;
  const legacyRe = /^(click|goto|reload)\s+(\S+)\s+(\S+)\s+(\S+)\s+#(\d+)\s*$/;
  for (const line of lines) {
    let m = line.match(tsRe);
    if (m) {
      events.push({
        at: m[1],
        mode: m[2],
        targetUrl: m[3],
        device: m[4],
        cacheMode: m[5],
        runIndex: parseInt(m[6], 10),
        hasTimestamp: true,
      });
      continue;
    }
    m = line.match(legacyRe);
    if (m) {
      events.push({
        at: null,
        mode: m[1],
        targetUrl: m[2],
        device: m[3],
        cacheMode: m[4],
        runIndex: parseInt(m[5], 10),
        hasTimestamp: false,
      });
    }
  }
  const windows = new Map();
  for (let i = 0; i < events.length; i++) {
    const ev = events[i];
    if (!ev.hasTimestamp) continue;
    const next = events.slice(i + 1).find((e) => e.hasTimestamp);
    const end = next ? next.at : ev.at;
    const key = [ev.mode, ev.device, ev.cacheMode, ev.targetUrl, ev.runIndex].join('|');
    windows.set(key, { start: ev.at, end, source: 'run-log' });
  }
  return { events, windows };
}

function cellStatus({ validCount, failedCount, invalidCount, unknownCount, plannedRepeats }) {
  const need = plannedRepeats;
  if (validCount >= need) {
    return { status: 'COMPLETE', reason: `VALID ${validCount}/${need}` };
  }
  if (validCount > 0) {
    return { status: 'PARTIAL', reason: `VALID ${validCount}/${need}` };
  }
  if (failedCount > 0) {
    return { status: 'FAILED', reason: `FAILED n=${failedCount}` };
  }
  if (invalidCount > 0) {
    return { status: 'INVALID', reason: `INVALID n=${invalidCount}` };
  }
  if (unknownCount > 0) {
    return { status: 'UNKNOWN', reason: `UNKNOWN n=${unknownCount}` };
  }
  return { status: 'NOT RUN', reason: 'no samples' };
}

function overallMeasureStatus({ plannedSampleCount, completeCells, plannedCells, validSamples }) {
  if (!plannedSampleCount || plannedSampleCount <= 0) {
    return { status: 'UNKNOWN', reason: 'scenario-plan missing planned sample count' };
  }
  if (completeCells === plannedCells && validSamples >= plannedSampleCount) {
    return {
      status: 'COMPLETE',
      reason: 'all planned cells COMPLETE with required VALID repeats (not a speed judgment)',
    };
  }
  return {
    status: 'PARTIAL',
    reason: `completeCells=${completeCells}/${plannedCells}; validSamples=${validSamples}/${plannedSampleCount}`,
  };
}

module.exports = {
  TOOL_VERSION,
  DEFAULT_SMOKE_WINDOW,
  sha256Text,
  sha256File,
  gitHead,
  effectiveCodeFingerprint,
  settingsFingerprint,
  inventoryFileSha,
  buildRunContext,
  rangesOverlap,
  resolveSampleWindow,
  classifySampleValidity,
  isResumeCompatible,
  parseRunLogWindows,
  cellStatus,
  overallMeasureStatus,
};
