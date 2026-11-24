const files = [
  '/wp-content/themes/skybook/assets/css/skybook-01.css',
  '/wp-content/themes/woovina/assets/css/style.min.css',
  '/wp-content/themes/woovina/assets/css/woo/woocommerce.min.css',
  '/wp-content/uploads/arap-fonts/fa-all.min.css',
];
(async () => {
  for (const f of files) {
    const res = await fetch('https://arap-library.de' + f, {
      headers: { 'Accept-Encoding': 'br, gzip, deflate' },
    });
    const txt = await res.text();
    const interesting = {};
    for (const [k, v] of res.headers.entries()) {
      if (/cache|server|vary|encoding|accept-ranges|etag|last-modified|content-length|x-cach|age|via|cf-cache/i.test(k)) interesting[k] = v;
    }
    console.log(f);
    console.log('  size', txt.length, '| encoding', res.headers.get('content-encoding'), '| status', res.status);
    console.log('  headers', JSON.stringify(interesting));
  }
})();
