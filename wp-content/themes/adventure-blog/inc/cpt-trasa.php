<?php
/**
 * Custom Post Type: Trasa.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Trasa CPT and taxonomies.
 */
function adventure_blog_register_trasa_cpt() {
	register_post_type(
		'trasa',
		array(
			'labels'              => array(
				'name'               => __( 'Trasy', 'adventure-blog' ),
				'singular_name'      => __( 'Trasa', 'adventure-blog' ),
				'add_new'            => __( 'Dodaj trasę', 'adventure-blog' ),
				'add_new_item'       => __( 'Dodaj nową trasę', 'adventure-blog' ),
				'edit_item'          => __( 'Edytuj trasę', 'adventure-blog' ),
				'new_item'           => __( 'Nowa trasa', 'adventure-blog' ),
				'view_item'          => __( 'Zobacz trasę', 'adventure-blog' ),
				'search_items'       => __( 'Szukaj tras', 'adventure-blog' ),
				'not_found'          => __( 'Nie znaleziono tras', 'adventure-blog' ),
				'not_found_in_trash' => __( 'Brak tras w koszu', 'adventure-blog' ),
				'menu_name'          => __( 'Trasy', 'adventure-blog' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'trasy' ),
			'menu_icon'           => 'dashicons-location-alt',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'        => true,
		)
	);

	register_taxonomy(
		'typ-trasy',
		'trasa',
		array(
			'labels'            => array(
				'name'          => __( 'Typ trasy', 'adventure-blog' ),
				'singular_name' => __( 'Typ trasy', 'adventure-blog' ),
			),
			'hierarchical'      => true,
			'rewrite'           => array( 'slug' => 'typ-trasy' ),
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'adventure_blog_register_trasa_cpt' );

/**
 * Create default route types on theme activation.
 */
function adventure_blog_create_default_terms() {
	$terms = array(
		'trasy-rowerowe' => 'Trasy rowerowe',
		'tatry'          => 'Tatry',
		'projekty'       => 'Projekty',
	);

	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'typ-trasy' ) ) {
			wp_insert_term( $name, 'typ-trasy', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'after_switch_theme', 'adventure_blog_create_default_terms' );
