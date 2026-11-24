<?php
/**
 * Plugin Name: Adwaa Seiten
 * Description: Legt die Seiten "Leistungen" und "Preise" an (einmalig, idempotent) und haengt sie ins Hauptmenue.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gemeinsames CSS fuer die beiden neuen Seiten (Tabellen + Abstaende auf Mobil).
 */
function adwaa_pages_style() {
	return '<style>
		.adwaa-prices h2 { margin-top: 2.2em; }
		.adwaa-prices table { width: 100%; border-collapse: collapse; margin: 0 0 1.6em; font-size: .95em; }
		.adwaa-prices th, .adwaa-prices td { text-align: left; padding: .62em .7em; border-bottom: 1px solid rgba(20,20,32,.14); vertical-align: top; }
		.adwaa-prices th { font-weight: 600; background: rgba(20,20,32,.045); }
		.adwaa-prices td:last-child, .adwaa-prices th:last-child { text-align: right; white-space: nowrap; }
		.adwaa-prices .adwaa-note { font-size: .9em; opacity: .78; }
		@media (max-width: 767px) {
			.adwaa-prices table { font-size: .9em; }
			.adwaa-prices th, .adwaa-prices td { padding: .5em .4em; }
			.adwaa-prices td:last-child, .adwaa-prices th:last-child { white-space: normal; }
			.adwaa-prices h2 { font-size: 1.45em; margin-top: 1.8em; }
		}
	</style>';
}

/**
 * Inhalt der Seite "Leistungen" - ausschliesslich aus den bereits auf der
 * Website genannten Leistungen und Formulierungen abgeleitet.
 */
function adwaa_page_leistungen_html() {
	return adwaa_pages_style() . <<<'HTML'
<div class="adwaa-prices">
<h1 class="adwaa-page-title">Leistungen</h1>
<p>Adwaa Creative Advertising &amp; Media ist in Hamburg zu Hause und deutschlandweit sowie in Europa unterwegs. Zum Angebot gehören Hochzeitsfilme, Eventfilme, Imagefilme und Unternehmensvideos sowie Fotografie, Videoschnitt und Grafikdesign – individuell geplant und passend zu eurer Geschichte und Marke.</p>
<p>Jede Zusammenarbeit beginnt mit einem Gespräch: Wir möchten verstehen, was euch wichtig ist und welche Stimmung euer Film vermitteln soll. Daraus entsteht ein Konzept – mit sorgfältiger Vorbereitung, einem aufmerksamen Blick beim Dreh und viel Feingefühl in der Postproduktion.</p>

<h2>Hochzeitsfilm</h2>
<p>Echte Menschen, besondere Momente und die Geschichten dahinter – authentisch, aufmerksam und mit viel Liebe zum Detail festgehalten. Von der ersten Idee bis zum fertigen Film begleiten wir euer Projekt persönlich.</p>

<h2>Dokumentarfilm &amp; Reportage</h2>
<p>Geschichten, die wirklich passiert sind: dokumentarische Formate mit ruhiger Beobachtung, sorgfältigem Schnitt und viel Feingefühl in der Postproduktion.</p>

<h2>Imagefilm &amp; Unternehmensvideo</h2>
<p>Ein Film, der zeigt, wofür ein Unternehmen steht – geplant entlang eurer Marke, eurer Zielgruppe und der Stimmung, die ihr vermitteln wollt.</p>

<h2>Eventfilm</h2>
<p>Die Atmosphäre eines Events in bewegten Bildern: Höhepunkte, Stimmung und die Menschen, die dabei waren – als Highlight-Video oder als ausführliche Dokumentation.</p>

<h2>Fotografie</h2>
<p>Hochzeits- und Projektfotografie mit professioneller Bearbeitung – abgestimmt auf Anlass, Location und den gewünschten Bildlook.</p>

<h2>Videoschnitt &amp; Postproduktion</h2>
<p>Schnitt, Farbkorrektur, Tonbearbeitung, Untertitel und Thumbnails – auch für bereits vorhandenes Material. So wird aus Rohmaterial ein fertiger Film.</p>

<h2>Motion Graphics &amp; Grafikdesign</h2>
<p>Logo-Animationen, 2D-Motion-Graphics, Erklärvideos und animierte Social-Media-Anzeigen. Dazu Grafikdesign von Social-Media-Post und Flyer bis zur kompletten Markenidentität.</p>

<h2>So läuft ein Projekt ab</h2>
<ol>
<li><strong>Gespräch &amp; Konzept:</strong> Wir klären Ziel, Stimmung und Umfang.</li>
<li><strong>Vorbereitung &amp; Dreh:</strong> Sorgfältige Planung, aufmerksamer Blick vor Ort.</li>
<li><strong>Postproduktion:</strong> Schnitt, Farbkorrektur, Ton und alles, was den Film rund macht.</li>
<li><strong>Lieferung:</strong> Fertige Dateien in den Formaten, die ihr braucht.</li>
</ol>

<p><a href="/preise/">Preise und Richtpreise ansehen</a> &nbsp;·&nbsp; <a href="/kontakt/">Projekt anfragen</a></p>
</div>
HTML;
}

/**
 * Inhalt der Seite "Preise" - 1:1 die Preise, die bereits auf "Über uns" stehen.
 */
function adwaa_page_preise_html() {
	$style = adwaa_pages_style();

	$html  = $style . '<div class="adwaa-prices">';
	$html .= '<h1 class="adwaa-page-title">Preise</h1>';
	$html .= '<p>Hier findet ihr eine Übersicht unserer Leistungen und Richtpreise. Das passende Angebot erstellen wir individuell nach Umfang und Aufwand.</p>';

	$html .= '<h2>Designleistungen</h2><table><thead><tr><th>Leistung</th><th>Preis</th></tr></thead><tbody>';
	$design = array(
		'Social-Media-Post'        => '25–50 €',
		'Story'                    => '20–35 €',
		'Anzeige / Sponsored Ad'   => '40–80 €',
		'Flyer'                    => '80–180 €',
		'Facebook-Titelbild'       => '40–80 €',
		'Broschüre'                => '180–500 €',
		'Visitenkarte'             => '60–150 €',
		'Roll-up'                  => 'ab 100 €',
		'Banner'                   => 'ab 80 €',
		'Speisekarte'              => '180–450 €',
		'Sticker'                  => '120–300 €',
		'Verpackungsdesign'        => '400–1.200 €',
		'Mock-up'                  => '50–120 €',
		'Logo'                     => '400–1.200 €',
	);
	foreach ( $design as $name => $price ) {
		$html .= '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
	}
	$html .= '</tbody></table>';

	$html .= '<h2>Markenidentität-Pakete</h2><table><tbody>';
	$brand = array(
		'Brand Basic'    => '800 €',
		'Brand Standard' => '1.500 €',
		'Brand Premium'  => '2.500–4.500 €',
	);
	foreach ( $brand as $name => $price ) {
		$html .= '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
	}
	$html .= '</tbody></table><p class="adwaa-note">Je nach Paket können Logo, Farbpalette, Typografie, Markenrichtlinien und Geschäftsausstattung enthalten sein.</p>';

	$html .= '<h2>Videoschnitt</h2><table><thead><tr><th>Leistung</th><th>Preis</th></tr></thead><tbody>';
	$video = array(
		'Reel / TikTok / Short (15–60 Sek.)'  => '50–120 €',
		'Social-Media-Video (1–2 Min.)'       => '120–250 €',
		'YouTube-Video (5–10 Min.)'           => '200–500 €',
		'YouTube-Video (10–20 Min.)'          => '400–900 €',
		'Podcast-Schnitt'                     => '150–500 €',
		'Unternehmensvideo'                   => '400–1.500 €',
		'Werbevideo'                          => '500–2.000 €',
		'Event-Highlight-Video'               => '300–900 €',
		'Slideshow-Video'                     => '80–200 €',
		'Untertitel'                          => '30–100 €',
		'Farbkorrektur &amp; Color Grading'    => '80–300 €',
		'Tonbearbeitung &amp; Audiomischung'   => '50–250 €',
		'Intro-/Outro-Animation'              => '100–300 €',
		'Thumbnail-Design'                    => '30–70 €',
	);
	foreach ( $video as $name => $price ) {
		$html .= '<tr><td>' . wp_kses_post( $name ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
	}
	$html .= '</tbody></table>';

	$html .= '<h2>Motion Graphics</h2><table><tbody>';
	$motion = array(
		'Logo-Animation'                       => '150–400 €',
		'2D-Motion-Graphics (30 Sek.)'         => '300–800 €',
		'Erklärvideo (60–90 Sek.)'             => '800–2.500 €',
		'Animierte Social-Media-Anzeige'       => '250–700 €',
		'Kinetic Typography'                   => '200–600 €',
	);
	foreach ( $motion as $name => $price ) {
		$html .= '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
	}
	$html .= '</tbody></table>';

	$html .= '<h2>Monatliche Design-Pakete</h2><table><tbody>';
	$monthly = array(
		'Starter – 10 Designs pro Monat'   => '350 €',
		'Basic – 15 Designs pro Monat'     => '500 €',
		'Standard – 20 Designs pro Monat'  => '700 €',
		'Premium – 30 Designs pro Monat'   => '950 €',
	);
	foreach ( $monthly as $name => $price ) {
		$html .= '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
	}
	$html .= '</tbody></table>';

	$html .= '<h2>Hochzeitsfotografie-Pakete</h2><table><thead><tr><th>Paket</th><th>Leistungen</th><th>Preis</th></tr></thead><tbody>';
	$html .= '<tr><td><strong>Paket 1</strong><br>Basic Shooting</td><td>60 Minuten Fotoshooting vor Ort<br>20 Bilder<br>Professionelle Bearbeitung</td><td>150 €</td></tr>';
	$html .= '<tr><td><strong>Paket 2</strong><br>Classic Shooting</td><td>90 Minuten Fotoshooting vor Ort<br>Alle Fotos inklusive – Sie wählen die Bilder zur Bearbeitung aus<br>30 Bilder<br>Fotodruck der Bilder<br>Professionelle Bearbeitung</td><td>400 €</td></tr>';
	$html .= '<tr><td><strong>Paket 3</strong><br>Premium Shooting</td><td>2 Stunden Fotoshooting vor Ort<br>Alle Fotos inklusive – Sie wählen die Bilder zur Bearbeitung aus<br>35 Bilder<br>Fotodruck der Bilder<br>Panorama-Album, 2 kleine Alben und Poster<br>Professionelle Bearbeitung</td><td>550 €</td></tr>';
	$html .= '<tr><td><strong>Paket 4</strong><br>Foto &amp; Video</td><td>2 Stunden Fotoshooting vor Ort<br>Alle Fotos inklusive – Sie wählen die Bilder zur Bearbeitung aus<br>35 Bilder<br>Fotodruck der Bilder<br>Trailer-Video</td><td>650 €</td></tr>';
	$html .= '</tbody></table>';

	$html .= '<p class="adwaa-note">Alle Preise sind in Euro angegeben und können je nach Projektumfang und Komplexität variieren. Hochzeitsfilm-Pakete und individuelle Projekte kalkulieren wir gern auf Anfrage.</p>';
	$html .= '<p><a href="/kontakt/">Individuelles Angebot anfragen</a></p>';
	$html .= '</div>';

	return $html;
}

/**
 * Einmalige Einrichtung: Seiten anlegen und Menuepunkte ergaenzen.
 */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['adwaa-seed'] ) ) {
		return;
	}

	$pages = array(
		'leistungen' => array(
			'title' => 'Leistungen',
			'html'  => adwaa_page_leistungen_html(),
			'seo'   => array(
				'title'       => 'Leistungen – Videoproduktion, Foto &amp; Design | Adwaa Hamburg',
				'description' => 'Leistungen von Adwaa in Hamburg: Hochzeitsfilm, Dokumentarfilm, Imagefilm, Eventfilm, Fotografie, Videoschnitt, Motion Graphics und Grafikdesign.',
			),
		),
		'preise'     => array(
			'title' => 'Preise',
			'html'  => adwaa_page_preise_html(),
			'seo'   => array(
				'title'       => 'Preise – Design &amp; Videoproduktion | Adwaa Hamburg',
				'description' => 'Richtpreise für Design, Videoschnitt, Motion Graphics und Hochzeitsfotografie bei Adwaa in Hamburg. Individuelles Angebot nach Umfang und Aufwand.',
			),
		),
	);

	$ids = array();

	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$id = $existing->ID;
			wp_update_post(
				array(
					'ID'           => $id,
					'post_title'   => $data['title'],
					'post_content' => $data['html'],
				)
			);
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $data['title'],
					'post_name'    => $slug,
					'post_content' => $data['html'],
				)
			);
		}

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		update_post_meta( $id, 'rank_math_title', $data['seo']['title'] );
		update_post_meta( $id, 'rank_math_description', $data['seo']['description'] );
		$ids[ $slug ] = (int) $id;
	}

	// Menue "menu-01" ergaenzen und neu sortieren.
	$menu = wp_get_nav_menu_object( 'menu-01' );

	if ( ! $menu ) {
		$menus = wp_get_nav_menus();
		$first = ! empty( $menus ) ? $menus[0] : null;
		$menu  = $first;
	}

	$report = array( 'pages' => $ids, 'menu' => null );

	if ( $menu ) {
		$items   = wp_get_nav_menu_items( $menu->term_id );
		$by_slug = array();

		foreach ( (array) $items as $item ) {
			$object = get_post( (int) $item->object_id );

			if ( $object ) {
				$by_slug[ $object->post_name ] = $item;
			}
		}

		foreach ( $ids as $slug => $id ) {
			if ( isset( $by_slug[ $slug ] ) ) {
				continue;
			}

			wp_update_nav_menu_item(
				$menu->term_id,
				0,
				array(
					'menu-item-title'     => $pages[ $slug ]['title'],
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}

		// Reihenfolge: Home, Über uns, Leistungen, Presse, Preise, Kontakt
		$order = array( 'home' => 1, 'uber-uns' => 2, 'leistungen' => 3, 'presse' => 4, 'preise' => 5, 'kontakt' => 6 );
		$items = wp_get_nav_menu_items( $menu->term_id );

		foreach ( (array) $items as $item ) {
			$object = get_post( (int) $item->object_id );
			$slug   = $object ? $object->post_name : '';

			if ( '' === $slug && 'page' === $item->object && (int) $item->object_id === (int) get_option( 'page_on_front' ) ) {
				$slug = 'home';
			}

			if ( isset( $order[ $slug ] ) ) {
				wp_update_post(
					array(
						'ID'         => $item->ID,
						'menu_order' => $order[ $slug ],
					)
				);
			}
		}

		$report['menu'] = $menu->name;
	}

	update_option( 'adwaa_pages_seeded', gmdate( 'c' ) );

	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	echo '<pre>' . esc_html( print_r( $report, true ) ) . '</pre>';
	exit;
}, 3 );
