<?php
/**
 * Test mail template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$result = isset( $result ) ? $result : '';
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'فحص البريد — Yalla Engage', 'yalla-engage' ); ?></h1>

	<div class="notice notice-info">
		<p><?php echo esc_html__( 'أرسل رسالة اختبار للتأكد من أن wp_mail() يعمل بشكل صحيح قبل إرسال الحملات.', 'yalla-engage' ); ?></p>
	</div>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=yena_test_mail' ) ); ?>">
		<?php wp_nonce_field( 'yena_test' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'البريد المستلم', 'yalla-engage' ); ?></th>
				<td><input type="email" name="yena_test_email" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="regular-text" /></td>
			</tr>
		</table>
		<?php submit_button( __( 'إرسال رسالة اختبار', 'yalla-engage' ), 'primary', 'yena_test_send' ); ?>
	</form>

	<?php if ( $result ) : ?>
		<div class="notice notice-<?php echo 'success' === $result ? 'success' : 'error'; ?> is-dismissible">
			<p><?php echo 'success' === $result ? esc_html__( 'تم إرسال رسالة الاختبار بنجاح. تحقق من صندوق بريدك.', 'yalla-engage' ) : esc_html__( 'فشل الإرسال. تأكد من إعداد SMTP عبر إضافة WP Mail SMTP.', 'yalla-engage' ); ?></p>
		</div>
	<?php endif; ?>

	<h2><?php echo esc_html__( 'رسائل الطابور والفشل', 'yalla-engage' ); ?></h2>
	<p><?php echo esc_html__( 'يعرض هذا القسم الرسائل العالقة أو الفاشلة في طابور الإرسال (إذا وُجدت).', 'yalla-engage' ); ?></p>
</div>
