const fs = require('fs');
(async () => {
  const res = await fetch('https://arap-library.de/?cur=' + Date.now(), { headers: { 'User-Agent': 'Mozilla/5.0' } });
  const h = await res.text();
  const preloads = [...h.matchAll(/<link[^>]*rel=['"]preload['"][^>]*>/gi)].map(m => m[0]);
  const links = [...h.matchAll(/<link[^>]*rel=['"]stylesheet['"][^>]*>/gi)].map(m => m[0]);
  function href(tag){ const m = tag.match(/href=['"]([^'"]+)['"]/i); return m?m[1]:''; }
  console.log('PRELOADS:');
  preloads.forEach(p => console.log(' ', href(p), '| as=' + (/as=['"]([^'"]+)/i.exec(p)?.[1]||'?')));
  console.log('STYLESHEET MATCHES:');
  preloads.filter(p=>/as=['"]style/i.test(p)).forEach(p => {
    const hp = href(p);
    const exact = links.filter(l => href(l) === hp);
    const base = links.filter(l => href(l).split('?')[0] === hp.split('?')[0]);
    console.log(' preload', hp);
    if (exact.length) console.log('   EXACT', exact.map(href));
    else if (base.length) console.log('   BASE ONLY', base.map(href));
    else console.log('   NO MATCH');
  });
})();
