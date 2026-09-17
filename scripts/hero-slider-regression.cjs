/**
 * Hero slider regression harness (request-controlled, no uploads changes).
 * Usage: node scripts/hero-slider-regression.cjs [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const BASE = process.env.HOME_URL || 'http://localhost:8898/';
const outDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/home-lcp/slider-regression',
);
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

function isBannerUrl(url) {
  return /flavour-banner|banner-v2|\/\d{2}-[a-z0-9-]*banner/i.test(url);
}

function slideKeyFromUrl(url) {
  const m = url.match(/\/(\d{2})-[^/?#]*banner/i);
  return m ? m[1] : null;
}

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

async function activeIndex(page) {
  return page.evaluate(() => {
    const el = document.querySelector('.ghahghah-hero__slide.is-active');
    return el ? parseInt(el.getAttribute('data-index') || '-1', 10) : -1;
  });
}

async function waitReady(page, ms = 15000) {
  await page.waitForFunction(
    () => document.querySelector('[data-ghahghah-hero-slider]')?.classList.contains('is-ready'),
    { timeout: ms },
  );
}

async function clickNext(page) {
  await page.click('[data-ghahghah-hero-next]');
}

async function clickPrev(page) {
  await page.click('[data-ghahghah-hero-prev]');
}

async function clickDot(page, idx) {
  await page.click(`[data-ghahghah-hero-dot][data-index="${idx}"]`);
}

/**
 * Per-slide request gate. Keys are '01'..'09' (filename prefix).
 * policy: { delayMs?, status?, hold?, body?: 'invalid'|'pass' }
 */
function installBannerGate(page) {
  const policies = new Map();
  const held = new Map(); // key -> [{continue, abort, respond}]
  const seen = [];

  const releaseOne = async (key) => {
    const q = held.get(key) || [];
    held.set(key, []);
    for (const item of q) {
      await item.continue();
    }
  };

  const api = {
    set(key, policy) {
      policies.set(key, policy || {});
    },
    clear(key) {
      policies.delete(key);
    },
    async release(key) {
      const pol = policies.get(key) || {};
      if (pol.hold) {
        pol.hold = false;
        policies.set(key, pol);
      }
      await releaseOne(key);
    },
    seen: () => [...seen],
    uniqueBannerKeys: () => [...new Set(seen.map((s) => s.key).filter(Boolean))],
  };

  page.on('request', async (req) => {
    try {
      if (req.resourceType() !== 'image' && !isBannerUrl(req.url())) {
        await req.continue();
        return;
      }
      if (!isBannerUrl(req.url())) {
        await req.continue();
        return;
      }
      const key = slideKeyFromUrl(req.url());
      seen.push({ url: req.url(), key, at: Date.now() });
      const pol = key ? policies.get(key) : null;
      if (!pol) {
        await req.continue();
        return;
      }
      if (pol.status === 404) {
        await req.respond({ status: 404, contentType: 'text/plain', body: 'missing' });
        return;
      }
      if (pol.body === 'invalid') {
        await req.respond({
          status: 200,
          contentType: 'image/webp',
          body: Buffer.from('not-a-valid-webp-payload'),
          headers: { 'Cache-Control': 'no-store' },
        });
        return;
      }
      const run = async () => {
        if (pol.delayMs) {
          await sleep(pol.delayMs);
        }
        await req.continue();
      };
      if (pol.hold) {
        if (!held.has(key)) held.set(key, []);
        held.get(key).push({
          continue: run,
        });
        return;
      }
      await run();
    } catch (err) {
      try {
        await req.continue();
      } catch {
        /* already handled */
      }
    }
  });

  return api;
}

async function withPage(puppeteer, opts, fn) {
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  try {
    const page = await browser.newPage();
    if (opts.emulateReducedMotion) {
      await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }]);
    }
    await page.setViewport(opts.viewport || { width: 390, height: 844, isMobile: false });
    if (opts.javaScriptEnabled === false) {
      await page.setJavaScriptEnabled(false);
    }
    if (opts.intercept) {
      await page.setRequestInterception(true);
    }
    return await fn(page, browser);
  } finally {
    await browser.close();
  }
}

function pass(name, detail) {
  return { name, ok: true, detail: detail || '' };
}
function fail(name, detail) {
  return { name, ok: false, detail: detail || '' };
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const results = [];
  const phase = process.env.SLIDER_PHASE || 'after';

  // --- 1) Slow destination (>4s) must not activate ---
  results.push(
    await withPage(puppeteer, { intercept: true }, async (page) => {
      const gate = installBannerGate(page);
      gate.set('02', { delayMs: 5500 });
      await page.goto(BASE, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await waitReady(page);
      const before = await activeIndex(page);
      await clickNext(page);
      await sleep(4500);
      const mid = await activeIndex(page);
      await sleep(2000);
      const afterWait = await activeIndex(page);
      const ok = before === 0 && mid === 0 && afterWait === 0;
      return ok
        ? pass('slow-load-keeps-active', `before=${before} mid=${mid} after=${afterWait}`)
        : fail('slow-load-keeps-active', `before=${before} mid=${mid} after=${afterWait}`);
    }),
  );

  // --- 2) 404 destination does not activate; healthy next still works ---
  results.push(
    await withPage(puppeteer, { intercept: true }, async (page) => {
      const gate = installBannerGate(page);
      gate.set('02', { status: 404 });
      await page.goto(BASE, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await waitReady(page);
      await clickNext(page);
      await sleep(1200);
      const after404 = await activeIndex(page);
      gate.clear('02');
      // Slide 03 (file 03-...) via second Next from still-active 0 → requested resets to 0 on fail,
      // so Next goes to 1 again. Use dot for slide 2 (index 2, file 03).
      await clickDot(page, 2);
      await sleep(2000);
      const afterHealthy = await activeIndex(page);
      const ok = after404 === 0 && afterHealthy === 2;
      return ok
        ? pass('404-then-healthy-dot', `after404=${after404} afterHealthy=${afterHealthy}`)
        : fail('404-then-healthy-dot', `after404=${after404} afterHealthy=${afterHealthy}`);
    }),
  );

  // --- 3) Invalid body / decode failure does not activate ---
  results.push(
    await withPage(puppeteer, { intercept: true }, async (page) => {
      const gate = installBannerGate(page);
      gate.set('02', { body: 'invalid' });
      await page.goto(BASE, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await waitReady(page);
      await clickNext(page);
      await sleep(1500);
      const idx = await activeIndex(page);
      gate.clear('02');
      await clickDot(page, 2);
      await sleep(2000);
      const healthy = await activeIndex(page);
      const ok = idx === 0 && healthy === 2;
      return ok
        ? pass('decode-fail-then-healthy', `idx=${idx} healthy=${healthy}`)
        : fail('decode-fail-then-healthy', `idx=${idx} healthy=${healthy}`);
    }),
  );

  // --- 4) Essential race: two Next, image1 finishes first, 2 still pending → not active 2 ---
  results.push(
    await withPage(puppeteer, { intercept: true }, async (page) => {
      const gate = installBannerGate(page);
      gate.set('02', { hold: true });
      gate.set('03', { hold: true });
      await page.goto(BASE, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await waitReady(page);
      const i0 = await activeIndex(page);
      await clickNext(page);
      await clickNext(page);
      await sleep(200);
      // Finish slide 1 (02) while 2 (03) still held.
      await gate.release('02');
      await sleep(800);
      const mid = await activeIndex(page);
      const midDot = await page.evaluate(
        () => document.querySelector('.ghahghah-hero__dot.is-active')?.getAttribute('data-index'),
      );
      const live = await page.evaluate(
        () => document.querySelector('[data-ghahghah-hero-live]')?.textContent || '',
      );
      await gate.release('03');
      await sleep(1500);
      const final = await activeIndex(page);
      // Critical: while 03 pending after 02 completed, must NOT show slide 2.
      const okMid = mid === 0 && midDot === '0' && !/اسلاید 3/.test(live);
      const okFinal = final === 2;
      const ok = i0 === 0 && okMid && okFinal;
      return ok
        ? pass('out-of-order-last-wins', `mid=${mid} midDot=${midDot} live="${live}" final=${final}`)
        : fail(
            'out-of-order-last-wins',
            `i0=${i0} mid=${mid} midDot=${midDot} live="${live}" final=${final}`,
          );
    }),
  );

  // --- 5) Controls: next/prev/dots/keyboard/swipe ---
  results.push(
    await withPage(puppeteer, { intercept: false }, async (page) => {
      await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
      await waitReady(page);
      await clickNext(page);
      await sleep(900);
      const afterNext = await activeIndex(page);
      await clickPrev(page);
      await sleep(900);
      const afterPrev = await activeIndex(page);
      await clickDot(page, 4);
      await sleep(1200);
      const afterDot = await activeIndex(page);
      await page.focus('[data-ghahghah-hero-slider]');
      await page.keyboard.press('ArrowLeft');
      await sleep(900);
      const afterKey = await activeIndex(page);
      const frame = await page.$('.ghahghah-hero__frame');
      const box = await frame.boundingBox();
      await page.mouse.move(box.x + box.width * 0.7, box.y + box.height * 0.5);
      await page.mouse.down();
      await page.mouse.move(box.x + box.width * 0.2, box.y + box.height * 0.5, { steps: 8 });
      await page.mouse.up();
      await sleep(900);
      const afterSwipe = await activeIndex(page);
      const ok =
        afterNext === 1 &&
        afterPrev === 0 &&
        afterDot === 4 &&
        afterKey === 5 &&
        afterSwipe === 6;
      return ok
        ? pass('controls-keyboard-swipe', {
            afterNext,
            afterPrev,
            afterDot,
            afterKey,
            afterSwipe,
          })
        : fail('controls-keyboard-swipe', {
            afterNext,
            afterPrev,
            afterDot,
            afterKey,
            afterSwipe,
          });
    }),
  );

  // --- 6) Nine distinct slides mobile + desktop ---
  for (const vp of [
    { width: 390, height: 844, label: 'mobile' },
    { width: 1440, height: 900, label: 'desktop' },
  ]) {
    results.push(
      await withPage(puppeteer, { viewport: { ...vp, isMobile: false } }, async (page) => {
        await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
        await waitReady(page);
        const srcs = [];
        for (let i = 0; i < 9; i++) {
          if (i > 0) {
            await clickNext(page);
            await sleep(700);
          }
          const info = await page.evaluate(() => {
            const slide = document.querySelector('.ghahghah-hero__slide.is-active');
            const img = slide?.querySelector('img');
            return {
              idx: slide?.getAttribute('data-index'),
              src: img?.currentSrc || img?.src || '',
              nw: img?.naturalWidth || 0,
            };
          });
          srcs.push(info);
        }
        const distinct = new Set(srcs.map((s) => s.src)).size;
        const allDecoded = srcs.every((s) => s.nw > 0);
        const ok = distinct === 9 && allDecoded && srcs.length === 9;
        return ok
          ? pass(`nine-distinct-${vp.label}`, { distinct, allDecoded })
          : fail(`nine-distinct-${vp.label}`, { distinct, allDecoded, srcs });
      }),
    );
  }

  // --- 7) Initial load: only slide 0 + at most one prefetch ---
  results.push(
    await withPage(puppeteer, { intercept: true }, async (page) => {
      const gate = installBannerGate(page);
      await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
      await waitReady(page);
      await sleep(500);
      const keys = gate.uniqueBannerKeys();
      const slidesWithSrc = await page.evaluate(
        () =>
          [...document.querySelectorAll('.ghahghah-hero__image')].filter((im) =>
            im.getAttribute('src'),
          ).length,
      );
      const ok = keys.length <= 2 && slidesWithSrc <= 2 && keys.includes('01');
      return ok
        ? pass('initial-prefetch-budget', { keys, slidesWithSrc })
        : fail('initial-prefetch-budget', { keys, slidesWithSrc });
    }),
  );

  // --- 8) First slide visible with JS disabled ---
  results.push(
    await withPage(puppeteer, { javaScriptEnabled: false }, async (page) => {
      await page.goto(BASE, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await sleep(1500);
      const data = await page.evaluate(() => {
        const slide = document.querySelector('.ghahghah-hero__slide.is-active');
        const img = slide?.querySelector('img');
        const cs = slide ? getComputedStyle(slide) : null;
        return {
          hasActive: !!slide,
          idx: slide?.getAttribute('data-index'),
          opacity: cs?.opacity,
          visibility: cs?.visibility,
          src: img?.getAttribute('src') || '',
          complete: !!img?.complete,
          nw: img?.naturalWidth || 0,
        };
      });
      const ok =
        data.hasActive &&
        data.idx === '0' &&
        data.src &&
        parseFloat(data.opacity) > 0.9 &&
        data.visibility === 'visible';
      return ok ? pass('noscript-first-slide-visible', data) : fail('noscript-first-slide-visible', data);
    }),
  );

  // --- 9) reduced-motion: transition none under .is-ready; no autoplay past interval+margin ---
  results.push(
    await withPage(puppeteer, { emulateReducedMotion: true }, async (page) => {
      await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
      await waitReady(page);
      const meta = await page.evaluate(() => {
        const root = document.querySelector('[data-ghahghah-hero-slider]');
        const slide = document.querySelector('.ghahghah-hero__slide');
        const cs = getComputedStyle(slide);
        return {
          interval: parseInt(root.getAttribute('data-interval') || '0', 10),
          ready: root.classList.contains('is-ready'),
          transitionDuration: cs.transitionDuration,
          transitionProperty: cs.transitionProperty,
          index: document.querySelector('.ghahghah-hero__slide.is-active')?.getAttribute('data-index'),
        };
      });
      const i0 = meta.index;
      const waitMs = meta.interval + 2000;
      await sleep(waitMs);
      const i1 = await page.evaluate(
        () => document.querySelector('.ghahghah-hero__slide.is-active')?.getAttribute('data-index'),
      );
      const durParts = String(meta.transitionDuration)
        .split(',')
        .map((s) => s.trim());
      const allZero = durParts.every((d) => d === '0s' || d === '0ms');
      const held = i0 === i1;
      const ok = meta.ready && allZero && held && waitMs > meta.interval;
      return ok
        ? pass('reduced-motion-ready-and-autoplay', {
            ...meta,
            waitMs,
            i1,
            allZero,
            held,
          })
        : fail('reduced-motion-ready-and-autoplay', {
            ...meta,
            waitMs,
            i1,
            allZero,
            held,
          });
    }),
  );

  // --- 10) Normal autoplay still advances without reduced motion ---
  results.push(
    await withPage(puppeteer, {}, async (page) => {
      await page.goto(BASE, { waitUntil: 'networkidle2', timeout: 90000 });
      await waitReady(page);
      // Avoid hover pause
      await page.mouse.move(0, 0);
      const interval = await page.evaluate(() =>
        parseInt(
          document.querySelector('[data-ghahghah-hero-slider]')?.getAttribute('data-interval') ||
            '0',
          10,
        ),
      );
      const i0 = await activeIndex(page);
      await sleep(interval + 1500);
      const i1 = await activeIndex(page);
      const ok = i0 === 0 && i1 === 1;
      return ok
        ? pass('autoplay-advances', { interval, i0, i1 })
        : fail('autoplay-advances', { interval, i0, i1 });
    }),
  );

  const summary = {
    phase,
    base: BASE,
    at: new Date().toISOString(),
    passed: results.filter((r) => r.ok).length,
    failed: results.filter((r) => !r.ok).length,
    results,
  };
  fs.writeFileSync(path.join(outDir, `summary-${phase}.json`), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary, null, 2));
  if (summary.failed > 0) {
    process.exitCode = 1;
  }
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
