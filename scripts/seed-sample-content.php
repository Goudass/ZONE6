<?php
/**
 * Seed sample posts and routes for all route categories.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Ensure taxonomy terms exist.
 */
function adventure_seed_ensure_terms() {
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

	adventure_blog_ensure_news_categories();
}

/**
 * Upsert post by slug.
 *
 * @param array $args Post args with post_name required.
 * @return int Post ID.
 */
function adventure_seed_upsert_post( $args ) {
	$slug = $args['post_name'];
	$type = $args['post_type'] ?? 'post';

	$existing = get_posts(
		array(
			'post_type'      => $type,
			'name'           => $slug,
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	if ( $existing ) {
		$args['ID'] = $existing[0];
		$post_id    = wp_update_post( $args, true );
	} else {
		$post_id = wp_insert_post( $args, true );
	}

	return is_wp_error( $post_id ) ? 0 : (int) $post_id;
}

/**
 * Import remote image to media library.
 *
 * @param string $url     Image URL.
 * @param int    $post_id Post ID.
 * @return int Attachment ID.
 */
function adventure_seed_import_media( $url, $post_id ) {
	$attachment_id = media_sideload_image( $url, $post_id, null, 'id' );

	return ( is_wp_error( $attachment_id ) || ! $attachment_id ) ? 0 : (int) $attachment_id;
}

/**
 * Set featured image from URL.
 *
 * @param int    $post_id Post ID.
 * @param string $url     Image URL.
 */
function adventure_seed_set_thumbnail( $post_id, $url ) {
	$attachment_id = adventure_seed_import_media( $url, $post_id );
	if ( $attachment_id ) {
		set_post_thumbnail( $post_id, $attachment_id );
	}
}

/**
 * Set gallery images from URLs.
 *
 * @param int      $post_id Post ID.
 * @param string[] $urls    Image URLs.
 */
function adventure_seed_set_gallery( $post_id, $urls ) {
	$ids = array();

	foreach ( $urls as $url ) {
		$id = adventure_seed_import_media( $url, $post_id );
		if ( $id ) {
			$ids[] = $id;
		}
	}

	if ( $ids ) {
		update_post_meta( $post_id, '_adventure_gallery_ids', $ids );
	}
}

/**
 * Load editorial HTML from theme sample-data.
 *
 * @param string $filename File name inside sample-data.
 * @return string
 */
function adventure_seed_load_editorial_file( $filename ) {
	$path = get_template_directory() . '/sample-data/' . ltrim( $filename, '/' );

	if ( ! file_exists( $path ) ) {
		return '';
	}

	return file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Build route editorial HTML from content blocks.
 *
 * @param array    $blocks  Editorial blocks.
 * @param string[] $gallery Gallery image URLs for image_index keys.
 * @return string
 */
function adventure_seed_build_route_editorial( $blocks, $gallery = array() ) {
	$html = "<div class=\"route-editorial\">\n";

	foreach ( $blocks as $index => $block ) {
		$reverse_class = ( ! empty( $block['reverse'] ) || 1 === $index % 2 ) ? ' route-editorial__block--reverse' : '';
		$image         = $block['image'] ?? ( $gallery[ $block['image_index'] ?? $index ] ?? '' );
		$eyebrow       = esc_html( $block['eyebrow'] ?? '' );
		$title         = esc_html( $block['title'] ?? '' );
		$alt           = esc_attr( $block['alt'] ?? $title );
		$paragraphs    = '';

		foreach ( (array) ( $block['paragraphs'] ?? array() ) as $paragraph ) {
			$paragraphs .= "\t\t\t<p>" . esc_html( $paragraph ) . "</p>\n";
		}

		$html .= "\t<section class=\"route-editorial__block{$reverse_class}\">\n";
		$html .= "\t\t<div class=\"route-editorial__text route-editorial__text--framed\">\n";
		$html .= "\t\t\t<p class=\"route-editorial__eyebrow\">{$eyebrow}</p>\n";
		$html .= "\t\t\t<h2>{$title}</h2>\n";
		$html .= $paragraphs;
		$html .= "\t\t</div>\n";

		if ( $image ) {
			$image = esc_url( $image );
			$html .= "\t\t<figure class=\"route-editorial__media\">\n";
			$html .= "\t\t\t<img src=\"{$image}\" alt=\"{$alt}\" loading=\"lazy\">\n";
			$html .= "\t\t</figure>\n";
		}

		$html .= "\t</section>\n";
	}

	$html .= "</div>\n";

	return $html;
}

/**
 * Default editorial layout for a route (intro + 2 photo sections).
 *
 * @param array $item Route seed item.
 * @return string
 */
function adventure_seed_default_route_editorial( $item ) {
	$intro   = wp_strip_all_tags( $item['post_content'] ?? '' );
	$gallery = $item['gallery'] ?? array();
	$image   = $item['image'] ?? '';

	return adventure_seed_build_route_editorial(
		array(
			array(
				'eyebrow'    => 'Opis trasy',
				'title'      => $item['post_title'],
				'paragraphs' => array_filter( array( $intro ) ),
				'image'      => $gallery[0] ?? $image,
			),
			array(
				'eyebrow'    => 'W terenie',
				'title'      => 'Wrażenia ze szlaku',
				'paragraphs' => array(
					'Relacja uzupełniona zdjęciami z trasy — tempo i przerwy dostosuj do pogody oraz własnej formy.',
					'Przed wyjściem sprawdź prognozę, zabierz mapę lub GPX i zaplanuj zapas czasu na zejście.',
				),
				'image'      => $gallery[1] ?? $image,
				'reverse'    => true,
			),
			array(
				'eyebrow'    => 'Na miejscu',
				'title'      => 'Widoki i detale',
				'paragraphs' => array(
					'Poza szczytem sezonu światło jest miększe, a szlaki mniej zatłoczone — wtedy najłatwiej uchwycić klimat miejsca.',
				),
				'image'      => $gallery[2] ?? $gallery[0] ?? $image,
			),
		),
		$gallery
	);
}

/**
 * Resolve route post content for seeding.
 *
 * @param array $item Route seed item.
 * @return string
 */
function adventure_seed_resolve_route_content( $item ) {
	if ( ! empty( $item['editorial_file'] ) ) {
		$content = adventure_seed_load_editorial_file( $item['editorial_file'] );
		if ( $content ) {
			return $content;
		}
	}

	if ( ! empty( $item['editorial_blocks'] ) ) {
		return adventure_seed_build_route_editorial( $item['editorial_blocks'], $item['gallery'] ?? array() );
	}

	return adventure_seed_default_route_editorial( $item );
}

/**
 * Attach sample GPX file to a route.
 *
 * @param int    $post_id  Route post ID.
 * @param string $filename GPX file in sample-data.
 */
function adventure_seed_attach_gpx( $post_id, $filename ) {
	$path = get_template_directory() . '/sample-data/' . ltrim( $filename, '/' );

	if ( ! file_exists( $path ) ) {
		return;
	}

	$upload = wp_upload_bits( basename( $path ), null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( ! empty( $upload['error'] ) ) {
		return;
	}

	$gpx_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'application/gpx+xml',
			'post_title'     => sanitize_file_name( basename( $path ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);

	if ( $gpx_id ) {
		update_post_meta( $post_id, '_adventure_gpx_id', $gpx_id );
	}
}

adventure_seed_ensure_terms();

$blog_posts = array();

$routes = array(
	array(
		'post_name'      => 'przykladowa-trasa-tatrzanska',
		'post_title'     => 'Orla Perć — fragment od Zawratu',
		'editorial_file' => 'sample-route-content.html',
		'gpx_file'       => 'sample-route.gpx',
		'route_start'    => 'Zawrat',
		'route_end'      => 'Dolina Pięciu Stawów',
		'overview_intro' => 'Klasyczny, wymagający fragment grani w Tatrach Wysokich. Start ze Schroniska Murowaniec przez Zawrat — trasa dla osób z doświadczeniem górskim i dobrym przygotowaniem kondycyjnym.',
		'term'           => 'tatry',
		'featured'      => '1',
		'trudnosc'      => 'srednia',
		'czas'          => '3h 15min',
		'dystans'       => '18 km',
		'przewyzszenie' => '650 m',
		'image'         => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'morskie-oko-pieci-stawow',
		'post_title'    => 'Morskie Oko — Pięć Stawów',
		'post_content'  => '<p>Popularny szlak w Dolinie Rybiego Potoku z przedłużeniem do Doliny Pięciu Stawów. Widokowe podejście, dobre na całodniową wycieczkę.</p>',
		'term'          => 'tatry',
		'featured'      => '0',
		'trudnosc'      => 'latwa',
		'czas'          => '5h 30min',
		'dystans'       => '22 km',
		'przewyzszenie' => '780 m',
		'image'         => 'https://images.unsplash.com/photo-1483728642387-6bc3bdd03c71?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1454496522488-7a8e488cd365?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'koscielec-czarny-staw',
		'post_title'    => 'Kościelec — Czarny Staw Gąsienicowy',
		'post_content'  => '<p>Trasa przez Dolinę Gąsienicową z wejściem na Kościelec. Dla osób, które chcą poczuć wysokość bez przechodzenia całej grani.</p>',
		'term'          => 'tatry',
		'featured'      => '0',
		'trudnosc'      => 'trudna',
		'czas'          => '6h 45min',
		'dystans'       => '16 km',
		'przewyzszenie' => '980 m',
		'image'         => 'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'tatry-rowerem-dolina-chocholowska',
		'post_title'    => 'Tatry rowerem — Dolina Chochołowska',
		'post_content'  => '<p>Spokojna trasa rowerowa przez Dolinę Chochołowską. Idealna na gravel lub MTB, szczególnie wczesną jesienią poza szczytem sezonu.</p>',
		'term'          => 'tatry',
		'featured'      => '0',
		'trudnosc'      => 'latwa',
		'czas'          => '2h 40min',
		'dystans'       => '24 km',
		'przewyzszenie' => '320 m',
		'image'         => 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1544191696-102dbdaeeaa5?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1541625602330-7437eacf07d0?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'trasa-rowerowa-katowice-myslowice',
		'post_title'    => 'Katowice — Mysłowice (ścieżka rowerowa)',
		'post_content'  => '<p>Miejska trasa łącząca aglomerację z zielonymi odcinkami wzdłuż rzeki. Dobry trening po pracy i szybki dojazd bez samochodu.</p>',
		'term'          => 'trasy-rowerowe',
		'featured'      => '0',
		'trudnosc'      => 'latwa',
		'czas'          => '1h 20min',
		'dystans'       => '28 km',
		'przewyzszenie' => '120 m',
		'image'         => 'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1541625602330-7437eacf07d0?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'beskid-slaski-petla-zywiec',
		'post_title'    => 'Beskid Śląski — pętla przez Żywiec',
		'post_content'  => '<p>Łagodna pętla z asfaltem i lekkim szutrem. Kilka podjazdów, ale bez ekstremalnego przewyższenia — dobra na całodzienny przejazd.</p>',
		'term'          => 'trasy-rowerowe',
		'featured'      => '0',
		'trudnosc'      => 'srednia',
		'czas'          => '4h 10min',
		'dystans'       => '62 km',
		'przewyzszenie' => '840 m',
		'image'         => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1544191696-102dbdaeeaa5?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1507035895480-2b3156c31fc8?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'jurajski-maraton-szkoleniowy',
		'post_title'    => 'Jurajski maraton szkoleniowy',
		'post_content'  => '<p>Przygotowanie do dłuższego dystansu: techniczne zjazdy, krótkie podjazdy i odcinki singla. Trasa testowa pod zawody.</p>',
		'term'          => 'trasy-rowerowe',
		'featured'      => '0',
		'trudnosc'      => 'trudna',
		'czas'          => '3h 30min',
		'dystans'       => '48 km',
		'przewyzszenie' => '920 m',
		'image'         => 'https://images.unsplash.com/photo-1541625602330-7437eacf07d0?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1544191696-102dbdaeeaa5?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'projekt-ultralight-backpacking',
		'post_title'    => 'Projekt: ultralight backpacking',
		'post_content'  => '<p>Testuję minimalistyczny zestaw na dwie doby w terenie. Cel — zejść poniżej 8 kg bez rezygnacji z bezpieczeństwa.</p>',
		'term'          => 'projekty',
		'featured'      => '0',
		'trudnosc'      => 'srednia',
		'czas'          => '2 dni',
		'dystans'       => '35 km',
		'przewyzszenie' => '1100 m',
		'image'         => 'https://images.unsplash.com/photo-1522168547953-303429a14e71?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1551632811-561732b07455?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'projekt-zimowy-nordic-walking',
		'post_title'    => 'Projekt: zimowy nordic walking',
		'post_content'  => '<p>Plan czterotygodniowego cyklu treningowego przed wyjazdem w góry. Notatki z testów na miejscowym terenie.</p>',
		'term'          => 'projekty',
		'featured'      => '0',
		'trudnosc'      => 'latwa',
		'czas'          => '4 tygodnie',
		'dystans'       => '—',
		'przewyzszenie' => '—',
		'image'         => 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1483728642387-6bc3bdd03c71?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1454496522488-7a8e488cd365?auto=format&fit=crop&w=1200&q=80',
		),
	),
	array(
		'post_name'     => 'projekt-gpx-biblioteka-tras',
		'post_title'    => 'Projekt: biblioteka tras GPX',
		'post_content'  => '<p>Buduję spójną kolekcję plików GPX z opisami i zdjęciami. Każda trasa dostanie wersję do nawigacji i krótką notatkę terenową.</p>',
		'term'          => 'projekty',
		'featured'      => '0',
		'trudnosc'      => 'latwa',
		'czas'          => 'w toku',
		'dystans'       => '—',
		'przewyzszenie' => '—',
		'image'         => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=1200&q=80',
		'gallery'       => array(
			'https://images.unsplash.com/photo-1522168547953-303429a14e71?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1551632811-561732b07455?auto=format&fit=crop&w=1200&q=80',
			'https://images.unsplash.com/photo-1541625602330-7437eacf07d0?auto=format&fit=crop&w=1200&q=80',
		),
	),
);

$created_posts  = 0;
$created_routes = 0;

foreach ( $blog_posts as $item ) {
	$post_id = adventure_seed_upsert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $item['post_title'],
			'post_name'    => $item['post_name'],
			'post_content' => $item['post_content'],
		)
	);

	if ( $post_id ) {
		wp_set_object_terms( $post_id, array(), 'typ-trasy' );

		if ( ! empty( $item['category'] ) ) {
			$category = get_category_by_slug( $item['category'] );
			if ( $category ) {
				wp_set_post_categories( $post_id, array( (int) $category->term_id ), false );
			}
		}

		adventure_seed_set_thumbnail( $post_id, $item['image'] );
		if ( ! empty( $item['gallery'] ) ) {
			adventure_seed_set_gallery( $post_id, $item['gallery'] );
		}
		++$created_posts;
	}
}

foreach ( $routes as $item ) {
	$post_id = adventure_seed_upsert_post(
		array(
			'post_type'    => 'trasa',
			'post_status'  => 'publish',
			'post_title'   => $item['post_title'],
			'post_name'    => $item['post_name'],
			'post_content' => adventure_seed_resolve_route_content( $item ),
		)
	);

	if ( ! $post_id ) {
		continue;
	}

	wp_set_object_terms( $post_id, $item['term'], 'typ-trasy' );
	update_post_meta( $post_id, '_adventure_featured', $item['featured'] );
	update_post_meta( $post_id, '_adventure_trudnosc', $item['trudnosc'] );
	update_post_meta( $post_id, '_adventure_czas', $item['czas'] );
	update_post_meta( $post_id, '_adventure_dystans', $item['dystans'] );
	update_post_meta( $post_id, '_adventure_przewyzszenie', $item['przewyzszenie'] );
	adventure_seed_set_thumbnail( $post_id, $item['image'] );
	if ( ! empty( $item['gallery'] ) ) {
		adventure_seed_set_gallery( $post_id, $item['gallery'] );
	}
	if ( ! empty( $item['gpx_file'] ) ) {
		adventure_seed_attach_gpx( $post_id, $item['gpx_file'] );
	}

	if ( ! empty( $item['route_start'] ) ) {
		update_post_meta( $post_id, '_adventure_route_start', $item['route_start'] );
	}

	if ( ! empty( $item['route_end'] ) ) {
		update_post_meta( $post_id, '_adventure_route_end', $item['route_end'] );
	}

	if ( ! empty( $item['overview_intro'] ) ) {
		update_post_meta( $post_id, '_adventure_overview_intro', $item['overview_intro'] );
	}

	++$created_routes;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success(
		sprintf(
			'Dodano/zaktualizowano: %d wpisów, %d tras (z galeriami i zdjęciami).',
			$created_posts,
			$created_routes
		)
	);
}
