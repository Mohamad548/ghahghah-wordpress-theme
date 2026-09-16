/**
 * Validate that Yekan actually paints (CDP CSS.getPlatformFontsForNode).
 * Usage: node scripts/font-render-validate.cjs [label] [outDir]
 * label: after | before-ef980de
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const label = process.argv[2] || 'after';
const outDir = path.resolve(
  process.argv[3] || `docs/audits/artifacts/agency-cls/font-validate/${label}`,
);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const base = (process.env.BASE_URL || 'http://localhost:8898').replace(/\/$/, '');

const PAGES = {
  home: `${base}/`,
  wholesale: `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d8%ae%d8%b1%db%8c%d8%af-%d8%b9%d9%85%d8%af%d9%87/`,
  agency: `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d9%86%d9%85%d8%a7%db%8c%d9%86%d8%af%da%af%db%8c/`,
};

const VIEWPORTS = {
  mobile: { width: 390, height: 844, isMobile: true },
  desktop: { width: 1440, height: 900, isMobile: false },
};

/** Chrome Slow 3G-ish — recorded in report. */
const SLOW_NET = {
  name: 'slow-3g-ish',
  downloadThroughput: Math.floor((500 * 1024) / 8),
  uploadThroughput: Math.floor((500 * 1024) / 8),
  latency: 400,
};

const SELECTORS = {
  home: {
    // Prefer text nodes with painted glyphs (logo .site-brand is often image-only).
    title: '.ghahghah-hero__live, .ghahghah-featured__title, h2, h1',
    body: '.ghahghah-featured__text, .ghahghah-factory__text, main p',
    menu: '.gg-bottom-nav__label, .site-header__nav a, .site-nav a',
  },
  wholesale: {
    title: 'h1, .ghahghah-wholesale-page__title, .ghahghah-page-title',
    body: 'main p, .ghahghah-wholesale-page__lead, .ghahghah-form__legal',
    menu: '.gg-bottom-nav__label, .site-header__nav a, .site-nav a',
  },
  agency: {
    title: '.ghahghah-agency-page__title',
    body: '.ghahghah-agency-page__lead',
    menu: '.gg-bottom-nav__label, .ghahghah-agency-page__crumb-current, .site-header__nav a, .site-nav a',
  },
};

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

function isYekan(familyName) {
  const n = (familyName || '').toLowerCase();
  return /yekan|بخ|bakh/.test(n);
}

async function resolveNodeId(client, page, rootId, selectors) {
  const list = selectors.split(',').map((s) => s.trim()).filter(Boolean);
  for (const sel of list) {
    try {
      const visible = await page.evaluate((selector) => {
        const el = document.querySelector(selector);
        if (!el) return false;
        const t = (el.textContent || '').trim();
        if (!t) return false;
        const r = el.getBoundingClientRect();
        if (r.width < 2 || r.height < 2) return false;
        const cs = getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden' || Number(cs.opacity) === 0) {
          return false;
        }
        return true;
      }, sel);
      if (!visible) continue;
      const { nodeId } = await client.send('DOM.querySelector', {
        nodeId: rootId,
        selector: sel,
      });
      if (nodeId) return { nodeId, selector: sel };
    } catch (_) {
      /* try next */
    }
  }
  return null;
}

async function fontsForNode(client, nodeId) {
  try {
    const res = await client.send('CSS.getPlatformFontsForNode', { nodeId });
    return res.fonts || [];
  } catch (e) {
    return [{ error: String(e.message || e) }];
  }
}

async function probePage(browser, pageKey, url, vpName, vp, opts) {
  const context = await browser.createBrowserContext();
  const page = await context.newPage();
  const client = await page.createCDPSession();
  await client.send('Network.enable');
  await client.send('DOM.enable');
  await client.send('CSS.enable');

  if (opts.disableCache) {
    await client.send('Network.setCacheDisabled', { cacheDisabled: true });
  }
  if (opts.slow) {
    await client.send('Network.emulateNetworkConditions', {
      offline: false,
      downloadThroughput: SLOW_NET.downloadThroughput,
      uploadThroughput: SLOW_NET.uploadThroughput,
      latency: SLOW_NET.latency,
    });
  }

  await page.setViewport({
    width: vp.width,
    height: vp.height,
    isMobile: vp.isMobile,
    hasTouch: vp.isMobile,
  });
  if (vp.isMobile) {
    await page.setUserAgent(
      'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
    );
  }

  const fontReqs = [];
  client.on('Network.responseReceived', (ev) => {
    const u = ev.response?.url || '';
    if (/yekan|fonts\/yekan|\.woff2?(\?|$)/i.test(u)) {
      fontReqs.push({
        url: u,
        status: ev.response.status,
        mimeType: ev.response.mimeType,
        fromCache: !!ev.response.fromDiskCache || !!ev.response.fromPrefetchCache,
        timing: ev.response.timing || null,
      });
    }
  });

  const t0 = Date.now();
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 120000 });
  // Allow optional font window + layout settle.
  await page.evaluate(() => document.fonts.ready.catch(() => {}));
  await new Promise((r) => setTimeout(r, opts.slow ? 2500 : 800));
  const navMs = Date.now() - t0;

  const { root } = await client.send('DOM.getDocument', { depth: 0 });
  const roles = SELECTORS[pageKey];
  const samples = {};

  for (const [role, sel] of Object.entries(roles)) {
    const hit = await resolveNodeId(client, page, root.nodeId, sel);
    if (!hit) {
      samples[role] = { selectorTried: sel, found: false };
      continue;
    }
    const platformFonts = await fontsForNode(client, hit.nodeId);
    const metrics = await page.evaluate((selector) => {
      const el = document.querySelector(selector);
      if (!el) return null;
      const cs = getComputedStyle(el);
      const r = el.getBoundingClientRect();
      const range = document.createRange();
      range.selectNodeContents(el);
      const rects = Array.from(range.getClientRects()).map((x) => ({
        x: x.x,
        y: x.y,
        w: x.width,
        h: x.height,
      }));
      let fontsCheck = null;
      try {
        fontsCheck = {
          yekan400: document.fonts.check('400 16px "Yekan Bakh FaNum"'),
          yekan700: document.fonts.check('700 16px "Yekan Bakh FaNum"'),
        };
      } catch (_) {
        fontsCheck = { error: true };
      }
      return {
        textSample: (el.textContent || '').trim().slice(0, 80),
        computedFamily: cs.fontFamily,
        fontSize: cs.fontSize,
        fontWeight: cs.fontWeight,
        lineHeight: cs.lineHeight,
        box: { w: r.width, h: r.height, x: r.x, y: r.y },
        lineBoxCount: rects.length,
        lineRects: rects.slice(0, 8),
        fontsCheck,
        // Note: fonts.check alone is NOT proof of painted glyphs.
      };
    }, hit.selector);

    const custom = platformFonts.filter((f) => f.isCustomFont);
    const yekanPainted = platformFonts.some((f) => isYekan(f.familyName));
    const fallbackOnly =
      platformFonts.length > 0 &&
      !yekanPainted &&
      !platformFonts.some((f) => f.error);

    samples[role] = {
      found: true,
      selector: hit.selector,
      platformFonts,
      yekanPainted,
      customFontPainted: custom.length > 0,
      fallbackOnly,
      ...metrics,
    };
  }

  const shot = path.join(outDir, `${pageKey}-${vpName}-${opts.tag}.png`);
  await page.screenshot({ path: shot, fullPage: false });

  const preload = await page.evaluate(() =>
    Array.from(document.querySelectorAll('link[rel="preload"][as="font"]')).map((l) => ({
      href: l.href,
      type: l.type,
      crossOrigin: l.crossOrigin,
    })),
  );

  await page.close();
  await context.close();

  return {
    pageKey,
    url,
    viewport: vpName,
    tag: opts.tag,
    navMs,
    fontRequests: fontReqs,
    preloadLinks: preload,
    samples,
    screenshot: path.basename(shot),
  };
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });

  const report = {
    label,
    generatedAt: new Date().toISOString(),
    base,
    method: 'CDP CSS.getPlatformFontsForNode (not font-family / fonts.check alone)',
    slowNetwork: SLOW_NET,
    runs: [],
  };

  try {
    for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
      for (const [pageKey, url] of Object.entries(PAGES)) {
        // Cold first visit
        report.runs.push(
          await probePage(browser, pageKey, url, vpName, vp, {
            tag: 'cold',
            disableCache: true,
            slow: false,
          }),
        );
        // Warm revisit (cache allowed)
        report.runs.push(
          await probePage(browser, pageKey, url, vpName, vp, {
            tag: 'warm',
            disableCache: false,
            slow: false,
          }),
        );
      }
    }
    // One slow-network cold pass per page (mobile only to bound runtime)
    for (const [pageKey, url] of Object.entries(PAGES)) {
      report.runs.push(
        await probePage(browser, pageKey, url, 'mobile', VIEWPORTS.mobile, {
          tag: 'slow-cold',
          disableCache: true,
          slow: true,
        }),
      );
    }
  } finally {
    await browser.close();
  }

  // Summary flags
  const flags = [];
  for (const run of report.runs) {
    for (const [role, s] of Object.entries(run.samples)) {
      if (!s.found) {
        flags.push({
          severity: 'warn',
          run: `${run.pageKey}/${run.viewport}/${run.tag}`,
          role,
          msg: 'selector not found',
        });
        continue;
      }
      if (s.fallbackOnly) {
        flags.push({
          severity: 'fail',
          run: `${run.pageKey}/${run.viewport}/${run.tag}`,
          role,
          msg: 'platform fonts show no Yekan — fallback painted',
          platformFonts: s.platformFonts,
          computedFamily: s.computedFamily,
        });
      } else if (!s.yekanPainted) {
        flags.push({
          severity: 'warn',
          run: `${run.pageKey}/${run.viewport}/${run.tag}`,
          role,
          msg: 'Yekan name not matched in platformFonts',
          platformFonts: s.platformFonts,
        });
      }
    }
  }
  report.flags = flags;
  report.yekanPaintedAllCritical = !flags.some((f) => f.severity === 'fail');

  const outJson = path.join(outDir, 'font-render-report.json');
  fs.writeFileSync(outJson, JSON.stringify(report, null, 2));
  console.log('Wrote', outJson);
  console.log(
    'yekanPaintedAllCritical=',
    report.yekanPaintedAllCritical,
    'flags=',
    flags.length,
  );
  for (const f of flags.slice(0, 20)) {
    console.log(`[${f.severity}] ${f.run} ${f.role}: ${f.msg}`);
  }
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
