/**
 * Single Wholesale Lighthouse run for TBT investigation.
 * Writes LHR to TEMP (no spaces) then copies into OUT_DIR.
 * Env: PHASE, PAIR, OUT_DIR, URL, SAVE_TRACE=0|1, CHROME_PATH
 */
import fs from 'fs';
import path from 'path';
import os from 'os';
import { spawnSync } from 'child_process';

const phase = process.env.PHASE;
const pair = process.env.PAIR || '1';
const outDir = process.env.OUT_DIR;
const url = process.env.URL;
const saveTrace = process.env.SAVE_TRACE === '1';
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';

if (!phase || !outDir || !url) {
  console.error('PHASE, OUT_DIR, URL required');
  process.exit(2);
}

fs.mkdirSync(outDir, { recursive: true });
const tag = `${phase}-pair${pair}`;
const outJson = path.resolve(outDir, `${tag}.json`);
const assetsDir = path.resolve(outDir, `${tag}-assets`);
const extractPath = path.resolve(outDir, `${tag}.extract.json`);
const errorPath = path.resolve(outDir, `${tag}.error.txt`);

const tmpRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-tbt-'));
const tmpJson = path.join(tmpRoot, `${tag}.json`);
const tmpAssets = path.join(tmpRoot, `${tag}-assets`);

const args = [
  '--yes',
  'lighthouse@12.2.1',
  url,
  '--output=json',
  `--chrome-path=${chrome}`,
  '--chrome-flags=--headless=new --no-sandbox --disable-gpu',
  '--only-categories=performance',
  '--form-factor=mobile',
  '--screenEmulation.mobile',
  '--throttling-method=simulate',
  '--quiet',
];

if (saveTrace) {
  fs.mkdirSync(tmpAssets, { recursive: true });
  args.push('-GA', `--output-path=${path.join(tmpAssets, 'report')}`);
} else {
  args.push(`--output-path=${tmpJson}`);
}

console.log(`[${tag}] lighthouse start saveTrace=${saveTrace} tmp=${tmpRoot}`);
const res = spawnSync('npx', args, {
  cwd: os.tmpdir(),
  encoding: 'utf8',
  shell: true,
  timeout: 420000,
  windowsHide: true,
});

if (res.status !== 0) {
  fs.writeFileSync(
    errorPath,
    `${res.stderr || ''}\n${res.stdout || ''}\nerror=${res.error || ''}\nexit=${res.status}`,
  );
  console.error(`[${tag}] FAIL exit=${res.status}`);
  console.error(res.stderr || res.stdout || String(res.error || ''));
  process.exit(res.status || 1);
}

function copyRecursive(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const ent of fs.readdirSync(src, { withFileTypes: true })) {
    const s = path.join(src, ent.name);
    const d = path.join(dest, ent.name);
    if (ent.isDirectory()) copyRecursive(s, d);
    else fs.copyFileSync(s, d);
  }
}

if (saveTrace) {
  copyRecursive(tmpAssets, assetsDir);
  const walkFind = (d) => {
    for (const ent of fs.readdirSync(d, { withFileTypes: true })) {
      const p = path.join(d, ent.name);
      if (ent.isDirectory()) {
        const f = walkFind(p);
        if (f) return f;
      } else if (ent.name.endsWith('.json') && /report/i.test(ent.name) && !/trace/i.test(ent.name)) {
        return p;
      }
    }
    return null;
  };
  const found = walkFind(assetsDir);
  if (found) fs.copyFileSync(found, outJson);
} else if (fs.existsSync(tmpJson)) {
  fs.copyFileSync(tmpJson, outJson);
}

try {
  fs.rmSync(tmpRoot, { recursive: true, force: true });
} catch {
  /* ignore */
}

if (!fs.existsSync(outJson)) {
  fs.writeFileSync(errorPath, `missing LHR after success\nstdout=${res.stdout}\nstderr=${res.stderr}`);
  console.error(`[${tag}] missing LHR at ${outJson}`);
  process.exit(1);
}

const lhr = JSON.parse(fs.readFileSync(outJson, 'utf8'));
const a = lhr.audits || {};
const metrics = a.metrics?.details?.items?.[0] || {};
const longTasks = a['long-tasks']?.details?.items || [];
const mainThread = a['mainthread-work-breakdown']?.details?.items || [];
const bootup = a['bootup-time']?.details?.items || [];

const extract = {
  tag,
  phase,
  pair: Number(pair),
  lighthouseVersion: lhr.lighthouseVersion,
  fetchTime: lhr.fetchTime,
  requestedUrl: lhr.requestedUrl,
  finalUrl: lhr.finalUrl,
  runtimeError: lhr.runtimeError || null,
  benchmarkIndex: lhr.environment?.benchmarkIndex ?? null,
  hostUserAgent: lhr.hostUserAgent || null,
  performance:
    typeof lhr.categories?.performance?.score === 'number'
      ? Math.round(lhr.categories.performance.score * 100)
      : null,
  simulated: {
    tbt_ms: a['total-blocking-time']?.numericValue ?? null,
    tbtError: a['total-blocking-time']?.errorMessage || null,
    lcp_ms: a['largest-contentful-paint']?.numericValue ?? null,
    fcp_ms: a['first-contentful-paint']?.numericValue ?? null,
    si_ms: a['speed-index']?.numericValue ?? null,
    tti_ms: a.interactive?.numericValue ?? null,
  },
  observed: {
    tbt_ms: metrics.observedTotalBlockingTime ?? null,
    lcp_ms: metrics.observedLargestContentfulPaint ?? null,
    fcp_ms: metrics.observedFirstContentfulPaint ?? null,
    load_ms: metrics.observedLoad ?? null,
    traceEnd_ms: metrics.observedTraceEnd ?? null,
  },
  longTasksTop: longTasks.slice(0, 10).map((t) => ({
    url: t.url,
    startTime: t.startTime,
    duration: t.duration,
  })),
  mainThreadTop: mainThread.slice(0, 8),
  bootupTop: bootup.slice(0, 8).map((b) => ({
    url: b.url,
    total: b.total,
    scripting: b.scripting,
    scriptParseCompile: b.scriptParseCompile,
  })),
  saveTrace,
};

fs.writeFileSync(extractPath, JSON.stringify(extract, null, 2));
console.log(JSON.stringify(extract, null, 2));
