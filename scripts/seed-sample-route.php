<?php
/**
 * Seed / update sample route with editorial content, media and GPX.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir    = get_template_directory();
$content_path = $theme_dir . '/sample-data/sample-route-content.html';
$gpx_path     = $theme_dir . '/sample-data/sample-route.gpx';

if ( ! file_exists( $content_path ) ) {
	WP_CLI::error( 'Brak pliku sample-route-content.html' );
}

$content = file_get_contents( $content_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

$route = get_posts(
	array(
		'post_type'      => 'trasa',
		'name'           => 'przykladowa-trasa-tatrzanska',
		'posts_per_page' => 1,
		'post_status'    => 'any',
	)
);

if ( empty( $route ) ) {
	$route_id = wp_insert_post(
		array(
			'post_type'    => 'trasa',
			'post_status'  => 'publish',
			'post_title'   => 'Orla Perć — fragment od Zawratu',
			'post_name'    => 'przykladowa-trasa-tatrzanska',
			'post_content' => $content,
		),
		true
	);
} else {
	$route_id = $route[0]->ID;
	wp_update_post(
		array(
			'ID'           => $route_id,
			'post_title'   => 'Orla Perć — fragment od Zawratu',
			'post_content' => $content,
		)
	);
}

if ( is_wp_error( $route_id ) ) {
	WP_CLI::error( $route_id->get_error_message() );
}

wp_set_object_terms( $route_id, 'tatry', 'typ-trasy' );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Import remote image or local file to media library.
 *
 * @param string $source URL or path.
 * @param int    $post_id Post ID.
 * @return int Attachment ID.
 */
function adventure_seed_import_media( $source, $post_id ) {
	if ( filter_var( $source, FILTER_VALIDATE_URL ) ) {
		$attachment_id = media_sideload_image( $source, $post_id, null, 'id' );
	} else {
		$file_array = array(
			'name'     => basename( $source ),
			'tmp_name' => $source,
		);
		$attachment_id = media_handle_sideload( $file_array, $post_id );
	}

	return is_wp_error( $attachment_id ) ? 0 : (int) $attachment_id;
}

$hero_id = adventure_seed_import_media(
	'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1920&q=80',
	$route_id
);
if ( $hero_id ) {
	set_post_thumbnail( $route_id, $hero_id );
}

$gallery_urls = array(
	'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1200&q=80',
	'https://images.unsplash.com/photo-1454496522488-7a8e488cd365?auto=format&fit=crop&w=1200&q=80',
	'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=80',
);

$gallery_ids = array();
foreach ( $gallery_urls as $url ) {
	$id = adventure_seed_import_media( $url, $route_id );
	if ( $id ) {
		$gallery_ids[] = $id;
	}
}
update_post_meta( $route_id, '_adventure_gallery_ids', $gallery_ids );

if ( file_exists( $gpx_path ) ) {
	$upload = wp_upload_bits( basename( $gpx_path ), null, file_get_contents( $gpx_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( empty( $upload['error'] ) ) {
		$gpx_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'application/gpx+xml',
				'post_title'     => sanitize_file_name( basename( $gpx_path ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$route_id
		);
		if ( $gpx_id ) {
			update_post_meta( $route_id, '_adventure_gpx_id', $gpx_id );
		}
	}
}

update_post_meta( $route_id, '_adventure_featured', '1' );
update_post_meta( $route_id, '_adventure_trudnosc', 'srednia' );
update_post_meta( $route_id, '_adventure_czas', '3h 15min' );
update_post_meta( $route_id, '_adventure_dystans', '18 km' );
update_post_meta( $route_id, '_adventure_przewyzszenie', '650 m' );
update_post_meta( $route_id, '_adventure_route_start', 'Zawrat' );
update_post_meta( $route_id, '_adventure_route_end', 'Dolina Pięciu Stawów' );
update_post_meta(
	$route_id,
	'_adventure_overview_intro',
	'Klasyczny, wymagający fragment grani w Tatrach Wysokich. Start ze Schroniska Murowaniec przez Zawrat — trasa dla osób z doświadczeniem górskim i dobrym przygotowaniem kondycyjnym.'
);

WP_CLI::success( 'Zaktualizowano trasę: ' . get_permalink( $route_id ) );
