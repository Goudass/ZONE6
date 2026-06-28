<?php
/**
 * Theme Customizer settings.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function adventure_blog_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'adventure_blog_branding',
		array(
			'title'    => __( 'ZONE6.PL — branding', 'adventure-blog' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'adventure_blog_name',
		array(
			'default'           => ADVENTURE_BLOG_SITE_NAME,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_blog_name',
		array(
			'label'   => __( 'Nazwa strony', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_hero_title',
		array(
			'default'           => 'Przygoda zaczyna się tu',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_hero_title',
		array(
			'label'   => __( 'Nagłówek hero', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_hero_subtitle',
		array(
			'default'           => 'Trasy rowerowe, górskie wyprawy i projekty outdoorowe.',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'adventure_hero_subtitle',
		array(
			'label'   => __( 'Podtytuł hero', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'adventure_hero_image',
		array(
			'default'           => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1920&q=80',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_hero_image',
		array(
			'label'   => __( 'Zdjęcie hero (URL)', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'adventure_home_about_teaser',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'adventure_home_about_teaser',
		array(
			'label'       => __( 'Strona główna — skrót „O mnie”', 'adventure-blog' ),
			'description' => __( 'Opcjonalny własny skrót. Puste = tagline i opis ze strony O mnie (hero).', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'adventure_home_about_image',
		array(
			'default'           => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=900&h=900&q=80',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_home_about_image',
		array(
			'label'       => __( 'Strona główna — zdjęcie „O mnie” (URL)', 'adventure-blog' ),
			'description' => __( 'Kwadratowe zdjęcie obok skrótu o autorze. Puste = zdjęcie wyróżniające strony O mnie.', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'url',
		)
	);

	$wp_customize->add_setting(
		'adventure_about_hero_tagline',
		array(
			'default'           => adventure_blog_get_about_copy_defaults()['tagline'],
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'adventure_about_hero_tagline',
		array(
			'label'   => __( 'Strona O mnie — tagline w hero', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'adventure_about_hero_intro',
		array(
			'default'           => adventure_blog_get_about_copy_defaults()['intro'],
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'adventure_about_hero_intro',
		array(
			'label'   => __( 'Strona O mnie — opis w hero', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'adventure_about_hero_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_about_hero_image',
		array(
			'label'       => __( 'Strona O mnie — zdjęcie hero (URL)', 'adventure-blog' ),
			'description' => __( 'Puste = zdjęcie wyróżniające strony lub to samo co hero na stronie głównej.', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'url',
		)
	);

	$archive_heroes = array(
		'tatry'          => __( 'Hero — Tatry (URL zdjęcia)', 'adventure-blog' ),
		'trasy_rowerowe' => __( 'Hero — Trasy rowerowe (URL zdjęcia)', 'adventure-blog' ),
		'projekty'       => __( 'Hero — Projekty (URL zdjęcia)', 'adventure-blog' ),
		'trasy'          => __( 'Hero — Wszystkie trasy (URL zdjęcia)', 'adventure-blog' ),
		'aktualnosci'    => __( 'Hero — Aktualności (URL zdjęcia)', 'adventure-blog' ),
		'kontakt'        => __( 'Hero — Kontakt (URL zdjęcia)', 'adventure-blog' ),
	);

	$defaults = adventure_blog_archive_hero_defaults();

	foreach ( $archive_heroes as $key => $label ) {
		$slug = str_replace( '_', '-', $key );
		$wp_customize->add_setting(
			'adventure_archive_hero_' . $key,
			array(
				'default'           => $defaults[ $slug ] ?? '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);

		$wp_customize->add_control(
			'adventure_archive_hero_' . $key,
			array(
				'label'   => $label,
				'section' => 'adventure_blog_branding',
				'type'    => 'url',
			)
		);
	}

	$wp_customize->add_setting(
		'adventure_instagram_url',
		array(
			'default'           => 'https://www.instagram.com/zone6',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_instagram_url',
		array(
			'label'   => __( 'Instagram URL', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'adventure_instagram_handle',
		array(
			'default'           => 'ZONE6',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_instagram_handle',
		array(
			'label'       => __( 'Instagram — nazwa wyświetlana', 'adventure-blog' ),
			'description' => __( 'Np. ZONE6 — widoczna w stopce obok ikony.', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_strava_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_strava_url',
		array(
			'label'   => __( 'Strava URL', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'adventure_komoot_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_komoot_url',
		array(
			'label'   => __( 'Komoot URL', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'adventure_contact_email',
		array(
			'default'           => get_option( 'admin_email' ),
			'sanitize_callback' => 'sanitize_email',
		)
	);

	$wp_customize->add_control(
		'adventure_contact_email',
		array(
			'label'   => __( 'Email kontaktowy (formularz)', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'email',
		)
	);

	$wp_customize->add_setting(
		'adventure_contact_display_email',
		array(
			'default'           => 'kontakt@6zone.pl',
			'sanitize_callback' => 'sanitize_email',
		)
	);

	$wp_customize->add_control(
		'adventure_contact_display_email',
		array(
			'label'       => __( 'Email wyświetlany na stronie Kontakt', 'adventure-blog' ),
			'description' => __( 'Puste = ten sam co email formularza.', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'email',
		)
	);

	$wp_customize->add_setting(
		'adventure_contact_phone',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_contact_phone',
		array(
			'label'   => __( 'Telefon (strona Kontakt)', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_contact_address',
		array(
			'default'           => __( 'Katowice, Polska', 'adventure-blog' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_contact_address',
		array(
			'label'   => __( 'Adres (strona Kontakt)', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_contact_map_label',
		array(
			'default'           => __( 'Katowice', 'adventure-blog' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'adventure_contact_map_label',
		array(
			'label'   => __( 'Etykieta na mapie (Kontakt)', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'adventure_footer_tagline',
		array(
			'default'           => 'Outdoor, trasy i przygody — 6zone.pl',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'adventure_footer_tagline',
		array(
			'label'   => __( 'Opis w stopce', 'adventure-blog' ),
			'section' => 'adventure_blog_branding',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'adventure_footer_bg_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		'adventure_footer_bg_image',
		array(
			'label'       => __( 'Tło stopki — zdjęcie (URL)', 'adventure-blog' ),
			'description' => __( 'Pozostaw puste, aby użyć tego samego zdjęcia co w hero.', 'adventure-blog' ),
			'section'     => 'adventure_blog_branding',
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'adventure_blog_customize_register' );

/**
 * Get blog display name.
 *
 * @return string
 */
function adventure_blog_get_name() {
	$name = get_theme_mod( 'adventure_blog_name', ADVENTURE_BLOG_SITE_NAME );

	if ( $name ) {
		return $name;
	}

	$wp_name = get_bloginfo( 'name' );

	return $wp_name ? $wp_name : ADVENTURE_BLOG_SITE_NAME;
}
