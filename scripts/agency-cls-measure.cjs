/**
 * Agency mobile Lighthouse ×3 + layout-shift attribution via PerformanceObserver.
 * Usage: node scripts/agency-cls-measure.cjs [phase] [outDir]
 * phase: before|after
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'before';
const outDir = path.resolve(process.argv[3] || `docs/audits/artifacts/agency-cls/${phase}`);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const agencyUrl =
  process.env.AGENCY_URL ||
  'http://localhost:8898/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d9%86%d9%85%d8%a7%db%8c%d9%86%d8%af%da%af%db%8c/';

fs.mkdirSync(outDir, { recursive: true });

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

function ensurePuppeteer() {
  try {
    return require('puppeteer-core');
  } catch {
    const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-pup-'));
    spawnSync('npm', ['init', '-y'], { cwd: tmp, shell: true, stdio: 'ignore' });
    spawnSync('npm', ['install', '--no-save', 'puppeteer-core@23.11.1'], {
      cwd: tmp,
      shell: true,
      stdio: 'ignore',
    });
    return require(path.join(tmp, 'node_modules', 'puppeteer-core'));
  }
}

function runLighthouse(run) {
  const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-lh-'));
  const tmpJson = path.join(tmp, `agency-mobile-run${run}.json`);
  const dest = path.join(outDir, `agency-mobile-run${run}.json`);
  console.log(`[${phase}] Lighthouse run ${run}`);
  const res = spawnSync(
    'npx',
    [
      '--yes',
      'lighthouse@12.2.1',
      agencyUrl,
      '--output=json',
      `--output-path=${tmpJson}`,
      `--chrome-path=${chrome}`,
      '--chrome-flags=--headless=new --no-sandbox --disable-gpu',
      '--only-categories=performance',
      '--form-factor=mobile',
      '--screenEmulation.mobile',
      '--throttling-method=simulate',
      '--quiet',
    ],
    { cwd: os.tmpdir(), encoding: 'utf8', shell: true, timeout: 420000, windowsHide: true },
  );
  if (res.status !== 0 || !fs.existsSync(tmpJson)) {
    return {
      run,
      error: res.stderr || res.stdout || `exit ${res.status}`,
    };
  }
  fs.copyFileSync(tmpJson, dest);
  try {
    fs.rmSync(tmp, { recursive: true, force: true });
  } catch (_) {}
  const lhr = JSON.parse(fs.readFileSync(dest, 'utf8'));
  const a = lhr.audits || {};
  const metrics = a.metrics?.details?.items?.[0] || {};
  return {
    run,
    lighthouseVersion: lhr.lighthouseVersion,
    fetchTime: lhr.fetchTime,
    benchmarkIndex: lhr.environment?.benchmarkIndex ?? null,
    performance:
      typeof lhr.categories?.performance?.score === 'number'
        ? Math.round(lhr.categories.performance.score * 100)
        : null,
    cls: a['cumulative-layout-shift']?.numericValue ?? null,
    lcp: a['largest-contentful-paint']?.numericValue ?? null,
    tbt: a['total-blocking-time']?.numericValue ?? null,
    observedCls: metrics.observedCumulativeLayoutShift ?? null,
    runtimeError: lhr.runtimeError || null,
  };
}

async function captureLayoutShifts() {
  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--window-size=390,844'],
  });
  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    await page.setUserAgent(
      'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
    );

    // Install observer BEFORE navigation.
    await page.evaluateOnNewDocument(() => {
      window.__ghCls = { entries: [], total: 0 };
      try {
        const po = new PerformanceObserver((list) => {
          for (const entry of list.getEntries()) {
            if (!entry || entry.entryType !== 'layout-shift') continue;
            if (entry.hadRecentInput) continue;
            const sources = (entry.sources || []).map((s) => {
              const node = s.node;
              let selector = null;
              try {
                if (node) {
                  const id = node.id ? `#${node.id}` : '';
                  const cls =
                    node.className && typeof node.className === 'string'
                      ? '.' + node.className.trim().split(/\s+/).slice(0, 3).join('.')
                      : '';
                  selector = `${node.nodeName.toLowerCase()}${id}${cls}`;
                }
              } catch (_) {}
              return {
                selector,
                previousRect: s.previousRect
                  ? {
                      x: s.previousRect.x,
                      y: s.previousRect.y,
                      width: s.previousRect.width,
                      height: s.previousRect.height,
                    }
                  : null,
                currentRect: s.currentRect
                  ? {
                      x: s.currentRect.x,
                      y: s.currentRect.y,
                      width: s.currentRect.width,
                      height: s.currentRect.height,
                    }
                  : null,
              };
            });
            window.__ghCls.entries.push({
              value: entry.value,
              startTime: entry.startTime,
              hadRecentInput: entry.hadRecentInput,
              sources,
            });
            window.__ghCls.total += entry.value;
          }
        });
        po.observe({ type: 'layout-shift', buffered: true });
        window.__ghClsObserver = po;
      } catch (e) {
        window.__ghCls.error = String(e);
      }
    });

    await page.goto(agencyUrl, { waitUntil: 'networkidle2', timeout: 90000 });
    await page.evaluate(async () => {
      const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
      const h = Math.max(document.body.scrollHeight, 2000);
      for (let y = 0; y < h; y += 400) {
        window.scrollTo(0, y);
        await sleep(150);
      }
      window.scrollTo(0, 0);
      await sleep(1000);
    });

    const data = await page.evaluate(() => window.__ghCls || null);
    const shot = path.join(outDir, 'agency-mobile-cls-probe.png');
    await page.screenshot({ path: shot, fullPage: false });
    fs.writeFileSync(path.join(outDir, 'layout-shifts.json'), JSON.stringify(data, null, 2));
    console.log('CLS probe total (no input):', data?.total);
    console.log('Shift entries:', data?.entries?.length);
    return data;
  } finally {
    await browser.close();
  }
}

(async () => {
  const shifts = await captureLayoutShifts();
  const runs = [];
  for (let r = 1; r <= 3; r++) {
    runs.push(runLighthouse(r));
  }
  const summary = {
    phase,
    generatedAt: new Date().toISOString(),
    url: agencyUrl,
    lighthouse: '12.2.1',
    formFactor: 'mobile',
    throttlingMethod: 'simulate',
    chrome,
    runs,
    medians: {
      cls: median(runs.map((x) => x.cls)),
      lcp_ms: median(runs.map((x) => x.lcp)),
      tbt_ms: median(runs.map((x) => x.tbt)),
      performance: median(runs.map((x) => x.performance)),
    },
    layoutShiftProbe: {
      total: shifts?.total ?? null,
      entryCount: shifts?.entries?.length ?? 0,
      top: (shifts?.entries || []).slice().sort((a, b) => b.value - a.value).slice(0, 8),
    },
  };
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary.medians, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
