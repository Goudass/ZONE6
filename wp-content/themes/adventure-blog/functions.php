<?php
/**
 * Adventure Blog theme functions.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ADVENTURE_BLOG_VERSION', '1.6.0' );
define( 'ADVENTURE_BLOG_DIR', get_template_directory() );
define( 'ADVENTURE_BLOG_URI', get_template_directory_uri() );
define( 'ADVENTURE_BLOG_SITE_NAME', 'Strefa6.pl' );
define( 'ADVENTURE_BLOG_SITE_URL', 'https://www.6zone.pl' );

require_once ADVENTURE_BLOG_DIR . '/inc/cpt-trasa.php';
require_once ADVENTURE_BLOG_DIR . '/inc/categories.php';
require_once ADVENTURE_BLOG_DIR . '/inc/meta-boxes.php';
require_once ADVENTURE_BLOG_DIR . '/inc/enqueue.php';
require_once ADVENTURE_BLOG_DIR . '/inc/customizer.php';
require_once ADVENTURE_BLOG_DIR . '/inc/contact-form.php';
require_once ADVENTURE_BLOG_DIR . '/inc/template-tags.php';
require_once ADVENTURE_BLOG_DIR . '/inc/fallback-menu.php';
require_once ADVENTURE_BLOG_DIR . '/inc/seo.php';

/**
 * Body classes for transparent header over hero.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function adventure_blog_body_classes( $classes ) {
	if ( adventure_blog_has_hero_banner() ) {
		$classes[] = 'header-overlay';
	}
	return $classes;
}
add_filter( 'body_class', 'adventure_blog_body_classes' );

/**
 * Ensure browser tab title uses current brand name.
 *
 * @param array $parts Title parts.
 * @return array
 */
function adventure_blog_document_title_parts( $parts ) {
	if ( ! is_array( $parts ) ) {
		return $parts;
	}

	$parts['site'] = ADVENTURE_BLOG_SITE_NAME;

	return $parts;
}
add_filter( 'document_title_parts', 'adventure_blog_document_title_parts' );

/**
 * Normalize legacy brand strings in frontend text.
 *
 * @param string $value Input text.
 * @return string
 */
function adventure_blog_normalize_brand_text( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return $value;
	}

	$replacements = array(
		'ZONE6.PL'                               => ADVENTURE_BLOG_SITE_NAME,
		'ZONE6'                                  => 'Strefa6',
		'6ZONE.PL'                               => ADVENTURE_BLOG_SITE_NAME,
		'6ZONE'                                  => 'Strefa6',
		'zone6.pl'                               => strtolower( ADVENTURE_BLOG_SITE_NAME ),
		'zone6'                                  => 'strefa6',
		'6zone.pl'                               => strtolower( ADVENTURE_BLOG_SITE_NAME ),
		'6zone'                                  => 'strefa6',
		'Outdoor, trasy i przygody - 6zone.pl'   => 'Outdoor, trasy i przygody - Strefa6',
		'Outdoor, trasy i przygody — 6zone.pl'   => 'Outdoor, trasy i przygody — Strefa6',
	);

	return strtr( $value, $replacements );
}

/**
 * Normalize title shown in browser tab.
 *
 * @param string $title Full document title.
 * @return string
 */
function adventure_blog_pre_get_document_title( $title ) {
	return adventure_blog_normalize_brand_text( $title );
}
add_filter( 'pre_get_document_title', 'adventure_blog_pre_get_document_title', 99 );

/**
 * Normalize bloginfo output for name/description.
 *
 * @param string $value Bloginfo value.
 * @param string $show  Requested field.
 * @return string
 */
function adventure_blog_bloginfo_filter( $value, $show ) {
	if ( in_array( $show, array( 'name', 'description' ), true ) ) {
		return adventure_blog_normalize_brand_text( $value );
	}

	return $value;
}
add_filter( 'bloginfo', 'adventure_blog_bloginfo_filter', 20, 2 );

/**
 * Theme setup.
 */
function adventure_blog_setup() {
	load_theme_textdomain( 'adventure-blog', ADVENTURE_BLOG_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_image_size( 'route-card', 1200, 1200, true );
	add_image_size( 'route-hero', 1920, 1080, true );
	add_image_size( 'gallery-thumb', 800, 600, true );

	register_nav_menus(
		array(
			'primary' => __( 'Menu główne', 'adventure-blog' ),
			'footer'  => __( 'Menu stopki', 'adventure-blog' ),
		)
	);
}
add_action( 'after_setup_theme', 'adventure_blog_setup' );

/**
 * Ensure news categories exist.
 */
function adventure_blog_bootstrap_categories() {
	adventure_blog_ensure_news_categories();
}
add_action( 'after_setup_theme', 'adventure_blog_bootstrap_categories', 20 );

/**
 * Register widget areas.
 */
function adventure_blog_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Stopka', 'adventure-blog' ),
			'id'            => 'footer-1',
			'before_widget' => '<div class="footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="footer-widget__title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'adventure_blog_widgets_init' );

/**
 * Aktualności archive: Tatry + Trasy rowerowe, newest first.
 *
 * @param WP_Query $query Main query.
 */
function adventure_blog_filter_news_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() && ! $query->is_front_page() ) {
		foreach ( adventure_blog_get_news_routes_query_args() as $key => $value ) {
			$query->set( $key, $value );
		}
		$query->set(
			'orderby',
			array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			)
		);
	}
}
add_action( 'pre_get_posts', 'adventure_blog_filter_news_archive_query' );

/**
 * Flush rewrite rules on theme activation.
 */
function adventure_blog_activation() {
	adventure_blog_register_trasa_cpt();
	adventure_blog_ensure_news_categories();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'adventure_blog_activation' );
