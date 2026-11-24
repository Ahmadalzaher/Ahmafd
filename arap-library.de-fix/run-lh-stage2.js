const {execSync} = require('child_process');
const url = 'https://arap-library.de/?arap_rb=1&arap_rb_key=stg9x2';
for (const [i, prof] of [[1,'lhprofile17'],[2,'lhprofile18'],[3,'lhprofile19']]) {
  const out = `D:/oben code/arap-library.de-fix/lh-stage2-r${i}.json`;
  const cmd = `npx -y lighthouse@latest "${url}" --preset=perf --chrome-path="C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe" --chrome-flags="--headless --no-sandbox --disable-gpu --user-data-dir=C:\\Users\\ahmad\\AppData\\Local\\Temp\\${prof}" --output=json --output-path="${out}" --quiet`;
  console.log('run', i);
  execSync(cmd, {stdio:'inherit', timeout:420000, cwd:'D:/oben code/arap-library.de-fix'});
}
console.log('done');