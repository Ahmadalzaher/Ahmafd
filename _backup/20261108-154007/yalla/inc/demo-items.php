<?php
/**
 * Demo events, formats, team, partners and magazine articles.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wording of the recurring dabke course, one entry per language.
 *
 * Shared by the first-install seed and the "create in three languages" form,
 * so both always produce the same texts.
 *
 * @return array
 */
function yd_event_copy() {
	return array(
		'de' => array(
			'title'   => 'Dabke Tanzen — offener Kurs',
			'excerpt' => 'Arabischer Volkstanz für Anfänger:innen und Fortgeschrittene — ohne Vorkenntnisse, ohne Anmeldung.',
			'body'    => '<p>Dabke ist ein arabischer Volkstanz, der vor allem im Libanon, Palästina, Syrien, Jordanien und Irak bekannt ist. Gemeinsam mit Kursleiter Ahmad lernt ihr arabische Folklore und übt den traditionellen Tanz. Vorkenntnisse braucht ihr keine — auch Anfänger:innen sind herzlich willkommen.</p><p><strong>Die Teilnahme ist kostenfrei.</strong> Kommt einfach vorbei — über eine Spende für unsere Arbeit freuen wir uns. Rückfragen an info@arab-kulturfestival.de.</p><p>Adresse: Moorfuhrtweg 9, 22301 Hamburg — donnerstags von 18:15 bis 19:45 Uhr.</p>',
			'city'    => 'Hamburg',
			'price'   => 'Eintritt frei · Spenden willkommen',
		),
		'en' => array(
			'title'   => 'Dabke dancing — open course',
			'excerpt' => 'Arabic folk dance for beginners and advanced dancers — no experience, no registration needed.',
			'body'    => '<p>Dabke is an Arabic folk dance known above all in Lebanon, Palestine, Syria, Jordan and Iraq. Together with course leader Ahmad you will learn Arabic folklore and practise the traditional dance. No previous knowledge is needed — beginners are very welcome.</p><p><strong>Participation is free.</strong> Just come along — donations for our work are very welcome. Questions: info@arab-kulturfestival.de.</p><p>Address: Moorfuhrtweg 9, 22301 Hamburg — Thursdays from 18:15 to 19:45.</p>',
			'city'    => 'Hamburg',
			'price'   => 'Free entry · donations welcome',
		),
		'ar' => array(
			'title'   => 'دبكة — دورة مفتوحة',
			'excerpt' => 'رقص شعبي عربي للمبتدئين والمتقدمين — بلا خبرة سابقة وبلا تسجيل.',
			'body'    => '<p>الدبكة رقص شعبي عربي معروف خاصة في لبنان وفلسطين وسوريا والأردن والعراق. مع المدرّب أحمد تتعلّمون الفولكلور العربي وتتدربون على الرقص التقليدي. لا حاجة إلى خبرة سابقة — والمبتدئون مرحّب بهم.</p><p><strong>المشاركة مجانية.</strong> تعال فقط — والتبرعات لدعم عملنا مرحّب بها. للاستفسار: info@arab-kulturfestival.de.</p><p>العنوان: Moorfuhrtweg 9, 22301 هامبورغ — كل خميس من 18:15 إلى 19:45.</p>',
			'city'    => 'هامبورغ',
			'price'   => 'الدخول مجاني · التبرعات مرحّب بها',
		),
	);
}

/**
 * Events (the weekly dabke course is the real recurring programme).
 *
 * @return array
 */
function yd_demo_events() {
	$dates = array(
		array( '2026-11-05', 'upcoming' ),
		array( '2026-11-19', 'upcoming' ),
		array( '2026-12-03', 'upcoming' ),
		array( '2026-12-17', 'upcoming' ),
		array( '2026-10-22', 'past' ),
		array( '2026-10-08', 'past' ),
	);

	$copy   = yd_event_copy();
	$events = array();

	foreach ( $dates as $row ) {
		list( $date, $state ) = $row;
		$is_past = 'past' === $state;

		foreach ( $copy as $lang => $data ) {
			$events[] = array(
				'lang'    => $lang,
				'title'   => $data['title'],
				'date'    => $date,
				'time'    => '18:15',
				'end'     => '19:45',
				'venue'   => 'Goldbekhaus',
				'city'    => $data['city'],
				'address' => 'Moorfuhrtweg 9, 22301 Hamburg',
				'price'   => $data['price'],
				'status'  => $is_past ? 'past' : 'upcoming',
				'excerpt' => $data['excerpt'],
				'content' => $data['body'],
			);
		}
	}

	return $events;
}

/**
 * The four festival formats.
 *
 * @return array
 */
function yd_demo_services() {
	return array(
		array(
			'lang'    => 'de',
			'title'   => 'Folkloretanz & Dabke',
			'kicker'  => 'Dabke',
			'excerpt' => 'Wenn Menschen sich an den Händen halten und im Takt schwingen, ist die Dabke schon da — sie braucht keine Einladung.',
			'content' => '<p>Dabke ist ein Volkstanz aus der Levante, der bei Hochzeiten, Festen und auf öffentlichen Plätzen getanzt wird. Die Hände sind ein Zeichen von Einheit, die Füße auf dem Boden ein Zeichen von Kraft. Wir unterrichten ihn so, dass jede:r mitkommen kann.</p><p>Für Anfänger:innen, Fortgeschrittene, Frauenkreise, Schulklassen und Teams.</p>',
			'order'   => 1,
		),
		array(
			'lang'    => 'de',
			'title'   => 'Arabische Musik',
			'kicker'  => 'Musik',
			'excerpt' => 'Oud, Violine und Perkussion: Konzerte und Sessions von klassischer Maqam-Musik bis zum zeitgenössischen Lied.',
			'content' => '<p>Arabische Musik wirkt durch Melodie, Maqam und Rhythmus. Wir bringen Ensembles und Solist:innen auf die Bühne — und öffnen Sessions für alle, die mitspielen möchten.</p>',
			'order'   => 2,
		),
		array(
			'lang'    => 'de',
			'title'   => 'Kalligrafie',
			'kicker'  => 'Schrift',
			'excerpt' => 'Buchstaben als Kunst: Workshops und Ausstellungen zur arabischen Schrift und ihrer Geschichte.',
			'content' => '<p>In der Kalligrafie zählt nicht nur Lesbarkeit, sondern Balance und Ausdruck. In unseren Workshops lernen Teilnehmende Rohr, Tinte und die Grundformen der arabischen Schrift kennen.</p>',
			'order'   => 3,
		),
		array(
			'lang'    => 'de',
			'title'   => 'Literatur & Hakawati',
			'kicker'  => 'Erzählen',
			'excerpt' => 'Lesungen, Gespräche und traditionelles Geschichtenerzählen — arabische Literatur im Exil.',
			'content' => '<p>Der Hakawati ist der Erzähler, der ein Publikum stundenlang halten kann. Wir verbinden diese Tradition mit zeitgenössischer arabischer Literatur, Übersetzung und Gespräch.</p>',
			'order'   => 4,
		),

		array(
			'lang'    => 'en',
			'title'   => 'Folk dance & dabke',
			'kicker'  => 'Dabke',
			'excerpt' => 'When people hold hands and sway to the beat, dabke is already there — it needs no invitation.',
			'content' => '<p>Dabke is a folk dance from the Levant, danced at weddings, celebrations and in public squares. Hands are a sign of unity, feet on the ground a sign of strength. We teach it so that everyone can join.</p><p>For beginners, advanced dancers, women’s circles, school classes and teams.</p>',
			'order'   => 1,
		),
		array(
			'lang'    => 'en',
			'title'   => 'Arabic music',
			'kicker'  => 'Music',
			'excerpt' => 'Oud, violin and percussion: concerts and sessions from classical maqam to contemporary song.',
			'content' => '<p>Arabic music works through melody, maqam and rhythm. We bring ensembles and soloists to the stage — and open sessions for everyone who wants to play along.</p>',
			'order'   => 2,
		),
		array(
			'lang'    => 'en',
			'title'   => 'Calligraphy',
			'kicker'  => 'Script',
			'excerpt' => 'Letters as art: workshops and exhibitions on Arabic script and its history.',
			'content' => '<p>In calligraphy, legibility is not the only thing that counts — balance and expression matter just as much. In our workshops participants get to know reed, ink and the basic forms of Arabic script.</p>',
			'order'   => 3,
		),
		array(
			'lang'    => 'en',
			'title'   => 'Literature & hakawati',
			'kicker'  => 'Storytelling',
			'excerpt' => 'Readings, talks and traditional storytelling — Arabic literature in exile.',
			'content' => '<p>The hakawati is a storyteller who can hold an audience for hours. We connect this tradition with contemporary Arabic literature, translation and conversation.</p>',
			'order'   => 4,
		),

		array(
			'lang'    => 'ar',
			'title'   => 'الرقص الشعبي والدبكة',
			'kicker'  => 'دبكة',
			'excerpt' => 'حين يتشابك الناس بالأيدي ويتمايلون على الإيقاع تكون الدبكة قد حضرت — لا تحتاج إلى دعوة.',
			'content' => '<p>الدبكة رقص شعبي شامي يُرقص في الأعراس والأعياد والساحات العامة. الأيدي رمز للوحدة، والأقدام على الأرض رمز للقوة. نعلّمها بطريقة تتيح للجميع المشاركة.</p><p>للمبتدئين والمتقدمين ودوائر النساء وصفوف المدارس والفرق.</p>',
			'order'   => 1,
		),
		array(
			'lang'    => 'ar',
			'title'   => 'الموسيقى العربية',
			'kicker'  => 'موسيقى',
			'excerpt' => 'العود والكمان والإيقاع: حفلات وجلسات من المقام الكلاسيكي إلى الأغنية المعاصرة.',
			'content' => '<p>تعمل الموسيقى العربية عبر اللحن والمقام والإيقاع. نستضيف فرقاً وعازفين منفردين — ونفتح جلسات لكل من يريد العزف معنا.</p>',
			'order'   => 2,
		),
		array(
			'lang'    => 'ar',
			'title'   => 'الخط العربي',
			'kicker'  => 'حرف',
			'excerpt' => 'الحروف كفن: ورش ومعارض عن الكتابة العربية وتاريخها.',
			'content' => '<p>في الخط العربي لا تُهمّ القراءة وحدها، بل التوازن والتعبير أيضاً. في ورشنا يتعرّف المشاركون على القصبة والحبر والأشكال الأساسية للحرف العربي.</p>',
			'order'   => 3,
		),
		array(
			'lang'    => 'ar',
			'title'   => 'الأدب والحكواتي',
			'kicker'  => 'حكي',
			'excerpt' => 'قراءات وحوارات وحكاية تقليدية — الأدب العربي في المنفى.',
			'content' => '<p>الحكواتي راوٍ يستطيع أن يأسر جمهوره ساعات. نصل هذه التقاليد بالأدب العربي المعاصر والترجمة والحوار.</p>',
			'order'   => 4,
		),
	);
}

/**
 * Quotes from the festival magazine.
 *
 * @return array
 */
function yd_demo_testimonials() {
	return array(
		array(
			'lang'  => 'de',
			'quote' => 'Wenn Sie Leute sehen, die sich an den Händen halten und zu den Klängen der Musik schwanken, dann wissen Sie, dass hier eine Hochzeit oder ein Fest beginnt. Die Dabke in der Levante braucht keine Einladung.',
			'org'   => 'Aus dem Artikel »Die Dabke braucht keine Einladung«',
			'role'  => 'Ahmad AL Zaher',
			'order' => 1,
		),
		array(
			'lang'  => 'de',
			'quote' => 'Das Ansehen der Kalligrafie ist überall dort gegeben, wo das Abschreiben heiliger Texte selbst als sakraler Vorgang beschrieben wird. Wichtiger als Lesbarkeit ist die perfekte ästhetische Ausgewogenheit.',
			'org'   => 'Aus dem Artikel »Kalligrafie«',
			'role'  => 'Arap Culture',
			'order' => 2,
		),
		array(
			'lang'  => 'de',
			'quote' => 'Heute lebe ich in Hamburg und trage meine syrische Nationalität und meine arabische Kultur mit mir. Deshalb habe ich arabische Kulturveranstaltungen ins Leben gerufen — Kultur gibt Menschen ein Gefühl der Zugehörigkeit.',
			'org'   => 'Aus dem Artikel »Arabische Kultur im Exil«',
			'role'  => 'Ahmad AL Zaher',
			'order' => 3,
		),

		array(
			'lang'  => 'en',
			'quote' => 'When you see people holding hands and swaying to the music, you know a wedding or a festival is beginning. Dabke in the Levant needs no invitation.',
			'org'   => 'From the article “Dabke needs no invitation”',
			'role'  => 'Ahmad AL Zaher',
			'order' => 1,
		),
		array(
			'lang'  => 'en',
			'quote' => 'Calligraphy is held in esteem wherever copying sacred texts is itself described as a sacred act. More important than legibility is perfect aesthetic balance.',
			'org'   => 'From the article “Calligraphy”',
			'role'  => 'Arap Culture',
			'order' => 2,
		),
		array(
			'lang'  => 'en',
			'quote' => 'Today I live in Hamburg and carry my Syrian nationality and my Arabic culture with me. That is why I started Arabic cultural events — culture gives people a sense of belonging.',
			'org'   => 'From the article “Arabic culture in exile”',
			'role'  => 'Ahmad AL Zaher',
			'order' => 3,
		),

		array(
			'lang'  => 'ar',
			'quote' => 'حين ترى الناس يتشابكون بالأيدي ويتمايلون على وقع الموسيقى، تعرف أن عرساً أو مهرجاناً يبدأ. الدبكة في بلاد الشام لا تحتاج إلى دعوة.',
			'org'   => 'من مقال «الدبكة لا تحتاج إلى دعوة»',
			'role'  => 'أحمد الظاهر',
			'order' => 1,
		),
		array(
			'lang'  => 'ar',
			'quote' => 'للخط العربي مكانة حيث يكون نسخ النصوص المقدّسة فعلاً مقدّساً بذاته. والأهم من وضوح القراءة هو التوازن الجمالي الكامل.',
			'org'   => 'من مقال «الخط العربي»',
			'role'  => 'آراب كالتشر',
			'order' => 2,
		),
		array(
			'lang'  => 'ar',
			'quote' => 'اليوم أعيش في هامبورغ وأحمل جنسيتي السورية وثقافتي العربية معي. لذلك أطلقت فعاليات ثقافية عربية — الثقافة تمنح الإنسان إحساساً بالانتماء.',
			'org'   => 'من مقال «الثقافة العربية في المنفى»',
			'role'  => 'أحمد الظاهر',
			'order' => 3,
		),
	);
}

/**
 * Team members.
 *
 * @return array
 */
function yd_demo_team() {
	return array(
		array(
			'lang'    => 'de',
			'title'   => 'Ahmad AL Zaher',
			'role'    => 'Gründer & künstlerischer Leiter',
			'place'   => 'Hamburg, Deutschland',
			'content' => '<p>Journalist, Gründer und Chefredakteur von Arap Culture sowie Gründer und künstlerischer Leiter des Arab Kulturfestivals. Ahmad hat das Festival ins Leben gerufen, um die Vision vieler arabischer Menschen in der Diaspora Wirklichkeit werden zu lassen: Zugehörigkeit.</p>',
			'photo'   => 'img/portrait-ahmad.jpg',
			'order'   => 1,
		),
		array(
			'lang'    => 'de',
			'title'   => 'Das Festivalteam',
			'role'    => 'Organisation, Übersetzung & Technik',
			'place'   => 'Hamburg, Deutschland',
			'content' => '<p>Künstler:innen, Übersetzer:innen, Techniker:innen und Ehrenamtliche, die das Festival jedes Jahr aufbauen, betreuen und wieder abbauen — und das ganze Jahr über daran arbeiten.</p>',
			'photo'   => 'img/stage-group.jpg',
			'order'   => 2,
		),

		array(
			'lang'    => 'en',
			'title'   => 'Ahmad AL Zaher',
			'role'    => 'Founder & artistic director',
			'place'   => 'Hamburg, Germany',
			'content' => '<p>Journalist, founder and editor-in-chief of Arap Culture, and founder and artistic director of the Arab Culture Festival. Ahmad created the festival to make the vision of many Arab people in the diaspora real: belonging.</p>',
			'photo'   => 'img/portrait-ahmad.jpg',
			'order'   => 1,
		),
		array(
			'lang'    => 'en',
			'title'   => 'The festival team',
			'role'    => 'Organisation, translation & tech',
			'place'   => 'Hamburg, Germany',
			'content' => '<p>Artists, translators, technicians and volunteers who build, run and dismantle the festival every year — and work on it all year round.</p>',
			'photo'   => 'img/stage-group.jpg',
			'order'   => 2,
		),

		array(
			'lang'    => 'ar',
			'title'   => 'أحمد الظاهر',
			'role'    => 'المؤسس والمدير الفني',
			'place'   => 'هامبورغ، ألمانيا',
			'content' => '<p>صحفي، ومؤسس ورئيس تحرير آراب كالتشر، ومؤسس مهرجان الثقافة العربية ومديره الفني. أطلق المهرجان لتحقيق رؤية كثير من العرب في المهجر: الانتماء.</p>',
			'photo'   => 'img/portrait-ahmad.jpg',
			'order'   => 1,
		),
		array(
			'lang'    => 'ar',
			'title'   => 'فريق المهرجان',
			'role'    => 'التنظيم والترجمة والتقنية',
			'place'   => 'هامبورغ، ألمانيا',
			'content' => '<p>فنانون ومترجمون وتقنيون ومتطوعون يبنون المهرجان كل عام ويديرونه — ويعملون عليه طوال السنة.</p>',
			'photo'   => 'img/stage-group.jpg',
			'order'   => 2,
		),
	);
}

/**
 * Partners and logos.
 *
 * @return array
 */
function yd_demo_partners() {
	return array(
		array( 'name' => 'Arap Culture', 'logo' => 'img/partner-arap.png', 'url' => 'https://www.arap-culture.com/', 'order' => 1 ),
		array( 'name' => 'Goldbekhaus Hamburg', 'logo' => 'img/partner-goldbekhaus.png', 'url' => 'https://www.goldbekhaus.de/', 'order' => 2 ),
		array( 'name' => 'Behörde für Kultur und Medien Hamburg', 'logo' => 'img/partner-bkm.png', 'url' => 'https://www.hamburg.de/bkm/', 'order' => 3 ),
		array( 'name' => 'Arap Library', 'logo' => 'img/partner-arap-library.png', 'url' => 'https://arap-library.de/', 'order' => 4 ),
		array( 'name' => 'Adwaa', 'logo' => 'img/partner-adwaa.png', 'url' => '', 'order' => 5 ),
		array( 'name' => 'Rooh Al-Sharq', 'logo' => 'img/partner-rooh-alsharq.png', 'url' => '', 'order' => 6 ),
	);
}
