#!/usr/bin/env node
/**
 * Reprocess full Lighthouse 12.x LHR JSON from TEMP into validation artifacts.
 * Does not modify production code. Safe to re-run.
 *
 * Usage:
 *   node docs/audits/artifacts/baseline-validation/extract-validity.mjs
 */
import fs from 'fs';
import path from 'path';
import crypto from 'crypto';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '../../../..');
const RAW = process.env.LH_RAW_DIR ||
  'C:/Users/Mohamad/AppData/Local/Temp/ghahghah-lh-raw-2026-09-16';
const OUT = path.join(ROOT, 'docs/audits/artifacts/baseline-validation');

const PAGES = [
  'home',
  'products_archive',
  'single_product',
  'articles_archive',
  'single_article',
  'wholesale',
  'agency',
  'contact',
];
const FFS = ['mobile', 'desktop'];

function sha256(file) {
  return crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
}

function isValidMetric(audit) {
  if (!audit) return false;
  if (audit.scoreDisplayMode === 'error') return false;
  if (audit.errorMessage) return false;
  if (typeof audit.numericValue !== 'number' || Number.isNaN(audit.numericValue)) {
    return false;
  }
  return true;
}

function median(nums) {
  const a = [...nums].sort((x, y) => x - y);
  if (!a.length) return null;
  const mid = Math.floor(a.length / 2);
  return a.length % 2 ? a[mid] : (a[mid - 1] + a[mid]) / 2;
}

function topNetwork(lhr, n = 10) {
  const items = lhr.audits?.['network-requests']?.details?.items || [];
  return [...items]
    .sort((a, b) => (b.transferSize || 0) - (a.transferSize || 0))
    .slice(0, n)
    .map((it) => ({
      url: it.url,
      resourceType: it.resourceType,
      transferSize: it.transferSize,
      resourceSize: it.resourceSize,
      startTime: it.startTime,
      endTime: it.endTime,
      priority: it.priority,
      statusCode: it.statusCode,
    }));
}

function extractLcpElement(lhr) {
  const el = lhr.audits?.['largest-contentful-paint-element'];
  if (!el) {
    return {
      present: false,
      note:
        'largest-contentful-paint-element missing. In LH13 this becomes lcp-phases-insight; this corpus is LH12.',
    };
  }
  if (el.errorMessage) {
    return {
      present: true,
      error: el.errorMessage,
      scoreDisplayMode: el.scoreDisplayMode,
    };
  }
  const items = el.details?.items || [];
  let node = null;
  for (const it of items) {
    if (it?.node) node = it.node;
    if (it?.items) {
      for (const sub of it.items) {
        if (sub?.node) node = sub.node;
      }
    }
  }
  return {
    present: true,
    scoreDisplayMode: el.scoreDisplayMode,
    node: node
      ? {
          selector: node.selector,
          snippet: node.snippet?.slice?.(0, 400),
          nodeLabel: node.nodeLabel,
          boundingRect: node.boundingRect,
        }
      : null,
    itemCount: items.length,
    detailsType: el.details?.type,
  };
}

function extractLayoutShifts(lhr) {
  const ls = lhr.audits?.['layout-shifts'];
  if (!ls) {
    return {
      layoutShiftsAudit: 'MISSING',
      cls: lhr.audits?.['cumulative-layout-shift']?.numericValue ?? null,
      note: 'LH13 replaces layout-shifts with cls-culprits-insight; corpus is LH12.',
    };
  }
  if (ls.errorMessage) return { error: ls.errorMessage };
  const items = ls.details?.items || [];
  return {
    score: ls.score,
    items: items.slice(0, 10).map((it) => ({
      score: it.score,
      node: it.node
        ? {
            selector: it.node.selector,
            snippet: it.node.snippet?.slice?.(0, 250),
            nodeLabel: it.node.nodeLabel,
          }
        : null,
    })),
  };
}

function findLcpResourceFromNetwork(lhr) {
  const items = lhr.audits?.['network-requests']?.details?.items || [];
  const images = items.filter((i) => i.resourceType === 'Image');
  const high = images.filter((i) => {
    const p = (i.priority || '').toLowerCase();
    return p === 'high' || p === 'veryhigh';
  });
  const byBytes = [...images].sort(
    (a, b) => (b.transferSize || 0) - (a.transferSize || 0),
  );
  const mapImg = (i) => ({
    url: i.url,
    transferSize: i.transferSize,
    resourceSize: i.resourceSize,
    priority: i.priority,
    startTime: i.startTime,
  });
  return {
    highPriorityImages: high.map(mapImg),
    largestImages: byBytes.slice(0, 8).map(mapImg),
  };
}

function loadRun(page, ff, run) {
  const name = `${page}-${ff}-run${run}.json`;
  const file = path.join(RAW, name);
  if (!fs.existsSync(file)) return null;
  const lhr = JSON.parse(fs.readFileSync(file, 'utf8'));
  return {
    name,
    file,
    lhr,
    sha256: sha256(file),
    bytes: fs.statSync(file).size,
  };
}

fs.mkdirSync(OUT, { recursive: true });

const matrix = {};
const checksums = [];
const toolMeta = {
  extractor: 'extract-validity.mjs',
  extractedAt: new Date().toISOString(),
  rawDir: RAW,
  nodeVersion: process.version,
  medianPolicy:
    'Median only from valid numeric samples; NO_LCP/null/errorMode excluded; validCount reported beside each median.',
};

for (const page of PAGES) {
  for (const ff of FFS) {
    const key = `${page}:${ff}`;
    const runs = [];
    for (let r = 1; r <= 3; r++) {
      const loaded = loadRun(page, ff, r);
      if (!loaded) {
        runs.push({ run: r, missing: true });
        continue;
      }
      const { lhr, name, sha256: hash, bytes } = loaded;
      checksums.push({ file: name, sha256: hash, bytes });

      const lcpA = lhr.audits?.['largest-contentful-paint'];
      const clsA = lhr.audits?.['cumulative-layout-shift'];
      const fcpA = lhr.audits?.['first-contentful-paint'];
      const tbtA = lhr.audits?.['total-blocking-time'];
      const siA = lhr.audits?.['speed-index'];
      const perf = lhr.categories?.performance?.score;
      const reqs = lhr.audits?.['network-requests']?.details?.items || [];
      const transfer = reqs.reduce((s, i) => s + (i.transferSize || 0), 0);

      runs.push({
        run: r,
        file: name,
        sha256: hash,
        bytes,
        lighthouseVersion: lhr.lighthouseVersion,
        fetchTime: lhr.fetchTime,
        requestedUrl: lhr.requestedUrl,
        finalUrl: lhr.finalDisplayedUrl || lhr.finalUrl,
        runtimeError: lhr.runtimeError || null,
        runWarnings: lhr.runWarnings || [],
        formFactor: lhr.configSettings?.formFactor,
        throttlingMethod: lhr.configSettings?.throttlingMethod,
        throttling: lhr.configSettings?.throttling,
        screenEmulation: lhr.configSettings?.screenEmulation,
        hostUserAgent: lhr.environment?.hostUserAgent,
        networkUserAgent: lhr.environment?.networkUserAgent,
        benchmarkIndex: lhr.environment?.benchmarkIndex,
        chromeVersionFromUA:
          (lhr.environment?.hostUserAgent || '').match(
            /HeadlessChrome\/([\d.]+)/,
          )?.[1] || null,
        performanceScore: perf == null ? null : Math.round(perf * 100),
        performanceScoreValid: typeof perf === 'number',
        metrics: {
          lcp: {
            value: lcpA?.numericValue ?? null,
            unit: 'ms',
            valid: isValidMetric(lcpA),
            errorMessage: lcpA?.errorMessage || null,
            scoreDisplayMode: lcpA?.scoreDisplayMode,
          },
          cls: {
            value: clsA?.numericValue ?? null,
            unit: 'unitless',
            valid: isValidMetric(clsA),
            errorMessage: clsA?.errorMessage || null,
          },
          fcp: {
            value: fcpA?.numericValue ?? null,
            unit: 'ms',
            valid: isValidMetric(fcpA),
            errorMessage: fcpA?.errorMessage || null,
          },
          tbt: {
            value: tbtA?.numericValue ?? null,
            unit: 'ms',
            valid: isValidMetric(tbtA),
            errorMessage: tbtA?.errorMessage || null,
          },
          si: {
            value: siA?.numericValue ?? null,
            unit: 'ms',
            valid: isValidMetric(siA),
            errorMessage: siA?.errorMessage || null,
          },
        },
        requestCount: reqs.length,
        transferSizeBytes: transfer,
        lcpElementAudit: extractLcpElement(lhr),
        networkLcpHints: findLcpResourceFromNetwork(lhr),
        insightKeysPresent: {
          'cls-culprits-insight': !!lhr.audits?.['cls-culprits-insight'],
          'lcp-discovery-insight': !!lhr.audits?.['lcp-discovery-insight'],
          'lcp-phases-insight': !!lhr.audits?.['lcp-phases-insight'],
          'image-delivery-insight': !!lhr.audits?.['image-delivery-insight'],
          'largest-contentful-paint-element':
            !!lhr.audits?.['largest-contentful-paint-element'],
          'layout-shifts': !!lhr.audits?.['layout-shifts'],
          'prioritize-lcp-image': !!lhr.audits?.['prioritize-lcp-image'],
        },
        prioritizeLcpImageError:
          lhr.audits?.['prioritize-lcp-image']?.errorMessage || null,
        finalScreenshotPresent: !!lhr.audits?.['final-screenshot']?.details?.data,
      });
    }

    const validLcp = runs.filter((r) => r.metrics?.lcp?.valid).map((r) => r.metrics.lcp.value);
    const validCls = runs.filter((r) => r.metrics?.cls?.valid).map((r) => r.metrics.cls.value);
    const validFcp = runs.filter((r) => r.metrics?.fcp?.valid).map((r) => r.metrics.fcp.value);
    const validTbt = runs.filter((r) => r.metrics?.tbt?.valid).map((r) => r.metrics.tbt.value);
    const validPerf = runs.filter((r) => r.performanceScoreValid).map((r) => r.performanceScore);
    const validXfer = runs
      .filter((r) => typeof r.transferSizeBytes === 'number')
      .map((r) => r.transferSizeBytes);
    const validReqs = runs
      .filter((r) => typeof r.requestCount === 'number')
      .map((r) => r.requestCount);

    matrix[key] = {
      page,
      formFactor: ff,
      runsAvailable: runs.filter((r) => !r.missing).length,
      validity: {
        lcp: {
          validCount: validLcp.length,
          invalidCount: runs.filter((r) => !r.missing).length - validLcp.length,
          invalidReasons: [
            ...new Set(
              runs.map((r) => r.metrics?.lcp?.errorMessage).filter(Boolean),
            ),
          ],
        },
        cls: {
          validCount: validCls.length,
          invalidCount: runs.filter((r) => !r.missing).length - validCls.length,
        },
        fcp: {
          validCount: validFcp.length,
          invalidCount: runs.filter((r) => !r.missing).length - validFcp.length,
        },
        tbt: {
          validCount: validTbt.length,
          invalidCount: runs.filter((r) => !r.missing).length - validTbt.length,
          invalidReasons: [
            ...new Set(
              runs.map((r) => r.metrics?.tbt?.errorMessage).filter(Boolean),
            ),
          ],
        },
        performance: {
          validCount: validPerf.length,
          invalidCount: runs.filter((r) => !r.missing).length - validPerf.length,
        },
      },
      mediansValidOnly: {
        lcp_ms: median(validLcp),
        cls: median(validCls),
        fcp_ms: median(validFcp),
        tbt_ms: median(validTbt),
        performance: median(validPerf),
        transferSizeBytes: median(validXfer),
        requestCount: median(validReqs),
      },
      runs,
    };
  }
}

function deep(page, ff, run = 2) {
  const loaded = loadRun(page, ff, run);
  if (!loaded) return null;
  const { lhr, name, sha256: hash } = loaded;
  const lcpMs = lhr.audits?.['largest-contentful-paint']?.numericValue;
  const top = topNetwork(lhr, 15);
  return {
    file: name,
    sha256: hash,
    lcpElement: extractLcpElement(lhr),
    layoutShifts: extractLayoutShifts(lhr),
    cls: lhr.audits?.['cumulative-layout-shift']?.numericValue ?? null,
    lcp: lcpMs ?? null,
    lcpError: lhr.audits?.['largest-contentful-paint']?.errorMessage || null,
    networkHints: findLcpResourceFromNetwork(lhr),
    top15ByTransfer: top,
    top10WithLcpRelation: top.slice(0, 10).map((t) => ({
      ...t,
      relativeToLcp:
        typeof lcpMs === 'number' && typeof t.startTime === 'number'
          ? t.startTime <= lcpMs
            ? 'started_before_or_at_lcp'
            : 'started_after_lcp'
          : 'unknown',
    })),
    prioritizeError:
      lhr.audits?.['prioritize-lcp-image']?.errorMessage || null,
    transferTotalBytes: (lhr.audits?.['network-requests']?.details?.items || [])
      .reduce((s, i) => s + (i.transferSize || 0), 0),
    resourceSizeTotalBytes: (
      lhr.audits?.['network-requests']?.details?.items || []
    ).reduce((s, i) => s + (i.resourceSize || 0), 0),
  };
}

const deepDives = {
  wholesale_mobile: deep('wholesale', 'mobile', 2),
  agency_mobile: deep('agency', 'mobile', 2),
  contact_mobile: deep('contact', 'mobile', 2),
  single_article_mobile: deep('single_article', 'mobile', 2),
  single_article_desktop: deep('single_article', 'desktop', 2),
  home_desktop: deep('home', 'desktop', 2),
  home_mobile: deep('home', 'mobile', 2),
};

const fetchTimeline = [];
for (const [key, block] of Object.entries(matrix)) {
  for (const r of block.runs) {
    if (r.fetchTime) {
      fetchTimeline.push({
        key,
        run: r.run,
        fetchTime: r.fetchTime,
        benchmarkIndex: r.benchmarkIndex,
      });
    }
  }
}
fetchTimeline.sort((a, b) => a.fetchTime.localeCompare(b.fetchTime));

const closePairs = [];
for (let i = 0; i < fetchTimeline.length; i++) {
  for (let j = i + 1; j < fetchTimeline.length; j++) {
    const dt = Math.abs(
      Date.parse(fetchTimeline[j].fetchTime) -
        Date.parse(fetchTimeline[i].fetchTime),
    );
    if (dt < 2000 && fetchTimeline[i].key !== fetchTimeline[j].key) {
      closePairs.push({ a: fetchTimeline[i], b: fetchTimeline[j], dtMs: dt });
    }
  }
}

const allVersions = Object.values(matrix).flatMap((b) =>
  b.runs.map((r) => r.lighthouseVersion).filter(Boolean),
);
const allChrome = Object.values(matrix).flatMap((b) =>
  b.runs.map((r) => r.chromeVersionFromUA).filter(Boolean),
);

const versionConsistency = {
  lighthouseVersions: [...new Set(allVersions)],
  chromeVersions: [...new Set(allChrome)],
  nodeAtExtraction: process.version,
  lighthouse13RequiresNode: '>=22.19',
  usedLighthouse: '12.2.1',
  insightKeysNote:
    'LH13 insight audits (cls-culprits-insight, lcp-discovery-insight, lcp-phases-insight, image-delivery-insight) are absent in LH12.2.1 — expected per https://developer.chrome.com/blog/lighthouse-13-0 — not an extractor failure.',
};

fs.writeFileSync(
  path.join(OUT, 'validity-matrix.json'),
  JSON.stringify({ toolMeta, versionConsistency, matrix }, null, 2),
);
fs.writeFileSync(
  path.join(OUT, 'deep-dives.json'),
  JSON.stringify(deepDives, null, 2),
);
fs.writeFileSync(
  path.join(OUT, 'checksums-raw-lhr.json'),
  JSON.stringify({ rawDir: RAW, files: checksums }, null, 2),
);
fs.writeFileSync(
  path.join(OUT, 'fetch-timeline.json'),
  JSON.stringify(
    {
      overlapSuspect: closePairs.length > 0,
      closePairsWithin2s: closePairs,
      timeline: fetchTimeline,
      interpretation:
        'Sequential batch expected. closePairsWithin2s>0 would suggest concurrent CPU contention.',
    },
    null,
    2,
  ),
);

console.log(
  JSON.stringify(
    {
      keys: Object.keys(matrix).length,
      versionConsistency,
      overlapSuspect: closePairs.length > 0,
      sample: Object.fromEntries(
        Object.entries(matrix).map(([k, v]) => [
          k,
          {
            lcpValid: v.validity.lcp.validCount,
            medianLcp: v.mediansValidOnly.lcp_ms,
            medianPerf: v.mediansValidOnly.performance,
            lcpReasons: v.validity.lcp.invalidReasons,
          },
        ]),
      ),
    },
    null,
    2,
  ),
);
