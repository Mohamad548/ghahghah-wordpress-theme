/**
 * Agency cold vs warm (cache-correct) — one shared-context warm pair.
 * Usage: node scripts/agency-font-warm-pair.cjs [outDir]
 *
 * cold: fresh context, cacheDisabled=true, single navigation
 * warm: fresh context, cacheDisabled=false for BOTH navigations in the same context;
 *       records fromDiskCache / fromPrefetchCache / 304 on each font response.
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const outDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/home-lcp/tooling-warm-pair',
);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const url =
  process.env.AGENCY_URL ||
  'http://localhost:8898/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d9%86%d9%85%d8%a7%db%8c%d9%86%d8%af%da%af%db%8c/';

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

async function collectFonts(page, client) {
  const fonts = [];
  const onResp = (ev) => {
    const u = ev.response?.url || '';
    if (!/yekan|\.woff2?/i.test(u)) return;
    fonts.push({
      url: u,
      status: ev.response.status,
      mimeType: ev.response.mimeType,
      fromDiskCache: !!ev.response.fromDiskCache,
      fromPrefetchCache: !!ev.response.fromPrefetchCache,
      fromServiceWorker: !!ev.response.fromServiceWorker,
    });
  };
  client.on('Network.responseReceived', onResp);
  await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });
  await page.evaluate(() => document.fonts.ready.catch(() => {}));
  await new Promise((r) => setTimeout(r, 600));
  client.off('Network.responseReceived', onResp);
  return fonts;
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  const report = {
    generatedAt: new Date().toISOString(),
    url,
    method: {
      cold: 'fresh BrowserContext, Network.setCacheDisabled(true), one navigation',
      warm:
        'fresh BrowserContext, cacheEnabled for nav1+nav2 on same Page/context; record cache flags',
    },
  };

  try {
    // --- cold ---
    {
      const ctx = await browser.createBrowserContext();
      const page = await ctx.newPage();
      const client = await page.createCDPSession();
      await client.send('Network.enable');
      await client.send('Network.setCacheDisabled', { cacheDisabled: true });
      await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
      report.cold = {
        cacheDisabled: true,
        fonts: await collectFonts(page, client),
      };
      await page.close();
      await ctx.close();
    }

    // --- warm pair (shared context, cache ON both) ---
    {
      const ctx = await browser.createBrowserContext();
      const page = await ctx.newPage();
      const client = await page.createCDPSession();
      await client.send('Network.enable');
      await client.send('Network.setCacheDisabled', { cacheDisabled: false });
      await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
      const nav1 = await collectFonts(page, client);
      const nav2 = await collectFonts(page, client);
      report.warm = {
        cacheDisabled: false,
        sharedContext: true,
        nav1: { fonts: nav1 },
        nav2: { fonts: nav2 },
        nav2FromDiskOrPrefetch: nav2.filter((f) => f.fromDiskCache || f.fromPrefetchCache || f.status === 304)
          .length,
      };
      await page.close();
      await ctx.close();
    }
  } finally {
    await browser.close();
  }

  const out = path.join(outDir, 'agency-warm-pair.json');
  fs.writeFileSync(out, JSON.stringify(report, null, 2));
  console.log('Wrote', out);
  console.log(
    'cold fonts',
    report.cold.fonts.length,
    'warm nav1',
    report.warm.nav1.fonts.length,
    'warm nav2',
    report.warm.nav2.fonts.length,
    'nav2 cached-ish',
    report.warm.nav2FromDiskOrPrefetch,
  );
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
