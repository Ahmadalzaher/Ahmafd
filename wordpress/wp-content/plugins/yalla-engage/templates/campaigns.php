<?php
/**
 * Campaigns list template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows = isset( $rows ) ? $rows : array();
$edit = isset( $edit ) ? $edit : null;
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'النشرات والحملات', 'yalla-engage' ); ?></h1>
	<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success"><p><?php echo esc_html__( 'تم حفظ الحملة.', 'yalla-engage' ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['sent'] ) ) : ?><div class="notice notice-success"><p><?php echo esc_html__( 'تم وضع الحملة في قائمة الإرسال.', 'yalla-engage' ); ?></p></div><?php endif; ?>
	<?php if ( isset( $_GET['error'] ) ) : ?><div class="notice notice-error"><p><?php echo esc_html__( 'تعذر تنفيذ العملية. تأكد من البيانات وحالة الحملة.', 'yalla-engage' ); ?></p></div><?php endif; ?>

	<h2><?php echo $edit ? esc_html__( 'تعديل حملة', 'yalla-engage' ) : esc_html__( 'إنشاء حملة جديدة', 'yalla-engage' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="background:#fff;padding:1rem;max-width:850px;">
		<input type="hidden" name="action" value="yena_save_campaign" />
		<input type="hidden" name="id" value="<?php echo $edit ? (int) $edit->id : 0; ?>" />
		<?php wp_nonce_field( 'yena_save_campaign' ); ?>
		<p><label><strong><?php echo esc_html__( 'الموضوع', 'yalla-engage' ); ?></strong><br /><input type="text" name="subject" class="large-text" required value="<?php echo $edit ? esc_attr( $edit->subject ) : ''; ?>" /></label></p>
		<p><label><strong><?php echo esc_html__( 'اللغة المستهدفة', 'yalla-engage' ); ?></strong><br />
			<select name="lang_target"><option value="all" <?php selected( $edit ? $edit->lang_target : 'all', 'all' ); ?>>كل اللغات</option><option value="de" <?php selected( $edit ? $edit->lang_target : '', 'de' ); ?>>Deutsch</option><option value="en" <?php selected( $edit ? $edit->lang_target : '', 'en' ); ?>>English</option><option value="ar" <?php selected( $edit ? $edit->lang_target : '', 'ar' ); ?>>العربية</option></select>
		</label></p>
		<p><label><strong><?php echo esc_html__( 'محتوى الرسالة', 'yalla-engage' ); ?></strong><br /><textarea name="body" rows="8" class="large-text" required><?php echo $edit ? esc_textarea( $edit->body ) : ''; ?></textarea></label></p>
		<?php submit_button( $edit ? __( 'حفظ التعديل', 'yalla-engage' ) : __( 'حفظ كمسودة', 'yalla-engage' ), 'primary', 'submit', false ); ?>
		<?php if ( $edit ) : ?><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=yena-campaigns' ) ); ?>">إلغاء</a><?php endif; ?>
	</form>

	<h2><?php echo esc_html__( 'الحملات المحفوظة', 'yalla-engage' ); ?></h2>
	<table class="wp-list-table widefixed fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'الموضوع', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'اللغة المستهدفة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الحالة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'إجمالي', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'أُرسِل', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'فشل', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'تاريخ الإنشاء', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'إجراءات', 'yalla-engage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->subject ); ?></td>
					<td><?php echo strtoupper( esc_html( $row->lang_target ) ); ?></td>
					<td><?php echo esc_html( $row->status ); ?></td>
					<td><?php echo (int) $row->total; ?></td>
					<td><?php echo (int) $row->sent; ?></td>
					<td><?php echo (int) $row->failed; ?></td>
					<td><?php echo esc_html( $row->created_at ); ?></td>
					<td>
						<?php if ( 'draft' === $row->status ) : ?><a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=yena-campaigns&edit=' . (int) $row->id ) ); ?>">تعديل</a>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
							<input type="hidden" name="action" value="yena_send_campaign" /><input type="hidden" name="id" value="<?php echo (int) $row->id; ?>" /><?php wp_nonce_field( 'yena_send_campaign' ); ?><button class="button button-small" type="submit">إرسال</button>
						</form><?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="8"><?php echo esc_html__( 'لا توجد حملات بعد.', 'yalla-engage' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
