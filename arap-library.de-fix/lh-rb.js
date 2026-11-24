const r = require('D:/oben code/arap-library.de-fix/lh-before.json');
const ins = r.audits['render-blocking-insight'];
const items = ins.details.items;
console.log('render-blocking-insight items:', items.length, '| estimatedSavingsMs:', ins.metricSavings && ins.metricSavings.FCP || ins.metricSavings);
const byType = {};
items.forEach(it => {
  const kind = it.resourceType || it.type || '?';
  byType[kind] = (byType[kind] || 0) + 1;
});
console.log('by resourceType:', JSON.stringify(byType));
console.log('');
const sorted = items.slice().sort((x, y) => (y.transferSize || 0) - (x.transferSize || 0));
console.log('top by transferSize:');
sorted.slice(0, 20).forEach(it => console.log(' ', (it.url || '').split('arap-library.de')[1] || (it.url || '').slice(0, 80), '| type', it.resourceType, '| size', Math.round((it.transferSize || 0) / 1000) + 'KB'));
