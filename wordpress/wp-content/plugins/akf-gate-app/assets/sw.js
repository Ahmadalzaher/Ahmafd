/* App shell only. API responses are never cached: offline admission stays disabled. */
var CACHE = 'akf-gate-app-v7';
var SHELL = [
  '/akf-scan/',
  '/wp-content/plugins/akf-gate-app/assets/app.css',
  '/wp-content/plugins/akf-gate-app/assets/app.js',
  '/wp-content/plugins/akf-gate-app/assets/jsqr.js'
];
self.addEventListener('install', function (e) {
  self.skipWaiting();
  e.waitUntil(caches.open(CACHE).then(function (c) { return c.addAll(SHELL); }));
});
self.addEventListener('activate', function (e) {
  e.waitUntil(caches.keys().then(function (keys) {
    return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
  }).then(function () { return self.clients.claim(); }));
});
self.addEventListener('fetch', function (e) {
  var url = new URL(e.request.url);
  if (url.pathname.indexOf('/wp-json/') !== -1) { return; }
  if (e.request.method !== 'GET') { return; }
  e.respondWith(fetch(e.request).then(function (res) {
    if (res.ok) {
      var copy = res.clone();
      caches.open(CACHE).then(function (c) { c.put(e.request, copy); });
    }
    return res;
  }).catch(function () { return caches.match(e.request); }));
});
