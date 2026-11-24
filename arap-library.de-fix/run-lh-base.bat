@echo off
cd /d "D:\oben code\arap-library.de-fix"
set URL=https://arap-library.de/?arap_rb=1^&arap_rb_key=stg9x2
set CHROME=C:\Program Files\Google\Chrome\Application\chrome.exe
set FLAGS=--headless=new --no-sandbox
call lighthouse "%URL%^&lh=base1" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-base1.json --chrome-flags="%FLAGS%" --quiet
call lighthouse "%URL%^&lh=base2" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-base2.json --chrome-flags="%FLAGS%" --quiet
call lighthouse "%URL%^&lh=base3" --chrome-path="%CHROME%" --preset=perf --form-factor=mobile --throttling-method=simulate --output=json --output-path=lh-sm-base3.json --chrome-flags="%FLAGS%" --quiet
echo DONE