const fs = require('fs');
const h = fs.readFileSync('D:/oben code/arap-library.de-fix/htmlcheck.html', 'utf8');
const preloads = [...h.matchAll(/<link[^>]*rel=['"]preload['"][^>]*>/gi)].map(m => m[0]);
const links = [...h.matchAll(/<link[^>]*rel=['"]stylesheet['"][^>]*>/gi)].map(m => m[0]);
function href(tag){ const m = tag.match(/href=['"]([^'"]+)['"]/i); return m?m[1]:''; }
console.log('PRELOADS:');
preloads.forEach(p => console.log(' ', href(p), '|', /as=['"]([^'"]+)/i.exec(p)?.[1]));
console.log('MATCHING STYLESHEETS:');
preloads.forEach(p => {
  const hp = href(p);
  const found = links.filter(l => href(l) === hp || href(l).split('?')[0] === hp.split('?')[0]);
  console.log(' preload', hp);
  found.forEach(f => console.log('   -> link', href(f)));
  if (!found.length) console.log('   -> NO MATCH');
});
