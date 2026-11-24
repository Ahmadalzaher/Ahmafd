<?php
/**
 * Plugin Name: Arap Backup Tool — أداة نسخ احتياطي مؤقتة
 * Description: أداة إدارية صغيرة لإنشاء نسخة من قالب skybook ونسخة SQL من قاعدة البيانات داخل مجلد الرفع، مع روابط تنزيل. تُستخدم قبل تحديث القالب ثم تُعطَّل.
 * Version: 1.0.0
 * Author: Arap Library
 * Text Domain: arap-backup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARAP_BACKUP_DIR', WP_CONTENT_DIR . '/uploads/arap-backups' );

add_action( 'admin_menu', 'arap_backup_menu' );
function arap_backup_menu() {
	add_management_page( 'نسخة أمان', 'نسخة أمان', 'manage_options', 'arap-backup', 'arap_backup_page' );
}

add_action( 'admin_post_arap_backup_run', 'arap_backup_run' );
function arap_backup_run() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'غير مصرّح.' );
	}
	check_admin_referer( 'arap_backup_run' );

	@set_time_limit( 0 );
	@ini_set( 'memory_limit', '512M' );

	if ( ! is_dir( ARAP_BACKUP_DIR ) ) {
		wp_mkdir_p( ARAP_BACKUP_DIR );
	}

	$stamp   = gmdate( 'Ymd-His' );
	$results = array();

	$results[] = arap_backup_zip_themes( $stamp );
	$results[] = arap_backup_dump_sql( $stamp );

	set_transient( 'arap_backup_last', $results, 3600 );
	wp_safe_redirect( admin_url( 'tools.php?page=arap-backup&done=1' ) );
	exit;
}

function arap_backup_zip_themes( $stamp ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return array( 'item' => 'themes', 'ok' => false, 'msg' => 'ZipArchive غير متاح على الخادم.' );
	}

	$dirs = array();
	foreach ( array( get_template_directory(), get_stylesheet_directory() ) as $d ) {
		$dirs[ $d ] = basename( $d );
	}

	$name = 'themes-' . $stamp . '.zip';
	$path = ARAP_BACKUP_DIR . '/' . $name;

	$zip = new ZipArchive();
	if ( true !== $zip->open( $path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		return array( 'item' => 'themes', 'ok' => false, 'msg' => 'تعذّر إنشاء ملف ZIP.' );
	}

	$count = 0;
	foreach ( $dirs as $dir => $base ) {
		if ( ! is_dir( $dir ) ) {
			continue;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ( $it as $file ) {
			$rel = $base . '/' . ltrim( str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) ) ), '/' );
			if ( $file->isDir() ) {
				$zip->addEmptyDir( $rel );
			} else {
				$zip->addFile( $file->getPathname(), $rel );
				$count++;
			}
		}
	}
	$zip->close();

	return array(
		'item' => 'themes',
		'ok'   => true,
		'name' => $name,
		'url'  => content_url( 'uploads/arap-backups/' . $name ),
		'msg'  => $count . ' ملفًا، ' . size_format( filesize( $path ) ),
	);
}

add_action( 'admin_post_arap_backup_theme', 'arap_backup_theme_only' );
function arap_backup_theme_only() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'غير مصرّح.' );
	}
	check_admin_referer( 'arap_backup_theme' );
	@set_time_limit( 0 );
	if ( ! is_dir( ARAP_BACKUP_DIR ) ) {
		wp_mkdir_p( ARAP_BACKUP_DIR );
	}
	$r = arap_backup_zip_themes( gmdate( 'Ymd-His' ) );
	set_transient( 'arap_backup_last', array( $r ), 3600 );
	wp_safe_redirect( admin_url( 'tools.php?page=arap-backup&done=1' ) );
	exit;
}

function arap_backup_dump_sql( $stamp ) {
	global $wpdb;

	$name = 'db-' . $stamp . '.sql';
	$path = ARAP_BACKUP_DIR . '/' . $name;
	$fh   = @fopen( $path, 'wb' );

	if ( ! $fh ) {
		return array( 'item' => 'db', 'ok' => false, 'msg' => 'تعذّر إنشاء ملف SQL.' );
	}

	fwrite( $fh, "-- Arap Library DB backup\n-- " . gmdate( 'c' ) . "\n\n" );
	fwrite( $fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n" );

	$tables = $wpdb->get_col( 'SHOW TABLES' );
	$rows   = 0;

	foreach ( $tables as $table ) {
		$create = $wpdb->get_row( 'SHOW CREATE TABLE `' . $table . '`', ARRAY_N );
		if ( ! $create || empty( $create[1] ) ) {
			continue;
		}

		fwrite( $fh, "DROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n\n" );

		$offset = 0;
		$batch  = 400;

		while ( true ) {
			$data = $wpdb->get_results( "SELECT * FROM `$table` LIMIT $offset, $batch", ARRAY_A );
			if ( empty( $data ) ) {
				break;
			}

			$cols = '`' . implode( '`,`', array_keys( $data[0] ) ) . '`';

			foreach ( $data as $row ) {
				$vals = array();
				foreach ( $row as $value ) {
					if ( null === $value ) {
						$vals[] = 'NULL';
					} else {
						$vals[] = "'" . esc_sql( $value ) . "'";
					}
				}
				fwrite( $fh, "INSERT INTO `$table` ($cols) VALUES (" . implode( ',', $vals ) . ");\n" );
				$rows++;
			}

			$offset += $batch;
			unset( $data );
		}

		fwrite( $fh, "\n" );
	}

	fwrite( $fh, "SET FOREIGN_KEY_CHECKS=1;\n" );
	fclose( $fh );

	return array(
		'item' => 'db',
		'ok'   => true,
		'name' => $name,
		'url'  => content_url( 'uploads/arap-backups/' . $name ),
		'msg'  => count( $tables ) . ' جدولًا، ' . number_format_i18n( $rows ) . ' صفًا، ' . size_format( filesize( $path ) ),
	);
}

add_action( 'admin_post_arap_backup_clean', 'arap_backup_clean' );
function arap_backup_clean() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'غير مصرّح.' );
	}
	check_admin_referer( 'arap_backup_clean' );
	$files = is_dir( ARAP_BACKUP_DIR ) ? glob( ARAP_BACKUP_DIR . '/*' ) : array();
	$n     = 0;
	foreach ( $files as $file ) {
		if ( is_file( $file ) && @unlink( $file ) ) {
			$n++;
		}
	}
	set_transient( 'arap_backup_last', array( array( 'item' => 'clean', 'ok' => true, 'msg' => 'حُذف ' . $n . ' ملفًا من الخادم.' ) ), 3600 );
	wp_safe_redirect( admin_url( 'tools.php?page=arap-backup&done=1' ) );
	exit;
}

function arap_backup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>نسخة أمان قبل تحديث القالب</h1>

		<?php if ( isset( $_GET['done'] ) ) : ?>
			<?php $last = get_transient( 'arap_backup_last' ); ?>
			<div class="notice notice-info">
				<p><strong>انتهى التنفيذ.</strong></p>
				<?php if ( is_array( $last ) ) : ?>
					<ul>
						<?php foreach ( $last as $item ) : ?>
							<li>
								<?php echo esc_html( $item['item'] ); ?>:
								<?php echo esc_html( $item['msg'] ); ?>
								<?php if ( ! empty( $item['url'] ) ) : ?>
									— <a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener">تنزيل</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<p>تُنشئ هذه الأداة ملفين في <code>wp-content/uploads/arap-backups/</code>: نسخة ZIP من القالبين (الأب والابن)، ونسخة SQL من قاعدة البيانات.</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="arap_backup_run" />
			<?php wp_nonce_field( 'arap_backup_run' ); ?>
			<?php submit_button( 'إنشاء النسخة الآن', 'primary', 'submit', false ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px">
			<input type="hidden" name="action" value="arap_backup_clean" />
			<?php wp_nonce_field( 'arap_backup_clean' ); ?>
			<?php submit_button( 'حذف النسخ من الخادم', 'delete', 'submit', false ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px">
			<input type="hidden" name="action" value="arap_backup_theme" />
			<?php wp_nonce_field( 'arap_backup_theme' ); ?>
			<?php submit_button( 'نسخة سريعة من القالبين فقط', 'secondary', 'submit', false ); ?>
		</form>

		<h2>الملفات الموجودة</h2>
		<?php
		$files = is_dir( ARAP_BACKUP_DIR ) ? glob( ARAP_BACKUP_DIR . '/*' ) : array();
		if ( empty( $files ) ) {
			echo '<p>لا توجد ملفات بعد.</p>';
		} else {
			echo '<ul>';
			foreach ( $files as $file ) {
				$base = basename( $file );
				echo '<li><a href="' . esc_url( content_url( 'uploads/arap-backups/' . $base ) ) . '" target="_blank" rel="noopener">' . esc_html( $base ) . '</a> — ' . esc_html( size_format( filesize( $file ) ) ) . '</li>';
			}
			echo '</ul>';
		}
		?>
	</div>
	<?php
}
