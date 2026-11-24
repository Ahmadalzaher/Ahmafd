
/* ===== YALLA-TOOLS-START ===== */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['yalla-import'] ) ) {
		return;
	}
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );
	$aid  = (int) $_GET['yalla-import'];
	$file = get_attached_file( $aid );
	if ( ! $file || ! file_exists( $file ) ) {
		echo 'file missing';
		exit;
	}
	$data = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $data ) || empty( $data['langs'] ) ) {
		echo 'json invalid';
		exit;
	}
	$ids = array();
	foreach ( $data['langs'] as $lang => $row ) {
		$post     = array(
			'post_type'    => 'yd_event',
			'post_status'  => 'publish',
			'post_title'   => $row['title'],
			'post_name'    => $row['slug'],
			'post_excerpt' => $row['excerpt'],
			'post_content' => $row['content'],
		);
		$existing = get_page_by_path( $row['slug'], OBJECT, 'yd_event' );
		if ( $existing ) {
			$post['ID'] = $existing->ID;
			$id         = wp_update_post( wp_slash( $post ), true );
		} else {
			$id = wp_insert_post( wp_slash( $post ), true );
		}
		if ( is_wp_error( $id ) || ! $id ) {
			echo 'error ' . $lang . "\n";
			continue;
		}
		update_post_meta( $id, '_yd_date', $data['date'] );
		update_post_meta( $id, '_yd_time', $data['time'] );
		update_post_meta( $id, '_yd_end_time', $data['end'] );
		update_post_meta( $id, '_yd_venue', $data['venue'] );
		update_post_meta( $id, '_yd_city', $data['city'] );
		update_post_meta( $id, '_yd_address', $data['address'] );
		update_post_meta( $id, '_yd_price', '' );
		update_post_meta( $id, '_yd_cta', '#yena-registration' );
		update_post_meta( $id, '_yd_cta_label', $row['cta_label'] );
		update_post_meta( $id, '_yd_status', 'upcoming' );
		update_post_meta( $id, '_yd_featured', '0' );
		update_post_meta( $id, 'yena_price_type', 'free' );
		update_post_meta( $id, 'yena_event_price', 0 );
		update_post_meta( $id, 'yena_capacity', 0 );
		update_post_meta( $id, 'yena_reg_open', '1' );
		update_post_meta( $id, 'yena_poster_image', $data['poster'] );
		update_post_meta( $id, 'yena_participant_logos', $data['logos'] );
		update_post_meta( $id, 'rank_math_title', $row['seo_title'] );
		update_post_meta( $id, 'rank_math_description', $row['seo_desc'] );
		update_post_meta( $id, 'rank_math_focus_keyword', $row['focus'] );
		update_post_meta( $id, 'rank_math_facebook_image', $data['poster'] );
		update_post_meta( $id, 'rank_math_twitter_image', $data['poster'] );
		set_post_thumbnail( $id, (int) $data['thumb'] );
		wp_set_object_terms( $id, $lang, 'yd_lang' );
		$ids[ $lang ] = (int) $id;
	}
	foreach ( $ids as $one ) {
		foreach ( $ids as $code => $other ) {
			update_post_meta( $one, '_yd_translation_' . $code, $other );
		}
	}
	echo 'ids: ' . wp_json_encode( $ids ) . "\n";
	foreach ( $ids as $code => $one ) {
		echo $code . ': ' . get_permalink( $one ) . "\n";
	}
	exit;
}, 2 );
/* ===== YALLA-TOOLS-END ===== */
