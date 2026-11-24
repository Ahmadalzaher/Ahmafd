<?php
/**
 * WordPress configuration — YallaDabka / Arab Kulturfestival rebuild (SQLite).
 *
 * @package YallaDabka
 */

define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY', '17c9dd49978e34e14035e16fb190f7d8ff3a7d43b6f7eeec' );
define( 'SECURE_AUTH_KEY', '3cbdd5de07ad43a2d7429c4948196bb6f5d06b6b347a26b7' );
define( 'LOGGED_IN_KEY', '3aedeade16d76a8d899d331c81adce350d6940df162a0483' );
define( 'NONCE_KEY', 'c82a904fbe9edf61ca56bda90f84effd8ce4b4b5f3e4d03' );
define( 'AUTH_SALT', 'bd2b7392d39a697d62d28432b46969b3a2cd464d594c8894' );
define( 'SECURE_AUTH_SALT', 'd75ee2cb47a1ae5d722fd4133ace28ee32d74ff7a6dfac95' );
define( 'LOGGED_IN_SALT', '62068c272ad619c87af680bbbab003b63a1005b47130cb53' );
define( 'NONCE_SALT', 'b415e0c720e5865e9aa1b15725ed088a3dbd3f3a9563884a' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
define( 'WP_HOME', 'http://localhost:8080' );
define( 'WP_SITEURL', 'http://localhost:8080' );
define( 'FS_METHOD', 'direct' );
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'DISALLOW_FILE_EDIT', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
