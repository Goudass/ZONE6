<?php
/**
 * Enqueue scripts and styles.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end assets.
 */
function adventure_blog_enqueue_assets() {
	wp_enqueue_style(
		'adventure-blog-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'adventure-blog-main',
		ADVENTURE_BLOG_URI . '/assets/css/main.css',
		array( 'adventure-blog-fonts' ),
		ADVENTURE_BLOG_VERSION
	);

	wp_enqueue_style(
		'adventure-blog-style',
		get_stylesheet_uri(),
		array( 'adventure-blog-main' ),
		ADVENTURE_BLOG_VERSION
	);

	wp_enqueue_script(
		'adventure-blog-main',
		ADVENTURE_BLOG_URI . '/assets/js/main.js',
		array(),
		ADVENTURE_BLOG_VERSION,
		true
	);

	$needs_leaflet = is_singular( 'trasa' ) || is_page( 'kontakt' );
	$needs_swiper  = is_singular( 'trasa' ) || ( is_singular( 'post' ) && adventure_blog_get_gallery_ids( get_queried_object_id() ) );

	if ( $needs_leaflet ) {
		wp_enqueue_style(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			array(),
			'1.9.4'
		);

		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			array(),
			'1.9.4',
			true
		);
	}

	if ( is_page( 'kontakt' ) ) {
		wp_enqueue_script(
			'adventure-blog-contact-map',
			ADVENTURE_BLOG_URI . '/assets/js/contact-map.js',
			array( 'leaflet' ),
			ADVENTURE_BLOG_VERSION,
			true
		);
	}

	if ( $needs_swiper ) {
		wp_enqueue_style(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
			array(),
			'11.0.0'
		);

		wp_enqueue_script(
			'swiper',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
			array(),
			'11.0.0',
			true
		);

		wp_enqueue_script(
			'adventure-blog-gallery',
			ADVENTURE_BLOG_URI . '/assets/js/gallery-swiper.js',
			array( 'swiper' ),
			ADVENTURE_BLOG_VERSION,
			true
		);
	}

	if ( is_singular( 'trasa' ) ) {
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
			array(),
			'4.4.1',
			true
		);

		wp_enqueue_script(
			'adventure-blog-gpx',
			ADVENTURE_BLOG_URI . '/assets/js/gpx-route.js',
			array( 'leaflet', 'chartjs', 'swiper' ),
			ADVENTURE_BLOG_VERSION,
			true
		);

		$gpx_id  = (int) get_post_meta( get_the_ID(), '_adventure_gpx_id', true );
		$gpx_url = $gpx_id ? wp_get_attachment_url( $gpx_id ) : '';

		wp_localize_script(
			'adventure-blog-gpx',
			'adventureGpx',
			array(
				'gpxUrl'       => esc_url_raw( $gpx_url ),
				'downloadText' => __( 'Pobierz GPX', 'adventure-blog' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'adventure_blog_enqueue_assets' );
