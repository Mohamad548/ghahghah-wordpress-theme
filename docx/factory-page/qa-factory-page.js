/**
 * Capture factory page screenshots.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright-core');

const OUT = path.join('docx', 'factory-page', 'screenshots');
fs.mkdirSync(OUT, { recursive: true });

const exe = 'C:/Users/Mohamad/AppData/Local/ms-playwright/chromium-1148/chrome-win/chrome.exe';
const url = 'http://localhost:8888/%da%a9%d8%a7%d8%b1%d8%ae%d8%a7%d9%86%d9%87/';

(async () => {
	const browser = await chromium.launch({ executablePath: exe, headless: true });
	for (const width of [390, 768, 1440]) {
		const page = await browser.newPage({
			viewport: { width, height: width <= 480 ? 2200 : 1600 },
		});
		await page.goto(url, { waitUntil: 'networkidle' });
		await page.waitForSelector('.ghahghah-fp');
		await page.screenshot({ path: path.join(OUT, `factory-${width}.png`), fullPage: true });
		const overflow = await page.evaluate(
			() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 1
		);
		console.log(`factory-${width}.png overflow=${overflow}`);
		await page.close();
	}
	await browser.close();
})();
