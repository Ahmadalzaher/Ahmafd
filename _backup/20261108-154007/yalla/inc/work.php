<?php
/**
 * Content and icons for the "our work & activities" page.
 *
 * The page lives in three languages (de/en/ar) and is rendered by
 * template-parts/work.php. Content is kept in code so all three
 * versions stay in sync without editor maintenance.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline icon set for the work page.
 *
 * @param string $name Icon name.
 * @return string SVG markup.
 */
function yd_work_icon( $name ) {
	$icons = array(
		'music'    => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
		'mic'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2.5" width="6" height="11" rx="3"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0"/><path d="M12 18v3"/></svg>',
		'dj'       => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="13" rx="2"/><path d="M7 20h10"/><path d="M8 8h5M8 11h3"/><circle cx="16.5" cy="11" r="2.5"/></svg>',
		'cap'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11.5v4.4c0 1.7 2.7 3.1 6 3.1s6-1.4 6-3.1v-4.4"/></svg>',
		'art'      => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10" r="1.8"/><path d="m5.5 17 4-5 3.5 4 2.5-2.5L19 17"/></svg>',
		'kufi'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5c5.5-1.6 11 3.5 8 6-2.6 2.2-7.5-1.6-4.5-4.3C11.4 4.7 20 6.5 20 13c0 4-3.5 6-8 5.5"/><path d="M4 21 12 3"/></svg>',
		'film'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="15" rx="2"/><path d="M7.5 4.5v15M16.5 4.5v15M3 9.5h4.5M3 14.5h4.5M16.5 9.5H21M16.5 14.5H21"/></svg>',
		'book'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 5.5c2.8-1.6 5.8-1.6 8.5 0 2.7-1.6 5.7-1.6 8.5 0v13c-2.8-1.6-5.8-1.6-8.5 0-2.7-1.6-5.7-1.6-8.5 0z"/><path d="M12 5.5v13"/></svg>',
		'heart'    => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20.5S4.5 15.6 4.5 10.6A4 4 0 0 1 12 8a4 4 0 0 1 7.5 2.6c0 5-7.5 9.9-7.5 9.9Z"/></svg>',
		'kitchen'  => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3v8M10 3v8M8 3v18"/><path d="M15 21a4 4 0 0 0 0-8h-.3A1.7 1.7 0 0 1 15 9.5h2V3"/></svg>',
		'school'   => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 4l9 6.5V21H3z"/><path d="M9.5 21v-6h5v6"/></svg>',
		'users'    => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7.5" r="3.2"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><circle cx="17.5" cy="9" r="2.6"/><path d="M16.5 20a5.5 5.5 0 0 1 5-5.4"/></svg>',
		'gift'     => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="8.5" width="17" height="13" rx="1.5"/><path d="M3.5 13h17M12 8.5V21"/><path d="M12 8.5C9.5 8.5 7.6 7 7.6 5.6 7.6 4.2 9 3.2 10.2 4c1.2.8 1.8 2.5 1.8 4.5 0-2 .6-3.7 1.8-4.5 1.2-.9 2.6 0 2.6 1.6 0 1.5-1.9 3-4.4 3z"/></svg>',
		'camera'   => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h1.3l1-2h6.4l1 2h1.3A2.5 2.5 0 0 1 20.5 8.5v8a2.5 2.5 0 0 1-2.5 2.5H6.5a2.5 2.5 0 0 1-2.5-2.5v-8Z"/><circle cx="12.5" cy="12.5" r="3.2"/></svg>',
		'design'   => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 4.5 19.5 8.5 8 20H4v-4z"/><path d="m13 7 4 4"/></svg>',
		'sound'    => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5 6.5 8.5H3.5v7h3L11 19z"/><path d="M15.5 9.5a3.6 3.6 0 0 1 0 5"/><path d="M18.5 7a7 7 0 0 1 0 10"/></svg>',
		'sparkles' => '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5 13.6 8l4.5.4-3.4 3 1 4.6L12 13.8 8.3 16l1-4.6-3.4-3L10.4 8z"/><path d="M19 16.5v3M17.5 18h3"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['sparkles'];
}

/**
 * Copy blocks for the work page, per language.
 *
 * @param string $lang Language code.
 * @return array
 */
function yd_work_content( $lang = '' ) {
	$lang = $lang ? $lang : yd_lang();

	$copy = array(
		'de' => array(
			'eyebrow' => 'Musik · Tanz · Kunst · Sprache · Familie',
			'intro'   => 'Wir feiern die Vielfalt der arabischen Kultur mit Musik, Tanz, Kunst und Sprache. Wir bieten Veranstaltungen, Kurse und interaktive Erlebnisse, die Begegnung, Lernen und Kreativität für alle öffnen.',
			'nav'     => 'Zu den Themen springen',
			'sections'    => array(
				array(
					'id'    => 'events',
					'icon'  => 'music',
					'title' => 'Veranstaltungen & Konzerte',
					'intro' => 'Bühne und öffentliche Formate, die Musik, Tanz und Kunst verbinden — offen für alle und für jede Generation.',
					'items' => array(
						array(
							't' => 'Festivals & Kulturtage',
							'd' => 'Wir organisieren Festivals und Tage, die die Vielfalt der arabischen Kultur zeigen: Bühnenkunst, Musik und interaktive Aktivitäten für alle Altersgruppen.',
						),
						array(
							't' => 'Orientalische Livemusik',
							'd' => 'Konzerte und Abende mit Sänger:innen, Musiker:innen und Ensembles — vom klassischen Tarab bis zu zeitgenössischen Sounds und Rhythmen.',
						),
						array(
							't' => 'Arabic DJ · Arabischer DJ',
							'd' => 'Ein vorfreudiges Ambiente aus arabischen Songs, orientalischen Beats und Klängen aus aller Welt — das Programm richten wir nach Publikum und Anlass aus.',
						),
					),
				),
				array(
					'id'    => 'courses',
					'icon'  => 'cap',
					'title' => 'Kurse & Workshops',
					'intro' => 'Lernen und Teilhabe in einem gemein­samen, lebendigen Rahmen.',
					'items' => array(
						array(
							't' => 'Dabke-Kurse',
							'd' => 'Kurse und Workshops für Einsteiger:innen und Freunde des Volkstanzes: die Schritte der Dabke, ihre Rhythmen und die Freude am Tanz in der Gruppe.',
						),
						array(
							't' => 'Orientalischer Tanz & Samar',
							'd' => 'Kurse und Workshops zu den Grundlagen des orientalischen Tanzes, zur Entwicklung des Rhythmusgefühls und zum Ausdruck durch Bewegung.',
						),
						array(
							't' => 'Kurse für orientalische Musik',
							'd' => 'Gesang und Instrumentalspiel kennenlernen und dabei arabische Instrumente, Maqamat und Rhythmen entdecken — Programme für verschiedene Levels.',
						),
						array(
							't' => 'Arabischunterricht',
							'd' => 'Kurse für Kinder und Erwachsene: Lesen, Schreiben und Sprechen mit spielerisch-interaktiven Methoden — für Kinder der arabischen Community und für alle, die Arabisch lernen möchten.',
						),
					),
				),
				array(
					'id'    => 'art',
					'icon'  => 'art',
					'title' => 'Kunst & Ausstellungen',
					'intro' => 'Räume für bildendes Schaffen und für die Begegnung von Künstler:innen mit ihrem Publikum.',
					'items' => array(
						array(
							't' => 'Organisation von Kunstausstellungen',
							'd' => 'Ausstellungen für Malerei, Fotografie und bildende Kunst, die es Künstler:innen ermöglichen, ihre Werke zu zeigen und mit dem Publikum ins Gespräch zu kommen.',
						),
						array(
							't' => 'Arabische Kalligrafie & traditionelle Handwerkskunst',
							'd' => 'Workshops und praktische Erlebnisse zur Schönheit der arabischen Kalligrafie und der Handwerkskunst — Erbe durch eigenes Gestalten entdecken.',
						),
					),
				),
				array(
					'id'    => 'cinema',
					'icon'  => 'film',
					'title' => 'Kino & Literatur',
					'intro' => 'Geschichten, die auf der Leinwand erzählt und im Gespräch vertieft werden.',
					'items' => array(
						array(
							't' => 'Filmvorführungen & Filmgespräche',
							'd' => 'Vorführungen arabischer Filme und Dokumentationen, gefolgt von Gesprächen mit Filmschaffenden und Publikum zum Austausch von Ideen und Erfahrungen.',
						),
						array(
							't' => 'Literatur- und Poesieabende',
							'd' => 'Begegnungen, Lesungen und poetische Abende mit Schriftsteller:innen und Gestaltern, die die arabische Sprache und die Vielfalt ihrer Stimmen feiern.',
						),
					),
				),
				array(
					'id'    => 'kids',
					'icon'  => 'heart',
					'title' => 'Kinder & Familien',
					'intro' => 'Aktivitäten, die Spiel mit Gestalten verbinden und Räume für Familien öffnen.',
					'items' => array(
						array(
							't' => 'Kultur- und Kreativangebote für Kinder',
							'd' => 'Arabische Geschichten, Bastel- und Handwerksangebote und musikalisch-bewegte Aktivitäten: Freude und Lernen verbinden und die Fantasie der Kinder fördern.',
						),
						array(
							't' => 'Familienveranstaltungen',
							'd' => 'Programme, die Junge und Alte um Kunst, Spiel und Musik versammeln und einen Ort schaffen, an dem Familien sich begegnen und austauschen.',
						),
					),
				),
				array(
					'id'    => 'kitchen',
					'icon'  => 'kitchen',
					'title' => 'Arabische Küche',
					'intro' => 'Eine arabische Tafel voller Aromen, Gerichte und Geschichten.',
					'items' => array(
						array(
							't' => 'Kochworkshops & Verkostungen',
							'd' => 'Erlebnisse, die die Vielfalt der arabischen Küche entdeckt: gemeinsam kochen, Rezepte und ihre Geschichten austauschen — in Zusammenarbeit mit Köch:innen und Restaurants.',
						),
						array(
							't' => 'Essens- und Musikabende',
							'd' => 'Abende, die die arabische Tafel mit Musik und Dabke verbinden — in einer Atmosphäre, die Begegnung und kulturellen Austausch fördert.',
						),
					),
				),
				array(
					'id'    => 'schools',
					'icon'  => 'school',
					'title' => 'Programme für Schulen & Institutionen',
					'intro' => 'Passgenaue Angebote für Schulen, Vereine und Teams.',
					'items' => array(
						array(
							't' => 'Kulturelle Programme für Schulen',
							'd' => 'Interaktive Workshops, die Kinder und Jugendliche an arabischer Sprache, Kunst, Musik und Traditionen heranführen — altersgerecht und nach ihrem Interesse ausgerichtet.',
						),
						array(
							't' => 'Workshops für Vereine & Teams',
							'd' => 'Gemeinschaftliche Aktivitäten mit Musik, Tanz und Kreativität, die Begegnung, Beteiligung und kulturellen Austausch stärken.',
						),
					),
				),
				array(
					'id'    => 'occasions',
					'icon'  => 'gift',
					'title' => 'Wir organisieren euren Anlass',
					'intro' => 'Bühne, Musik und Programm wissen wir passend zu eurem Anlass zu gestalten.',
					'items' => array(
						array(
							't' => 'Hochzeiten, Verlobungen & Familienfeste',
							'd' => 'Wir koordinieren eure Feier und stellen ein künstlerisches und musikalisches Programm zusammen, das zu eurem Geschmack passt und eurem Fest seinen eigenen Charakter gibt.',
						),
						array(
							't' => 'Firmen-, Vereins- & Institutionsevents',
							'd' => 'Planung und Umsetzung von Jubiläumsfesten, Abenden und öffentlichen Begegnungen — mit Abstimmung von Programm, Künstler:innen und Technik.',
						),
					),
				),
				array(
					'id'    => 'produktion',
					'icon'  => 'camera',
					'title' => 'Medien, Produktion & Technik',
					'intro' => 'Kamera und Technik im Dienst eines stimmigen Auftritts.',
					'items' => array(
						array(
							't' => 'Fest-Fotografie & Dokumentation',
							'd' => 'Foto- und Video-Dokumentation, die die Atmosphäre eurer Veranstaltung und ihre schönsten Momente festhält — inklusive Kurzfilmen und Inhalten zum Veröffentlichen.',
						),
						array(
							't' => 'Gestaltung & Werbematerial',
							'd' => 'Design von Plakaten, Einladungen, Programmen und digitalen wie gedruckten Materialien für ein stimmiges visuelles Auftreten eurer Veranstaltung.',
						),
						array(
							't' => 'Ton & Licht',
							'd' => 'Auswahl und Aufbau von Ton- und Lichttechnik in Abstimmung mit Raum, Bühne und Programm.',
						),
					),
				),
			),
			'past' => array(
				'title'    => 'Vergangene Projekte',
				'eyebrow'  => 'Momentaufnahmen',
				'intro'    => 'Ausschnitte aus Festivals, Konzerten, Workshops und Begegnungen, an denen wir mitgewirkt haben — ihre Spuren findet ihr in Foto und Film.',
			),
			'cta'  => array(
				'title' => 'Plant eure Veranstaltung mit uns.',
				'text'  => 'Ihr habt eine Idee für ein Fest, einen Workshop oder einen Kulturtag? Teilt eure Ideen mit uns — gemeinsam entwickeln wir ein Programm, das zu eurem Publikum und eurem Anlass passt.',
				'btn'   => 'Kontakt aufnehmen',
			),
		),

		'en' => array(
			'eyebrow' => 'Music · Dance · Art · Language · Family',
			'intro'   => 'We celebrate the diversity of Arabic culture through music, dance, art and language. We offer events, courses and hands-on experiences that open the doors of meeting, learning and creativity for everyone.',
			'nav'     => 'Jump to a topic',
			'sections'    => array(
				array(
					'id'    => 'events',
					'icon'  => 'music',
					'title' => 'Events & Concerts',
					'intro' => 'Stages and public formats that bring together music, dance and the arts — open to all and to every generation.',
					'items' => array(
						array(
							't' => 'Festivals & Culture Days',
							'd' => 'We organise festivals and days that reflect the richness of Arabic culture: performances, music and interactive activities for all ages.',
						),
						array(
							't' => 'Live Oriental Music',
							'd' => 'Concerts and evenings with singers, musicians and ensembles — classical tarab alongside contemporary sounds and rhythms.',
						),
						array(
							't' => 'Arabic DJ',
							'd' => 'A festive atmosphere of Arabic songs, oriental beats and music from around the world, with a programme tailored to your audience and occasion.',
						),
					),
				),
				array(
					'id'    => 'courses',
					'icon'  => 'cap',
					'title' => 'Courses & Workshops',
					'intro' => 'Learning and participation inside a joyful, communal setting.',
					'items' => array(
						array(
							't' => 'Dabke Classes',
							'd' => 'Courses and workshops for beginners and folk-dance lovers: the steps of dabke, its rhythms and the pleasure of dancing together.',
						),
						array(
							't' => 'Oriental Dance & Samar',
							'd' => 'Courses and workshops covering the basics of oriental dance, developing a feel for rhythm and expressing yourself through movement.',
						),
						array(
							't' => 'Oriental Music Courses',
							'd' => 'Learn to play and to sing and explore Arabic instruments, maqamat and rhythms — with programmes that suit different levels.',
						),
						array(
							't' => 'Arabic Language Classes',
							'd' => 'Courses for children and adults covering reading, writing and conversation through playful, interactive methods — for children of the Arab community and everyone who wants to learn Arabic.',
						),
					),
				),
				array(
					'id'    => 'art',
					'icon'  => 'art',
					'title' => 'Art & Exhibitions',
					'intro' => 'Spaces for visual creativity and for artists meeting their audience.',
					'items' => array(
						array(
							't' => 'Organising Art Exhibitions',
							'd' => 'Exhibitions of painting, photography and visual arts that give artists a place to show their work and meet the audience in dialogue.',
						),
						array(
							't' => 'Arabic Calligraphy & Traditional Crafts',
							'd' => 'Workshops and hands-on experiences with the beauty of Arabic calligraphy and traditional handcrafts, discovering heritage by practicing and creating.',
						),
					),
				),
				array(
					'id'    => 'cinema',
					'icon'  => 'film',
					'title' => 'Cinema & Literature',
					'intro' => 'Stories told on the screen and deepened in conversation.',
					'items' => array(
						array(
							't' => 'Film Screenings & Cinema Talks',
							'd' => 'Screenings of Arabic films and documentaries, followed by conversations with filmmakers and the audience to exchange ideas and experiences.',
						),
						array(
							't' => 'Literary & Poetry Evenings',
							'd' => 'Meetings, readings and poetic evenings with writers and creators celebrating the Arabic language and the diversity of its voices.',
						),
					),
				),
				array(
					'id'    => 'kids',
					'icon'  => 'heart',
					'title' => 'Children & Families',
					'intro' => 'Activities that blend play with creative expression and open spaces for families to meet.',
					'items' => array(
						array(
							't' => 'Cultural & Creative Activities for Children',
							'd' => 'Arabic storytelling, craft workshops and music and movement activities that combine fun with learning and stretch children’s imagination.',
						),
						array(
							't' => 'Family Events',
							'd' => 'Programmes that gather the young and the old around art, play and music, creating a shared place for families to meet and connect.',
						),
					),
				),
				array(
					'id'    => 'kitchen',
					'icon'  => 'kitchen',
					'title' => 'Arabic Kitchen',
					'intro' => 'An Arabic table full of aromas, dishes and stories.',
					'items' => array(
						array(
							't' => 'Cooking Workshops & Tastings',
							'd' => 'Experiences that explore the diversity of Arabic cooking: preparing dishes together, sharing recipes and their stories — in cooperation with chefs and restaurants.',
						),
						array(
							't' => 'Food & Music Evenings',
							'd' => 'Evenings that bring the Arab table together with music and dabke, in an atmosphere that encourages meeting and cultural exchange.',
						),
					),
				),
				array(
					'id'    => 'schools',
					'icon'  => 'school',
					'title' => 'School & Institution Programmes',
					'intro' => 'Programmes matched to ages, groups and interests.',
					'items' => array(
						array(
							't' => 'Cultural Programmes for Schools',
							'd' => 'Interactive workshops that introduce children and young people to Arabic language, art, music and traditions — matched to their ages and interests.',
						),
						array(
							't' => 'Workshops for Associations & Teams',
							'd' => 'Group activities combining music, dance and creativity that strengthen connection, participation and cultural exchange.',
						),
					),
				),
				array(
					'id'    => 'occasions',
					'icon'  => 'gift',
					'title' => 'We Plan Your Occasion',
					'intro' => 'We coordinate stage, music and programme to fit your occasion.',
					'items' => array(
						array(
							't' => 'Weddings, Engagements & Family Celebrations',
							'd' => 'We coordinate your private occasion and prepare an artistic and musical programme that matches your taste and gives your celebration its own character.',
						),
						array(
							't' => 'Corporate, Association & Institution Events',
							'd' => 'Planning and delivering annual celebrations, evenings and public meetings — with coordination of programme, artists and technical equipment.',
						),
					),
				),
				array(
					'id'    => 'produktion',
					'icon'  => 'camera',
					'title' => 'Media, Production & Equipment',
					'intro' => 'Camera and technical craft in service of a coherent event.',
					'items' => array(
						array(
							't' => 'Event Photography & Documentation',
							'd' => 'Photo and video documentation that captures the atmosphere of your event and its highlights — including short films and content ready to publish.',
						),
						array(
							't' => 'Design & Promotional Material',
							'd' => 'Design of posters, invitations, programmes and digital or printed material that gives your event a coherent visual presence.',
						),
						array(
							't' => 'Sound & Lighting',
							'd' => 'Coordinating and setting up sound and lighting systems suited to the venue, the stage and the programme.',
						),
					),
				),
			),
			'past' => array(
				'title'    => 'Past Projects',
				'eyebrow'  => 'Moments',
				'intro'    => 'A selection of festivals, concerts, workshops and encounters we have taken part in — their traces in photo and film.',
			),
			'cta'  => array(
				'title' => 'Plan your event with us.',
				'text'  => 'Do you have an idea for a celebration, a workshop or a cultural day? Share your ideas with us and together we will develop a programme that fits your audience and your occasion.',
				'btn'   => 'Get in touch',
			),
		),

		'ar' => array(
			'eyebrow' => 'الموسيقى · الرقص · الفنون · اللغة · العائلة',
			'intro'   => 'نحتفي بتنوّع الثقافة العربية من خلال الموسيقى والرقص والفنون واللغة. نقدّم فعاليات ودورات وتجارب تفاعلية تفتح أبواب اللقاء والتعلّم والإبداع للجميع.',
			'nav'     => 'انتقل إلى المحاور',
			'sections'    => array(
				array(
					'id'    => 'events',
					'icon'  => 'music',
					'title' => 'الفعاليات والحفلات',
					'intro' => 'مسرح ومناسبات عامة تجمع الموسيقى والرقص والفنون، مفتوحة للجميع ولجميع الأعمار.',
					'items' => array(
						array(
							't' => 'المهرجانات والأيام الثقافية',
							'd' => 'تنظيم مهرجانات وفعاليات تعكس ثراء الثقافة العربية، تجمع العروض الفنية والموسيقى والأنشطة التفاعلية لمختلف الأعمار.',
						),
						array(
							't' => 'الموسيقى الشرقية الحية',
							'd' => 'إحياء الحفلات والأمسيات بمشاركة مطربين وعازفين وفرق موسيقية، ببرامج تجمع الطرب الأصيل والإيقاعات المعاصرة.',
						),
						array(
							't' => 'دي جي عربي — Arabic DJ',
							'd' => 'أجواء احتفالية تجمع الأغاني العربية والإيقاعات الشرقية والموسيقى العالمية، مع اختيار البرنامج بما يناسب جمهوركم ومناسبتكم.',
						),
					),
				),
				array(
					'id'    => 'courses',
					'icon'  => 'cap',
					'title' => 'الدورات وورش العمل',
					'intro' => 'دورات تفتح باب التعلّم والمشاركة في أجواء جماعية ممتعة.',
					'items' => array(
						array(
							't' => 'تعليم الدبكة',
							'd' => 'دورات وورش للمبتدئين ومحبي الرقص الشعبي، لتعلّم خطوات الدبكة وإيقاعاتها والاستمتاع بالرقص الجماعي.',
						),
						array(
							't' => 'تعليم الرقص الشرقي والسماح',
							'd' => 'دورات وورش لتعلّم أساسيات الرقص الشرقي، وتنمية الإحساس بالإيقاع والتعبير بالحركة.',
						),
						array(
							't' => 'دورات الموسيقى الشرقية',
							'd' => 'تعلّم العزف والغناء والتعرّف إلى الآلات والمقامات والإيقاعات الشرقية، ضمن برامج تناسب مستويات مختلفة.',
						),
						array(
							't' => 'تعليم اللغة العربية',
							'd' => 'دورات للأطفال والكبار تشمل القراءة والكتابة والمحادثة بأساليب تفاعلية ممتعة، لأبناء الجالية العربية ولغير الناطقين بها.',
						),
					),
				),
				array(
					'id'    => 'art',
					'icon'  => 'art',
					'title' => 'الفنون والمعارض',
					'intro' => 'مساحات للإبداع البصري ولقاء الفنانين بجمهورهم.',
					'items' => array(
						array(
							't' => 'تنظيم المعارض الفنية',
							'd' => 'معارض للرسم والتصوير والفنون البصرية، تتيح للفنانين عرض أعمالهم والتواصل مع الجمهور.',
						),
						array(
							't' => 'الخط العربي والحرف التقليدية',
							'd' => 'ورش وتجارب عملية للتعرّف إلى جمال الخط العربي والحرف اليدوية، واكتشاف التراث من خلال الممارسة والإبداع.',
						),
					),
				),
				array(
					'id'    => 'cinema',
					'icon'  => 'film',
					'title' => 'السينما والأدب',
					'intro' => 'قصص تُروى على الشاشة وتتعمّق في الحوار.',
					'items' => array(
						array(
							't' => 'عروض الأفلام واللقاءات السينمائية',
							'd' => 'عروض لأفلام عربية ووثائقية، تتبعها حوارات مع صنّاع الأفلام والجمهور لتبادل الأفكار والتجارب.',
						),
						array(
							't' => 'الأمسيات الأدبية والشعرية',
							'd' => 'لقاءات وقراءات شعرية وحوارات مع كتّاب ومبدعين، تحتفي باللغة العربية وتنوّع أصواتها.',
						),
					),
				),
				array(
					'id'    => 'kids',
					'icon'  => 'heart',
					'title' => 'الأطفال والعائلات',
					'intro' => 'نشاطات تجمع اللعب بالتعبير والجمال، وتفتح للعائلات مساحة للقاء.',
					'items' => array(
						array(
							't' => 'أنشطة ثقافية وإبداعية للأطفال',
							'd' => 'حكايات عربية وأشغال يدوية وأنشطة موسيقية وحركية تجمع المتعة والتعلّم وتنمّي خيال الأطفال.',
						),
						array(
							't' => 'فعاليات عائلية',
							'd' => 'برامج تجمع الصغار والكبار حول الفن واللعب والموسيقى، وتخلق مساحة للقاء والتواصل بين العائلات.',
						),
					),
				),
				array(
					'id'    => 'kitchen',
					'icon'  => 'kitchen',
					'title' => 'المطبخ العربي',
					'intro' => 'مائدة عربية غنية بالنكهات والأطباق وحكاياتها.',
					'items' => array(
						array(
							't' => 'ورش الطبخ وتجارب التذوّق',
							'd' => 'تجارب للتعرّف إلى تنوّع المطبخ العربي، من خلال إعداد الأطباق وتبادل وصفاتها وحكاياتها، بالتعاون مع طهاة ومطاعم.',
						),
						array(
							't' => 'أمسيات الطعام والموسيقى',
							'd' => 'لقاءات تجمع المائدة العربية بالموسيقى والدبكة، في أجواء تشجّع التعارف والتبادل الثقافي.',
						),
					),
				),
				array(
					'id'    => 'schools',
					'icon'  => 'school',
					'title' => 'برامج المدارس والمؤسسات',
					'intro' => 'برامج تناسب الأعمار والمجموعات والاهتمامات.',
					'items' => array(
						array(
							't' => 'برامج ثقافية للمدارس',
							'd' => 'ورش تفاعلية تعرّف الأطفال والشباب باللغة والفنون والموسيقى والتقاليد العربية، بما يناسب أعمارهم واهتماماتهم.',
						),
						array(
							't' => 'ورش للجمعيات وفرق العمل',
							'd' => 'أنشطة جماعية تجمع الموسيقى والرقص والإبداع، لتعزيز التواصل والمشاركة والتبادل الثقافي.',
						),
					),
				),
				array(
					'id'    => 'occasions',
					'icon'  => 'gift',
					'title' => 'تنظيم مناسباتكم',
					'intro' => 'ننسّق المسرح والموسيقى والبرنامج بما يناسب مناسبتكم.',
					'items' => array(
						array(
							't' => 'الأعراس والخطوبة والاحتفالات العائلية',
							'd' => 'تنسيق مناسباتكم الخاصة وإعداد برامج فنية وموسيقية تناسب ذوقكم وتمنح احتفالكم طابعه الخاص.',
						),
						array(
							't' => 'فعاليات الشركات والجمعيات والمؤسسات',
							'd' => 'تخطيط وتنفيذ الاحتفالات السنوية والأمسيات واللقاءات العامة، مع تنسيق البرنامج والفنانين والتجهيزات.',
						),
					),
				),
				array(
					'id'    => 'produktion',
					'icon'  => 'camera',
					'title' => 'التصوير والإنتاج والتجهيزات',
					'intro' => 'الكاميرا والتجهيز الفني في خدمة فعالية متناسقة.',
					'items' => array(
						array(
							't' => 'تصوير وتوثيق الفعاليات',
							'd' => 'تصوير فوتوغرافي وفيديو يوثّق أجواء مناسبتكم وأبرز لحظاتها، مع إعداد أفلام قصيرة ومحتوى للنشر.',
						),
						array(
							't' => 'التصميم والمواد الدعائية',
							'd' => 'تصميم بوسترات ودعوات وبرامج ومواد رقمية ومطبوعة تمنح فعاليتكم حضورًا بصريًا متناسقًا.',
						),
						array(
							't' => 'الصوت والإضاءة',
							'd' => 'تنسيق وتجهيز أنظمة الصوت والإضاءة بما يناسب المكان والعروض والبرنامج.',
						),
					),
				),
			),
			'past' => array(
				'title'    => 'مشاريعنا السابقة',
				'eyebrow'  => 'لحظات من أعمالنا',
				'intro'    => 'اكتشفوا مجموعة من الفعاليات والحفلات والمعارض وورش العمل التي شاركنا في تنظيمها وتنفيذها، وتعرّفوا إلى قصصها من خلال الصور والفيديو.',
			),
			'cta'  => array(
				'title' => 'خطّطوا فعاليتكم معنا',
				'text'  => 'هل لديكم فكرة لحفل أو ورشة أو يوم ثقافي؟ شاركونا أفكاركم لنطوّر معًا برنامجًا يناسب جمهوركم ومناسبتكم.',
				'btn'   => 'تواصلوا معنا',
			),
		),
	);

	return isset( $copy[ $lang ] ) ? $copy[ $lang ] : $copy['de'];
}
