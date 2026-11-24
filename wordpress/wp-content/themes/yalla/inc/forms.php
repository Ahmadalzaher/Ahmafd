<?php
/**
 * Contact form handling (no plugin required).
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle the contact form submission.
 */
function yd_handle_contact_form() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = remove_query_arg( 'contact', $redirect );

	if ( ! isset( $_POST['yd_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['yd_contact_nonce'] ), 'yd_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) . '#contact-form' );
		exit;
	}

	// Honeypot.
	if ( ! empty( $_POST['yd_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'ok', $redirect ) . '#contact-form' );
		exit;
	}

	$name    = isset( $_POST['yd_name'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_name'] ) ) : '';
	$email   = isset( $_POST['yd_email'] ) ? sanitize_email( wp_unslash( $_POST['yd_email'] ) ) : '';
	$subject = isset( $_POST['yd_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['yd_subject'] ) ) : '';
	$message = isset( $_POST['yd_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['yd_message'] ) ) : '';
	$consent = isset( $_POST['yd_consent'] );

	if ( ! $name || ! is_email( $email ) || ! $message || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) . '#contact-form' );
		exit;
	}

	$to      = yd_contact( 'email' );
	$subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), $subject ? $subject : __( 'Neue Anfrage', 'yalla' ) );
	$body    = sprintf(
		"Name: %s\nE-Mail: %s\n\n%s\n\n---\n%s",
		$name,
		$email,
		$message,
		home_url( '/' )
	);

	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contact', $sent ? 'ok' : 'error', $redirect ) . '#contact-form' );
	exit;
}
add_action( 'admin_post_yd_contact', 'yd_handle_contact_form' );
add_action( 'admin_post_nopriv_yd_contact', 'yd_handle_contact_form' );

/**
 * Render the contact form.
 */
function yd_contact_form() {
	$status = isset( $_GET['contact'] ) ? sanitize_key( $_GET['contact'] ) : '';
	?>
	<div id="contact-form">
		<?php if ( 'ok' === $status ) : ?>
			<div class="form-alert form-alert--ok" role="status"><?php echo esc_html( yd_t( 'form_ok' ) ); ?></div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="form-alert" role="alert"><?php echo esc_html( yd_t( 'form_error' ) ); ?></div>
		<?php endif; ?>

		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="yd_contact" />
			<?php wp_nonce_field( 'yd_contact', 'yd_contact_nonce' ); ?>

			<div class="grid-2">
				<div class="field">
					<label for="yd_name"><?php echo esc_html( yd_t( 'form_name' ) ); ?></label>
					<input type="text" id="yd_name" name="yd_name" required autocomplete="name" />
				</div>
				<div class="field">
					<label for="yd_email"><?php echo esc_html( yd_t( 'form_email' ) ); ?></label>
					<input type="email" id="yd_email" name="yd_email" required autocomplete="email" />
				</div>
			</div>

			<div class="field">
				<label for="yd_subject"><?php echo esc_html( yd_t( 'form_subject' ) ); ?></label>
				<input type="text" id="yd_subject" name="yd_subject" />
			</div>

			<div class="field">
				<label for="yd_message"><?php echo esc_html( yd_t( 'form_message' ) ); ?></label>
				<textarea id="yd_message" name="yd_message" rows="6" required></textarea>
			</div>

			<div class="field field--check">
				<input type="checkbox" id="yd_consent" name="yd_consent" value="1" required />
				<label for="yd_consent"><?php echo esc_html( yd_t( 'form_consent' ) ); ?></label>
			</div>

			<p class="screen-reader-text">
				<label for="yd_website">Website</label>
				<input type="text" id="yd_website" name="yd_website" tabindex="-1" autocomplete="off" />
			</p>

			<button class="btn" type="submit"><?php echo esc_html( yd_t( 'form_send' ) ); ?></button>
		</form>
	</div>
	<?php
}
