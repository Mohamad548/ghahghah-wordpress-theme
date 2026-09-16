/**
 * Home LCP diagnosis — PerformanceObserver before navigation; no scroll/click.
 * Usage: node scripts/home-lcp-diagnose.cjs [phase] [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'before';
const outDir = path.resolve(process.argv[3] || `docs/audits/artifacts/home-lcp/${phase}`);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const homeUrl = process.env.HOME_URL || 'http://localhost:8898/';

fs.mkdirSync(outDir, { recursive: true });

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

function runLighthouse(run, label) {
  const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-lh-'));
  const tmpJson = path.join(tmp, `home-mobile-${label}-run${run}.json`);
  const dest = path.join(outDir, `home-mobile-${label}-run${run}.json`);
  console.log(`[${phase}] LH ${label} run ${run}`);
  const res = spawnSync(
    'npx',
    [
      '--yes',
      'lighthouse@12.2.1',
      homeUrl,
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
    return { run, label, error: res.stderr || res.stdout || `exit ${res.status}`, valid: false };
  }
  fs.copyFileSync(tmpJson, dest);
  try {
    fs.rmSync(tmp, { recursive: true, force: true });
  } catch (_) {}
  const lhr = JSON.parse(fs.readFileSync(dest, 'utf8'));
  const a = lhr.audits || {};
  const lcpAudit = a['largest-contentful-paint'];
  const metrics = a.metrics?.details?.items?.[0] || {};
  const lcpNumeric = lcpAudit?.numericValue;
  const lcpError = lcpAudit?.errorMessage || lhr.runtimeError?.message || null;
  const noLcp =
    lcpNumeric == null ||
    Number.isNaN(lcpNumeric) ||
    !!lcpError ||
    /NO_LCP|largest.?contentful.?paint/i.test(String(lcpError || ''));
  return {
    run,
    label,
    valid: !noLcp && typeof lcpNumeric === 'number',
    noLcp,
    lighthouseVersion: lhr.lighthouseVersion,
    fetchTime: lhr.fetchTime,
    benchmarkIndex: lhr.environment?.benchmarkIndex ?? null,
    performance:
      typeof lhr.categories?.performance?.score === 'number'
        ? Math.round(lhr.categories.performance.score * 100)
        : null,
    lcp: lcpNumeric ?? null,
    lcpError,
    cls: a['cumulative-layout-shift']?.numericValue ?? null,
    tbt: a['total-blocking-time']?.numericValue ?? null,
    observedLcp: metrics.observedLargestContentfulPaint ?? null,
    observedCls: metrics.observedCumulativeLayoutShift ?? null,
    runtimeError: lhr.runtimeError || null,
    path: dest.replace(/\\/g, '/'),
  };
}

async function diagnoseBrowser(label, { disableHeroJs = false, reduceMotion = false } = {}) {
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
    const client = await page.createCDPSession();
    await client.send('Network.enable');
    await client.send('Network.setCacheDisabled', { cacheDisabled: true });

    if (reduceMotion) {
      await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    }

    if (disableHeroJs) {
      await page.setRequestInterception(true);
      page.on('request', (req) => {
        if (/\/hero\.js(\?|$)/i.test(req.url())) {
          req.abort();
          return;
        }
        req.continue();
      });
    }

    const imageNet = [];
    client.on('Network.requestWillBeSent', (ev) => {
      const u = ev.request.url;
      if (!/\/uploads\/|hero|flavour|banner|\.(webp|jpe?g|png)(\?|$)/i.test(u)) return;
      if (!/image|webp|jpeg|png|jpg/i.test(ev.type || '') && !/\.(webp|jpe?g|png)(\?|$)/i.test(u)) {
        // still capture likely hero assets
      }
      imageNet.push({
        kind: 'request',
        url: u,
        ts: ev.timestamp,
        initiator: ev.initiator?.type,
        type: ev.type,
      });
    });
    client.on('Network.responseReceived', (ev) => {
      const u = ev.response.url;
      if (!/\/uploads\/|flavour|banner|\.(webp|jpe?g|png)(\?|$)/i.test(u)) return;
      imageNet.push({
        kind: 'response',
        url: u,
        status: ev.response.status,
        mimeType: ev.response.mimeType,
        fromDiskCache: !!ev.response.fromDiskCache,
        ts: ev.timestamp,
      });
    });

    // Install LCP observer BEFORE navigation — no scroll/click later.
    await page.evaluateOnNewDocument(() => {
      window.__ghLcp = {
        entries: [],
        visibility: document.visibilityState,
        errors: [],
      };
      try {
        const po = new PerformanceObserver((list) => {
          for (const e of list.getEntries()) {
            let selector = null;
            let url = null;
            try {
              const el = e.element;
              if (el) {
                const id = el.id ? `#${el.id}` : '';
                const cls =
                  el.className && typeof el.className === 'string'
                    ? '.' + el.className.trim().split(/\s+/).slice(0, 4).join('.')
                    : '';
                selector = `${el.nodeName.toLowerCase()}${id}${cls}`;
                url = el.currentSrc || el.src || null;
              }
            } catch (_) {}
            window.__ghLcp.entries.push({
              size: e.size,
              startTime: e.startTime,
              renderTime: e.renderTime,
              loadTime: e.loadTime,
              url: e.url || url,
              id: e.id,
              selector,
            });
          }
        });
        po.observe({ type: 'largest-contentful-paint', buffered: true });
        window.__ghLcpObserver = po;
      } catch (err) {
        window.__ghLcp.errors.push(String(err));
      }
    });

    const t0 = Date.now();
    await page.goto(homeUrl, { waitUntil: 'networkidle2', timeout: 120000 });
    // Settle without interaction.
    await new Promise((r) => setTimeout(r, 3000));
    const navMs = Date.now() - t0;

    const probe = await page.evaluate(() => {
      const entries = window.__ghLcp?.entries || [];
      const last = entries.length ? entries[entries.length - 1] : null;
      const active = document.querySelector('.ghahghah-hero__slide.is-active');
      const img = active?.querySelector('img.ghahghah-hero__image');
      const slides = Array.from(document.querySelectorAll('.ghahghah-hero__slide'));
      const slideImgs = slides.map((s, i) => {
        const im = s.querySelector('img');
        const cs = getComputedStyle(s);
        return {
          index: i,
          isActive: s.classList.contains('is-active'),
          opacity: cs.opacity,
          visibility: cs.visibility,
          loading: im?.getAttribute('loading'),
          fetchpriority: im?.getAttribute('fetchpriority'),
          complete: im?.complete ?? null,
          naturalWidth: im?.naturalWidth ?? null,
          currentSrc: im?.currentSrc || im?.src || null,
          srcAttr: im?.getAttribute('src'),
        };
      });
      return {
        visibilityState: document.visibilityState,
        hidden: document.hidden,
        lcpEntryCount: entries.length,
        lcpLast: last,
        lcpAll: entries,
        errors: window.__ghLcp?.errors || [],
        activeHero: img
          ? {
              selector: 'img.ghahghah-hero__image',
              currentSrc: img.currentSrc || img.src,
              complete: img.complete,
              naturalWidth: img.naturalWidth,
              naturalHeight: img.naturalHeight,
              loading: img.getAttribute('loading'),
              fetchpriority: img.getAttribute('fetchpriority'),
              decoding: img.getAttribute('decoding'),
              widthAttr: img.getAttribute('width'),
              heightAttr: img.getAttribute('height'),
              rect: (() => {
                const r = img.getBoundingClientRect();
                return { x: r.x, y: r.y, w: r.width, h: r.height };
              })(),
            }
          : null,
        slideImgs,
        slideCount: slides.length,
      };
    });

    // Decode timing for active image (read-only).
    const decodeMs = await page.evaluate(async () => {
      const img = document.querySelector('.ghahghah-hero__slide.is-active img');
      if (!img || !img.decode) return null;
      const t = performance.now();
      try {
        await img.decode();
        return performance.now() - t;
      } catch (e) {
        return { error: String(e) };
      }
    });

    const shot = path.join(outDir, `diagnose-${label}.png`);
    await page.screenshot({ path: shot, fullPage: false });

    // Hero image responses only (uploads flavour/banner)
    const heroResponses = imageNet.filter(
      (x) => x.kind === 'response' && /uploads|flavour|banner/i.test(x.url),
    );

    return {
      label,
      disableHeroJs,
      reduceMotion,
      navMs,
      decodeMs,
      probe,
      heroImageResponses: heroResponses,
      heroImageResponseCount: heroResponses.length,
      imageNetSample: imageNet.slice(0, 80),
      screenshot: path.basename(shot),
      note: 'No scroll, click, or image-health during this probe',
    };
  } finally {
    await browser.close();
  }
}

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

(async () => {
  const diagnoses = [];
  diagnoses.push(await diagnoseBrowser('baseline'));
  // Single-variable isolation if needed — always collect; cheap relative to LH.
  diagnoses.push(await diagnoseBrowser('no-hero-js', { disableHeroJs: true }));
  diagnoses.push(await diagnoseBrowser('reduced-motion', { reduceMotion: true }));

  const lhRuns = [];
  for (let r = 1; r <= 3; r++) {
    lhRuns.push(runLighthouse(r, 'simulate'));
  }

  const valid = lhRuns.filter((x) => x.valid);
  const invalid = lhRuns.filter((x) => !x.valid);

  const summary = {
    phase,
    generatedAt: new Date().toISOString(),
    homeUrl,
    lighthouse: '12.2.1',
    formFactor: 'mobile',
    throttlingMethod: 'simulate',
    chrome,
    browserDiagnoses: diagnoses.map((d) => ({
      label: d.label,
      lcpEntryCount: d.probe.lcpEntryCount,
      lcpLast: d.probe.lcpLast,
      visibilityState: d.probe.visibilityState,
      heroImageResponseCount: d.heroImageResponseCount,
      activeComplete: d.probe.activeHero?.complete,
      activeNaturalWidth: d.probe.activeHero?.naturalWidth,
      slidesComplete: d.probe.slideImgs?.filter((s) => s.complete && s.naturalWidth > 0).length,
      slideCount: d.probe.slideCount,
    })),
    lighthouseRuns: lhRuns,
    validRunCount: valid.length,
    invalidRunCount: invalid.length,
    medians: {
      lcp_ms: median(valid.map((x) => x.lcp)),
      cls: median(valid.map((x) => x.cls)),
      tbt_ms: median(valid.map((x) => x.tbt)),
      performance: median(valid.map((x) => x.performance)),
    },
  };

  fs.writeFileSync(path.join(outDir, 'diagnose-full.json'), JSON.stringify({ summary, diagnoses }, null, 2));
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
