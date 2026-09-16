/**
 * Browser image health check for 8898 (mobile + desktop).
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const { spawnSync } = require('child_process');
const { pathToFileURL } = require('url');

const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const outDir = path.resolve('docs/audits/artifacts/media-recovery/screenshots');
fs.mkdirSync(outDir, { recursive: true });

const pages = {
  home: 'http://localhost:8898/',
  products: 'http://localhost:8898/products/',
  articles: 'http://localhost:8898/?post_type=post',
  guide: 'http://localhost:8898/wholesale-request-guide/',
  wholesale:
    'http://localhost:8898/%d8%af%d8%b1%d8%ae%d9%88%d8%a7%d8%b3%d8%aa-%d8%ae%d8%b1%db%8c%d8%af-%d8%b9%d9%85%d8%af%d9%87/',
};

const viewports = {
  mobile: { width: 390, height: 844, isMobile: true },
  desktop: { width: 1440, height: 900, isMobile: false },
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

(async () => {
  const puppeteer = ensurePuppeteer();
  const report = { generatedAt: new Date().toISOString(), results: {} };
  const browser = await puppeteer.launch({
    executablePath: chrome,
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu', '--window-size=1440,900'],
  });

  try {
    for (const [vpName, vp] of Object.entries(viewports)) {
      report.results[vpName] = {};
      for (const [pageKey, url] of Object.entries(pages)) {
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
        const responses = [];
        page.on('response', (res) => {
          const u = res.url();
          if (/\/uploads\/|\.webp|\.jpe?g|\.png|\.gif|\.svg/i.test(u)) {
            responses.push({
              url: u,
              status: res.status(),
              type: res.headers()['content-type'] || '',
            });
          }
        });
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 90000 });

        await page.evaluate(async () => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const h = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
          for (let y = 0; y < h; y += Math.floor(window.innerHeight * 0.7)) {
            window.scrollTo(0, y);
            await sleep(180);
          }
          window.scrollTo(0, 0);
          await sleep(250);
        });

        const slideProbe = await page.evaluate(async () => {
          const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
          const slides = Array.from(document.querySelectorAll('.ghahghah-hero__slide'));
          const nextBtn = document.querySelector('[data-ghahghah-hero-next]');

          const captureActive = () => {
            const active = document.querySelector('.ghahghah-hero__slide.is-active');
            if (!active) return null;
            const img = active.querySelector('img.ghahghah-hero__image, img');
            if (!img) return { missingImg: true };
            return {
              currentSrc: img.currentSrc || img.src || '',
              naturalWidth: img.naturalWidth,
              naturalHeight: img.naturalHeight,
              complete: img.complete,
              display: getComputedStyle(img).display,
              visibility: getComputedStyle(img).visibility,
              opacity: getComputedStyle(img).opacity,
              clientWidth: img.clientWidth,
              clientHeight: img.clientHeight,
            };
          };

          const perSlide = [];
          perSlide.push({ index: 0, active: captureActive() });
          for (let i = 1; i < Math.max(slides.length, 1); i++) {
            if (nextBtn) nextBtn.click();
            await sleep(700);
            perSlide.push({ index: i, active: captureActive() });
          }

          return {
            slideCountDom: slides.length,
            hasNext: Boolean(nextBtn),
            perSlide,
            allContentImages: Array.from(document.images).map((img) => ({
              currentSrc: img.currentSrc || img.src || '',
              naturalWidth: img.naturalWidth,
              naturalHeight: img.naturalHeight,
              complete: img.complete,
              visible:
                img.clientWidth > 0 &&
                img.clientHeight > 0 &&
                getComputedStyle(img).visibility !== 'hidden' &&
                getComputedStyle(img).display !== 'none' &&
                Number(getComputedStyle(img).opacity) > 0,
            })),
          };
        });

        const shotPath = path.join(outDir, `${pageKey}-${vpName}.png`);
        await page.screenshot({ path: shotPath, fullPage: false });

        const failedImgs = (slideProbe.allContentImages || []).filter(
          (i) =>
            i.currentSrc &&
            /uploads|themes\/ghahghah-theme\/assets/i.test(i.currentSrc) &&
            (!i.complete || i.naturalWidth <= 0),
        );
        const failedResponses = responses.filter((r) => r.status >= 400);

        const slideHealth = (slideProbe.perSlide || []).map((s) => {
          const a = s.active || {};
          return {
            index: s.index,
            ok: Boolean(a.naturalWidth > 0 && a.complete && a.clientWidth > 0),
            currentSrc: a.currentSrc || null,
            naturalWidth: a.naturalWidth || 0,
          };
        });

        report.results[vpName][pageKey] = {
          url,
          screenshot: shotPath.replace(/\\/g, '/'),
          slideCountDom: slideProbe.slideCountDom,
          hasNext: slideProbe.hasNext,
          slideHealth,
          allSlidesOk:
            slideProbe.slideCountDom === 0 ||
            (slideHealth.length >= slideProbe.slideCountDom &&
              slideHealth.slice(0, slideProbe.slideCountDom).every((s) => s.ok)),
          failedImgs,
          failedResponses: failedResponses.slice(0, 40),
          imageCount: slideProbe.allContentImages.length,
          healthyVisible: slideProbe.allContentImages.filter((i) => i.visible && i.naturalWidth > 0)
            .length,
        };

        console.log(
          `[${vpName}/${pageKey}] slides=${slideProbe.slideCountDom} allSlidesOk=${report.results[vpName][pageKey].allSlidesOk} failedDecode=${failedImgs.length} failedHttp=${failedResponses.length}`,
        );
        await page.close();
      }
    }
  } finally {
    await browser.close();
  }

  fs.writeFileSync(
    path.resolve('docs/audits/artifacts/media-recovery/browser-image-health.json'),
    JSON.stringify(report, null, 2),
  );
  console.log('Wrote browser-image-health.json');
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
