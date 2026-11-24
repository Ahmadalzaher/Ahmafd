function dump(f) {
  const r = require('D:/oben code/arap-library.de-fix/' + f);
  const a = r.audits;
  console.log('=== ' + f + ' (lh ' + r.lighthouseVersion + ') ===');
  const names = ['first-contentful-paint', 'largest-contentful-paint', 'total-blocking-time', 'cumulative-layout-shift'];
  names.forEach(n => {
    if (a[n]) console.log('  ' + n, a[n].displayValue);
  });
  const rbi = a['render-blocking-insight'];
  if (rbi) {
    console.log('  render-blocking-insight: items=' + (rbi.details.items || []).length + ' | saving=' + JSON.stringify(rbi.metricSavings || null));
  }
  const tbt = a['total-blocking-time'];
  console.log('  FCP', a['first-contentful-paint'].displayValue, '| LCP', a['largest-contentful-paint'].displayValue, '| TBT', a['total-blocking-time'].displayValue, '| CLS', a['cumulative-layout-shift'].displayValue);
  const rbItems = (rbi && rbi.details && rbi.details.items) || [];
  console.log('  render-blocking-insight items:');
  rbItems.slice(0, 40).forEach(it => console.log('    -', (it.url || '').split('arap-library.de')[1] || (it.url || '').slice(-70), '| type', it.resourceType));
  return rbItems;
}
const before = dump('lh-before.json');
const after = dump('lh-after.json');
console.log('render-blocking delta:', (after || []).length, '->', (before || []).length);
