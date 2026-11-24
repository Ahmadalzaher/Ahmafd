const http = require('http');
const fs = require('fs');
const path = require('path');

const OUT = path.join(__dirname, 'live-backup');
fs.mkdirSync(OUT, { recursive: true });

const server = http.createServer((req, res) => {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Headers', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
  if (req.method === 'OPTIONS') { res.writeHead(204); res.end(); return; }

  if (req.method === 'GET') {
    res.writeHead(200, { 'Content-Type': 'text/plain' });
    res.end('ok');
    return;
  }

  if (req.method === 'POST' && req.url.startsWith('/save')) {
    const url = new URL(req.url, 'http://x');
    const name = (url.searchParams.get('name') || 'dump.txt').replace(/[^a-zA-Z0-9._-]/g, '_');
    let body = '';
    req.setEncoding('utf8');
    req.on('data', c => { body += c; });
    req.on('end', () => {
      const target = path.join(OUT, name);
      fs.writeFileSync(target, body, 'utf8');
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ ok: true, name, bytes: Buffer.byteLength(body, 'utf8'), target }));
    });
    return;
  }

  res.writeHead(404); res.end('nope');
});

server.listen(8099, '127.0.0.1', () => console.log('backup server on 8099'));
