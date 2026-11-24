<?php
/**
 * Customizer options for the festival identity.
 *
 * @package Yalla
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function yd_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'yd_brand',
		array(
			'title'    => __( 'Festival-Identität', 'yalla' ),
			'priority' => 20,
		)
	);

	// Identity.
	$wp_customize->add_section(
		'yd_identity',
		array(
			'title' => __( 'Name & Kontakt', 'yalla' ),
			'panel' => 'yd_brand',
		)
	);

	$fields = array(
		'yd_brand_name'    => array( 'label' => __( 'Festivalname', 'yalla' ), 'default' => 'Arab Kulturfestival' ),
		'yd_contact_email' => array( 'label' => __( 'E-Mail', 'yalla' ), 'default' => 'info@arab-kulturfestival.de' ),
		'yd_contact_phone' => array( 'label' => __( 'Telefon (Schema/NAP)', 'yalla' ), 'default' => '+49 178 1116220' ),
		'yd_contact_street' => array( 'label' => __( 'Straße und Hausnummer (NAP)', 'yalla' ), 'default' => 'Julius-Vosseler-Straße 106b' ),
		'yd_contact_postal' => array( 'label' => __( 'Postleitzahl (NAP)', 'yalla' ), 'default' => '22527' ),
		'yd_contact_city'  => array( 'label' => __( 'Stadt (NAP)', 'yalla' ), 'default' => 'Hamburg' ),
		'yd_contact_location' => array( 'label' => __( 'Ort', 'yalla' ), 'default' => 'Hamburg, Deutschland' ),
		'yd_video_url'     => array( 'label' => __( 'YouTube-Video (Startseite)', 'yalla' ), 'default' => '' ),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field['label'],
				'section' => 'yd_identity',
				'type'    => 'text',
			)
		);
	}

	// Hero image.
	$wp_customize->add_section(
		'yd_images',
		array(
			'title' => __( 'Bilder', 'yalla' ),
			'panel' => 'yd_brand',
		)
	);

	$wp_customize->add_setting(
		'yd_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'yd_hero_image',
			array(
				'label'       => __( 'Startseiten-Hero', 'yalla' ),
				'description' => __( 'Empfohlen: mindestens 1600 px breit, querformatig.', 'yalla' ),
				'section'     => 'yd_images',
			)
		)
	);

	$wp_customize->add_setting(
		'yd_feature_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'yd_feature_image',
			array(
				'label'   => __( 'Bild im Video-Block', 'yalla' ),
				'section' => 'yd_images',
			)
		)
	);

	// Socials.
	$wp_customize->add_section(
		'yd_socials',
		array(
			'title' => __( 'Social Media', 'yalla' ),
			'panel' => 'yd_brand',
		)
	);

	foreach ( array(
		'instagram' => 'Instagram',
		'facebook'  => 'Facebook',
		'youtube'   => 'YouTube',
		'x'         => 'X / Twitter',
	) as $key => $label ) {
		$setting = 'yd_social_' . $key;
		$wp_customize->add_setting(
			$setting,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$setting,
			array(
				'label'   => $label,
				'section' => 'yd_socials',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'yd_customize_register' );
