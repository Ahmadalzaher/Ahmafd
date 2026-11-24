const fs = require('fs');
(async () => {
  const res = await fetch('https://arap-library.de/?htmlcheck=' + Date.now(), { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
  const h = await res.text();
  fs.writeFileSync('D:/oben code/arap-library.de-fix/htmlcheck.html', h);
  console.log('status', res.status, 'len', h.length);
  const m1 = h.match(/<script src="[^"]*main\.min\.js[^"]*"[^>]*>/g) || [];
  console.log('main.min.js tags:', m1);
  const m2 = h.match(/<script src="[^"]*woocommerce\.min\.js[^"]*"[^>]*>/g) || [];
  console.log('woocommerce.min.js tags:', m2);
  const m3 = h.match(/<script src="[^"]*slick\.min\.js[^"]*"[^>]*>/g) || [];
  console.log('slick tags:', m3);
  const m4 = h.match(/<script src="[^"]*frontend\.min\.js[^"]*"[^>]*>/g) || [];
  console.log('elementor frontend tags:', m4.slice(0, 2));
  console.log('hasMagnificCss', h.includes('magnific-popup.min.css'));
  console.log('hasAnimateCss', h.includes('third/animate.css'));
  console.log('count defer=', (h.match(/defer/g) || []).length);
})();
