const fs = require('fs');
(async () => {
  const targets = [
    'https://arap-library.de/?probe_c=' + Date.now(),
    'https://arap-library.de/?probe_d=' + Date.now(),
  ];
  for (const u of targets) {
    const res = await fetch(u, { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
    const h = await res.text();
    console.log(u, 'len', h.length, 'faceOverride', h.includes('arap-font-display'), 'dataUri', h.includes('data:text/css'), 'cache', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0]);
  }
})();
