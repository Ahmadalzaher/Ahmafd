<?php
/**
 * Registrations list template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows = isset( $rows ) ? $rows : array();
$total  = isset( $total ) ? $total : 0;
$checked = isset( $checked ) ? $checked : 0;
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'تسجيلات الفعاليات', 'yalla-engage' ); ?></h1>

	<p><?php echo esc_html__( 'إجمالي التسجيلات:', 'yalla-engage' ) ?> <strong><?php echo (int) $total; ?></strong> — <?php echo esc_html__( 'حضر:', 'yalla-engage' ) ?> <strong style="color:#6d9c76;"><?php echo (int) $checked; ?></strong></p>

	<table class="wp-list-table widefixed fixed striped">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'الاسم', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'البريد الإلكتروني', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الفعالية', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الأفراد', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الحالة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'الدفع', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'حضر / غائب', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'رقم التذكرة', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'تاريخ الإنشاء', 'yalla-engage' ); ?></th>
				<th><?php echo esc_html__( 'إجراءات', 'yalla-engage' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row->name ); ?></td>
					<td><?php echo esc_html( $row->email ); ?></td>
					<td><?php echo esc_html( get_the_title( $row->event_id ) ); ?></td>
					<td><?php echo (int) $row->persons; ?></td>
					<td><?php echo esc_html( $row->status ); ?></td>
					<td>
						<?php if ( 'pending_payment' === $row->status || 'pending' === $row->payment_status ) : ?>
							<span style="color:#a06b00;font-weight:700;">بانتظار الدفع</span>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:4px;">
								<input type="hidden" name="action" value="yena_mark_paid" /><input type="hidden" name="id" value="<?php echo (int) $row->id; ?>" /><?php wp_nonce_field( 'yena_mark_paid' ); ?><button class="button button-small" type="submit">تأكيد الدفع وإرسال التذكرة</button>
							</form>
						<?php else : ?>
							<?php echo esc_html( 'paid' === $row->payment_status ? 'مدفوع' : '—' ); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ( 'checked_in' === $row->status ) : ?>
							<span style="color:#6d9c76;font-weight:700;"><?php echo esc_html__( 'حضر', 'yalla-engage' ); ?></span>
						<?php else : ?>
							<span style="color:#999;"><?php echo esc_html__( 'لم يحضر', 'yalla-engage' ); ?></span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $row->ticket_number ? $row->ticket_number : '—' ); ?></td>
					<td><?php echo esc_html( $row->created_at ); ?></td>
					<td>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'هل أنت متأكد أنك تريد إلغاء هذا التسجيل؟', 'yalla-engage' ) ); ?>');">
							<input type="hidden" name="action" value="yena_cancel_registration" />
							<input type="hidden" name="id" value="<?php echo (int) $row->id; ?>" />
							<?php wp_nonce_field( 'yena_cancel' ); ?>
							<button class="button button-small" type="submit"><?php echo esc_html__( 'إلغاء', 'yalla-engage' ); ?></button>
						</form>
						<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=yena_export_registrations' ), 'yena_export' ) ); ?>"><?php echo esc_html__( 'تصدير CSV', 'yalla-engage' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="10"><?php echo esc_html__( 'لا توجد تسجيلات بعد.', 'yalla-engage' ); ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
