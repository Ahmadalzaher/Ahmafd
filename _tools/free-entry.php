<?php
/**
 * Free admission: drop the per-session fee, keep the course free and invite
 * donations. Runs both the old and the new dash variants so it is idempotent.
 *
 * Run: php wp-cli.phar eval-file _tools/free-entry.php
 */

$replacements = array(
	// Event body.
	'<strong>Die Teilnahme ist kostenfrei.</strong> Kommt einfach vorbei. Rückfragen an '
		=> '<strong>Die Teilnahme ist kostenfrei.</strong> Kommt einfach vorbei — über eine Spende für unsere Arbeit freuen wir uns. Rückfragen an ',
	'<strong>Participation is free.</strong> Just come along. Questions: '
		=> '<strong>Participation is free.</strong> Just come along — donations for our work are very welcome. Questions: ',
	'<strong>المشاركة مجانية.</strong> تعال فقط. للاستفسار: '
		=> '<strong>المشاركة مجانية.</strong> تعال فقط — والتبرعات لدعم عملنا مرحّب بها. للاستفسار: ',

	// Events page lede.
	'Die Teilnahme an vielen Formaten ist kostenfrei - komm einfach vorbei.'
		=> 'Die Teilnahme an vielen Formaten ist kostenfrei — über eine Spende freuen wir uns.',
	'Die Teilnahme an vielen Formaten ist kostenfrei — komm einfach vorbei.'
		=> 'Die Teilnahme an vielen Formaten ist kostenfrei — über eine Spende freuen wir uns.',
	'Many formats are free - just come along.'
		=> 'Many formats are free — donations are welcome.',
	'Many formats are free — just come along.'
		=> 'Many formats are free — donations are welcome.',
	'المشاركة في أنماط كثيرة مجانية — تعال فقط.'
		=> 'المشاركة في أنماط كثيرة مجانية — والتبرعات مرحّب بها.',

	// Programme page, dabke paragraph.
	'Vorkenntnisse sind nicht nötig.</p>'
		=> 'Vorkenntnisse sind nicht nötig. Die offenen Kurse sind kostenfrei, über eine Spende für unsere Arbeit freuen wir uns.</p>',
	'whoever holds the line is part of the circle — no experience required.</p>'
		=> 'whoever holds the line is part of the circle — no experience required. The open classes are free of charge; donations for our work are welcome.</p>',
	'ولا حاجة إلى خبرة سابقة.</p>'
		=> 'ولا حاجة إلى خبرة سابقة. الدورات المفتوحة مجانية، والتبرعات لدعم عملنا مرحّب بها.</p>',
);

$prices = array(
	'de' => array( 'Eintritt frei', 'Eintritt frei · Spenden willkommen' ),
	'en' => array( 'Free entry', 'Free entry · donations welcome' ),
	'ar' => array( 'الدخول مجاني', 'الدخول مجاني · التبرعات مرحّب بها' ),
);

$types = array( 'page', 'post', 'yd_event' );
$count = 0;

foreach ( $types as $type ) {
	foreach ( get_posts( array( 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ) ) as $post ) {
		$content = $post->post_content;
		$new     = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );

		if ( $new !== $content ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_content' => $new ) );
			echo "content $type {$post->ID} {$post->post_name}\n";
			++$count;
		}

		if ( 'yd_event' !== $type ) {
			continue;
		}

		$lang     = 'de';
		$terms    = wp_get_post_terms( $post->ID, 'yd_lang', array( 'fields' => 'slugs' ) );

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) && isset( $prices[ $terms[0] ] ) ) {
			$lang = $terms[0];
		}

		$current = get_post_meta( $post->ID, '_yd_price', true );

		if ( '' === $current || in_array( $current, array( $prices[ $lang ][0], $prices[ $lang ][1] ), true ) ) {
			update_post_meta( $post->ID, '_yd_price', $prices[ $lang ][1] );
			echo "price   yd_event {$post->ID} -> {$prices[ $lang ][1]}\n";
			++$count;
		} else {
			echo "KEEP    yd_event {$post->ID} price=\"$current\"\n";
		}
	}
}

echo "updated $count object(s)\n";
