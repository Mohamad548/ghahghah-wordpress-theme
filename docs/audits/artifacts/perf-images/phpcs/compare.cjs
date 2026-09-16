const fs = require('fs');
const { execSync } = require('child_process');

function load(p) {
  return JSON.parse(fs.readFileSync(p, 'utf8'));
}

const base = load('docs/audits/artifacts/perf-images/phpcs/base-report.json');
const head = load('docs/audits/artifacts/perf-images/phpcs/head-report.json');

function normPath(p) {
  return p.replace(/\\/g, '/').replace(/^.*ghahghah-theme\//, 'ghahghah-theme/');
}

function msgs(report) {
  const out = [];
  for (const [file, data] of Object.entries(report.files || {})) {
    for (const m of data.messages || []) {
      out.push({
        file: normPath(file),
        line: m.line,
        column: m.column,
        severity: m.severity,
        type: m.type,
        source: m.source,
        message: m.message,
      });
    }
  }
  return out;
}

const b = msgs(base);
const h = msgs(head);
const key = (m) => `${m.file}|${m.source}|${m.message}|${m.type}`;
const keyLine = (m) => `${m.file}:${m.line}|${m.source}|${m.type}`;
const bKeys = new Set(b.map(key));
const hKeys = new Set(h.map(key));
const bLine = new Set(b.map(keyLine));
const hLine = new Set(h.map(keyLine));
const addedByContent = [...hKeys].filter((k) => !bKeys.has(k));
const removedByContent = [...bKeys].filter((k) => !hKeys.has(k));
const addedByLine = [...hLine].filter((k) => !bLine.has(k));
const removedByLine = [...bLine].filter((k) => !hLine.has(k));
const hByLine = new Map(h.map((m) => [keyLine(m), m]));
const addedMsgs = addedByLine.map((k) => hByLine.get(k)).filter(Boolean);

const diff = execSync(
  'git diff 7add574 def0c87 --unified=0 -- ghahghah-theme/inc/contact-page-settings.php ghahghah-theme/inc/media/sync-theme-media.php ghahghah-theme/inc/request-pages-settings.php ghahghah-theme/inc/single-article-settings.php ghahghah-theme/single-post.php',
  { encoding: 'utf8' },
);
const changed = new Map();
let cur = null;
for (const line of diff.split(/\r?\n/)) {
  const mf = line.match(/^diff --git a\/(.*?) b\/(.*)$/);
  if (mf) {
    cur = mf[2];
    if (!changed.has(cur)) changed.set(cur, new Set());
    continue;
  }
  const hh = line.match(/^@@ -\d+(?:,\d+)? \+(\d+)(?:,(\d+))? @@/);
  if (hh && cur) {
    const start = +hh[1];
    const len = hh[2] === undefined ? 1 : +hh[2];
    for (let i = 0; i < len; i++) changed.get(cur).add(start + i);
  }
}

const newDebt = [];
const preExistingOnChangedLines = [];
for (const m of h) {
  const lines = changed.get(m.file);
  if (!lines || !lines.has(m.line)) continue;
  const existedSame = b.some(
    (x) =>
      x.file === m.file && x.source === m.source && x.type === m.type && x.message === m.message,
  );
  if (existedSame) preExistingOnChangedLines.push(m);
  else newDebt.push(m);
}

const summary = {
  phpcsVersion: '3.13.6',
  baseTotals: base.totals,
  headTotals: head.totals,
  baseMsgCount: b.length,
  headMsgCount: h.length,
  addedByExactContentCount: addedByContent.length,
  removedByExactContentCount: removedByContent.length,
  addedByFileLineSourceCount: addedByLine.length,
  removedByFileLineSourceCount: removedByLine.length,
  newDebtOnChangedLines: newDebt,
  preExistingSniffOnChangedLines: preExistingOnChangedLines.slice(0, 20),
  newDebtCount: newDebt.length,
  sampleAddedByLine: addedMsgs.slice(0, 30),
};

fs.writeFileSync(
  'docs/audits/artifacts/perf-images/phpcs/compare-summary.json',
  JSON.stringify(summary, null, 2),
);

const plain = [];
plain.push('PHPCS Base vs HEAD (changed theme PHP only)');
plain.push(`PHPCS version: 3.13.6`);
plain.push(`Base totals: errors=${base.totals.errors} warnings=${base.totals.warnings}`);
plain.push(`HEAD totals: errors=${head.totals.errors} warnings=${head.totals.warnings}`);
plain.push(`Messages base=${b.length} head=${h.length}`);
plain.push(`New debt on changed lines (sniff+message not in base set): ${newDebt.length}`);
for (const m of newDebt) {
  plain.push(`NEW ${m.type} ${m.file}:${m.line} ${m.source} :: ${m.message}`);
}
plain.push(`Added file:line:source keys: ${addedByLine.length}`);
plain.push(`Removed file:line:source keys: ${removedByLine.length}`);
plain.push('NOTE: Equal error counts do not prove no new debt; see NEW lines above.');
plain.push(
  `Overall status: FAIL (baseline debt present). New debt: ${newDebt.length === 0 ? 'NONE' : 'YES'}`,
);
fs.writeFileSync('docs/audits/artifacts/perf-images/phpcs/compare.txt', plain.join('\n') + '\n');
console.log(plain.join('\n'));
