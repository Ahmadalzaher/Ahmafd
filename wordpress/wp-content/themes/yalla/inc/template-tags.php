<?php
/**
 * Presentation helpers.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icon set.
 *
 * @param string $name Icon name.
 * @return string
 */
function yd_icon( $name ) {
	$icons = array(
		'play'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>',
		'arrow'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
		'mail'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
		'pin'       => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s7-6.1 7-11a7 7 0 1 0-14 0c0 4.9 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/></svg>',
		'calendar'  => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>',
		'clock'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M13.5 21v-7h2.4l.4-3h-2.8V9.2c0-.9.3-1.5 1.6-1.5H16.5V5.1C16.2 5.1 15.2 5 14 5c-2.4 0-4 1.5-4 4.1V11H7.5v3H10v7z"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M21.6 7.2c-.2-.9-.9-1.6-1.8-1.8C18.1 5 12 5 12 5s-6.1 0-7.8.4c-.9.2-1.6.9-1.8 1.8C2 8.9 2 12 2 12s0 3.1.4 4.8c.2.9.9 1.6 1.8 1.8C5.9 19 12 19 12 19s6.1 0 7.8-.4c.9-.2 1.6-.9 1.8-1.8.4-1.7.4-4.8.4-4.8s0-3.1-.4-4.8ZM10 15.5v-7l6 3.5z"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M17.5 3h3.2l-7 8 8.2 10h-6.4l-5-6.1-5.8 6.1H1.5l7.5-8.6L1.2 3h6.6l4.5 5.6zm-1.1 16.1h1.8L7.6 4.8H5.7z"/></svg>',
		'star'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.3 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8z"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Echo an icon.
 *
 * @param string $name Icon name.
 */
function yd_the_icon( $name ) {
	echo yd_icon( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Localised month names (short).
 *
 * @return array
 */
function yd_months() {
	$lang = yd_lang();

	$sets = array(
		'de' => array( 'JAN', 'FEB', 'MÄR', 'APR', 'MAI', 'JUN', 'JUL', 'AUG', 'SEP', 'OKT', 'NOV', 'DEZ' ),
		'en' => array( 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC' ),
		'ar' => array( 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ),
	);

	return isset( $sets[ $lang ] ) ? $sets[ $lang ] : $sets['de'];
}

/**
 * Localised weekday names (short).
 *
 * @return array
 */
function yd_weekdays() {
	$sets = array(
		'de' => array( 'So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa' ),
		'en' => array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ),
		'ar' => array( 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت' ),
	);

	$lang = yd_lang();
	return isset( $sets[ $lang ] ) ? $sets[ $lang ] : $sets['de'];
}

/**
 * Split a stored date into display parts.
 *
 * @param string $date Date in Y-m-d.
 * @return array
 */
function yd_date_parts( $date ) {
	if ( ! $date ) {
		return array(
			'day'     => '—',
			'month'   => '',
			'weekday' => '',
			'iso'     => '',
		);
	}

	$ts      = strtotime( $date );
	$months  = yd_months();
	$days    = yd_weekdays();
	$month   = (int) gmdate( 'n', $ts );
	$weekday = (int) gmdate( 'w', $ts );

	return array(
		'day'     => gmdate( 'd', $ts ),
		'month'   => $months[ $month - 1 ],
		'weekday' => $days[ $weekday ],
		'iso'     => gmdate( 'Y-m-d', $ts ),
	);
}

/**
 * Human readable date.
 *
 * @param string $date Date in Y-m-d.
 * @return string
 */
function yd_pretty_date( $date ) {
	if ( ! $date ) {
		return '';
	}
	$parts = yd_date_parts( $date );
	if ( 'ar' === yd_lang() ) {
		return $parts['weekday'] . '، ' . (int) $parts['day'] . '. ' . $parts['month'] . ' ' . gmdate( 'Y', strtotime( $date ) );
	}
	return $parts['weekday'] . ', ' . (int) $parts['day'] . '. ' . $parts['month'] . ' ' . gmdate( 'Y', strtotime( $date ) );
}

/**
 * Post image URL with fallbacks.
 *
 * @param WP_Post $post Post.
 * @param string  $size Image size.
 * @return string
 */
function yd_image( $post, $size = 'large' ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return yd_asset( 'img/hero-dabke.jpg' );
	}

	if ( has_post_thumbnail( $post ) ) {
		$url = get_the_post_thumbnail_url( $post, $size );
		if ( $url ) {
			return $url;
		}
	}

	$bundled = get_post_meta( $post->ID, '_yd_image', true );

	// Formats: the demo import assigned generic photos, so remap the known
	// defaults by kicker while still respecting a genuinely custom image.
	if ( 'yd_service' === $post->post_type ) {
		$generic = array( '', 'img/calligraphy.jpg', 'img/community-circle.jpg', 'img/stage-group.jpg' );

		if ( in_array( $bundled, $generic, true ) ) {
			return yd_asset( yd_service_image( $post ) );
		}
	}

	if ( $bundled && ! is_numeric( $bundled ) ) {
		if ( 0 === strpos( $bundled, 'http' ) ) {
			return $bundled;
		}
		return yd_asset( $bundled );
	}
	if ( is_numeric( $bundled ) && $bundled ) {
		$url = wp_get_attachment_image_url( (int) $bundled, $size );
		if ( $url ) {
			return $url;
		}
	}

	$fallbacks = array(
		'yd_event'       => 'img/hero-dabke.jpg',
		'yd_service'     => 'img/community-circle.jpg',
		'yd_team'        => 'img/portrait-ahmad.jpg',
		'yd_testimonial' => 'img/stage-group.jpg',
		'post'           => 'img/community-circle.jpg',
	);

	$type = get_post_type( $post );
	if ( isset( $fallbacks[ $type ] ) ) {
		return yd_asset( $fallbacks[ $type ] );
	}

	return yd_asset( 'img/stage-group.jpg' );
}

/**
 * Bundled image path for a format, chosen from its kicker / title.
 *
 * The demo import used to assign the same two photos to every language, which
 * put calligraphy photos on music pages. Matching on the kicker (and the title
 * as a fallback, so the Arabic titles work too) keeps the right photo with the
 * right format without needing a database migration.
 *
 * @param string $hint Kicker, title or slug.
 * @return string Relative path inside /assets.
 */
function yd_service_image_path( $hint ) {
	$key = strtolower( (string) $hint );

	if ( preg_match( '/dabke|dance|tanz|folk|رقص|دبك|دبكه/u', $key ) ) {
		return 'img/community-circle.jpg';
	}

	if ( preg_match( '/music|musik|maqam|oud|موسيق/u', $key ) ) {
		return 'img/music.jpg';
	}

	if ( preg_match( '/calligraph|kalligraf|schrift|script|خط/u', $key ) ) {
		return 'img/calligraphy.jpg';
	}

	if ( preg_match( '/literat|hakawati|erzähl|erzaehl|story|أدب|حكوات/u', $key ) ) {
		return 'img/violinist.jpg';
	}

	return 'img/community-circle.jpg';
}

/**
 * Format image for a service post.
 *
 * @param WP_Post|int $post Service post.
 * @return string Relative path inside /assets.
 */
function yd_service_image( $post ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 'img/community-circle.jpg';
	}

	$kicker = get_post_meta( $post->ID, '_yd_kicker', true );

	return yd_service_image_path( $kicker . ' ' . $post->post_name . ' ' . $post->post_title );
}

/**
 * WebP sibling of a bundled JPEG/PNG asset, when one exists.
 *
 * @param string $url Asset URL.
 * @return string Empty when no WebP variant is available.
 */
function yd_webp_url( $url ) {
	if ( ! $url || ! preg_match( '/\.(jpe?g|png)$/i', $url ) ) {
		return '';
	}

	$webp     = preg_replace( '/\.(jpe?g|png)$/i', '.webp', $url );
	$relative = str_replace( YALLA_URI . '/assets/', '', $webp );

	if ( $relative !== $webp && file_exists( YALLA_DIR . '/assets/' . $relative ) ) {
		return $webp;
	}

	return '';
}

/**
 * Intrinsic dimensions of a bundled asset.
 *
 * @param string $url Asset URL.
 * @return array{0:int,1:int} Width and height.
 */
function yd_image_dimensions( $url ) {
	$fallback = array( 1280, 853 );

	if ( ! $url ) {
		return $fallback;
	}

	$relative = str_replace( YALLA_URI . '/assets/', '', $url );
	$file     = YALLA_DIR . '/assets/' . $relative;

	if ( $relative !== $url && file_exists( $file ) ) {
		$size = @getimagesize( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $size ) {
			return array( (int) $size[0], (int) $size[1] );
		}
	}

	return $fallback;
}

/**
 * Hero image for the homepage.
 *
 * @return string
 */
function yd_hero_image() {
	$custom = get_theme_mod( 'yd_hero_image' );
	if ( $custom ) {
		return $custom;
	}
	return yd_asset( 'img/hero-dabke.jpg' );
}

/**
 * Extract a YouTube ID from a URL.
 *
 * @param string $url URL.
 * @return string
 */
function yd_youtube_id( $url ) {
	if ( ! $url ) {
		return '';
	}
	if ( preg_match( '#(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{6,})#', $url, $matches ) ) {
		return $matches[1];
	}
	return '';
}

/**
 * Event row markup.
 *
 * @param WP_Post $post    Event post.
 * @param bool    $is_next Highlight as the next event.
 */
function yd_event_row( $post, $is_next = false ) {
	$date   = get_post_meta( $post->ID, '_yd_date', true );
	$time   = get_post_meta( $post->ID, '_yd_time', true );
	$end    = get_post_meta( $post->ID, '_yd_end_time', true );
	$venue  = get_post_meta( $post->ID, '_yd_venue', true );
	$city   = get_post_meta( $post->ID, '_yd_city', true );
	$status = get_post_meta( $post->ID, '_yd_status', true );
	$parts  = yd_date_parts( $date );
	$today  = current_time( 'Y-m-d' );

	$time_label = $time;
	if ( $time && $end ) {
		$time_label = $time . ' – ' . $end;
	}

	$is_past = $date && $date < $today;
	?>
	<article class="event-row">
		<div class="event-date">
			<span class="event-date__day"><?php echo esc_html( $parts['day'] ); ?></span>
			<span>
				<span class="event-date__month"><?php echo esc_html( $parts['month'] ); ?></span>
				<span class="event-date__wd"><?php echo esc_html( $parts['weekday'] ); ?></span>
			</span>
		</div>

		<div>
			<h3 class="event-title">
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
				<?php if ( $is_next ) : ?>
					<span class="event-tag"><?php echo esc_html( yd_t( 'next' ) ); ?></span>
				<?php elseif ( $is_past || 'soldout' === $status ) : ?>
					<span class="event-tag event-tag--muted"><?php echo esc_html( yd_t( 'past' ) ); ?></span>
				<?php endif; ?>
			</h3>
			<div class="event-meta">
				<?php
				$yd_meta_src = has_excerpt( $post ) ? get_the_excerpt( $post ) : get_post_field( 'post_content', $post->ID );
				$yd_meta_src = preg_replace( '/<(br|\/p|\/div|\/li|\/h[1-6])\s*\/?>/i', ' ', (string) $yd_meta_src );
				$yd_meta     = wp_trim_words( wp_strip_all_tags( $yd_meta_src ), 14, '…' );
				?>
				<span><?php echo esc_html( $yd_meta ); ?></span>
			</div>
		</div>

		<div class="event-venue">
			<?php echo esc_html( $venue ); ?>
			<?php if ( $city && false === mb_stripos( $venue, $city ) ) : ?>
				<span><?php echo esc_html( $city ); ?></span>
			<?php endif; ?>
		</div>

		<div class="event-time"><?php echo esc_html( $time_label ); ?></div>

		<div class="event-action">
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( yd_t( 'details' ) ); ?> &rarr;</a>
		</div>
	</article>
	<?php
}

/**
 * Compact event card used in archives and related listings.
 *
 * @param WP_Post $post Event post.
 */
function yd_event_card( $post ) {
	$date  = get_post_meta( $post->ID, '_yd_date', true );
	$time  = get_post_meta( $post->ID, '_yd_time', true );
	$end   = get_post_meta( $post->ID, '_yd_end_time', true );
	$venue = get_post_meta( $post->ID, '_yd_venue', true );
	$parts = yd_date_parts( $date );

	$time_label = $time && $end ? $time . ' – ' . $end : $time;
	?>
	<article class="event-card">
		<div class="event-date">
			<span class="event-date__day"><?php echo esc_html( $parts['day'] ); ?></span>
			<span>
				<span class="event-date__month"><?php echo esc_html( $parts['month'] ); ?></span>
				<span class="event-date__wd"><?php echo esc_html( $parts['weekday'] ); ?></span>
			</span>
		</div>
		<h3 class="event-title"><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
		<div class="event-meta"><?php echo esc_html( $venue ); ?><?php echo $time_label ? ' · ' . esc_html( $time_label ) : ''; ?></div>
		<div class="card__foot">
			<a class="link-arrow" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( yd_t( 'details' ) ); ?></a>
		</div>
	</article>
	<?php
}

/**
 * Navigation items for the current language, built from the page tree.
 *
 * @return array
 */
function yd_nav_items() {
	$lang  = yd_lang();
	$map   = yd_page_map();
	$order = array( 'festival', 'program', 'events', 'articles', 'press', 'about', 'contact' );
	$items = array();

	foreach ( $order as $group ) {
		if ( ! isset( $map[ $lang ][ $group ] ) ) {
			continue;
		}
		$id = $map[ $lang ][ $group ];
		if ( get_post_meta( $id, '_yd_nav_hidden', true ) ) {
			continue;
		}
		$items[] = array(
			'id'    => $id,
			'title' => get_the_title( $id ),
			'url'   => get_permalink( $id ),
			'group' => $group,
		);
	}

	if ( empty( $items ) ) {
		// Bootstrap fallback before the demo content is seeded.
		$fallback = array(
			'festival' => yd_t( 'nav_festival' ),
			'program'  => yd_t( 'nav_program' ),
			'events'   => yd_t( 'nav_events' ),
			'articles' => yd_t( 'nav_articles' ),
			'press'    => yd_t( 'nav_press' ),
			'about'    => yd_t( 'nav_about' ),
			'contact'  => yd_t( 'nav_contact' ),
		);
		foreach ( $fallback as $group => $title ) {
			$items[] = array(
				'id'    => 0,
				'title' => $title,
				'url'   => yd_link( $group ),
				'group' => $group,
			);
		}
	}

	return $items;
}

/**
 * Brand block (logo + wordmark).
 *
 * @param bool $footer Use the light footer variant.
 */
function yd_brand( $footer = false ) {
	$logo_id  = get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : yd_asset( 'img/logo-mark.png' );
	$name     = get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' );
	?>
	<a class="brand" href="<?php echo esc_url( yd_home_url() ); ?>" rel="home">
		<picture>
			<source srcset="<?php echo esc_url( yd_asset( 'img/logo-mark.webp' ) ); ?>" type="image/webp" />
			<img class="brand__mark" src="<?php echo esc_url( yd_asset( 'img/logo-mark.webp' ) ); ?>" alt="<?php echo esc_attr( $name ); ?>" width="122" height="224" />
		</picture>
		<span class="brand__text">
			<span class="brand__name"><?php echo esc_html( $name ); ?></span>
			<span class="brand__sub"><?php echo esc_html( $footer ? yd_t( 'footer_made_with' ) : yd_t( 'brand_sub' ) ); ?></span>
		</span>
	</a>
	<?php
}

/**
 * Social profiles defined in the customizer.
 *
 * @return array
 */
function yd_socials() {
	$defaults = array(
		'instagram' => 'https://www.instagram.com/arap.culture/',
		'facebook'  => 'https://www.facebook.com/Arap.cultur',
		'youtube'   => 'https://www.youtube.com/channel/UC8QljYPQaRs5YrBjsurD_3g',
	);

	$out = array();
	foreach ( $defaults as $key => $default ) {
		$url = get_theme_mod( 'yd_social_' . $key, $default );
		if ( $url ) {
			$out[ $key ] = $url;
		}
	}
	return $out;
}

/**
 * Contact details.
 *
 * @param string $key email|location.
 * @return string
 */
function yd_contact( $key = 'email' ) {
	$defaults = array(
		'email'    => 'info@arab-kulturfestival.de',
		'phone'    => '+49 178 1116220',
		'street'   => 'Julius-Vosseler-Straße 106b',
		'postal'   => '22527',
		'city'     => 'Hamburg',
		'location' => 'Hamburg, Deutschland',
	);
	$value = get_theme_mod( 'yd_contact_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return $value;
}

/**
 * Section heading.
 *
 * @param string $eyebrow Eyebrow text.
 * @param string $title   Title (allows em).
 * @param string $text    Optional lede.
 * @param string $cta     Optional CTA HTML.
 */
function yd_section_head( $eyebrow, $title, $text = '', $cta = '' ) {
	?>
	<div class="section-head reveal">
		<div class="section-head__text">
			<?php if ( $eyebrow ) : ?>
				<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h2><?php echo wp_kses( $title, array( 'em' => array(), 'strong' => array(), 'br' => array() ) ); ?></h2>
			<?php if ( $text ) : ?>
				<p class="lede"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $cta ) : ?>
			<div class="section-head__cta"><?php echo wp_kses_post( $cta ); ?></div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Pagination for archives and article listings.
 */
function yd_pagination() {
	$links = paginate_links(
		array(
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'type'      => 'array',
		)
	);

	if ( empty( $links ) ) {
		return;
	}

	echo '<nav class="pagination" aria-label="' . esc_attr__( 'Seiten', 'yalla' ) . '">';
	foreach ( $links as $link ) {
		echo wp_kses_post( $link );
	}
	echo '</nav>';
}

/**
 * Reading time estimate.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function yd_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	$words   = str_word_count( $content );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Article card markup.
 *
 * @param WP_Post $post Post.
 */
function yd_article_card( $post ) {
	?>
	<article class="article-card">
		<a class="article-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
			<img src="<?php echo esc_url( yd_image( $post, 'yd-card' ) ); ?>" alt="<?php echo esc_attr( get_the_title( $post ) ); ?>" width="720" height="480" loading="lazy" />
		</a>
		<div class="article-card__body">
			<p class="article-card__meta"><?php echo esc_html( yd_pretty_date( get_the_date( 'Y-m-d', $post ) ) ); ?></p>
			<h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post ) ), 24 ) ); ?></p>
			<div class="article-card__foot">
				<a class="link-arrow" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( yd_t( 'read_article' ) ); ?></a>
			</div>
		</div>
	</article>
	<?php
}
