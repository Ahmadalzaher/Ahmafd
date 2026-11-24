const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.20.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.20','Version: 2.2.21');
const oldMap = `\t\t'Privacy Policy' => 'سياسة الخصوصية',
		'privacy policy' => 'سياسة الخصوصية',`;
const newMap = `\t\t'Privacy Policy' => 'سياسة الخصوصية',
		'privacy policy' => 'سياسة الخصوصية',
		'Returning customer?' => 'عميل سابق؟',
		'Click here to login' => 'انقر هنا لتسجيل الدخول',
		'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.' => 'إذا سبق لك التسوق معنا، يرجى إدخال بياناتك أدناه. إذا كنت عميلاً جديداً، يرجى المتابعة إلى قسم الفاتورة.',
		'Username or email' => 'اسم المستخدم أو البريد الإلكتروني',
		'Username or email address' => 'اسم المستخدم أو البريد الإلكتروني',
		'Password' => 'كلمة المرور',
		'Remember me' => 'تذكرني',
		'Lost your password?' => 'نسيت كلمة المرور؟',`;
if(s.includes(oldMap)) s=s.replace(oldMap,newMap);
const oldOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		return $html;`;
const newOb = `		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'Returning customer?', 'عميل سابق؟', $html );
		$html = str_replace( 'Click here to login', 'انقر هنا لتسجيل الدخول', $html );
		return $html;`;
if(s.includes(oldOb)) s=s.replace(oldOb,newOb);
fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.21.php',s,'utf8');
console.log('ok', s.includes('2.2.21'));