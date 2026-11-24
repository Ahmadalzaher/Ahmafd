const fs = require('fs');
const h = fs.readFileSync('D:/oben code/arap-library.de-fix/htmlplain.html', 'utf8');
const tags = [...h.matchAll(/<link[^>]*magnific[^>]*>/g)].map(m => m[0]);
console.log('magnific link tags:', tags);
console.log('defer count:', (h.match(/\bdefer\b/g) || []).length);
// check whether magnific CSS body is present
console.log('has magnific css content:', h.includes('magnific-image'));
