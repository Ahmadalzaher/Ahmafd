<?php
/**
 * Settings template for Yalla Engage.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = isset( $settings ) ? $settings : array();
$notify_email = isset( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
$batch_size   = isset( $settings['batch_size'] ) ? $settings['batch_size'] : 20;
$reminder     = isset( $settings['reminder'] ) ? $settings['reminder'] : '1';
$texts        = array();
foreach ( array( 'de', 'en', 'ar' ) as $lang ) {
	$texts[ $lang ] = YENA_Forms::get_instance()->get_texts_for_lang( $lang );
}

// Saved notice.
$saved = isset( $_GET['yena-saved'] ) ? '1' : '';
if ( $saved ) {
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'تم حفظ الإعدادات بنجاح.', 'yalla-engage' ) . '</p></div>';
}
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'الإعدادات — النشرة والتسجيل', 'yalla-engage' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=yena_save_settings' ) ); ?>">
		<?php wp_nonce_field( 'yena_settings' ); ?>

		<h2><?php echo esc_html__( 'البريد والإرسال', 'yalla-engage' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php echo esc_html__( 'بريد الإشعار للمسؤول', 'yalla-engage' ); ?></th>
				<td><input type="email" name="yena_notify_email" value="<?php echo esc_attr( $notify_email ); ?>" class="regular-text" /> <?php echo esc_html__( 'يصل إليك إشعار عند كل تسجيل جديد.', 'yalla-engage' ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'حجم الدفعة للإرسال', 'yalla-engage' ); ?></th>
				<td><input type="number" name="yena_batch_size" value="<?php echo esc_attr( $batch_size ); ?>" min="1" max="100" class="small-text" /> رسائل كل 5 دقائق. اختر قيمة منخفضة لاستضافة مشتركة (مثل 20).</td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html__( 'تذكير قبل الفعالية بيوم', 'yalla-engage' ); ?></th>
				<td><input type="checkbox" name="yena_reminder" value="1" <?php checked( $reminder, '1' ); ?> /> <?php echo esc_html__( 'مفعل', 'yalla-engage' ); ?></td>
			</tr>
		</table>

		<h2><?php echo esc_html__( 'نصوص الرسائل والنماذج', 'yalla-engage' ); ?></h2>
		<p><?php echo esc_html__( 'يمكنك تعديل كل نص بكل لغة. القيم الافتراضية تُستخدم إذا ترك الحقل فارغاً.', 'yalla-engage' ); ?></p>

		<?php foreach ( array( 'de' => 'Deutsch', 'en' => 'English', 'ar' => 'العربية' ) as $lang => $lang_name ) : ?>
			<h3><?php echo esc_html( $lang_name ); ?></h3>
			<table class="form-table">
				<?php
				$fields = array(
					'news_eyebrow'          => 'عنوان قسم النشرة',
					'news_title'            => 'عنوان النشرة',
					'news_intro'            => 'مقدمة النشرة',
					'news_name'             => 'حقل الاسم',
					'news_name_ph'          => 'مثال الاسم',
					'news_email'            => 'حقل البريد',
					'news_email_ph'         => 'مثال البريد',
					'news_consent'          => 'نص الموافقة للنشرة',
					'news_website'          => 'حقل مكافحة الروبوتات',
					'news_button'           => 'زر الاشتراك',
					'news_note'             => 'ملاحظة النشرة',
					'news_pending'          => 'رسالة انتظار التأكيد',
					'news_confirmed'        => 'رسالة التأكيد',
					'news_already'          => 'رسالة البريد الموجود',
					'news_error'            => 'رسالة خطأ النشرة',
					'news_invalid'          => 'رسالة رابط النشرة غير الصالح',
					'news_unsubscribed'     => 'رسالة إلغاء الاشتراك',
					'news_rate'             => 'رسالة كثرة المحاولات',
					'news_mail_fail'        => 'رسالة فشل البريد',
					'news_confirm_subject' => 'موضوع تأكيد الاشتراك',
					'news_confirm_body'    => 'نص تأكيد الاشتراك',
					'news_confirm_ignore'  => 'ملاحظة تجاهل رسالة الاشتراك',
					'news_welcome_subject' => 'موضوع رسالة الترحيب',
					'news_welcome_body'    => 'نص رسالة الترحيب',
					'reg_eyebrow'          => 'عنوان قسم التسجيل',
					'reg_title'            => 'عنوان التسجيل',
					'reg_intro'            => 'مقدمة التسجيل',
					'reg_name'             => 'حقل اسم المشارك',
					'reg_email'            => 'حقل بريد المشارك',
					'reg_phone'            => 'حقل الهاتف',
					'reg_persons'          => 'حقل عدد الأشخاص',
					'reg_notes'            => 'حقل الملاحظات',
					'reg_consent'          => 'نص موافقة التسجيل',
					'reg_newsletter'       => 'نص اشتراك النشرة',
					'reg_website'          => 'حقل مكافحة الروبوتات للتسجيل',
					'reg_button'           => 'زر التسجيل',
					'reg_ok'               => 'رسالة التسجيل الناجح',
					'reg_ok_nomail'        => 'رسالة التسجيل دون بريد',
					'reg_full'             => 'رسالة الفعالية الممتلئة',
					'reg_already'          => 'رسالة التسجيل المكرر',
					'reg_cancelled'        => 'رسالة الإلغاء',
					'reg_error'            => 'رسالة خطأ التسجيل',
					'reg_invalid'          => 'رسالة الرابط غير الصالح',
					'reg_closed'           => 'رسالة التسجيل المغلق',
					'reg_rate'             => 'رسالة كثرة محاولات التسجيل',
					'reg_payment_unavailable' => 'رسالة الدفع غير المتاح',
					'reg_pending_payment'  => 'رسالة انتظار تأكيد الدفع',
					'reg_left'             => 'رسالة المقاعد المتبقية',
					'reg_unlimited'        => 'رسالة التسجيل المفتوح',
					'reg_free'             => 'رسالة الفعالية المجانية',
					'reg_paid'             => 'رسالة الفعالية المدفوعة',
					'reg_participants_title' => 'عنوان شعارات المشاركين',
					'reg_confirm_subject'  => 'موضوع تأكيد التسجيل',
					'reg_confirm_body'     => 'نص تأكيد التسجيل',
					'reg_admin_subject'    => 'موضوع إشعار المسؤول',
					'reg_cancel_note'      => 'ملاحظة إلغاء التسجيل',
					'ticket_title'         => 'عنوان التذكرة',
					'ticket_hello'         => 'تحية التذكرة',
					'ticket_event'         => 'عنوان حقل الفعالية في التذكرة',
					'ticket_date'          => 'عنوان التاريخ',
					'ticket_time'          => 'عنوان الوقت',
					'ticket_venue'         => 'عنوان المكان',
					'ticket_persons'       => 'عنوان عدد الأشخاص',
					'ticket_number'        => 'عنوان رقم التذكرة',
					'ticket_verify'        => 'رابط التحقق',
					'ticket_info'          => 'معلومات الدخول',
					'ticket_pdf'           => 'ملاحظة PDF',
					'ticket_cancel'        => 'رابط إلغاء التذكرة',
					'news_name_fallback'   => 'الاسم الاحتياطي',
					'campaign_subject'     => 'موضوع افتراضي للحملة',
					'campaign_body'        => 'نص افتراضي للحملة',
					'poster_title'         => 'عنوان البوستر',
					'poster_upload'        => 'زر رفع البوستر',
				);
				foreach ( $fields as $key => $label ) :
					$current = isset( $texts[ $lang ][ $key ] ) ? $texts[ $lang ][ $key ] : '';
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $label ); ?></th>
					<td><textarea name="yena_text_<?php echo esc_attr( $lang ); ?>_<?php echo esc_attr( $key ); ?>" rows="3" class="large-text"><?php echo esc_html( $current ); ?></textarea></td>
				</tr>
			<?php endforeach; ?>
			</table>
		<?php endforeach; ?>

		<h2><?php echo esc_html__( 'مهمة cron المجدولة (الاستضافة)', 'yalla-engage' ); ?></h2>
		<p><?php echo esc_html__( 'إذا كانت الزيارات إلى موقعك قليلة، قد لا يعمل wp-cron بشكل موثوق. أضف مهمة cron حقيقية من لوحة الاستضافة (مثلاً STRATO: Webspace verwalten ← Cronjobs):', 'yalla-engage' ); ?></p>
		<code style="display:block;background:#f5eddd;padding:1rem;border:1px solid #d8cdbd;border-radius:4px;margin-bottom:1rem;">
wget -q -O - "<?php echo esc_url( home_url( '/wp-cron.php?doing_wp_cron=1' ) ); ?>" &gt;/dev/null 2&gt;&amp;1
		</code>
		<p><?php echo esc_html__( 'ضبط التكرار: كل 5 دقائق.', 'yalla-engage' ); ?></p>

		<?php submit_button( __( 'حفظ الإعدادات', 'yalla-engage' ), 'primary', 'yena_save_settings' ); ?>
	</form>
</div>
