const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.26.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.26','Version: 2.2.27');
const old = `			var labels = [
				['.site-search-toggle', 'بحث'],
				['.searchform-submit', 'بحث'],
				['.sidr-class-searchform-submit', 'بحث'],
				['.toggle-sidr-close', 'إغلاق القائمة'],
				['.sidr-class-toggle-sidr-close', 'إغلاق القائمة'],
				['.wvn-nav-link.next', 'المنتج التالي'],
				['.wvn-nav-link.prev', 'المنتج السابق'],
				['.quantity a.plus', 'زيادة الكمية'],
				['.quantity a.minus', 'إنقاص الكمية']
			];`;
const neu = `			var labels = [
				['.site-search-toggle', 'بحث'],
				['.searchform-submit', 'بحث'],
				['.sidr-class-searchform-submit', 'بحث'],
				['.toggle-sidr-close', 'إغلاق القائمة'],
				['.sidr-class-toggle-sidr-close', 'إغلاق القائمة'],
				['.wvn-nav-link.next', 'المنتج التالي'],
				['.wvn-nav-link.prev', 'المنتج السابق'],
				['.quantity a.plus', 'زيادة الكمية'],
				['.quantity a.minus', 'إنقاص الكمية'],
				['#scroll-top', 'العودة للأعلى'],
				['.scroll-top', 'العودة للأعلى'],
				['.slick-prev', 'السابق'],
				['.slick-next', 'التالي'],
				['.owl-prev', 'السابق'],
				['.owl-next', 'التالي'],
				['.mobile-menu .hamburger', 'القائمة']
			];`;
if(!s.includes(old)){console.log('no'); process.exit(1);}
s=s.replace(old,neu);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.27.php',s,'utf8');
console.log('ok');