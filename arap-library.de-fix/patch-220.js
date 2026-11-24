const fs=require('fs');
let p='D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.19.php';
let s=fs.readFileSync(p,'utf8');
s=s.replace('Version: 2.2.19','Version: 2.2.20');

// Add filters for checkout fields (AJAX-safe)
const anchor = `add_filter( 'ngettext', 'arap_ar_ngettext', 20, 5 );`;
const add = `add_filter( 'ngettext', 'arap_ar_ngettext', 20, 5 );

add_filter( 'woocommerce_default_address_fields', function( $fields ) {
	if ( isset( $fields['postcode']['label'] ) ) {
		$fields['postcode']['label'] = 'الرمز البريدي';
	}
	if ( isset( $fields['postcode']['placeholder'] ) ) {
		$fields['postcode']['placeholder'] = 'الرمز البريدي';
	}
	return $fields;
} );
add_filter( 'woocommerce_gateway_title', function( $title, $id ) {
	if ( $id === 'paypal' || stripos( $title, 'paypal' ) !== false ) { return 'باي بال'; }
	return $title;
}, 20, 2 );`;
if(s.includes(anchor) && !s.includes('woocommerce_default_address_fields')) s=s.replace(anchor, add);

// OB extra replacements
const oldOb = `		$html = str_replace( 'Datenschutzerklärung - Arap Library', 'سياسة الخصوصية – مكتبة آراب', $html );
		$html = str_replace( '>PayPal<', '>باي بال<', $html );
		return $html;`;
const newOb = `		$html = str_replace( 'Datenschutzerklärung - Arap Library', 'سياسة الخصوصية – مكتبة آراب', $html );
		$html = str_replace( '>PayPal<', '>باي بال<', $html );
		$html = str_replace( 'بواسطة PayPal', 'بواسطة باي بال', $html );
		$html = str_replace( 'حساب على PayPal', 'حساب على باي بال', $html );
		$html = str_replace( 'إلى PayPal', 'إلى باي بال', $html );
		$html = str_replace( 'الدفع بواسطة PayPal', 'الدفع بواسطة باي بال', $html );
		$html = preg_replace( '/الرمز البريدي\\s*\\/\\s*ZIP/u', 'الرمز البريدي', $html );
		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		return $html;`;
if(s.includes(oldOb)) s=s.replace(oldOb, newOb);

fs.writeFileSync('D:/oben code/arap-library.de-fix/live-backup/arap-tweaks-2.2.20.php',s,'utf8');
console.log('ok', s.includes('2.2.20'));