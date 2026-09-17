const { chromium } = require('playwright-core');
const path = require('path');

(async () => {
	const fileUrl = 'file:///' + path.resolve('docx/featured/preview.html').split(path.sep).join('/');
	const browser = await chromium.launch({ channel: 'chrome', headless: true }).catch(() =>
		chromium.launch({ headless: true })
	);
	const page = await browser.newPage();
	const out = [];

	for (const w of [390, 768, 1440]) {
		await page.setViewportSize({ width: w, height: 1200 });
		await page.goto(fileUrl, { waitUntil: 'load' });
		await page.waitForTimeout(500);
		await page.locator('.ghahghah-featured').screenshot({
			path: `docx/featured/screenshots/featured-preview-${w}.png`,
		});
		const info = await page.evaluate(() => {
			const grid = getComputedStyle(document.querySelector('.ghahghah-featured__grid'));
			const card = getComputedStyle(document.querySelector('.ghahghah-featured__card'));
			const top = document.querySelector('.ghahghah-featured__all--top');
			const foot = document.querySelector('.ghahghah-featured__footer');
			return {
				cols: grid.gridTemplateColumns.trim().split(/\s+/).filter(Boolean).length,
				gap: grid.gap || grid.rowGap,
				radius: card.borderRadius,
				topDisplay: getComputedStyle(top).display,
				footerDisplay: getComputedStyle(foot).display,
				cardCount: document.querySelectorAll('.ghahghah-featured__card').length,
			};
		});
		out.push({ w, ...info });
	}

	await page.setViewportSize({ width: 1440, height: 900 });
	await page.goto(fileUrl, { waitUntil: 'load' });
	await page.locator('.ghahghah-featured__cta').first().focus();
	const outline = await page.locator('.ghahghah-featured__cta').first().evaluate((el) => getComputedStyle(el).outlineStyle);
	out.push({ focusOutline: outline });

	console.log(JSON.stringify(out, null, 2));
	await browser.close();
})().catch((e) => {
	console.error(e);
	process.exit(1);
});
