const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.27.php';
let s=fs.readFileSync(p,'utf8');
const old=`if (!els[j].getAttribute('aria-label')) {
						els[j].setAttribute('aria-label', labels[i][1]);
					}`;
const neu=`var want=labels[i][1]; var cur=els[j].getAttribute('aria-label');
					if(!cur || ((labels[i][0].indexOf('slick')>-1||labels[i][0].indexOf('owl')>-1) && (cur==='Previous'||cur==='Next'))) { els[j].setAttribute('aria-label', want); }
					if(cur==='Previous' && labels[i][0].indexOf('prev')>-1) { els[j].setAttribute('aria-label', want); }
					if(cur==='Next' && labels[i][0].indexOf('next')>-1) { els[j].setAttribute('aria-label', want); }`;
if(!s.includes(old)){console.log('no')}else{fs.writeFileSync(p,s.replace(old,neu));console.log('ok')}