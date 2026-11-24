<?php
/**
 * Crude static check: find calls to undefined global functions in the plugin.
 * Compares against functions declared in a local WordPress copy + PHP builtins + plugin itself.
 */
$wpDirs = array(
	'D:/oben code/wordpress/wp-includes',
	'D:/oben code/wordpress/wp-admin/includes',
);
$pluginDir = 'D:/oben code/yalla-engage-extract/yalla-engage';

$defined = array();

function scan_defs( $dir, &$defined ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( 'php' !== strtolower( $f->getExtension() ) ) {
			continue;
		}
		$src = file_get_contents( $f->getPathname() );
		if ( preg_match_all( '/\bfunction\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $src, $m ) ) {
			foreach ( $m[1] as $name ) {
				$defined[ strtolower( $name ) ] = true;
			}
		}
	}
}

foreach ( $wpDirs as $d ) {
	if ( is_dir( $d ) ) {
		scan_defs( $d, $defined );
	}
}
scan_defs( $pluginDir, $defined );

foreach ( get_defined_functions()['internal'] as $fn ) {
	$defined[ strtolower( $fn ) ] = true;
}

// Very common WP functions that may live outside the scanned dirs.
foreach ( array( 'wp_mail', 'get_option', 'update_option', 'add_option', 'delete_option', 'current_user_can', 'wp_verify_nonce', 'check_admin_referer', 'wp_safe_redirect', 'wp_redirect', 'admin_url', 'site_url', 'home_url', 'wp_nonce_url', 'wp_nonce_field', 'wp_create_nonce', 'sanitize_text_field', 'sanitize_email', 'sanitize_key', 'sanitize_title', 'absint', 'esc_html', 'esc_attr', 'esc_url', 'esc_js', 'esc_textarea', 'esc_html__', 'esc_html_e', 'esc_attr__', 'esc_attr_e', '__', '_e', '_n', '_x', 'wp_kses_post', 'wp_strip_all_tags', 'is_email', 'wp_json_encode', 'wp_parse_args', 'shortcode_atts', 'do_shortcode', 'get_post_meta', 'update_post_meta', 'delete_post_meta', 'get_posts', 'get_the_ID', 'get_permalink', 'get_the_title', 'wp_insert_post', 'wp_update_post', 'wp_delete_post', 'current_time', 'wp_upload_dir', 'wp_mkdir_p', 'get_bloginfo', 'is_admin', 'is_user_logged_in', 'wp_get_current_user', 'get_userdata', 'get_user_by', 'wp_hash', 'wp_generate_password', 'wp_rand', 'number_format_i18n', 'date_i18n', 'size_format', 'wp_doing_ajax', 'wp_doing_cron', 'wp_next_scheduled', 'wp_schedule_event', 'wp_unschedule_event', 'wp_clear_scheduled_hook', 'wp_schedule_single_event', 'get_transient', 'set_transient', 'delete_transient', 'wp_cache_get', 'wp_cache_set', 'wp_cache_delete', 'wp_remote_get', 'wp_remote_post', 'wp_remote_retrieve_body', 'wp_remote_retrieve_response_code', 'is_wp_error', 'wp_mail_from', 'wp_mail_from_name', 'wp_mail_content_type', 'plugin_dir_path', 'plugin_dir_url', 'plugin_basename', 'register_activation_hook', 'register_deactivation_hook', 'add_action', 'add_filter', 'remove_action', 'remove_filter', 'do_action', 'apply_filters', 'has_action', 'has_filter', 'add_shortcode', 'add_menu_page', 'add_submenu_page', 'remove_menu_page', 'add_options_page', 'add_meta_box', 'register_post_type', 'register_taxonomy', 'wp_enqueue_style', 'wp_enqueue_script', 'wp_localize_script', 'wp_register_style', 'wp_register_script', 'wp_add_inline_style', 'admin_enqueue_scripts', 'wp_die', 'wp_send_json', 'wp_send_json_success', 'wp_send_json_error', 'check_ajax_referer', 'wp_get_current_user', 'get_current_screen', 'add_settings_error', 'settings_fields', 'do_settings_sections', 'register_setting', 'add_settings_section', 'add_settings_field', 'get_role', 'add_role', 'remove_role', 'wp_dropdown_pages', 'wp_editor', 'selected', 'checked', 'disabled', 'wp_read_image_metadata', 'wp_get_image_editor', 'wp_handle_upload', 'media_handle_upload', 'wp_insert_attachment', 'wp_generate_attachment_metadata', 'wp_get_attachment_url', 'get_attached_file', 'wp_get_attachment_image', 'wp_get_attachment_image_url', 'wp_get_attachment_metadata', 'attachment_url_to_postid', 'wp_get_attachment_image_src', 'get_post', 'get_post_type', 'get_post_status', 'get_the_title', 'wp_reset_postdata', 'query_posts', 'have_posts', 'the_post', 'wp_count_posts', 'wp_trash_post', 'wp_parse_url', 'trailingslashit', 'untrailingslashit', 'wp_normalize_path', 'wp_is_mobile', 'is_ssl', 'wp_login_url', 'wp_logout_url', 'wp_lostpassword_url', 'get_avatar', 'wp_staticize_emoji', 'wp_kses', 'wpautop', 'wp_trim_words', 'wp_html_excerpt', 'human_time_diff', 'wp_timezone', 'wp_date', 'wp_timezone_string', 'wp_get_environment_type', 'wp_is_json_request', 'rest_ensure_response', 'register_rest_route', 'wp_localize_script', 'wp_set_script_translations', 'load_plugin_textdomain', 'determine_locale', 'get_locale', 'wp_get_available_translations', 'wp_dropdown_users', 'wp_nonce_ays', 'wp_nonce_ays', 'wp_verify_nonce', 'wp_handle_sideload', 'download_url', 'unzip_file', 'WP_Filesystem', 'get_filesystem_method', 'request_filesystem_credentials', 'wp_tempnam', 'wp_max_upload_size', 'wp_is_writable', 'wp_mkdir_p', 'wp_get_upload_dir', 'wp_get_image_mime', 'wp_check_filetype', 'wp_check_filetype_and_ext', 'wp_attachment_is_image', 'wp_getimagesize', 'wp_imagecreatetruecolor', 'imagecreatefrompng', 'imagecreatetruecolor', 'imagealphablending', 'imagesavealpha', 'imagecolorallocate', 'imagefill', 'imagecopyresampled', 'imagepng', 'imagedestroy', 'imagecreatefromjpeg', 'imagecreatefromwebp', 'imagejpeg', 'imagewebp', 'imagecolorat', 'imagesx', 'imagesy', 'imagefontwidth', 'imagefontheight', 'imagestring', 'imagettftext', 'imagecreatefromstring', 'imagefilter', 'imagecrop', 'imagecopy', 'imagerotate', 'image_type_to_mime_type', 'wp_getimagesize', 'wp_tempnam', 'get_temp_dir', 'wp_unique_filename', 'wp_mkdir_p', 'wp_delete_file' ) as $fn ) {
	$defined[ strtolower( $fn ) ] = true;
}

$keywords = array( 'if', 'elseif', 'else', 'for', 'foreach', 'while', 'switch', 'case', 'return', 'function', 'echo', 'print', 'isset', 'unset', 'empty', 'list', 'array', 'catch', 'try', 'throw', 'new', 'clone', 'include', 'include_once', 'require', 'require_once', 'exit', 'die', 'and', 'or', 'xor', 'instanceof', 'fn', 'match', 'static', 'class', 'interface', 'trait', 'extends', 'implements', 'namespace', 'use', 'global', 'declare', 'endwhile', 'endforeach', 'endif', 'endswitch', 'endforeach', 'yield', 'parent', 'self' );

$issues = array();
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $pluginDir, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	if ( 'php' !== strtolower( $f->getExtension() ) ) {
		continue;
	}
	$rel = str_replace( $pluginDir . '/', '', str_replace( '\\', '/', $f->getPathname() ) );
	$src = file_get_contents( $f->getPathname() );
	// strip comments and strings roughly
	$src2 = preg_replace( '#/\*.*?\*/#s', ' ', $src );
	$src2 = preg_replace( '#//[^\n]*#', ' ', $src2 );
	$src2 = preg_replace( '#\'(?:\\\\.|[^\'\\\\])*\'#s', "''", $src2 );
	$src2 = preg_replace( '#"(?:\\\\.|[^"\\\\])*"#s', '""', $src2 );

	if ( preg_match_all( '/(?<![\w$>:\\\\])([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $src2, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[1] as $hit ) {
			$name = $hit[0];
			$lower = strtolower( $name );
			if ( in_array( $lower, $keywords, true ) ) {
				continue;
			}
			if ( isset( $defined[ $lower ] ) ) {
				continue;
			}
			$line = substr_count( substr( $src2, 0, $hit[1] ), "\n" ) + 1;
			$issues[] = "$rel:$line $name()";
		}
	}
}

echo "potential undefined calls: " . count( $issues ) . "\n";
foreach ( $issues as $i ) {
	echo $i . "\n";
}
