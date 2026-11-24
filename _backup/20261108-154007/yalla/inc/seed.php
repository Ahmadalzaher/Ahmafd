<?php
/**
 * One-time content import: pages, events, formats, team, partners, articles.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once YALLA_DIR . '/inc/demo-pages.php';
require_once YALLA_DIR . '/inc/demo-items.php';
require_once YALLA_DIR . '/inc/demo-articles.php';

/**
 * Resolve the PDF placeholders in the demo copy.
 *
 * @param string $content Raw content.
 * @return string
 */
function yd_demo_replace( $content ) {
	return str_replace(
		array( '%PDF2024%', '%PDF2022%' ),
		array( yd_asset( 'pdf/programm-2024.pdf' ), yd_asset( 'pdf/programm-2022.pdf' ) ),
		$content
	);
}

/**
 * Take over a page that already uses the intended slug.
 *
 * On a site that already had pages (a relaunch, for example) the old page owns
 * the slug. Creating a second page would give it a "-2" suffix and split the
 * navigation. Instead the existing page is marked as this group's page and
 * kept as it is.
 *
 * @param string $slug  Intended slug.
 * @param string $lang  Language.
 * @param string $group Translation group.
 * @return int Page ID or 0.
 */
function yd_seed_adopt_page( $slug, $lang, $group ) {
	$candidates = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private', 'draft', 'pending', 'future' ),
			'posts_per_page' => 10,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'name'           => $slug,
			'no_found_rows'  => true,
		)
	);

	foreach ( $candidates as $candidate ) {
		if ( yd_page_lang( $candidate ) !== $lang ) {
			continue;
		}
		if ( get_post_meta( $candidate->ID, '_yd_group', true ) ) {
			continue;
		}

		update_post_meta( $candidate->ID, '_yd_group', $group );

		return (int) $candidate->ID;
	}

	return 0;
}

/**
 * Find an existing imported item so a repeated import does not duplicate it.
 *
 * Pages and events are guarded by other means; the remaining post types are
 * matched by title (and, for articles, by slug) within the same language.
 *
 * @param string $post_type Post type.
 * @param string $title     Post title.
 * @param string $lang      Language code, empty to ignore language.
 * @param string $slug      Optional slug.
 * @return int Post ID or 0 when nothing was found.
 */
function yd_seed_lookup( $post_type, $title, $lang = '', $slug = '' ) {
	$base = array(
		'post_type'      => $post_type,
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);

	if ( $lang ) {
		$base['tax_query'] = array(
			array(
				'taxonomy' => 'yd_lang',
				'field'    => 'slug',
				'terms'    => $lang,
			),
		);
	}

	$lookups = array();

	if ( '' !== $title ) {
		$lookups[] = array( 'title' => $title );
	}
	if ( '' !== $slug ) {
		$lookups[] = array( 'name' => $slug );
	}

	foreach ( $lookups as $lookup ) {
		$ids = get_posts( array_merge( $base, $lookup ) );

		if ( $ids ) {
			return (int) $ids[0];
		}
	}

	return 0;
}

/**
 * Is there already an event at this date and time in this language?
 *
 * @param string $date Date (Y-m-d).
 * @param string $time Start time.
 * @param string $lang Language code.
 * @return int Post ID or 0.
 */
function yd_seed_event_exists( $date, $time, $lang ) {
	$ids = get_posts(
		array(
			'post_type'      => 'yd_event',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => '_yd_date',
					'value' => $date,
				),
				array(
					'key'   => '_yd_time',
					'value' => $time,
				),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'yd_lang',
					'field'    => 'slug',
					'terms'    => $lang,
				),
			),
		)
	);

	return $ids ? (int) $ids[0] : 0;
}

/**
 * Create a page if its translation group does not exist yet.
 *
 * @param array $page   Page definition.
 * @param int   $parent Parent page ID.
 * @return int Page ID.
 */
function yd_seed_page( $page, $parent = 0, &$created = null ) {
	$created = false;
	$map = yd_page_map();
	$lang = $page['lang'];
	$group = $page['group'];

	if ( isset( $map[ $lang ][ $group ] ) ) {
		return (int) $map[ $lang ][ $group ];
	}

	// A page with this slug may belong to the site's previous version.
	$adopted = yd_seed_adopt_page( $page['slug'], $lang, $group );

	if ( $adopted ) {
		wp_cache_delete( 'yd_page_map', 'yalla' );

		return $adopted;
	}

	$content = yd_demo_replace( isset( $page['content'] ) ? $page['content'] : '' );

	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $content,
			'post_excerpt' => isset( $page['excerpt'] ) ? $page['excerpt'] : '',
			'post_parent'  => $parent,
			'menu_order'   => isset( $page['menu_order'] ) ? $page['menu_order'] : 0,
		),
		true
	);

	if ( is_wp_error( $id ) ) {
		return 0;
	}

	update_post_meta( $id, '_yd_group', $group );
	if ( ! empty( $page['hidden'] ) ) {
		update_post_meta( $id, '_yd_nav_hidden', '1' );
	}

	// Make the import visible to the page map cache-buster.
	wp_cache_delete( 'yd_page_map', 'yalla' );

	return (int) $id;
}

/**
 * Import everything.
 *
 * @param bool $force Re-run even if already imported.
 * @return array Summary of created objects.
 */
function yd_seed_content( $force = false ) {
	$done = get_option( 'yd_seeded' );
	if ( $done && ! $force ) {
		return array( 'skipped' => true );
	}

	$summary = array(
		'pages'    => 0,
		'events'   => 0,
		'services' => 0,
		'team'     => 0,
		'quotes'   => 0,
		'partners' => 0,
		'articles' => 0,
	);

	// Transcript groups so the two language roots are created first.
	$pages = yd_demo_pages();
	usort(
		$pages,
		function ( $a, $b ) {
			$rank = array( 'home' => 0, 'festival' => 1, 'program' => 2, 'events' => 3, 'articles' => 4, 'press' => 5, 'about' => 6, 'contact' => 7 );
			$ra   = isset( $rank[ $a['group'] ] ) ? $rank[ $a['group'] ] : 9;
			$rb   = isset( $rank[ $b['group'] ] ) ? $rank[ $b['group'] ] : 9;
			if ( $ra === $rb ) {
				return strcmp( $a['lang'], $b['lang'] );
			}
			return $ra - $rb;
		}
	);

	$roots = array();

	foreach ( $pages as $page ) {
		$parent = 0;
		if ( 'de' !== $page['lang'] && isset( $roots[ $page['lang'] ] ) ) {
			$parent = $roots[ $page['lang'] ];
		}
		$created = false;
		$id      = yd_seed_page( $page, $parent, $created );
		if ( 'home' === $page['group'] ) {
			$roots[ $page['lang'] ] = $id;
		}
		if ( $id && $created ) {
			++$summary['pages'];
		}
	}

	// Front page.
	if ( ! empty( $roots['de'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $roots['de'] );
		update_option( 'page_for_posts', 0 );
	}

	// Brand defaults.
	set_theme_mod( 'yd_brand_name', 'Arab Kulturfestival' );
	set_theme_mod( 'yd_contact_email', 'info@arab-kulturfestival.de' );
	set_theme_mod( 'yd_contact_location', 'Hamburg, Deutschland' );

	// Events.
	foreach ( yd_demo_events() as $event ) {
		$key = md5( $event['lang'] . $event['date'] . $event['title'] );
		if ( get_option( 'yd_event_' . $key ) ) {
			continue;
		}
		if ( yd_seed_event_exists( $event['date'], $event['time'], $event['lang'] ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'yd_event',
				'post_status'  => 'publish',
				'post_title'   => $event['title'],
				'post_content' => $event['content'],
				'post_excerpt' => $event['excerpt'],
				'meta_input'   => array(
					'_yd_date'      => $event['date'],
					'_yd_time'      => $event['time'],
					'_yd_end_time'  => $event['end'],
					'_yd_venue'     => $event['venue'],
					'_yd_city'      => $event['city'],
					'_yd_address'   => $event['address'],
					'_yd_price'     => $event['price'],
					'_yd_status'    => 'past' === $event['status'] ? 'upcoming' : $event['status'],
					'_yd_image'     => 'img/music.jpg',
				),
			),
			true
		);

		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, $event['lang'] );
			update_option( 'yd_event_' . $key, $id, false );
			++$summary['events'];
		}
	}

	// Formats.
	foreach ( yd_demo_services() as $service ) {
		if ( yd_seed_lookup( 'yd_service', $service['title'], $service['lang'] ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'yd_service',
				'post_status'  => 'publish',
				'post_title'   => $service['title'],
				'post_content' => $service['content'],
				'post_excerpt' => $service['excerpt'],
				'menu_order'   => $service['order'],
				'meta_input'   => array(
					'_yd_kicker'    => $service['kicker'],
					'_yd_cta_label' => yd_t( 'occasion_cta' ),
					'_yd_cta'       => '',
					'_yd_image'     => 0 === $service['order'] % 2 ? 'img/calligraphy.jpg' : 'img/community-circle.jpg',
				),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, $service['lang'] );
			++$summary['services'];
		}
	}

	// Team.
	foreach ( yd_demo_team() as $member ) {
		if ( yd_seed_lookup( 'yd_team', $member['title'], $member['lang'] ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'yd_team',
				'post_status'  => 'publish',
				'post_title'   => $member['title'],
				'post_content' => $member['content'],
				'menu_order'   => $member['order'],
				'meta_input'   => array(
					'_yd_role'     => $member['role'],
					'_yd_location' => $member['place'],
					'_yd_image'    => $member['photo'],
				),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, $member['lang'] );
			++$summary['team'];
		}
	}

	// Quotes.
	foreach ( yd_demo_testimonials() as $quote ) {
		$quote_title = wp_trim_words( $quote['quote'], 8 );

		if ( yd_seed_lookup( 'yd_testimonial', $quote_title, $quote['lang'] ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'yd_testimonial',
				'post_status'  => 'publish',
				'post_title'   => $quote_title,
				'post_content' => $quote['quote'],
				'menu_order'   => $quote['order'],
				'meta_input'   => array(
					'_yd_org'  => $quote['org'],
					'_yd_role' => $quote['role'],
				),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, $quote['lang'] );
			++$summary['quotes'];
		}
	}

	// Partners.
	foreach ( yd_demo_partners() as $partner ) {
		if ( yd_seed_lookup( 'yd_partner', $partner['name'], 'de' ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'   => 'yd_partner',
				'post_status' => 'publish',
				'post_title'  => $partner['name'],
				'menu_order'  => $partner['order'],
				'meta_input'  => array(
					'_yd_url'   => $partner['url'],
					'_yd_image' => $partner['logo'],
				),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, 'de' );
			++$summary['partners'];
		}
	}

	// Magazine.
	$category = term_exists( 'magazin', 'category' );
	if ( ! $category ) {
		$category = wp_insert_term( 'Magazin', 'category', array( 'slug' => 'magazin' ) );
	}
	$category_id = is_array( $category ) ? (int) $category['term_id'] : (int) $category;

	foreach ( yd_demo_articles() as $article ) {
		if ( yd_seed_lookup( 'post', $article['title'], $article['lang'], $article['slug'] ) ) {
			continue;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $article['title'],
				'post_name'    => $article['slug'],
				'post_content' => $article['content'],
				'post_excerpt' => $article['excerpt'],
				'post_date'    => $article['date'],
				'post_category' => array( $category_id ),
				'meta_input'   => array(
					'_yd_image' => $article['image'],
				),
			),
			true
		);
		if ( ! is_wp_error( $id ) ) {
			yd_set_lang( $id, $article['lang'] );
			++$summary['articles'];
		}
	}

	// Permalinks.
	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();

	update_option( 'yd_seeded', time() );

	return $summary;
}

/**
 * Run the import when the theme is activated.
 */
function yd_maybe_seed() {
	if ( get_option( 'yd_seeded' ) ) {
		return;
	}
	yd_seed_content();
}
add_action( 'after_switch_theme', 'yd_maybe_seed' );

/**
 * Admin screen under Tools → Yalla Demo-Inhalte.
 */
function yd_seed_admin_menu() {
	add_management_page(
		__( 'Yalla Demo-Inhalte', 'yalla' ),
		__( 'Yalla Inhalte', 'yalla' ),
		'manage_options',
		'yalla-content',
		'yd_seed_admin_page'
	);
}
add_action( 'admin_menu', 'yd_seed_admin_menu' );

/**
 * Render the import screen.
 */
function yd_seed_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$message = '';
	if ( isset( $_POST['yd_seed_run'] ) && check_admin_referer( 'yd_seed_run' ) ) {
		$result  = yd_seed_content( true );
		$message = sprintf(
			/* translators: 1: pages, 2: events, 3: services, 4: quotes, 5: partners, 6: articles */
			__( 'Import abgeschlossen: %1$d Seiten, %2$d Termine, %3$d Angebote, %4$d Stimmen, %5$d Partner, %6$d Artikel.', 'yalla' ),
			$result['pages'],
			$result['events'],
			$result['services'],
			$result['quotes'],
			$result['partners'],
			$result['articles']
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Yalla — Inhalte & Struktur', 'yalla' ); ?></h1>
		<?php if ( $message ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Dieser Import legt die dreisprachige Seitenstruktur, Termine, Formate, Team, Partner und Magazinartikel an. Was bereits existiert, bleibt unangetastet: Seiten werden über ihre Übersetzungsgruppe erkannt, alle anderen Inhalte über Titel und Sprache. Mehrfaches Ausführen ist deshalb unschädlich.', 'yalla' ); ?></p>
		<p><?php esc_html_e( 'Ältere Versionen des Themes haben beim zweiten Lauf Duplikate erzeugt. Solche Doppelungen finden und entfernen Sie unter Werkzeuge → Yalla Duplikate.', 'yalla' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'yd_seed_run' ); ?>
			<p>
				<button class="button button-primary" name="yd_seed_run" value="1">
					<?php esc_html_e( 'Demo-Inhalte importieren / ergänzen', 'yalla' ); ?>
				</button>
			</p>
		</form>

		<h2><?php esc_html_e( 'Sprachstruktur', 'yalla' ); ?></h2>
		<p><?php esc_html_e( 'Deutsch liegt auf der Startseite. Englisch liegt unter /en/, Arabisch unter /ar/. Jede Sprache ist ein eigener Seitenbaum — neue Seiten einfach als Unterseite der jeweiligen Sprachstartseite anlegen und dieselbe »Übersetzungs-Gruppe« vergeben.', 'yalla' ); ?></p>
	</div>
	<?php
}
