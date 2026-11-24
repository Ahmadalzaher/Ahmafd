@echo off
cd /d "D:\oben code\arap-library.de-fix"
set URL=https://arap-library.de/?arap_rb=1^&arap_rb_key=stg9x2
set CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe
set FLAGS=--headless=new --no-sandbox
call lighthouse "%URL%^&lh=after1" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-after1.json --chrome-flags="%FLAGS%" --quiet
call lighthouse "%URL%^&lh=after2" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-after2.json --chrome-flags="%FLAGS%" --quiet
call lighthouse "%URL%^&lh=after3" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-after3.json --chrome-flags="%FLAGS%" --quiet
echo DONE