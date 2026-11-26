<?php
/**
 * Yalla Arab Kulturfestival — theme bootstrap.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YALLA_VERSION', '1.6.8' );
define( 'YALLA_DIR', get_template_directory() );
define( 'YALLA_URI', get_template_directory_uri() );

require_once YALLA_DIR . '/inc/setup.php';
require_once YALLA_DIR . '/inc/i18n.php';
require_once YALLA_DIR . '/inc/content-types.php';
require_once YALLA_DIR . '/inc/meta-boxes.php';
require_once YALLA_DIR . '/inc/template-tags.php';
require_once YALLA_DIR . '/inc/customizer.php';
require_once YALLA_DIR . '/inc/seo.php';
require_once YALLA_DIR . '/inc/rank-math.php';
require_once YALLA_DIR . '/inc/redirects.php';
require_once YALLA_DIR . '/inc/ai.php';
require_once YALLA_DIR . '/inc/security.php';
require_once YALLA_DIR . '/inc/consent.php';
require_once YALLA_DIR . '/inc/forms.php';
require_once YALLA_DIR . '/inc/admin-ux.php';
require_once YALLA_DIR . '/inc/seed.php';
require_once YALLA_DIR . '/inc/quick-event.php';
require_once YALLA_DIR . '/inc/duplicates.php';

/**
 * Asset URL helper.
 *
 * @param string $path Relative path inside /assets.
 * @return string
 */
function yd_asset( $path = '' ) {
	return YALLA_URI . '/assets/' . ltrim( $path, '/' );
}
