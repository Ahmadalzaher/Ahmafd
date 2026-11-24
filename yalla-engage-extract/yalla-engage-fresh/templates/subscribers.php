<?php
/**
 * Subscribers list template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$search = isset( $search ) ? $search : '';
$status = isset( $status ) ? $status : '';
$lang   = isset( $lang ) ? $lang : '';
$total  = isset( $total ) ? $total : 0;
$rows   = isset( $rows ) ? $rows : array();
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'المشتركون', 'yalla-engage' ); ?></h1>

	<form method="get" style="margin-bottom:1rem;">
		<input type="hidden" name="page" value="yena-subscribers" />
		<input type="search" name="s" placeholder="<?php echo esc_attr__( 'ابحث بالبريد أو الاسم...', 'yalla-engage' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
		<select name="status">
			<option value=""><?php echo esc_html__( 'جميع الحالات', 'yalla-engage' ); ?></option>
			<option value="confirmed" <?php selected( $status, 'confirmed' ); ?>><?php echo esc_html__( 'مؤكد', 'yalla-engage' ); ?></option>
			<option value="pending" <?php selected( $status, 'pending' ); ?>><?php echo esc_html__( 'في انتظار التأكيد', 'yalla-engage' ); ?></option>
			<option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php echo esc_html__( 'ملغي الاشتراك', 'yalla-engage' ); ?></option>
		</select>
		<select name="lang">
			<option value=""><?php echo esc_html__( "جميع اللغات", 'yalla-engage' ); ?></option>
			<option value="de" <?php selected( $lang, 'de' ); ?>>Deutsch</option>
			<option value="en" <?php selected( $lang, 'en' ); ?>>English</option>
			<option value="ar" <?php selected( $lang, 'ar' ); ?>>العربية</option>
		</select>
		<button class="button" type="submit"><?php echo esc_html__( 'بحث', 'yalla-engage' ); ?></button>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=yena_export_subscribers' ), 'yena_export' ) ); ?>"><?php echo esc_html__( 'تصدير CSV', 'yalla-engage' ); ?></a>
	</form>

	<p><?php echo esc_html__( 'إجمالي المشتركين:', 'yalla-engage' ) ?> <strong><?php echo (int) $total; ?></strong></p>

	<table class="wp-list-table widefixed fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'البريد الإلكتروني', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الاسم', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'اللغة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الحالة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'تاريخ الإنشاء', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'إجراءات', 'yalla-engage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->email ); ?></td>
					<td><?php echo esc_html( $row->name ); ?></td>
					<td><?php echo strtoupper( esc_html( $row->lang ) ); ?></td>
					<td><?php echo esc_html( $row->status ); ?></td>
					<td><?php echo esc_html( $row->created_at ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js__( 'هل أنت متأكد أنك تريد حذف هذا المشترك نهائياً؟', 'yalla-engage' ); ?>');">
							<input type="hidden" name="action" value="yena_delete_subscriber" />
							<input type="hidden" name="id" value="<?php echo (int) $row->id; ?>" />
							<?php wp_nonce_field( 'yena_delete' ); ?>
							<button class="button button-small" type="submit"><?php echo esc_html__( 'حذف', 'yalla-engage' ); ?></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="6"><?php echo esc_html__( 'لا يوجد مشتركون.', 'yalla-engage' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
