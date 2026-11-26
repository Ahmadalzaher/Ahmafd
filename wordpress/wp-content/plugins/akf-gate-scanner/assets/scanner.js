(function () {
  'use strict';
  var video = document.getElementById('akf-video');
  var canvas = document.getElementById('akf-canvas');
  var result = document.getElementById('akf-result');
  var log = document.getElementById('akf-log');
  var tokenInput = document.getElementById('akf-token');
  var offlineBanner = document.getElementById('akf-offline');
  var stream = null;
  var scanning = false;
  var lastCode = '';
  var lastAt = 0;

  function cfg() { return window.akfScanner || {}; }
  function i18n(k) { var d = cfg().i18n || {}; return d[k] || k; }
  function root() { return cfg().root || ''; }
  function nonce() { return cfg().nonce || ''; }

  function online() { return navigator.onLine !== false; }
  function setOffline() {
    offlineBanner.hidden = false;
    document.getElementById('akf-check').disabled = true;
    var u = document.getElementById('akf-undo');
    if (u) { u.disabled = true; }
    result.className = 'akf-result akf-yellow';
    result.textContent = i18n('offline');
  }
  function setOnline() {
    offlineBanner.hidden = true;
    document.getElementById('akf-check').disabled = false;
    var u = document.getElementById('akf-undo');
    if (u) { u.disabled = false; }
  }
  window.addEventListener('offline', setOffline);
  window.addEventListener('online', setOnline);
  if (!online()) { setOffline(); }

  function api(action, payload, cb) {
    if (!online()) { setOffline(); return; }
    fetch(root() + action, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce() },
      body: JSON.stringify(payload || {})
    }).then(function (r) { return r.json(); }).then(cb).catch(function () { setOffline(); });
  }

  function show(data) {
    var map = {
      valid: 'akf-green', already_used: 'akf-red', invalid: 'akf-red',
      cancelled: 'akf-red', refunded: 'akf-red', wrong_event: 'akf-yellow',
      error: 'akf-yellow'
    };
    result.className = 'akf-result ' + (map[data.result] || 'akf-yellow');
    var text = data.message || '';
    if (data.ticket) {
      text += ' — ' + (data.ticket.name || '') + ' · ' + (data.ticket.number || '') +
        ' · ' + (data.ticket.persons || 0);
    }
    result.textContent = text;
    log.textContent = (new Date()).toLocaleTimeString() + '  ' + data.result + '\n' + log.textContent;
  }

  function gate() { return parseInt(document.getElementById('akf-gate').value, 10) || 0; }

  function checkToken(code) {
    lastCode = code;
    api('validate', { code: code, gate: gate() }, function (data) {
      if (data && data.result === 'valid') {
        // Admission is only granted after the server's atomic check-in.
        api('checkin', {
          code: code,
          gate: gate(),
          persons: data.ticket ? data.ticket.persons : 0
        }, show);
      } else {
        show(data);
      }
    });
  }

  function loadGates() {
    fetch(root() + 'gates', {
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': nonce() }
    }).then(function (r) { return r.json(); }).then(function (data) {
      var sel = document.getElementById('akf-gate');
      if (!sel || !data || !data.gates) { return; }
      data.gates.forEach(function (g) {
        var o = document.createElement('option');
        o.value = g.id;
        o.textContent = g.name;
        sel.appendChild(o);
      });
    }).catch(function () {});
  }

  function scanFrame() {
    if (!scanning || !online()) { return; }
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      var ctx = canvas.getContext('2d');
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      var img = ctx.getImageData(0, 0, canvas.width, canvas.height);
      var found = null;
      if (typeof window.jsQR === 'function') {
        var qr = JsQRQR(img.data, img.width, img.height);
        if (qr) { found = qr.data; }
      }
      if (!found && window.BarcodeDetector) {
        // Native Code128 detection where supported.
        try {
          var detector = new BarcodeDetector({ formats: ['code_128'] });
          detector.detect(video).then(function (codes) {
            if (codes.length) { handleCode(codes[0].rawValue); }
          }).catch(function () {});
        } catch (e) {}
      }
      if (found) { handleCode(found); }
    }
    requestAnimationFrame(scanFrame);
  }

  function JsQRQR(data, w, h) {
    try { return window.jsQR(data, w, h); } catch (e) { return null; }
  }

  function handleCode(code) {
    var now = Date.now();
    if (code === lastCode && now - lastAt < 4000) { return; }
    lastAt = now;
    tokenInput.value = code;
    checkToken(code);
  }

  function start() {
    if (!online()) { setOffline(); return; }
    navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }, audio: false
    }).then(function (s) {
      stream = s;
      video.srcObject = s;
      video.play();
      scanning = true;
      scanFrame();
      var track = s.getVideoTracks()[0];
      var caps = track.getCapabilities ? track.getCapabilities() : {};
      if (caps.torch) { document.getElementById('akf-torch').hidden = false; }
    }).catch(function () {
      result.className = 'akf-result akf-red';
      result.textContent = 'Camera permission denied';
    });
  }

  function stop() {
    scanning = false;
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    video.srcObject = null;
  }

  loadGates();
  document.getElementById('akf-start').addEventListener('click', start);
  document.getElementById('akf-stop').addEventListener('click', stop);
  document.getElementById('akf-check').addEventListener('click', function () {
    var v = tokenInput.value.trim();
    if (v) { checkToken(v); }
  });
  var undoBtn = document.getElementById('akf-undo');
  if (undoBtn) {
    undoBtn.addEventListener('click', function () {
      var reason = window.prompt('Reason for undo (required):');
      if (!reason) { return; }
      api('undo', { code: tokenInput.value.trim(), reason: reason }, show);
    });
  }
  var torch = document.getElementById('akf-torch');
  if (torch) {
    torch.addEventListener('click', function () {
      if (!stream) { return; }
      var track = stream.getVideoTracks()[0];
      var c = track.getCapabilities ? track.getCapabilities() : {};
      if (!c.torch) { return; }
      track.applyConstraints({ advanced: [{ torch: !track.getSettings().torch }] }).catch(function () {});
    });
  }
})();
