const fs = require('fs');
const h = fs.readFileSync('D:/oben code/arap-library.de-fix/htmlcheck.html', 'utf8');
const scripts = [...h.matchAll(/<script[^>]*\ssrc=['"]([^'"]+)['"][^>]*>/g)].map(m => ({ src: m[1].split('arap-library.de')[1] || m[1].slice(-60), defer: /\bdefer\b/.test(m[0]), async: /\basync\b/.test(m[0]) }));
const nonDef = scripts.filter(s => !s.defer);
console.log('total script tags:', scripts.length);
console.log('deferred:', scripts.filter(s => s.defer).length);
console.log('non-deferred front:', nonDef.filter(s => /(arap-library\.de|^)/.test(s.src) || !/\(\w\)/.test(s.src)).slice(0, 40).map(s => s.src));
console.log('jquery tags:', scripts.filter(s => /jquery/.test(s.src)).map(s => ({ src: s.src.slice(-30), defer: s.defer })));
