const http = require('http');
const fs = require('fs');
const path = require('path');

const ROOT = path.join(__dirname, 'live-backup');

const server = http.createServer((req, res) => {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Headers', '*');
  if (req.method === 'OPTIONS') { res.writeHead(204); res.end(); return; }

  const url = new URL(req.url, 'http://x');
  if (req.method === 'GET' && url.pathname === '/file') {
    const name = (url.searchParams.get('name') || '').replace(/[^a-zA-Z0-9._-]/g, '');
    const target = path.join(ROOT, name);
    if (!name || !fs.existsSync(target)) { res.writeHead(404); res.end('missing'); return; }
    res.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end(fs.readFileSync(target, 'utf8'));
    return;
  }
  if (req.method === 'GET') { res.writeHead(200); res.end('ok'); return; }
  res.writeHead(404); res.end('nope');
});

server.listen(8100, '127.0.0.1', () => console.log('file server on 8100'));
