const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright-core');

const css = fs.readFileSync('ghahghah-theme/assets/css/featured.css', 'utf8');
const js = fs.readFileSync('ghahghah-theme/assets/js/featured.js', 'utf8');

const imgs = [
	{ src: '../../ghahghah-theme/assets/images/products/catalog/limoo-corn-pellet-full.webp', name: 'چیپس ذرت قهقهه طعم لیمویی' },
	{ src: '../../ghahghah-theme/assets/images/products/catalog/vinegar-corn-pellet-full.webp', name: 'چیپس ذرت قهقهه طعم سرکه‌ای' },
	{ src: '../../ghahghah-theme/assets/images/products/catalog/pizza-corn-pellet-full.webp', name: 'چیپس ذرت قهقهه طعم پیتزا' },
	{ src: '../../ghahghah-theme/assets/images/products/catalog/shallot-yogurt.jpg', name: 'چیپس ذرت قهقهه طعم ماست موسیر' },
	{ src: '../../ghahghah-theme/assets/images/products/catalog/parsley-onion.jpg', name: 'چیپس ذرت قهقهه طعم پیاز جعفری' },
	{ src: '../../ghahghah-theme/assets/images/products/catalog/ketchup.jpg', name: 'چیپس ذرت قهقهه طعم کچاپ' },
];

const arrowLeft =
	'<svg class="ghahghah-featured__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>';
const arrowRight =
	'<svg class="ghahghah-featured__arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';

const cards = imgs
	.map(
		(item, index) => `
<li class="ghahghah-featured__item" data-ghahghah-featured-item data-index="${index}">
  <article class="ghahghah-featured__card">
    <div class="ghahghah-featured__media"><img class="ghahghah-featured__img" src="${item.src}" alt="" width="400" height="520" loading="lazy" decoding="async" /></div>
    <h3 class="ghahghah-featured__name">${item.name}</h3>
    <a class="ghahghah-featured__cta" href="#p${index}"><span>مشاهده محصول</span>${arrowLeft}</a>
  </article>
</li>`
	)
	.join('');

const html = `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>کاروسل محصولات منتخب</title>
<style>
:root{--ghahghah-font-primary:Tahoma,sans-serif}
*{box-sizing:border-box}
body{margin:0;font-family:var(--ghahghah-font-primary);background:#fff}
${css}
</style>
</head>
<body>
<section class="ghahghah-featured" aria-labelledby="ghahghah-featured-title" data-ghahghah-featured>
  <div class="ghahghah-featured__shell">
    <header class="ghahghah-featured__head">
      <div class="ghahghah-featured__intro">
        <h2 id="ghahghah-featured-title" class="ghahghah-featured__title">محصولات منتخب <span class="ghahghah-featured__accent">قهقهه</span></h2>
        <p class="ghahghah-featured__text">طعم‌های قهقهه را بیشتر بشناسید.</p>
      </div>
      <div class="ghahghah-featured__toolbar">
        <div class="ghahghah-featured__controls" data-ghahghah-featured-controls hidden>
          <button type="button" class="ghahghah-featured__nav ghahghah-featured__nav--prev" data-ghahghah-featured-prev aria-controls="ghahghah-featured-track" aria-label="محصولات قبلی">${arrowRight}</button>
          <button type="button" class="ghahghah-featured__nav ghahghah-featured__nav--next" data-ghahghah-featured-next aria-controls="ghahghah-featured-track" aria-label="محصولات بعدی">${arrowLeft}</button>
        </div>
        <a class="ghahghah-featured__all ghahghah-featured__all--top" href="#"><span>مشاهده همه محصولات</span>${arrowLeft}</a>
      </div>
    </header>
    <div class="ghahghah-featured__viewport" data-ghahghah-featured-viewport>
      <ul id="ghahghah-featured-track" class="ghahghah-featured__track" data-ghahghah-featured-track tabindex="0" aria-label="فهرست محصولات منتخب">${cards}</ul>
    </div>
    <p class="screen-reader-text" data-ghahghah-featured-live aria-live="polite" aria-atomic="true"></p>
    <div class="ghahghah-featured__footer">
      <a class="ghahghah-featured__all ghahghah-featured__all--bottom" href="#"><span>مشاهده همه محصولات</span>${arrowLeft}</a>
    </div>
  </div>
</section>
<script>${js}</script>
</body>
</html>`;

fs.mkdirSync('docx/featured/screenshots', { recursive: true });
fs.writeFileSync('docx/featured/preview-carousel.html', html);

(async () => {
	const fileUrl = 'file:///' + path.resolve('docx/featured/preview-carousel.html').split(path.sep).join('/');
	const browser = await chromium.launch({ channel: 'chrome', headless: true }).catch(() => chromium.launch({ headless: true }));
	const page = await browser.newPage();
	const report = [];

	for (const w of [320, 390, 768, 1440]) {
		await page.setViewportSize({ width: w, height: 900 });
		await page.goto(fileUrl, { waitUntil: 'load' });
		await page.waitForTimeout(400);
		await page.locator('.ghahghah-featured').screenshot({ path: `docx/featured/screenshots/carousel-${w}.png` });

		const metrics = await page.evaluate(() => {
			const track = document.querySelector('[data-ghahghah-featured-track]');
			const controls = document.querySelector('[data-ghahghah-featured-controls]');
			const items = [...document.querySelectorAll('[data-ghahghah-featured-item]')];
			const first = items[0]?.getBoundingClientRect();
			const second = items[1]?.getBoundingClientRect();
			const trackRect = track.getBoundingClientRect();
			const bodyScrollWidth = document.documentElement.scrollWidth;
			const bodyClientWidth = document.documentElement.clientWidth;
			return {
				visibleApprox: first ? Math.round((trackRect.width / first.width) * 100) / 100 : 0,
				gap: Math.round((first && second ? Math.abs(first.left - second.right) : 0) * 10) / 10,
				controlsHidden: controls?.hidden ?? null,
				pageHorizontalScroll: bodyScrollWidth > bodyClientWidth + 1,
				itemCount: items.length,
				mediaH: Math.round(document.querySelector('.ghahghah-featured__media')?.getBoundingClientRect().height || 0),
			};
		});

		// Reach all 6 via next clicks
		const next = page.locator('[data-ghahghah-featured-next]');
		if (await next.isVisible()) {
			for (let i = 0; i < 8; i++) {
				if (await next.isDisabled()) break;
				await next.click();
				await page.waitForTimeout(350);
			}
		}
		const endState = await page.evaluate(() => {
			const items = [...document.querySelectorAll('[data-ghahghah-featured-item]')];
			const last = items[items.length - 1]?.getBoundingClientRect();
			const track = document.querySelector('[data-ghahghah-featured-track]')?.getBoundingClientRect();
			const nextDisabled = document.querySelector('[data-ghahghah-featured-next]')?.disabled;
			const prevDisabled = document.querySelector('[data-ghahghah-featured-prev]')?.disabled;
			return {
				lastVisible: !!(last && track && last.left >= track.left - 4),
				nextDisabled,
				prevDisabled,
			};
		});

		await page.locator('[data-ghahghah-featured-prev]').click({ trial: false }).catch(() => {});
		await page.waitForTimeout(200);

		report.push({ w, ...metrics, ...endState });
	}

	// keyboard focus
	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto(fileUrl, { waitUntil: 'load' });
	await page.locator('.ghahghah-featured__cta').nth(0).focus();
	const outline = await page.locator('.ghahghah-featured__cta').nth(0).evaluate((el) => getComputedStyle(el).outlineStyle);
	report.push({ focusOutline: outline });

	console.log(JSON.stringify(report, null, 2));
	await browser.close();
})().catch((e) => {
	console.error(e);
	process.exit(1);
});
