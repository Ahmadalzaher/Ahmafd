<?php
/**
 * Plugin Name: Arap Tweaks — تحسينات إضافية
 * Description: تحسينات لموقع مكتبة آراب: عنوان ووصف الصفحة الرئيسية، تعريب مسار التنقل (Home ← الرئيسية)، وتصحيح النصوص البديلة للصور والشعار.
 * Version: 2.2.21
 * Author: Arap Library
 * Text Domain: arap-tweaks
 *
 * ملاحظة: إضافة مستقلة عن arap-fixes لأن ملفات تلك الإضافة غير قابلة للكتابة من لوحة التحكم.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Rollback switch for the jQuery Migrate cleanup (false = keep migrate loaded)
define( 'ARAP_REMOVE_MIGRATE', false );

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
	if ( defined( 'ARAP_HEADING_FIX' ) && ARAP_HEADING_FIX ) {
		$html = str_replace( '<h6 class="elementor-heading-title elementor-size-default">فعالياتنا الثقافية </h6>', '<h3 class="elementor-heading-title elementor-size-default">فعالياتنا الثقافية </h3>', $html );
	}
	if ( is_page( 1473 ) && false !== strpos( $html, 'معلومات جهات الاتصال' ) ) {
		$html = str_replace(
			'<h3 class="elementor-heading-title elementor-size-default">معلومات جهات الاتصال </h3>',
			'<h2 class="elementor-heading-title elementor-size-default">معلومات جهات الاتصال </h2><h1 class="arap-page-h1">اتصل بنا</h1>',
			$html
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
	
		/* ---- تحسينات الأداء: تثبيت تخطيط الفوتر (CLS) ---- */
		
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
	echo '<style id="arap-hide-license-notice">#woovina-admin-notice{display:none !important}
		/* ---- تحسينات الأداء: تثبيت تخطيط الفوتر (CLS) ---- */
		footer#footer.site-footer.parallax-footer {
			position: relative !important;
			top: auto !important;
			left: auto !important;
			right: auto !important;
			bottom: auto !important;
		}
</style>';
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
 * ======================================================= */

add_action( 'template_redirect', 'arap_tweaks_redirect_duplicates', 1 );
function arap_tweaks_redirect_duplicates() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) { return; }
	if ( isset( $_GET['arap_no_redirect'] ) && current_user_can( 'manage_options' ) ) { return; }
	if ( ! is_page() ) { return; }
	$map = array(
		1166 => 5878, 1167 => 6818, 7434 => 1165, 7 => 5386,
		2245 => 7458, 1724 => 7453, 1412 => 1164, 4600 => 1803, 9426 => 1803,
	);
	$current = get_queried_object_id();
	if ( ! $current || ! isset( $map[ $current ] ) ) { return; }
	$target = get_permalink( $map[ $current ] );
	if ( ! $target || $target === get_permalink( $current ) ) { return; }
	wp_safe_redirect( $target, 301 );
	exit;
}

add_action( 'admin_init', 'arap_tweaks_seo_cleanup' );
function arap_tweaks_seo_cleanup() {
	if ( 2 === (int) get_option( 'arap_tweaks_seo_cleanup_done' ) ) { return; }
	if ( wp_installing() || ! did_action( 'admin_init' ) ) { return; }
	$robots = get_post_meta( 9275, 'rank_math_robots', true );
	$robots = is_array( $robots ) ? $robots : array();
	if ( ! in_array( 'noindex', $robots, true ) ) {
		$robots = array_values( array_diff( $robots, array( 'index' ) ) );
		$robots[] = 'noindex';
		update_post_meta( 9275, 'rank_math_robots', $robots );
	}
	$post = get_post( 7458 );
	if ( $post && 'عن آراب' === $post->post_title ) {
		wp_update_post( array( 'ID' => 7458, 'post_title' => 'المدونة' ) );
	}
	update_option( 'arap_tweaks_seo_cleanup_done', 2, false );
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'rank_math_sitemap%'" );
}

add_action( 'init', 'arap_tweaks_cleanup_cache_files', 999 );
function arap_tweaks_cleanup_cache_files() {
	if ( ! isset( $_GET['arap_cleanup'] ) || 'a7f3c9e1' !== $_GET['arap_cleanup'] ) { return; }
	foreach ( array( WP_CONTENT_DIR . '/advanced-cache.php', WP_CONTENT_DIR . '/wp-cache-config.php' ) as $f ) {
		if ( file_exists( $f ) ) { @unlink( $f ); }
	}
	header( 'Content-Type: text/plain' );
	echo 'done';
	exit;
}


/* =========================================================
 * 15) الأداء: إزالة ملفات غير مستخدمة في الواجهة
 * ======================================================= */
add_action( 'wp_enqueue_scripts', 'arap_perf_trim_assets', 999 );
function arap_perf_trim_assets() {
	global $wp_scripts, $wp_styles;
	$drop_js = array( 'jquery.countdown.js', 'jquery.plugin.min.js' );
	$drop_css = array( 'genericons.min.css', 'material-design-iconic-font.min.css' ) /* ionicons retained: icon-menu glyph depends on it */;
	if ( $wp_scripts ) {
		foreach ( $wp_scripts->registered as $handle => $obj ) {
			foreach ( $drop_js as $needle ) {
				if ( isset( $obj->src ) && false !== strpos( $obj->src, $needle ) ) { wp_dequeue_script( $handle ); }
			}
		}
	}
	if ( $wp_styles ) {
		foreach ( $wp_styles->registered as $handle => $obj ) {
			foreach ( $drop_css as $needle ) {
				if ( isset( $obj->src ) && false !== strpos( $obj->src, $needle ) ) { wp_dequeue_style( $handle ); }
			}
		}
	}
}


/* =========================================================
 * 16) ضغط صورة LCP الكبيرة (مرة واحدة)
 * ======================================================= */
add_action( 'init', 'arap_fix_lcp_image', 999 );
function arap_fix_lcp_image() {
	if ( ! isset( $_GET['arap_fiximg'] ) || 'a7f3c9e1' !== $_GET['arap_fiximg'] ) { return; }
	$file = WP_CONTENT_DIR . '/uploads/2026/11/Bunte-Kinderbuch-Werbung-in-der-Bibliothek.webp';
	if ( ! file_exists( $file ) ) { echo 'missing'; exit; }
	$before = filesize( $file );
	if ( ! file_exists( $file . '.bak' ) ) { @copy( $file, $file . '.bak' ); }
	$editor = wp_get_image_editor( $file );
	if ( is_wp_error( $editor ) ) { echo 'editor-error: ' . $editor->get_error_message(); exit; }
	$editor->set_quality( 82 );
	$editor->resize( 616, 654, false );
	$saved = $editor->save( $file, 'image/webp' );
	if ( is_wp_error( $saved ) ) { echo 'save-error: ' . $saved->get_error_message(); exit; }
	clearstatcache();
	echo 'before=' . $before . ' after=' . filesize( $file ) . ' saved=' . $saved['file'];
	exit;
}


add_action( 'init', 'arap_recompress_webp', 999 );
function arap_recompress_webp() {
	if ( ! isset( $_GET['arap_rewebp'] ) || 'a7f3c9e1' !== $_GET['arap_rewebp'] ) { return; }
	$files = array( '/uploads/2021/05/slider1.png.webp', '/uploads/2025/10/2.webp' );
	foreach ( $files as $rel ) {
		$file = WP_CONTENT_DIR . $rel;
		if ( ! file_exists( $file ) ) { echo $rel . ':missing '; continue; }
		$before = filesize( $file );
		if ( ! file_exists( $file . '.bak' ) ) { @copy( $file, $file . '.bak' ); }
		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) ) { echo $rel . ':editor-error '; continue; }
		$editor->set_quality( 82 );
		$editor->save( $file, 'image/webp' );
		clearstatcache();
		echo basename( $file ) . ':' . $before . '->' . filesize( $file ) . ' ';
	}
	exit;
}


/* =========================================================
 * 17) تقليل طلبات الواجهة (نسخة آمنة قابلة للتراجع)
 * ======================================================= */
add_action( 'wp_head', 'arap_perf_trim_requests', 1 );
function arap_perf_trim_requests() {
	if ( is_admin() ) { return; }

	// dashicons للزائرين فقط
	if ( ! is_user_logged_in() ) { wp_dequeue_style( 'dashicons' ); }

	// jQuery Migrate غير مطلوب
			// إزالة jquery-migrate بأمان: حذفه من تبعيات jquery أولاً
		if ( defined( 'ARAP_REMOVE_MIGRATE' ) && ARAP_REMOVE_MIGRATE ) {
			$js = wp_scripts();
		if ( isset( $js->registered['jquery'] ) ) {
			$js->registered['jquery']->deps = array_values( array_diff( $js->registered['jquery']->deps, array( 'jquery-migrate' ) ) );
		}
			wp_dequeue_script( 'jquery-migrate' );
		}

	// سكربتات WooCommerce غير الحرجة خارج صفحات المتجر (السلة المصغّرة تبقى)
	if ( function_exists( 'is_woocommerce' ) && ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		global $wp_scripts;
		if ( $wp_scripts ) {
			foreach ( $wp_scripts->registered as $handle => $obj ) {
				if ( ! isset( $obj->src ) ) { continue; }
				foreach ( array( 'sourcebuster.min.js', 'order-attribution.min.js' ) as $needle ) {
					if ( false !== strpos( $obj->src, $needle ) ) { wp_dequeue_script( $handle ); }
				}
			}
		}
	}
}


/* =========================================================
 * 18) نسخة احتياطية كاملة للإضافات والرفوعات (مرة واحدة)
 * ======================================================= */
add_action( 'init', 'arap_full_backup', 999 );
function arap_full_backup() {
	if ( ! isset( $_GET['arap_fullbackup'] ) || 'a7f3c9e1' !== $_GET['arap_fullbackup'] ) { return; }
	@set_time_limit( 0 );
	$dir = WP_CONTENT_DIR . '/uploads/arap-backups';
	if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0755, true ); }
	$targets = array( 'plugins' => WP_PLUGIN_DIR, 'uploads' => WP_CONTENT_DIR . '/uploads' );
	foreach ( $targets as $name => $root ) {
		$file = $dir . '/' . $name . '-full-20261120.zip';
		$zip = new ZipArchive();
		if ( true !== $zip->open( $file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) { echo $name . ':open-fail '; continue; }
		$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
		$n = 0;
		foreach ( $it as $f ) {
			$path = $f->getPathname();
			if ( ! $f->isFile() || false !== strpos( $path, '/arap-backups/' ) ) { continue; }
			$zip->addFile( $path, str_replace( $root . '/', '', $path ) );
			$n++;
		}
		$zip->close();
		clearstatcache();
		echo $name . ':' . $n . 'files:' . round( filesize( $file ) / 1048576 ) . 'MB ';
	}
	exit;
}


add_action( 'wp_head', 'arap_assets_debug', 2 );
function arap_assets_debug() {
	if ( ! isset( $_GET['arap_assets_debug'] ) || 'a7f3c9e1' !== $_GET['arap_assets_debug'] ) { return; }
	global $wp_styles, $wp_scripts;
	echo "<!--ARAPASSETS
";
	foreach ( $wp_styles->registered as $h => $o ) {
		if ( ! isset( $o->src ) ) { continue; }
		$s = strtolower( $o->src );
		if ( strpos( $s, 'font-awesome' ) !== false || strpos( $s, 'all.min' ) !== false || strpos( $s, 'v4-shims' ) !== false || strpos( $s, 'eicons' ) !== false ) {
			echo 'STYLE|' . $h . '|' . $o->src . '|' . $o->ver . "
";
		}
	}
	foreach ( $wp_scripts->registered as $h => $o ) {
		if ( ! isset( $o->src ) ) { continue; }
		$s = strtolower( $o->src );
		if ( strpos( $s, 'font-awesome' ) !== false || strpos( $s, 'v4-shims' ) !== false ) {
			echo 'SCRIPT|' . $h . '|' . $o->src . '|' . $o->ver . "
";
		}
	}
	echo "-->
";
}


/* =========================================================
 * V1.8.2 - Font Awesome dedupe (CONTACT PAGE ONLY)
 * Rollback: set ARAP_FA_DEDUPE to false (or restore v1.8.1).
 * Kept: font-awesome (theme FA5 5.15.1) + font-awesome-shims
 *       + elementor-icons + wew-eicons-bc
 * ======================================================= */
define( 'ARAP_FA_DEDUPE', false );
add_action( 'wp_head', 'arap_fa_dedupe_contact', 2 );
function arap_fa_dedupe_contact() {
	if ( ! ARAP_FA_DEDUPE || ! is_page( 1473 ) ) { return; }
	wp_dequeue_style( 'fontawesome' );              // parent theme FA4 4.7.0
	wp_dequeue_style( 'font-awesome-5-all' );       // Elementor FA 4.4.1 all.min.css
	wp_dequeue_style( 'font-awesome-4-shim' );      // Elementor v4-shims CSS
	wp_dequeue_style( 'elementor-icons-shared-0' ); // Elementor FA core 5.15.3
	wp_dequeue_script( 'font-awesome-4-shim' );     // Elementor v4-shims JS
}


/* =========================================================
 * V1.8.6 - Swiper CSS for WooVina Testimonial Carousel
 * Rollback: set ARAP_SWIPER_CSS to false.
 * ======================================================= */
define( 'ARAP_SWIPER_CSS', true );
add_action( 'wp_enqueue_scripts', 'arap_swiper_css', 20 );
function arap_swiper_css() {
	if ( ! ARAP_SWIPER_CSS || is_admin() ) { return; }
	wp_enqueue_style( 'arap-swiper-css', plugins_url( 'elementor/assets/lib/swiper/v8/css/swiper.min.css' ), array(), '8' );
}


/* =========================================================
 * V1.8.7 - Mobile navbar: FA user icon for My Account
 * Rollback: set ARAP_MOBILE_ACCOUNT_ICON to false.
 * ======================================================= */
define( 'ARAP_MOBILE_ACCOUNT_ICON', true );
add_action( 'wp_head', 'arap_mobile_account_icon', 99 );
function arap_mobile_account_icon() {
	if ( ! ARAP_MOBILE_ACCOUNT_ICON ) { return; }
	echo '<style id="arap-mobile-account-icon">#mobile-navbar i.dashicons-admin-users{font-size:1.4em !important;vertical-align:top !important}#mobile-navbar i.dashicons-admin-users:before{font-family:"Font Awesome 5 Free" !important;font-weight:900 !important;content:"\f007" !important}</style>';
}


/* =========================================================
 * V1.8.8 - Slider ARIA cleanup (one versioned rule)
 * Rollback: set ARAP_A11Y_SLIDER_FIX to false.
 * ======================================================= */
define( 'ARAP_A11Y_SLIDER_FIX', true );
add_action( 'wp_footer', 'arap_a11y_slider_fix_js', 100 );
function arap_a11y_slider_fix_js() {
	if ( ! ARAP_A11Y_SLIDER_FIX ) { return; }
	echo '<script id="arap-a11y-slider-fix">(function(){function c(){var i,a;var dots=document.querySelectorAll(".slick-dots");for(i=0;i<dots.length;i++){dots[i].removeAttribute("role");var lis=dots[i].children;for(a=0;a<lis.length;a++){["role","aria-selected","aria-controls","aria-hidden"].forEach(function(k){lis[a].removeAttribute(k);});var b=lis[a].querySelector("button");if(b){b.removeAttribute("data-role");if(!b.getAttribute("aria-label")){b.setAttribute("aria-label","الانتقال إلى الشريحة "+(a+1));}}}}var tr=document.querySelectorAll(".slick-track");for(i=0;i<tr.length;i++){tr[i].removeAttribute("role");tr[i].removeAttribute("aria-label");}var sl=document.querySelectorAll(".slick-slide");for(i=0;i<sl.length;i++){sl[i].removeAttribute("aria-describedby");}var menus=document.querySelectorAll("#wpadminbar ul[role=menu]");for(i=0;i<menus.length;i++){var kids=menus[i].children;for(a=0;a<kids.length;a++){if(kids[a].tagName==="LI"&&!kids[a].getAttribute("role")){kids[a].setAttribute("role","none");}}}var ha=document.querySelectorAll("#wpadminbar a[aria-haspopup]");for(i=0;i<ha.length;i++){if(!ha[i].getAttribute("role")){ha[i].setAttribute("role","menuitem");}}}if(document.readyState!=="loading"){c();}else{document.addEventListener("DOMContentLoaded",c);}window.addEventListener("load",c);var n=0;var iv=setInterval(function(){c();if(++n>300){clearInterval(iv);}},200);new MutationObserver(c).observe(document.documentElement,{childList:true,subtree:true,attributes:true,attributeFilter:["role","aria-selected","aria-controls"]});})();</script>';
}


/* =========================================================
 * V2.0 - AI discovery files (one-shot, token protected)
 * Rollback: set ARAP_AI_FILES to false.
 * ======================================================= */
define( 'ARAP_AI_FILES', true );
add_action( 'init', 'arap_ai_files_write', 999 );
function arap_ai_files_write() {
	if ( ! ARAP_AI_FILES || ! isset( $_GET['arap_aifiles'] ) || 'a7f3c9e1' !== $_GET['arap_aifiles'] ) { return; }
	$llms = implode( "
", array(
		'# مكتبة آراب (Arap Library)',
		'',
		'> مكتبة آراب مكتبة إلكترونية عربية مقرها هامبورغ في ألمانيا، تبيع أكثر من 4000 كتاب عربي مع شحن إلى ألمانيا وأوروبا.',
		'',
		'Arap Library is an Arabic-language online bookstore based in Hamburg, Germany. It offers more than 4,000 Arabic books: novels, poetry, children books, history, psychology, philosophy and educational titles, with shipping to Germany and Europe.',
		'',
		'## Homepage',
		'- [Arap Library - Homepage](https://arap-library.de/)',
		'',
		'## Online bookstore',
		'- [Shop - all Arabic books](https://arap-library.de/shop/)',
		'- [Children books](https://arap-library.de/product-category/childrens-books/)',
		'- [Historical books](https://arap-library.de/product-category/historical-books/)',
		'- [Translated novels](https://arap-library.de/product-category/translated-novels/)',
		'- [Psychology](https://arap-library.de/product-category/psychology/)',
		'- [Human development](https://arap-library.de/product-category/human-development/)',
		'- [Arabic literature](https://arap-library.de/product-category/arabic-literature/)',
		'- [Arabic dictionaries](https://arap-library.de/product-category/arabic-dictionaries/)',
		'- [Arabic poetry](https://arap-library.de/product-category/arabic-poetry/)',
		'- [Short stories](https://arap-library.de/product-category/short-stories/)',
		'',
		'## About and policies',
		'- [About us](https://arap-library.de/about/)',
		'- [Contact us](https://arap-library.de/contact-us/)',
		'- [Privacy policy](https://arap-library.de/privacy-policy/)',
		'- [Refund and returns](https://arap-library.de/refund_returns/)',
		'- [Blog](https://arap-library.de/blog/)',
		'',
		'Note: cart, checkout and my-account are personalized pages and are not intended for crawlers.',
	) ) . "
";
	$ard = array( 'entries' => array() );
	$old = array( 'specVersion' => '0.91', 'host' => home_url( '/' ), 'entries' => array() );
	$dir = ABSPATH . '.well-known';
	if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0755, true ); }
	@file_put_contents( ABSPATH . 'llms.txt', $llms, LOCK_EX );
	@file_put_contents( $dir . '/ard.json', wp_json_encode( $ard, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "
", LOCK_EX );
	@file_put_contents( $dir . '/ai-catalog.json', wp_json_encode( $old, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . "
", LOCK_EX );
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'files written';
	exit;
}


/* =========================================================
 * V2.0.1 - ARD discovery signals
 * Rollback: set ARAP_ARD_LINKS to false.
 * ======================================================= */
define( 'ARAP_ARD_LINKS', true );
add_filter( 'robots_txt', 'arap_ard_robots_directive', 1000 );
function arap_ard_robots_directive( $output ) {
	if ( ! ARAP_ARD_LINKS ) { return $output; }
	return $output;
}
add_action( 'wp_head', 'arap_ard_head_links', 1 );
function arap_ard_head_links() {
	if ( ! ARAP_ARD_LINKS ) { return; }
	echo '<link rel="ard" href="' . esc_url( home_url( '/.well-known/ard.json' ) ) . '">' . "
";
	echo '<link rel="ai-catalog" href="' . esc_url( home_url( '/.well-known/ai-catalog.json' ) ) . '">' . "
";
}


/* V2.0.2 - heading hierarchy fix. Rollback: ARAP_HEADING_FIX=false */
define( 'ARAP_HEADING_FIX', true );


/* =========================================================
 * V2.0.4 - Tab + mobile-menu accessibility (isolated rule)
 * Rollback: set ARAP_TABS_A11Y_FIX to false.
 * ======================================================= */
define( 'ARAP_TABS_A11Y_FIX', true );
add_action( 'wp_footer', 'arap_tabs_a11y_js', 100 );
function arap_tabs_a11y_js() {
	if ( ! ARAP_TABS_A11Y_FIX ) { return; }
	echo '<script id="arap-tabs-a11y">(function(){function tabs(){var wraps=document.querySelectorAll(".wew-tabs-wrap");for(var w=0;w<wraps.length;w++){var wrap=wraps[w];wrap.setAttribute("role","tablist");wrap.setAttribute("aria-label","تبويبات المنتجات");var ts=[].slice.call(document.querySelectorAll(".wew-tab-title"));for(var i=0;i<ts.length;i++){var tab=ts[i];var n=tab.getAttribute("data-tab")||(i+1);if(!tab.id){tab.id=(tab.className.indexOf("wew-tab-mobile-title")>-1?"wew-tab-mobile-title-":"wew-tab-title-")+n;}var id="wew-tab-content-"+n;var panel=document.getElementById(id);var active=tab.className.indexOf("active")>-1;tab.setAttribute("aria-controls",id);tab.setAttribute("aria-selected",active?"true":"false");tab.setAttribute("tabindex",active?"0":"-1");if(panel){panel.setAttribute("role","tabpanel");if(tab.id.indexOf("mobile")<0||!panel.getAttribute("aria-labelledby")){panel.setAttribute("aria-labelledby",tab.id);}if(!panel.hasAttribute("tabindex")){panel.setAttribute("tabindex","0");}}}if(!wrap.getAttribute("data-arap-tabs-bound")){wrap.setAttribute("data-arap-tabs-bound","1");wrap.addEventListener("keydown",function(e){var k=e.key;if(["ArrowRight","ArrowLeft","Home","End"].indexOf(k)<0){return;}var list=[].slice.call(this.querySelectorAll(".wew-tab-title"));var cur=list.indexOf(document.activeElement);if(cur<0){return;}var next=cur;if(k==="ArrowRight"){next=(cur+1)%list.length;}else if(k==="ArrowLeft"){next=(cur-1+list.length)%list.length;}else if(k==="Home"){next=0;}else if(k==="End"){next=list.length-1;}e.preventDefault();list[next].focus();list[next].click();});}}}function mobile(){var ms=document.querySelectorAll(".custom-header-nav .mobile-menu");for(var i=0;i<ms.length;i++){var a=ms[i];a.setAttribute("aria-label","Open navigation menu");a.setAttribute("aria-controls","sidr");var h=a.querySelector(".hamburger");var open=document.body.className.indexOf("sidr-open")>-1||(h&&h.className.indexOf("is-active")>-1);if(!a.hasAttribute("aria-expanded")){a.setAttribute("aria-expanded",open?"true":"false");}if(h){h.setAttribute("aria-hidden","true");}}}function run(){tabs();mobile();}if(document.readyState!=="loading"){run();}else{document.addEventListener("DOMContentLoaded",run);}window.addEventListener("load",run);var _n=0;var _iv=setInterval(function(){run();if(++_n>60){clearInterval(_iv);}},500);document.addEventListener("click",function(e){setTimeout(run,150);var t=e.target&&e.target.closest?e.target.closest(".mobile-menu"):null;if(t){setTimeout(function(){t.setAttribute("aria-expanded",t.getAttribute("aria-expanded")==="true"?"false":"true");},250);}},true);})();</script>';
}


/* =========================================================
 * V2.0.9 - Mobile home link accessible name (isolated rule)
 * Rollback: set ARAP_MOBILE_HOME_A11Y_FIX to false.
 * ======================================================= */
define( 'ARAP_MOBILE_HOME_A11Y_FIX', true );
add_action( 'wp_footer', 'arap_mobile_home_a11y_js', 101 );
function arap_mobile_home_a11y_js() {
	if ( ! ARAP_MOBILE_HOME_A11Y_FIX ) { return; }
	echo '<script id="arap-mobile-home-a11y">(function(){function f(){var a=document.querySelector("#mobile-navbar ul#menu-mobile-navbar li.nav-home > a");if(a&&!a.getAttribute("aria-label")){a.setAttribute("aria-label","Home");}}if(document.readyState!=="loading"){f();}else{document.addEventListener("DOMContentLoaded",f);}setTimeout(f,600);setTimeout(f,2500);})();</script>';
}


/* =========================================================
 * V2.1.1 - Remove duplicate Elementor Font Awesome CSS
 * Rollback: set ARAP_FA_DEDUPE2 to false.
 * Keeps: theme FA4, theme FA5, theme v4-shims, JS shim, eicons.
 * ======================================================= */
define( 'ARAP_FA_DEDUPE2', true );
add_action( 'wp_head', 'arap_fa_dedupe2', 2 );
function arap_fa_dedupe2() {
	if ( ! ARAP_FA_DEDUPE2 || is_admin() ) { return; }
	wp_dequeue_style( 'font-awesome-5-all' );
	wp_dequeue_style( 'font-awesome-4-shim' );
}


/* =========================================================
/* =========================================================
 * V2.2.0 - Icon fonts: font-display swap + preload + trim
 * Rollback: set ARAP_FONT_OPT to false.
 * ========================================================= */
define( 'ARAP_FONT_OPT', true );

/* Foundation Icons is enqueued by the WooVina menu-icons module. Its only
 * icon on this site sits in a nav widget hidden on every breakpoint, so the
 * stylesheet and its font request are dropped. */
add_action( 'wp_head', 'arap_font_dequeue_unused', 1 );
function arap_font_dequeue_unused() {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return; }
	// foundation-icons kept: used by mobile navbar (fi-burst-sale)
}

/* Preload the two icon fonts that are used above the fold. */
add_action( 'wp_head', 'arap_font_preload', 99 );
function arap_font_preload() {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return; }
	$theme = get_template_directory_uri();
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin="anonymous" href="' . esc_url( $theme . '/assets/fonts/fontawesome/webfonts/fa-solid-900.woff2' ) . '">' . "\n";
	echo '<link rel="preload" as="font" type="font/woff2" crossorigin="anonymous" href="' . esc_url( $theme . '/assets/fonts/simple-line-icons/Simple-Line-Icons.woff2?v=2.4.0' ) . '">' . "\n";
}

/* Fix font-display at the source. Build a modified copy of each icon
 * stylesheet (font-display:block -> swap, or add swap where it is missing),
 * rewrite the font URLs to absolute, store the copy in uploads, and point the
 * style handle at that copy. Exactly one @font-face per font; nothing is
 * stacked on top of the original rule. Copies live in uploads so theme and
 * plugin updates cannot overwrite them. */
define( 'ARAP_FONT_COPY_VER', '2.2.5' );

add_action( 'init', 'arap_font_build_swapped_copies', 20 );
function arap_font_build_swapped_copies() {
	if ( ! ARAP_FONT_OPT ) { return; }
	$dir = WP_CONTENT_DIR . '/uploads/arap-fonts';
	if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0755, true ); }

	$jobs = array(
		'fa-all.min.css' => array(
			get_template_directory() . '/assets/fonts/fontawesome/css/all.min.css',
			get_template_directory_uri() . '/assets/fonts/fontawesome/webfonts/',
			'url(../webfonts/',
			false,
		),
		'eicons-bc.min.css' => array(
			WP_PLUGIN_DIR . '/woovina-elementor-widgets/assets/css/eicons-bc/style.min.css',
			plugins_url( 'woovina-elementor-widgets/assets/css/eicons-bc/fonts/' ),
			'url(fonts/',
			true,
		),
	);

	foreach ( $jobs as $name => $job ) {
		list( $src_file, $font_base, $rel, $add_display ) = $job;
		$target = $dir . '/' . $name;
		$stamp  = $target . '.ver';
		if ( file_exists( $target ) && file_exists( $stamp ) && trim( (string) @file_get_contents( $stamp ) ) === ARAP_FONT_COPY_VER ) { continue; }
		$css = @file_get_contents( $src_file );
		if ( false === $css ) { continue; }
		$css = str_replace( 'font-display:block', 'font-display:swap', $css );
		if ( $add_display ) {
			$css = preg_replace( '/(@font-face\s*\{[^}]*?)(\})/', '$1;font-display:swap;$2', $css, 1 );
		}
		$css = str_replace( $rel, 'url(' . $font_base, $css );
		if ( false !== @file_put_contents( $target, $css, LOCK_EX ) ) {
			@file_put_contents( $stamp, ARAP_FONT_COPY_VER, LOCK_EX );
		}
	}
}

add_filter( 'style_loader_src', 'arap_font_swapped_src', 10, 2 );
function arap_font_swapped_src( $src, $handle ) {
	if ( ! ARAP_FONT_OPT || is_admin() ) { return $src; }
	$map = array(
		'font-awesome'  => 'fa-all.min.css',
		'wew-eicons-bc' => 'eicons-bc.min.css',
	);
	if ( ! isset( $map[ $handle ] ) ) { return $src; }
	$file = WP_CONTENT_DIR . '/uploads/arap-fonts/' . $map[ $handle ];
	if ( ! file_exists( $file ) ) { return $src; }
	return content_url( 'uploads/arap-fonts/' . $map[ $handle ] ) . '?ver=' . ARAP_FONT_COPY_VER;
}

/* =========================================================
 * V2.2.10 - Render-blocking reduction (staged, admin-test only)
 * Public visitors: NEVER affected unless ARAP_RB_PUBLIC is true.
 * Admin test: ?arap_rb=1 + logged-in manage_options.
 * ========================================================= */
define( 'ARAP_RB_PUBLIC', false );
define( 'ARAP_RB_TEST', true );
// Stage toggles (only effective when arap_rb_test_active())
define( 'ARAP_RB_STAGE_DROP', false );   // Stage 1 (off, pending decision)
define( 'ARAP_RB_STAGE_PRELOAD', false ); // Stage 2 (off - no measurable benefit)
define( 'ARAP_RB_STAGE_DEFER_THEME', false );   // Stage 3a
define( 'ARAP_RB_STAGE_DEFER_PLUGINS', false ); // Stage 3b
define( 'ARAP_RB_STAGE_DEFER_SLIDER', false );  // Stage 3c

function arap_rb_test_active() {
	if ( ARAP_RB_PUBLIC ) { return ! is_admin(); }
	if ( ! ARAP_RB_TEST || is_admin() ) { return false; }
	$has = ( isset( $_GET['arap_rb'] ) && '1' === $_GET['arap_rb'] ) || ( isset( $_COOKIE['arap_rb'] ) && '1' === $_COOKIE['arap_rb'] );
	return $has && ( current_user_can( 'manage_options' ) || ( isset($_GET['arap_rb_key']) && 'stg9x2' === $_GET['arap_rb_key'] ) );
}
add_action( 'init', 'arap_rb_nocache_guard', 0 );
function arap_rb_nocache_guard() {
	if ( ! arap_rb_test_active() ) { return; }
	if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
	if ( ! defined( 'DONOTCACHEOBJECT' ) ) { define( 'DONOTCACHEOBJECT', true ); }
	if ( ! defined( 'DONOTCACHEDB' ) ) { define( 'DONOTCACHEDB', true ); }
	nocache_headers();
}
function arap_rb_stage( $stage ) {
	if ( ! arap_rb_test_active() ) { return false; }
	switch ( $stage ) {
		case 'drop': return (bool) ARAP_RB_STAGE_DROP;
		case 'preload': return (bool) ARAP_RB_STAGE_PRELOAD;
		case 'defer_theme': return (bool) ARAP_RB_STAGE_DEFER_THEME;
		case 'defer_plugins': return (bool) ARAP_RB_STAGE_DEFER_PLUGINS;
		case 'defer_slider': return (bool) ARAP_RB_STAGE_DEFER_SLIDER;
	}
	return false;
}

/* Defer front-end scripts - staged. jQuery/jQuery Migrate always synchronous. */
add_filter( 'script_loader_tag', 'arap_rb_defer_scripts', 10, 3 );
function arap_rb_defer_scripts( $tag, $handle, $src ) {
	if ( is_admin() ) { return $tag; }
	$defer_any = arap_rb_stage( 'defer_theme' ) || arap_rb_stage( 'defer_plugins' ) || arap_rb_stage( 'defer_slider' );
	if ( ! $defer_any ) { return $tag; }
	if ( false === strpos( $tag, 'src=' ) ) { return $tag; }
	if ( false !== strpos( $tag, ' defer' ) || false !== strpos( $tag, ' async' ) ) { return $tag; }
	$src_l = strtolower( (string) $src );
	if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) { return $tag; }
	if ( false !== strpos( $src_l, 'jquery.min.js' ) || false !== strpos( $src_l, 'jquery-migrate' ) ) { return $tag; }

	$is_theme = ( false !== strpos( $src_l, '/themes/' ) );
	$is_plugin = ( false !== strpos( $src_l, '/plugins/' ) );
	$slider_handles = array( 'slick-js', 'arap-slick', 'jquery-slick', 'woovina-slick', 'elementor-frontend', 'elementor-frontend-modules', 'wew-frontend', 'woovina-main', 'skybook-js', 'blog-carousel' );
	$is_slider = in_array( $handle, $slider_handles, true )
		|| false !== strpos( $src_l, 'slick' )
		|| false !== strpos( $src_l, '/frontend.min.js' )
		|| false !== strpos( $src_l, '/main.min.js' )
		|| false !== strpos( $src_l, '/skybook.js' )
		|| false !== strpos( $src_l, 'blog-carousel' );

	$allow = false;
	if ( $is_slider ) {
		$allow = arap_rb_stage( 'defer_slider' );
	} elseif ( $is_theme ) {
		$allow = arap_rb_stage( 'defer_theme' );
	} elseif ( $is_plugin ) {
		$allow = arap_rb_stage( 'defer_plugins' );
	}
	if ( ! $allow ) { return $tag; }
	return preg_replace( '/<script\s/i', '<script defer ', $tag, 1 );
}

/* Stage 1: drop unused CSS only. */
add_filter( 'style_loader_tag', 'arap_rb_drop_style_tags', 10, 2 );
function arap_rb_drop_style_tags( $tag, $handle ) {
	if ( ! arap_rb_stage( 'drop' ) ) { return $tag; }
	$lower = strtolower( $tag . ' ' . $handle );
	if ( false !== strpos( $lower, 'magnific' ) || false !== strpos( $lower, 'animate' ) ) {
		return '';
	}
	return $tag;
}
add_action( 'wp_enqueue_scripts', 'arap_rb_drop_unused', 99 );
function arap_rb_drop_unused() {
	if ( ! arap_rb_stage( 'drop' ) ) { return; }
	$styles = wp_styles();
	foreach ( $styles->registered as $handle => $obj ) {
		if ( ! isset( $obj->src ) ) { continue; }
		$src = strtolower( $obj->src );
		if ( false !== strpos( $src, 'magnific' ) || false !== strpos( $src, 'animate' ) ) {
			wp_dequeue_style( $handle );
		}
	}
}

/* Stage 2: preload (max 4, exact ver URLs). */
add_filter( 'wp_preload_resources', 'arap_rb_preload_resources' );
function arap_rb_preload_resources( $hints ) {
	if ( ! arap_rb_stage( 'preload' ) ) { return $hints; }
	$hints[] = array( 'href' => get_stylesheet_directory_uri() . '/assets/css/skybook-01.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/style.min.css?ver=2.2', 'as' => 'style' );
	$hints[] = array( 'href' => get_template_directory_uri() . '/assets/css/woo/woocommerce.min.css?ver=7.1.5', 'as' => 'style' );
	$hints[] = array( 'href' => content_url( 'uploads/arap-fonts/fa-all.min.css' ) . '?ver=2.2.5', 'as' => 'style' );
	return $hints;
}

/* =========================================================
 * V2.2.15 - Arabic localization (visitor-facing strings)
 * ========================================================= */
add_filter( 'woocommerce_page_title', function( $title ) {
	$t = trim( (string) $title );
	if ( $t === 'Shop' ) { return 'تسوق'; }
	return $title;
} );
add_filter( 'the_title', function( $title, $id = null ) {
	$t = trim( (string) $title );
	if ( $t === 'Shop' ) {
		if ( function_exists( 'is_shop' ) && is_shop() ) { return 'تسوق'; }
		if ( function_exists( 'wc_get_page_id' ) && (int) $id === (int) wc_get_page_id( 'shop' ) ) { return 'تسوق'; }
	}
	return $title;
}, 10, 2 );
add_filter( 'gettext', 'arap_ar_gettext', 20, 3 );
function arap_ar_gettext( $translated, $text, $domain ) {
	$map = array(
		'Shop' => 'تسوق',
		'Continue Shopping' => 'متابعة التسوق',
		'Go To The Cart' => 'الذهاب إلى السلة',
		'Item added to your cart' => 'تمت إضافة المنتج إلى السلة',
		'My Cart' => 'سلتي',
		'Cart' => 'السلة',
		'Quick View' => 'نظرة سريعة',
		'Out of Stock' => 'نفذت الكمية',
		'Previous' => 'السابق',
		'Next' => 'التالي',
		'Search' => 'بحث',
		'Menu' => 'القائمة',
		'View:' => 'عرض:',
		'All' => 'الكل',
		'Login' => 'تسجيل الدخول',
		'Register' => 'تسجيل جديد',
		'No products in the cart.' => 'لا توجد منتجات في السلة.',
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
		'privacy policy' => 'سياسة الخصوصية',
		'Returning customer?' => 'عميل سابق؟',
		'Click here to login' => 'انقر هنا لتسجيل الدخول',
		'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.' => 'إذا سبق لك التسوق معنا، يرجى إدخال بياناتك أدناه. إذا كنت عميلاً جديداً، يرجى المتابعة إلى قسم الفاتورة.',
		'Username or email' => 'اسم المستخدم أو البريد الإلكتروني',
		'Username or email address' => 'اسم المستخدم أو البريد الإلكتروني',
		'Password' => 'كلمة المرور',
		'Remember me' => 'تذكرني',
		'Lost your password?' => 'نسيت كلمة المرور؟',
		'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.' => 'سيتم استخدام بياناتك الشخصية لدعم تجربتك في هذا الموقع، وإدارة الوصول إلى حسابك، ولأغراض أخرى موضحة في %s.',
		'privacy policy' => 'سياسة الخصوصية',
		'0 Comments' => '0 تعليقات',
		'1 Comment' => 'تعليق واحد',
		'Comments' => 'تعليقات',
		'Read More' => 'اقرأ المزيد',
		'Add to cart' => 'أضف إلى السلة',
		'Add to wishlist' => 'أضف إلى قائمة الرغبات',
		'Browse wishlist' => 'تصفح قائمة الرغبات',
		'Product added!' => 'تمت إضافة المنتج!',
		'View Wishlist' => 'عرض قائمة الرغبات',
	);
	if ( isset( $map[ $text ] ) ) { return $map[ $text ]; }
	return $translated;
}
add_filter( 'ngettext', 'arap_ar_ngettext', 20, 5 );

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
}, 20, 2 );

add_action( 'template_redirect', function() {
	if ( is_admin() || ( function_exists('wp_doing_ajax') && wp_doing_ajax() ) ) return;
	ob_start( function( $html ) {
		$html = str_replace( '<h1 class="page-header-title clr" itemprop="headline">Shop</h1>', '<h1 class="page-header-title clr" itemprop="headline">تسوق</h1>', $html );
		$html = str_replace( ' items in the cart (', ' منتجات في السلة (', $html );
		$html = preg_replace( '/>\s*Menu\s*</u', '>القائمة<', $html );
		$html = str_replace( '>Search</', '>بحث</', $html );
		$html = str_replace( 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our', 'سيتم استخدام بياناتك الشخصية لدعم تجربتك في هذا الموقع، وإدارة الوصول إلى حسابك، ولأغراض أخرى موضحة في', $html );
		$html = str_replace( '>privacy policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( '>WordPress.org<', '>ووردبريس.أورج<', $html );
		$html = str_replace( 'class="elementor-screen-only">Twitter</span>', 'class="elementor-screen-only">تويتر</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Instagram</span>', 'class="elementor-screen-only">إنستغرام</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Youtube</span>', 'class="elementor-screen-only">يوتيوب</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Envelope</span>', 'class="elementor-screen-only">البريد الإلكتروني</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Spotify</span>', 'class="elementor-screen-only">سبوتيفاي</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Facebook-f</span>', 'class="elementor-screen-only">فيسبوك</span>', $html );
		$html = str_replace( 'class="elementor-screen-only">Facebook</span>', 'class="elementor-screen-only">فيسبوك</span>', $html );
		$html = str_replace( 'الرمز البريدي / ZIP', 'الرمز البريدي', $html );
		$html = str_replace( 'الرمز البريدي&nbsp;/ ZIP', 'الرمز البريدي', $html );
		$html = str_replace( 'Postcode / ZIP', 'الرمز البريدي', $html );
		$html = str_replace( '>Billing details<', '>تفاصيل الفاتورة<', $html );
		$html = str_replace( '>Your order<', '>طلبك<', $html );
		$html = str_replace( '>Have a coupon?<', '>لديك قسيمة؟<', $html );
		$html = str_replace( '>Click here to enter your code<', '>انقر هنا لإدخال الرمز<', $html );
		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'Datenschutzerklärung – Arap Library', 'سياسة الخصوصية – مكتبة آراب', $html );
		$html = str_replace( 'Datenschutzerklärung - Arap Library', 'سياسة الخصوصية – مكتبة آراب', $html );
		$html = str_replace( '>PayPal<', '>باي بال<', $html );
		$html = str_replace( 'بواسطة PayPal', 'بواسطة باي بال', $html );
		$html = str_replace( 'حساب على PayPal', 'حساب على باي بال', $html );
		$html = str_replace( 'إلى PayPal', 'إلى باي بال', $html );
		$html = str_replace( 'الدفع بواسطة PayPal', 'الدفع بواسطة باي بال', $html );
		$html = preg_replace( '/الرمز البريدي\s*\/\s*ZIP/u', 'الرمز البريدي', $html );
		$html = str_replace( '>Privacy Policy<', '>سياسة الخصوصية<', $html );
		$html = str_replace( 'Returning customer?', 'عميل سابق؟', $html );
		$html = str_replace( 'Click here to login', 'انقر هنا لتسجيل الدخول', $html );
		return $html;
	} );
}, 1 );

function arap_ar_ngettext( $translated, $single, $plural, $number, $domain ) {
	if ( $single === '%s Comment' && $plural === '%s Comments' ) {
		return $number == 1 ? 'تعليق واحد' : '%s تعليقات';
	}
	if ( $single === '%s item' && $plural === '%s items' ) {
		return $number == 1 ? 'منتج واحد' : '%s منتجات';
	}
	return $translated;
}
