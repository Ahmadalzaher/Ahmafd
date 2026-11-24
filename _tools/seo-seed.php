<?php
/**
 * Seed Rank Math titles, descriptions and focus keywords for all three
 * languages, link the translations and tidy the event list.
 *
 * Run: php wp-cli.phar eval-file _tools/seo-seed.php
 */

$map = yd_page_map();

/* ------------------------------------------------------- translations -- */

$post_groups = array(
	'article-dabke'      => array( 145, 146, 147 ),
	'article-exile'      => array( 148, 149, 150 ),
	'article-calligraphy' => array( 151, 152, 153 ),
	'article-music'      => array( 154, 155, 156 ),
);

$event_groups = array(
	'event-2026-11-05' => array( 94, 95, 96 ),
	'event-2026-11-19' => array( 97, 98, 99 ),
	'event-2026-12-03' => array( 100, 101, 102 ),
	'event-2026-12-17' => array( 103, 104, 105 ),
);

foreach ( array_merge( $post_groups, $event_groups ) as $group => $ids ) {
	$langs = array( 'de', 'en', 'ar' );

	foreach ( $ids as $index => $id ) {
		update_post_meta( $id, '_yd_group', $group );

		foreach ( $langs as $lang_index => $lang ) {
			update_post_meta( $id, '_yd_translation_' . $lang, $ids[ $lang_index ] );
		}
	}
}

/* Two seeded dates lie in the past and were never part of the agreed list. */
foreach ( array( 106, 107, 108, 109, 110, 111 ) as $id ) {
	wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
}

/* -------------------------------------------------------------- pages -- */

$pages = array(
	'home'     => array(
		'de' => array(
			'title'  => 'Arabisches Kulturfestival Hamburg – Termine & Dabke',
			'desc'   => 'Das Arab Kulturfestival bringt Dabke, arabische Musik, Kalligrafie und Literatur nach Hamburg. Alle Termine, das Programm und Infos zum Mitmachen.',
			'focus'  => 'Arabisches Kulturfestival Hamburg',
		),
		'en' => array(
			'title'  => 'Arab Cultural Festival Hamburg – Events & Dabke',
			'desc'   => 'The Arab Cultural Festival brings dabke, Arabic music, calligraphy and literature to Hamburg. See the dates, the programme and how to join in.',
			'focus'  => 'Arab Cultural Festival Hamburg',
		),
		'ar' => array(
			'title'  => 'مهرجان الثقافة العربية في هامبورغ – فعاليات ودبكة',
			'desc'   => 'يجمع مهرجان الثقافة العربية في هامبورغ الدبكة والموسيقى والخط العربي والأدب. تعرّف على المواعيد والبرنامج وكيف تشارك.',
			'focus'  => 'مهرجان الثقافة العربية في هامبورغ',
		),
	),
	'festival' => array(
		'de' => array(
			'title'  => 'Festival in Hamburg: Arabische Kultur erleben',
			'desc'   => 'Musik, Dabke, Kalligrafie und Literatur: Das Arab Kulturfestival zeigt die Vielfalt arabischer Kultur in Hamburg – interkulturell und nahbar.',
			'focus'  => 'Interkulturelles Festival Hamburg',
		),
		'en' => array(
			'title'  => 'Intercultural Festival Hamburg: Arab Culture',
			'desc'   => 'Music, dabke, calligraphy and literature: the Arab Cultural Festival shows the diversity of Arab culture in Hamburg – open and intercultural.',
			'focus'  => 'Intercultural Festival Hamburg',
		),
		'ar' => array(
			'title'  => 'مهرجان ثقافي في هامبورغ: الثقافة العربية',
			'desc'   => 'موسيقى ودبكة وخط عربي وأدب: يقدّم مهرجان الثقافة العربية تنوّع الثقافة العربية في هامبورغ بروح منفتحة وتفاعلية.',
			'focus'  => 'مهرجان ثقافي في هامبورغ',
		),
	),
	'program'  => array(
		'de' => array(
			'title'  => 'Dabke Kurs Hamburg & Festivalprogramm',
			'desc'   => 'Dabke lernen in Hamburg: offene Kurse, Musik und Mitmachangebote. Hier findest du das Programm des Arab Kulturfestivals und alle Termine.',
			'focus'  => 'Dabke Kurs Hamburg',
		),
		'en' => array(
			'title'  => 'Dabke Classes Hamburg & Festival Programme',
			'desc'   => 'Learn dabke in Hamburg: open classes, music and hands-on workshops. Find the Arab Cultural Festival programme and all upcoming dates.',
			'focus'  => 'Dabke Classes Hamburg',
		),
		'ar' => array(
			'title'  => 'دورة دبكة في هامبورغ وبرنامج المهرجان',
			'desc'   => 'تعلّم الدبكة في هامبورغ: دورات مفتوحة وموسيقى وفعاليات تفاعلية. اكتشف برنامج مهرجان الثقافة العربية وجميع المواعيد.',
			'focus'  => 'دورة دبكة في هامبورغ',
		),
	),
	'events'   => array(
		'de' => array(
			'title'  => 'Arabische Veranstaltungen Hamburg: Termine 2026',
			'desc'   => 'Alle Termine des Arab Kulturfestivals in Hamburg: Dabke-Kurse, Konzerte und Begegnungen. Datum, Uhrzeit und Ort auf einen Blick.',
			'focus'  => 'Arabische Veranstaltungen Hamburg',
		),
		'en' => array(
			'title'  => 'Arab Events Hamburg: Dates 2026',
			'desc'   => 'All Arab Cultural Festival dates in Hamburg: dabke classes, concerts and community meetups. Date, time and venue at a glance.',
			'focus'  => 'Arab Events Hamburg',
		),
		'ar' => array(
			'title'  => 'فعاليات عربية في هامبورغ: مواعيد 2026',
			'desc'   => 'جميع مواعيد مهرجان الثقافة العربية في هامبورغ: دورات دبكة وحفلات ولقاءات. التاريخ والوقت والمكان في نظرة واحدة.',
			'focus'  => 'فعاليات عربية في هامبورغ',
		),
	),
	'articles' => array(
		'de' => array(
			'title'  => 'Arabische Musik & Kultur: Artikel und Hintergründe',
			'desc'   => 'Geschichten über Dabke, arabische Musik, Kalligrafie und Kultur im Exil – gelesen und geschrieben von und mit der Community in Hamburg.',
			'focus'  => 'Arabische Musik Hamburg',
		),
		'en' => array(
			'title'  => 'Arabic Music & Culture: Articles and Stories',
			'desc'   => 'Stories about dabke, Arabic music, calligraphy and culture in exile – read and written by and with the community in Hamburg.',
			'focus'  => 'Arabic Music Hamburg',
		),
		'ar' => array(
			'title'  => 'موسيقى عربية وثقافة: مقالات وحكايات',
			'desc'   => 'حكايات عن الدبكة والموسيقى العربية والخط العربي والثقافة في المنفى — بقلم المجتمع وفي هامبورغ.',
			'focus'  => 'موسيقى عربية في هامبورغ',
		),
	),
	'about'    => array(
		'de' => array(
			'title'  => 'Arabische Kultur Hamburg: Über uns',
			'desc'   => 'Wer hinter dem Arab Kulturfestival steht: Ahmad AL Zaher und Arap Culture fördern arabische Kultur, Dabke und interkulturellen Austausch in Hamburg.',
			'focus'  => 'Arabische Kultur Hamburg',
		),
		'en' => array(
			'title'  => 'Arab Culture Hamburg: About us',
			'desc'   => 'Who is behind the Arab Cultural Festival: Ahmad AL Zaher and Arap Culture promote Arab culture, dabke and intercultural exchange in Hamburg.',
			'focus'  => 'Arab Culture Hamburg',
		),
		'ar' => array(
			'title'  => 'الثقافة العربية في هامبورغ: من نحن',
			'desc'   => 'من يقف خلف مهرجان الثقافة العربية: أحمد الظاهر وآراب كالتشر، من أجل الثقافة العربية والدبكة والتبادل الثقافي في هامبورغ.',
			'focus'  => 'الثقافة العربية في هامبورغ',
		),
	),
	'contact'  => array(
		'de' => array(
			'title'  => 'Kontakt – Arab Kulturfestival Hamburg',
			'desc'   => 'Fragen zu Terminen, Dabke-Kursen oder Presse? Schreib uns an info@arab-kulturfestival.de oder nutze das Kontaktformular.',
			'focus'  => 'Arab Kulturfestival Hamburg Kontakt',
		),
		'en' => array(
			'title'  => 'Contact – Arab Cultural Festival Hamburg',
			'desc'   => 'Questions about dates, dabke classes or press? Write to info@arab-kulturfestival.de or use the contact form.',
			'focus'  => 'Arab Cultural Festival Hamburg contact',
		),
		'ar' => array(
			'title'  => 'اتصل بنا – مهرجان الثقافة العربية في هامبورغ',
			'desc'   => 'أسئلة عن المواعيد أو دورات الدبكة أو الصحافة؟ راسلنا على info@arab-kulturfestival.de أو استخدم نموذج التواصل.',
			'focus'  => 'مهرجان الثقافة العربية في هامبورغ تواصل',
		),
	),
	'press'    => array(
		'de' => array(
			'title'  => 'Presse – Arab Kulturfestival Hamburg',
			'desc'   => 'Pressematerial, Berichte und Ansprechpartner zum Arab Kulturfestival Hamburg: hier findest du Medienberichte und Kontakt für Redaktionen.',
			'focus'  => 'Arab Kulturfestival Hamburg Presse',
		),
		'en' => array(
			'title'  => 'Press – Arab Cultural Festival Hamburg',
			'desc'   => 'Press material, media reports and contacts for the Arab Cultural Festival Hamburg: find coverage and get in touch with our team.',
			'focus'  => 'Arab Cultural Festival Hamburg press',
		),
		'ar' => array(
			'title'  => 'الصحافة – مهرجان الثقافة العربية في هامبورغ',
			'desc'   => 'مواد صحفية وتقارير وجهات اتصال لمهرجان الثقافة العربية في هامبورغ: تغطية إعلامية وتواصل مباشر مع الفريق.',
			'focus'  => 'مهرجان الثقافة العربية في هامبورغ صحافة',
		),
	),
	'privacy'  => array(
		'de' => array(
			'title'  => 'Datenschutz – Arab Kulturfestival Hamburg',
			'desc'   => 'Wie wir personenbezogene Daten verarbeiten, welche Dienste wir nutzen und wie du deine YouTube-Einwilligung jederzeit ändern oder widerrufen kannst.',
			'focus'  => 'Datenschutz Arab Kulturfestival',
		),
		'en' => array(
			'title'  => 'Privacy – Arab Cultural Festival Hamburg',
			'desc'   => 'How we process personal data, which services we use and how you can change or withdraw your YouTube consent at any time.',
			'focus'  => 'privacy Arab Cultural Festival',
		),
		'ar' => array(
			'title'  => 'إعدادات الخصوصية – مهرجان الثقافة العربية في هامبورغ',
			'desc'   => 'كيف نعالج البيانات الشخصية، وما الخدمات التي نستخدمها، وكيف يمكنك تغيير موافقتك على يوتيوب أو سحبها في أي وقت.',
			'focus'  => 'خصوصية مهرجان الثقافة العربية',
		),
	),
	'imprint'  => array(
		'de' => array(
			'title'  => 'Impressum – Arab Kulturfestival Hamburg',
			'desc'   => 'Anbieterkennzeichnung nach § 5 DDG: Arab Kulturfestival, Julius-Vosseler-Straße 106b, 22527 Hamburg. Kontakt und Verantwortliche.',
			'focus'  => 'Impressum Arab Kulturfestival',
		),
		'en' => array(
			'title'  => 'Legal notice – Arab Cultural Festival Hamburg',
			'desc'   => 'Provider information: Arab Cultural Festival, Julius-Vosseler-Straße 106b, 22527 Hamburg, Germany. Contact details and responsibility.',
			'focus'  => 'legal notice Arab Cultural Festival',
		),
		'ar' => array(
			'title'  => 'بيانات الناشر – مهرجان الثقافة العربية في هامبورغ',
			'desc'   => 'بيانات الجهة المسؤولة: مهرجان الثقافة العربية، Julius-Vosseler-Straße 106b، 22527 هامبورغ، ألمانيا. بيانات التواصل والمسؤولية.',
			'focus'  => 'بيانات الناشر مهرجان الثقافة العربية',
		),
	),
	'cookies'  => array(
		'de' => array(
			'title'  => 'Cookies – Arab Kulturfestival Hamburg',
			'desc'   => 'Welche Cookies und lokalen Speicher wir verwenden: keine Analyse- oder Werbecookies, YouTube nur nach ausdrücklicher Einwilligung.',
			'focus'  => 'Cookies Arab Kulturfestival',
		),
		'en' => array(
			'title'  => 'Cookies – Arab Cultural Festival Hamburg',
			'desc'   => 'Which cookies and local storage we use: no analytics or advertising cookies, YouTube only after your explicit consent.',
			'focus'  => 'cookies Arab Cultural Festival',
		),
		'ar' => array(
			'title'  => 'ملفات تعريف الارتباط – مهرجان الثقافة العربية في هامبورغ',
			'desc'   => 'ما ملفات تعريف الارتباط والتخزين المحلي التي نستخدمها: لا ملفات تحليل أو إعلان، ويوتيوب فقط بعد موافقتك الصريحة.',
			'focus'  => 'ملفات تعريف الارتباط مهرجان الثقافة العربية',
		),
	),
);

$count = 0;

foreach ( $pages as $group => $translations ) {
	foreach ( $translations as $lang => $data ) {
		if ( ! isset( $map[ $lang ][ $group ] ) ) {
			echo "MISSING page $group/$lang\n";
			continue;
		}

		$id = (int) $map[ $lang ][ $group ];

		update_post_meta( $id, 'rank_math_title', $data['title'] );
		update_post_meta( $id, 'rank_math_description', $data['desc'] );
		update_post_meta( $id, 'rank_math_focus_keyword', $data['focus'] );
		update_post_meta( $id, 'rank_math_facebook_image', yd_image( $id, 'yd-hero' ) );
		update_post_meta( $id, 'rank_math_twitter_image', yd_image( $id, 'yd-hero' ) );
		++$count;
	}
}

/* -------------------------------------------------------------- posts -- */

$posts = array(
	145 => array(
		'title' => 'Dabke in Hamburg: Warum der Tanz keine Einladung braucht',
		'desc'  => 'Dabke ist mehr als ein Tanz: Er entsteht im Kreis, ohne Bühne und ohne Einladung. Ein Blick auf Herkunft, Rhythmus und Gemeinschaft.',
		'focus' => 'Dabke Hamburg',
	),
	146 => array(
		'title' => 'Dabke in Hamburg: why the dance needs no invitation',
		'desc'  => 'Dabke is more than a dance: it happens in a circle, without a stage and without an invitation. A look at origins, rhythm and community.',
		'focus' => 'Dabke Hamburg',
	),
	147 => array(
		'title' => 'الدبكة في هامبورغ: رقصة لا تحتاج إلى دعوة',
		'desc'  => 'الدبكة أكثر من رقص: تُرقص في حلقة، بلا مسرح وبلا دعوة. نظرة على الجذور والإيقاع والجماعة.',
		'focus' => 'دبكة في هامبورغ',
	),
	148 => array(
		'title' => 'Arabische Kultur im Exil: Identität, die bleibt',
		'desc'  => 'Was bleibt, wenn die Heimat fehlt? Wie arabische Kultur im Exil weiterlebt – in Musik, Sprache, Essen und gemeinsamen Abenden.',
		'focus' => 'Arabische Kultur Hamburg',
	),
	149 => array(
		'title' => 'Arabic culture in exile: identity that stays',
		'desc'  => 'What remains when home is missing? How Arabic culture lives on in exile – through music, language, food and shared evenings.',
		'focus' => 'Arab culture Hamburg',
	),
	150 => array(
		'title' => 'الثقافة العربية في المنفى: هوية تبقى',
		'desc'  => 'ماذا يبقى عندما يغيب الوطن؟ كيف تعيش الثقافة العربية في المنفى عبر الموسيقى واللغة والطعام والأمسيات المشتركة.',
		'focus' => 'الثقافة العربية في هامبورغ',
	),
	151 => array(
		'title' => 'Arabische Kalligrafie: Wenn Buchstaben Bilder werden',
		'desc'  => 'Arabische Kalligrafie verbindet Schrift und Bild. Wie Buchstaben zu Kunst werden und warum die Handschrift bis heute fasziniert.',
		'focus' => 'Arabische Kalligrafie Hamburg',
	),
	152 => array(
		'title' => 'Arabic calligraphy: when letters become images',
		'desc'  => 'Arabic calligraphy connects writing and image. How letters become art and why handwriting still fascinates today.',
		'focus' => 'Arabic calligraphy Hamburg',
	),
	153 => array(
		'title' => 'الخط العربي: حين تصبح الحروف صوراً',
		'desc'  => 'الخط العربي يجمع بين الكتابة والصورة. كيف تتحول الحروف إلى فن ولماذا ما زال الخط يأسرنا حتى اليوم.',
		'focus' => 'الخط العربي هامبورغ',
	),
	154 => array(
		'title' => 'Arabische Musik: Maqam, Rhythmus und Stimme',
		'desc'  => 'Maqam, Iqaa und Stimme: Was arabische Musik ausmacht und wie sie in Hamburg zwischen Tradition und neuen Klängen weiterlebt.',
		'focus' => 'Arabische Musik Hamburg',
	),
	155 => array(
		'title' => 'Arabic music: maqam, rhythm and voice',
		'desc'  => 'Maqam, iqaa and voice: what defines Arabic music and how it lives on in Hamburg between tradition and new sounds.',
		'focus' => 'Arabic music Hamburg',
	),
	156 => array(
		'title' => 'الموسيقى العربية: المقام والإيقاع والصوت',
		'desc'  => 'المقام والإيقاع والصوت: ما يميّز الموسيقى العربية وكيف تعيش في هامبورغ بين التقليد والأصوات الجديدة.',
		'focus' => 'موسيقى عربية في هامبورغ',
	),
);

foreach ( $posts as $id => $data ) {
	update_post_meta( $id, 'rank_math_title', $data['title'] );
	update_post_meta( $id, 'rank_math_description', $data['desc'] );
	update_post_meta( $id, 'rank_math_focus_keyword', $data['focus'] );
	update_post_meta( $id, 'rank_math_facebook_image', yd_image( $id, 'yd-hero' ) );
	update_post_meta( $id, 'rank_math_twitter_image', yd_image( $id, 'yd-hero' ) );
	++$count;
}

/* ------------------------------------------------------------- events -- */

$months = array(
	'de' => array( 1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' ),
	'en' => array( 1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
	'ar' => array( 1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ),
);

$templates = array(
	'de' => array(
		'title' => 'Dabke Kurs Hamburg am %1$s. %2$s %3$s',
		'desc'  => 'Offener Dabke-Kurs in Hamburg am %1$s. %2$s %3$s, 18:15–19:45 Uhr im Goldbekhaus. Eintritt frei, Spenden willkommen – keine Vorkenntnisse nötig.',
		'focus' => 'Dabke Kurs Hamburg %1$s. %2$s %3$s',
	),
	'en' => array(
		'title' => 'Dabke class Hamburg on %1$s %2$s %3$s',
		'desc'  => 'Open dabke class in Hamburg on %1$s %2$s %3$s, 6:15–7:45 pm at Goldbekhaus. Free entry, donations welcome – no experience needed.',
		'focus' => 'Dabke class Hamburg %1$s %2$s %3$s',
	),
	'ar' => array(
		'title' => 'دورة دبكة في هامبورغ %1$s %2$s %3$s',
		'desc'  => 'دورة دبكة مفتوحة في هامبورغ يوم %1$s %2$s %3$s من 18:15 إلى 19:45 في غولدبيك هاوس. الدخول مجاني والتبرعات مرحّب بها — دون خبرة سابقة.',
		'focus' => 'دورة دبكة في هامبورغ %1$s %2$s %3$s',
	),
);

foreach ( array( 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105 ) as $id ) {
	$date = get_post_meta( $id, '_yd_date', true );

	$lang = 'de';
	foreach ( $event_groups as $ids ) {
		$index = array_search( $id, $ids, true );
		if ( false !== $index ) {
			$lang = array( 'de', 'en', 'ar' )[ $index ];
			break;
		}
	}

	if ( ! $date || ! isset( $months[ $lang ] ) ) {
		echo "SKIP event $id\n";
		continue;
	}

	$timestamp = strtotime( $date );
	$day       = (int) gmdate( 'j', $timestamp );
	$month     = $months[ $lang ][ (int) gmdate( 'n', $timestamp ) ];
	$year      = gmdate( 'Y', $timestamp );
	$template  = $templates[ $lang ];

	$title = sprintf( $template['title'], $day, $month, $year );
	$desc  = sprintf( $template['desc'], $day, $month, $year );
	$focus = sprintf( $template['focus'], $day, $month, $year );

	update_post_meta( $id, 'rank_math_title', $title );
	update_post_meta( $id, 'rank_math_description', $desc );
	update_post_meta( $id, 'rank_math_focus_keyword', $focus );
	update_post_meta( $id, 'rank_math_facebook_image', yd_image( $id, 'yd-hero' ) );
	update_post_meta( $id, 'rank_math_twitter_image', yd_image( $id, 'yd-hero' ) );
	++$count;
}

echo "SEO meta written for $count objects.\n";

// Rank Math caches its sitemap; drop it so the next request rebuilds.
if ( class_exists( 'RankMath\\Sitemap\\Cache' ) ) {
	\RankMath\Sitemap\Cache::invalidate_storage();
}
