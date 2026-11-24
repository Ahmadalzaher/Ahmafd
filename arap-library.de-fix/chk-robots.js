const https=require('https');
['/cart/','/my-account/'].forEach(p=>{
  https.get('https://arap-library.de'+p,res=>{let d='';res.on('data',c=>d+=c);res.on('end',()=>{const m=d.match(/<meta[^>]+name=["']robots["'][^>]+content=["']([^"']+)/i);console.log(p,m?m[1]:'none');});});
});