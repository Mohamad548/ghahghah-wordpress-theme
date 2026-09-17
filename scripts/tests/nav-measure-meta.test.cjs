/**
 * Small unit tests for nav-measure-meta (no Puppeteer / no WP).
 * Usage: node scripts/tests/nav-measure-meta.test.cjs
 */
const assert = require('assert');
const path = require('path');
const meta = require('../lib/nav-measure-meta.cjs');

let passed = 0;
function check(name, fn) {
  try {
    fn();
    passed++;
    console.log('PASS', name);
  } catch (e) {
    console.error('FAIL', name, e.message || e);
    process.exitCode = 1;
  }
}

check('rangesOverlap detects partial overlap', () => {
  assert.strictEqual(
    meta.rangesOverlap('2026-09-17T10:00:00Z', '2026-09-17T10:05:00Z', '2026-09-17T09:57:49Z', '2026-09-17T10:07:21Z'),
    true,
  );
  assert.strictEqual(
    meta.rangesOverlap('2026-09-17T10:08:00Z', '2026-09-17T10:09:00Z', '2026-09-17T09:57:49Z', '2026-09-17T10:07:21Z'),
    false,
  );
});

check('classify VALID only with provable window outside smoke', () => {
  const row = {
    measured: true,
    ttfbMs: 100,
    startedAt: '2026-09-17T09:50:00.000Z',
    endedAt: '2026-09-17T09:50:20.000Z',
  };
  const r = meta.classifySampleValidity(row);
  assert.strictEqual(r.validity, 'VALID');
});

check('classify INVALID when sample window overlaps smoke', () => {
  const row = {
    measured: true,
    ttfbMs: 100,
    startedAt: '2026-09-17T10:00:00.000Z',
    endedAt: '2026-09-17T10:00:30.000Z',
  };
  const r = meta.classifySampleValidity(row);
  assert.strictEqual(r.validity, 'INVALID');
  assert.strictEqual(r.reason, 'overlap-smoke-disruption-window');
});

check('classify UNKNOWN when execution window not provable (birthtime ignored)', () => {
  const row = {
    measured: true,
    ttfbMs: 100,
    fileBirth: '2026-09-17T09:50:00.000Z',
    codeHeadAtMark: 'abc',
  };
  const r = meta.classifySampleValidity(row);
  assert.strictEqual(r.validity, 'UNKNOWN');
  assert.strictEqual(r.reason, 'execution-window-not-provable');
});

check('settingsFingerprint stable for docs-only (same settings object)', () => {
  const a = meta.settingsFingerprint({
    base: 'http://localhost:8898',
    phase: 'post-slug',
    repeats: 2,
    devices: ['mobile', 'desktop'],
    skipClick: false,
    inventorySha: 'inv',
    smokeWindow: meta.DEFAULT_SMOKE_WINDOW,
  });
  const b = meta.settingsFingerprint({
    base: 'http://localhost:8898',
    phase: 'post-slug',
    repeats: 2,
    devices: ['desktop', 'mobile'],
    skipClick: false,
    inventorySha: 'inv',
    smokeWindow: meta.DEFAULT_SMOKE_WINDOW,
  });
  assert.strictEqual(a, b);
});

check('settingsFingerprint changes when repeats change', () => {
  const a = meta.settingsFingerprint({
    base: 'http://localhost:8898',
    phase: 'post-slug',
    repeats: 2,
    devices: ['desktop'],
    skipClick: false,
    inventorySha: 'inv',
    smokeWindow: meta.DEFAULT_SMOKE_WINDOW,
  });
  const b = meta.settingsFingerprint({
    base: 'http://localhost:8898',
    phase: 'post-slug',
    repeats: 3,
    devices: ['desktop'],
    skipClick: false,
    inventorySha: 'inv',
    smokeWindow: meta.DEFAULT_SMOKE_WINDOW,
  });
  assert.notStrictEqual(a, b);
});

check('isResumeCompatible rejects UNKNOWN and mismatched toolVersion', () => {
  const expected = {
    toolVersion: meta.TOOL_VERSION,
    effectiveCodeSha: 'code',
    settingsFingerprint: 'set',
    mode: 'click',
    device: 'desktop',
    cacheMode: 'cache-disabled',
    targetUrl: 'http://localhost:8898/',
    runIndex: 1,
  };
  assert.strictEqual(meta.isResumeCompatible({ validity: 'UNKNOWN', ttfbMs: 1 }, expected).ok, false);
  assert.strictEqual(
    meta.isResumeCompatible(
      {
        validity: 'VALID',
        ttfbMs: 1,
        toolVersion: 'old',
        effectiveCodeSha: 'code',
        settingsFingerprint: 'set',
        mode: 'click',
        device: 'desktop',
        cacheMode: 'cache-disabled',
        targetUrl: 'http://localhost:8898/',
        runIndex: 1,
      },
      expected,
    ).ok,
    false,
  );
  assert.strictEqual(
    meta.isResumeCompatible(
      {
        validity: 'VALID',
        ttfbMs: 1,
        measured: true,
        toolVersion: meta.TOOL_VERSION,
        effectiveCodeSha: 'code',
        settingsFingerprint: 'set',
        mode: 'click',
        device: 'desktop',
        cacheMode: 'cache-disabled',
        targetUrl: 'http://localhost:8898/',
        runIndex: 1,
      },
      expected,
    ).ok,
    true,
  );
});

check('parseRunLogWindows requires timestamps', () => {
  const legacy = meta.parseRunLogWindows(
    'click http://localhost:8898/ desktop cache-disabled #1\n{"ok":true}\n',
  );
  assert.strictEqual(legacy.windows.size, 0);
  const stamped = meta.parseRunLogWindows(
    [
      '2026-09-17T09:50:00.000Z click http://localhost:8898/ desktop cache-disabled #1',
      '{"ok":true}',
      '2026-09-17T09:50:20.000Z click http://localhost:8898/ desktop cache-disabled #2',
    ].join('\n'),
  );
  const key = 'click|desktop|cache-disabled|http://localhost:8898/|1';
  assert.ok(stamped.windows.has(key));
  assert.strictEqual(stamped.windows.get(key).start, '2026-09-17T09:50:00.000Z');
  assert.strictEqual(stamped.windows.get(key).end, '2026-09-17T09:50:20.000Z');
});

check('cellStatus COMPLETE only at planned repeats', () => {
  assert.strictEqual(meta.cellStatus({ validCount: 1, failedCount: 0, invalidCount: 0, unknownCount: 0, plannedRepeats: 2 }).status, 'PARTIAL');
  assert.strictEqual(meta.cellStatus({ validCount: 2, failedCount: 0, invalidCount: 0, unknownCount: 0, plannedRepeats: 2 }).status, 'COMPLETE');
  assert.strictEqual(meta.cellStatus({ validCount: 0, failedCount: 0, invalidCount: 0, unknownCount: 0, plannedRepeats: 2 }).status, 'NOT RUN');
});

check('overallMeasureStatus not hardcoded', () => {
  const partial = meta.overallMeasureStatus({
    plannedSampleCount: 10,
    completeCells: 1,
    plannedCells: 5,
    validSamples: 2,
  });
  assert.strictEqual(partial.status, 'PARTIAL');
  const complete = meta.overallMeasureStatus({
    plannedSampleCount: 10,
    completeCells: 5,
    plannedCells: 5,
    validSamples: 10,
  });
  assert.strictEqual(complete.status, 'COMPLETE');
  assert.ok(/not a speed judgment/i.test(complete.reason));
});

check('effectiveCodeFingerprint returns hex', () => {
  const root = path.resolve(__dirname, '../..');
  const fp = meta.effectiveCodeFingerprint(root);
  assert.ok(/^[a-f0-9]{64}$/.test(fp));
});

console.log(`\n${passed} checks passed`);
