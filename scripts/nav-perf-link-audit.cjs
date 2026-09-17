/**
 * Extract Products-related hrefs from real DOM surfaces and probe Document redirects.
 * Usage: node scripts/nav-perf-link-audit.cjs [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const http = require('http');
const https = require('https');
const { URL } = require('url');
const { spawnSync } = require('child_process');

const BASE = process.env.HOME_URL || 'http://localhost:8898';
const outDir = path.resolve(process.argv[2] || 'docs/audits/artifacts/nav-perf/links');
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

function fetchChain(startUrl, maxHops = 8) {
  return new Promise((resolve, reject) => {
    const chain = [];
    const go = (url, hopsLeft) => {
      const u = new URL(url);
      const lib = u.protocol === 'https:' ? https : http;
      const req = lib.request(
        url,
        {
          method: 'GET',
          headers: { 'User-Agent': 'ghahghah-nav-audit/1.0', Accept: 'text/html' },
        },
        (res) => {
          const loc = res.headers.location || null;
          chain.push({
            url,
            status: res.statusCode,
            location: loc,
            host: u.host,
            pathname: u.pathname,
            search: u.search,
          });
          res.resume();
          if (
            hopsLeft > 0 &&
            loc &&
            res.statusCode >= 300 &&
            res.statusCode < 400
          ) {
            const next = new URL(loc, url).toString();
            go(next, hopsLeft - 1);
          } else {
            resolve({ startUrl, finalUrl: url, finalStatus: res.statusCode, chain });
          }
        },
      );
      req.on('error', reject);
      req.setTimeout(30000, () => req.destroy(new Error('timeout')));
      req.end();
    };
    go(startUrl, maxHops);
  });
}

(async () => {
  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });

  const extract = async (viewport, label) => {
    const page = await browser.newPage();
    await page.setViewport(viewport);
    await page.goto(BASE + '/', { waitUntil: 'networkidle2', timeout: 90000 });
    const data = await page.evaluate(() => {
      const textOf = (el) => (el?.textContent || '').replace(/\s+/g, ' ').trim();
      const pick = (root, sel) =>
        [...(root || document).querySelectorAll(sel)].map((a) => ({
          href: a.getAttribute('href'),
          absolute: a.href,
          text: textOf(a),
          classes: a.className || '',
        }));

      const header = document.querySelector('header, .site-header, .ghahghah-header');
      const footer = document.querySelector('footer, .site-footer, .ghahghah-footer');
      const mobileNav = document.querySelector(
        '.mobile-bottom-nav, [data-ghahghah-mobile-nav], .ghahghah-mobile-nav, .ghahghah-bottom-nav',
      );
      const mobileMenu = document.querySelector(
        '.ghahghah-mobile-menu, .mobile-menu, #mobile-menu, [data-ghahghah-mobile-menu]',
      );

      const productish = (items) =>
        items.filter(
          (i) =>
            /product|محصول/i.test(i.text + ' ' + (i.href || '') + ' ' + (i.absolute || '')),
        );

      const allAnchors = pick(document, 'a[href]');
      return {
        headerProducts: productish(pick(header, 'a[href]')),
        footerProducts: productish(pick(footer, 'a[href]')),
        mobileBottomProducts: productish(pick(mobileNav, 'a[href]')),
        mobileMenuProducts: productish(pick(mobileMenu, 'a[href]')),
        allProductish: productish(allAnchors),
        surfaces: {
          hasHeader: !!header,
          hasFooter: !!footer,
          hasMobileNav: !!mobileNav,
          hasMobileMenu: !!mobileMenu,
          headerHtmlHint: header ? header.className : null,
          mobileNavHtmlHint: mobileNav ? mobileNav.className : null,
        },
      };
    });
    await page.close();
    return { label, viewport, ...data };
  };

  const desktop = await extract({ width: 1440, height: 900, isMobile: false }, 'desktop');
  const mobile = await extract(
    { width: 390, height: 844, isMobile: true, hasTouch: true },
    'mobile',
  );

  const candidateHrefs = [
    ...new Set(
      [...desktop.allProductish, ...mobile.allProductish]
        .map((i) => i.absolute || i.href)
        .filter(Boolean),
    ),
  ];

  // Always probe both historical forms
  const probes = [
    ...candidateHrefs,
    BASE + '/products/',
    BASE + '/' + encodeURIComponent('محصولات') + '/',
    'http://localhost:8888/products/',
  ].filter((v, i, a) => a.indexOf(v) === i);

  const redirectResults = [];
  for (const url of probes) {
    try {
      redirectResults.push(await fetchChain(url));
    } catch (e) {
      redirectResults.push({ startUrl: url, error: String(e.message || e) });
    }
  }

  // Compare final destinations for /products/ vs /محصولات/
  const ascii = redirectResults.find((r) => /\/products\/?$/.test(r.startUrl || ''));
  const persian = redirectResults.find((r) =>
    decodeURIComponent(r.startUrl || '').includes('محصولات'),
  );

  const summary = {
    base: BASE,
    at: new Date().toISOString(),
    desktop,
    mobile,
    uniqueProductHrefs: candidateHrefs,
    redirectResults,
    destinationCompare: {
      asciiFinal: ascii?.finalUrl || null,
      persianFinal: persian?.finalUrl || null,
      sameFinal:
        ascii && persian
          ? ascii.finalUrl === persian.finalUrl ||
            new URL(ascii.finalUrl).pathname === new URL(persian.finalUrl).pathname
          : null,
      asciiChain: ascii?.chain || null,
      persianChain: persian?.chain || null,
    },
  };

  fs.writeFileSync(path.join(outDir, 'link-audit.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify({
    uniqueProductHrefs: candidateHrefs,
    destinationCompare: summary.destinationCompare,
    desktopHeader: desktop.headerProducts,
    desktopFooter: desktop.footerProducts,
    mobileBottom: mobile.mobileBottomProducts,
    surfaces: { desktop: desktop.surfaces, mobile: mobile.surfaces },
  }, null, 2));

  await browser.close();
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
