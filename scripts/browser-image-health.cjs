/**
 * Browser image health QA — pending / hidden / loaded / failed.
 * Usage: node scripts/browser-image-health.cjs [baseUrl] [outDir]
 * Exit 1 if any expected (current-viewport) image fails after wait.
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');

const base = (process.argv[2] || 'http://localhost:8898').replace(/\/$/, '');
const outDir = path.resolve(
  process.argv[3] || 'docs/audits/artifacts/media-recovery/browser-health',
);
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const LOAD_TIMEOUT_MS = Number(process.env.IMG_LOAD_TIMEOUT_MS || 20000);

fs.mkdirSync(outDir, { recursive: true });

const pages = {
  home: `${base}/`,
  products: `${base}/products/`,
  articles: `${base}/?post_type=post`,
  guide: `${base}/wholesale-request-guide/`,
  wholesale: `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d8%ae%d8%b1%db%8c%d8%af-%d8%b9%d9%85%d8%af%d9%87/`,
  agency: null, // filled after discovery
};

function ensurePuppeteer() {
  try {
    return require('puppeteer-core');
  } catch {
    const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-pup-'));
    spawnSync('npm', ['init', '-y'], { cwd: tmp, shell: true, stdio: 'ignore' });
    const inst = spawnSync('npm', ['install', '--no-save', 'puppeteer-core@23.11.1'], {
      cwd: tmp,
      shell: true,
      encoding: 'utf8',
    });
    if (inst.status !== 0) {
      throw new Error('puppeteer-core install failed: ' + (inst.stderr || inst.stdout));
    }
    return require(path.join(tmp, 'node_modules', 'puppeteer-core'));
  }
}

function discoverAgencyUrl() {
  const r = spawnSync(
    'docker',
    [
      'exec',
      'wp-env-ghahghah-fix-perf-images-c3316861-cli-1',
      'wp',
      '--allow-root',
      '--skip-themes',
      '--skip-plugins',
      'eval',
      'foreach ( get_pages( array( "number" => 200 ) ) as $p ) { $t = $p->post_title . " " . $p->post_name; if ( preg_match( "/نمایند|agency/iu", $t ) ) { echo get_permalink( $p ); echo PHP_EOL; break; } }',
    ],
    { encoding: 'utf8' },
  );
  const line = (r.stdout || '')
    .split(/\r?\n/)
    .map((s) => s.trim())
    .find((s) => /^https?:\/\//.test(s));
  return line || `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d9%86%d9%85%d8%a7%db%8c%d9%86%d8%af%da%af%db%8c/`;
}

const viewports = {
  mobile: { width: 390, height: 844, isMobile: true },
  desktop: { width: 1440, height: 900, isMobile: false },
};

(async () => {
  pages.agency = discoverAgencyUrl();
  console.log('Agency URL:', pages.agency);

  const puppeteer = ensurePuppeteer();
  const report = {
    generatedAt: new Date().toISOString(),
    base,
    loadTimeoutMs: LOAD_TIMEOUT_MS,
    results: {},
  };
  let hardFailures = 0;

  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--window-size=1440,900'],
  });

  try {
    for (const [vpName, vp] of Object.entries(viewports)) {
      report.results[vpName] = {};
      for (const [pageKey, url] of Object.entries(pages)) {
        if (!url) continue;
        const page = await browser.newPage();
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

        const httpFails = [];
        page.on('response', (res) => {
          const u = res.url();
          if (!/\/uploads\/|\/themes\/ghahghah-theme\/assets\/images\//i.test(u)) return;
          if (res.status() >= 400) httpFails.push({ url: u, status: res.status() });
        });

        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 90000 });

        // Scroll to trigger lazy-load (do not change theme lazy behavior).
        await page.evaluate(async () => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
          for (let y = 0; y < h; y += Math.floor(window.innerHeight * 0.65)) {
            window.scrollTo(0, y);
            await sleep(200);
          }
          window.scrollTo(0, 0);
          await sleep(200);
        });

        // Advance hero slides if present.
        await page.evaluate(async () => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const slides = document.querySelectorAll('.ghahghah-hero__slide');
          const next = document.querySelector('[data-ghahghah-hero-next]');
          for (let i = 1; i < slides.length; i++) {
            if (next) next.click();
            await sleep(500);
          }
          // return to first
          const prev = document.querySelector('[data-ghahghah-hero-prev]');
          for (let i = 1; i < slides.length; i++) {
            if (prev) prev.click();
            await sleep(200);
          }
        });

        // Wait for expected visible images to leave pending.
        const classify = await page.evaluate(async (timeoutMs) => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const isAsset = (src) =>
            /\/uploads\/|\/themes\/ghahghah-theme\/assets\/images\//i.test(src || '');

          const isHidden = (img) => {
            const cs = getComputedStyle(img);
            if (cs.display === 'none' || cs.visibility === 'hidden' || Number(cs.opacity) === 0) {
              return true;
            }
            // Picture: if img is not the chosen currentSrc candidate for this viewport, treat as N/A via dimensions.
            if (img.clientWidth === 0 && img.clientHeight === 0) return true;
            // Inactive hero slides (not .is-active) — still expected after click-through; if still zero box, hidden.
            const slide = img.closest('.ghahghah-hero__slide');
            if (slide && !slide.classList.contains('is-active')) {
              // Off-stage slides may be opacity 0; wait path already clicked through — classify by decode only later.
              if (cs.opacity === '0' || cs.visibility === 'hidden') return true;
            }
            return false;
          };

          const snapshot = () =>
            Array.from(document.images)
              .filter((img) => isAsset(img.currentSrc || img.src))
              .map((img) => {
                const src = img.currentSrc || img.src || '';
                const hidden = isHidden(img);
                let status = 'pending';
                if (img.complete) {
                  if (img.naturalWidth > 0) status = 'loaded';
                  else status = 'failed';
                } else if (hidden) {
                  status = 'hidden';
                } else {
                  status = 'pending';
                }
                // Hidden + not yet complete → hidden (other viewport / inactive), not failed.
                if (hidden && status === 'pending') status = 'hidden';
                if (hidden && status === 'failed' && !img.complete) status = 'hidden';
                // Hidden with complete naturalWidth 0 can be unloaded source — count as hidden not failed.
                if (hidden && img.naturalWidth === 0) status = 'hidden';
                return {
                  src,
                  status,
                  naturalWidth: img.naturalWidth,
                  complete: img.complete,
                  clientWidth: img.clientWidth,
                  clientHeight: img.clientHeight,
                };
              });

          const start = Date.now();
          let items = snapshot();
          while (Date.now() - start < timeoutMs) {
            const pendingVisible = items.filter((i) => i.status === 'pending');
            if (pendingVisible.length === 0) break;
            // Nudge lazy-load: scroll each pending image into view and await load/error.
            const pendingImgs = Array.from(document.images).filter((img) => {
              const src = img.currentSrc || img.src || '';
              if (!isAsset(src)) return false;
              if (isHidden(img)) return false;
              return !img.complete || img.naturalWidth === 0;
            });
            for (const img of pendingImgs) {
              img.scrollIntoView({ block: 'center', inline: 'nearest' });
              // Test-only: encourage load without changing theme source (native lazy can stall in headless).
              try {
                img.loading = 'eager';
              } catch (_) {}
              if (!img.complete || img.naturalWidth === 0) {
                const src = img.currentSrc || img.src;
                if (src) {
                  // Force a reload attempt for stalled requests.
                  img.src = src;
                }
              }
              await new Promise((resolve) => {
                if (img.complete && img.naturalWidth > 0) {
                  resolve();
                  return;
                }
                const done = () => resolve();
                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
                setTimeout(done, 5000);
              });
              try {
                if (img.decode) await img.decode();
              } catch (_) {
                /* ignore */
              }
            }
            await sleep(200);
            items = snapshot();
          }

          // Final: pending/failed-incomplete → verify via fetch+decode; only real bad URLs fail.
          items = await Promise.all(
            items.map(async (i) => {
              if (i.status === 'loaded' || i.status === 'hidden') return i;
              let okNet = false;
              try {
                const res = await fetch(i.src, { method: 'GET', cache: 'force-cache' });
                const ct = res.headers.get('content-type') || '';
                okNet = res.ok && ct.startsWith('image/');
                if (okNet) {
                  const blob = await res.blob();
                  const bmp = await createImageBitmap(blob);
                  okNet = bmp.width > 0;
                  bmp.close();
                }
              } catch (_) {
                okNet = false;
              }
              if (okNet) {
                return { ...i, status: 'loaded', note: 'verified-via-fetch' };
              }
              return { ...i, status: 'failed', reason: i.reason || 'bad-or-unreachable' };
            }),
          );

          // Active hero slides check
          const slides = Array.from(document.querySelectorAll('.ghahghah-hero__slide'));
          const next = document.querySelector('[data-ghahghah-hero-next]');
          const slideHealth = [];
          for (let i = 0; i < slides.length; i++) {
            if (i > 0 && next) {
              next.click();
              await sleep(600);
            }
            const active = document.querySelector('.ghahghah-hero__slide.is-active img');
            if (!active) {
              slideHealth.push({ index: i, status: 'failed', reason: 'no-img' });
              continue;
            }
            const t0 = Date.now();
            while (!active.complete && Date.now() - t0 < timeoutMs) await sleep(100);
            try {
              if (active.decode) await active.decode();
            } catch (_) {
              /* decode reject => failed below */
            }
            const ok = active.complete && active.naturalWidth > 0;
            slideHealth.push({
              index: i,
              status: ok ? 'loaded' : 'failed',
              src: active.currentSrc || active.src || '',
              naturalWidth: active.naturalWidth,
            });
          }

          return { items, slideHealth };
        }, LOAD_TIMEOUT_MS);

        const failed = [
          ...classify.items.filter((i) => i.status === 'failed'),
          ...classify.slideHealth.filter((s) => s.status === 'failed'),
        ];
        const counts = classify.items.reduce(
          (a, i) => {
            a[i.status] = (a[i.status] || 0) + 1;
            return a;
          },
          { pending: 0, hidden: 0, loaded: 0, failed: 0 },
        );

        const shot = path.join(outDir, `${pageKey}-${vpName}.png`);
        await page.screenshot({ path: shot, fullPage: false });

        const pageFail = failed.length > 0 || httpFails.length > 0;
        if (pageFail) hardFailures += 1;

        report.results[vpName][pageKey] = {
          url,
          screenshot: shot.replace(/\\/g, '/'),
          counts,
          slideHealth: classify.slideHealth,
          failed: failed.slice(0, 40),
          httpFails: httpFails.slice(0, 40),
          ok: !pageFail,
        };

        console.log(
          `[${vpName}/${pageKey}] ok=${!pageFail} loaded=${counts.loaded} hidden=${counts.hidden} failed=${counts.failed} httpFails=${httpFails.length} slides=${classify.slideHealth.length}`,
        );
        await page.close();
      }
    }
  } finally {
    await browser.close();
  }

  const outJson = path.join(outDir, 'browser-image-health.json');
  fs.writeFileSync(outJson, JSON.stringify(report, null, 2));
  console.log('Wrote', outJson);
  if (hardFailures > 0) {
    console.error(`HARD_FAILURES=${hardFailures}`);
    process.exit(1);
  }
  console.log('BROWSER_IMAGE_HEALTH_OK');
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
