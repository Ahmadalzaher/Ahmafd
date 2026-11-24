<?php
/**
 * Plugin Name: Arap Fixes — إصلاحات مكتبة آراب
 * Description: إصلاحات تقنية وSEO لموقع arap-library.de: فحص الروابط الدائمة، ترويسات أمان، بيانات منظمة للمنتجات، نصوص بديلة وصفية، إزالة تكرار H1، وتحميل مكتبة Slick المفقودة (تعطّل السلايدرات).
 * Version: 1.0.0
 * Author: Arap Library
 * Text Domain: arap-fixes
 * Requires PHP: 7.4
 *
 * ملاحظة: هذا الملف مستقل تمامًا ولا يعدّل أي ملف في القالب أو الإضافات الأخرى.
 * لإيقاف أي جزء استخدم الفلاتر المكتوبة بجانبه.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARAP_FIXES_VERSION', '1.0.0' );

/* =========================================================
 * 1) الترويسات الأمنية
 * ======================================================= */

add_action( 'send_headers', 'arap_fixes_security_headers' );
function arap_fixes_security_headers() {
	if ( is_admin() ) {
		return;
	}

	if ( ! headers_sent() ) {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header_remove( 'X-Powered-By' );
	}

	// HSTS — يُفعّل فقط عند وجود HTTPS فعلي. عدّل max-age إن رغبت.
	if ( is_ssl() && ! headers_sent() ) {
		header( 'Strict-Transport-Security: max-age=15552000' );
	}
}

/* =========================================================
 * 2) إخفاء إصدار ووردبريس
 * ======================================================= */

remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* =========================================================
 * 3) robots.txt — منع الصفحات غير المفيدة وإضافة Sitemap
 * ======================================================= */

add_filter( 'robots_txt', 'arap_fixes_robots_txt', 20, 2 );
function arap_fixes_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output;
	}

	$rules = "Disallow: /cart/\nDisallow: /checkout/\nDisallow: /my-account/\nDisallow: /wishlist/\nDisallow: /*?add-to-cart=\nDisallow: /*?s=\nDisallow: /*?orderby=\nDisallow: /*?filter_\n";

	// لا نكرّر القواعد إن كانت مضافة مسبقًا.
	if ( strpos( $output, 'Disallow: /cart/' ) === false ) {
		$output .= "\nUser-agent: *\n" . $rules;
	}

	if ( stripos( $output, 'Sitemap:' ) === false ) {
		$output .= "\nSitemap: " . home_url( '/sitemap_index.xml' ) . "\n";
	}

	return $output;
}

/* =========================================================
 * 4) noindex للصفحات الوظيفية (سلة، دفع، حسابي، بحث، 404)
 * ======================================================= */

function arap_fixes_should_noindex() {
	if ( is_search() || is_404() ) {
		return true;
	}

	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url() ) ) {
		return true;
	}

	return false;
}

add_filter( 'wp_robots', 'arap_fixes_wp_robots' );
function arap_fixes_wp_robots( $robots ) {
	if ( arap_fixes_should_noindex() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}

add_filter( 'rank_math/frontend/robots', 'arap_fixes_rank_math_robots' );
function arap_fixes_rank_math_robots( $robots ) {
	if ( arap_fixes_should_noindex() ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
}

/* =========================================================
 * 5) تصحيح البيانات المنظمة (Rank Math)
 * ======================================================= */

add_filter( 'rank_math/json_ld', 'arap_fixes_json_ld', 99, 2 );
function arap_fixes_json_ld( $data, $jsonld = null ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}

	$is_front = is_front_page();
	$social   = array(
		'https://www.facebook.com/Araplibrary/',
		'https://twitter.com/ArapCulture',
		'https://www.instagram.com/arap.library/',
	);

	foreach ( $data as $key => $piece ) {
		if ( ! is_array( $piece ) || empty( $piece['@type'] ) ) {
			continue;
		}
		$types = (array) $piece['@type'];

		// 5.1 تنظيم sameAs وإصلاح الروابط المكسورة (مثل twitter.com/Arap Culture).
		if ( array_intersect( $types, array( 'Organization', 'Library', 'Store', 'LocalBusiness', 'OnlineStore' ) ) ) {
			$clean = array();
			if ( ! empty( $piece['sameAs'] ) ) {
				foreach ( (array) $piece['sameAs'] as $url ) {
					$url = trim( preg_replace( '/\s+/', '', (string) $url ) );
					if ( $url && filter_var( $url, FILTER_VALIDATE_URL ) ) {
						$clean[] = $url;
					}
				}
			}
			$clean               = array_values( array_unique( array_merge( $clean, $social ) ) );
			$data[ $key ]['sameAs'] = $clean;
		}

		// 5.2 الصفحة الرئيسية متجر لا مقال.
		if ( $is_front && in_array( 'Article', $types, true ) ) {
			$new_types = array();
			foreach ( $types as $type ) {
				$new_types[] = ( 'Article' === $type ) ? 'CollectionPage' : $type;
			}
			$data[ $key ]['@type'] = ( 1 === count( $new_types ) ) ? $new_types[0] : $new_types;
		}

		// 5.3 إثراء بيانات المنتج.
		if ( in_array( 'Product', $types, true ) ) {
			$data[ $key ] = arap_fixes_enrich_product( $data[ $key ] );
		}
	}

	return $data;
}

function arap_fixes_enrich_product( $product_data ) {
	// إزالة اسم الموقع من اسم المنتج داخل الـ Schema فقط.
	if ( ! empty( $product_data['name'] ) ) {
		$product_data['name'] = trim(
			preg_replace( '/\s*[\|\-–—]\s*مكتبة\s*آراب\s*$/u', '', (string) $product_data['name'] )
		);
	}

	if ( empty( $product_data['brand'] ) ) {
		$product_data['brand'] = array(
			'@type' => 'Brand',
			'name'  => 'مكتبة آراب',
		);
	}

	if ( empty( $product_data['offers'] ) || ! is_array( $product_data['offers'] ) ) {
		return $product_data;
	}

	$permalink = get_permalink();

	$enrich_offer = function ( $offer ) use ( $permalink ) {
		if ( ! is_array( $offer ) ) {
			return $offer;
		}

		if ( empty( $offer['url'] ) && $permalink ) {
			$offer['url'] = $permalink;
		}
		if ( empty( $offer['priceCurrency'] ) ) {
			$offer['priceCurrency'] = 'EUR';
		}
		if ( empty( $offer['priceValidUntil'] ) ) {
			$offer['priceValidUntil'] = gmdate( 'Y-m-d', strtotime( '+1 year' ) );
		}
		if ( empty( $offer['itemCondition'] ) ) {
			$offer['itemCondition'] = 'https://schema.org/NewCondition';
		}
		if ( empty( $offer['seller'] ) ) {
			$offer['seller'] = array(
				'@type' => 'Organization',
				'name'  => 'مكتبة آراب',
			);
		}
		return $offer;
	};

	if ( isset( $product_data['offers']['price'] ) ) {
		$product_data['offers'] = $enrich_offer( $product_data['offers'] );
	} else {
		foreach ( $product_data['offers'] as $i => $offer ) {
			$product_data['offers'][ $i ] = $enrich_offer( $offer );
		}
	}

	return $product_data;
}

/* =========================================================
 * 6) تعريب كلمة Home في Breadcrumb
 * ======================================================= */

add_filter( 'rank_math/frontend/breadcrumb/items', 'arap_fixes_breadcrumb_items' );
function arap_fixes_breadcrumb_items( $crumbs ) {
	if ( ! is_array( $crumbs ) ) {
		return $crumbs;
	}

	$map = array(
		'home'  => 'الرئيسية',
		'Home'  => 'الرئيسية',
		'المنتج' => 'المنتجات',
	);

	foreach ( $crumbs as $i => $crumb ) {
		if ( ! is_array( $crumb ) ) {
			continue;
		}
		// صيغة Rank Math: [ 0 => النص, 1 => الرابط ]، وبعض الإصدارات تستخدم text/url.
		foreach ( array( 0, 'text' ) as $text_key ) {
			if ( isset( $crumb[ $text_key ] ) && isset( $map[ $crumb[ $text_key ] ] ) ) {
				$crumbs[ $i ][ $text_key ] = $map[ $crumb[ $text_key ] ];
			}
		}
	}

	return $crumbs;
}

/* =========================================================
 * 7) نصوص بديلة وصفية للصور (بدل «مكتبة آراب 12»)
 * ======================================================= */

add_filter( 'wp_get_attachment_image_attributes', 'arap_fixes_image_alt', 10, 3 );
function arap_fixes_image_alt( $attr, $attachment, $size ) {
	$current = isset( $attr['alt'] ) ? trim( $attr['alt'] ) : '';

	if ( '' !== $current && ! preg_match( '/^مكتبة\s*آراب\s*\d*$/u', $current ) ) {
		return $attr;
	}

	$parent_id = ! empty( $attachment->post_parent ) ? (int) $attachment->post_parent : 0;
	$title     = $parent_id ? get_the_title( $parent_id ) : '';

	if ( '' === $title ) {
		$title = get_bloginfo( 'name' );
	}

	$attr['alt'] = $title . ' — مكتبة آراب';

	return $attr;
}

/* =========================================================
 * 8) إزالة تكرار H1 (الصفحة الرئيسية)
 * ======================================================= */

add_action( 'template_redirect', 'arap_fixes_start_h1_buffer', 1 );
function arap_fixes_start_h1_buffer() {
	if ( is_admin() || is_feed() || is_robots() || is_trackback() || wp_doing_ajax() || ! is_front_page() ) {
		return;
	}

	if ( ! apply_filters( 'arap_fixes_normalize_h1', true ) ) {
		return;
	}

	ob_start( 'arap_fixes_normalize_h1_callback' );
}

function arap_fixes_normalize_h1_callback( $html ) {
	if ( ! is_string( $html ) || stripos( $html, '<h1' ) === false ) {
		return $html;
	}

	$count = 0;

	return preg_replace_callback(
		'/<h1(\s[^>]*)?>(.*?)<\/h1>/is',
		function ( $m ) use ( &$count ) {
			$count++;
			if ( 1 === $count ) {
				return $m[0];
			}
			$attrs = isset( $m[1] ) ? $m[1] : '';
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);
}

/* =========================================================
 * 9) روابط «أضف إلى السلة» بلا متابعة (تقليل زحف غير مفيد)
 * ======================================================= */

add_filter( 'woocommerce_loop_add_to_cart_link', 'arap_fixes_nofollow_cart', 10, 2 );
function arap_fixes_nofollow_cart( $html, $product ) {
	if ( false === strpos( $html, 'rel=' ) ) {
		return str_replace( '<a ', '<a rel="nofollow" ', $html );
	}
	return preg_replace( '/rel="([^"]*)"/', 'rel="$1 nofollow"', $html, 1 );
}

/* =========================================================
 * 10) تحميل مكتبة Slick المفقودة (تعطّل السلايدرات وQuick View)
 * ======================================================= */

add_action( 'wp_enqueue_scripts', 'arap_fixes_enqueue_slick', 2 );
function arap_fixes_enqueue_slick() {
	if ( is_admin() ) {
		return;
	}

	$js  = plugin_dir_url( __FILE__ ) . 'assets/slick.min.js';
	$css = plugin_dir_url( __FILE__ ) . 'assets/slick.css';

	wp_enqueue_style( 'arap-slick', $css, array(), '1.8.1' );
	wp_enqueue_script( 'arap-slick', $js, array( 'jquery' ), '1.8.1', false );
}

/* =========================================================
 * 11) إصلاح الشعار المقصوص + CSS إصلاحي بسيط
 * ======================================================= */

add_action( 'wp_head', 'arap_fixes_inline_css', 99 );
function arap_fixes_inline_css() {
	$max_desktop = (int) apply_filters( 'arap_fixes_logo_max_height', 78 );
	$max_mobile  = (int) apply_filters( 'arap_fixes_logo_max_height_mobile', 56 );
	?>
	<style id="arap-fixes-css">
		#site-logo .custom-logo,
		.custom-header-logo .custom-logo,
		.custom-logo-link .custom-logo {
			width: auto;
			height: auto;
			max-height: <?php echo esc_attr( $max_desktop ); ?>px;
			max-width: 340px;
			object-fit: contain;
		}
		.custom-logo-link { line-height: 0; display: inline-block; }
		@media (max-width: 767px) {
			#site-logo .custom-logo,
			.custom-header-logo .custom-logo,
			.custom-logo-link .custom-logo {
				max-height: <?php echo esc_attr( $max_mobile ); ?>px;
				max-width: 220px;
			}
		}
	</style>
	<?php
}

/* =========================================================
 * 12) لوحة فحص الروابط الدائمة (أدوات → إصلاحات آراب)
 * ======================================================= */

add_action( 'admin_menu', 'arap_fixes_admin_menu' );
function arap_fixes_admin_menu() {
	add_management_page(
		'إصلاحات آراب',
		'إصلاحات آراب',
		'manage_options',
		'arap-fixes',
		'arap_fixes_admin_page'
	);
}

function arap_fixes_probe( $path ) {
	$url = add_query_arg( 'arap_probe', time(), home_url( $path ) );

	$res = wp_remote_get(
		$url,
		array(
			'timeout'     => 20,
			'redirection' => 2,
			'sslverify'   => false,
			'headers'     => array( 'User-Agent' => 'ArapFixes/1.0; ' . home_url( '/' ) ),
		)
	);

	if ( is_wp_error( $res ) ) {
		return array(
			'url'   => $url,
			'code'  => 0,
			'len'   => 0,
			'error' => $res->get_error_message(),
		);
	}

	$body = (string) wp_remote_retrieve_body( $res );
	$code = (int) wp_remote_retrieve_response_code( $res );

	return array(
		'url'       => $url,
		'code'      => $code,
		'len'       => strlen( $body ),
		'error'     => '',
		'apache404' => ( $code === 404 && strlen( $body ) < 1000 && stripos( $body, '404 Not Found' ) !== false ),
	);
}

function arap_fixes_htaccess_state() {
	$file    = ABSPATH . '.htaccess';
	$exists  = file_exists( $file );
	$content = $exists ? (string) @file_get_contents( $file ) : '';
	$writable = $exists ? is_writable( $file ) : is_writable( ABSPATH );

	return array(
		'file'     => $file,
		'exists'   => $exists,
		'writable' => (bool) $writable,
		'has_wp'   => ( false !== strpos( $content, 'RewriteEngine On' ) ),
		'size'     => strlen( $content ),
	);
}

function arap_fixes_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';

	if ( isset( $_POST['arap_flush'] ) && check_admin_referer( 'arap_fixes_action' ) ) {
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		flush_rewrite_rules( false );
		if ( function_exists( 'save_mod_rewrite_rules' ) ) {
			save_mod_rewrite_rules();
		}
		$notice = 'تم تفريغ قواعد الروابط ومحاولة إعادة كتابة ملف .htaccess.';
	}

	$structure = get_option( 'permalink_structure' );
	$ht        = arap_fixes_htaccess_state();
	$probes    = array(
		'الصفحة الرئيسية'      => arap_fixes_probe( '/' ),
		'صفحة المتجر'          => arap_fixes_probe( '/shop/' ),
		'السلة'                => arap_fixes_probe( '/cart/' ),
		'خريطة الموقع'         => arap_fixes_probe( '/sitemap_index.xml' ),
	);
	$broken    = false;
	foreach ( $probes as $probe ) {
		if ( ! empty( $probe['apache404'] ) ) {
			$broken = true;
		}
	}
	?>
	<div class="wrap" dir="rtl">
		<h1>إصلاحات آراب — فحص الروابط الدائمة</h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<?php if ( $broken ) : ?>
			<div class="notice notice-error">
				<p><strong>الروابط الدائمة معطّلة على مستوى الخادم (Apache يرجع 404).</strong></p>
				<p>هذا لا يُصلَح من ووردبريس. المطلوب من مزوّد الاستضافة تفعيل <code>AllowOverride All</code> و<code>mod_rewrite</code> لمجلد الموقع.</p>
				<p>يمكنك إرسال النص التالي للدعم الفني:</p>
				<textarea readonly rows="6" style="width:100%;font-family:monospace;direction:ltr;text-align:left">Hello support,

The site <?php echo esc_url( home_url( '/' ) ); ?> runs WordPress with pretty permalinks, but all pretty URLs (for example /shop/ and /product/example/) return the default Apache 404 page, while /index.php and /?p=123 work fine.

This means mod_rewrite / .htaccess rules are not being applied for this document root. Please:
1) enable mod_rewrite,
2) set AllowOverride All (or at least "AllowOverride FileInfo Options") for the document root of this domain,
3) confirm that .htaccess is readable by the web server user.

Thank you.</textarea>
			</div>
		<?php else : ?>
			<div class="notice notice-success"><p><strong>الروابط الدائمة تعمل بشكل صحيح.</strong></p></div>
		<?php endif; ?>

		<h2>الحالة</h2>
		<table class="widefat striped" style="max-width:900px">
			<tbody>
				<tr><td>بنية الروابط</td><td><code><?php echo esc_html( $structure ? $structure : 'plain (افتراضية)' ); ?></code></td></tr>
				<tr><td>ملف .htaccess</td><td><?php echo $ht['exists'] ? 'موجود' : '<strong>غير موجود</strong>'; ?> — <?php echo $ht['writable'] ? 'قابل للكتابة' : '<strong>غير قابل للكتابة</strong>'; ?> — قاعدة WordPress: <?php echo $ht['has_wp'] ? 'موجودة' : '<strong>غير موجودة</strong>'; ?></td></tr>
				<tr><td>mod_rewrite (كشف ووردبريس)</td><td><?php echo ( function_exists( 'got_url_rewrite' ) && got_url_rewrite() ) ? 'يبدو مفعّلًا' : 'غير مكتشف'; ?></td></tr>
			</tbody>
		</table>

		<h2>فحص الروابط</h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th>الصفحة</th><th>الرمز</th><th>الحجم</th><th>النتيجة</th></tr></thead>
			<tbody>
			<?php foreach ( $probes as $label => $probe ) : ?>
				<tr>
					<td><?php echo esc_html( $label ); ?></td>
					<td><?php echo esc_html( $probe['code'] ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $probe['len'] ) ); ?> بايت</td>
					<td>
						<?php
						if ( ! empty( $probe['error'] ) ) {
							echo esc_html( 'خطأ: ' . $probe['error'] );
						} elseif ( ! empty( $probe['apache404'] ) ) {
							echo '<strong style="color:#b32d2e">404 من Apache — معطّل</strong>';
						} elseif ( 200 === (int) $probe['code'] ) {
							echo '<span style="color:#007017">يعمل</span>';
						} else {
							echo esc_html( 'رمز ' . $probe['code'] );
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="margin-top:20px">
			<?php wp_nonce_field( 'arap_fixes_action' ); ?>
			<p>
				<button class="button button-primary" name="arap_flush" value="1">إعادة بناء قواعد الروابط وملف .htaccess</button>
			</p>
			<p class="description">بعد الإصلاح من جهة الاستضافة، اضغط هذا الزر مرة واحدة ثم أعد فحص الروابط في الأعلى.</p>
		</form>
	</div>
	<?php
}

/* =========================================================
 * 13) التنشيط: تفريغ القواعد مرة واحدة
 * ======================================================= */

register_activation_hook( __FILE__, 'arap_fixes_activate' );
function arap_fixes_activate() {
	flush_rewrite_rules( false );
}
