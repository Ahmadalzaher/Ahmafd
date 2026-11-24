(async () => {
  const res = await fetch('https://arap-library.de/', { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
  const h = await res.text();
  console.log('status', res.status, 'len', h.length);
  console.log('hasMagnificCss', h.includes('magnific-popup.min.css'));
  console.log('hasAnimateCss', h.includes('third/animate.css'));
  console.log('cache comment', (h.match(/<!-- Cached page generated[^>]*-->/) || ['none'])[0]);
  console.log('defer count', (h.match(/\bdefer\b/g) || []).length);
  const m = h.match(/<link[^>]*magnific[^>]*>/g) || [];
  console.log('magnific tags', m);
})();
