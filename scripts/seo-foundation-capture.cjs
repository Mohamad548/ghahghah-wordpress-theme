/**
 * Capture SEO-relevant HTML signals for key URLs.
 * Usage: node scripts/seo-foundation-capture.cjs [before|after] [outDir]
 */
const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');
const { URL } = require('url');

const phase = process.argv[2] || 'before';
const outDir = path.resolve(
  process.argv[3] || `docs/audits/artifacts/seo-foundation/${phase}`,
);
const BASE = process.env.HOME_URL || 'http://localhost:8898';

fs.mkdirSync(outDir, { recursive: true });

const PAGES = [
  { id: 'home', path: '/' },
  { id: 'products', path: '/products/' },
  { id: 'product', path: '/products/corn-pellet-cheese/' },
  { id: 'articles', path: '/articles/' },
  { id: 'article', path: '/snack-shapes-guide/' },
  { id: 'wholesale', path: '/wholesale/' },
  { id: 'agency', path: '/agency/' },
  { id: 'contact', path: '/contact/' },
  { id: 'search', path: '/?s=اسنک' },
  { id: 'missing-404', path: '/this-page-does-not-exist-seo-404/' },
];


function fetchUrl(url) {
  return new Promise((resolve, reject) => {
    const u = new URL(url);
    const lib = u.protocol === 'https:' ? https : http;
    const req = lib.request(
      url,
      { method: 'GET', headers: { 'User-Agent': 'ghahghah-seo-capture/1.0' } },
      (res) => {
        const chunks = [];
        res.on('data', (c) => chunks.push(c));
        res.on('end', () => {
          resolve({
            status: res.statusCode,
            headers: res.headers,
            body: Buffer.concat(chunks).toString('utf8'),
            finalUrl: url,
          });
        });
      },
    );
    req.on('error', reject);
    req.setTimeout(60000, () => {
      req.destroy(new Error('timeout'));
    });
    req.end();
  });
}

function allMatches(html, re) {
  const out = [];
  let m;
  const r = new RegExp(re.source, re.flags.includes('g') ? re.flags : re.flags + 'g');
  while ((m = r.exec(html))) {
    out.push(m[1] !== undefined ? m[1].trim() : m[0]);
  }
  return out;
}

function first(html, re) {
  const m = html.match(re);
  return m ? (m[1] || m[0]).trim() : null;
}

function parse(html, status, url) {
  const titles = allMatches(html, /<title[^>]*>([\s\S]*?)<\/title>/i);
  const h1s = allMatches(html, /<h1\b[^>]*>([\s\S]*?)<\/h1>/gi).map((t) =>
    t.replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim(),
  );
  const metaDesc = first(
    html,
    /<meta\s+[^>]*name=["']description["'][^>]*content=["']([^"']*)["'][^>]*>/i,
  ) || first(
    html,
    /<meta\s+[^>]*content=["']([^"']*)["'][^>]*name=["']description["'][^>]*>/i,
  );
  const robots = first(
    html,
    /<meta\s+[^>]*name=["']robots["'][^>]*content=["']([^"']*)["'][^>]*>/i,
  ) || first(
    html,
    /<meta\s+[^>]*content=["']([^"']*)["'][^>]*name=["']robots["'][^>]*>/i,
  );
  const canonical = first(
    html,
    /<link\s+[^>]*rel=["']canonical["'][^>]*href=["']([^"']+)["'][^>]*>/i,
  ) || first(
    html,
    /<link\s+[^>]*href=["']([^"']+)["'][^>]*rel=["']canonical["'][^>]*>/i,
  );
  const og = {};
  for (const prop of ['title', 'description', 'url', 'type', 'image', 'locale', 'site_name']) {
    const vals = allMatches(
      html,
      new RegExp(
        `<meta\\s+[^>]*(?:property|name)=["']og:${prop}["'][^>]*content=["']([^"']*)["'][^>]*>`,
        'gi',
      ),
    ).concat(
      allMatches(
        html,
        new RegExp(
          `<meta\\s+[^>]*content=["']([^"']*)["'][^>]*(?:property|name)=["']og:${prop}["'][^>]*>`,
          'gi',
        ),
      ),
    );
    if (vals.length) og[prop] = vals;
  }
  const twitter = {};
  for (const name of ['card', 'title', 'description', 'image']) {
    const vals = allMatches(
      html,
      new RegExp(
        `<meta\\s+[^>]*name=["']twitter:${name}["'][^>]*content=["']([^"']*)["'][^>]*>`,
        'gi',
      ),
    );
    if (vals.length) twitter[name] = vals;
  }
  const jsonLdRaw = allMatches(
    html,
    /<script[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi,
  );
  const jsonLd = [];
  const jsonLdErrors = [];
  for (const raw of jsonLdRaw) {
    try {
      const parsed = JSON.parse(raw);
      jsonLd.push(parsed);
    } catch (e) {
      jsonLdErrors.push(String(e.message || e));
    }
  }
  const types = [];
  const walk = (node) => {
    if (!node || typeof node !== 'object') return;
    if (Array.isArray(node)) {
      node.forEach(walk);
      return;
    }
    if (node['@type']) {
      const t = node['@type'];
      if (Array.isArray(t)) types.push(...t);
      else types.push(t);
    }
    if (node['@graph']) walk(node['@graph']);
  };
  jsonLd.forEach(walk);

  return {
    url,
    status,
    title: titles[0] || null,
    titleCount: titles.length,
    h1: h1s,
    h1Count: h1s.length,
    metaDescription: metaDesc,
    robots,
    canonical,
    og,
    twitter,
    jsonLdBlockCount: jsonLd.length,
    jsonLdTypes: types,
    jsonLdErrors,
    jsonLd,
  };
}

(async () => {
  // Resolve product URL from homepage link if needed
  try {
    const home = await fetchUrl(BASE + '/');
    const m = home.body.match(/href="(http:\/\/localhost:8898\/products\/[^"]+)"/);
    if (m) {
      const prod = PAGES.find((p) => p.id === 'product');
      if (prod) prod.path = m[1].replace(BASE, '');
    }
  } catch {
    /* keep default */
  }

  const rows = [];
  for (const page of PAGES) {
    const url = page.path.startsWith('http') ? page.path : BASE + page.path;
    try {
      const res = await fetchUrl(url);
      const parsed = parse(res.body, res.status, url);
      parsed.id = page.id;
      rows.push(parsed);
      fs.writeFileSync(path.join(outDir, `${page.id}.html`), res.body);
      console.log(JSON.stringify({ id: page.id, status: parsed.status, h1: parsed.h1, title: parsed.title }));
    } catch (e) {
      rows.push({ id: page.id, url, error: String(e.message || e) });
      console.error(page.id, e);
    }
  }

  const summary = {
    phase,
    capturedAt: new Date().toISOString(),
    base: BASE,
    pages: rows,
  };
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log('WROTE', path.join(outDir, 'summary.json'));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
