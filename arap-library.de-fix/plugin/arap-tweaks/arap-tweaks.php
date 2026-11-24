<?php
/**
 * Plugin Name: Arap Tweaks — تحسينات إضافية
 * Description: تحسينات لموقع مكتبة آراب: عنوان ووصف الصفحة الرئيسية، تعريب مسار التنقل (Home ← الرئيسية)، وتصحيح النصوص البديلة للصور والشعار.
 * Version: 2.3.8
 * Author: Arap Library
 * Text Domain: arap-tweaks
 *
 * ملاحظة: إضافة مستقلة عن arap-fixes لأن ملفات تلك الإضافة غير قابلة للكتابة من لوحة التحكم.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * 1) عنوان ووصف الصفحة الرئيسية (Rank Math)
 * ======================================================= */

add_action( 'admin_init', 'arap_tweaks_seed_home_meta' );
function arap_tweaks_seed_home_meta() {
	if ( 2 === (int) get_option( 'arap_tweaks_home_meta_done' ) ) {
		return;
	}

	$page_id = (int) get_option( 'page_on_front' );
	if ( ! $page_id ) {
		return;
	}

	$title = 'مكتبة آراب | كتب عربية للشراء في ألمانيا وأوروبا';
	$desc  = 'أكثر من 4000 كتاب عربي: روايات وشعر وفكر وكتب أطفال. اطلب كتبك العربية أونلاين مع شحن سريع إلى ألمانيا وأوروبا من مكتبة آراب.';

	update_post_meta( $page_id, 'rank_math_title', $title );
	update_post_meta( $page_id, 'rank_math_description', $desc );
	if ( '' === (string) get_post_meta( $page_id, 'rank_math_focus_keyword', true ) ) {
		update_post_meta( $page_id, 'rank_math_focus_keyword', 'كتب عربية' );
	}

	update_option( 'arap_tweaks_home_meta_done', 2 );
}

/* =========================================================
 * 2) تعريب «Home» في مسار التنقل الخاص بالقالب (site-breadcrumbs)
 * ======================================================= */

add_filter( 'gettext', 'arap_tweaks_translate_home', 20, 3 );
function arap_tweaks_translate_home( $translated, $text, $domain ) {
	if ( is_admin() || 'Home' !== $text ) {
		return $translated;
	}

	if ( in_array( $domain, array( 'woovina', 'woovina-extra', 'skybook', 'woocommerce' ), true ) ) {
		return 'الرئيسية';
	}

	return $translated;
}

/* مسار تنقل WooCommerce أيضاً (في حال استخدامه في صفحات أخرى) */
add_filter( 'woocommerce_breadcrumb_defaults', 'arap_tweaks_wc_breadcrumb_defaults', 99 );
function arap_tweaks_wc_breadcrumb_defaults( $defaults ) {
	$defaults['home'] = 'الرئيسية';
	return $defaults;
}

/* =========================================================
 * 3) تصحيح النصوص البديلة: الشعار المكرر والصور الزخرفية (wrapper1/wrapper2…)
 * ======================================================= */

add_filter( 'wp_get_attachment_image_attributes', 'arap_tweaks_fix_alt', 20, 3 );
function arap_tweaks_fix_alt( $attr, $attachment, $size ) {
	$alt  = isset( $attr['alt'] ) ? trim( $attr['alt'] ) : '';
	$site = get_bloginfo( 'name' );

	if ( '' === $alt ) {
		return $attr;
	}

	// شعار مكرر: «مكتبة آراب — مكتبة آراب»
	if ( $alt === $site . ' — ' . $site || $alt === $site . ' - ' . $site ) {
		$attr['alt'] = 'شعار ' . $site;
		return $attr;
	}

	// أسماء ملفات/عناصر زخرفية بلا معنى: wrapper1, image2, placeholder, rectangle…
	if ( preg_match( '/^(wrapper|image|img|placeholder|rectangle|group|shape)[-_ ]?\d*$/i', $alt ) ) {
		$parent_id = ! empty( $attachment->post_parent ) ? (int) $attachment->post_parent : 0;
		$parent    = $parent_id ? get_the_title( $parent_id ) : '';

		// صور زخرفية بحتة: نص بديل فارغ أفضل من نص مضلِّل.
		$attr['alt'] = $parent ? $parent . ' — ' . $site : '';
	}

	return $attr;
}


/* =========================================================
 * 4) إزالة الاعتماد على موقع القالب التجريبي (skybook.woovina.net)
 *    + تنظيف النص البديل الزخرفي wrapper1/wrapper2
 * ======================================================= */

add_action( 'template_redirect', 'arap_tweaks_start_buffer', 20 );
function arap_tweaks_start_buffer() {
	if ( is_admin() || is_feed() || is_robots() || wp_doing_ajax() || is_customize_preview() ) {
		return;
	}
	ob_start( 'arap_tweaks_rewrite_assets' );
}

function arap_tweaks_rewrite_assets( $html ) {
	if ( is_page( 1473 ) && false !== strpos( $html, 'معلومات جهات الاتصال' ) ) {
		$heading = '<h1 class="arap-page-h1">اتصل بنا</h1>';
		$html    = preg_replace(
			'/(<h3 class="elementor-heading-title[^"]*">\s*معلومات جهات الاتصال\s*<\/h3>)/u',
			'<h2 class="elementor-heading-title elementor-size-default">$1</h2><h1 class="arap-page-h1">اتصل بنا</h1>',
			$html,
			1
		);
	}

	if ( false === strpos( $html, 'skybook.woovina.net' ) && false === stripos( $html, 'alt="wrapper' ) && false === strpos( $html, 'class="widget-title"' ) ) {
		return $html;
	}

	$map = array(
		'https://skybook.woovina.net/demo-01/wp-content/uploads/2020/05/wrapper1.png' => 'https://arap-library.de/wp-content/uploads/2020/05/wrapper1.png',
		'https://skybook.woovina.net/demo-01/wp-content/uploads/2020/05/wrapper2.png' => 'https://arap-library.de/wp-content/uploads/2020/05/wrapper2.png',
		'https://skybook.woovina.net/demo-01/wp-content/themes/skybook/assets/images/payment.png' => 'https://arap-library.de/wp-content/uploads/2026/11/payment.png',
	);

	$html = str_replace( array_keys( $map ), array_values( $map ), $html );

	$html = preg_replace( '/alt="wrapper\d*"/i', 'alt=""', $html );

	// عناوين الشريط الجانبي: h4 يكسر ترتيب العناوين (h2 → h4)
	$html = preg_replace( '/<h4 class="widget-title">(.*?)<\/h4>/is', '<h3 class="widget-title">$1</h3>', $html );

	return $html;
}


/* =========================================================
 * 5) إخفاء إصدار ووردبريس من كود الصفحة (تقليل بصمة الإصدار)
 * ======================================================= */

add_action( 'init', 'arap_tweaks_hide_version' );
function arap_tweaks_hide_version() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_filter( 'the_generator', '__return_empty_string' );


/* =========================================================
 * 6) حذف ملف robots.txt الثابت ليتولّى ووردبريس إنشاءه ديناميكيًا
 * ======================================================= */

add_action( 'admin_init', 'arap_tweaks_maybe_remove_robots' );
function arap_tweaks_maybe_remove_robots() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'arap_tweaks_robots_state' ) ) {
		return;
	}

	$file = ABSPATH . 'robots.txt';

	if ( ! file_exists( $file ) ) {
		update_option( 'arap_tweaks_robots_state', 'absent' );
		return;
	}

	update_option( 'arap_tweaks_robots_state', @unlink( $file ) ? 'deleted' : 'failed' );
}

/* robots.txt نظيف: كتلة واحدة + سطر Sitemap (يتجاوز أي فلتر سابق) */
add_filter( 'robots_txt', 'arap_tweaks_robots_txt', 999, 2 );
function arap_tweaks_robots_txt( $output, $public ) {
	if ( '0' === (string) $public ) {
		return $output;
	}

	$lines = array(
		'User-agent: *',
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'Disallow: /cart/',
		'Disallow: /checkout/',
		'Disallow: /my-account/',
		'Disallow: /wishlist/',
		'Disallow: /*?add-to-cart=',
		'Disallow: /*?s=',
		'Disallow: /*?orderby=',
		'Disallow: /*?filter_',
		'',
		'Sitemap: ' . home_url( '/sitemap_index.xml' ),
		'',
	);

	return implode( "\n", $lines );
}

/* =========================================================
 * 7) CSS وصولية: تباين النصوص، أهداف اللمس، نسب أبعاد الأيقونات
 * ======================================================= */

add_action( 'wp_head', 'arap_tweaks_a11y_css', 99 );
function arap_tweaks_a11y_css() {
	?>
	<style id="arap-tweaks-a11y">
		/* تباين نص حقل البحث */
		form.woovina-searchform input.field,
		.wew-search-wrap input.field,
		form.searchform input.field {
			color: #2b2b2b !important;
		}
		form.woovina-searchform input.field::placeholder,
		.wew-search-wrap input.field::placeholder {
			color: #5c5c5c !important;
			opacity: 1;
		}
		/* السعر قبل التخفيض */
		span.price del,
		span.price del bdi,
		.woocommerce-Price-amount del {
			color: #6b6b6b !important;
			opacity: 1 !important;
		}
		/* نقاط السلايدر: هدف لمس لا يقل عن 24px */
		ul.slick-dots li { margin: 0 4px !important; }
		ul.slick-dots li,
		ul.slick-dots li button {
			width: 28px !important;
			height: 28px !important;
			min-width: 28px;
			min-height: 28px;
			box-sizing: border-box;
		}
		.elementor-image-box-description { color: #5f5f5f !important; }
		/* مسار التنقل: النص الحالي كان #a1a1a1 */
		.site-breadcrumbs,
		.site-breadcrumbs .trail-item span,
		.site-breadcrumbs .trail-browse {
			color: #6b6b6b !important;
		}
		/* حالة المخزون */
		p.stock.in-stock,
		.stock.in-stock {
			color: #2e7d32 !important;
		}
		/* روابط تصنيفات المنتجات في الشريط الجانبي */
		.product-categories li a,
		.widget_product_categories a {
			color: #b45309 !important;
		}
		/* أسعار القوائم الجانبية */
		ul.product_list_widget .woocommerce-Price-amount bdi,
		ul.product_list_widget ins .woocommerce-Price-amount bdi {
			color: #1a7f8f !important;
		}
		ul.product_list_widget del .woocommerce-Price-amount bdi,
		ul.product_list_widget del bdi,
		del .woocommerce-Price-amount bdi,
		del bdi,
		.woocommerce-Price-amount del,
		.woocommerce-Price-amount del bdi {
			color: #6b6b6b !important;
			opacity: 1 !important;
		}
		/* السعر القديم المشطوب كان بشفافية 50% */
		del,
		del bdi,
		del span {
			opacity: 1 !important;
		}
		ul.slick-dots li button {
			min-width: 28px;
			min-height: 28px;
			padding: 0;
			font-size: 0;
		}
		ul.slick-dots li button:before {
			line-height: 28px !important;
			font-size: 10px !important;
		}
		/* أيقونات القوائم: الحفاظ على نسبة الأبعاد */
		a.menu-link img.icon,
		ul.sub-menu img.icon,
		.sidr-class-icon {
			width: auto !important;
			height: auto !important;
			max-width: 22px;
			max-height: 22px;
			object-fit: contain;
		}
		/* ---- عنوان صفحة «اتصل بنا» (كانت بلا H1) ---- */
		h1.arap-page-h1 {
			font-size: 30px !important;
			line-height: 1.4 !important;
			font-weight: 700 !important;
			color: #1f1f1f !important;
			margin: 0 0 18px !important;
		}

		/* ---- تحسينات الأداء: تثبيت تخطيط الفوتر (CLS) ---- */
		footer#footer.site-footer.parallax-footer {
			position: relative !important;
			top: auto !important;
			left: auto !important;
			right: auto !important;
			bottom: auto !important;
		}
</style>
	<?php
}

/* =========================================================
 * 8) JS وصولية: إصلاح أدوار Slick + أسماء الأزرار الأيقونية
 * ======================================================= */

add_action( 'wp_footer', 'arap_tweaks_a11y_js', 99 );
function arap_tweaks_a11y_js() {
	?>
	<script id="arap-tweaks-a11y-js">
	(function () {
		function clean() {
			var d, i, dots = document.querySelectorAll('.slick-dots');

			for (d = 0; d < dots.length; d++) {
				dots[d].removeAttribute('role');
				var lis = dots[d].children;
				for (i = 0; i < lis.length; i++) {
					var li = lis[i];
					li.removeAttribute('role');
					li.removeAttribute('aria-hidden');
					li.removeAttribute('aria-selected');
					li.removeAttribute('aria-controls');
					var btn = li.querySelector('button');
					if (btn) {
						btn.removeAttribute('role');
						btn.removeAttribute('tabindex');
						if (!btn.textContent.replace(/\s+/g, '')) {
							btn.setAttribute('aria-label', 'الانتقال إلى الشريحة ' + (i + 1));
						}
					}
				}
			}

			var tracks = document.querySelectorAll('.slick-track');
			for (i = 0; i < tracks.length; i++) {
				tracks[i].removeAttribute('role');
				tracks[i].removeAttribute('aria-label');
				var slides = tracks[i].children;
				for (var s = 0; s < slides.length; s++) {
					slides[s].removeAttribute('role');
					slides[s].removeAttribute('aria-selected');
				}
			}

			var labels = [
				['.site-search-toggle', 'بحث'],
				['.searchform-submit', 'بحث'],
				['.sidr-class-searchform-submit', 'بحث'],
				['.toggle-sidr-close', 'إغلاق القائمة'],
				['.sidr-class-toggle-sidr-close', 'إغلاق القائمة'],
				['.wvn-nav-link.next', 'المنتج التالي'],
				['.wvn-nav-link.prev', 'المنتج السابق'],
				['.quantity a.plus', 'زيادة الكمية'],
				['.quantity a.minus', 'إنقاص الكمية']
			];
			for (i = 0; i < labels.length; i++) {
				var els = document.querySelectorAll(labels[i][0]);
				for (var j = 0; j < els.length; j++) {
					if (!els[j].getAttribute('aria-label')) {
						els[j].setAttribute('aria-label', labels[i][1]);
					}
					var href = els[j].getAttribute('href') || '';
					if (href.indexOf('javascript:') === 0) {
						els[j].setAttribute('href', labels[i][1] === 'بحث' ? '/?s=' : '/');
					}
				}
			}

			var tabs = document.querySelectorAll('[tabindex]');
			for (i = 0; i < tabs.length; i++) {
				if (parseInt(tabs[i].getAttribute('tabindex'), 10) > 0) {
					tabs[i].removeAttribute('tabindex');
				}
			}
		}

		document.addEventListener('click', function (e) {
			var el = e.target, sel = '.site-search-toggle,.toggle-sidr-close,.sidr-class-toggle-sidr-close';
			while (el && el !== document) {
				if (el.matches && el.matches(sel)) { e.preventDefault(); return; }
				el = el.parentNode;
			}
		}, true);

		var scheduled = false;
		function run() {
			if (scheduled) { return; }
			scheduled = true;
			setTimeout(function () { scheduled = false; try { clean(); } catch (e) {} }, 150);
		}

		if (document.readyState !== 'loading') { run(); } else { document.addEventListener('DOMContentLoaded', run); }
		window.addEventListener('load', run);

		if (window.MutationObserver) {
			new MutationObserver(run).observe(document.documentElement, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['role', 'aria-hidden', 'aria-selected', 'aria-controls']
			});
		}
	})();
	</script>
	<?php
}

/* =========================================================
 * 9) تنبيه ترخيص القالب: إخفاء بصري فقط (لا تعطيل لأي وظيفة)
 *    ترويسة القالب تبقى كما هي، ووظائف الموقع لا تتأثر.
 * ======================================================= */

add_action( 'admin_head', 'arap_tweaks_hide_license_notice' );
function arap_tweaks_hide_license_notice() {
	echo '<style id="arap-hide-license-notice">#woovina-admin-notice{display:none !important}</style>';
}

/* =========================================================
 * 10) تعليق التحديث التلقائي لقالب skybook حتى يُجرى يدويًا
 *     (تحديث رئيسي 2.2 ← 3.4 يحتاج نسخة احتياطية أولًا)
 * ======================================================= */

add_filter( 'auto_update_theme', 'arap_tweaks_hold_theme_autoupdate', 20, 2 );
function arap_tweaks_hold_theme_autoupdate( $update, $theme ) {
	if ( isset( $theme->stylesheet ) && 'skybook' === $theme->stylesheet ) {
		return false;
	}
	return $update;
}

/* =========================================================
 * 11) إخفاء بقايا إضافات غير قابلة للحذف من قائمة الإضافات
 *     (مجلداتها موجودة على القرص بصلاحيات تمنع الحذف، وهي معطّلة)
 * ======================================================= */

add_filter( 'all_plugins', 'arap_tweaks_hide_stale_plugins' );
function arap_tweaks_hide_stale_plugins( $plugins ) {
	if ( ! is_admin() ) {
		return $plugins;
	}

	$hide = array( 'arap-fixes-ZAwFXX5IRalVeljL/', 'updraftplus/', 'wpvivid-backuprestore/' );

	foreach ( array_keys( $plugins ) as $file ) {
		foreach ( $hide as $prefix ) {
			if ( 0 === strpos( $file, $prefix ) ) {
				unset( $plugins[ $file ] );
				break;
			}
		}
	}

	return $plugins;
}

/* =========================================================
 * 12) IndexNow (Bing / Yandex / Seznam): ملف المفتاح + إشعار فوري
 *     لا يحتاج أي حساب، ويُسرّع فهرسة الصفحات الجديدة.
 * ======================================================= */

define( 'ARAP_INDEXNOW_KEY', 'a7f3c9e14b6d8250f4c1e9b73d5a06c2' );

add_action( 'init', 'arap_tweaks_indexnow_key_file', 1 );
function arap_tweaks_indexnow_key_file() {
	if ( is_admin() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';

	if ( '/' . ARAP_INDEXNOW_KEY . '.txt' !== $path ) {
		return;
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	echo ARAP_INDEXNOW_KEY;
	exit;
}

add_action( 'save_post', 'arap_tweaks_indexnow_ping_post', 20, 3 );
function arap_tweaks_indexnow_ping_post( $post_id, $post, $update ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( ! in_array( $post->post_type, array( 'post', 'page', 'product' ), true ) ) {
		return;
	}

	if ( 'publish' !== $post->post_status ) {
		return;
	}

	$url = get_permalink( $post_id );

	if ( $url ) {
		arap_tweaks_indexnow_submit( array( $url ) );
	}
}

function arap_tweaks_indexnow_submit( $urls ) {
	$urls = array_values( array_unique( array_filter( (array) $urls ) ) );

	if ( empty( $urls ) ) {
		return false;
	}

	$body = array(
		'host'        => wp_parse_url( home_url(), PHP_URL_HOST ),
		'key'         => ARAP_INDEXNOW_KEY,
		'keyLocation' => home_url( '/' . ARAP_INDEXNOW_KEY . '.txt' ),
		'urlList'     => array_slice( $urls, 0, 10000 ),
	);

	$res = wp_remote_post(
		'https://api.indexnow.org/indexnow',
		array(
			'timeout'  => 8,
			'blocking' => false,
			'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
			'body'     => wp_json_encode( $body ),
		)
	);

	return ! is_wp_error( $res );
}

/* =========================================================
 * 13) IndexNow: إرسال آلي مجدول + تشغيل يدوي للمدير
 *     (لأن التحقق من ملف المفتاح قد يتأخر عند أول إعداد)
 * ======================================================= */

add_action( 'init', 'arap_tweaks_indexnow_schedule', 2 );
function arap_tweaks_indexnow_schedule() {
	if ( wp_next_scheduled( 'arap_tweaks_indexnow_cron' ) ) {
		return;
	}

	if ( (int) get_option( 'arap_indexnow_sent', 0 ) ) {
		return;
	}

	wp_schedule_event( time() + 300, 'hourly', 'arap_tweaks_indexnow_cron' );
}

function arap_tweaks_indexnow_urls() {
	$urls  = array();
	$index = wp_remote_get( home_url( '/sitemap_index.xml' ), array( 'timeout' => 20 ) );

	if ( is_wp_error( $index ) ) {
		return $urls;
	}

	$xml = wp_remote_retrieve_body( $index );

	if ( ! preg_match_all( '#<loc>\s*([^<\s]+)\s*</loc>#i', $xml, $maps ) ) {
		return $urls;
	}

	foreach ( $maps[1] as $map ) {
		$res = wp_remote_get( html_entity_decode( $map ), array( 'timeout' => 20 ) );

		if ( is_wp_error( $res ) ) {
			continue;
		}

		$body = wp_remote_retrieve_body( $res );

		if ( preg_match_all( '#<loc>\s*([^<\s]+)\s*</loc>#i', $body, $locs ) ) {
			foreach ( $locs[1] as $loc ) {
				$urls[] = html_entity_decode( $loc );
			}
		}
	}

	return array_values( array_unique( $urls ) );
}

function arap_tweaks_indexnow_post( $urls, $blocking = false ) {
	$urls = array_values( array_unique( array_filter( (array) $urls ) ) );

	if ( empty( $urls ) ) {
		return false;
	}

	$body = array(
		'host'        => wp_parse_url( home_url(), PHP_URL_HOST ),
		'key'         => ARAP_INDEXNOW_KEY,
		'keyLocation' => home_url( '/' . ARAP_INDEXNOW_KEY . '.txt' ),
		'urlList'     => array_slice( $urls, 0, 10000 ),
	);

	$endpoints = array( 'https://api.indexnow.org/indexnow', 'https://www.bing.com/indexnow' );

	foreach ( $endpoints as $endpoint ) {
		$res = wp_remote_post(
			$endpoint,
			array(
				'timeout'  => $blocking ? 25 : 8,
				'blocking' => $blocking,
				'headers'  => array( 'Content-Type' => 'application/json; charset=utf-8' ),
				'body'     => wp_json_encode( $body ),
			)
		);

		if ( ! $blocking ) {
			return true;
		}

		if ( is_wp_error( $res ) ) {
			continue;
		}

		$code = (int) wp_remote_retrieve_response_code( $res );

		if ( 200 === $code || 202 === $code ) {
			return true;
		}
	}

	return false;
}

add_action( 'arap_tweaks_indexnow_cron', 'arap_tweaks_indexnow_cron_run' );
function arap_tweaks_indexnow_cron_run() {
	$attempts = (int) get_option( 'arap_indexnow_attempts', 0 );

	if ( $attempts >= 24 ) {
		wp_clear_scheduled_hook( 'arap_tweaks_indexnow_cron' );
		return;
	}

	update_option( 'arap_indexnow_attempts', $attempts + 1, false );

	if ( arap_tweaks_indexnow_post( arap_tweaks_indexnow_urls(), true ) ) {
		update_option( 'arap_indexnow_sent', time(), false );
		update_option( 'arap_indexnow_attempts', 0, false );
		wp_clear_scheduled_hook( 'arap_tweaks_indexnow_cron' );
	}
}

add_action( 'init', 'arap_tweaks_indexnow_manual', 3 );
function arap_tweaks_indexnow_manual() {
	if ( ! isset( $_GET['arap_indexnow'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$urls = arap_tweaks_indexnow_urls();
	$ok   = arap_tweaks_indexnow_post( $urls, true );

	if ( $ok ) {
		update_option( 'arap_indexnow_sent', time(), false );
		wp_clear_scheduled_hook( 'arap_tweaks_indexnow_cron' );
	}

	header( 'Content-Type: application/json; charset=utf-8' );
	echo wp_json_encode( array( 'submitted' => count( $urls ), 'ok' => $ok ) );
	exit;
}

/* =========================================================
 * 14) دمج الصفحات المكرّرة (301) وإخراج صفحات التجربة من الفهرسة
 *     الهدف: منع المحتوى المكرر وتحسين إشارات السيو
 * ======================================================= */

define( 'ARAP_HOME_GAP', true );

add_action( 'wp_head', 'arap_reduce_homepage_gap', 99 );
function arap_reduce_homepage_gap() {
	if ( ! ARAP_HOME_GAP || is_admin() || is_customize_preview() ) { return; }
	if ( ! ( is_front_page() || is_home() ) ) { return; }
	?>
	<style>
		/* Reduce gap between hero section and footer on homepage */
		.home .slick-slide-bg, .home .hero { margin-bottom: 30px !important; }
		.home .site-footer, .home .footer { margin-top: 30px !important; }
	</style>
	<?php
}

/* =========================================================
 * 15) تقليل المسافة بين الهيرو والفوتر في الصفحة الرئيسية
 *     Rollback: set ARAP_HOME_GAP to false.
 * ======================================================= */
function arap_tweaks_redirect_duplicates() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	// مخرج للتشخيص: ?arap_no_redirect=1
	if ( isset( $_GET['arap_no_redirect'] ) && current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! is_page() ) {
		return;
	}

	$map = array(
		1166 => 5878, // إتمام الطلب (نسخة قديمة) ← صفحة الدفع الرسمية
		1167 => 6818, // حسابي (نسخة عربية) ← صفحة الحساب الرسمية
		7434 => 1165, // سلة التسوق (نسخة) ← صفحة السلة الرسمية
		7    => 5386, // قائمة الرغبات (نسخة) ← الصفحة المعتمدة
		2245 => 7458, // المدونة (صفحة ثابتة) ← صفحة المقالات
		1724 => 7453, // من نحن (نسخة) ← صفحة «عن آراب»
		1412 => 1164, // صفحة متجر تجريبية ← المتجر
		4600 => 1803, // صفحة 404 تجريبية ← الرئيسية
		9426 => 1803, // صفحة Elementor فارغة ← الرئيسية
	);

	$current = get_queried_object_id();

	if ( ! $current || ! isset( $map[ $current ] ) ) {
		return;
	}

	$target = get_permalink( $map[ $current ] );

	if ( ! $target || $target === get_permalink( $current ) ) {
		return;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}

/* صفحات/أدوات لا قيمة فهرسة لها + تصحيح عنوان صفحة المقالات */
add_action( 'admin_init', 'arap_tweaks_seo_cleanup' );
function arap_tweaks_seo_cleanup() {
	if ( 2 === (int) get_option( 'arap_tweaks_seo_cleanup_done' ) ) {
		return;
	}

	// تنفيذ أثناء تثبيت الإضافة يُمنع أي عمليات قاعدة بيانات
	if ( wp_installing() || ! did_action( 'admin_init' ) ) {
		return;
	}

	// صفحة المقارنة (أداة) لا تُفهرس
	$robots = get_post_meta( 9275, 'rank_math_robots', true );
	$robots = is_array( $robots ) ? $robots : array();

	if ( ! in_array( 'noindex', $robots, true ) ) {
		$robots   = array_values( array_diff( $robots, array( 'index' ) ) );
		$robots[] = 'noindex';
		update_post_meta( 9275, 'rank_math_robots', $robots );
	}

	// عنوان صفحة المقالات كان «عن آراب»
	$post = get_post( 7458 );

	if ( $post && 'عن آراب' === $post->post_title ) {
		wp_update_post(
			array(
				'ID'         => 7458,
				'post_title' => 'المدونة',
			)
		);
	}

	update_option( 'arap_tweaks_seo_cleanup_done', 2, false );

	// إبطال ذاكرة خرائط الموقع مؤقتًا حتى تظهر التغييرات
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'rank\\_math\\_sitemap%'" );
}
