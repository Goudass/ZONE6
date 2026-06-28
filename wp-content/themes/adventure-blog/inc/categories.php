<?php
/**
 * News routes (Aktualności → typ-trasy: Tatry, Trasy rowerowe).
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Route type slugs shown under Aktualności (typ-trasy: Tatry, Trasy rowerowe).
 *
 * @return string[]
 */
function adventure_blog_get_news_category_slugs() {
	return array( 'tatry', 'trasy-rowerowe' );
}

/**
 * WP_Query args: trasy z Tatry lub Trasy rowerowe.
 *
 * @return array<string, mixed>
 */
function adventure_blog_get_news_routes_query_args() {
	return array(
		'post_type' => 'trasa',
		'tax_query' => array(
			array(
				'taxonomy' => 'typ-trasy',
				'field'    => 'slug',
				'terms'    => adventure_blog_get_news_category_slugs(),
			),
		),
	);
}

/**
 * Ensure Aktualności parent and child categories exist.
 */
function adventure_blog_ensure_news_categories() {
	$parent = term_exists( 'aktualnosci', 'category' );

	if ( ! $parent ) {
		$parent = wp_insert_term(
			'Aktualności',
			'category',
			array(
				'slug' => 'aktualnosci',
			)
		);
	}

	$parent_id = is_array( $parent ) ? (int) $parent['term_id'] : (int) $parent;

	$children = array(
		'tatry'          => 'Tatry',
		'trasy-rowerowe' => 'Trasy rowerowe',
	);

	foreach ( $children as $slug => $name ) {
		$existing = term_exists( $slug, 'category' );

		if ( ! $existing ) {
			wp_insert_term(
				$name,
				'category',
				array(
					'slug'   => $slug,
					'parent' => $parent_id,
				)
			);
			continue;
		}

		$term_id = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
		$term    = get_term( $term_id, 'category' );

		if ( $term instanceof WP_Term && (int) $term->parent !== $parent_id ) {
			wp_update_term(
				$term_id,
				'category',
				array(
					'parent' => $parent_id,
				)
			);
		}
	}
}

/**
 * Permalink for a news child category.
 *
 * @param string $slug Category slug.
 * @return string
 */
function adventure_blog_get_news_category_link( $slug ) {
	$term = get_term_by( 'slug', $slug, 'typ-trasy' );

	return ( $term instanceof WP_Term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/' );
}

/**
 * Permalink for the Aktualności archive page.
 *
 * @return string
 */
function adventure_blog_get_aktualnosci_link() {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id ) {
		return get_permalink( $posts_page_id );
	}

	return home_url( '/aktualnosci/' );
}
