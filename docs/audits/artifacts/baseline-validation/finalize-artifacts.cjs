const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

function redact(file) {
  if (!fs.existsSync(file)) return;
  let s = fs.readFileSync(file, 'utf8');
  s = s.replace(/([?&]key=)[^&"\\]+/gi, '$1REDACTED');
  s = s.replace(/([?&]token=)[^&"\\]+/gi, '$1REDACTED');
  fs.writeFileSync(file, s);
}

redact('docs/audits/artifacts/baseline-validation/deep-dives.json');
redact('docs/audits/artifacts/baseline-validation/lcp-network-enriched.json');

function walk(dir, acc = []) {
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, ent.name);
    if (ent.isDirectory()) walk(p, acc);
    else acc.push(p);
  }
  return acc;
}

const root = 'docs/audits/artifacts/baseline-validation';
const files = walk(root);
const rows = files.map((f) => ({
  path: f.split(path.sep).join('/'),
  bytes: fs.statSync(f).size,
  sha256: crypto.createHash('sha256').update(fs.readFileSync(f)).digest('hex'),
}));
fs.writeFileSync(
  path.join(root, 'ARTIFACT_CHECKSUMS.json'),
  JSON.stringify({ generatedAt: new Date().toISOString(), files: rows }, null, 2),
);
const total = rows.reduce((s, r) => s + r.bytes, 0);
console.log(JSON.stringify({ files: rows.length, totalBytes: total }, null, 2));
rows
  .sort((a, b) => b.bytes - a.bytes)
  .slice(0, 20)
  .forEach((r) => console.log(r.bytes, r.path));
