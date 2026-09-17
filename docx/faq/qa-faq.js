/**
 * Capture FAQ page screenshots at 390 / 768 / 1440.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright-core');

const OUT = path.join('docx', 'faq', 'screenshots');
fs.mkdirSync(OUT, { recursive: true });

const candidates = [
	process.env.PLAYWRIGHT_CHROMIUM_PATH,
	'C:/Users/Mohamad/AppData/Local/ms-playwright/chromium-1148/chrome-win/chrome.exe',
	'C:/Users/Mohamad/AppData/Local/ms-playwright/chromium_headless_shell-1148/chrome-win/headless_shell.exe',
].filter(Boolean);

async function main() {
	const executablePath = candidates.find((p) => fs.existsSync(p));
	if (!executablePath) {
		throw new Error('Chromium not found for Playwright');
	}

	const browser = await chromium.launch({
		executablePath,
		headless: true,
	});

	const widths = [390, 768, 1440];
	for (const width of widths) {
		const page = await browser.newPage({
			viewport: { width, height: width <= 480 ? 900 : 1100 },
			deviceScaleFactor: 1,
		});
		await page.goto('http://localhost:8888/faq/', { waitUntil: 'networkidle' });
		await page.waitForSelector('.ghahghah-faq');
		// Expand second item once to show open/closed contrast on mid/desktop shots
		if (width >= 768) {
			const closed = page.locator('details.ghahghah-faq__item:not([open]) summary').first();
			if (await closed.count()) {
				await closed.click();
			}
		}
		await page.screenshot({
			path: path.join(OUT, `faq-${width}.png`),
			fullPage: true,
		});
		await page.close();
		console.log('saved', `faq-${width}.png`);
	}

	await browser.close();
}

main().catch((err) => {
	console.error(err);
	process.exit(1);
});
