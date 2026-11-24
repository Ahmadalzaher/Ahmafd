const urls = [
  'https://arap-library.de/',
  'https://arap-library.de/?nocache=' + Date.now(),
  'https://arap-library.de/?another=' + Date.now(),
];
(async () => {
  for (const u of urls) {
    const res = await fetch(u, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache',
      },
    });
    const h = await res.text();
    const hdrs = {};
    for (const [k, v] of res.headers.entries()) {
      if (/cache|age|x-|cf-|server|via|litesc|nginx|varnish/i.test(k)) hdrs[k] = v;
    }
    console.log('URL', u);
    console.log('  status', res.status, 'len', h.length);
    console.log('  faceOverride', h.includes('arap-font-display'), '| dataUri', h.includes('data:text/css'), '| foundationCss', h.includes('foundation-icons.min.css'));
    console.log('  cacheComment', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0]);
    console.log('  headers', JSON.stringify(hdrs));
    console.log('');
  }
})();
