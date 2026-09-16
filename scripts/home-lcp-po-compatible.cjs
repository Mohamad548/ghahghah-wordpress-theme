/**
 * Compatible Home LCP ×3: 390×844, isMobile=false, default DPR.
 * Usage: node scripts/home-lcp-po-compatible.cjs [phase] [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const phase = process.argv[2] || 'after';
const outDir = path.resolve(
  process.argv[3] || `docs/audits/artifacts/home-lcp/po-compatible/${phase}`,
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

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const runs = [];
  for (let i = 1; i <= 3; i++) {
    const browser = await puppeteer.launch({
      executablePath: chrome,
      headless: 'new',
      args: ['--no-sandbox', '--disable-gpu'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 390, height: 844, isMobile: false, hasTouch: false });
    const client = await page.createCDPSession();
    await client.send('Network.enable');
    await client.send('Network.setCacheDisabled', { cacheDisabled: true });
    const banners = [];
    client.on('Network.responseReceived', (ev) => {
      const u = ev.response.url;
      if (/flavour-banner|banner-v2|\/\d{2}-[a-z0-9-]*banner/i.test(u)) {
        banners.push({ url: u, status: ev.response.status, encoded: ev.response.encodedDataLength });
      }
    });
    await page.evaluateOnNewDocument(() => {
      window.__gh = { lcp: [], cls: 0 };
      new PerformanceObserver((l) => {
        for (const e of l.getEntries()) {
          if (e.hadRecentInput) continue;
          window.__gh.cls += e.value || 0;
        }
      }).observe({ type: 'layout-shift', buffered: true });
      new PerformanceObserver((l) => {
        for (const e of l.getEntries()) {
          window.__gh.lcp.push({
            size: e.size,
            startTime: e.startTime,
            renderTime: e.renderTime,
            loadTime: e.loadTime,
            url: e.url,
          });
        }
      }).observe({ type: 'largest-contentful-paint', buffered: true });
    });
    const t0 = Date.now();
    await page.goto('http://localhost:8898/', { waitUntil: 'networkidle2', timeout: 90000 });
    await new Promise((r) => setTimeout(r, 3000));
    const navMs = Date.now() - t0;
    const data = await page.evaluate(() => {
      const last = window.__gh.lcp.at(-1) || null;
      const imgs = [...document.querySelectorAll('.ghahghah-hero__image')];
      const activeImg = document.querySelector('.ghahghah-hero__slide.is-active img');
      return {
        visibilityState: document.visibilityState,
        lcpEntries: window.__gh.lcp,
        lcpLast: last,
        cls: window.__gh.cls,
        slidesWithSrc: imgs.filter((im) => im.getAttribute('src')).length,
        active: {
          complete: !!activeImg?.complete,
          nw: activeImg?.naturalWidth || 0,
          src: activeImg?.currentSrc || null,
        },
        ready: !!document.querySelector('[data-ghahghah-hero-slider]')?.classList.contains('is-ready'),
      };
    });
    const uniqueBanners = [...new Set(banners.map((b) => b.url))];
    const transfer = banners.reduce((s, b) => s + (b.encoded || 0), 0);
    const row = {
      run: i,
      navMs,
      lcpMs: data.lcpLast?.startTime ?? null,
      lcp: data.lcpLast,
      cls: data.cls,
      bannerRequestCount: uniqueBanners.length,
      bannerTransferBytes: transfer,
      slidesWithSrc: data.slidesWithSrc,
      active: data.active,
      ready: data.ready,
      valid: data.lcpLast != null,
    };
    runs.push(row);
    console.log(JSON.stringify(row));
    await page.screenshot({ path: path.join(outDir, `po-run${i}.png`), fullPage: false });
    await browser.close();
  }
  const valid = runs.filter((r) => r.valid);
  const summary = {
    phase,
    method: {
      viewport: '390x844',
      isMobile: false,
      deviceScaleFactor: 'default',
      throttling: 'none-lab',
      observer: 'LCP+CLS before navigation',
      noScroll: true,
    },
    lighthouseMobileNote:
      'LH --form-factor=mobile reports NO_LCP under Chrome mobile emulation; treated as invalid for LCP comparison',
    runs,
    validRunCount: valid.length,
    invalidRunCount: runs.length - valid.length,
    medians: {
      lcp_ms: median(valid.map((r) => r.lcpMs)),
      cls: median(valid.map((r) => r.cls)),
      bannerRequestCount: median(valid.map((r) => r.bannerRequestCount)),
      bannerTransferBytes: median(valid.map((r) => r.bannerTransferBytes)),
      slidesWithSrc: median(valid.map((r) => r.slidesWithSrc)),
    },
  };
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log('MEDIAN', JSON.stringify(summary.medians));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
