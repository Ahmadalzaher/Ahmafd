<?php
/**
 * Configure Rank Math for the trilingual festival site.
 *
 * Run: php wp-cli.phar eval-file _tools/rank-math-config.php
 */

if ( ! defined( 'RANK_MATH_VERSION' ) ) {
	echo "Rank Math is not active.\n";
	return;
}

/* ---------------------------------------------------------------- titles -- */
$titles = (array) get_option( 'rank-math-options-titles', array() );

$titles = array_merge(
	$titles,
	array(
		'title_separator'            => '-',
		'website_name'               => 'Arab Kulturfestival',
		'knowledgegraph_type'        => 'company',
		'knowledgegraph_name'        => 'Arab Kulturfestival',
		'twitter_card_type'          => 'summary_large_image',
		'noindex_search'             => 'on',
		'noindex_empty_taxonomies'   => 'on',
		'disable_date_archives'      => 'on',
		'homepage_title'             => 'Arabisches Kulturfestival Hamburg – Termine & Dabke',
		'homepage_description'       => 'Dabke, Musik, Kalligrafie und Literatur aus der arabischen Welt: Termine, Programm und Karten für das Arab Kulturfestival in Hamburg.',
		'404_title'                  => 'Seite nicht gefunden %sep% %sitename%',
		'404_description'            => 'Diese Seite gibt es nicht mehr. Programm, Termine und Kontakt findest du auf der Startseite des Arab Kulturfestivals Hamburg.',
		'search_title'               => 'Suchergebnisse für %search_query% %sep% %sitename%',
		'pt_page_title'              => '%title% %sep% %sitename%',
		'pt_page_description'        => '%excerpt%',
		'pt_page_add_meta_box'       => 'on',
		'pt_post_title'              => '%title% %sep% %sitename%',
		'pt_post_description'        => '%excerpt%',
		'pt_post_add_meta_box'       => 'on',
		'pt_yd_event_title'          => '%title% %sep% %sitename%',
		'pt_yd_event_description'    => '%excerpt%',
		'pt_yd_event_add_meta_box'   => 'on',
		'pt_yd_event_default_rich_snippet' => 'event',
		'pt_attachment_add_meta_box' => 'off',
		'tax_category_custom_robots' => 'on',
		'tax_category_robots'        => array( 'noindex' ),
		'tax_post_tag_custom_robots' => 'on',
		'tax_post_tag_robots'        => array( 'noindex' ),
	)
);

update_option( 'rank-math-options-titles', $titles );

/* --------------------------------------------------------------- sitemap -- */
$sitemap = (array) get_option( 'rank-math-options-sitemap', array() );

$sitemap = array_merge(
	$sitemap,
	array(
		'items_per_page'          => 200,
		'include_images'          => 'on',
		'include_featured_image'  => 'off',
		'authors_sitemap'         => 'off',
		'html_sitemap'            => 'off',
		'exclude_posts'           => '',
		'exclude_terms'           => '',
		'pt_page_sitemap'         => 'on',
		'pt_post_sitemap'         => 'on',
		'pt_yd_event_sitemap'     => 'on',
		'pt_attachment_sitemap'   => 'off',
		'pt_yd_service_sitemap'   => 'off',
		'pt_yd_team_sitemap'      => 'off',
		'pt_yd_testimonial_sitemap' => 'off',
		'pt_yd_partner_sitemap'   => 'off',
		'tax_category_sitemap'    => 'off',
		'tax_post_tag_sitemap'    => 'off',
		'tax_yd_lang_sitemap'     => 'off',
	)
);

update_option( 'rank-math-options-sitemap', $sitemap );

/* --------------------------------------------------------------- general -- */
$general = (array) get_option( 'rank-math-options-general', array() );

$general = array_merge(
	$general,
	array(
		'breadcrumbs'          => 'off',
		'nofollow_external_links' => 'off',
		'noindex_empty_taxonomies' => 'on',
		'attachment_redirect'  => 'on',
		'attachment_redirect_url' => home_url( '/' ),
	)
);

update_option( 'rank-math-options-general', $general );

// Skip the registration/wizard nags; the SEO setup is scripted.
update_option( 'rank_math_registration_skip', 1 );
update_option( 'rank_math_review_notice', 1 );

rank_math()->settings->reset();
delete_option( 'rank_math_flush_rewrite' );

echo "Rank Math configured.\n";
echo 'sitemap post types: page=' . ( $sitemap['pt_page_sitemap'] ?? '?' ) . ' post=' . ( $sitemap['pt_post_sitemap'] ?? '?' ) . ' event=' . ( $sitemap['pt_yd_event_sitemap'] ?? '?' ) . "\n";
