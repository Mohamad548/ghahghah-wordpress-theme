/**
 * Sitewide public URL + internal-link inventory for nav performance.
 * Uses WP REST + DOM crawl. Does not submit forms. Skips search/filter combos.
 *
 * Usage: node scripts/nav-sitewide-inventory.cjs [outDir]
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const http = require('http');
const https = require('https');
const { URL } = require('url');
const { spawnSync } = require('child_process');

const BASE = (process.env.HOME_URL || 'http://localhost:8898').replace(/\/$/, '');
const outDir = path.resolve(process.argv[2] || 'docs/audits/artifacts/nav-perf/sitewide/inventory');
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

function fetchJson(url) {
  return new Promise((resolve, reject) => {
    const u = new URL(url);
    const lib = u.protocol === 'https:' ? https : http;
    const req = lib.get(
      url,
      { headers: { Accept: 'application/json', 'User-Agent': 'ghahghah-nav-inventory/1.0' } },
      (res) => {
        let body = '';
        res.setEncoding('utf8');
        res.on('data', (c) => (body += c));
        res.on('end', () => {
          try {
            resolve({ status: res.statusCode, json: JSON.parse(body || 'null'), headers: res.headers });
          } catch (e) {
            reject(new Error(`JSON parse ${url}: ${e.message}`));
          }
        });
      },
    );
    req.on('error', reject);
    req.setTimeout(60000, () => req.destroy(new Error('timeout')));
  });
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
          headers: { 'User-Agent': 'ghahghah-nav-inventory/1.0', Accept: 'text/html' },
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
          if (hopsLeft > 0 && loc && res.statusCode >= 300 && res.statusCode < 400) {
            go(new URL(loc, url).toString(), hopsLeft - 1);
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

function normPath(href) {
  try {
    const u = new URL(href, BASE);
    if (u.origin !== new URL(BASE).origin) return null;
    // Drop pure admin/rest/uploads
    if (u.pathname.startsWith('/wp-admin') || u.pathname.startsWith('/wp-json')) return null;
    if (u.pathname.startsWith('/wp-content/uploads')) return null;
    // Collapse filter/search/pagination variants to base path for destination set
    if (u.searchParams.has('s')) return null; // search — exclude from measure destinations
    const paged = u.searchParams.get('paged') || u.pathname.match(/\/page\/(\d+)/);
    const pathname = u.pathname === '/' ? '/' : u.pathname.replace(/\/?$/, '/');
    return {
      absolute: u.origin + pathname,
      pathname,
      search: u.search,
      hasPaged: !!paged,
      hasFlavor: u.searchParams.has('gh_flavor'),
      flavor: u.searchParams.get('gh_flavor'),
    };
  } catch {
    return null;
  }
}

async function restCollection(route) {
  const items = [];
  let page = 1;
  for (;;) {
    const url = `${BASE}/wp-json/wp/v2/${route}?per_page=100&page=${page}&status=publish&_fields=id,link,slug,title,type,template`;
    const { status, json, headers } = await fetchJson(url);
    if (status === 404) break;
    if (status >= 400) throw new Error(`${route} ${status}`);
    if (!Array.isArray(json) || !json.length) break;
    items.push(...json);
    const totalPages = parseInt(headers['x-wp-totalpages'] || '1', 10);
    if (page >= totalPages) break;
    page += 1;
  }
  return items;
}

(async () => {
  const [pages, posts, products] = await Promise.all([
    restCollection('pages'),
    restCollection('posts'),
    restCollection('ghahghah-products').catch(() => restCollection('ghahghah_product').catch(() => [])),
  ]);

  // CPT archive
  const archiveCandidates = [`${BASE}/products/`];
  const wpDestinations = [];
  for (const p of pages) {
    wpDestinations.push({
      source: 'wp-rest',
      type: 'page',
      id: p.id,
      slug: p.slug,
      title: p.title?.rendered || '',
      url: p.link,
    });
  }
  for (const p of posts) {
    wpDestinations.push({
      source: 'wp-rest',
      type: 'post',
      id: p.id,
      slug: p.slug,
      title: p.title?.rendered || '',
      url: p.link,
    });
  }
  for (const p of products) {
    wpDestinations.push({
      source: 'wp-rest',
      type: 'ghahghah_product',
      id: p.id,
      slug: p.slug,
      title: p.title?.rendered || '',
      url: p.link,
    });
  }
  wpDestinations.push({
    source: 'cpt-archive',
    type: 'archive',
    id: null,
    slug: 'products',
    title: 'Products archive',
    url: `${BASE}/products/`,
  });
  wpDestinations.push({
    source: 'home',
    type: 'home',
    id: null,
    slug: '',
    title: 'Home',
    url: `${BASE}/`,
  });

  const puppeteer = ensurePuppeteer();
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });

  const surfaces = {};
  const crawlSeeds = [`${BASE}/`];
  // Also open a few hubs for breadcrumbs/pagination/cards
  for (const hub of [`${BASE}/products/`, ...pages.slice(0, 8).map((p) => p.link)]) {
    if (hub && !crawlSeeds.includes(hub)) crawlSeeds.push(hub);
  }

  const linkRows = [];
  for (const seed of crawlSeeds) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1440, height: 900, isMobile: false });
    try {
      await page.goto(seed, { waitUntil: 'domcontentloaded', timeout: 90000 });
      await page.waitForSelector('body', { timeout: 15000 });
      // Open mobile menu if present (desktop crawl still captures footer/header)
      const extracted = await page.evaluate((seedUrl) => {
        const textOf = (el) => (el?.textContent || '').replace(/\s+/g, ' ').trim();
        const surfaces = {
          header: 'header, .site-header',
          footer: 'footer, .site-footer',
          bottom: '.gg-bottom-nav, [data-ghahghah-bottom-nav], nav.bottom-nav',
          mobileMenu: '.site-nav--mobile, .mobile-nav, #mobile-menu, .ghahghah-mobile-menu',
          breadcrumb: '.breadcrumb, .breadcrumbs, nav[aria-label*="breadcrumb" i]',
          pagination: '.pagination, .nav-links, .page-numbers',
          main: 'main, .site-main, #content',
        };
        const rows = [];
        for (const [name, sel] of Object.entries(surfaces)) {
          const root = document.querySelector(sel);
          if (!root) continue;
          for (const a of root.querySelectorAll('a[href]')) {
            rows.push({
              seed: seedUrl,
              surface: name,
              href: a.getAttribute('href'),
              absolute: a.href,
              text: textOf(a).slice(0, 120),
              classes: (a.className || '').toString().slice(0, 120),
            });
          }
        }
        // CTA-ish in main
        for (const a of document.querySelectorAll('main a[href], .ghahghah-featured a[href], .card a[href]')) {
          rows.push({
            seed: seedUrl,
            surface: 'cta-or-card',
            href: a.getAttribute('href'),
            absolute: a.href,
            text: textOf(a).slice(0, 120),
            classes: (a.className || '').toString().slice(0, 120),
          });
        }
        return rows;
      }, seed);
      linkRows.push(...extracted);

      // Mobile viewport pass on home only for bottom nav / drawer
      if (seed === `${BASE}/`) {
        await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
        await page.reload({ waitUntil: 'domcontentloaded', timeout: 90000 });
        const mobileToggle = await page.$('button[aria-controls], .menu-toggle, .site-header__toggle, [data-menu-toggle]');
        if (mobileToggle) {
          try {
            await mobileToggle.click();
            await new Promise((r) => setTimeout(r, 400));
          } catch (_) {}
        }
        const mobileRows = await page.evaluate((seedUrl) => {
          const textOf = (el) => (el?.textContent || '').replace(/\s+/g, ' ').trim();
          const roots = [
            ['bottom', document.querySelector('.gg-bottom-nav, [data-ghahghah-bottom-nav]')],
            ['mobileMenu', document.querySelector('.site-nav, .mobile-nav, #mobile-menu, .ghahghah-mobile-menu, .site-header')],
            ['footer', document.querySelector('footer, .site-footer')],
          ];
          const rows = [];
          for (const [name, root] of roots) {
            if (!root) continue;
            for (const a of root.querySelectorAll('a[href]')) {
              rows.push({
                seed: seedUrl,
                surface: name + '-mobile',
                href: a.getAttribute('href'),
                absolute: a.href,
                text: textOf(a).slice(0, 120),
                classes: (a.className || '').toString().slice(0, 120),
              });
            }
          }
          return rows;
        }, seed);
        linkRows.push(...mobileRows);
      }
    } catch (e) {
      linkRows.push({ seed, surface: 'ERROR', href: null, absolute: null, text: String(e.message), classes: '' });
    } finally {
      await page.close();
    }
  }
  await browser.close();

  // Unique measure destinations: public content URLs without search; flavor filters collapsed to archive+note
  const byPath = new Map();
  const register = (url, meta) => {
    const n = normPath(url);
    if (!n) return;
    // Skip sample-page / hello-world unless linked from public nav — still list in wpDestinations
    const key = n.absolute.split('?')[0];
    if (!byPath.has(key)) {
      byPath.set(key, {
        url: key,
        pathname: n.pathname,
        linkedFrom: [],
        flavorsSeen: [],
        pagedSeen: false,
        meta: [],
      });
    }
    const row = byPath.get(key);
    if (meta?.surface) row.linkedFrom.push(meta);
    if (n.hasFlavor && n.flavor && !row.flavorsSeen.includes(n.flavor)) row.flavorsSeen.push(n.flavor);
    if (n.hasPaged) row.pagedSeen = true;
    if (meta?.wp) row.meta.push(meta.wp);
  };

  for (const d of wpDestinations) register(d.url, { wp: d });
  for (const a of archiveCandidates) register(a, { surface: 'archive-seed', seed: BASE });
  for (const l of linkRows) {
    if (!l.absolute) continue;
    register(l.absolute, { surface: l.surface, seed: l.seed, text: l.text });
  }

  // Document chains for unique destinations (no form posts)
  const chains = [];
  for (const url of byPath.keys()) {
    try {
      chains.push(await fetchChain(url));
    } catch (e) {
      chains.push({ startUrl: url, error: String(e.message), chain: [] });
    }
  }

  const uniqueDestinations = [...byPath.values()].map((d) => {
    const chain = chains.find((c) => c.startUrl === d.url);
    return {
      ...d,
      linkedFrom: d.linkedFrom,
      document: chain || null,
      redirectCount: chain?.chain ? Math.max(0, chain.chain.length - 1) : null,
      finalUrl: chain?.finalUrl || null,
      finalStatus: chain?.finalStatus || null,
    };
  });

  // Nav click candidates: unique hrefs from header/footer/bottom/mobile (parent-level paths)
  const navClickHrefs = [];
  const seenNav = new Set();
  for (const l of linkRows) {
    if (!l.absolute) continue;
    if (!/(header|footer|bottom|mobileMenu)/i.test(l.surface || '')) continue;
    const n = normPath(l.absolute);
    if (!n || n.hasFlavor) continue; // measure archive once; flavors noted separately
    if (seenNav.has(n.absolute)) continue;
    seenNav.add(n.absolute);
    navClickHrefs.push({
      url: n.absolute,
      text: l.text,
      surfaces: [...new Set(linkRows.filter((x) => normPath(x.absolute)?.absolute === n.absolute).map((x) => x.surface))],
    });
  }

  const summary = {
    at: new Date().toISOString(),
    base: BASE,
    auth: 'logged-out',
    counts: {
      pages: pages.length,
      posts: posts.length,
      products: products.length,
      linkRows: linkRows.length,
      uniqueDestinations: uniqueDestinations.length,
      navClickHrefs: navClickHrefs.length,
    },
    wpDestinations,
    uniqueDestinations,
    navClickHrefs,
    linkRows,
    notes: [
      'Search URLs (?s=) excluded from measure destinations.',
      'gh_flavor filter links collapsed to /products/ destination; flavors listed on that row.',
      'Pagination noted via pagedSeen; only base archive/list URL is a measure destination.',
      'Forms were not submitted.',
    ],
  };

  fs.writeFileSync(path.join(outDir, 'inventory.json'), JSON.stringify(summary, null, 2));
  fs.writeFileSync(
    path.join(outDir, 'unique-destinations.json'),
    JSON.stringify(uniqueDestinations, null, 2),
  );
  fs.writeFileSync(path.join(outDir, 'nav-click-hrefs.json'), JSON.stringify(navClickHrefs, null, 2));
  console.log(JSON.stringify(summary.counts, null, 2));
  console.log('Wrote', outDir);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
