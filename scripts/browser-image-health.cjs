/**
 * Browser image health QA — pending / hidden / loaded / failed.
 * Usage: node scripts/browser-image-health.cjs [baseUrl] [outDir]
 *
 * Rules:
 * - Scroll + real slider controls only; never mutate loading/src/srcset.
 * - "loaded" = same DOM img: complete && naturalWidth > 0 && decode OK.
 * - fetch/createImageBitmap is fileHealth only — never promotes DOM status to loaded.
 * - Expected visible images still pending/failed after timeout ⇒ hard fail.
 * - Home requires exactly 9 distinct hero slides visited; missing slider ⇒ fail.
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
const HOME_EXPECTED_SLIDES = Number(process.env.HOME_EXPECTED_SLIDES || 9);

fs.mkdirSync(outDir, { recursive: true });

const pages = {
  home: `${base}/`,
  products: `${base}/products/`,
  articles: `${base}/?post_type=post`,
  guide: `${base}/wholesale-request-guide/`,
  wholesale: `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d8%ae%d8%b1%db%8c%d8%af-%d8%b9%d9%85%d8%af%d9%87/`,
  agency: null,
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
  return (
    line ||
    `${base}/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d9%86%d9%85%d8%a7%db%8c%d9%86%d8%af%da%af%db%8c/`
  );
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
    homeExpectedSlides: HOME_EXPECTED_SLIDES,
    rules: {
      mutateLoadingSrcSrcset: false,
      fetchPromotesDomLoaded: false,
      homeRequiresExactSlides: HOME_EXPECTED_SLIDES,
    },
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

        // Natural scroll only — do not change theme loading behavior.
        await page.evaluate(async () => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
          for (let y = 0; y < h; y += Math.floor(window.innerHeight * 0.65)) {
            window.scrollTo(0, y);
            await sleep(220);
          }
          window.scrollTo(0, 0);
          await sleep(200);
        });

        const classify = await page.evaluate(
          async (timeoutMs, pageKeyInner, expectedSlides) => {
            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
            const isAsset = (src) =>
              /\/uploads\/|\/themes\/ghahghah-theme\/assets\/images\//i.test(src || '');

            const isHiddenForViewport = (img) => {
              const cs = getComputedStyle(img);
              if (cs.display === 'none' || cs.visibility === 'hidden') return true;
              if (Number(cs.opacity) === 0 && !img.closest('.ghahghah-hero__slide')) return true;
              if (img.clientWidth === 0 && img.clientHeight === 0) return true;
              const slide = img.closest('.ghahghah-hero__slide');
              if (slide && !slide.classList.contains('is-active')) return true;
              return false;
            };

            const waitDomLoaded = async (img, budgetMs) => {
              const t0 = Date.now();
              while (Date.now() - t0 < budgetMs) {
                if (img.complete && img.naturalWidth > 0) {
                  try {
                    if (img.decode) await img.decode();
                    return { ok: true, decodeOk: true };
                  } catch (_) {
                    return { ok: false, decodeOk: false, reason: 'decode-failed' };
                  }
                }
                if (img.complete && img.naturalWidth === 0) {
                  return { ok: false, decodeOk: false, reason: 'naturalWidth-0' };
                }
                await sleep(100);
              }
              return {
                ok: false,
                decodeOk: false,
                reason: img.complete ? 'timeout-complete-bad' : 'timeout-pending',
              };
            };

            const fileHealth = async (src) => {
              if (!src) return { ok: false, reason: 'no-src' };
              try {
                const res = await fetch(src, { method: 'GET', cache: 'no-store' });
                const ct = res.headers.get('content-type') || '';
                if (!res.ok || !ct.startsWith('image/')) {
                  return { ok: false, status: res.status, contentType: ct };
                }
                const blob = await res.blob();
                const bmp = await createImageBitmap(blob);
                const w = bmp.width;
                bmp.close();
                return { ok: w > 0, width: w, status: res.status, contentType: ct };
              } catch (e) {
                return { ok: false, reason: String(e && e.message ? e.message : e) };
              }
            };

            // --- Hero slides via real controls (Home requires exact count) ---
            const slides = Array.from(document.querySelectorAll('.ghahghah-hero__slide'));
            const next = document.querySelector('[data-ghahghah-hero-next]');
            const slideHealth = [];
            const distinctSrcs = new Set();
            let sliderError = null;

            if (pageKeyInner === 'home') {
              if (slides.length !== expectedSlides) {
                sliderError = `expected-${expectedSlides}-slides-got-${slides.length}`;
              } else if (!next && slides.length > 1) {
                sliderError = 'missing-next-control';
              }
            }

            if (slides.length > 0) {
              for (let i = 0; i < slides.length; i++) {
                if (i > 0) {
                  if (!next) {
                    slideHealth.push({
                      index: i,
                      status: 'failed',
                      reason: 'no-next-control',
                    });
                    continue;
                  }
                  next.click();
                  await sleep(650);
                }
                const activeSlide = document.querySelector('.ghahghah-hero__slide.is-active');
                const active = activeSlide
                  ? activeSlide.querySelector('img')
                  : null;
                if (!active) {
                  slideHealth.push({ index: i, status: 'failed', reason: 'no-active-img' });
                  continue;
                }
                active.scrollIntoView({ block: 'center', inline: 'nearest' });
                const wait = await waitDomLoaded(active, Math.min(timeoutMs, 12000));
                const src = active.currentSrc || active.src || '';
                if (src) distinctSrcs.add(src);
                const fh = await fileHealth(src);
                slideHealth.push({
                  index: i,
                  status: wait.ok ? 'loaded' : 'failed',
                  reason: wait.ok ? undefined : wait.reason,
                  src,
                  naturalWidth: active.naturalWidth,
                  complete: active.complete,
                  decodeOk: wait.decodeOk,
                  fileHealth: fh,
                });
              }
              if (pageKeyInner === 'home' && !sliderError) {
                if (distinctSrcs.size < expectedSlides) {
                  sliderError = `distinct-slide-srcs-${distinctSrcs.size}-lt-${expectedSlides}`;
                }
              }
            } else if (pageKeyInner === 'home') {
              sliderError = sliderError || 'no-hero-slider';
            }

            // --- Page images: wait with scroll into view only ---
            const start = Date.now();
            const classifyOnce = () =>
              Array.from(document.images)
                .filter((img) => isAsset(img.currentSrc || img.src))
                .map((img) => {
                  const src = img.currentSrc || img.src || '';
                  const hidden = isHiddenForViewport(img);
                  let status = 'pending';
                  if (hidden) {
                    status = 'hidden';
                  } else if (img.complete && img.naturalWidth > 0) {
                    status = 'loaded';
                  } else if (img.complete && img.naturalWidth === 0) {
                    status = 'failed';
                  } else {
                    status = 'pending';
                  }
                  return {
                    src,
                    status,
                    naturalWidth: img.naturalWidth,
                    complete: img.complete,
                    clientWidth: img.clientWidth,
                    clientHeight: img.clientHeight,
                    loadingAttr: img.getAttribute('loading'),
                  };
                });

            let items = classifyOnce();
            while (Date.now() - start < timeoutMs) {
              const pendingVisible = items.filter((i) => i.status === 'pending');
              if (pendingVisible.length === 0) break;

              const pendingImgs = Array.from(document.images).filter((img) => {
                const src = img.currentSrc || img.src || '';
                if (!isAsset(src)) return false;
                if (isHiddenForViewport(img)) return false;
                return !(img.complete && img.naturalWidth > 0);
              });

              for (const img of pendingImgs) {
                // Do NOT set loading=eager or rewrite src/srcset.
                img.scrollIntoView({ block: 'center', inline: 'nearest' });
                await waitDomLoaded(img, 4000);
              }
              await sleep(200);
              items = classifyOnce();
            }

            // Attach fileHealth for non-loaded expected images — never promote to loaded.
            items = await Promise.all(
              items.map(async (i) => {
                if (i.status === 'loaded' || i.status === 'hidden') {
                  return i;
                }
                const fh = await fileHealth(i.src);
                // Still pending/failed for DOM display; fileHealth is informational only.
                const status = i.status === 'pending' ? 'failed' : i.status;
                return {
                  ...i,
                  status,
                  reason:
                    i.status === 'pending'
                      ? 'dom-not-displayed-before-timeout'
                      : i.reason || 'dom-failed',
                  fileHealth: fh,
                };
              }),
            );

            return {
              items,
              slideHealth,
              sliderError,
              distinctSlideSrcCount: distinctSrcs.size,
              slideCount: slides.length,
            };
          },
          LOAD_TIMEOUT_MS,
          pageKey,
          HOME_EXPECTED_SLIDES,
        );

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

        const homeSliderFail =
          pageKey === 'home' &&
          (!!classify.sliderError ||
            classify.slideCount !== HOME_EXPECTED_SLIDES ||
            classify.slideHealth.length !== HOME_EXPECTED_SLIDES ||
            classify.slideHealth.some((s) => s.status !== 'loaded'));

        const pageFail = failed.length > 0 || httpFails.length > 0 || homeSliderFail;
        if (pageFail) hardFailures += 1;

        report.results[vpName][pageKey] = {
          url,
          screenshot: shot.replace(/\\/g, '/'),
          counts,
          slideCount: classify.slideCount,
          distinctSlideSrcCount: classify.distinctSlideSrcCount,
          sliderError: classify.sliderError,
          slideHealth: classify.slideHealth,
          failed: failed.slice(0, 40),
          httpFails: httpFails.slice(0, 40),
          homeSliderFail: pageKey === 'home' ? homeSliderFail : undefined,
          ok: !pageFail,
        };

        console.log(
          `[${vpName}/${pageKey}] ok=${!pageFail} loaded=${counts.loaded} hidden=${counts.hidden} failed=${counts.failed} httpFails=${httpFails.length} slides=${classify.slideCount} distinct=${classify.distinctSlideSrcCount}${classify.sliderError ? ' ERR=' + classify.sliderError : ''}`,
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
