const target = 'https://arap-library.de/?psi=' + Date.now();
const api = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' +
  encodeURIComponent(target) + '&strategy=mobile&category=performance';

(async () => {
  const res = await fetch(api);
  const text = await res.text();
  let json;
  try { json = JSON.parse(text); } catch (e) {
    console.log('HTTP', res.status, 'non-json', text.slice(0, 300));
    process.exit(1);
  }
  if (!json.lighthouseResult) {
    console.log('no lighthouseResult', JSON.stringify(json).slice(0, 500));
    process.exit(1);
  }
  const lr = json.lighthouseResult;
  const a = lr.audits || {};
  console.log('finalUrl:', lr.finalDisplayedUrl || lr.finalUrl);
  console.log('perfScore:', lr.categories.performance.score);
  console.log('LCP:', a['largest-contentful-paint'] && a['largest-contentful-paint'].displayValue);
  console.log('CLS:', a['cumulative-layout-shift'] && a['cumulative-layout-shift'].displayValue);
  const fontKeys = Object.keys(a).filter(k => /font/i.test(k));
  console.log('font audit keys:', fontKeys.join(', '));
  for (const k of fontKeys) {
    const au = a[k];
    console.log('---', k, '|', au.title, '| score', au.score);
    if (au.details && au.details.items) {
      console.log(JSON.stringify(au.details.items).slice(0, 1200));
    }
  }
  const woffItems = [];
  for (const k of Object.keys(a)) {
    const au = a[k];
    const s = JSON.stringify(au.details || {});
    if (s.includes('.woff')) woffItems.push(k);
  }
  console.log('audits mentioning woff:', woffItems.join(', '));
})();
