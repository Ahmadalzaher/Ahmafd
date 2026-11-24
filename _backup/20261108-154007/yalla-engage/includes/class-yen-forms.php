<?php
/**
 * Front-end forms for Yalla Engage: newsletter subscription and event registration.
 *
 * @package Yalla_Engage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end forms handler.
 */
class YENA_Forms {

	protected static $instance = null;

	const RATE_LIMIT = 5;
	const RATE_WINDOW = 10 * MINUTE_IN_SECONDS;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function init() {
		$instance = self::get_instance();

		add_action( 'admin_post_yena_newsletter', array( $instance, 'handle_newsletter' ) );
		add_action( 'admin_post_nopriv_yena_newsletter', array( $instance, 'handle_newsletter' ) );

		add_action( 'admin_post_yena_register', array( $instance, 'handle_registration' ) );
		add_action( 'admin_post_nopriv_yena_register', array( $instance, 'handle_registration' ) );

		add_action( 'template_redirect', array( $instance, 'newsletter_action' ) );
		add_action( 'template_redirect', array( $instance, 'registration_cancel' ) );
		add_action( 'yena_cleanup_pending_payments', array( $instance, 'cleanup_pending_payments' ) );

		if ( function_exists( 'WC' ) || class_exists( 'WooCommerce' ) ) {
			add_action( 'woocommerce_order_status_processing', array( $instance, 'yena_woocommerce_order_paid' ) );
			add_action( 'woocommerce_order_status_completed', array( $instance, 'yena_woocommerce_order_paid' ) );
			add_action( 'woocommerce_payment_complete', array( $instance, 'yena_woocommerce_order_paid' ) );
			add_action( 'woocommerce_order_status_cancelled', array( $instance, 'yena_woocommerce_order_cancelled' ) );
			add_action( 'woocommerce_order_status_failed', array( $instance, 'yena_woocommerce_order_failed' ) );
			add_action( 'woocommerce_order_status_refunded', array( $instance, 'yena_woocommerce_order_refunded' ) );
		}
	}

	/* ------------------------------------------------------------------ */
	/* Newsletter form                                                    */
	/* ------------------------------------------------------------------ */

	public function render_newsletter_form() {
		$settings = get_option( 'yena_settings', array() );
		$texts    = $this->get_texts();

		$status = isset( $_GET['yena_news'] ) ? sanitize_key( wp_unslash( $_GET['yena_news'] ) ) : '';
		$messages = array(
			'pending'      => array( 'news_pending', 'ok' ),
			'confirmed'    => array( 'news_confirmed', 'ok' ),
			'already'      => array( 'news_already', 'ok' ),
			'unsubscribed' => array( 'news_unsubscribed', 'ok' ),
			'error'        => array( 'news_error', 'err' ),
			'invalid'      => array( 'news_invalid', 'err' ),
			'rate'         => array( 'news_rate', 'err' ),
			'mail_fail'    => array( 'news_mail_fail', 'err' ),
		);

		$alert = '';
		if ( $status && isset( $messages[ $status ] ) ) {
			$key   = $messages[ $status ][0];
			$kind  = $messages[ $status ][1];
			$alert = '<div class="yena-form-alert' . ( 'ok' === $kind ? ' yena-form-alert--ok' : '' ) . '" role="status">' . esc_html( $texts[ $key ] ) . '</div>';
		}

		ob_start();
		?>
		<section class="yena-newsletter" id="yena-newsletter">
			<div class="yena-newsletter__head">
				<p class="eyebrow"><?php echo esc_html( $texts['news_eyebrow'] ); ?></p>
				<h2 class="yena-newsletter__title"><?php echo esc_html( $texts['news_title'] ); ?></h2>
				<p class="yena-newsletter__text"><?php echo esc_html( $texts['news_intro'] ); ?></p>
			</div>
			<?php echo $alert; ?>
			<form class="yena-newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="yena_newsletter" />
				<?php wp_nonce_field( 'yena_newsletter', 'yena_news_nonce' ); ?>

				<div class="yena-field">
					<label for="yena_news_name"><?php echo esc_html( $texts['news_name'] ); ?></label>
					<input type="text" id="yena_news_name" name="yena_news_name" autocomplete="name" placeholder="<?php echo esc_attr( $texts['news_name_ph'] ); ?>" />
				</div>

				<div class="yena-field">
					<label for="yena_news_email"><?php echo esc_html( $texts['news_email'] ); ?></label>
					<input type="email" id="yena_news_email" name="yena_news_email" required autocomplete="email" placeholder="<?php echo esc_attr( $texts['news_email_ph'] ); ?>" />
				</div>

				<div class="yena-field yena-field--check">
					<input type="checkbox" id="yena_news_consent" name="yena_news_consent" value="1" required />
					<label for="yena_news_consent"><?php echo esc_html( $texts['news_consent'] ); ?></label>
				</div>

				<p class="yena-screen-reader-text">
					<label for="yena_news_website"><?php echo esc_html( $texts['news_website'] ); ?></label>
					<input type="text" id="yena_news_website" name="yena_news_website" tabindex="-1" autocomplete="off" />
				</p>

				<div class="yena-newsletter__actions">
					<button class="yena-btn yena-btn--hard" type="submit"><?php echo esc_html( $texts['news_button'] ); ?></button>
					<p class="yena-form-note"><?php echo esc_html( $texts['news_note'] ); ?></p>
				</div>
			</form>
		</section>
		<?php
		return ob_get_clean();
	}

	public function handle_newsletter() {
		if ( ! isset( $_POST['yena_news_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yena_news_nonce'] ) ), 'yena_newsletter' ) ) {
			$this->engage_redirect( array( 'yena_news' => 'error' ), 'newsletter' );
		}

		if ( ! empty( $_POST['yena_news_website'] ) ) {
			$this->engage_redirect( array( 'yena_news' => 'pending' ), 'newsletter' );
		}

		if ( $this->rate_limited( 'news' ) ) {
			$this->engage_redirect( array( 'yena_news' => 'rate' ), 'newsletter' );
		}

		$name   = isset( $_POST['yena_news_name'] ) ? sanitize_text_field( wp_unslash( $_POST['yena_news_name'] ) ) : '';
		$email  = isset( $_POST['yena_news_email'] ) ? sanitize_email( wp_unslash( $_POST['yena_news_email'] ) ) : '';
		$lang   = isset( $_POST['yena_news_lang'] ) ? sanitize_key( wp_unslash( $_POST['yena_news_lang'] ) ) : '';
		$consent = ! empty( $_POST['yena_news_consent'] );

		if ( ! $email || ! is_email( $email ) || ! $consent ) {
			$this->engage_redirect( array( 'yena_news' => 'error' ), 'newsletter' );
		}

		$email = strtolower( $email );
		if ( ! $lang ) {
			$lang = function_exists( 'yd_lang' ) ? yd_lang() : 'de';
		}

		global $wpdb;
		$table = YENA_DB::subscribers();

		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE email = %s", $email ) );

		if ( $existing && 'confirmed' === $existing->status ) {
			$this->engage_redirect( array( 'yena_news' => 'already' ), 'newsletter' );
		}

		$token   = bin2hex( random_bytes( 20 ) );
		$now     = current_time( 'mysql', true );
		$ip_hash = hash( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0' );

		$consent_text = isset( $_POST['yena_news_consent_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['yena_news_consent_text'] ) ) : '';

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'name'            => $name,
					'lang'            => $lang,
					'status'          => 'pending',
					'token'           => $token,
					'consent_text'    => $consent_text,
					'consent_ip_hash' => $ip_hash,
					'consent_at'      => $now,
					'unsubscribed_at' => null,
				),
				array( 'id' => $existing->id )
			);
			$subscriber_id = $existing->id;
		} else {
			$subscriber_id = $wpdb->insert(
				$table,
				array(
					'email'           => $email,
					'name'            => $name,
					'lang'            => $lang,
					'status'          => 'pending',
					'token'           => $token,
					'consent_text'    => $consent_text,
					'consent_ip_hash' => $ip_hash,
					'consent_at'      => $now,
					'created_at'      => $now,
				)
			);
			$subscriber_id = $wpdb->insert_id;
		}

		if ( ! $subscriber_id ) {
			$this->engage_redirect( array( 'yena_news' => 'error' ), 'newsletter' );
		}

		$sent = $this->send_confirmation_email( $subscriber_id, $email, $token, $lang );

		$this->engage_redirect( array( 'yena_news' => $sent ? 'pending' : 'mail_fail' ), 'newsletter' );
	}

	public function send_confirmation_email( $subscriber_id, $email, $token, $lang ) {
		$texts = $this->get_texts_for_lang( $lang );
		$link  = add_query_arg(
			array(
				'yena_news' => 'confirm',
				'id'        => $subscriber_id,
				'token'     => $token,
			),
			home_url( '/' )
		);

		global $wpdb;
		$name = (string) $wpdb->get_var( $wpdb->prepare( 'SELECT name FROM ' . YENA_DB::subscribers() . ' WHERE id = %d', $subscriber_id ) );
		if ( ! $name ) {
			$name = $texts['news_name_fallback'];
		}

		$body = str_replace(
			array( '{name}', '{link}' ),
			array( $name, $link ),
			$texts['news_confirm_body']
		);

		$sent = wp_mail( $email, $texts['news_confirm_subject'], $body );

		wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[%s] ' . $texts['news_confirm_subject'], get_bloginfo( 'name' ) ),
			'New subscription pending confirmation: ' . $email
		);

		return (bool) $sent;
	}

	public function newsletter_action() {
		if ( ! isset( $_GET['yena_news'] ) ) {
			return;
		}

		$mode = sanitize_key( wp_unslash( $_GET['yena_news'] ) );
		if ( 'confirm' !== $mode && 'unsubscribe' !== $mode ) {
			return;
		}

		global $wpdb;
		$table   = YENA_DB::subscribers();
		$id      = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$sub     = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) : null;

		if ( ! $sub || ! $token || ! hash_equals( $sub->token, $token ) ) {
			wp_safe_redirect( add_query_arg( 'yena_news', 'invalid', home_url( '/' ) ) . '#yena-newsletter' );
			exit;
		}

		if ( 'confirm' === $mode ) {
			$now = current_time( 'mysql', true );
			$wpdb->update(
				$table,
				array( 'status' => 'confirmed', 'confirmed_at' => $now ),
				array( 'id' => $id )
			);

			$texts = $this->get_texts_for_lang( $sub->lang );
			wp_mail( $sub->email, $texts['news_welcome_subject'], $texts['news_welcome_body'] );

			wp_safe_redirect( add_query_arg( 'yena_news', 'confirmed', home_url( '/' ) ) . '#yena-newsletter' );
			exit;
		}

		$now = current_time( 'mysql', true );
		$wpdb->update(
			$table,
			array( 'status' => 'unsubscribed', 'unsubscribed_at' => $now ),
			array( 'id' => $id )
		);

		wp_safe_redirect( add_query_arg( 'yena_news', 'unsubscribed', home_url( '/' ) ) . '#yena-newsletter' );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Event registration form                                            */
	/* ------------------------------------------------------------------ */

	public function render_registration_form( $event_id = 0 ) {
		if ( ! $event_id ) {
			$event_id = get_the_ID();
		}
		if ( 'yd_event' !== get_post_type( $event_id ) ) {
			return '';
		}

		$settings = get_option( 'yena_settings', array() );
		$texts    = $this->get_texts();

		$lang = function_exists( 'yd_lang' ) ? yd_lang() : 'de';

		$status = isset( $_GET['yena_reg'] ) ? sanitize_key( wp_unslash( $_GET['yena_reg'] ) ) : '';
		$messages = array(
			'ok'           => array( 'reg_ok', 'ok' ),
			'ok_nomail'    => array( 'reg_ok_nomail', 'err' ),
			'full'         => array( 'reg_full', 'ok' ),
			'full_nomail'  => array( 'reg_full', 'ok' ),
			'already'      => array( 'reg_already', 'ok' ),
			'cancelled'    => array( 'reg_cancelled', 'ok' ),
			'error'        => array( 'reg_error', 'err' ),
			'invalid'      => array( 'reg_invalid', 'err' ),
			'closed'       => array( 'reg_closed', 'err' ),
			'rate'         => array( 'reg_rate', 'err' ),
			'payment_unavailable' => array( 'reg_payment_unavailable', 'err' ),
			'pending_payment' => array( 'reg_pending_payment', 'ok' ),
		);

		$alert = '';
		if ( $status && isset( $messages[ $status ] ) ) {
			$key   = $messages[ $status ][0];
			$kind  = $messages[ $status ][1];
			$alert = '<div class="yena-form-alert' . ( 'ok' === $kind ? ' yena-form-alert--ok' : '' ) . '" role="status">' . esc_html( $texts[ $key ] ) . '</div>';
		}

		$reg_open = get_post_meta( $event_id, 'yena_reg_open', true );
		$capacity = (int) get_post_meta( $event_id, 'yena_capacity', true );
		$closed   = '0' === $reg_open;
		$event_price = $this->get_event_price( $event_id );

		$taken = $this->taken_seats( $event_id );
		$left  = $capacity > 0 ? max( 0, $capacity - $taken ) : 0;

		if ( $closed ) {
			$seats_note = $texts['reg_closed'];
		} elseif ( $capacity > 0 ) {
			$seats_note = sprintf( $texts['reg_left'], $left );
		} else {
			$seats_note = $texts['reg_unlimited'];
		}

		// Poster image.
		$poster_url = get_post_meta( $event_id, 'yena_poster_image', true );

		ob_start();
		?>
		<section class="yena-registration" id="yena-registration">
			<div class="yena-registration__head">
				<?php if ( $poster_url ) : ?>
					<div class="yena-registration__poster">
						<img src="<?php echo esc_url( $poster_url ); ?>" alt="<?php echo esc_attr( get_the_title( $event_id ) ); ?>" />
					</div>
				<?php endif; ?>
				<p class="eyebrow"><?php echo esc_html( $texts['reg_eyebrow'] ); ?></p>
				<h2 class="yena-registration__title"><?php echo esc_html( $texts['reg_title'] ); ?></h2>
				<p class="yena-registration__text"><?php echo esc_html( $texts['reg_intro'] ); ?></p>
				<?php if ( $event_price > 0 ) : ?>
					<p class="yena-registration__price"><?php echo esc_html( sprintf( $texts['reg_paid'], number_format_i18n( $event_price, 2 ) ) ); ?></p>
				<?php else : ?>
					<p class="yena-registration__price"><?php echo esc_html( $texts['reg_free'] ); ?></p>
				<?php endif; ?>
				<p class="yena-registration__seats"><?php echo esc_html( $seats_note ); ?></p>
			</div>
			<?php echo $alert; ?>

			<?php if ( $closed ) : ?>
				<p class="yena-form-alert" role="status"><?php echo esc_html( $texts['reg_closed'] ); ?></p>
			<?php else : ?>
				<?php if ( $capacity > 0 && $left <= 0 ) : ?>
					<p class="yena-form-alert" role="status"><?php echo esc_html( $texts['reg_full'] ); ?></p>
				<?php else : ?>
					<form class="yena-registration__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="yena_register" />
						<input type="hidden" name="yena_reg_event" value="<?php echo esc_attr( $event_id ); ?>" />
						<?php wp_nonce_field( 'yena_registration', 'yena_reg_nonce' ); ?>

						<div class="yena-field">
							<label for="yena_reg_name"><?php echo esc_html( $texts['reg_name'] ); ?></label>
							<input type="text" id="yena_reg_name" name="yena_reg_name" required autocomplete="name" />
						</div>

						<div class="yena-field">
							<label for="yena_reg_email"><?php echo esc_html( $texts['reg_email'] ); ?></label>
							<input type="email" id="yena_reg_email" name="yena_reg_email" required autocomplete="email" />
						</div>

						<div class="yena-field">
							<label for="yena_reg_phone"><?php echo esc_html( $texts['reg_phone'] ); ?></label>
							<input type="tel" id="yena_reg_phone" name="yena_reg_phone" autocomplete="tel" />
						</div>

						<div class="yena-field">
							<label for="yena_reg_persons"><?php echo esc_html( $texts['reg_persons'] ); ?></label>
							<select id="yena_reg_persons" name="yena_reg_persons">
								<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
									<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
								<?php endfor; ?>
							</select>
						</div>

						<div class="yena-field">
							<label for="yena_reg_notes"><?php echo esc_html( $texts['reg_notes'] ); ?></label>
							<textarea id="yena_reg_notes" name="yena_reg_notes" rows="3"></textarea>
						</div>

						<div class="yena-field yena-field--check">
							<input type="checkbox" id="yena_reg_consent" name="yena_reg_consent" value="1" required />
							<label for="yena_reg_consent"><?php echo esc_html( $texts['reg_consent'] ); ?></label>
						</div>

						<div class="yena-field yena-field--check">
							<input type="checkbox" id="yena_reg_newsletter" name="yena_reg_newsletter" value="1" />
							<label for="yena_reg_newsletter"><?php echo esc_html( $texts['reg_newsletter'] ); ?></label>
						</div>

						<p class="yena-screen-reader-text">
							<label for="yena_reg_website"><?php echo esc_html( $texts['reg_website'] ); ?></label>
							<input type="text" id="yena_reg_website" name="yena_reg_website" tabindex="-1" autocomplete="off" />
						</p>

						<button class="yena-btn yena-btn--hard" type="submit"><?php echo esc_html( $texts['reg_button'] ); ?></button>
					</form>
				<?php endif; ?>
			<?php endif; ?>
			<?php echo $this->render_participant_logos( $event_id ); ?>
		</section>
		<?php
		return ob_get_clean();
	}

	public function maybe_append_registration( $content ) {
		if ( ! is_singular( 'yd_event' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( has_shortcode( $content, 'yen_registration' ) ) {
			return $content;
		}
		return $content . $this->render_registration_form( get_the_ID() );
	}

	/**
	 * Render optional participant/partner logos at the bottom of the event block.
	 */
	public function render_participant_logos( $event_id ) {
		$logos = get_post_meta( $event_id, 'yena_participant_logos', true );
		if ( ! is_array( $logos ) || empty( $logos ) ) {
			return '';
		}
		$texts = $this->get_texts();
		ob_start();
		?>
		<section class="yena-participant-logos" aria-label="<?php echo esc_attr( $texts['reg_participants_title'] ); ?>">
			<h3><?php echo esc_html( $texts['reg_participants_title'] ); ?></h3>
			<div class="yena-participant-logos__grid">
				<?php foreach ( $logos as $logo ) :
					$url = isset( $logo['url'] ) ? esc_url( $logo['url'] ) : '';
					$name = isset( $logo['name'] ) ? $logo['name'] : '';
					$link = isset( $logo['link'] ) ? esc_url( $logo['link'] ) : '';
					if ( ! $url ) { continue; }
					$img = '<img src="' . $url . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';
				?>
					<div class="yena-participant-logos__item">
						<?php if ( $link ) : ?><a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"><?php echo $img; ?></a><?php else : ?><?php echo $img; ?><?php endif; ?>
						<?php if ( $name ) : ?><span><?php echo esc_html( $name ); ?></span><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	public function handle_registration() {
		if ( ! isset( $_POST['yena_reg_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['yena_reg_nonce'] ) ), 'yena_registration' ) ) {
			$this->engage_redirect( array( 'yena_reg' => 'error' ), 'registration' );
		}

		if ( ! empty( $_POST['yena_reg_website'] ) ) {
			$this->engage_redirect( array( 'yena_reg' => 'ok' ), 'registration' );
		}

		if ( $this->rate_limited( 'reg' ) ) {
			$this->engage_redirect( array( 'yena_reg' => 'rate' ), 'registration' );
		}

		$event_id = isset( $_POST['yena_reg_event'] ) ? absint( $_POST['yena_reg_event'] ) : 0;
		$event    = $event_id ? get_post( $event_id ) : null;

		if ( ! $event || 'yd_event' !== $event->post_type || 'publish' !== $event->post_status ) {
			$this->engage_redirect( array( 'yena_reg' => 'error' ), 'registration' );
		}

		if ( '0' === get_post_meta( $event_id, 'yena_reg_open', true ) ) {
			$this->engage_redirect( array( 'yena_reg' => 'closed' ), 'registration' );
		}

		$name    = isset( $_POST['yena_reg_name'] ) ? sanitize_text_field( wp_unslash( $_POST['yena_reg_name'] ) ) : '';
		$email   = isset( $_POST['yena_reg_email'] ) ? sanitize_email( wp_unslash( $_POST['yena_reg_email'] ) ) : '';
		$phone   = isset( $_POST['yena_reg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['yena_reg_phone'] ) ) : '';
		$persons = isset( $_POST['yena_reg_persons'] ) ? absint( $_POST['yena_reg_persons'] ) : 1;
		$notes   = isset( $_POST['yena_reg_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['yena_reg_notes'] ) ) : '';
		$consent = ! empty( $_POST['yena_reg_consent'] );
		$newsletter = ! empty( $_POST['yena_reg_newsletter'] );

		$persons = max( 1, min( 10, $persons ) );

		if ( ! $name || ! $email || ! is_email( $email ) || ! $consent ) {
			$this->engage_redirect( array( 'yena_reg' => 'error' ), 'registration' );
		}

		// Determine event price type
		$price_type = get_post_meta( $event_id, 'yena_price_type', true );
		$event_price = get_post_meta( $event_id, 'yena_event_price', true );

		// If event is free, no price should be set; if paid, price must be > 0
		if ( 'free' === $price_type && $event_price ) {
			// Free event but price was set - reset price
			$event_price = 0;
		}
		if ( 'paid' === $price_type && ( ! $event_price || $event_price <= 0 ) ) {
			// Paid event but no price set - redirect with error
			$this->engage_redirect( array( 'yena_reg' => 'price_error' ), 'registration' );
			return;
		}

		// For free events: automatically confirm registration
		// For paid events: set status to pending_payment
		if ( 'free' === $price_type ) {
			$status = 'paid';
			$payment_status = 'paid';
			$payment_method = 'free_event';
			$payment_amount = 0.0;
		} else {
			// Paid event
			$status = 'pending_payment';
			$payment_status = 'pending';
			$payment_method = 'woocommerce';
			$payment_amount = $event_price * $persons;
		}

		$email = strtolower( $email );
		$lang  = function_exists( 'yd_lang' ) ? yd_lang() : 'de';
		$settings = get_option( 'yena_settings', array() );
		$texts = $this->get_texts();

		global $wpdb;
		$table  = YENA_DB::registrations();
		$now    = current_time( 'mysql', true );
		$ip_hash = hash( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0' );

		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE event_id = %d AND email = %s", $event_id, $email ) );

		if ( $existing && 'cancelled' !== $existing->status ) {
			$this->engage_redirect( array( 'yena_reg' => 'already' ), 'registration' );
		}

		// Serialize registrations per event so simultaneous requests cannot take the last seat twice.
		$lock_name = 'yena_event_' . $event_id;
		$lock      = $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $lock_name ) );
		if ( '1' !== (string) $lock ) {
			$this->engage_redirect( array( 'yena_reg' => 'error' ), 'registration' );
		}

		// Recheck the duplicate and capacity after acquiring the per-event lock.
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE event_id = %d AND email = %s", $event_id, $email ) );
		if ( $existing && 'cancelled' !== $existing->status ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
			$this->engage_redirect( array( 'yena_reg' => 'already' ), 'registration' );
		}

		$capacity = (int) get_post_meta( $event_id, 'yena_capacity', true );
		$taken    = $this->taken_seats( $event_id );
		if ( $capacity > 0 && $taken + $persons > $capacity ) {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
			$this->engage_redirect( array( 'yena_reg' => 'full' ), 'registration' );
		}

		// Check if event has a price (paid event)
		$event_price = $this->get_event_price( $event_id );
		$is_paid_event = $event_price > 0;
		$total_amount  = $event_price * $persons;

		$token   = bin2hex( random_bytes( 20 ) );
		$ticket_code = bin2hex( random_bytes( 16 ) );
		$ticket_number = $this->generate_ticket_number( $event_id );

		// Paid registrations require an actual WooCommerce order with a fee item.
		$wc_order    = null;
		$wc_order_id = null;
		if ( $is_paid_event ) {
			if ( ! function_exists( 'wc_create_order' ) || ! function_exists( 'wc_get_order' ) ) {
				$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
				$this->engage_redirect( array( 'yena_reg' => 'payment_unavailable' ), 'registration' );
			}

			$wc_order = wc_create_order( array( 'status' => 'pending' ) );
			if ( is_wp_error( $wc_order ) || ! $wc_order instanceof WC_Order ) {
				$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
				$this->log_payment_error( 'Could not create WooCommerce order for event ' . $event_id );
				$this->engage_redirect( array( 'yena_reg' => 'payment_unavailable' ), 'registration' );
			}

			$wc_order->set_billing_first_name( $name );
			$wc_order->set_billing_email( $email );
			$wc_order->set_billing_phone( $phone );
			$wc_order->set_currency( get_woocommerce_currency() );

			$fee = new WC_Order_Item_Fee();
			$fee->set_name( get_the_title( $event_id ) );
			$fee->set_amount( $total_amount );
			$fee->set_total( $total_amount );
			$fee->set_tax_status( 'none' );
			$wc_order->add_item( $fee );
			$wc_order->calculate_totals( false );

			if ( count( $wc_order->get_items( 'fee' ) ) < 1 || abs( (float) $wc_order->get_total() - (float) $total_amount ) > 0.01 ) {
				$wc_order->update_status( 'cancelled', 'Yalla Engage order total validation failed.' );
				$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
				$this->log_payment_error( 'Order total mismatch for event ' . $event_id . ', expected ' . $total_amount . ', got ' . $wc_order->get_total() );
				$this->engage_redirect( array( 'yena_reg' => 'payment_unavailable' ), 'registration' );
			}

			$wc_order->save();
			$wc_order_id = $wc_order->get_id();
		}

		// For paid events, set status to 'pending_payment' until an administrator confirms.
		if ( $is_paid_event ) {
			$status = 'pending_payment';
		} else {
			$status = 'confirmed';
		}

		$consent_text = isset( $_POST['yena_reg_consent_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['yena_reg_consent_text'] ) ) : '';
		$registration_saved = true;

		if ( $existing ) {
			$registration_saved = false !== $wpdb->update(
				$table,
				array(
					'name'             => $name,
					'email'            => $email,
					'phone'            => $phone,
					'persons'          => $persons,
					'notes'            => $notes,
					'lang'             => $lang,
					'status'           => $status,
					'token'            => $token,
					'consent_text'     => $consent_text,
					'consent_ip_hash'  => $ip_hash,
					'consent_at'       => $now,
					'ticket_code'      => $ticket_code,
					'ticket_number'    => $ticket_number,
					'checked_in_at'    => null,
					'checked_in_by'    => null,
					'checked_in_persons' => null,
					'payment_status'   => $payment_status,
					'order_id'         => $wc_order_id,
					'payment_amount'   => $is_paid_event ? $total_amount : 0,
					'payment_method'   => $payment_method,
				),
				array( 'id' => $existing->id )
			);
			$reg_id = $existing->id;
		} else {
			$registration_saved = false !== $wpdb->insert(
				$table,
				array(
					'event_id'         => $event_id,
					'name'             => $name,
					'email'            => $email,
					'phone'            => $phone,
					'persons'          => $persons,
					'notes'            => $notes,
					'lang'             => $lang,
					'status'           => $status,
					'token'            => $token,
					'consent_text'     => $consent_text,
					'consent_ip_hash'  => $ip_hash,
					'consent_at'       => $now,
					'created_at'       => $now,
					'ticket_code'      => $ticket_code,
					'ticket_number'    => $ticket_number,
					'payment_status'   => $payment_status,
					'order_id'         => $wc_order_id,
					'payment_amount'   => $is_paid_event ? $total_amount : 0,
					'payment_method'   => $payment_method,
				)
			);
			$reg_id = $registration_saved ? absint( $wpdb->insert_id ) : 0;
		}

		if ( ! $registration_saved || ! $reg_id ) {
			if ( $wc_order ) {
				$wc_order->update_status( 'cancelled', 'Yalla Engage registration record could not be saved.' );
			}
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );
			$this->engage_redirect( array( 'yena_reg' => 'error' ), 'registration' );
		}

		if ( $wc_order ) {
			$wc_order->update_meta_data( '_yena_registration_id', absint( $reg_id ) );
			$wc_order->save();
		}
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock_name ) );

		if ( $newsletter ) {
			$this->subscribe_newsletter( $name, $email, $lang );
		}

		// Free events are confirmed immediately. Paid events remain pending until
		// an administrator confirms payment from the registrations screen.
		if ( ! $is_paid_event ) {
			$sent = $this->send_registration_confirmation( $reg_id, $event_id, $name, $email, $persons, $lang, $ticket_code, $ticket_number );

			$admin_email = isset( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
			if ( $admin_email ) {
				$admin_body = sprintf(
					"%s\n%s\n%s\n%s\n%s\n%s\n%s\n%s",
					$texts['reg_name'] . ': ' . $name,
					$texts['reg_email'] . ': ' . $email,
					$texts['reg_phone'] . ': ' . $phone,
					$texts['reg_persons'] . ': ' . $persons,
					$texts['reg_notes'] . ': ' . $notes,
					'Event: ' . get_the_title( $event_id ),
					'Status: ' . $status,
					'Time: ' . $now
				);
				wp_mail( $admin_email, sprintf( $texts['reg_admin_subject'], get_the_title( $event_id ) ), $admin_body );
			}

			$flag = $sent ? 'ok' : 'ok_nomail';
			$this->engage_redirect( array( 'yena_reg' => $flag ), 'registration' );
		} else {
			$admin_email = isset( $settings['notify_email'] ) ? $settings['notify_email'] : get_option( 'admin_email' );
			if ( $admin_email ) {
				wp_mail( $admin_email, sprintf( $texts['reg_admin_subject'], get_the_title( $event_id ) ), 'Paid registration pending payment: ' . $email );
			}
			// Send this registrant to payment for their own order.
			$order = wc_get_order( $wc_order_id );
			if ( $order ) {
				wp_safe_redirect( $order->get_checkout_payment_url() );
				exit;
			}
			$this->engage_redirect( array( 'yena_reg' => 'payment_unavailable' ), 'registration' );
		}
	}

	/** Confirm a paid order and send its ticket at most once. */
	public function yena_woocommerce_order_paid( $order_id ) {
		$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
		if ( ! $order || 'yes' === $order->get_meta( '_yena_ticket_sent' ) ) {
			return;
		}

		global $wpdb;
		$table = YENA_DB::registrations();
		$reg   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE order_id = %d", absint( $order_id ) ) );
		if ( ! $reg || 'confirmed' === $reg->status || 'refunded' === $reg->status || 'cancelled' === $reg->status ) {
			return;
		}

		$updated = $wpdb->update(
			$table,
			array( 'status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => sanitize_key( $order->get_payment_method() ) ),
			array( 'id' => absint( $reg->id ), 'status' => 'pending_payment' ),
			array( '%s', '%s', '%s' ),
			array( '%d', '%s' )
		);
		if ( false === $updated || 0 === $updated ) {
			return;
		}

		$sent = $this->send_registration_confirmation(
			$reg->id,
			$reg->event_id,
			$reg->name,
			$reg->email,
			$reg->persons,
			$reg->lang,
			$reg->ticket_code,
			$reg->ticket_number
		);
		if ( $sent ) {
			$order->update_meta_data( '_yena_ticket_sent', 'yes' );
			$order->save();
		} else {
			$this->log_payment_error( 'Ticket email failed for registration ' . absint( $reg->id ) . ', order ' . absint( $order_id ) );
		}
	}

	/** Keep failed registrations pending until the 24-hour cleanup; do not release seats early. */
	public function yena_woocommerce_order_failed( $order_id ) {
		$this->update_order_registration_state( $order_id, 'pending_payment', 'failed' );
	}

	/** A cancelled WooCommerce order cancels its registration and releases its seat. */
	public function yena_woocommerce_order_cancelled( $order_id ) {
		$this->update_order_registration_state( $order_id, 'cancelled', 'cancelled' );
	}

	/** A refunded order remains auditable as refunded, and releases its seat once. */
	public function yena_woocommerce_order_refunded( $order_id ) {
		$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
		if ( ! $order || 'yes' === $order->get_meta( '_yena_refund_processed' ) ) {
			return;
		}
		if ( $this->update_order_registration_state( $order_id, 'refunded', 'refunded' ) ) {
			$order->update_meta_data( '_yena_refund_processed', 'yes' );
			$order->save();
		}
	}

	/** Update the linked registration once for a WooCommerce state transition. */
	private function update_order_registration_state( $order_id, $registration_status, $payment_status ) {
		global $wpdb;
		$table = YENA_DB::registrations();
		$allowed_statuses = 'failed' === $payment_status ? array( 'pending_payment' ) : array( 'pending_payment', 'confirmed' );
		$status_placeholders = implode( ', ', array_fill( 0, count( $allowed_statuses ), '%s' ) );
		$sql                 = "UPDATE {$table} SET status = %s, payment_status = %s WHERE order_id = %d AND status IN ({$status_placeholders})";
		$args                = array_merge( array( $registration_status, $payment_status, absint( $order_id ) ), $allowed_statuses );
		$updated             = $wpdb->query( $wpdb->prepare( $sql, $args ) );
		return 1 === (int) $updated;
	}

	/** Cancel only aged registrations whose WooCommerce order is still pending or failed. */
	public function cleanup_pending_payments() {
		if ( ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		global $wpdb;
		$table  = YENA_DB::registrations();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
		$regs   = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, order_id FROM {$table} WHERE status = %s AND created_at < %s",
			'pending_payment',
			$cutoff
		) );
		foreach ( $regs as $reg ) {
			$order = $reg->order_id ? wc_get_order( $reg->order_id ) : false;
			if ( ! $order || ! in_array( $order->get_status(), array( 'pending', 'failed' ), true ) ) {
				continue;
			}
			$order->update_status( 'cancelled', 'Yalla Engage: pending registration expired after 24 hours.' );
			$wpdb->update(
				$table,
				array( 'status' => 'cancelled', 'payment_status' => 'cancelled' ),
				array( 'id' => absint( $reg->id ), 'status' => 'pending_payment' ),
				array( '%s', '%s' ),
				array( '%d', '%s' )
			);
		}
	}

	/** Write payment integration diagnostics to WooCommerce's log system. */
	private function log_payment_error( $message ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->error( $message, array( 'source' => 'yalla-engage' ) );
		}
	}

	private function subscribe_newsletter( $name, $email, $lang ) {
		global $wpdb;
		$table = YENA_DB::subscribers();

		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, status FROM {$table} WHERE email = %s", $email ) );

		if ( $existing && 'confirmed' === $existing->status ) {
			return true;
		}

		$token   = bin2hex( random_bytes( 20 ) );
		$now     = current_time( 'mysql', true );
		$ip_hash = hash( 'sha256', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0' );

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'name'            => $name,
					'lang'            => $lang,
					'status'          => 'pending',
					'token'           => $token,
					'consent_at'      => $now,
					'unsubscribed_at' => null,
				),
				array( 'id' => $existing->id )
			);
			$id = $existing->id;
		} else {
			$id = $wpdb->insert(
				$table,
				array(
					'email'          => $email,
					'name'           => $name,
					'lang'           => $lang,
					'status'         => 'pending',
					'token'          => $token,
					'consent_at'     => $now,
					'created_at'     => $now,
				)
			);
			$id = $wpdb->insert_id;
		}

		if ( ! $id ) {
			return false;
		}

		return $this->send_confirmation_email( $id, $email, $token, $lang );
	}

	public function send_registration_confirmation( $reg_id, $event_id, $name, $email, $persons, $lang, $ticket_code, $ticket_number ) {
		$texts = $this->get_texts_for_lang( $lang );

		$date  = get_post_meta( $event_id, '_yd_date', true );
		$time  = get_post_meta( $event_id, '_yd_time', true );
		$end   = get_post_meta( $event_id, '_yd_end_time', true );
		$venue = get_post_meta( $event_id, '_yd_venue', true );
		$city  = get_post_meta( $event_id, '_yd_city', true );

		$time_lab    = ( $time && $end ) ? $time . ' – ' . $end : $time;
		$venue_lab   = implode( ', ', array_filter( array( $venue, $city ) ) );
		$event_title = get_the_title( $event_id );

		$cancel_link = add_query_arg(
			array(
				'yena_reg' => 'cancel',
				'id'       => $reg_id,
				'token'    => $this->get_reg_token( $reg_id ),
			),
			get_permalink( $event_id )
		);

		$verify_link = add_query_arg(
			array(
				'yena_verify' => '1',
				'code'        => $ticket_code,
			),
			home_url( '/' )
		);

		$qr_dir   = YENA_DIR . 'assets/qr';
		$qr_file  = $qr_dir . '/qr_' . $reg_id . '.png';
		$pdf_file = $qr_dir . '/ticket_' . $reg_id . '.pdf';

		YENA_QR::generate( $verify_link, $qr_file, 600 );
		YENA_PDF::generate( $reg_id, $name, $event_title, $date, $time_lab, $venue_lab, $persons, $ticket_number, $cancel_link, $verify_link, $qr_file, $lang, $pdf_file );

		$html = $this->get_confirmation_html( $reg_id, $name, $event_title, $date, $time_lab, $venue_lab, $persons, $ticket_number, $cancel_link, $verify_link, $lang );

		$subject = str_replace( '{event}', $event_title, $texts['reg_confirm_subject'] );

		$from_email = sanitize_email( get_option( 'admin_email' ) );
		$from_name  = sanitize_text_field( get_bloginfo( 'name' ) );

		$headers = array(
			'From: ' . $from_name . ' <' . $from_email . '>',
			'Reply-To: ' . $from_name . ' <' . $from_email . '>',
			'Content-Type: text/html; charset=UTF-8',
		);

		$attachments = is_file( $pdf_file ) ? array( $pdf_file ) : array();

		$embed = function ( $phpmailer ) use ( $qr_file, $reg_id ) {
			if ( is_file( $qr_file ) ) {
				$phpmailer->addEmbeddedImage( $qr_file, 'yena_qr_' . $reg_id, 'qr_' . $reg_id . '.png', 'base64', 'image/png' );
			}
		};

		add_action( 'phpmailer_init', $embed );
		$sent = wp_mail( $email, $subject, $html, $headers, $attachments );
		remove_action( 'phpmailer_init', $embed );

		return (bool) $sent;
	}

	/**
	 * HTML body of the entry-ticket e-mail.
	 *
	 * @return string
	 */
	private function get_confirmation_html( $reg_id, $name, $event_title, $date, $time, $venue, $persons, $ticket_number, $cancel_link, $verify_link, $lang ) {
		$texts = $this->get_texts_for_lang( $lang );
		$dir   = ( 'ar' === $lang ) ? 'rtl' : 'ltr';
		$align = ( 'ar' === $lang ) ? 'right' : 'left';
		$qr_cid = 'cid:yena_qr_' . $reg_id;

		$logo_url = ( defined( 'YENA_DIR' ) && is_readable( YENA_DIR . 'assets/images/yena-ticket-logo.png' ) && defined( 'YENA_URI' ) ) ? YENA_URI . 'assets/images/yena-ticket-logo.png' : '';
		if ( ! $logo_url ) {
			$logo_id  = get_theme_mod( 'custom_logo' );
			$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		}

		$rows = array(
			$texts['ticket_event']   => $event_title,
			$texts['ticket_date']    => $date,
			$texts['ticket_time']    => $time,
			$texts['ticket_venue']   => $venue,
			$texts['ticket_persons'] => (string) $persons,
			$texts['ticket_number']  => $ticket_number,
		);

		$table_rows = '';
		foreach ( $rows as $label => $value ) {
			$table_rows .= '<tr>'
				. '<td style="padding:10px 12px;font-weight:700;color:#2b1c14;border-bottom:1px solid #e6e0d4;width:42%;">' . esc_html( $label ) . '</td>'
				. '<td style="padding:10px 12px;border-bottom:1px solid #e6e0d4;">' . esc_html( $value ) . '</td>'
				. '</tr>';
		}

		$confirmation_body = isset( $texts['reg_confirm_body'] ) ? $texts['reg_confirm_body'] : '';
		$cancel_note       = isset( $texts['reg_cancel_note'] ) ? $texts['reg_cancel_note'] : '';
		$body_values       = array(
			'{name}'        => esc_html( $name ),
			'{event}'       => esc_html( $event_title ),
			'{date}'        => esc_html( $date ),
			'{time}'        => esc_html( $time ),
			'{venue}'       => esc_html( $venue ),
			'{persons}'     => esc_html( (string) $persons ),
			'{ticket}'      => esc_html( $ticket_number ),
			'{cancel_note}' => esc_html( $cancel_note ),
			'{cancel_link}' => '<a href="' . esc_url( $cancel_link ) . '">' . esc_html( $texts['ticket_cancel'] ) . '</a>',
		);
		$confirmation_body = strtr( $confirmation_body, $body_values );
		$confirmation_body = nl2br( $confirmation_body, false );

		ob_start();
		?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo esc_attr( $dir ); ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f5eddd;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5eddd;padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #d8cdbd;border-radius:10px;font-family:Tahoma, Arial, sans-serif;color:#2b1c14;direction:<?php echo esc_attr( $dir ); ?>;text-align:<?php echo esc_attr( $align ); ?>;">
					<tr>
						<td style="background:#f5eddd;padding:24px;border-bottom:7px solid #2b1c14;border-radius:10px 10px 0 0;color:#2b1c14;">
							<?php if ( $logo_url ) : ?>
								<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="190" style="max-width:100%;height:auto;display:block;margin:0 0 14px;" />
							<?php endif; ?>
							<div style="font-size:20px;font-weight:700;color:#2b1c14;"><?php echo esc_html( $texts['ticket_title'] ); ?></div>
						</td>
					</tr>
					<tr>
						<td style="padding:24px;font-size:16px;line-height:1.6;">
							<div style="margin:0 0 16px;"><?php echo wp_kses( $confirmation_body, array( 'br' => array(), 'a' => array( 'href' => array() ) ) ); ?></div>
							<p style="text-align:center;margin:26px 0 10px;">
								<img src="<?php echo esc_attr( $qr_cid ); ?>" alt="<?php echo esc_attr( $texts['ticket_number'] ); ?>" width="240" height="240" style="display:block;margin:0 auto;border:2px solid #b9252b;border-radius:10px;padding:8px;background:#ffffff;" />
							</p>
							<p style="text-align:center;font-size:13px;color:#666666;margin:0 0 8px;">
								<?php echo esc_html( $texts['ticket_info'] ); ?>
							</p>
							<p style="text-align:center;font-size:13px;color:#666666;margin:0 0 8px;">
								<?php echo esc_html( $texts['ticket_pdf'] ); ?>
							</p>
							<p style="text-align:center;font-size:13px;margin:16px 0 0;">
								<a href="<?php echo esc_url( $verify_link ); ?>" style="color:#b9252b;"><?php echo esc_html( $texts['ticket_verify'] ); ?></a>
							</p>
							<p style="text-align:center;font-size:12px;color:#999999;margin:16px 0 0;">
								<a href="<?php echo esc_url( $cancel_link ); ?>" style="color:#999999;"><?php echo esc_html( $texts['ticket_cancel'] ); ?></a>
							</p>
						</td>
					</tr>
					<tr>
						<td style="padding:16px 24px;border-top:1px solid #e6e0d4;font-size:12px;color:#777777;">
							<?php echo esc_html( get_bloginfo( 'name' ) ); ?> &mdash; <?php echo esc_html( home_url( '/' ) ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
		<?php
		return ob_get_clean();
	}

	public function get_reg_token( $reg_id ) {
		global $wpdb;
		$table = YENA_DB::registrations();
		return (string) $wpdb->get_var( $wpdb->prepare( "SELECT token FROM {$table} WHERE id = %d", $reg_id ) );
	}

	public function registration_cancel() {
		if ( ! isset( $_GET['yena_reg'] ) || 'cancel' !== sanitize_key( wp_unslash( $_GET['yena_reg'] ) ) ) {
			return;
		}

		global $wpdb;
		$table   = YENA_DB::registrations();
		$id      = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$reg     = $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ) : null;

		if ( ! $reg || ! $token || ! hash_equals( $reg->token, $token ) ) {
			wp_safe_redirect( add_query_arg( 'yena_reg', 'invalid', home_url( '/' ) ) . '#yena-registration' );
			exit;
		}

		$now = current_time( 'mysql', true );
		$wpdb->update(
			$table,
			array( 'status' => 'cancelled' ),
			array( 'id' => $id )
		);

		$target = home_url( '/');
		if ( $reg->event_id ) {
			$permalink = get_permalink( $reg->event_id );
			if ( $permalink ) {
				$target = $permalink;
			}
		}

		wp_safe_redirect( add_query_arg( 'yena_reg', 'cancelled', $target ) . '#yena-registration' );
		exit;
	}

	public function taken_seats( $event_id ) {
		global $wpdb;
		$table = YENA_DB::registrations();
		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(persons), 0) FROM {$table} WHERE event_id = %d AND status IN ('confirmed', 'pending_payment')",
			$event_id
		) );
		return (int) $total;
	}

	public function rate_limited( $bucket ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
		$key = 'yena_rl_' . $bucket . '_' . md5( $ip );
		$count = (int) get_transient( $key );
		if ( $count >= self::RATE_LIMIT ) {
			return true;
		}
		set_transient( $key, $count + 1, self::RATE_WINDOW );
		return false;
	}

	protected function engage_redirect( $args, $anchor = '' ) {
		$url = wp_get_referer();
		if ( ! $url ) {
			$url = home_url( '/' );
		}
		$url = remove_query_arg( array( 'yena_news', 'yena_reg' ), $url );
		$url = add_query_arg( $args, $url );
		if ( $anchor ) {
			$url .= '#' . $anchor;
		}
		wp_safe_redirect( $url );
		exit;
	}

	public function get_texts() {
		return $this->get_texts_for_lang( function_exists( 'yd_lang' ) ? yd_lang() : 'de' );
	}

	public function get_texts_for_lang( $lang ) {
		$settings = get_option( 'yena_settings', array() );
		$texts    = isset( $settings['texts'][ $lang ] ) ? (array) $settings['texts'][ $lang ] : array();
		$defaults = $this->default_texts( $lang );

		// Empty saved values must not hide the built-in defaults.
		$texts = array_filter(
			$texts,
			static function ( $value ) {
				return '' !== trim( (string) $value );
			}
		);

		return array_merge( $defaults, $texts );
	}

	/**
	 * Return the configured price for an event. An explicit free selection
	 * overrides legacy price metadata from the old event module.
	 */
	public function get_event_price( $event_id ) {
		$type = get_post_meta( $event_id, 'yena_price_type', true );
		if ( 'free' === $type ) {
			return 0.0;
		}

		$price = (float) get_post_meta( $event_id, 'yena_event_price', true );
		if ( $price <= 0 ) {
			$price = (float) get_post_meta( $event_id, '_yd_price', true );
		}
		if ( $price <= 0 ) {
			$price = (float) get_post_meta( $event_id, 'yena_standard_price', true );
		}

		return max( 0, $price );
	}

	public function default_texts( $lang ) {
		if ( 'ar' === $lang ) {
			return array(
				'news_eyebrow'        => 'ابقَ على الإيقاع',
				'news_title'          => 'النشرة البريدية',
				'news_intro'          => 'مواعيد وورشات وأخبار المهرجان — رسائل قليلة في السنة، بلا إزعاج.',
				'news_name'           => 'الاسم (اختياري)',
				'news_name_ph'        => 'اسمك',
				'news_email'          => 'البريد الإلكتروني',
				'news_email_ph'       => 'اسم@مثال.com',
				'news_consent'        => 'أوافق على حفظ بريدي الإلكتروني لتلقي النشرة.',
				'news_website'        => 'الموقع الإلكتروني',
				'news_button'         => 'اشترك',
				'news_note'           => 'يمكنك إلغاء الاشتراك في أي وقت عبر الرابط في كل رسالة.',
				'news_pending'        => 'خطوة أخيرة! يرجى تأكيد الاشتراك عبر الرابط في بريدك.',
				'news_confirmed'      => 'شكراً! تم تأكيد اشتراكك في النشرة البريدية.',
				'news_already'        => 'هذا البريد مشترك بالفعل.',
				'news_error'          => 'يرجى إدخال بريد إلكتروني صالح والموافقة على الاشتراك.',
				'news_invalid'        => 'رابط التأكيد غير صالح أو منتهي.',
				'news_unsubscribed'   => 'تم إلغاء اشتراكك في النشرة البريدية.',
				'news_rate'           => 'محاولات كثيرة. يرجى المحاولة بعد بضع دقائق.',
				'news_mail_fail'      => 'تم حفظ اشتراكك، لكن تعذّر إرسال رسالة التأكيد.',
				'news_confirm_subject'=> 'يرجى تأكيد اشتراكك في النشرة البريدية',
				'news_confirm_body'   => "مرحباً {name}،\n\nيرجى تأكيد اشتراكك في النشرة البريدية لمهرجان الثقافة العربية عبر هذا الرابط:\n{link}\n\nإذا لم تكن قد اشتركت، تجاهل هذه الرسالة.",
				'news_confirm_ignore' => 'إذا لم تكن قد اشتركت، تجاهل هذه الرسالة.',
				'news_welcome_subject'=> 'أهلاً بك في النشرة البريدية',
				'news_welcome_body'   => "شكراً لتأكيدك! سنوافيك بالمواعيد وأخبار المهرجان.\n\nيمكنك إلغاء الاشتراك في أي وقت عبر الرابط في رسائلنا.",
				'reg_eyebrow'         => 'احجز مقعدك',
				'reg_title'           => 'تسجيل الحضور',
				'reg_intro'           => 'سجّل بياناتك — ستتلقى تأكيداً عبر البريد الإلكتروني.',
				'reg_name'            => 'الاسم',
				'reg_email'           => 'البريد الإلكتروني',
				'reg_phone'           => 'الهاتف (اختياري)',
				'reg_persons'         => 'عدد الأشخاص',
				'reg_notes'           => 'ملاحظات (اختياري)',
				'reg_consent'         => 'أوافق على حفظ بياناتي لهذا التسجيل.',
				'reg_newsletter'      => 'أريد أيضاً الاشتراك في النشرة البريدية',
				'reg_website'         => 'الموقع الإلكتروني',
				'reg_button'          => 'احجز مقعداً',
				'reg_ok'              => 'شكراً! تم استلام تسجيلك — التأكيد في طريقه إلى بريدك.',
				'reg_ok_nomail'       => 'تم حفظ تسجيلك، لكن تعذّر إرسال رسالة التأكيد.',
				'reg_full'            => 'الفعالية مكتملة.',
				'reg_already'         => 'يوجد تسجيل مسبق بهذا البريد لهذه الفعالية.',
				'reg_cancelled'       => 'تم إلغاء تسجيلك.',
				'reg_error'           => 'يرجى مراجعة بياناتك.',
				'reg_invalid'         => 'الرابط غير صالح أو منتهي.',
				'reg_closed'          => 'التسجيل لهذه الفعالية مغلق.',
				'reg_rate'            => 'محاولات كثيرة. يرجى المحاولة بعد بضع دقائق.',
				'reg_payment_unavailable' => 'هذه الفعالية مدفوعة، لكن الدفع الإلكتروني غير مُعدّ بعد. يرجى التواصل مع المنظم.',
				'reg_pending_payment' => 'تم استلام طلبك. سيتم إرسال التذكرة بعد تأكيد الدفع من المنظم.',
				'reg_left'            => 'المقاعد المتبقية: %d',
				'reg_unlimited'       => 'التسجيل مفتوح',
				'reg_free'            => 'الفعالية مجانية',
				'reg_paid'            => 'رسوم المشاركة: %s € لكل شخص',
				'reg_participants_title' => 'المشاركون والشركاء',
				'reg_confirm_subject' => 'تأكيد التسجيل / تذكرة دخول: {event}',
				'reg_confirm_body'    => "مرحباً {name}،\n\nتم تأكيد تسجيلك في:\n{event}\nالتاريخ: {date}\nالوقت: {time}\nالمكان: {venue}\nعدد الأشخاص: {persons}\nرقم التذكرة: {ticket}\n\n{cancel_note}\n{cancel_link}",
				'reminder_subject'    => 'تذكير: {event} غداً',
				'reminder_intro'      => 'نذكّرك بأنك مسجّل في الفعالية التالية، وموعدها غداً:',
				'reminder_note'       => 'أحضر تذكرتك معك عند الباب. نتمنى لك وقتاً ممتعاً!',
				'reg_cancel_note'     => 'لإلغاء تسجيلك:',
				'reg_admin_subject'   => 'تسجيل جديد: {event}',
				'news_name_fallback'  => 'عزيزي المشترك',
				'ticket_title'        => 'تأكيد التسجيل / تذكرة دخول',
				'ticket_hello'        => 'مرحباً {name}، تم تأكيد تسجيلك في الفعالية التالية:',
				'ticket_event'        => 'الفعالية',
				'ticket_date'         => 'التاريخ',
				'ticket_time'         => 'الوقت',
				'ticket_venue'        => 'المكان',
				'ticket_persons'      => 'عدد الأشخاص',
				'ticket_number'       => 'رقم التذكرة',
				'ticket_verify'       => 'تحقق من التذكرة',
				'ticket_info'         => 'أرضِ هذه التذكرة عند الباب للدخول إلى الفعالية.',
				'ticket_pdf'          => 'التذكرة مرفقة أيضاً كملف PDF.',
				'ticket_cancel'       => 'لإلغاء التسجيل',
			);
		}

		if ( 'en' === $lang ) {
			return array(
				'news_eyebrow'        => 'Stay in rhythm',
				'news_title'          => 'Newsletter',
				'news_intro'          => 'Events, workshops and festival news — a few emails a year, no spam.',
				'news_name'           => 'Name (optional)',
				'news_name_ph'        => 'Your name',
				'news_email'          => 'Email address',
				'news_email_ph'       => 'name@example.com',
				'news_consent'        => 'I agree to my email address being stored for the newsletter.',
				'news_website'        => 'Website',
				'news_button'         => 'Subscribe',
				'news_note'           => 'You can unsubscribe any time via the link in every email.',
				'news_pending'        => 'Almost there! Please confirm your subscription via the link in your inbox.',
				'news_confirmed'      => 'Thank you! Your newsletter subscription is confirmed.',
				'news_already'        => 'This email address is already subscribed.',
				'news_error'          => 'Please enter a valid email address and accept the consent.',
				'news_invalid'        => 'The confirmation link is invalid or has expired.',
				'news_unsubscribed'   => 'You have been unsubscribed from the newsletter.',
				'news_rate'           => 'Too many attempts. Please try again in a few minutes.',
				'news_mail_fail'      => 'Your subscription was saved, but the confirmation email could not be sent.',
				'news_confirm_subject'=> 'Please confirm your newsletter subscription',
				'news_confirm_body'   => "Hello {name},\n\nPlease confirm your subscription to the Arab Kulturfestival newsletter using this link:\n{link}\n\nIf you did not subscribe, simply ignore this email.",
				'news_confirm_ignore' => 'If you did not subscribe, simply ignore this email.',
				'news_welcome_subject'=> 'Welcome to the newsletter',
				'news_welcome_body'   => "Thank you for confirming! We will be in touch with upcoming dates and festival news.\n\nYou can unsubscribe at any time via the link in our emails.",
				'reg_eyebrow'         => 'Save your seat',
				'reg_title'           => 'Registration',
				'reg_intro'           => 'Sign up — you will receive a confirmation by email.',
				'reg_name'            => 'Name',
				'reg_email'           => 'Email',
				'reg_phone'           => 'Phone (optional)',
				'reg_persons'         => 'Number of persons',
				'reg_notes'           => 'Message (optional)',
				'reg_consent'         => 'I agree to my details being stored for this registration.',
				'reg_newsletter'      => 'I would also like to subscribe to the newsletter',
				'reg_website'         => 'Website',
				'reg_button'          => 'Reserve a seat',
				'reg_ok'              => 'Thank you! Your registration was received — a confirmation is on its way by email.',
				'reg_ok_nomail'       => 'Your registration was saved, but the confirmation email could not be sent.',
				'reg_full'            => 'This event is fully booked.',
				'reg_already'         => 'There is already a registration for this email at this event.',
				'reg_cancelled'       => 'Your registration has been cancelled.',
				'reg_error'           => 'Please check your details.',
				'reg_invalid'         => 'The link is invalid or has expired.',
				'reg_closed'          => 'Registration for this event is closed.',
				'reg_rate'            => 'Too many attempts. Please try again in a few minutes.',
				'reg_payment_unavailable' => 'This event is paid, but online payment is not configured yet. Please contact the organiser.',
				'reg_pending_payment' => 'Your request was received. The ticket will be sent after the organiser confirms payment.',
				'reg_left'            => '%d seats left',
				'reg_unlimited'       => 'Registration open',
				'reg_free'            => 'This event is free',
				'reg_paid'            => 'Participation fee: €%s per person',
				'reg_participants_title' => 'Participants and partners',
				'reg_confirm_subject' => 'Registration Confirmed / Entry Ticket: {event}',
				'reg_confirm_body'    => "Hello {name},\n\nYour registration is confirmed for:\n{event}\nDate: {date}\nTime: {time}\nVenue: {venue}\nPersons: {persons}\nTicket Number: {ticket}\n\n{cancel_note}\n{cancel_link}",
				'reminder_subject'    => 'Reminder: {event} is tomorrow',
				'reminder_intro'      => 'A quick reminder about your registration for tomorrow:',
				'reminder_note'       => 'Please bring your ticket with you to the entrance. Enjoy the event!',
				'reg_cancel_note'     => 'To cancel your registration:',
				'reg_admin_subject'   => 'New registration: {event}',
				'news_name_fallback'  => 'there',
				'ticket_title'        => 'Registration Confirmed / Entry Ticket',
				'ticket_hello'        => 'Hello {name}, your registration is confirmed for:',
				'ticket_event'        => 'Event',
				'ticket_date'         => 'Date',
				'ticket_time'         => 'Time',
				'ticket_venue'        => 'Venue',
				'ticket_persons'      => 'Persons',
				'ticket_number'       => 'Ticket number',
				'ticket_verify'       => 'Verify ticket',
				'ticket_info'         => 'Present this ticket at the entrance for entry.',
				'ticket_pdf'          => 'The ticket is also attached as a PDF file.',
				'ticket_cancel'       => 'To cancel your registration',
			);
		}

		// German (default).
		return array(
			'news_eyebrow'        => 'Bleib im Takt',
			'news_title'          => 'Newsletter',
			'news_intro'          => 'Termine, Workshops und Festivalnews — wenige E-Mails im Jahr, kein Spam.',
			'news_name'           => 'Name (optional)',
			'news_name_ph'        => 'Ihr Name',
			'news_email'          => 'E-Mail-Adresse',
			'news_email_ph'       => 'name@example.com',
			'news_consent'        => 'Ich bin mit der Speicherung meiner E-Mail-Adresse für den Newsletter einverstanden.',
			'news_website'        => 'Website',
			'news_button'         => 'Anmelden',
			'news_note'           => 'Sie können sich jederzeit über den Link in jeder E-Mail abmelden.',
			'news_pending'        => 'Fast geschafft! Bitte bestätigen Sie die Anmeldung über den Link in Ihrem Posteingang.',
			'news_confirmed'      => 'Danke! Ihre Newsletter-Anmeldung ist bestätigt.',
			'news_already'        => 'Diese E-Mail-Adresse ist bereits angemeldet.',
			'news_error'          => 'Bitte geben Sie eine gültige E-Mail-Adresse ein und akzeptieren Sie die Einwilligung.',
			'news_invalid'        => 'Der Bestätigungslink ist ungültig oder abgelaufen.',
			'news_unsubscribed'   => 'Sie wurden vom Newsletter abgemeldet.',
			'news_rate'           => 'Zu viele Versuche. Bitte versuchen Sie es in ein paar Minuten erneut.',
			'news_mail_fail'      => 'Ihre Anmeldung wurde gespeichert, aber die Bestätigungs-E-Mail konnte nicht versendet werden.',
			'news_confirm_subject'=> 'Bitte bestätigen Sie Ihre Newsletter-Anmeldung',
			'news_confirm_body'   => "Hallo {name},\n\nbitte bestätigen Sie Ihre Anmeldung zum Newsletter des Arab Kulturfestivals über diesen Link:\n{link}\n\nWenn Sie sich nicht angemeldet haben, ignorieren Sie diese E-Mail einfach.",
			'news_confirm_ignore' => 'Wenn Sie sich nicht angemeldet haben, ignorieren Sie diese E-Mail einfach.',
			'news_welcome_subject'=> 'Willkommen beim Newsletter',
			'news_welcome_body'   => "Danke für Ihre Bestätigung! Wir melden uns mit den nächsten Terminen und Festivalnews.\n\nSie können sich jederzeit über den Link in unseren E-Mails abmelden.",
			'reg_eyebrow'         => 'Platz sichern',
			'reg_title'           => 'Anmeldung',
			'reg_intro'           => 'Tragen Sie sich ein — Sie erhalten eine Bestätigung per E-Mail.',
			'reg_name'            => 'Name',
			'reg_email'           => 'E-Mail',
			'reg_phone'           => 'Telefon (optional)',
			'reg_persons'         => 'Anzahl Personen',
			'reg_notes'           => 'Nachricht (optional)',
			'reg_consent'         => 'Ich bin mit der Speicherung meiner Angaben für diese Anmeldung einverstanden.',
			'reg_newsletter'      => 'Ich möchte mich ebenfalls für den Newsletter anmelden',
			'reg_website'         => 'Website',
			'reg_button'          => 'Platz reservieren',
			'reg_ok'              => 'Danke! Ihre Anmeldung ist eingegangen — die Bestätigung ist per E-Mail unterwegs.',
			'reg_ok_nomail'       => 'Ihre Anmeldung wurde gespeichert, aber die Bestätigungs-E-Mail konnte nicht versendet werden.',
			'reg_full'            => 'Der Termin ist ausgebucht.',
			'reg_already'         => 'Für diese E-Mail besteht bereits eine Anmeldung zu diesem Termin.',
			'reg_cancelled'       => 'Ihre Anmeldung wurde storniert.',
			'reg_error'           => 'Bitte prüfen Sie Ihre Angaben.',
			'reg_invalid'         => 'Der Link ist ungültig oder abgelaufen.',
			'reg_closed'          => 'Die Anmeldung für diesen Termin ist geschlossen.',
			'reg_rate'            => 'Zu viele Versuche. Bitte versuchen Sie es in ein paar Minuten erneut.',
			'reg_payment_unavailable' => 'Diese Veranstaltung ist kostenpflichtig, aber die Online-Zahlung ist noch nicht eingerichtet. Bitte wenden Sie sich an den Veranstalter.',
			'reg_pending_payment' => 'Ihre Anfrage wurde empfangen. Das Ticket wird nach Zahlungsbestätigung durch den Veranstalter versendet.',
			'reg_left'            => 'Noch %d Plätze frei',
			'reg_unlimited'       => 'Anmeldung geöffnet',
			'reg_free'            => 'Die Veranstaltung ist kostenlos',
			'reg_paid'            => 'Teilnahmegebühr: %s € pro Person',
			'reg_participants_title' => 'Teilnehmende und Partner',
			'reg_confirm_subject' => 'Anmeldung bestätigt / Eintrittskarte: {event}',
			'reg_confirm_body'    => "Hallo {name},\n\nIhre Anmeldung ist bestätigt für:\n{event}\nDatum: {date}\nUhrzeit: {time}\nOrt: {venue}\nPersonen: {persons}\nTicket-Nummer: {ticket}\n\n{cancel_note}\n{cancel_link}",
			'reg_cancel_note'     => 'Um Ihre Anmeldung zu stornieren:',
			'reg_admin_subject'   => 'Neue Anmeldung: {event}',
			'news_name_fallback'  => 'Hallo',
			'ticket_title'        => 'Anmeldung bestätigt / Eintrittskarte',
			'ticket_hello'        => 'Hallo {name}, Ihre Anmeldung ist bestätigt für:',
			'ticket_event'        => 'Veranstaltung',
			'ticket_date'         => 'Datum',
			'ticket_time'         => 'Uhrzeit',
			'ticket_venue'        => 'Ort',
			'ticket_persons'      => 'Personen',
			'ticket_number'       => 'Ticket-Nummer',
			'ticket_verify'       => 'Ticket prüfen',
			'ticket_info'         => 'Bitte präsentieren Sie dieses Ticket am Einlass.',
			'ticket_pdf'          => 'Das Ticket ist zusätzlich als PDF-Datei angehängt.',
			'ticket_cancel'       => 'Um Ihre Anmeldung zu stornieren',
		);
	}

	public function generate_ticket_number( $event_id ) {
		global $wpdb;
		$table = YENA_DB::registrations();
		$year  = gmdate( 'Y' );
		$like  = 'YE-' . $year . '-%';

		// Numbers must be unique across all events. Counting rows per event
		// produced duplicates when several language events shared a title.
		$max = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED))
				 FROM {$table}
				 WHERE ticket_number LIKE %s",
				$like
			)
		);

		$next = $max + 1;
		$ticket = 'YE-' . $year . '-' . str_pad( (string) $next, 4, '0', STR_PAD_LEFT );

		// Safety net: keep incrementing until the value is unused.
		while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE ticket_number = %s LIMIT 1", $ticket ) ) ) {
			$next++;
			$ticket = 'YE-' . $year . '-' . str_pad( (string) $next, 4, '0', STR_PAD_LEFT );
		}

		return $ticket;
	}
}
