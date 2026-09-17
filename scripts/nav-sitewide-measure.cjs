/**
 * Sitewide nav timing: real clicks + goto/reload per unique destination.
 * Usage: node scripts/nav-sitewide-measure.cjs <phase> [inventoryDir] [outDir]
 *
 * Env:
 *   NAV_REPEATS=3
 *   NAV_SKIP_CLICK=1
 *   NAV_ONLY=comma paths filter
 *   NAV_DEVICES=desktop,mobile  (default: desktop for goto/reload; click uses both)
 *   NAV_RESUME=1               (default on) skip existing valid sample files
 *   NAV_FORCE=1                refuse to overwrite unless set with explicit delete
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const crypto = require('crypto');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'post-slug';
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
const RESUME = process.env.NAV_RESUME !== '0';
const ONLY = (process.env.NAV_ONLY || '')
  .split(',')
  .map((s) => s.trim())
  .filter(Boolean);
const DEVICE_LIST = (process.env.NAV_DEVICES || 'desktop,mobile')
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

function urlHash(url) {
  return crypto.createHash('sha256').update(String(url)).digest('hex');
}

function deviceKey(viewport) {
  if (!viewport) return 'unknown';
  if (viewport.isMobile || viewport.width <= 500) return 'mobile';
  return 'desktop';
}

function cacheModeLabel(cold, prepared) {
  if (cold) return 'cache-disabled';
  if (prepared) return 'cache-enabled-prepared';
  return 'cache-enabled-unprepared';
}

function sampleFileName({ mode, device, cacheMode, runIndex, targetUrl }) {
  const hash = urlHash(targetUrl);
  // Full URL hash + phase/device/mode/cache/run — no truncated Base64 collisions.
  return `${phase}__${mode}__${device}__${cacheMode}__r${runIndex}__${hash}.json`;
}

function sampleKeyFromMeta(meta) {
  return sampleFileName(meta);
}

function normalizeCompareUrl(href) {
  try {
    const u = new URL(href, BASE);
    const pathNorm = (u.pathname.replace(/\/$/, '') || '/') + '/';
    const keys = [...u.searchParams.keys()].sort();
    const q = new URLSearchParams();
    for (const k of keys) {
      for (const v of u.searchParams.getAll(k)) q.append(k, v);
    }
    const search = q.toString();
    return u.origin + pathNorm + (search ? `?${search}` : '');
  } catch {
    return String(href);
  }
}

function urlsMatch(a, b) {
  return normalizeCompareUrl(a) === normalizeCompareUrl(b);
}

function writeSampleAtomic(filePath, row) {
  if (fs.existsSync(filePath)) {
    let existing;
    try {
      existing = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch {
      throw new Error(`Refusing overwrite of unreadable sample: ${filePath}`);
    }
    const same =
      existing.sampleId === row.sampleId &&
      existing.targetUrl === row.targetUrl &&
      existing.mode === row.mode &&
      existing.device === row.device &&
      existing.cacheMode === row.cacheMode &&
      existing.runIndex === row.runIndex;
    if (same && RESUME) {
      return { wrote: false, resumed: true, row: existing };
    }
    throw new Error(
      `Sample collision / unwanted overwrite blocked: ${path.basename(filePath)} ` +
        `(existing target=${existing.targetUrl} new=${row.targetUrl})`,
    );
  }
  const tmp = filePath + '.tmp';
  fs.writeFileSync(tmp, JSON.stringify(row, null, 2));
  fs.renameSync(tmp, filePath);
  return { wrote: true, resumed: false, row };
}

function loadExistingSamples() {
  const map = new Map();
  if (!fs.existsSync(outDir)) return map;
  for (const name of fs.readdirSync(outDir)) {
    if (!name.endsWith('.json')) continue;
    if (
      name === 'summary.json' ||
      name === 'summary-partial.json' ||
      name === 'manifest.json' ||
      name === 'sample-validity-index.json' ||
      name === 'scenario-plan.json'
    ) {
      continue;
    }
    if (!name.startsWith(`${phase}__`)) continue;
    const full = path.join(outDir, name);
    try {
      const row = JSON.parse(fs.readFileSync(full, 'utf8'));
      // Resume only VALID samples; INVALID/FAILED must be re-run.
      if (row && row.validity && row.validity !== 'VALID') continue;
      if (row && row.measured === false) continue;
      if (row && row.ttfbMs == null) continue;
      if (row && row.sampleId) map.set(row.sampleId, { file: full, row });
      else map.set(name.replace(/\.json$/, ''), { file: full, row });
    } catch {
      // leave damaged files in place; do not delete
    }
  }
  return map;
}

function isResumableValidFile(filePath) {
  if (!fs.existsSync(filePath)) return false;
  try {
    const row = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    if (row.validity && row.validity !== 'VALID') return false;
    if (row.measured === false) return false;
    if (row.ttfbMs == null) return false;
    return true;
  } catch {
    return false;
  }
}

async function launch(puppeteer, { cacheDisabled, viewport }) {
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  const page = await browser.newPage();
  await page.setViewport(viewport);
  const client = await page.createCDPSession();
  await client.send('Network.enable');
  await client.send('Network.setCacheDisabled', { cacheDisabled: !!cacheDisabled });
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
    finalUrlMatch: paint.url ? urlsMatch(paint.url, meta.targetUrl) : false,
  };
}

const VIEWPORTS = {
  desktop: { width: 1440, height: 900, isMobile: false, hasTouch: false },
  mobile: { width: 390, height: 844, isMobile: true, hasTouch: true },
};

async function measureGoto(puppeteer, { cold, device, targetUrl, runIndex }) {
  const viewport = VIEWPORTS[device];
  const prepared = !cold;
  const cacheMode = cacheModeLabel(cold, prepared);
  const { browser, page, docs } = await launch(puppeteer, { cacheDisabled: cold, viewport });
  try {
    let warmPrepOk = false;
    if (prepared) {
      await page.goto(targetUrl, { waitUntil: 'networkidle2', timeout: 120000 });
      await settleDestination(page);
      // Confirm destination document + subresources actually loaded under cache-enabled.
      warmPrepOk = true;
      docs.length = 0;
      await sleep(200);
    }
    const clickWall = Date.now();
    await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 90000 });
    const paint = await settleDestination(page);
    return packageRow(
      {
        mode: 'goto',
        targetUrl,
        cold,
        cacheMode: prepared ? (warmPrepOk ? 'cache-enabled-prepared' : 'cache-enabled-unprepared') : 'cache-disabled',
        device,
        runIndex,
        viewport,
        warmPrep: prepared,
        warmPrepOk,
        measured: true,
      },
      docs,
      clickWall,
      paint,
    );
  } finally {
    await browser.close();
  }
}

async function measureReload(puppeteer, { cold, device, targetUrl, runIndex }) {
  const viewport = VIEWPORTS[device];
  const prepared = !cold;
  const { browser, page, docs } = await launch(puppeteer, { cacheDisabled: cold, viewport });
  try {
    await page.goto(targetUrl, { waitUntil: prepared ? 'networkidle2' : 'domcontentloaded', timeout: 120000 });
    await settleDestination(page);
    docs.length = 0;
    await sleep(200);
    const clickWall = Date.now();
    await page.reload({ waitUntil: 'domcontentloaded', timeout: 90000 });
    const paint = await settleDestination(page);
    return packageRow(
      {
        mode: 'reload',
        targetUrl,
        cold,
        cacheMode: prepared ? 'cache-enabled-prepared' : 'cache-disabled',
        device,
        runIndex,
        viewport,
        warmPrep: prepared,
        warmPrepOk: prepared,
        measured: true,
      },
      docs,
      clickWall,
      paint,
    );
  } finally {
    await browser.close();
  }
}

async function findInteractiveLink(page, targetUrl, preferMobile) {
  if (preferMobile) {
    const toggle = await page.$(
      'button.menu-toggle, .site-header__toggle, [data-menu-toggle], button[aria-controls]',
    );
    if (toggle) {
      try {
        await toggle.click();
        await sleep(350);
      } catch (_) {}
    }
  }
  return page.evaluateHandle((want) => {
    const wantNorm = (() => {
      try {
        const u = new URL(want);
        const pathNorm = (u.pathname.replace(/\/$/, '') || '/') + '/';
        const keys = [...u.searchParams.keys()].sort();
        const q = new URLSearchParams();
        for (const k of keys) for (const v of u.searchParams.getAll(k)) q.append(k, v);
        return u.origin + pathNorm + (q.toString() ? `?${q}` : '');
      } catch {
        return want;
      }
    })();
    const normHref = (href) => {
      try {
        const u = new URL(href, location.origin);
        const pathNorm = (u.pathname.replace(/\/$/, '') || '/') + '/';
        const keys = [...u.searchParams.keys()].sort();
        const q = new URLSearchParams();
        for (const k of keys) for (const v of u.searchParams.getAll(k)) q.append(k, v);
        return u.origin + pathNorm + (q.toString() ? `?${q}` : '');
      } catch {
        return '';
      }
    };
    const interactiveVisible = (el) => {
      const r = el.getBoundingClientRect();
      const cs = getComputedStyle(el);
      if (r.width < 2 || r.height < 2) return false;
      if (cs.visibility === 'hidden' || cs.display === 'none' || Number(cs.opacity) === 0) return false;
      if (cs.pointerEvents === 'none') return false;
      // Must be in (or near) viewport after scroll — caller scrolls first.
      return true;
    };
    const cands = [...document.querySelectorAll('a[href]')].filter((a) => {
      if (a.hasAttribute('hidden') || a.getAttribute('aria-hidden') === 'true') return false;
      return normHref(a.href) === wantNorm;
    });
    const scored = cands
      .map((a) => {
        const cls = (a.className || '').toString();
        let score = 0;
        if (interactiveVisible(a)) score += 10;
        if (cls.includes('gg-bottom-nav')) score += 6;
        if (cls.includes('site-nav__link')) score += 4;
        if (a.closest('header')) score += 3;
        if (a.closest('footer')) score += 2;
        if (a.closest('.gg-bottom-nav')) score += 5;
        return { a, score, visible: interactiveVisible(a) };
      })
      .filter((x) => x.visible);
    scored.sort((x, y) => y.score - x.score);
    return scored[0]?.a || null;
  }, targetUrl);
}

async function measureClick(puppeteer, { cold, device, targetUrl, runIndex, originUrls }) {
  const viewport = VIEWPORTS[device];
  const prepared = !cold;
  const origins = originUrls && originUrls.length ? originUrls : [BASE + '/'];
  const { browser, page, docs } = await launch(puppeteer, { cacheDisabled: cold, viewport });
  try {
    let lastError = 'link-not-found-interactive';
    for (const origin of origins) {
      await page.goto(origin, {
        waitUntil: prepared ? 'networkidle2' : 'domcontentloaded',
        timeout: 120000,
      });
      await sleep(400);
      if (prepared) {
        // Origin page prepared under cache-enabled before the click navigation.
      }
      const handle = await findInteractiveLink(page, targetUrl, viewport.isMobile);
      const el = handle.asElement();
      if (!el) {
        lastError = `link-not-interactive-on:${origin}`;
        continue;
      }
      await el.evaluate((node) => node.scrollIntoView({ block: 'center', inline: 'center' }));
      await sleep(150);
      const box = await el.boundingBox();
      if (!box) {
        lastError = `link-no-bounding-box-on:${origin}`;
        continue;
      }
      // Re-check visibility after scroll (user-equivalent interaction only).
      const stillOk = await el.evaluate((node) => {
        const r = node.getBoundingClientRect();
        const cs = getComputedStyle(node);
        return (
          r.width > 1 &&
          r.height > 1 &&
          cs.visibility !== 'hidden' &&
          cs.display !== 'none' &&
          Number(cs.opacity) !== 0 &&
          cs.pointerEvents !== 'none'
        );
      });
      if (!stillOk) {
        lastError = `link-not-visible-after-scroll-on:${origin}`;
        continue;
      }
      const clickHref = await page.evaluate((node) => node.href, el);
      docs.length = 0;
      const clickWall = Date.now();
      await page.mouse.click(box.x + box.width / 2, box.y + box.height / 2);
      try {
        await page.waitForFunction(
          (want) => {
            try {
              const norm = (href) => {
                const u = new URL(href, location.origin);
                const pathNorm = (u.pathname.replace(/\/$/, '') || '/') + '/';
                const keys = [...u.searchParams.keys()].sort();
                const q = new URLSearchParams();
                for (const k of keys) for (const v of u.searchParams.getAll(k)) q.append(k, v);
                return u.origin + pathNorm + (q.toString() ? `?${q}` : '');
              };
              return norm(location.href) === norm(want);
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
              clickOrigin: origin,
              cold,
              cacheMode: prepared ? 'cache-enabled-prepared' : 'cache-disabled',
              device,
              runIndex,
              viewport,
              measured: false,
              clickMethod: 'mouse',
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
          {
            mode: 'click',
            targetUrl,
            clickHref,
            clickOrigin: origin,
            cold,
            cacheMode: prepared ? 'cache-enabled-prepared' : 'cache-disabled',
            device,
            runIndex,
            viewport,
            measured: true,
            clickMethod: 'mouse',
            warmPrep: prepared,
            warmPrepOk: prepared,
          },
          docs,
          clickWall,
          paint,
        ),
      };
    }
    return {
      mode: 'click',
      targetUrl,
      cold,
      cacheMode: prepared ? 'cache-enabled-prepared' : 'cache-disabled',
      device,
      runIndex,
      viewport,
      measured: false,
      error: lastError,
      clickMethod: null,
    };
  } finally {
    await browser.close();
  }
}

function buildMedians(rows) {
  const groups = {};
  for (const r of rows) {
    const key = [
      r.mode,
      r.device || deviceKey(r.viewport),
      r.cacheMode || cacheModeLabel(!!r.cold, !!r.warmPrep),
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
  return medians;
}

function writeSummary({ status, planned, rows, inv, destinations, navClicks, completedKeys, pendingKeys }) {
  const summary = {
    phase,
    status,
    at: new Date().toISOString(),
    base: BASE,
    method: {
      auth: 'logged-out',
      throttling: 'none',
      repeats: REPEATS,
      cacheDisabled: 'CDP Network.setCacheDisabled=true; new browser each run',
      cacheEnabledPrepared:
        'CDP cache enabled; destination (or click origin) loaded to networkidle2 before measured navigation',
      namingNote: 'Never label cache-enabled as warm without preparation; report cache-disabled exactly',
      serverCaches: 'not flushed',
      click: SKIP_CLICK
        ? 'skipped'
        : 'real mouse click only on interactive visible link; origins from inventory, not element.click on hidden',
      sampleId: 'sha256(fullUrl)+phase+mode+device+cacheMode+run',
      resume: RESUME,
    },
    inventoryCounts: inv.counts,
    destinationCount: destinations.length,
    navClickCount: navClicks.length,
    plannedSampleCount: planned,
    completedSampleCount: completedKeys.length,
    pendingSampleCount: pendingKeys.length,
    pendingKeys: pendingKeys.slice(0, 200),
    medians: buildMedians(rows),
    rows,
  };
  const partialPath = path.join(outDir, 'summary-partial.json');
  const finalPath = path.join(outDir, 'summary.json');
  fs.writeFileSync(partialPath, JSON.stringify(summary, null, 2));
  if (status === 'COMPLETE') {
    fs.writeFileSync(finalPath, JSON.stringify(summary, null, 2));
  } else {
    fs.writeFileSync(finalPath, JSON.stringify({ ...summary, note: 'PARTIAL — use summary-partial.json' }, null, 2));
  }
  return summary;
}

function originsForTarget(inv, targetUrl) {
  const want = normalizeCompareUrl(targetUrl);
  const seeds = new Set([BASE + '/']);
  for (const d of inv.navClickHrefs || []) {
    if (normalizeCompareUrl(d.url) !== want) continue;
    // Prefer surfaces that imply home; also use linkedFrom from uniqueDestinations
  }
  for (const d of inv.uniqueDestinations || []) {
    if (normalizeCompareUrl(d.url) !== want) continue;
    for (const lf of d.linkedFrom || []) {
      if (lf.seed) seeds.add(lf.seed);
    }
  }
  for (const l of inv.linkRows || []) {
    if (!l.absolute) continue;
    if (normalizeCompareUrl(l.absolute) !== want) continue;
    if (l.seed) seeds.add(l.seed);
  }
  // Prefer home first, then hubs.
  const ordered = [...seeds].sort((a, b) => {
    if (a === BASE + '/' || a === BASE) return -1;
    if (b === BASE + '/' || b === BASE) return 1;
    return a.localeCompare(b);
  });
  return ordered;
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
    .filter((u) => ONLY.length || !/sample-page|hello-world/i.test(u));
  const navClicks = (inv.navClickHrefs || [])
    .filter((d) => {
      const t = (d.text || '').trim();
      if (/بازگشت به بالا/i.test(t)) return false;
      if (/طعم-|همه-محصولات|%d8%b7%d8%b9%d9%85|%d9%87%d9%85%d9%87-%d9%85%d8%ad|legacy-/i.test(d.url)) return false;
      return true;
    })
    .map((d) => d.url)
    .filter((u) => allowUrl(u));

  const puppeteer = ensurePuppeteer();
  const existing = loadExistingSamples();
  const plan = [];

  const devicesGoto = DEVICE_LIST.includes('mobile')
    ? DEVICE_LIST
    : ['desktop'];

  if (!SKIP_CLICK) {
    for (const targetUrl of navClicks) {
      for (const device of devicesGoto) {
        for (const cold of [true, false]) {
          for (let i = 1; i <= REPEATS; i++) {
            const cacheMode = cold ? 'cache-disabled' : 'cache-enabled-prepared';
            const meta = { mode: 'click', device, cacheMode, runIndex: i, targetUrl };
            plan.push({ kind: 'click', cold, ...meta });
          }
        }
      }
    }
  }

  for (const targetUrl of destinations) {
    for (const device of devicesGoto) {
      for (const cold of [true, false]) {
        const cacheMode = cold ? 'cache-disabled' : 'cache-enabled-prepared';
        for (let i = 1; i <= REPEATS; i++) {
          plan.push({ kind: 'goto', mode: 'goto', cold, device, cacheMode, runIndex: i, targetUrl });
        }
        for (let i = 1; i <= REPEATS; i++) {
          plan.push({ kind: 'reload', mode: 'reload', cold, device, cacheMode, runIndex: i, targetUrl });
        }
      }
    }
  }

  // Deduplicate by sampleId (same mode/device/cache/run/url) without dropping destinations.
  const seenPlan = new Set();
  const dedupedPlan = [];
  let dupesRemoved = 0;
  for (const job of plan) {
    const id = sampleFileName(job).replace(/\.json$/, '');
    if (seenPlan.has(id)) {
      dupesRemoved++;
      continue;
    }
    seenPlan.add(id);
    dedupedPlan.push(job);
  }
  plan.length = 0;
  plan.push(...dedupedPlan);

  fs.writeFileSync(
    path.join(outDir, 'scenario-plan.json'),
    JSON.stringify(
      {
        at: new Date().toISOString(),
        phase,
        uniqueDestinations: destinations.length,
        navClickTargets: navClicks.length,
        devices: devicesGoto,
        methods: SKIP_CLICK ? ['goto', 'reload'] : ['click', 'goto', 'reload'],
        cacheModes: ['cache-disabled', 'cache-enabled-prepared'],
        repeats: REPEATS,
        plannedSamples: plan.length,
        duplicatesRemoved: dupesRemoved,
        destinations,
        navClicks,
      },
      null,
      2,
    ),
  );

  const rows = [];
  const completedKeys = [];
  const pendingKeys = [];

  for (const job of plan) {
    const fname = sampleFileName(job);
    const sampleId = fname.replace(/\.json$/, '');
    const filePath = path.join(outDir, fname);
    if (RESUME && (existing.has(sampleId) || isResumableValidFile(filePath))) {
      const hit = existing.get(sampleId);
      const row = hit?.row || JSON.parse(fs.readFileSync(filePath, 'utf8'));
      rows.push(row);
      completedKeys.push(sampleId);
      continue;
    }
    pendingKeys.push(sampleId);
  }

  console.log(
    JSON.stringify({
      phase,
      destinations: destinations.length,
      navClicks: navClicks.length,
      planned: plan.length,
      already: completedKeys.length,
      todo: pendingKeys.length,
      repeats: REPEATS,
      devices: devicesGoto,
    }),
  );

  // Persist resume manifest immediately.
  fs.writeFileSync(
    path.join(outDir, 'manifest.json'),
    JSON.stringify(
      {
        phase,
        at: new Date().toISOString(),
        planned: plan.length,
        completed: completedKeys.length,
        pending: pendingKeys.length,
      },
      null,
      2,
    ),
  );
  writeSummary({
    status: pendingKeys.length ? 'PARTIAL' : 'COMPLETE',
    planned: plan.length,
    rows,
    inv,
    destinations,
    navClicks,
    completedKeys,
    pendingKeys,
  });

  for (const job of plan) {
    const fname = sampleFileName(job);
    const sampleId = fname.replace(/\.json$/, '');
    const filePath = path.join(outDir, fname);
    if (RESUME && isResumableValidFile(filePath)) continue;

    console.log(`${job.kind} ${job.targetUrl} ${job.device} ${job.cacheMode} #${job.runIndex}`);
    let row;
    try {
      if (job.kind === 'click') {
        row = await measureClick(puppeteer, {
          cold: job.cold,
          device: job.device,
          targetUrl: job.targetUrl,
          runIndex: job.runIndex,
          originUrls: originsForTarget(inv, job.targetUrl),
        });
      } else if (job.kind === 'goto') {
        row = await measureGoto(puppeteer, {
          cold: job.cold,
          device: job.device,
          targetUrl: job.targetUrl,
          runIndex: job.runIndex,
        });
      } else {
        row = await measureReload(puppeteer, {
          cold: job.cold,
          device: job.device,
          targetUrl: job.targetUrl,
          runIndex: job.runIndex,
        });
      }
    } catch (err) {
      row = {
        mode: job.mode,
        targetUrl: job.targetUrl,
        cold: job.cold,
        cacheMode: job.cacheMode,
        device: job.device,
        runIndex: job.runIndex,
        measured: false,
        error: String(err.message || err),
      };
    }

    row.sampleId = sampleId;
    row.phase = phase;
    row.cacheMode = row.cacheMode || job.cacheMode;
    row.device = row.device || job.device;
    row.validity =
      row.measured === false || row.error
        ? 'FAILED'
        : row.ttfbMs != null
          ? 'VALID'
          : 'UNKNOWN';
    row.validityReason = row.error || (row.validity === 'VALID' ? 'fresh-run' : 'no-ttfb');

    try {
      const saved = writeSampleAtomic(filePath, row);
      rows.push(saved.row);
      completedKeys.push(sampleId);
      const idx = pendingKeys.indexOf(sampleId);
      if (idx >= 0) pendingKeys.splice(idx, 1);
    } catch (err) {
      console.error(String(err.message || err));
      row.error = (row.error ? row.error + ';' : '') + String(err.message || err);
      rows.push(row);
    }

    writeSummary({
      status: pendingKeys.length ? 'PARTIAL' : 'COMPLETE',
      planned: plan.length,
      rows,
      inv,
      destinations,
      navClicks,
      completedKeys,
      pendingKeys,
    });

    console.log(
      JSON.stringify({
        ok: row.measured !== false,
        ttfb: row.ttfbMs,
        clickToReq: row.clickToDocumentRequestMs,
        fcp: row.fcpMs,
        cacheMode: row.cacheMode,
        device: row.device,
        url: row.finalUrl || row.error,
      }),
    );
  }

  const summary = writeSummary({
    status: pendingKeys.length ? 'PARTIAL' : 'COMPLETE',
    planned: plan.length,
    rows,
    inv,
    destinations,
    navClicks,
    completedKeys,
    pendingKeys,
  });
  console.log('STATUS', summary.status, 'completed', completedKeys.length, '/', plan.length);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
