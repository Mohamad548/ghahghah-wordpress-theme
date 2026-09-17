/**
 * Focused Home→Products navigation timing (click / goto / soft-reload).
 * Uses CDP Document events + URL/h1 readiness (avoids flaky networkidle/load waits).
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'before';
const outDir = path.resolve(process.argv[3] || `docs/audits/artifacts/nav-perf/runs/${phase}`);
const BASE = process.env.HOME_URL || 'http://localhost:8898';
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
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

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function launch(puppeteer, { cold, viewport }) {
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  const page = await browser.newPage();
  await page.setViewport(viewport);
  const client = await page.createCDPSession();
  await client.send('Network.enable');
  await client.send('Network.setCacheDisabled', { cacheDisabled: !!cold });
  const docs = [];
  const byId = new Map();
  client.on('Network.requestWillBeSent', (ev) => {
    if (ev.type !== 'Document') return;
    const row = {
      requestId: ev.requestId,
      url: ev.request.url,
      wallTime: ev.wallTime,
      timestamp: ev.timestamp,
      redirectResponse: ev.redirectResponse
        ? { url: ev.redirectResponse.url, status: ev.redirectResponse.status }
        : null,
      status: null,
      fromCache: null,
      encodedDataLength: null,
      responseReceivedWall: null,
      finishedWall: null,
    };
    byId.set(ev.requestId, row);
    docs.push(row);
  });
  client.on('Network.responseReceived', (ev) => {
    const row = byId.get(ev.requestId);
    if (!row) return;
    row.status = ev.response.status;
    row.fromCache = !!(ev.response.fromDiskCache || ev.response.fromPrefetchCache);
    row.responseReceivedWall = Date.now();
    row.timing = ev.response.timing || null;
  });
  client.on('Network.loadingFinished', (ev) => {
    const row = byId.get(ev.requestId);
    if (!row) return;
    row.encodedDataLength = ev.encodedDataLength;
    row.finishedWall = Date.now();
  });
  return { browser, page, client, docs, byId };
}

async function settleDestination(page) {
  await page.waitForFunction(() => !!document.querySelector('h1'), { timeout: 60000 });
  await sleep(600);
  return page.evaluate(() => {
    const nav = performance.getEntriesByType('navigation')[0];
    const paints = performance.getEntriesByType('paint');
    const fcp = paints.find((p) => p.name === 'first-contentful-paint');
    let lcp = null;
    try {
      const entries = performance.getEntriesByType('largest-contentful-paint');
      if (entries.length) lcp = entries[entries.length - 1].startTime;
    } catch (_) {}
    const resources = performance.getEntriesByType('resource');
    return {
      url: location.href,
      title: document.title,
      h1: document.querySelector('h1')?.textContent?.trim() || null,
      bodyClass: document.body.className,
      outstandingEstimate: resources.filter((r) => r.responseEnd === 0).length,
      resourceCount: resources.length,
      fcpMs: fcp ? fcp.startTime : null,
      lcpMs: lcp,
      lcpStatus: typeof lcp === 'number' && lcp > 0 ? 'valid' : 'INVALID',
      navTiming: nav
        ? {
            redirectCount: nav.redirectCount,
            redirectMs: nav.redirectEnd > 0 ? nav.redirectEnd - nav.redirectStart : 0,
            ttfbMs: nav.responseStart - nav.requestStart,
            requestStart: nav.requestStart,
            responseStart: nav.responseStart,
            responseEnd: nav.responseEnd,
            domContentLoadedEventEnd: nav.domContentLoadedEventEnd,
            loadEventEnd: nav.loadEventEnd,
            transferSize: nav.transferSize,
            encodedBodySize: nav.encodedBodySize,
            startTime: nav.startTime,
          }
        : null,
    };
  });
}

function pickDestDoc(docs, hint) {
  const matched = [...docs].reverse().find((d) => (hint ? d.url.includes(hint) : true));
  return matched || docs[docs.length - 1] || null;
}

function packageRow(meta, docs, clickWall, paint, hint) {
  const doc = pickDestDoc(docs, hint);
  let clickToRequestMs = null;
  if (clickWall != null && doc?.wallTime != null) {
    clickToRequestMs = doc.wallTime * 1000 - clickWall;
  }
  const t = doc?.timing;
  // Chrome Network timing is relative to request start in ms (or -1)
  const queueingMs =
    t && t.dnsStart >= 0 ? Math.max(0, t.dnsStart) : t && t.connectStart >= 0 ? Math.max(0, t.connectStart) : null;
  const waitingMs = t && t.receiveHeadersStart >= 0 && t.sendEnd >= 0 ? t.receiveHeadersStart - t.sendEnd : null;
  return {
    ...meta,
    clickHref: meta.clickHref || null,
    finalUrl: paint.url,
    title: paint.title,
    h1: paint.h1,
    bodyClass: paint.bodyClass,
    documentChain: docs.map((d) => ({
      url: d.url,
      status: d.status,
      redirectFrom: d.redirectResponse,
      fromCache: d.fromCache,
      encodedDataLength: d.encodedDataLength,
    })),
    clickToDocumentRequestMs: clickToRequestMs,
    redirectMs: paint.navTiming?.redirectMs ?? 0,
    queueingMs,
    waitingTtfbMs: waitingMs ?? paint.navTiming?.ttfbMs ?? null,
    ttfbMs: paint.navTiming?.ttfbMs ?? null,
    htmlReceiveMs: paint.navTiming ? paint.navTiming.responseEnd - paint.navTiming.requestStart : null,
    fcpMs: paint.fcpMs,
    lcpMs: paint.lcpStatus === 'valid' ? paint.lcpMs : null,
    lcpStatus: paint.lcpStatus,
    navTiming: paint.navTiming,
    resourceCount: paint.resourceCount,
  };
}

async function measureClick(puppeteer, { cold, viewport, surface, runIndex }) {
  const { browser, page, docs } = await launch(puppeteer, { cold, viewport });
  try {
    await page.goto(BASE + '/', { waitUntil: 'domcontentloaded', timeout: 90000 });
    await sleep(400);
    docs.length = 0;

    let selector;
    let hint;
    if (surface === 'desktop-header') {
      selector = '#primary-menu-desktop > .menu-item-object-ghahghah_product > .site-nav__row > a.site-nav__link';
      hint = '/products';
    } else {
      selector = 'nav.gg-bottom-nav a.gg-bottom-nav__link';
      // resolve exact products bottom link
    }

    const clickHref = await page.$eval(
      surface === 'desktop-header'
        ? selector
        : 'nav.gg-bottom-nav a.gg-bottom-nav__link',
      (root, surfaceName) => {
        if (surfaceName === 'desktop-header') return root.href;
        const links = [...document.querySelectorAll('nav.gg-bottom-nav a.gg-bottom-nav__link')];
        const hit = links.find((a) => (a.textContent || '').replace(/\s+/g, ' ').trim() === 'محصولات');
        if (!hit) throw new Error('bottom products link missing');
        return hit.href;
      },
      surface,
    );

    if (surface === 'mobile-bottom') {
      hint = clickHref.includes('products') ? '/products' : '%d9%85%d8%ad%d8%b5%d9%88%d9%84%d8%a7%d8%aa';
      selector = await page.evaluate(() => {
        const links = [...document.querySelectorAll('nav.gg-bottom-nav a.gg-bottom-nav__link')];
        const hit = links.find((a) => (a.textContent || '').replace(/\s+/g, ' ').trim() === 'محصولات');
        hit.setAttribute('data-nav-perf-target', '1');
        return 'a[data-nav-perf-target="1"]';
      });
    }

    const box = await page.$eval(selector, (a) => {
      const r = a.getBoundingClientRect();
      return { x: r.x + r.width / 2, y: r.y + r.height / 2 };
    });

    const clickWall = Date.now();
    await page.mouse.click(box.x, box.y);
    await page.waitForFunction(
      (frag) => location.href.includes(frag) || location.href.includes(decodeURIComponent(frag)),
      { timeout: 90000 },
      hint,
    );
    const paint = await settleDestination(page);
    return packageRow(
      { mode: 'click', surface, cold, runIndex, viewport, clickHref },
      docs,
      clickWall,
      paint,
      hint,
    );
  } finally {
    await browser.close();
  }
}

async function measureGoto(puppeteer, { cold, viewport, targetUrl, runIndex, warmPrep }) {
  const { browser, page, docs } = await launch(puppeteer, { cold, viewport });
  try {
    if (warmPrep && !cold) {
      await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await settleDestination(page);
      docs.length = 0;
      await sleep(200);
    }
    const clickWall = Date.now();
    await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 90000 });
    const paint = await settleDestination(page);
    return packageRow(
      { mode: 'goto', targetUrl, cold, runIndex, viewport, warmPrep: !!warmPrep },
      docs,
      clickWall,
      paint,
      targetUrl.includes('products') ? '/products' : '%d9%85',
    );
  } finally {
    await browser.close();
  }
}

async function measureReload(puppeteer, { cold, viewport, targetUrl, runIndex }) {
  const { browser, page, docs } = await launch(puppeteer, { cold, viewport });
  try {
    await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 90000 });
    await settleDestination(page);
    docs.length = 0;
    await sleep(200);
    const clickWall = Date.now();
    await page.reload({ waitUntil: 'domcontentloaded', timeout: 90000 });
    const paint = await settleDestination(page);
    return packageRow(
      { mode: 'reload', targetUrl, cold, runIndex, viewport },
      docs,
      clickWall,
      paint,
      targetUrl.includes('products') ? '/products' : '%d9%85',
    );
  } finally {
    await browser.close();
  }
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const desktopVp = { width: 1440, height: 900, isMobile: false, hasTouch: false };
  const mobileVp = { width: 390, height: 844, isMobile: true, hasTouch: true };
  const rows = [];
  const REPEATS = 3;

  for (const cold of [true, false]) {
    for (let i = 1; i <= REPEATS; i++) {
      console.log(`desktop-click cold=${cold} #${i}`);
      const row = await measureClick(puppeteer, {
        cold,
        viewport: desktopVp,
        surface: 'desktop-header',
        runIndex: i,
      });
      rows.push(row);
      fs.writeFileSync(
        path.join(outDir, `desktop-click-${cold ? 'cold' : 'warm'}-${i}.json`),
        JSON.stringify(row, null, 2),
      );
      console.log(JSON.stringify({ ttfb: row.ttfbMs, clickToReq: row.clickToDocumentRequestMs, fcp: row.fcpMs, url: row.finalUrl }));
    }
  }

  for (const cold of [true, false]) {
    for (let i = 1; i <= REPEATS; i++) {
      console.log(`mobile-click cold=${cold} #${i}`);
      const row = await measureClick(puppeteer, {
        cold,
        viewport: mobileVp,
        surface: 'mobile-bottom',
        runIndex: i,
      });
      rows.push(row);
      fs.writeFileSync(
        path.join(outDir, `mobile-click-${cold ? 'cold' : 'warm'}-${i}.json`),
        JSON.stringify(row, null, 2),
      );
      console.log(JSON.stringify({ ttfb: row.ttfbMs, clickToReq: row.clickToDocumentRequestMs, fcp: row.fcpMs, url: row.finalUrl }));
    }
  }

  const targets = [...new Set(rows.map((r) => r.clickHref).filter(Boolean))];
  for (const targetUrl of targets) {
    for (const cold of [true, false]) {
      for (let i = 1; i <= REPEATS; i++) {
        console.log(`goto ${targetUrl} cold=${cold} #${i}`);
        rows.push(
          await measureGoto(puppeteer, {
            cold,
            viewport: desktopVp,
            targetUrl,
            runIndex: i,
            warmPrep: !cold,
          }),
        );
      }
      for (let i = 1; i <= REPEATS; i++) {
        console.log(`reload ${targetUrl} cold=${cold} #${i}`);
        rows.push(
          await measureReload(puppeteer, {
            cold,
            viewport: desktopVp,
            targetUrl,
            runIndex: i,
          }),
        );
      }
    }
  }

  const groups = {};
  for (const r of rows) {
    const key = [r.mode, r.surface || '', r.cold ? 'cold' : 'warm', (r.clickHref || r.targetUrl || '').replace(BASE, '')].join('|');
    (groups[key] ||= []).push(r);
  }
  const medians = {};
  for (const [k, list] of Object.entries(groups)) {
    medians[k] = {
      n: list.length,
      clickToDocumentRequestMs: median(list.map((r) => r.clickToDocumentRequestMs)),
      redirectMs: median(list.map((r) => r.redirectMs)),
      ttfbMs: median(list.map((r) => r.ttfbMs)),
      htmlReceiveMs: median(list.map((r) => r.htmlReceiveMs)),
      fcpMs: median(list.map((r) => r.fcpMs)),
      lcpMs: median(list.map((r) => (r.lcpStatus === 'valid' ? r.lcpMs : null))),
      finals: [...new Set(list.map((r) => r.finalUrl))],
    };
  }

  const summary = {
    phase,
    at: new Date().toISOString(),
    base: BASE,
    method: {
      auth: 'logged-out',
      throttling: 'none',
      cold: 'CDP Network.setCacheDisabled=true; new browser each run',
      warm: 'cache enabled; goto warmPrep before measured goto; reload after prior load',
      serverCaches: 'not flushed',
      click: 'real mouse click on link bounding-box center',
    },
    rows,
    medians,
  };
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log('MEDIANS', JSON.stringify(medians, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
