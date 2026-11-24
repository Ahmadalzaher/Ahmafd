const fs = require('fs');
function get(f) {
  const r = JSON.parse(fs.readFileSync('D:/oben code/arap-library.de-fix/' + f, 'utf8'));
  const a = r.audits;
  const rb = a['render-blocking-insight'];
  const lt = a['long-tasks'];
  return {
    file:f,
    rb:(rb.details.items||[]).length,
    fcp: a['first-contentful-paint'].numericValue,
    lcp: a['largest-contentful-paint'].numericValue,
    tbt: a['total-blocking-time'].numericValue,
    cls: a['cumulative-layout-shift'].numericValue,
    ttfb: a['server-response-time'] ? a['server-response-time'].numericValue : null,
    longTasks: lt && lt.details && lt.details.items ? lt.details.items.length : (lt && lt.numericValue) || 0,
    fcpD:a['first-contentful-paint'].displayValue, lcpD:a['largest-contentful-paint'].displayValue, tbtD:a['total-blocking-time'].displayValue
  };
}
function median(arr){ const s=[...arr].sort((a,b)=>a-b); return s[Math.floor(s.length/2)]; }
const files = process.argv.slice(2);
const runs = files.map(get);
runs.forEach(r=>console.log(r));
console.log('MEDIAN', { rb: median(runs.map(r=>r.rb)), fcp: median(runs.map(r=>r.fcp)), lcp: median(runs.map(r=>r.lcp)), tbt: median(runs.map(r=>r.tbt)), cls: median(runs.map(r=>r.cls)), ttfb: median(runs.map(r=>r.ttfb||0)), longTasks: median(runs.map(r=>r.longTasks||0)) });
