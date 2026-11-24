
/* ===== YALLA-TOOLS-START ===== */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['yalla-import'] ) ) {
		return;
	}
	nocache_headers();
	header( 'Content-Type: text/plain; charset=utf-8' );

	$date    = '2026-12-04';
	$time    = '18:00';
	$end     = '21:00';
	$venue   = 'Gemeinde St. Markus';
	$city    = 'Hamburg';
	$address = 'Heider Straße 1, Hoheluft-Ost';
	$thumb   = 2836;
	$poster  = 'https://arab-kulturfestival.de/wp-content/uploads/2026/10/festival-19.webp';
	$logos   = array(
		array(
			'url'  => 'https://arab-kulturfestival.de/wp-content/uploads/2026/11/logo-small.png',
			'name' => 'Oriental Band Hamburg',
			'link' => '',
		),
	);

	$langs = array();

	$langs['de'] = array(
		'title'     => 'Einladung zur Jahresabschlussfeier 2026',
		'slug'      => 'jahresabschlussfeier-2026',
		'excerpt'   => 'Gemeinsam auf ein ereignisreiches Jahr zurückblicken – mit Live-Musik, Bastelangeboten und internationalem Mitbring-Buffet.',
		'cta'       => 'Anmelden',
		'seo_title' => 'Jahresabschlussfeier 2026 – 4. Dezember in Hamburg | Hamburger* mit Herz e.V.',
		'seo_desc'  => 'Einladung zur Jahresabschlussfeier 2026 am Freitag, 4. Dezember, 18:00–21:00 Uhr in der Gemeinde St. Markus, Heider Straße 1, Hamburg. Live-Musik, Bastelangebote und internationales Mitbring-Buffet.',
		'focus'     => 'Jahresabschlussfeier 2026 Hamburg',
		'content'   => '<p>Das Jahr neigt sich dem Ende zu – und wir möchten es gemeinsam mit Dir feiern!</p>
<p><strong>Freitag, 04. Dezember 2026 | 18:00–21:00 Uhr</strong></p>
<p><strong>Gemeinde St. Markus</strong><br />
Heider Straße 1, Hoheluft-Ost</p>
<p>Wir laden Dich herzlich ein, gemeinsam auf ein ereignisreiches Jahr zurückzublicken und es in festlicher Atmosphäre ausklingen zu lassen.</p>
<p>Freu Dich auf einen schönen Abend mit:</p>
<ul>
<li>Live-Musik von der Oriental Band Hamburg</li>
<li>Kreativen Bastelangeboten</li>
<li>Guten Gesprächen und netten Begegnungen</li>
<li>Einem internationalen Mitbring-Buffet</li>
</ul>
<p>Damit unser Buffet so vielfältig wird wie unser Miteinander, bring gerne eine kleine Köstlichkeit aus Deiner Heimat oder Deiner Lieblingsküche mit.</p>
<p>Für Getränke ist gesorgt.</p>
<p>Bitte melde Dich über den Anmeldebutton in der Einladung an.</p>
<p>Wir freuen uns auf Dich!</p>
<p><strong>Dein Vorstand und Team von Hamburger* mit Herz e.V.</strong></p>',
	);

	$langs['ar'] = array(
		'title'     => 'دعوة للاحتفال بنهاية العام 2026',
		'slug'      => 'حفل-نهاية-العام-2026',
		'excerpt'   => 'أمسية احتفالية نستعيد فيها معاً ذكريات العام الحافل — مع موسيقى حية وأنشطة إبداعية وبوفيه عالمي.',
		'cta'       => 'التسجيل',
		'seo_title' => 'دعوة للاحتفال بنهاية العام 2026 — 4 ديسمبر في هامبورغ | Hamburger* mit Herz e.V.',
		'seo_desc'  => 'دعوة للاحتفال بنهاية العام 2026 يوم الجمعة 4 ديسمبر من الساعة 18:00 حتى 21:00 في كنيسة سانت ماركوس، Heider Straße 1 في هامبورغ. موسيقى حية وأنشطة إبداعية وبوفيه عالمي.',
		'focus'     => 'حفل نهاية العام 2026 هامبورغ',
		'content'   => '<p>مع اقتراب نهاية العام، يسعدنا أن نحتفل معاً بهذه المناسبة الجميلة!</p>
<p><strong>الجمعة، 4 ديسمبر/كانون الأول 2026 | من الساعة 18:00 حتى 21:00</strong></p>
<p><strong>كنيسة سانت ماركوس – Gemeinde St. Markus</strong><br />
Heider Straße 1, Hoheluft-Ost</p>
<p>ندعوك لقضاء أمسية مميزة نستعيد فيها معاً أحداث عام حافل بالتجارب والذكريات، ونودّعه في أجواء احتفالية دافئة.</p>
<p>ينتظرك برنامج متنوع يتضمن:</p>
<ul>
<li>موسيقى حية مع فرقة Oriental Band Hamburg</li>
<li>أنشطة إبداعية وأعمال يدوية</li>
<li>لقاءات جميلة وأحاديث ودية</li>
<li>بوفيه عالمي من أطباق ومأكولات متنوعة</li>
</ul>
<p>ولكي تعكس مائدتنا تنوع ثقافاتنا، يسعدنا أن تحضر معك طبقاً صغيراً من مطبخ بلدك أو من أطباقك المفضلة.</p>
<p>المشروبات متوفرة من طرفنا.</p>
<p>يرجى التسجيل مسبقاً عبر زر التسجيل الموجود في الدعوة.</p>
<p>نتطلع إلى لقائك والاحتفال معك!</p>
<p><strong>مجلس الإدارة وفريق العمل في جمعية Hamburger* mit Herz e.V.</strong></p>',
	);

	$langs['en'] = array(
		'title'     => 'Invitation to the 2026 Year-End Celebration',
		'slug'      => 'year-end-celebration-2026',
		'excerpt'   => 'Looking back on an eventful year together – with live music, creative crafts and an international potluck buffet.',
		'cta'       => 'Register',
		'seo_title' => '2026 Year-End Celebration – 4 December in Hamburg | Hamburger* mit Herz e.V.',
		'seo_desc'  => 'Invitation to the 2026 Year-End Celebration on Friday, 4 December, 6:00–9:00 PM at Gemeinde St. Markus, Heider Straße 1, Hamburg. Live music, arts and crafts, and an international potluck buffet.',
		'focus'     => 'year-end celebration 2026 Hamburg',
		'content'   => '<p>As the year draws to a close, we would love to celebrate it together with you!</p>
<p><strong>Friday, 4 December 2026 | 6:00 PM – 9:00 PM</strong></p>
<p><strong>Gemeinde St. Markus</strong><br />
Heider Straße 1, Hoheluft-Ost</p>
<p>We warmly invite you to join us for a special evening as we look back on an eventful year and celebrate its end in a festive and welcoming atmosphere.</p>
<p>Enjoy an evening featuring:</p>
<ul>
<li>Live music by Oriental Band Hamburg</li>
<li>Creative arts and crafts activities</li>
<li>Great conversations and friendly encounters</li>
<li>An international potluck buffet</li>
</ul>
<p>To make our buffet as diverse as our community, feel free to bring a small dish from your home country or one of your favourite cuisines.</p>
<p>Drinks will be provided.</p>
<p>Please register in advance using the registration button in the invitation.</p>
<p>We look forward to celebrating with you!</p>
<p><strong>Your Board and Team of Hamburger* mit Herz e.V.</strong></p>',
	);

	$ids = array();

	foreach ( $langs as $lang => $row ) {
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
			echo 'error ' . $lang;
			continue;
		}

		update_post_meta( $id, '_yd_date', $date );
		update_post_meta( $id, '_yd_time', $time );
		update_post_meta( $id, '_yd_end_time', $end );
		update_post_meta( $id, '_yd_venue', $venue );
		update_post_meta( $id, '_yd_city', $city );
		update_post_meta( $id, '_yd_address', $address );
		update_post_meta( $id, '_yd_price', '' );
		update_post_meta( $id, '_yd_cta', '#yena-registration' );
		update_post_meta( $id, '_yd_cta_label', $row['cta'] );
		update_post_meta( $id, '_yd_status', 'upcoming' );
		update_post_meta( $id, '_yd_featured', '0' );
		update_post_meta( $id, 'yena_price_type', 'free' );
		update_post_meta( $id, 'yena_event_price', 0 );
		update_post_meta( $id, 'yena_capacity', 0 );
		update_post_meta( $id, 'yena_reg_open', '1' );
		update_post_meta( $id, 'yena_poster_image', $poster );
		update_post_meta( $id, 'yena_participant_logos', $logos );
		update_post_meta( $id, 'rank_math_title', $row['seo_title'] );
		update_post_meta( $id, 'rank_math_description', $row['seo_desc'] );
		update_post_meta( $id, 'rank_math_focus_keyword', $row['focus'] );
		update_post_meta( $id, 'rank_math_facebook_image', $poster );
		update_post_meta( $id, 'rank_math_twitter_image', $poster );
		set_post_thumbnail( $id, $thumb );
		wp_set_object_terms( $id, $lang, 'yd_lang' );

		$ids[ $lang ] = (int) $id;
	}

	foreach ( $ids as $one ) {
		foreach ( $ids as $code => $other ) {
			update_post_meta( $one, '_yd_translation_' . $code, $other );
		}
	}

	echo 'ids: ' . wp_json_encode( $ids ) . ' | ';

	foreach ( $ids as $code => $one ) {
		echo $code . ' ' . get_permalink( $one ) . ' | ';
	}

	exit;
}, 2 );
/* ===== YALLA-TOOLS-END ===== */
