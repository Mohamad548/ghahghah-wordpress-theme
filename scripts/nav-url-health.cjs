/**
 * Public URL health + redirect encoding/query checks (HTTP only).
 * Usage: node scripts/nav-url-health.cjs [outDir]
 */
const fs = require('fs');
const path = require('path');
const http = require('http');
const { URL } = require('url');

const BASE = (process.env.HOME_URL || 'http://localhost:8898').replace(/\/$/, '');
const outDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/url-migration/health',
);
const invPath = path.resolve(
  process.argv[3] || 'docs/audits/artifacts/nav-perf/sitewide/inventory/inventory.json',
);
fs.mkdirSync(outDir, { recursive: true });

function fetchOnce(url, maxHops = 8) {
  return new Promise((resolve, reject) => {
    const chain = [];
    const go = (u, hops) => {
      const parsed = new URL(u);
      const req = http.request(
        {
          hostname: parsed.hostname,
          port: parsed.port || 80,
          path: parsed.pathname + parsed.search,
          method: 'GET',
          headers: { 'User-Agent': 'ghahghah-url-health/1.0', Accept: 'text/html', Connection: 'close' },
        },
        (res) => {
          const loc = res.headers.location || null;
          chain.push({ url: u, status: res.statusCode, location: loc });
          res.resume();
          if (hops > 0 && loc && res.statusCode >= 300 && res.statusCode < 400) {
            go(new URL(loc, u).toString(), hops - 1);
          } else {
            resolve({ startUrl: url, finalUrl: u, finalStatus: res.statusCode, chain, hops: chain.length - 1 });
          }
        },
      );
      req.on('error', reject);
      req.setTimeout(60000, () => req.destroy(new Error('timeout')));
      req.end();
    };
    go(url, maxHops);
  });
}

function encVariants(persianPath) {
  // persianPath like /مقالات/
  const trimmed = persianPath.replace(/^\/|\/$/g, '');
  const lower = trimmed
    .split('/')
    .map((p) =>
      [...p]
        .map((ch) => {
          const c = ch.codePointAt(0);
          if (c < 128) return ch;
          return '%' + c.toString(16).toUpperCase().padStart(2, '0').replace(/(..)/g, (m) => {
            // utf-8 encode
            return null;
          });
        })
        .join(''),
    )
    .join('/');
  // proper UTF-8 percent encoding
  const utf8 = '/' + trimmed.split('/').map(encodeURIComponent).join('/') + '/';
  const utf8Lower = utf8.toLowerCase();
  const utf8Upper = '/' + trimmed
    .split('/')
    .map((seg) =>
      encodeURIComponent(seg).replace(/%[0-9a-f]{2}/gi, (m) => m.toUpperCase()),
    )
    .join('/') + '/';
  return [persianPath, utf8, utf8Lower, utf8Upper];
}

(async () => {
  const inv = fs.existsSync(invPath) ? JSON.parse(fs.readFileSync(invPath, 'utf8')) : null;
  const publicUrls = [];
  if (inv) {
    for (const d of inv.uniqueDestinations || []) {
      if (d.url && !/sample-page|hello-world|legacy-/i.test(d.url)) publicUrls.push(d.url);
    }
  } else {
    publicUrls.push(
      `${BASE}/`,
      `${BASE}/products/`,
      `${BASE}/articles/`,
      `${BASE}/wholesale/`,
      `${BASE}/agency/`,
      `${BASE}/factory/`,
      `${BASE}/contact/`,
      `${BASE}/faq/`,
      `${BASE}/privacy-policy/`,
    );
  }

  const redirectCases = [
    { from: '/مقالات/', expectPath: '/articles/' },
    { from: '/درخواست-خرید-عمده/', expectPath: '/wholesale/' },
    { from: '/درخواست-نمایندگی/', expectPath: '/agency/' },
    { from: '/تماس-با-ما/', expectPath: '/contact/' },
    { from: '/کارخانه/', expectPath: '/factory/' },
    { from: '/محصولات/', expectPath: '/products/' },
    { from: '/products-legacy/', expectPath: '/products/' },
    { from: '/legacy-all-products/', expectPath: '/products/' },
    { from: '/legacy-flavor-pizza/', expectPath: '/products/', expectQueryIncludes: 'gh_flavor=pizza' },
    { from: '/legacy-flavor-lemon/', expectPath: '/products/', expectQueryIncludes: 'gh_flavor=lemon' },
  ];

  const queryCases = [
    {
      from: '/محصولات/?gh_flavor=pizza&gh_sort=title&gh_q=test&paged=2',
      expectPath: '/products/',
      expectKeys: ['gh_flavor', 'gh_sort', 'gh_q', 'paged'],
    },
    {
      from: '/products-legacy/?gh_sort=price&gh_q=chip',
      expectPath: '/products/',
      expectKeys: ['gh_sort', 'gh_q'],
    },
  ];

  const coverage = [];
  for (const url of [...new Set(publicUrls)]) {
    try {
      const r = await fetchOnce(url);
      const ok = r.finalStatus >= 200 && r.finalStatus < 400 && r.hops <= 1;
      coverage.push({
        url,
        status: ok ? 'PASS' : 'FAIL',
        reason: ok ? 'ok' : `finalStatus=${r.finalStatus} hops=${r.hops}`,
        ...r,
      });
    } catch (e) {
      coverage.push({ url, status: 'FAIL', reason: String(e.message || e) });
    }
  }

  const redirects = [];
  for (const c of redirectCases) {
    const variants = encVariants(c.from);
    for (const v of variants) {
      const url = BASE + v + (v.includes('?') ? '' : '');
      // encVariants returns paths; rebuild carefully
      const full = BASE + (v.startsWith('/') ? v : '/' + v);
      try {
        const r = await fetchOnce(full);
        const final = new URL(r.finalUrl);
        const pathOk = (final.pathname.replace(/\/?$/, '/') === c.expectPath.replace(/\/?$/, '/'));
        const qOk = c.expectQueryIncludes ? final.search.includes(c.expectQueryIncludes) : true;
        const singleHop = r.hops === 1 || (r.hops === 0 && pathOk); // already at dest
        const ok = pathOk && qOk && r.finalStatus < 400 && r.hops <= 1;
        redirects.push({
          case: c.from,
          variant: v,
          full,
          status: ok ? 'PASS' : 'FAIL',
          reason: ok
            ? 'ok'
            : `pathOk=${pathOk} qOk=${qOk} hops=${r.hops} final=${r.finalUrl} http=${r.finalStatus}`,
          ...r,
        });
      } catch (e) {
        redirects.push({ case: c.from, variant: v, status: 'FAIL', reason: String(e.message || e) });
      }
    }
  }

  const queries = [];
  for (const c of queryCases) {
    const full = BASE + c.from;
    try {
      const r = await fetchOnce(full);
      const final = new URL(r.finalUrl);
      const pathOk = final.pathname.replace(/\/?$/, '/') === c.expectPath.replace(/\/?$/, '/');
      const missing = c.expectKeys.filter((k) => !final.searchParams.has(k));
      const ok = pathOk && missing.length === 0 && r.hops <= 1 && r.finalStatus < 400;
      queries.push({
        ...c,
        full,
        status: ok ? 'PASS' : 'FAIL',
        reason: ok ? 'ok' : `pathOk=${pathOk} missing=${missing.join(',')} hops=${r.hops} final=${r.finalUrl}`,
        finalUrl: r.finalUrl,
        hops: r.hops,
        chain: r.chain,
      });
    } catch (e) {
      queries.push({ ...c, status: 'FAIL', reason: String(e.message || e) });
    }
  }

  const summary = {
    at: new Date().toISOString(),
    base: BASE,
    counts: {
      coverage: coverage.length,
      coveragePass: coverage.filter((x) => x.status === 'PASS').length,
      coverageFail: coverage.filter((x) => x.status === 'FAIL').length,
      redirects: redirects.length,
      redirectsPass: redirects.filter((x) => x.status === 'PASS').length,
      redirectsFail: redirects.filter((x) => x.status === 'FAIL').length,
      queries: queries.length,
      queriesPass: queries.filter((x) => x.status === 'PASS').length,
      queriesFail: queries.filter((x) => x.status === 'FAIL').length,
    },
    coverage,
    redirects,
    queries,
  };
  fs.writeFileSync(path.join(outDir, 'health.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary.counts, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
