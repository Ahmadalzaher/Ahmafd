const fs = require('fs');
(async () => {
  const res = await fetch('https://arap-library.de/?plain=' + Date.now(), { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36' } });
  const h = await res.text();
  fs.writeFileSync('D:/oben code/arap-library.de-fix/htmlplain.html', h);
  console.log('status', res.status, 'len', h.length);
  console.log('hasMagnificCss', h.includes('magnific-popup.min.css'));
  console.log('hasAnimateCss', h.includes('third/animate.css'));
  const m = h.match(/<link[^>]*magnific[^>]*>/g) || [];
  console.log('magnific links:', m);
  const a = h.match(/<link[^>]*animate[^>]*>/g) || [];
  console.log('animate links:', a);
})();
