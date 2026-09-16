/**
 * Sequential mobile Lighthouse runner for perf-images before/after.
 * Usage: node run-lh.mjs <phase> <urls.json> <outDir>
 * phase: before|after
 */
import fs from 'fs';
import path from 'path';
import { spawnSync } from 'child_process';

const phase = process.argv[2];
const urlsPath = process.argv[3];
const outDir = process.argv[4];
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const urls = JSON.parse(fs.readFileSync(urlsPath, 'utf8'));
const pages = ['wholesale', 'agency', 'contact', 'article'];

fs.mkdirSync(outDir, { recursive: true });

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

function isValid(audit) {
  return !!(audit && audit.scoreDisplayMode !== 'error' && !audit.errorMessage && typeof audit.numericValue === 'number');
}

const summary = { phase, generatedAt: new Date().toISOString(), lighthouse: '12.2.1', formFactor: 'mobile', throttlingMethod: 'simulate', pages: {} };

for (const page of pages) {
  const url = urls[page];
  const runs = [];
  for (let r = 1; r <= 3; r++) {
    const out = path.join(outDir, `${page}-mobile-run${r}.json`);
    console.log(`[${phase}] ${page} run ${r}`);
    const res = spawnSync(
      'npx',
      [
        '--yes',
        'lighthouse@12.2.1',
        url,
        '--output=json',
        `--output-path=${out}`,
        `--chrome-path=${chrome}`,
        '--chrome-flags=--headless=new --no-sandbox --disable-gpu',
        '--only-categories=performance',
        '--form-factor=mobile',
        '--screenEmulation.mobile',
        '--throttling-method=simulate',
        '--quiet',
      ],
      { cwd: 'C:/Users/Mohamad/AppData/Local/Temp', encoding: 'utf8', shell: true },
    );
    if (res.status !== 0) {
      runs.push({ run: r, error: res.stderr || res.stdout || `exit ${res.status}` });
      continue;
    }
    const lhr = JSON.parse(fs.readFileSync(out, 'utf8'));
    const a = lhr.audits || {};
    const reqs = a['network-requests']?.details?.items || [];
    const transfer = reqs.reduce((s, i) => s + (i.transferSize || 0), 0);
    const shot = a['final-screenshot']?.details?.data;
    if (shot && r === 2) {
      fs.writeFileSync(
        path.join(outDir, `${page}-mobile-final.jpg`),
        Buffer.from(shot.replace(/^data:image\/jpeg;base64,/, ''), 'base64'),
      );
    }
    const targetImgs = reqs.filter((i) => i.resourceType === 'Image').map((i) => ({
      url: i.url,
      status: i.statusCode,
      transferSize: i.transferSize,
      resourceSize: i.resourceSize,
      mimeType: i.mimeType,
    }));
    runs.push({
      run: r,
      lighthouseVersion: lhr.lighthouseVersion,
      fetchTime: lhr.fetchTime,
      runtimeError: lhr.runtimeError || null,
      performance: typeof lhr.categories?.performance?.score === 'number' ? Math.round(lhr.categories.performance.score * 100) : null,
      lcp: isValid(a['largest-contentful-paint']) ? a['largest-contentful-paint'].numericValue : null,
      lcpError: a['largest-contentful-paint']?.errorMessage || null,
      cls: isValid(a['cumulative-layout-shift']) ? a['cumulative-layout-shift'].numericValue : null,
      tbt: isValid(a['total-blocking-time']) ? a['total-blocking-time'].numericValue : null,
      tbtError: a['total-blocking-time']?.errorMessage || null,
      requestCount: reqs.length,
      transferSize: transfer,
      images: targetImgs.filter((i) =>
        /pizza|parsley|corn-isolated|article-|bowl-of-real|corn-and-real|decorative-snack|packshot/i.test(i.url),
      ),
    });
  }

  const validLcp = runs.map((r) => r.lcp).filter((v) => typeof v === 'number');
  const validCls = runs.map((r) => r.cls).filter((v) => typeof v === 'number');
  const validTbt = runs.map((r) => r.tbt).filter((v) => typeof v === 'number');
  const validXfer = runs.map((r) => r.transferSize).filter((v) => typeof v === 'number');
  const validReq = runs.map((r) => r.requestCount).filter((v) => typeof v === 'number');
  const validPerf = runs.map((r) => r.performance).filter((v) => typeof v === 'number');

  summary.pages[page] = {
    url,
    runs,
    mediansValidOnly: {
      lcp_ms: median(validLcp),
      lcpValidN: validLcp.length,
      cls: median(validCls),
      clsValidN: validCls.length,
      tbt_ms: median(validTbt),
      tbtValidN: validTbt.length,
      transferSize: median(validXfer),
      requestCount: median(validReq),
      performance: median(validPerf),
    },
  };
}

fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
console.log(JSON.stringify(summary, null, 2));
