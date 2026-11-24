const {execFileSync} = require('child_process');
const fs = require('fs');
function curl(url){
  const args=['-s','-o','NUL','-D','-','-w','%{http_code} %{time_namelookup} %{time_connect} %{time_appconnect} %{time_starttransfer} %{time_total}','-A','ArapTTFBProbe/1.0',url];
  const out = execFileSync('curl.exe', args, {encoding:'utf8', timeout:30000});
  const lines = out.split(/\r?\n/);
  const last = lines.filter(Boolean).slice(-1)[0] || '';
  const parts = last.trim().split(/\s+/);
  const [code,dns,conn,tls,ttfb,total] = parts;
  const headers = {};
  for(const l of lines){
    const m = l.match(/^([^:]+):\s*(.*)$/);
    if(m) headers[m[1].toLowerCase()] = m[2].trim();
  }
  return {
    code:+code, dns:+dns, conn:+conn, tls:+tls, ttfb:+ttfb, total:+total,
    cache: headers['x-cache']||headers['x-wp-super-cache']||headers['cf-cache-status']||headers['x-cache-status']||'',
    cc: headers['cache-control']||'',
    age: headers['age']||'',
    powered: headers['x-powered-by']||'',
    server: headers['server']||'',
    vary: headers['vary']||''
  };
}
async function sleep(ms){ return new Promise(r=>setTimeout(r,ms)); }
(async()=>{
  const types=[
    ['normal','https://arap-library.de/'],
    ['warm','https://arap-library.de/'],
    ['gate','https://arap-library.de/?arap_rb=1&arap_rb_key=stg9x2'],
    ['uncached_param','https://arap-library.de/?ttfbprobe=' + Date.now()],
  ];
  const res={};
  // warm up normal
  curl('https://arap-library.de/');
  await sleep(1500);
  for(const [name,url] of types){
    res[name]=[];
    for(let i=0;i<5;i++){
      const r=curl(url);
      res[name].push(r);
      await sleep(800);
    }
  }
  fs.writeFileSync('D:/oben code/arap-library.de-fix/ttfb-results.json', JSON.stringify(res,null,2));
  console.log(JSON.stringify(res));
})();