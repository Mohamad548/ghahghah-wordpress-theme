/**
 * Capture desktop Before final screenshots for 4 pages under baseline image PHP.
 */
import fs from 'fs';
import path from 'path';
import os from 'os';
import { spawnSync } from 'child_process';

const ROOT = process.cwd();
const BASELINE = '7add574586a6b1c636881efd1b237a38cd81363d';
const AFTER_REF = 'def0c87b467a5d7ed77432153684f57cc7999e8b';
const IMAGE_PATHS = [
  'ghahghah-theme/inc/contact-page-settings.php',
  'ghahghah-theme/inc/media/sync-theme-media.php',
  'ghahghah-theme/inc/request-pages-settings.php',
  'ghahghah-theme/inc/single-article-settings.php',
  'ghahghah-theme/single-post.php',
];
const outDir = path.join(ROOT, 'docs/audits/artifacts/perf-images/desktop/before');
const urls = JSON.parse(
  fs.readFileSync(path.join(ROOT, 'docs/audits/artifacts/perf-images/urls.json'), 'utf8'),
);
const pages = ['wholesale', 'agency', 'contact', 'article'];
const chrome = process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe';

function gitCheckout(ref) {
  const r = spawnSync('git', ['checkout', ref, '--', ...IMAGE_PATHS], {
    cwd: ROOT,
    encoding: 'utf8',
    shell: false,
  });
  if (r.status !== 0) throw new Error(r.stderr || r.stdout || `git checkout ${ref} failed`);
}

fs.mkdirSync(outDir, { recursive: true });
console.log('Switching to BASELINE for desktop before shots');
gitCheckout(BASELINE);

const summary = { phase: 'before-desktop', generatedAt: new Date().toISOString(), pages: {} };

try {
  for (const page of pages) {
    const url = urls[page];
    const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'ghh-desk-'));
    const tmpJson = path.join(tmp, `${page}.json`);
    console.log('[desktop-before]', page);
    const res = spawnSync(
      'npx',
      [
        '--yes',
        'lighthouse@12.2.1',
        url,
        '--output=json',
        `--output-path=${tmpJson}`,
        `--chrome-path=${chrome}`,
        '--chrome-flags=--headless=new --no-sandbox --disable-gpu',
        '--only-categories=performance',
        '--preset=desktop',
        '--quiet',
      ],
      { cwd: os.tmpdir(), encoding: 'utf8', shell: true, timeout: 420000, windowsHide: true },
    );
    if (res.status !== 0 || !fs.existsSync(tmpJson)) {
      summary.pages[page] = { error: res.stderr || res.stdout || `exit ${res.status}` };
      continue;
    }
    const destJson = path.join(outDir, `${page}-desktop-run1.json`);
    fs.copyFileSync(tmpJson, destJson);
    const lhr = JSON.parse(fs.readFileSync(destJson, 'utf8'));
    const shot = lhr.audits?.['final-screenshot']?.details?.data;
    if (shot) {
      fs.writeFileSync(
        path.join(outDir, `${page}-desktop-final.jpg`),
        Buffer.from(String(shot).replace(/^data:image\/jpeg;base64,/, ''), 'base64'),
      );
    }
    const htmlAsset =
      spawnSync('curl', ['-sS', url], { encoding: 'utf8', shell: true }).stdout || '';
    const refs = [...htmlAsset.matchAll(/[\w/-]+\.(?:png|webp)/gi)].map((m) => m[0]).slice(0, 8);
    const a = lhr.audits || {};
    summary.pages[page] = {
      url,
      performance: Math.round((lhr.categories?.performance?.score || 0) * 100),
      lcp: a['largest-contentful-paint']?.numericValue ?? null,
      tbt: a['total-blocking-time']?.numericValue ?? null,
      cls: a['cumulative-layout-shift']?.numericValue ?? null,
      screenshot: Boolean(shot),
      htmlImageRefs: refs,
    };
    try {
      fs.rmSync(tmp, { recursive: true, force: true });
    } catch {
      /* ignore */
    }
  }
} finally {
  console.log('Restoring AFTER');
  gitCheckout(AFTER_REF);
}

fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
console.log(JSON.stringify(summary, null, 2));
