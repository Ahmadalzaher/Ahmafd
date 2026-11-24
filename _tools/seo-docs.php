<?php
/**
 * Generate docs/seo.md from the live Rank Math meta data.
 *
 * Usage: php wp-cli.phar eval-file _tools/seo-docs.php > ..\docs\seo.md
 */

$map   = yd_page_map();
$langs = array( 'de', 'en', 'ar' );

$page_groups = array(
	'home'     => 'Startseite / Home',
	'festival' => 'Festival',
	'program'  => 'Programm / Programme',
	'events'   => 'Termine / Events',
	'articles' => 'Artikel / Articles',
	'about'    => 'Über uns / About us',
	'contact'  => 'Kontakt / Contact',
	'press'    => 'Presse / Press',
	'privacy'  => 'Datenschutz / Privacy',
	'imprint'  => 'Impressum / Legal notice',
	'cookies'  => 'Cookies',
);

$post_groups = array(
	'article-dabke'       => 'Artikel: Dabke braucht keine Einladung',
	'article-exile'       => 'Artikel: Kultur im Exil',
	'article-calligraphy' => 'Artikel: Kalligrafie',
	'article-music'       => 'Artikel: Arabische Musik',
);

$event_ids = array(
	'event-2026-11-05' => array( 94, 95, 96 ),
	'event-2026-11-19' => array( 97, 98, 99 ),
	'event-2026-12-03' => array( 100, 101, 102 ),
	'event-2026-12-17' => array( 103, 104, 105 ),
);

$post_ids = array(
	'article-dabke'       => array( 145, 146, 147 ),
	'article-exile'       => array( 148, 149, 150 ),
	'article-calligraphy' => array( 151, 152, 153 ),
	'article-music'       => array( 154, 155, 156 ),
);

$clean = static function ( $value ) {
	return str_replace( '|', '\|', (string) $value );
};

$out = array();

$out[] = '# SEO-Konfiguration / SEO configuration / إعدادات SEO';
$out[] = '';
$out[] = 'Alle Titel, Beschreibungen und Fokus-Keywords liegen in Rank Math und sind pro Seite und pro Sprache einzeln gepflegt. Diese Tabelle ist die Quelle der Wahrheit für die Redaktion: sie beschreibt, was aktuell ausgeliefert wird.';
$out[] = '';
$out[] = 'All titles, descriptions and focus keywords live in Rank Math and are maintained per page and per language. This table is the editorial reference for what the site currently outputs.';
$out[] = '';
$out[] = 'جميع العناوين والأوصاف والكلمات المفتاحية محفوظة في إضافة Rank Math وتُدار لكل صفحة ولكل لغة على حدة. هذا الجدول مرجع التحرير لما يُخرجه الموقع حالياً.';
$out[] = '';
$out[] = '## Aufteilung / Division of labour';
$out[] = '';
$out[] = '| Zuständig | Was |';
$out[] = '| --- | --- |';
$out[] = '| Rank Math | `<title>`, Meta-Description, Open Graph, Twitter Cards, Canonical, Robots-Meta, XML-Sitemaps |';
$out[] = '| Theme (`inc/seo.php`) | hreflang-Cluster (DE/EN/AR + x-default), JSON-LD-Graph (Organization, WebSite, BreadcrumbList, Article, Event) |';
$out[] = '';
$out[] = 'Rank Maths eigenes JSON-LD ist über `inc/rank-math.php` abgeschaltet (`rank_math/json_ld`), damit kein Schema doppelt erscheint. Die Sitemap-Konfiguration liegt in Rank Math, nicht im Theme.';
$out[] = '';
$out[] = '## Organization / NAP (alle Sprachen)';
$out[] = '';
$out[] = '| Feld | Wert | Pflege |';
$out[] = '| --- | --- | --- |';
$out[] = '| name | ' . $clean( get_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' ) ) . ' | Customizer → Festival-Identität → Name & Kontakt |';
$out[] = '| alternateName | Arap Culture | Theme (`inc/seo.php`) |';
$out[] = '| email | ' . $clean( yd_contact( 'email' ) ) . ' | Customizer → Name & Kontakt |';
$out[] = '| telephone | ' . $clean( yd_contact( 'phone' ) ) . ' | Customizer → Name & Kontakt |';
$out[] = '| streetAddress | ' . $clean( yd_contact( 'street' ) ) . ' | Customizer → Name & Kontakt |';
$out[] = '| postalCode | ' . $clean( yd_contact( 'postal' ) ) . ' | Customizer → Name & Kontakt |';
$out[] = '| addressLocality | ' . $clean( yd_contact( 'city' ) ) . ' | Customizer → Name & Kontakt |';
$out[] = '| addressCountry | DE | Theme (`inc/seo.php`) |';
$out[] = '| founder | Ahmad AL Zaher | Theme (`inc/seo.php`) |';
$out[] = '| sameAs | Instagram, Facebook, YouTube | `yd_socials()` in `inc/template-tags.php` |';
$out[] = '';

foreach ( $langs as $lang ) {
	$label = strtoupper( $lang );

	$out[] = '## ' . $label . ' — Seiten / Pages';
	$out[] = '';
	$out[] = '| Seite | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |';
	$out[] = '| --- | --- | --- | --- | --- | --- |';

	foreach ( $page_groups as $group => $name ) {
		if ( ! isset( $map[ $lang ][ $group ] ) ) {
			continue;
		}

		$id   = (int) $map[ $lang ][ $group ];
		$url  = get_permalink( $id );
		$path = wp_parse_url( $url, PHP_URL_PATH );

		$out[] = sprintf(
			'| %s | `%s` | %s | %s | %s | Organization, WebSite, BreadcrumbList |',
			$clean( $name ),
			$clean( $path ),
			$clean( get_post_meta( $id, 'rank_math_focus_keyword', true ) ),
			$clean( get_post_meta( $id, 'rank_math_title', true ) ),
			$clean( get_post_meta( $id, 'rank_math_description', true ) )
		);
	}

	$out[] = '';
	$out[] = '## ' . $label . ' — Artikel / Articles';
	$out[] = '';
	$out[] = '| Artikel | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |';
	$out[] = '| --- | --- | --- | --- | --- | --- |';

	foreach ( $post_groups as $group => $name ) {
		$ids = $post_ids[ $group ];
		$id  = (int) $ids[ array_search( $lang, $langs, true ) ];
		$path = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );

		$out[] = sprintf(
			'| %s | `%s` | %s | %s | %s | Organization, WebSite, BreadcrumbList, Article |',
			$clean( $name ),
			$clean( $path ),
			$clean( get_post_meta( $id, 'rank_math_focus_keyword', true ) ),
			$clean( get_post_meta( $id, 'rank_math_title', true ) ),
			$clean( get_post_meta( $id, 'rank_math_description', true ) )
		);
	}

	$out[] = '';
	$out[] = '## ' . $label . ' — Termine / Events';
	$out[] = '';
	$out[] = '| Termin | URL | Fokus-Keyword | SEO-Titel | Meta-Description | Schema |';
	$out[] = '| --- | --- | --- | --- | --- | --- |';

	foreach ( $event_ids as $group => $ids ) {
		$id   = (int) $ids[ array_search( $lang, $langs, true ) ];
		$path = wp_parse_url( get_permalink( $id ), PHP_URL_PATH );
		$date = get_post_meta( $id, '_yd_date', true );

		$out[] = sprintf(
			'| %s | `%s` | %s | %s | %s | Organization, WebSite, BreadcrumbList, Event |',
			$clean( 'Dabke ' . $date ),
			$clean( $path ),
			$clean( get_post_meta( $id, 'rank_math_focus_keyword', true ) ),
			$clean( get_post_meta( $id, 'rank_math_title', true ) ),
			$clean( get_post_meta( $id, 'rank_math_description', true ) )
		);
	}

	$out[] = '';
}

$out[] = '## Pflege im Backend / Editing in wp-admin';
$out[] = '';
$out[] = '1. `Beiträge` → Seite/Artikel/Termin öffnen.';
$out[] = '2. Im Block **Rank Math SEO** (unter dem Editor) das Feld **Focus Keyword** prüfen und Titel sowie Description überschreiben. Die Felder sind pro Übersetzung eigene Beiträge, also dreimal pflegen.';
$out[] = '3. Titel-Templates unter `Rank Math SEO → Titles & Meta` ändern, wenn der Aufbau (`%title% %sep% %sitename%`) für alle Seiten gelten soll. Die hier dokumentierten Titel sind individuelle Overrides und gewinnen immer.';
$out[] = '4. Nach dem Speichern cached Rank Math die Sitemap. Unter `Rank Math SEO → Sitemap Settings` auf **Regenerate** klicken (oder `wp rank-math sitemap generate`).';
$out[] = '';
$out[] = '## Google Search Console';
$out[] = '';
$out[] = '1. In der Search Console eine Property für die Domain anlegen (Domain-Property, nicht URL-Präfix), damit DE, EN und AR gemeinsam gemessen werden.';
$out[] = '2. `Sitemap` → `https://domain.tld/sitemap_index.xml` einreichen. Die Indexdatei listet `page-sitemap.xml`, `post-sitemap.xml` und `yd_event-sitemap.xml`.';
$out[] = '3. Unter `Internationale Targeting` prüfen, dass Google die hreflang-Angaben erkennt. Die Zuordnung kommt aus dem Theme, nicht aus Rank Math.';
$out[] = '4. Nach dem Launch `URL-Prüfung` für die drei Startseiten (`/`, `/en/`, `/ar/`) und je einen Termin nutzen und die Indexierung beantragen.';
$out[] = '';
$out[] = '## Was hier bewusst nicht steht';
$out[] = '';
$out[] = '- Keine erfundenen Bewertungen (`aggregateRating`) und keine Fake-Rezensionen im Event-Schema.';
$out[] = '- Keine Platzhalter-Termine: die vier Kurse (05.11., 19.11., 03.12., 17.12.2026, 18:15–19:45, Goldbekhaus) sind bestätigt (Stand 28.10.2026); zwei ältere Seed-Termine liegen als Entwurf vor.';
$out[] = '- Beiträge und Termine liegen sprachübergreifend unter derselben URL-Struktur (`/dabke-…/`, `/event/…/`), nicht unter `/en/` oder `/ar/`. Die Sprachzuordnung passiert über hreflang und Canonical, nicht über den Pfad.';
$out[] = '- Keine Drittanbieter-Requests beim Laden: Schriften liegen lokal (`assets/fonts/`, OFL), YouTube lädt erst nach Einwilligung. Google Fonts, `gstatic` und ähnliche Hosts kommen im Quelltext nicht vor.';
$out[] = '- Keine Garantie auf Ranking 1. Die Tabelle dokumentiert die technische Grundlage, nicht ein Versprechen.';
$out[] = '';

$markdown = implode( "\n", $out );
$targets  = array(
	// Ships with the theme so the table travels with the ZIP.
	__DIR__ . '/../wordpress/wp-content/themes/yalla/docs/seo.md',
	// Project documentation folder.
	dirname( __DIR__ ) . '/docs/seo.md',
);

foreach ( $targets as $target ) {
	if ( ! is_dir( dirname( $target ) ) ) {
		mkdir( dirname( $target ), 0777, true );
	}

	file_put_contents( $target, $markdown );
	echo 'written ' . $target . ' (' . strlen( $markdown ) . " bytes)\n";
}
