const fs = require('fs');
const h = fs.readFileSync('D:/oben code/arap-library.de-fix/htmlcheck.html', 'utf8');
const scripts = [...h.matchAll(/<script src=['"]([^'"]+)['"][^>]*>/g)].map(m => m[1].split('arap-library.de')[1] || m[1].slice(-60));
const deferred = scripts.filter(s => /'[^']*defer/.test(s));
console.log('script tags:', scripts.length);
console.log('with defer attr:', deferred.length);
const nonDeferred = scripts.filter(s => !/'defer/.test(s));
console.log('sample non-deferred:', nonDeferred.slice(0, 30).map(s => s.slice(-45)));
