const fs = require('fs');
const urls = [
  'https://arap-library.de/?probe_a=' + Date.now(),
  'https://arap-library.de/?probe_b=' + Date.now(),
  'https://arap-library.de/',
];
(async () => {
  for (const u of urls) {
    const res = await fetch(u, { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
    const h = await res.text();
    const faceAt = h.indexOf('arap-font-display');
    const dataAt = h.indexOf('data:text/css');
    const swapAt = h.indexOf('font-display:swap');
    const blockAt = h.indexOf('font-display:block');
    console.log(u, '=> status', res.status, 'len', h.length, '| faceOverride', faceAt, '| dataUri', dataAt, '| swap', swapAt, '| block', blockAt, '| cache', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0]);
  }
})();
