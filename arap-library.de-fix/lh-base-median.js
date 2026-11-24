const fs = require('fs');
function get(f) {
  const r = JSON.parse(fs.readFileSync('D:/oben code/arap-library.de-fix/' + f, 'utf8'));
  const a = r.audits;
  const rb = a['render-blocking-insight'];
  return { file:f, fcp:a['first-contentful-paint'].numericValue, lcp:a['largest-contentful-paint'].numericValue, tbt:a['total-blocking-time'].numericValue, cls:a['cumulative-layout-shift'].numericValue, rb:(rb.details.items||[]).length, fcpD:a['first-contentful-paint'].displayValue, lcpD:a['largest-contentful-paint'].displayValue, tbtD:a['total-blocking-time'].displayValue, clsD:a['cumulative-layout-shift'].displayValue };
}
function median(arr){ const s=[...arr].sort((a,b)=>a-b); return s[Math.floor(s.length/2)]; }
const runs = ['lh-base-r1.json','lh-base-r2.json','lh-base-r3.json'].map(get);
runs.forEach(r=>console.log(r));
console.log('MEDIAN', { rb: median(runs.map(r=>r.rb)), fcp: median(runs.map(r=>r.fcp)), lcp: median(runs.map(r=>r.lcp)), tbt: median(runs.map(r=>r.tbt)), cls: median(runs.map(r=>r.cls)) });
