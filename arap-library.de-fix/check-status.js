(async () => {
  const res = await fetch('https://arap-library.de/?st=' + Date.now(), { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
  const h = await res.text();
  const preloads = [...h.matchAll(/<link[^>]*rel=['"]preload[^>]*>/g)].map(m => m[0]);
  console.log('preloads', preloads.length);
  preloads.forEach(p => console.log(' ', p.slice(0, 180)));
  console.log('defer', (h.match(/\bdefer\b/g) || []).length);
  console.log('magnific', h.includes('magnific-popup.min.css'));
  console.log('animate-third', h.includes('third/animate.css'));
  console.log('cache', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0].slice(0,80));
})();
