(function () {
  'use strict';
  var $ = function (id) { return document.getElementById(id); };
  var root = window.akfApp && window.akfApp.root ? window.akfApp.root : '';
  var TOKEN_KEY = 'akfGateToken';
  var token = localStorage.getItem(TOKEN_KEY) || '';
  var stream = null;
  var scanning = false;
  var lastCode = '';
  var lastAt = 0;
  var canUndo = false;
  var userName = '';

  function setOffline(off) {
    $('offline').hidden = !off;
    var retry = $('retry');
    if (retry) { retry.hidden = !off; }
    $('check').disabled = !!off;
    $('undo').disabled = !!off || !canUndo;
  }
  // navigator.onLine is unreliable in installed PWAs on Android. Never block a
  // request because of it: only a failed fetch means offline.
  function online() { return true; }
  window.addEventListener('online', function () { retryOnline(); });
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) { retryOnline(); }
  });

  function showLogin(err) {
    $('login').hidden = false;
    $('scan').hidden = true;
    $('logout').hidden = true;
    if (err) { $('login-error').textContent = err; $('login-error').hidden = false; }
  }

  function retryOnline() {
    if (!token) { return; }
    fetch(root + 'me', { headers: { 'X-AKF-Token': token }, cache: 'no-store' })
      .then(function (r) { if (r.ok) { setOffline(false); } })
      .catch(function () {});
  }

  function api(action, payload, cb) {
    var headers = { 'Content-Type': 'application/json', 'X-AKF-Token': token };
    fetch(root + action, {
      method: payload === null ? 'GET' : 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: headers,
      body: payload === null ? undefined : JSON.stringify(payload)
    }).then(function (r) {
      if (r.status === 401) { throw new Error('SESSION'); }
      return r.json().then(function (d) { return { status: r.status, data: d }; });
    }).then(function (res) {
      setOffline(false);
      if (res.data && res.data.code === 'akf_app_unauthorized') { throw new Error('SESSION'); }
      cb(res.data);
    }).catch(function (e) {
      if (String(e && e.message) === 'SESSION') {
        localStorage.removeItem(TOKEN_KEY);
        token = '';
        showLogin('Session expired. Please sign in again.');
        return;
      }
      // Only a real network failure means offline. A server error must not
      // disable admission or leave a stale offline banner on screen.
      if (e instanceof TypeError) {
        setOffline(true);
      } else {
        showResult({ result: 'error', message: 'Server error. Try again.' });
      }
    });
  }

  /** Accept either a raw token or the full verification URL printed in the QR. */
  function normalizeCode(raw) {
    var v = String(raw || '').trim();
    if (!v) { return ''; }
    try {
      var u = new URL(v);
      var c = u.searchParams.get('code');
      if (c) { v = c; }
    } catch (e) {}
    // Chat apps and keyboards can inject zero-width characters, line breaks,
    // backticks or spaces. Keep exactly the 64 hex characters of the token.
    var hex = v.replace(/[^a-fA-F0-9]/g, '').toLowerCase();
    return hex.length === 64 ? hex : v;
  }

  function showResult(data) {
    var el0 = $('result');
    if (el0 && el0.scrollIntoView) { el0.scrollIntoView({ block: 'center' }); }
    var map = {
      valid: 'ok', already_used: 'bad', invalid: 'bad',
      cancelled: 'bad', refunded: 'bad', wrong_event: 'warn', error: 'warn'
    };
    var el = $('result');
    el.className = 'result ' + (map[data.result] || 'warn');
    var text = (data.message || '');
    if (data.ticket) {
      text += '\n' + (data.ticket.name || '') + ' · ' + (data.ticket.number || '') +
        ' · ' + (data.ticket.persons || 0) + ' pers.';
    }
    el.textContent = text;
    var line = new Date().toLocaleTimeString() + '  ' + (data.result || 'error');
    $('log').textContent = line + '\n' + $('log').textContent;
  }

  function gate() { return parseInt($('gate').value, 10) || 0; }

  function checkToken(raw) {
    var code = normalizeCode(raw);
    if (!code) { return; }
    $('token').value = code;
    api('validate', { code: code, gate: gate() }, function (data) {
      if (data && data.result === 'valid') {
        // Admission is granted only by the server's atomic check-in.
        api('checkin', { code: code, gate: gate(), persons: data.ticket ? data.ticket.persons : 0 }, showResult);
      } else {
        showResult(data);
      }
    });
  }

  function loadGates() {
    api('gates', null, function (data) {
      if (!data || !data.gates) { return; }
      var sel = $('gate');
      (data.gates).forEach(function (g) {
        var o = document.createElement('option');
        o.value = g.id; o.textContent = g.name;
        sel.appendChild(o);
      });
    });
  }

  function scanFrame() {
    if (!scanning) { return; }
    var video = $('video');
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
      var c = $('canvas');
      c.width = video.videoWidth; c.height = video.videoHeight;
      var ctx = c.getContext('2d');
      ctx.drawImage(video, 0, 0, c.width, c.height);
      var found = null;
      if (typeof window.jsQR === 'function') {
        var img = ctx.getImageData(0, 0, c.width, c.height);
        var q = window.jsQR(img.data, img.width, img.height);
        if (q) { found = q.data; }
      }
      if (!found && window.BarcodeDetector) {
        try {
          var d = new BarcodeDetector({ formats: ['code_128'] });
          d.detect(video).then(function (codes) { if (codes.length) { handleCode(codes[0].rawValue); } }).catch(function () {});
        } catch (e) {}
      }
      if (found) { handleCode(found); }
    }
    requestAnimationFrame(scanFrame);
  }

  function handleCode(code) {
    var now = Date.now();
    if (code === lastCode && now - lastAt < 4000) { return; }
    lastAt = now; lastCode = code;
    var el = $('result');
    el.className = 'result warn';
    el.textContent = 'QR erkannt — wird geprüft …';
    checkToken(code);
  }

  function startCamera(silent) {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      if (!silent) { showResult({ result: 'error', message: 'Camera API unavailable' }); }
      return;
    }
    navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 } }, audio: false
    }).then(function (s) {
      stream = s;
      $('video').srcObject = s;
      return $('video').play().then(function () {
        scanning = true;
        $('start').hidden = true; $('stop').hidden = false;
        scanFrame();
        var caps = s.getVideoTracks()[0].getCapabilities ? s.getVideoTracks()[0].getCapabilities() : {};
        if (caps.torch) { $('torch').hidden = false; }
      });
    }).catch(function () {
      if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
      $('video').srcObject = null;
      $('start').hidden = false;
      $('stop').hidden = true;
      if (!silent) {
        showResult({ result: 'error', message: 'Camera permission denied' });
      } else {
        var el = $('result');
        el.className = 'result warn';
        el.textContent = 'Tap Start camera to enable scanning / اضغط Start camera لتشغيل الكاميرا';
      }
    });
  }

  function stopCamera() {
    scanning = false;
    if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
    $('video').srcObject = null;
    $('start').hidden = false; $('stop').hidden = true; $('torch').hidden = true;
  }

  function entered(me) {
    userName = me.name || '';
    canUndo = !!me.can_undo;
    $('logout').hidden = false;
    $('scan').hidden = false;
    $('login').hidden = true;
    $('undo').hidden = !canUndo;
    setOffline(!online());
    if (me.gate) { $('gate').value = String(me.gate); }
    loadGates();
    // Behave like a phone camera: start scanning as soon as the staff member
    // is signed in. Silent failure avoids an error banner on page reloads.
    startCamera(true);
    if ('serviceWorker' in navigator && window.akfApp && window.akfApp.sw) {
      navigator.serviceWorker.register(window.akfApp.sw, { scope: '/akf-scan/' }).catch(function () {});
    }
  }

  $('signin').addEventListener('click', function () {
    var u = $('user').value.trim(), p = $('pass').value;
    if (!u || !p) { return; }
    $('login-error').hidden = true;
    fetch(root + 'login', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: u, password: p })
    }).then(function (r) {
      return r.json().then(function (d) { return { status: r.status, data: d }; });
    }).then(function (res) {
      if (res.status !== 200 || !res.data.token) {
        showLogin((res.data && res.data.message) || 'Sign in failed.');
        return;
      }
      token = res.data.token;
      localStorage.setItem(TOKEN_KEY, token);
      $('pass').value = '';
      entered({ name: res.data.name, can_undo: res.data.can_undo, gate: 0 });
    }).catch(function () { showLogin('Network error.'); });
  });

  $('logout').addEventListener('click', function () {
    localStorage.removeItem(TOKEN_KEY);
    token = ''; showLogin(); stopCamera();
  });
  $('start').addEventListener('click', startCamera);
  $('stop').addEventListener('click', stopCamera);
  $('torch').addEventListener('click', function () {
    if (!stream) { return; }
    var tr = stream.getVideoTracks()[0];
    var c = tr.getCapabilities ? tr.getCapabilities() : {};
    if (!c.torch) { return; }
    tr.applyConstraints({ advanced: [{ torch: !tr.getSettings().torch }] }).catch(function () {});
  });
  $('check').addEventListener('click', function () {
    var v = $('token').value.trim();
    if (v) { checkToken(v); }
  });
  // On phones, pressing Enter on the keyboard or pasting a token should be
  // enough — the Check button can sit below the on-screen keyboard.
  $('token').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      var v = $('token').value.trim();
      if (v) { checkToken(v); }
    }
  });
  var pasteTimer = null;
  $('token').addEventListener('input', function () {
    clearTimeout(pasteTimer);
    var v = normalizeCode($('token').value);
    if (/^[a-f0-9]{64}$/i.test(v)) {
      pasteTimer = setTimeout(function () { checkToken(v); }, 400);
    }
  });

  // A stale offline flag must clear itself as soon as the server answers.
  setInterval(function () {
    if ($('offline').hidden || !token) { return; }
    retryOnline();
  }, 5000);
  var retryBtn = $('retry');
  if (retryBtn) { retryBtn.addEventListener('click', retryOnline); }
  $('undo').addEventListener('click', function () {
    var reason = window.prompt('Reason for undo (required):');
    if (!reason) { return; }
    api('undo', { code: normalizeCode($('token').value), reason: reason }, showResult);
  });

  if (token) {
    api('me', null, entered);
  } else {
    showLogin();
  }
})();
