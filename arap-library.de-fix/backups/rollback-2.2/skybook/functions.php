<?php
/**
 * Skybook theme functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Text Domain: skybook
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

if(is_admin()) {

	// Requires & Recommend plugins
	if(! class_exists('TGM_Plugin_Activation')) {
		require_once(get_stylesheet_directory() .'/includes/class-tgm-plugin-activation.php');
	}
	require_once(get_stylesheet_directory() .'/includes/tgm-plugin-activation.php');
	
	if(! class_exists('ThemeUpdateChecker')) {
		require_once(get_stylesheet_directory() .'/includes/theme-update-checker.php');
	}
	$theme_updater = new ThemeUpdateChecker('skybook', 'https://woovina.com/');
	
	// Activate License Key
	if(class_exists('WooVina_Theme_Licenses')) {
		$license = new WooVina_Theme_Licenses('Child Theme', 'Skybook');		
	}
}
 
 
/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function skybook_enqueue_parent_style() {
	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme('WooVina');
	$version = $theme->get('Version');
	// Load the stylesheet
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('woovina-style'), $version);
	
}
add_action('wp_enqueue_scripts', 'skybook_enqueue_parent_style');


/**
 * Load the child theme demo CSS file
 *
 */
function skybook_enqueue_style() {
	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme('Skybook');
	$version = $theme->get('Version');
	
	// Set default CSS
	if(!get_theme_mod('woovina_css_file')) {
		set_theme_mod('woovina_css_file', 'skybook-01.css');
	}
		
	// Load the stylesheet
	wp_enqueue_style('woovina-niche', get_stylesheet_directory_uri() . '/assets/css/' . get_theme_mod('woovina_css_file'), false, $version);
	
	// Load custom JS
	wp_enqueue_script('skybook-js', get_stylesheet_directory_uri() . '/assets/js/skybook.js', array('jquery'), $version, true);
}
add_action('wp_enqueue_scripts', 'skybook_enqueue_style');

add_filter('woocommerce_get_image_size_gallery_thumbnail', function($size) { return array('width' => 90, 'height' => 120, 'crop' => 1); });


/**
 * Run copyright removal
 *
 * @since 1.7
 */
function skybook_copyright_removal() {
	$theme 	 			= wp_get_theme();
	$license 			= get_option('edd_license_details');
	$license_details 	= (isset($license) && isset($license['woovina_skybook'])) ? $license['woovina_skybook'] : false;
	
	if(! empty($license_details) && is_object($license_details) && true === $license_details->success && 'Skybook Free' != $license_details->item_name) {
		return false;
	}
	
	echo '<div id="woovina-copyright" class="hide-tablet hide-mobile">Powered by <br><a href="https://themeforest.net/item/x/19710474" title="WooCommerce Theme For Book Store" target="_blank">Skybook Theme</a></div>';	
}
add_action('woovina_copyright_removal', 'skybook_copyright_removal');


/**
 * Show activate notice
 *
 * @since 1.7
 */
function skybook_activate_notice() {
	$license 			= get_option('edd_license_details');
	$license_details 	= (isset($license) && isset($license['woovina_skybook'])) ? $license['woovina_skybook'] : false;
	
	if(!class_exists('WooVina_Extra') || (! empty($license_details) && is_object($license_details) && true === $license_details->success)) {
		return false;
	}
	
	?>    
	<div id="woovina-admin-notice" class="updated notice is-dismissible" style="padding-top: 10px;">
        <strong><?php _e('Thanks for using Skybook Theme', 'skybook'); ?></strong>
		<p><?php _e('Please activate your license to get feature updates, premium support!', 'skybook'); ?>
			<br><?php echo sprintf(
				__('If you don\'t have any license key, you can %1$sPurchase a license here%2$s.', 'skybook'),
				'<a href="https://themeforest.net/cart/configure_before_adding/19710474?license=regular&support=bundle_12month" target="_blank" title="Purchase a license key!">',
				'</a>'
			); ?></p>
		<p><a class="btn button-primary" href="admin.php?page=woovina-panel-licenses"><?php _e('Activate Now', 'skybook'); ?></a></p>
    </div>
    <?php
}
add_action('admin_notices', 'skybook_activate_notice');


/**
 * Show renewal notice
 *
 * @since 1.7
 */
function skybook_renewal_notice() {
	$license 			= get_option('edd_license_details');
	$license_details 	= (isset($license) && isset($license['woovina_skybook'])) ? $license['woovina_skybook'] : false;
	
	$now        	= current_time('timestamp');
	$expire_date	= isset($license_details->expires) && trim($license_details->expires) != '' ? $license_details->expires : '';
	$expiration 	= strtotime($expire_date, current_time('timestamp'));
	
	if(! empty($license_details) && is_object($license_details) && true === $license_details->success && $expiration < $now)
	{
	?>
    <div id="woovina-admin-notice" class="updated notice is-dismissible" style="padding-top: 10px;">
        <strong><?php _e('Your license has expired!', 'skybook'); ?></strong>
		<p><?php _e('Whoops! It looks like your Skybook license has expired! That means that you will not receive automatic updates.', 'skybook'); ?>
			<br><?php _e('You can renew your license by click on the below button!', 'skybook'); ?></p>
		<p><a class="btn button-primary" href="https://themeforest.net/cart/configure_before_adding/19710474?license=regular&support=bundle_12month" target="_blank"><?php _e('Renew License Now', 'skybook'); ?></a></p>
    </div>
    <?php
	}
}
add_action('admin_notices', 'skybook_renewal_notice');


/**
 * Get Skybook demos.
 * 
 * @since   1.4
 */
function skybook_get_demos() {	
	$theme_url  = get_stylesheet_directory_uri() . '/demos/';
	$theme_dir  = get_stylesheet_directory() . '/demos/';
	
	$data = array(
		'Layout 1' => array(
			'xml_file'     		=> $theme_dir . 'skybook-01/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-01/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-01/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-01/skybook-01.jpg',
			'form_file'  		=> $theme_url . 'skybook-01/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-01/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-01.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),

		'Layout 2' => array(
			'xml_file'     		=> $theme_dir . 'skybook-02/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-02/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-02/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-02/skybook-02.jpg',
			'form_file'  		=> $theme_url . 'skybook-02/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-02/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-02.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),

		'Layout 3' => array(
			'xml_file'     		=> $theme_dir . 'skybook-03/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-03/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-03/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-03/skybook-03.jpg',
			'form_file'  		=> $theme_url . 'skybook-03/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-03/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-03.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),

		'Layout 4' => array(
			'xml_file'     		=> $theme_dir . 'skybook-04/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-04/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-04/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-04/skybook-04.jpg',
			'form_file'  		=> $theme_url . 'skybook-04/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-04/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-04.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),

		'Layout 5' => array(
			'xml_file'     		=> $theme_dir . 'skybook-05/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-05/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-05/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-05/skybook-05.jpg',
			'form_file'  		=> $theme_url . 'skybook-05/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-05/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-05.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(	
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),

		'Layout 6' => array(
			'xml_file'     		=> $theme_dir . 'skybook-06/contents.xml',
			'theme_settings' 	=> $theme_url . 'skybook-06/customizer.json',
			'widgets_file'  	=> $theme_url . 'skybook-06/widgets.wie',
			'preview_image'		=> $theme_url . 'skybook-06/skybook-06.jpg',
			'form_file'  		=> $theme_url . 'skybook-06/form.json',
			'preview_url'		=> 'https://skybook.woovina.net/demo-06/',
			'home_title'  		=> 'Home',
			'blog_title'  		=> 'Blog',
			'posts_to_show'  	=> '12',
			'elementor_width'  	=> '1200',
			'css_file'			=> 'skybook-06.css',
			'woo_image_size'	=> '600',
			'woo_thumb_size'	=> '300',
			'woo_crop_width'	=> '60',
			'woo_crop_height'	=> '77',
			'required_plugins'  => array(
				'free' => array(
					array(
						'slug' 		=> 'elementor',
						'init' 		=> 'elementor/elementor.php',
						'name' 		=> 'Elementor',
					),
					array(
						'slug' 		=> 'woocommerce',
						'init' 		=> 'woocommerce/woocommerce.php',
						'name' 		=> 'WooCommerce',
					),
					array(
						'slug'  	=> 'wpforms-lite',
						'init'  	=> 'wpforms-lite/wpforms.php',
						'name'  	=> 'WPForms',
					),
				),
				'premium' => array(
					array(
						'slug' 		=> 'woovina-sticky-header',
						'init' 		=> 'woovina-sticky-header/woovina-sticky-header.php',
						'name' 		=> 'WooVina Sticky Header',
					),
					array(
						'slug' 		=> 'woovina-preloader',
						'init'  	=> 'woovina-preloader/woovina-preloader.php',
						'name' 		=> 'WooVina Preloader',
					),
					array(
						'slug' 		=> 'woovina-product-sharing',
						'init' 		=> 'woovina-product-sharing/woovina-product-sharing.php',
						'name' 		=> 'WooVina Product Sharing',
					),
					array(
						'slug' 		=> 'woovina-popup-login',
						'init' 		=> 'woovina-popup-login/woovina-popup-login.php',
						'name' 		=> 'WooVina Popup Login',
					),
					array(
						'slug' 		=> 'woovina-woo-popup',
						'init' 		=> 'woovina-woo-popup/woovina-woo-popup.php',
						'name' 		=> 'WooVina Woo Popup',
					),
					array(
						'slug'  	=> 'woovina-variation-swatches',
						'init'  	=> 'woovina-variation-swatches/woovina-variation-swatches.php',
						'name'  	=> 'WooVina Variation Swatches',
					),
				),
			),
		),
	);
	
	// Return
	return $data;
}
add_filter('wvn_demos_data', 'skybook_get_demos');