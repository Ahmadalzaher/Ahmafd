(async()=>{
  let r=await fetch('https://arap-library.de/shop/?v='+Date.now(),{headers:{'User-Agent':'Mozilla/5.0'}});
  let h=await r.text();
  let m=h.match(/<meta name="description" content="([^"]+)"/i);
  console.log(m?m[1].slice(0,160):'no');
  r=await fetch('https://arap-library.de/checkout/?v='+Date.now(),{headers:{'User-Agent':'Mozilla/5.0'}});
  h=await r.text();
  const h1s=h.match(/<h1[^>]*>[\s\S]*?<\/h1>/gi)||[];
  console.log('h1',h1s.length);
})();