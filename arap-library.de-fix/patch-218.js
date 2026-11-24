const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.17.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.17','Version: 2.2.18');

// Add gettext mappings
const oldMap = `\t\t'No products in the cart.' => 'لا توجد منتجات في السلة.',`;
const newMap = `\t\t'No products in the cart.' => 'لا توجد منتجات في السلة.',
		'Billing details' => 'تفاصيل الفاتورة',
		'Your order' => 'طلبك',
		'Have a coupon?' => 'لديك قسيمة؟',
		'Click here to enter your code' => 'انقر هنا لإدخال الرمز',
		'If you have a coupon code, please apply it below.' => 'إذا كان لديك رمز قسيمة، يرجى إدخاله أدناه.',
		'Apply coupon' => 'تطبيق القسيمة',
		'Coupon code' => 'رمز القسيمة',
		'Place order' => 'تأكيد الطلب',
		'Additional information' => 'معلومات إضافية',
		'Order notes' => 'ملاحظات الطلب',
		'Notes about your order, e.g. special notes for delivery.' => 'ملاحظات حول طلبك، مثال: ملاحظات خاصة بالتسليم.',
		'Product' => 'المنتج',
		'Subtotal' => 'المجموع الفرعي',
		'Total' => 'الإجمالي',
		'Shipping' => 'الشحن',
		'Payment method' => 'طريقة الدفع',
		'Privacy Policy' => 'سياسة الخصوصية',
		'privacy policy' => 'سياسة الخصوصية',`;
if(s.includes(oldMap)) s=s.replace(oldMap,newMap);

// Add OB replacements
const oldOb = `\t\t$html = str_replace( 'class="elementor-screen-only">Facebook</span>', 'class="elementor-screen-only">فيسبوك</span>', $html );
		return $html;`;
const newOb = `\t\t$html = str_replace( 'class="elementor-screen-only">Facebook</span>', 'class="elementor-screen-only">فيسبوك</span>', $html );
		$html = str_replace( 'الرمز البريدي / ZIP', 'الرمز البريدي', $html );
		$html = str_replace( '>Billing details<', '>تفاصيل الفاتورة<', $html );
		$html = str_replace( '>Your order<', '>طلبك<', $html );
		$html = str_replace( '>Have a coupon?<', '>لديك قسيمة؟<', $html );
		$html = str_replace( '>Click here to enter your code<', '>انقر هنا لإدخال الرمز<', $html );
		return $html;`;
if(s.includes(oldOb)) s=s.replace(oldOb,newOb);

fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.18.php',s,'utf8');
console.log('ok', s.includes('2.2.18'), s.includes('تفاصيل الفاتورة'));