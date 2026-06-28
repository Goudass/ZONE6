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
define( 'ADVENTURE_BLOG_SITE_NAME', 'ZONE6.PL' );
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
