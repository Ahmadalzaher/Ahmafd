const https = require('https');
function get(url){return new Promise((res,rej)=>{https.get(url,{headers:{'User-Agent':'Mozilla/5.0'}},r=>{let d='';r.on('data',c=>d+=c);r.on('end',()=>res(d));}).on('error',rej);});}
function texts(html){
  const body = (html.match(/<body[\s\S]*?<\/body>/i)||[''])[0];
  const clean = body.replace(/<script[\s\S]*?<\/script>/gi,' ').replace(/<style[\s\S]*?<\/style>/gi,' ').replace(/<[^>]+>/g,'\n').replace(/&nbsp;|&#160;/g,' ').replace(/&amp;/g,'&').replace(/&#8211;/g,'-').replace(/&#\d+;/g,' ');
  const lines = clean.split(/\n/).map(s=>s.replace(/\s+/g,' ').trim()).filter(Boolean);
  const en = lines.filter(t=>/[A-Za-z]{3,}/.test(t) && !/^(https?:|www\.)/.test(t));
  return [...new Set(en)].slice(0,120);
}
(async()=>{
  for(const u of ['https://arap-library.de/','https://arap-library.de/shop/','https://arap-library.de/cart/','https://arap-library.de/checkout/','https://arap-library.de/my-account/']){
    const h = await get(u);
    console.log('===',u);
    texts(h).forEach(t=>console.log(' -',t.slice(0,110)));
  }
})();