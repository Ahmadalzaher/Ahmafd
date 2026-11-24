(async()=>{
const r=await fetch('https://arap-library.de/product/17%d8%b1%d9%85%d8%b6%d8%a7%d9%86/',{headers:{'User-Agent':'Mozilla/5.0'}});
const h=await r.text();
const re=/<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi;
let m,i=0; while((m=re.exec(h))&&i<4){ console.log('---',i); console.log(m[1].slice(0,300).replace(/\s+/g,' ')); i++; }
console.log('count',i);
})();