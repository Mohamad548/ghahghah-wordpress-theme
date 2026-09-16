const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

(async () => {
  const outDir = path.join(__dirname, '..', 'docx', 'header', 'screenshots');
  fs.mkdirSync(outDir, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ locale: 'fa-IR' });

  const desktop = await context.newPage();
  await desktop.setViewportSize({ width: 1440, height: 900 });
  await desktop.goto('http://localhost:8888/', { waitUntil: 'networkidle' });
  await desktop.waitForSelector('.site-header');

  const dir = await desktop.locator('html').getAttribute('dir');
  console.log('html dir =', dir);

  const productsToggle = desktop.locator('.site-nav--desktop [data-ghahghah-submenu-toggle]').first();
  if (await productsToggle.count()) {
    await productsToggle.click();
    await desktop.waitForTimeout(400);
  }

  // Capture header band including open submenu
  await desktop.screenshot({
    path: path.join(outDir, 'header-desktop-1440.png'),
    clip: { x: 0, y: 0, width: 1440, height: 280 },
  });

  const mobile = await context.newPage();
  await mobile.setViewportSize({ width: 390, height: 844 });
  await mobile.goto('http://localhost:8888/', { waitUntil: 'networkidle' });
  await mobile.click('[data-ghahghah-drawer-open]');
  await mobile.waitForSelector('[data-ghahghah-drawer]:not([hidden])');

  const mobileToggle = mobile.locator('.site-nav--mobile [data-ghahghah-submenu-toggle]').first();
  if (await mobileToggle.count()) {
    await mobileToggle.click();
    await mobile.waitForTimeout(300);
  }

  await mobile.screenshot({
    path: path.join(outDir, 'header-mobile-drawer-390.png'),
  });

  await browser.close();
  console.log('Screenshots saved to', outDir);
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
