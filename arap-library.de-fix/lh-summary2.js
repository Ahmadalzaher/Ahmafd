const r = require('D:/oben code/arap-library.de-fix/lh-before.json');
const a = r.audits;
const keys = Object.keys(a);
console.log('audit keys containing block/render/preload:');
keys.filter(k => /block|render|preload|critical/i.test(k)).forEach(k => {
  const au = a[k];
  console.log(' ', k, '| score', au.score, '| items', JSON.stringify((au.details && au.details.items) ? (Array.isArray(au.details.items) ? au.details.items.length : 'obj') : 0));
});
console.log('');
console.log('FCP', a['first-contentful-paint'].displayValue);
console.log('LCP', a['largest-contentful-paint'].displayValue);
console.log('TBT', a['total-blocking-time'].displayValue);
console.log('network-requests count', (a['network-requests'].details && a['network-requests'].details.items || []).length);
