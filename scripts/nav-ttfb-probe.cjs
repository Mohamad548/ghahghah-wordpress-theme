/**
 * Limited TTFB decomposition for Home + slow pages on the same wp-env.
 * Compares: static file via Apache, minimal PHP, full WordPress front request.
 * Optionally enables SAVEQUERIES / timer for one WP request and records overhead.
 *
 * Usage: node scripts/nav-ttfb-probe.cjs [outDir]
 */
const fs = require('fs');
const path = require('path');
const http = require('http');
const { URL } = require('url');
const { spawnSync } = require('child_process');

const BASE = (process.env.HOME_URL || 'http://localhost:8898').replace(/\/$/, '');
const outDir = path.resolve(
  process.argv[2] || 'docs/audits/artifacts/nav-perf/ttfb-probe',
);
const REPEATS = Math.max(3, parseInt(process.env.TTFB_REPEATS || '5', 10));
fs.mkdirSync(outDir, { recursive: true });

function median(nums) {
  const a = [...nums].filter((n) => typeof n === 'number' && !Number.isNaN(n)).sort((x, y) => x - y);
  if (!a.length) return null;
  const m = Math.floor(a.length / 2);
  return a.length % 2 ? a[m] : (a[m - 1] + a[m]) / 2;
}

function timedGet(url, headers = {}) {
  return new Promise((resolve, reject) => {
    const u = new URL(url);
    const start = process.hrtime.bigint();
    let ttfbNs = null;
    const req = http.request(
      {
        hostname: u.hostname,
        port: u.port || 80,
        path: u.pathname + u.search,
        method: 'GET',
        headers: { 'User-Agent': 'ghahghah-ttfb-probe/1.0', Connection: 'close', ...headers },
      },
      (res) => {
        ttfbNs = process.hrtime.bigint() - start;
        let bytes = 0;
        res.on('data', (c) => {
          bytes += c.length;
        });
        res.on('end', () => {
          const totalNs = process.hrtime.bigint() - start;
          resolve({
            url,
            status: res.statusCode,
            ttfbMs: Number(ttfbNs) / 1e6,
            totalMs: Number(totalNs) / 1e6,
            bytes,
            server: res.headers.server || null,
            xPoweredBy: res.headers['x-powered-by'] || null,
          });
        });
      },
    );
    req.on('error', reject);
    req.setTimeout(120000, () => req.destroy(new Error('timeout')));
    req.end();
  });
}

async function repeats(label, fn) {
  const rows = [];
  for (let i = 1; i <= REPEATS; i++) {
    try {
      const row = await fn(i);
      rows.push({ ok: true, ...row });
      console.log(label, i, row.ttfbMs?.toFixed?.(1) ?? row);
    } catch (e) {
      rows.push({ ok: false, error: String(e.message || e) });
      console.log(label, i, 'ERR', e.message || e);
    }
  }
  return {
    label,
    n: rows.length,
    ok: rows.filter((r) => r.ok).length,
    medianTtfbMs: median(rows.filter((r) => r.ok).map((r) => r.ttfbMs)),
    medianTotalMs: median(rows.filter((r) => r.ok).map((r) => r.totalMs)),
    rows,
  };
}

function wpEval(php) {
  // Write PHP to a temp file inside the worktree so wp-env can see it.
  const rel = 'docs/audits/artifacts/nav-perf/ttfb-probe/_probe-eval.php';
  const abs = path.resolve(rel);
  fs.mkdirSync(path.dirname(abs), { recursive: true });
  fs.writeFileSync(abs, php);
  const r = spawnSync(
    './node_modules/.bin/wp-env',
    ['run', 'cli', 'wp', 'eval-file', 'wp-content/themes/ghahghah-theme/../../../' + rel.replace(/\\/g, '/')],
    { encoding: 'utf8', shell: true, cwd: path.resolve('.'), timeout: 120000 },
  );
  // Fallback: copy into uploads and eval from there
  if (r.status !== 0) {
    const alt = spawnSync(
      './node_modules/.bin/wp-env',
      [
        'run',
        'cli',
        'bash',
        '-lc',
        `cat > /tmp/ttfb-probe.php <<'PHP'\n${php.replace(/\\/g, '\\\\').replace(/`/g, '\\`')}\nPHP\nwp eval-file /tmp/ttfb-probe.php`,
      ],
      { encoding: 'utf8', shell: true, cwd: path.resolve('.'), timeout: 120000 },
    );
    return { status: alt.status, stdout: alt.stdout || '', stderr: alt.stderr || '' };
  }
  return { status: r.status, stdout: r.stdout || '', stderr: r.stderr || '' };
}

(async () => {
  // Discover slower public pages via quick HEAD-ish GET of candidates.
  const candidates = [
    `${BASE}/`,
    `${BASE}/products/`,
    `${BASE}/articles/`,
    `${BASE}/wholesale/`,
    `${BASE}/agency/`,
    `${BASE}/factory/`,
    `${BASE}/contact/`,
  ];
  const scout = [];
  for (const url of candidates) {
    const samples = [];
    for (let i = 0; i < 3; i++) {
      samples.push(await timedGet(url, { 'Cache-Control': 'no-cache' }));
    }
    scout.push({
      url,
      medianTtfbMs: median(samples.map((s) => s.ttfbMs)),
      samples,
    });
  }
  scout.sort((a, b) => (b.medianTtfbMs || 0) - (a.medianTtfbMs || 0));
  const home = scout.find((s) => s.url === `${BASE}/`) || scout[0];
  const slow = scout.filter((s) => s.url !== home.url).slice(0, 2);

  // Static file: use a known theme asset under Apache.
  const staticUrl = `${BASE}/wp-content/themes/ghahghah-theme/style.css`;

  // Minimal PHP via wp-env: create a throwaway file in uploads if possible.
  const miniPhpSetup = spawnSync(
    './node_modules/.bin/wp-env',
    [
      'run',
      'cli',
      'bash',
      '-lc',
      `mkdir -p /var/www/html/wp-content/uploads/ttfb-probe && printf '%s' '<?php header("Content-Type: text/plain"); echo "ok";' > /var/www/html/wp-content/uploads/ttfb-probe/mini.php && ls -la /var/www/html/wp-content/uploads/ttfb-probe/mini.php`,
    ],
    { encoding: 'utf8', shell: true, cwd: path.resolve('.'), timeout: 60000 },
  );

  const miniUrl = `${BASE}/wp-content/uploads/ttfb-probe/mini.php`;

  const results = {
    at: new Date().toISOString(),
    base: BASE,
    repeats: REPEATS,
    scout,
    targets: { home: home.url, slow: slow.map((s) => s.url) },
    static: await repeats('static', () => timedGet(staticUrl, { 'Cache-Control': 'no-cache' })),
    minimalPhp: await repeats('minimal-php', () => timedGet(miniUrl, { 'Cache-Control': 'no-cache' })),
    fullWp: {},
    profiling: {},
    attribution: null,
    miniPhpSetup: { status: miniPhpSetup.status, stderr: (miniPhpSetup.stderr || '').slice(-500) },
  };

  for (const url of [home.url, ...slow.map((s) => s.url)]) {
    results.fullWp[url] = await repeats('full-wp:' + url.replace(BASE, ''), () =>
      timedGet(url, { 'Cache-Control': 'no-cache', Pragma: 'no-cache' }),
    );
  }

  // Profile one Home request inside WP (timer + queries) and one without — measure HTTP delta.
  const profilePhp = `<?php
if (!defined('SAVEQUERIES')) define('SAVEQUERIES', true);
$GLOBALS['wpdb']->queries = array();
timer_start();
// Simulate front bootstrap cost markers after WP already loaded (eval-file context).
$boot = array(
  'php_sapi' => PHP_SAPI,
  'absppath_exists' => defined('ABSPATH'),
  'active_plugins' => (array) get_option('active_plugins', array()),
  'theme' => get_stylesheet(),
);
$q_before = is_array($GLOBALS['wpdb']->queries) ? count($GLOBALS['wpdb']->queries) : 0;
// Cheap probes that still hit common theme option reads.
$locs = get_nav_menu_locations();
$redir = get_option('ghahghah_url_redirects', array());
home_url('/');
$elapsed = timer_stop(false, 4);
$q_after = is_array($GLOBALS['wpdb']->queries) ? count($GLOBALS['wpdb']->queries) : 0;
$queries = array_slice($GLOBALS['wpdb']->queries ?: array(), $q_before, 40);
$sum_q = 0.0;
foreach ($queries as $q) { $sum_q += isset($q[1]) ? (float)$q[1] : 0; }
echo wp_json_encode(array(
  'elapsed_s' => (float)$elapsed,
  'query_count_delta' => $q_after - $q_before,
  'query_time_s' => $sum_q,
  'menu_locations' => $locs,
  'redirect_map_count' => is_array($redir) ? count($redir) : 0,
  'boot' => $boot,
), JSON_UNESCAPED_SLASHES);
`;

  const profiled = wpEval(profilePhp);
  let profileJson = null;
  try {
    const m = (profiled.stdout || '').match(/\{[\s\S]*\}\s*$/);
    profileJson = m ? JSON.parse(m[0]) : null;
  } catch (_) {}

  // HTTP with and without a custom header that theme could ignore — overhead of this probe itself.
  const httpBaseline = await repeats('http-home-baseline', () =>
    timedGet(home.url, { 'Cache-Control': 'no-cache' }),
  );
  results.profiling = {
    wpEvalStatus: profiled.status,
    wpEvalStderrTail: (profiled.stderr || '').slice(-800),
    inProcess: profileJson,
    httpBaseline,
    note: 'inProcess measures option/menu reads inside already-booted WP-CLI, not full Apache front controller cost. Compare static vs minimalPhp vs fullWp HTTP TTFB for env attribution.',
  };

  const staticMed = results.static.medianTtfbMs;
  const miniMed = results.minimalPhp.medianTtfbMs;
  const homeMed = results.fullWp[home.url]?.medianTtfbMs;
  let attribution = {
    status: 'UNRESOLVED',
    evidence: [],
  };
  if (staticMed != null && miniMed != null && homeMed != null) {
    attribution.evidence.push({
      staticMs: staticMed,
      minimalPhpMs: miniMed,
      fullHomeMs: homeMed,
      phpOverStatic: miniMed - staticMed,
      wpOverMinimalPhp: homeMed - miniMed,
    });
    if (staticMed > 500 && miniMed - staticMed < 100) {
      attribution.status = 'LIKELY_DOCKER_OR_FS_OR_APACHE';
      attribution.reason =
        'Static file TTFB already high; minimal PHP adds little → bottleneck before WordPress app code.';
    } else if (miniMed - staticMed > 200 && homeMed - miniMed < 300) {
      attribution.status = 'LIKELY_PHP_RUNTIME';
      attribution.reason = 'Minimal PHP much slower than static; full WP close to minimal PHP.';
    } else if (homeMed - miniMed > 500) {
      attribution.status = 'LIKELY_WORDPRESS_THEME_OR_PLUGINS_OR_DB';
      attribution.reason =
        'Full WP TTFB substantially above minimal PHP on same stack → app/bootstrap/DB/plugins/theme.';
    } else {
      attribution.status = 'UNRESOLVED';
      attribution.reason = 'Gaps between layers not decisive enough for a single cause.';
    }
  }
  results.attribution = attribution;

  fs.writeFileSync(path.join(outDir, 'ttfb-probe.json'), JSON.stringify(results, null, 2));
  console.log(JSON.stringify({ attribution, home: homeMed, static: staticMed, mini: miniMed }, null, 2));
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
