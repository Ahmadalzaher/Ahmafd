(async () => {
  const res = await fetch('https://arap-library.de/?preloadcheck=' + Date.now(), { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
  const h = await res.text();
  const preloads = [...h.matchAll(/<link[^>]*rel=['"]preload[^>]*>/g)].map(m => m[0].slice(0, 170));
  console.log('preload hints:', preloads.length);
  preloads.forEach(p => console.log('  ', p));
  console.log('hasMagnific', h.includes('magnific'), '| hasAnimate', h.includes('third/animate.css'));
})();
