/**
 * Limited Home/Wholesale impact probe: font request count + LCP-ish image timing.
 * Usage: node scripts/font-impact-probe.cjs [label] [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const label = process.argv[2] || 'after';
const outDir = path.resolve(
  process.argv[3] || `docs/audits/artifacts/agency-cls/font-impact/${label}`,
);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const base = (process.env.BASE_URL || 'http://localhost:8898').replace(/\/$/, '');

const PAGES = {
  home: `${base}/`,
  wholesale: `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d8%ae%d8%b1%db%8c%d8%af-%d8%b9%d9%85%d8%af%d9%87/`,
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

(async () => {
  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  const report = { label, generatedAt: new Date().toISOString(), base, pages: {} };

  try {
    for (const [key, url] of Object.entries(PAGES)) {
      const page = await browser.newPage();
      const client = await page.createCDPSession();
      await client.send('Network.enable');
      await client.send('Network.setCacheDisabled', { cacheDisabled: true });
      await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });

      const fonts = [];
      const images = [];
      const preloads = [];

      client.on('Network.requestWillBeSent', (ev) => {
        const u = ev.request.url;
        if (/yekan|\.woff2?/i.test(u)) {
          fonts.push({
            url: u,
            type: ev.type,
            initiator: ev.initiator?.type,
            wallTime: ev.wallTime,
          });
        }
      });
      client.on('Network.responseReceived', (ev) => {
        const u = ev.response.url;
        if (/yekan|\.woff2?/i.test(u)) {
          const f = fonts.find((x) => x.url === u && !x.status);
          if (f) {
            f.status = ev.response.status;
            f.mimeType = ev.response.mimeType;
            f.fromDiskCache = !!ev.response.fromDiskCache;
          }
        }
        if (ev.type === 'Image' || /\/uploads\/|\/assets\/images\//i.test(u)) {
          images.push({
            url: u,
            status: ev.response.status,
            mimeType: ev.response.mimeType,
          });
        }
      });

      const t0 = Date.now();
      await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
      await page.evaluate(() => document.fonts.ready.catch(() => {}));
      await new Promise((r) => setTimeout(r, 500));
      const navMs = Date.now() - t0;

      const dom = await page.evaluate(() => {
        const preload = Array.from(
          document.querySelectorAll('link[rel="preload"][as="font"]'),
        ).map((l) => l.href);
        const heroImg =
          document.querySelector('.ghahghah-hero__slide.is-active img') ||
          document.querySelector('main img');
        let lcpCandidate = null;
        if (heroImg) {
          lcpCandidate = {
            src: heroImg.currentSrc || heroImg.src,
            complete: heroImg.complete,
            naturalWidth: heroImg.naturalWidth,
            loading: heroImg.getAttribute('loading'),
          };
        }
        return { preload, lcpCandidate, preloadCount: preload.length };
      });

      // Duplicate preload detection only — request≠proof of preload consumption.
      const preloadCounts = {};
      for (const href of dom.preload) {
        preloadCounts[href] = (preloadCounts[href] || 0) + 1;
      }
      const duplicatePreloads = Object.entries(preloadCounts)
        .filter(([, n]) => n > 1)
        .map(([href, n]) => ({ href, n }));

      const uniqueFontUrls = [...new Set(fonts.map((f) => f.url.split('?')[0]))];
      const uniquePreloadUrls = [...new Set(dom.preload.map((h) => h.split('?')[0]))];

      report.pages[key] = {
        url,
        navMs,
        fontRequestCount: fonts.length,
        uniqueFontUrlCount: uniqueFontUrls.length,
        uniqueFontUrls,
        fonts,
        preload: dom.preload,
        preloadCount: dom.preloadCount,
        uniquePreloadCount: uniquePreloadUrls.length,
        duplicatePreloads,
        // Limited claim: unique preload tags vs unique font URL requests (not "preload used").
        claim:
          uniquePreloadUrls.length === 2 && uniqueFontUrls.length === 2
            ? 'two_unique_preloads_and_two_font_requests'
            : 'see_counts',
        note: 'Font file requested does not prove the preload hint was consumed by the browser.',
        lcpCandidate: dom.lcpCandidate,
        imageResponseCount: images.length,
      };

      console.log(
        `[${key}] fonts=${fonts.length} uniqueFonts=${uniqueFontUrls.length} preload=${dom.preloadCount} uniquePreload=${uniquePreloadUrls.length} dup=${duplicatePreloads.length} navMs=${navMs}`,
      );
      await page.close();
    }
  } finally {
    await browser.close();
  }

  fs.writeFileSync(path.join(outDir, 'impact.json'), JSON.stringify(report, null, 2));
  console.log('Wrote', path.join(outDir, 'impact.json'));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
