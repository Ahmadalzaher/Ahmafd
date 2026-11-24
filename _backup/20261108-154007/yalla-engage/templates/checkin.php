<?php
/**
 * Mobile-first ticket check-in screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

nocache_headers();

// A QR link opened by a visitor must never reveal participant data.
if ( ! current_user_can( 'yena_manage' ) ) {
	?><!doctype html>
	<html <?php language_attributes(); ?>>
	<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
	<body style="font-family:Arial,sans-serif;padding:2rem;text-align:center;background:#f5eddd;color:#2b1c14;">
		<h1><?php echo esc_html__( 'هذه التذكرة تُفحص عند الباب', 'yalla-engage' ); ?></h1>
		<p><?php echo esc_html__( 'يرجى إبراز رمز QR لموظف الدخول.', 'yalla-engage' ); ?></p>
	</body>
	</html><?php
	return;
}

$checkin_value    = isset( $checkin_value ) ? $checkin_value : '';
$checkin_event_id = isset( $checkin_event_id ) ? (int) $checkin_event_id : 0;
$checkin_result   = isset( $checkin_result ) ? $checkin_result : '';
$checkin_reg      = isset( $checkin_reg ) ? $checkin_reg : null;
$checkin_events   = isset( $checkin_events ) ? $checkin_events : array();

$labels = array(
	'valid'          => array( 'valid', 'صحيحة — يمكن تأكيد الدخول', '#e7f6e9' ),
	'wrong_event'    => array( 'wrong', 'هذه التذكرة تخص فعالية أخرى', '#fff4cf' ),
	'not_found'      => array( 'invalid', 'التذكرة غير موجودة', '#fde7e7' ),
	'cancelled'      => array( 'invalid', 'التذكرة ملغاة', '#fde7e7' ),
	'already_used'   => array( 'invalid', 'تم استخدام التذكرة مسبقاً', '#fde7e7' ),
	'pending_payment'=> array( 'invalid', 'لم يتم تأكيد الدفع بعد', '#fff4cf' ),
);
?><div class="wrap" style="max-width:760px;">
	<h1><?php echo esc_html__( 'فحص تذاكر الدخول', 'yalla-engage' ); ?></h1>
	<p><?php echo esc_html__( 'يمكنك مسح QR بالكاميرا أو إدخال رقم التذكرة يدوياً.', 'yalla-engage' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="background:#fff;padding:1rem;border:1px solid #ddd;border-radius:8px;">
		<input type="hidden" name="action" value="yena_checkin_lookup">
		<?php wp_nonce_field( 'yena_checkin_lookup' ); ?>
		<label for="yena_ticket"><strong><?php echo esc_html__( 'رقم التذكرة أو رمز QR', 'yalla-engage' ); ?></strong></label>
		<input id="yena_ticket" name="yena_ticket" type="text" value="<?php echo esc_attr( $checkin_value ); ?>" required style="display:block;width:100%;font-size:1.25rem;padding:.8rem;margin:.5rem 0;box-sizing:border-box;">
		<label for="yena_event"><strong><?php echo esc_html__( 'الفعالية المقصودة', 'yalla-engage' ); ?></strong></label>
		<select id="yena_event" name="yena_event" style="display:block;width:100%;padding:.7rem;margin:.5rem 0 1rem;">
			<option value="0"><?php echo esc_html__( 'كل الفعاليات', 'yalla-engage' ); ?></option>
			<?php foreach ( $checkin_events as $event ) : ?>
				<option value="<?php echo (int) $event->ID; ?>" <?php selected( $checkin_event_id, $event->ID ); ?>><?php echo esc_html( $event->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<button type="button" id="yena-start-scanner" class="button" style="min-height:46px;margin-right:.5rem;"><?php echo esc_html__( 'مسح QR بالكاميرا', 'yalla-engage' ); ?></button>
		<button type="submit" class="button button-primary" style="min-height:46px;"><?php echo esc_html__( 'فحص التذكرة', 'yalla-engage' ); ?></button>
	</form>

	<div id="yena-scanner-box" hidden style="margin-top:1rem;background:#111;padding:1rem;border-radius:8px;text-align:center;">
		<video id="yena-scanner-video" autoplay playsinline style="width:100%;max-height:360px;background:#000;"></video>
		<button type="button" id="yena-stop-scanner" class="button" style="margin-top:.75rem;"><?php echo esc_html__( 'إيقاف الكاميرا', 'yalla-engage' ); ?></button>
		<p id="yena-scanner-error" style="color:#ffb3b3;"></p>
	</div>

	<?php if ( isset( $_GET['yena_error'] ) && 'rate' === sanitize_key( wp_unslash( $_GET['yena_error'] ) ) ) : ?>
		<div style="margin-top:1rem;padding:1rem;background:#fff4cf;border:1px solid #e0c76b;"><?php echo esc_html__( 'محاولات كثيرة. انتظر دقيقة ثم أعد المحاولة.', 'yalla-engage' ); ?></div>
	<?php endif; ?>

	<?php if ( $checkin_result && isset( $labels[ $checkin_result ] ) ) : $notice = $labels[ $checkin_result ]; ?>
		<div style="margin-top:1rem;padding:1rem;background:<?php echo esc_attr( $notice[2] ); ?>;border-radius:8px;font-size:1.1rem;">
			<strong><?php echo esc_html( $notice[1] ); ?></strong>
			<?php if ( $checkin_reg ) : ?>
				<p><?php echo esc_html( get_the_title( $checkin_reg->event_id ) ); ?> — <?php echo esc_html( $checkin_reg->ticket_number ); ?></p>
				<?php if ( 'already_used' === $checkin_result ) : ?>
					<p><?php echo esc_html__( 'وقت الاستخدام:', 'yalla-engage' ); ?> <?php echo esc_html( $checkin_reg->checked_in_at ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( 'valid' === $checkin_result && $checkin_reg ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1rem;background:#fff;padding:1rem;border:1px solid #6d9c76;border-radius:8px;">
			<input type="hidden" name="action" value="yena_checkin_verify">
			<input type="hidden" name="yena_reg_id" value="<?php echo (int) $checkin_reg->id; ?>">
			<?php wp_nonce_field( 'yena_checkin' ); ?>
			<p><strong><?php echo esc_html( $checkin_reg->name ); ?></strong> — <?php echo (int) $checkin_reg->persons; ?> <?php echo esc_html__( 'أشخاص مسجلون', 'yalla-engage' ); ?></p>
			<label for="yena_checkin_persons"><?php echo esc_html__( 'عدد الأشخاص الذين حضروا فعلياً', 'yalla-engage' ); ?></label>
			<input id="yena_checkin_persons" name="yena_checkin_persons" type="number" min="1" max="<?php echo (int) $checkin_reg->persons; ?>" value="<?php echo (int) $checkin_reg->persons; ?>" style="display:block;font-size:1.2rem;padding:.6rem;margin:.5rem 0 1rem;">
			<button type="submit" class="button button-primary" style="width:100%;min-height:52px;font-size:1.1rem;"><?php echo esc_html__( 'تأكيد الدخول', 'yalla-engage' ); ?></button>
		</form>
	<?php endif; ?>
</div>
<script>
(function(){
	var start=document.getElementById('yena-start-scanner'), stop=document.getElementById('yena-stop-scanner'), box=document.getElementById('yena-scanner-box'), video=document.getElementById('yena-scanner-video'), input=document.getElementById('yena_ticket'), form=input ? input.form : null, error=document.getElementById('yena-scanner-error');
	function stopScan(){ if(window.YenaScanner){window.YenaScanner.stop();} if(box){box.hidden=true;} }
	if(start){start.addEventListener('click',function(){ if(!window.YenaScanner){if(error){error.textContent='أداة المسح غير متاحة.';}return;} box.hidden=false; window.YenaScanner.start(video,function(value){try{var parsed=new URL(value,window.location.href);value=parsed.searchParams.get('code')||value;}catch(e){}input.value=value;stopScan();if(form){form.submit();}}).catch(function(){if(error){error.textContent='يرجى السماح باستخدام الكاميرا وفتح الصفحة عبر HTTPS.';}});});}
	if(stop){stop.addEventListener('click',stopScan);}
})();
</script>
