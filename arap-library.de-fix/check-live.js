const fs = require('fs');
(async () => {
  const res = await fetch('https://arap-library.de/?srvcheck=' + Date.now(), {
    headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' },
  });
  const h = await res.text();
  fs.writeFileSync('D:/oben code/arap-library.de-fix/srvcheck.html', h);
  console.log('status', res.status, 'len', h.length);
  console.log('hasFaceOverride', h.includes('arap-font-display'));
  console.log('hasDataUri', h.includes('data:text/css'));
  console.log('hasFoundationCss', h.includes('foundation-icons.min.css'));
  console.log('hasSwapFace', h.includes('font-display:swap'));
  console.log('hasBlock', h.includes('font-display:block'));
  console.log('cache', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0]);
})();
