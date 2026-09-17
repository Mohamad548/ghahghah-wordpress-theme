/**
 * Sitewide nav timing: real clicks (nav surfaces) + goto/soft-reload per unique destination.
 * Usage: node scripts/nav-sitewide-measure.cjs <phase> [inventoryDir] [outDir]
 *
 * Env:
 *   NAV_REPEATS=3
 *   NAV_SKIP_CLICK=1
 *   NAV_ONLY=comma paths filter
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'pre-slug';
const inventoryDir = path.resolve(
  process.argv[3] || 'docs/audits/artifacts/nav-perf/sitewide/inventory',
);
const outDir = path.resolve(
  process.argv[4] || `docs/audits/artifacts/nav-perf/sitewide/runs/${phase}`,
);
const BASE = (process.env.HOME_URL || 'http://localhost:8898').replace(/\/$/, '');
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const REPEATS = Math.max(1, parseInt(process.env.NAV_REPEATS || '3', 10));
const SKIP_CLICK = process.env.NAV_SKIP_CLICK === '1';
const ONLY = (process.env.NAV_ONLY || '')
  .split(',')
  .map((s) => s.trim())
  .filter(Boolean);

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

function allowUrl(url) {
  if (!ONLY.length) return true;
  return ONLY.some((p) => url.includes(p));
}

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
      timing: null,
    };
    byId.set(ev.requestId, row);
    docs.push(row);
  });
  client.on('Network.responseReceived', (ev) => {
    const row = byId.get(ev.requestId);
    if (!row) return;
    row.status = ev.response.status;
    row.fromCache = !!(ev.response.fromDiskCache || ev.response.fromPrefetchCache);
    row.timing = ev.response.timing || null;
  });
  client.on('Network.loadingFinished', (ev) => {
    const row = byId.get(ev.requestId);
    if (!row) return;
    row.encodedDataLength = ev.encodedDataLength;
  });
  return { browser, page, docs };
}

async function settleDestination(page) {
  await page.waitForFunction(() => !!document.body, { timeout: 60000 });
  await sleep(500);
  return page.evaluate(() => {
    const nav = performance.getEntriesByType('navigation')[0];
    const paints = performance.getEntriesByType('paint');
    const fcp = paints.find((p) => p.name === 'first-contentful-paint');
    let lcp = null;
    try {
      const entries = performance.getEntriesByType('largest-contentful-paint');
      if (entries.length) lcp = entries[entries.length - 1].startTime;
    } catch (_) {}
    return {
      url: location.href,
      title: document.title,
      h1: document.querySelector('h1')?.textContent?.trim() || null,
      bodyClass: document.body.className,
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
            transferSize: nav.transferSize,
            encodedBodySize: nav.encodedBodySize,
          }
        : null,
    };
  });
}

function packageRow(meta, docs, clickWall, paint) {
  const doc = [...docs].reverse().find((d) => d.status) || docs[docs.length - 1] || null;
  let clickToRequestMs = null;
  if (clickWall != null && doc?.wallTime != null) {
    clickToRequestMs = doc.wallTime * 1000 - clickWall;
  }
  const t = doc?.timing;
  const waitingMs =
    t && t.receiveHeadersStart >= 0 && t.sendEnd >= 0 ? t.receiveHeadersStart - t.sendEnd : null;
  return {
    ...meta,
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
    redirectCount: paint.navTiming?.redirectCount ?? 0,
    waitingTtfbMs: waitingMs ?? paint.navTiming?.ttfbMs ?? null,
    ttfbMs: paint.navTiming?.ttfbMs ?? null,
    htmlReceiveMs: paint.navTiming ? paint.navTiming.responseEnd - paint.navTiming.requestStart : null,
    fcpMs: paint.fcpMs,
    lcpMs: paint.lcpStatus === 'valid' ? paint.lcpMs : null,
    lcpStatus: paint.lcpStatus,
    navTiming: paint.navTiming,
  };
}

async function measureGoto(puppeteer, { cold, viewport, targetUrl, runIndex, warmPrep }) {
  const { browser, page, docs } = await launch(puppeteer, { cold, viewport });
  try {
    if (warmPrep) {
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
    return packageRow({ mode: 'reload', targetUrl, cold, runIndex, viewport }, docs, clickWall, paint);
  } finally {
    await browser.close();
  }
}

async function measureClick(puppeteer, { cold, viewport, targetUrl, runIndex, preferMobile }) {
  const { browser, page, docs } = await launch(puppeteer, { cold, viewport });
  try {
    await page.goto(BASE + '/', { waitUntil: 'domcontentloaded', timeout: 90000 });
    await sleep(400);
    if (preferMobile) {
      const toggle = await page.$('button.menu-toggle, .site-header__toggle, [data-menu-toggle], button[aria-controls]');
      if (toggle) {
        try {
          await toggle.click();
          await sleep(350);
        } catch (_) {}
      }
    }
    const handle = await page.evaluateHandle((want) => {
      const wantPath = new URL(want).pathname.replace(/\/$/, '') || '/';
      const visible = (el) => {
        const r = el.getBoundingClientRect();
        const cs = getComputedStyle(el);
        return r.width > 1 && r.height > 1 && cs.visibility !== 'hidden' && cs.display !== 'none' && cs.opacity !== '0';
      };
      const cands = [...document.querySelectorAll('a[href]')].filter((a) => {
        try {
          const p = new URL(a.href).pathname.replace(/\/$/, '') || '/';
          return p === wantPath;
        } catch {
          return false;
        }
      });
      const scored = cands.map((a) => {
        const cls = (a.className || '').toString();
        let score = 0;
        if (visible(a)) score += 10;
        if (cls.includes('gg-bottom-nav')) score += 5;
        if (cls.includes('site-nav__link')) score += 4;
        if (a.closest('header')) score += 3;
        if (a.closest('footer')) score += 2;
        if (a.closest('.gg-bottom-nav')) score += 6;
        return { a, score };
      });
      scored.sort((x, y) => y.score - x.score);
      return scored[0]?.a || null;
    }, targetUrl);
    const el = handle.asElement();
    if (!el) {
      return {
        mode: 'click',
        targetUrl,
        cold,
        runIndex,
        viewport,
        error: 'link-not-found-on-home',
        measured: false,
      };
    }
    await el.evaluate((node) => node.scrollIntoView({ block: 'center', inline: 'center' }));
    await sleep(150);
    let box = await el.boundingBox();
    if (!box) {
      // Last resort: JS click when layout hides the node from hit-testing.
      const clickHref = await page.evaluate((node) => node.href, el);
      docs.length = 0;
      const clickWall = Date.now();
      await Promise.all([
        page.waitForFunction(
          (want) => {
            try {
              const a = new URL(location.href).pathname.replace(/\/$/, '') || '/';
              const b = new URL(want).pathname.replace(/\/$/, '') || '/';
              return a === b;
            } catch {
              return false;
            }
          },
          { timeout: 60000 },
          targetUrl,
        ).catch(() => null),
        el.evaluate((node) => node.click()),
      ]);
      const paint = await settleDestination(page);
      return {
        ...packageRow(
          {
            mode: 'click',
            targetUrl,
            clickHref,
            cold,
            runIndex,
            viewport,
            measured: true,
            clickMethod: 'element.click',
          },
          docs,
          clickWall,
          paint,
        ),
      };
    }
    const clickHref = await page.evaluate((node) => node.href, el);
    docs.length = 0;
    const clickWall = Date.now();
    await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
    try {
      await page.waitForFunction(
        (want) => {
          try {
            const norm = (p) => decodeURIComponent(p.replace(/\/$/, '') || '/');
            const a = norm(new URL(location.href).pathname);
            const b = norm(new URL(want).pathname);
            return a === b;
          } catch {
            return false;
          }
        },
        { timeout: 90000 },
        targetUrl,
      );
    } catch (err) {
      const paint = await settleDestination(page).catch(() => ({
        url: page.url(),
        title: null,
        h1: null,
        bodyClass: '',
        fcpMs: null,
        lcpMs: null,
        lcpStatus: 'INVALID',
        navTiming: null,
      }));
      return {
        ...packageRow(
          {
            mode: 'click',
            targetUrl,
            clickHref,
            cold,
            runIndex,
            viewport,
            measured: false,
            error: 'nav-timeout:' + String(err.message || err),
          },
          docs,
          clickWall,
          paint,
        ),
        measured: false,
      };
    }
    const paint = await settleDestination(page);
    return {
      ...packageRow(
        { mode: 'click', targetUrl, clickHref, cold, runIndex, viewport, measured: true },
        docs,
        clickWall,
        paint,
      ),
    };
  } finally {
    await browser.close();
  }
}

(async () => {
  const invPath = path.join(inventoryDir, 'inventory.json');
  if (!fs.existsSync(invPath)) {
    console.error('Missing inventory:', invPath);
    process.exit(1);
  }
  const inv = JSON.parse(fs.readFileSync(invPath, 'utf8'));
  const destinations = (inv.uniqueDestinations || [])
    .map((d) => d.url)
    .filter((u) => allowUrl(u))
    // Skip WP sample noise unless ONLY set
    .filter((u) => ONLY.length || !/sample-page|hello-world/i.test(u));
  const navClicks = (inv.navClickHrefs || [])
    .filter((d) => {
      const t = (d.text || '').trim();
      if (/بازگشت به بالا/i.test(t)) return false;
      // Skip orphan legacy flavor pages as nav parents (still in uniqueDestinations).
      if (/طعم-|همه-محصولات|%d8%b7%d8%b9%d9%85|%d9%87%d9%85%d9%87-%d9%85%d8%ad/.test(d.url)) return false;
      return true;
    })
    .map((d) => d.url)
    .filter((u) => allowUrl(u));

  const puppeteer = ensurePuppeteer();
  const desktopVp = { width: 1440, height: 900, isMobile: false, hasTouch: false };
  const mobileVp = { width: 390, height: 844, isMobile: true, hasTouch: true };
  const rows = [];

  console.log('destinations', destinations.length, 'navClicks', navClicks.length, 'repeats', REPEATS);

  if (!SKIP_CLICK) {
    for (const targetUrl of navClicks) {
      for (const cold of [true, false]) {
        for (let i = 1; i <= REPEATS; i++) {
          const preferMobile = /products|wholesale|agency|contact|محصول|عمده|نمایند|تماس/i.test(targetUrl);
          const viewport = preferMobile && i === 1 && cold ? mobileVp : desktopVp;
          // Alternate: first cold of products-like on mobile bottom preference via viewport
          console.log(`click ${targetUrl} cold=${cold} #${i} vp=${viewport.width}`);
          let row;
          try {
            row = await measureClick(puppeteer, {
              cold,
              viewport,
              targetUrl,
              runIndex: i,
              preferMobile: viewport.isMobile,
            });
          } catch (err) {
            row = {
              mode: 'click',
              targetUrl,
              cold,
              runIndex: i,
              viewport,
              measured: false,
              error: String(err.message || err),
            };
          }
          rows.push(row);
          fs.writeFileSync(
            path.join(
              outDir,
              `click-${cold ? 'cold' : 'warm'}-${i}-${Buffer.from(targetUrl).toString('base64url').slice(0, 40)}.json`,
            ),
            JSON.stringify(row, null, 2),
          );
          console.log(
            JSON.stringify({
              ok: row.measured !== false,
              ttfb: row.ttfbMs,
              clickToReq: row.clickToDocumentRequestMs,
              fcp: row.fcpMs,
              url: row.finalUrl || row.error,
            }),
          );
        }
      }
    }
  }

  for (const targetUrl of destinations) {
    for (const cold of [true, false]) {
      for (let i = 1; i <= REPEATS; i++) {
        console.log(`goto ${targetUrl} cold=${cold} #${i}`);
        const row = await measureGoto(puppeteer, {
          cold,
          viewport: desktopVp,
          targetUrl,
          runIndex: i,
          warmPrep: !cold,
        });
        rows.push(row);
        console.log(JSON.stringify({ ttfb: row.ttfbMs, fcp: row.fcpMs, redir: row.redirectCount, url: row.finalUrl }));
      }
      for (let i = 1; i <= REPEATS; i++) {
        console.log(`reload ${targetUrl} cold=${cold} #${i}`);
        const row = await measureReload(puppeteer, {
          cold,
          viewport: desktopVp,
          targetUrl,
          runIndex: i,
        });
        rows.push(row);
        console.log(JSON.stringify({ ttfb: row.ttfbMs, fcp: row.fcpMs, url: row.finalUrl }));
      }
    }
  }

  const groups = {};
  for (const r of rows) {
    const key = [
      r.mode,
      r.cold ? 'cold' : 'warm',
      (r.targetUrl || '').replace(BASE, '') || r.error || '',
    ].join('|');
    (groups[key] ||= []).push(r);
  }
  const medians = {};
  for (const [k, list] of Object.entries(groups)) {
    const measured = list.filter((r) => r.measured !== false && r.ttfbMs != null);
    medians[k] = {
      n: list.length,
      measured: measured.length,
      clickToDocumentRequestMs: median(measured.map((r) => r.clickToDocumentRequestMs)),
      redirectMs: median(measured.map((r) => r.redirectMs)),
      redirectCount: median(measured.map((r) => r.redirectCount)),
      ttfbMs: median(measured.map((r) => r.ttfbMs)),
      htmlReceiveMs: median(measured.map((r) => r.htmlReceiveMs)),
      fcpMs: median(measured.map((r) => r.fcpMs)),
      lcpMs: median(measured.map((r) => (r.lcpStatus === 'valid' ? r.lcpMs : null))),
      finals: [...new Set(measured.map((r) => r.finalUrl))],
      errors: list.filter((r) => r.measured === false).map((r) => r.error),
    };
  }

  const summary = {
    phase,
    at: new Date().toISOString(),
    base: BASE,
    method: {
      auth: 'logged-out',
      throttling: 'none',
      repeats: REPEATS,
      cold: 'CDP Network.setCacheDisabled=true; new browser each run',
      warm: 'cache enabled; goto warmPrep; reload after prior load',
      serverCaches: 'not flushed',
      click: SKIP_CLICK ? 'skipped' : 'real mouse click on best-scoring home link for path',
    },
    inventoryCounts: inv.counts,
    destinationCount: destinations.length,
    navClickCount: navClicks.length,
    rows,
    medians,
  };
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log('MEDIANS', JSON.stringify(medians, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
