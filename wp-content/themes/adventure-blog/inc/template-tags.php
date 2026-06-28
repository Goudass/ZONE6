<?php
/**
 * Template helper functions.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post date for display (dzień.miesiąc.rok).
 *
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function adventure_blog_format_post_date( $post_id = null ) {
	return get_the_date( 'd.m.Y', $post_id );
}

/**
 * Home page about section image URL.
 *
 * @return string
 */
function adventure_blog_get_home_about_image() {
	$default = 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=900&h=900&q=80';
	$image   = get_theme_mod( 'adventure_home_about_image', '' );

	if ( $image ) {
		return $image;
	}

	$about_page = get_page_by_path( 'o-mnie' );
	if ( $about_page && has_post_thumbnail( $about_page ) ) {
		$url = get_the_post_thumbnail_url( $about_page, 'route-card' );
		if ( $url ) {
			return $url;
		}
	}

	return $default;
}

/**
 * About page hero image URL.
 *
 * @param int $post_id Page ID.
 * @return string
 */
function adventure_blog_get_about_hero_image( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, 'route-hero' );
		if ( $url ) {
			return $url;
		}
	}

	$custom = get_theme_mod( 'adventure_about_hero_image', '' );
	if ( $custom ) {
		return $custom;
	}

	return adventure_blog_get_home_hero_image();
}

/**
 * Default about page copy (tagline + intro).
 *
 * @return array{tagline: string, intro: string}
 */
function adventure_blog_get_about_copy_defaults() {
	return array(
		'tagline' => __( 'Żyję aktywnie. Testuję granice. Szukam nowych doświadczeń.', 'adventure-blog' ),
		'intro'   => __( 'Przez lata pływanie nauczyło mnie dyscypliny, ale to ciekawość świata sprawia, że ciągle szukam nowych wyzwań. Tutaj zbieram wszystko, co inspiruje mnie na co dzień.', 'adventure-blog' ),
	);
}

/**
 * About page copy from the Customizer.
 *
 * @return array{tagline: string, intro: string}
 */
function adventure_blog_get_about_copy() {
	$defaults = adventure_blog_get_about_copy_defaults();

	return array(
		'tagline' => get_theme_mod( 'adventure_about_hero_tagline', $defaults['tagline'] ),
		'intro'   => get_theme_mod( 'adventure_about_hero_intro', $defaults['intro'] ),
	);
}

/**
 * Get route meta fields.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function adventure_blog_get_route_meta( $post_id ) {
	$gallery_ids = get_post_meta( $post_id, '_adventure_gallery_ids', true );
	if ( ! is_array( $gallery_ids ) ) {
		$gallery_ids = array();
	}

	return array(
		'trudnosc'       => get_post_meta( $post_id, '_adventure_trudnosc', true ) ?: 'latwa',
		'czas'           => get_post_meta( $post_id, '_adventure_czas', true ) ?: '',
		'dystans'        => get_post_meta( $post_id, '_adventure_dystans', true ) ?: '',
		'przewyzszenie'  => get_post_meta( $post_id, '_adventure_przewyzszenie', true ) ?: '',
		'gpx_id'         => (int) get_post_meta( $post_id, '_adventure_gpx_id', true ),
		'gallery_ids'    => $gallery_ids,
		'overview_intro' => get_post_meta( $post_id, '_adventure_overview_intro', true ) ?: '',
		'route_start'    => get_post_meta( $post_id, '_adventure_route_start', true ) ?: '',
		'route_end'      => get_post_meta( $post_id, '_adventure_route_end', true ) ?: '',
	);
}

/**
 * Get gallery attachment IDs for a post or route.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function adventure_blog_get_gallery_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$ids     = get_post_meta( $post_id, '_adventure_gallery_ids', true );

	if ( ! is_array( $ids ) ) {
		return array();
	}

	return array_values( array_filter( array_map( 'absint', $ids ) ) );
}

/**
 * Difficulty label map.
 *
 * @return array
 */
function adventure_blog_difficulty_labels() {
	return array(
		'latwa'   => __( 'Łatwa', 'adventure-blog' ),
		'srednia' => __( 'Średnia', 'adventure-blog' ),
		'trudna'  => __( 'Trudna', 'adventure-blog' ),
	);
}

/**
 * Render difficulty badge.
 *
 * @param string $level Difficulty slug.
 */
function adventure_blog_difficulty_badge( $level ) {
	$labels = adventure_blog_difficulty_labels();
	$label  = isset( $labels[ $level ] ) ? $labels[ $level ] : $labels['latwa'];

	printf(
		'<span class="badge badge--%1$s">%2$s</span>',
		esc_attr( $level ),
		esc_html( $label )
	);
}

/**
 * Remove legacy section number markup from route editorial content.
 *
 * @param string $content Post content.
 * @return string
 */
function adventure_blog_strip_route_editorial_numbers( $content ) {
	if ( ! is_singular( 'trasa' ) ) {
		return $content;
	}

	return preg_replace(
		'/<span[^>]*\broute-editorial__num\b[^>]*>[\s\S]*?<\/span>\s*/i',
		'',
		$content
	);
}
add_filter( 'the_content', 'adventure_blog_strip_route_editorial_numbers', 5 );

/**
 * Render a transparent outline icon for route stats.
 *
 * @param string $type Icon key: time, distance, elevation.
 */
function adventure_blog_route_stat_icon( $type ) {
	$icons = array(
		'time'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>',
		'distance'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12c2-3.5 4-5.5 7-5.5s5 2 7 5.5"/><path d="M8.5 12h7"/><path d="M10 9.5 8 12l2 2.5"/><path d="M14 9.5l2 2.5-2 2.5"/></svg>',
		'elevation'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18 9.5 8.5 13 13l3.5-5L21 18z"/><path d="M12 5.5V3"/><path d="M12 5.5 10.5 7"/><path d="M12 5.5 13.5 7"/></svg>',
		'difficulty'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18 9 6l3 6 2-4 6 10"/><path d="M4 18h16"/></svg>',
		'category'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7l2 2h7v10H4z"/><path d="M4 6v12"/></svg>',
		'gpx-distance' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16c3-6 5-8 8-8s5 2 8 8"/><circle cx="12" cy="8" r="2.25"/><path d="M12 10.5V14"/></svg>',
		'ele-min'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v12"/><path d="M8.5 11.5 12 15l3.5-3.5"/><path d="M5 19h14"/></svg>',
		'ele-max'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V8"/><path d="M8.5 12.5 12 9l3.5 3.5"/><path d="M5 5h14"/></svg>',
		'ele-gain'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18h16"/><path d="m7 14 4-6 3 4 3-7"/></svg>',
	);

	if ( ! isset( $icons[ $type ] ) ) {
		return;
	}

	printf(
		'<span class="route-stats__icon route-stats__icon--%1$s" aria-hidden="true">%2$s</span>',
		esc_attr( $type ),
		$icons[ $type ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG markup.
	);
}

/**
 * Render route card meta row (distance, time, elevation).
 *
 * @param array $meta Route meta.
 */
function adventure_blog_route_card_meta( $meta ) {
	$items = array();

	if ( ! empty( $meta['dystans'] ) ) {
		$items[] = array(
			'type'  => 'distance',
			'value' => $meta['dystans'],
		);
	}

	if ( ! empty( $meta['czas'] ) ) {
		$items[] = array(
			'type'  => 'time',
			'value' => $meta['czas'],
		);
	}

	if ( ! empty( $meta['przewyzszenie'] ) ) {
		$items[] = array(
			'type'  => 'elevation',
			'value' => $meta['przewyzszenie'],
		);
	}

	if ( empty( $items ) ) {
		return;
	}
	?>
	<ul class="route-card__meta">
		<?php foreach ( $items as $item ) : ?>
			<li>
				<?php adventure_blog_route_stat_icon( $item['type'] ); ?>
				<span><?php echo esc_html( $item['value'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Render one detail card in the route map panel.
 *
 * @param array $args Card args: icon, label, value, badge, gpx_key.
 */
function adventure_blog_route_detail_card( $args ) {
	$icon    = $args['icon'] ?? 'distance';
	$label   = $args['label'] ?? '';
	$value   = $args['value'] ?? '';
	$badge   = $args['badge'] ?? '';
	$gpx_key = $args['gpx_key'] ?? '';
	$classes = 'route-details__card';

	if ( $gpx_key ) {
		$classes .= ' route-details__card--gpx';
	}
	?>
	<li class="<?php echo esc_attr( $classes ); ?>">
		<div class="route-details__head">
			<?php adventure_blog_route_stat_icon( $icon ); ?>
			<span class="route-details__label"><?php echo esc_html( $label ); ?></span>
		</div>
		<?php if ( $badge ) : ?>
			<div class="route-details__value">
				<?php adventure_blog_difficulty_badge( $badge ); ?>
			</div>
		<?php else : ?>
			<p
				class="route-details__value"
				<?php echo $gpx_key ? 'id="route-gpx-' . esc_attr( $gpx_key ) . '"' : ''; ?>
			><?php echo esc_html( $value ); ?></p>
		<?php endif; ?>
	</li>
	<?php
}

/**
 * Render one glass stat card.
 *
 * @param string $type  Icon key.
 * @param string $label Stat label.
 * @param string $value Stat value.
 */
function adventure_blog_route_stat_card( $type, $label, $value ) {
	?>
	<li class="route-stats__card">
		<div class="route-stats__head">
			<?php adventure_blog_route_stat_icon( $type ); ?>
			<span class="route-stats__label"><?php echo esc_html( $label ); ?></span>
		</div>
		<p class="route-stats__value"><?php echo esc_html( $value ); ?></p>
	</li>
	<?php
}

/**
 * Render route meta stats row.
 *
 * @param array $meta Route meta.
 */
function adventure_blog_route_stats( $meta ) {
	$items = array();

	if ( ! empty( $meta['czas'] ) ) {
		$items[] = array(
			'type'  => 'time',
			'label' => __( 'Czas', 'adventure-blog' ),
			'value' => $meta['czas'],
		);
	}

	if ( ! empty( $meta['dystans'] ) ) {
		$items[] = array(
			'type'  => 'distance',
			'label' => __( 'Dystans', 'adventure-blog' ),
			'value' => $meta['dystans'],
		);
	}

	if ( ! empty( $meta['przewyzszenie'] ) ) {
		$items[] = array(
			'type'  => 'elevation',
			'label' => __( 'Przewyższenie', 'adventure-blog' ),
			'value' => $meta['przewyzszenie'],
		);
	}

	if ( empty( $items ) ) {
		return;
	}
	?>
	<ul class="route-stats">
		<?php
		foreach ( $items as $item ) {
			adventure_blog_route_stat_card( $item['type'], $item['label'], $item['value'] );
		}
		?>
	</ul>
	<?php
}

/**
 * Compact inline route stats (hero / overview).
 *
 * @param array $meta Route meta.
 */
function adventure_blog_route_stats_compact( $meta ) {
	$parts = array();

	if ( ! empty( $meta['czas'] ) ) {
		$parts[] = array(
			'type'  => 'time',
			'value' => $meta['czas'],
		);
	}

	if ( ! empty( $meta['dystans'] ) ) {
		$parts[] = array(
			'type'  => 'distance',
			'value' => $meta['dystans'],
		);
	}

	if ( ! empty( $meta['przewyzszenie'] ) ) {
		$parts[] = array(
			'type'  => 'elevation',
			'value' => $meta['przewyzszenie'],
		);
	}

	if ( empty( $parts ) ) {
		return;
	}
	?>
	<p class="route-stats-compact" aria-label="<?php esc_attr_e( 'Parametry trasy', 'adventure-blog' ); ?>">
		<?php
		foreach ( $parts as $index => $part ) {
			if ( $index > 0 ) {
				echo '<span class="route-stats-compact__sep" aria-hidden="true">·</span>';
			}
			?>
			<span class="route-stats-compact__item">
				<?php adventure_blog_route_stat_icon( $part['type'] ); ?>
				<span><?php echo esc_html( $part['value'] ); ?></span>
			</span>
			<?php
		}
		?>
	</p>
	<?php
}

/**
 * Intro copy for the route overview section.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function adventure_blog_get_route_overview_intro( $post_id ) {
	$meta = adventure_blog_get_route_meta( $post_id );

	if ( ! empty( $meta['overview_intro'] ) ) {
		return $meta['overview_intro'];
	}

	$excerpt = get_post_field( 'post_excerpt', $post_id );
	if ( $excerpt ) {
		return $excerpt;
	}

	return '';
}

/**
 * Detailed route specification panel (map section).
 *
 * @param int   $post_id Post ID.
 * @param array $meta    Route meta.
 */
function adventure_blog_route_details_panel( $post_id, $meta ) {
	$level = $meta['trudnosc'] ?? 'latwa';

	$manual_rows = array(
		array(
			'icon'  => 'difficulty',
			'label' => __( 'Trudność', 'adventure-blog' ),
			'badge' => $level,
		),
	);

	if ( ! empty( $meta['czas'] ) ) {
		$manual_rows[] = array(
			'icon'  => 'time',
			'label' => __( 'Czas', 'adventure-blog' ),
			'value' => $meta['czas'],
		);
	}

	if ( ! empty( $meta['dystans'] ) ) {
		$manual_rows[] = array(
			'icon'  => 'distance',
			'label' => __( 'Dystans', 'adventure-blog' ),
			'value' => $meta['dystans'],
		);
	}

	if ( ! empty( $meta['przewyzszenie'] ) ) {
		$manual_rows[] = array(
			'icon'  => 'elevation',
			'label' => __( 'Przewyższenie', 'adventure-blog' ),
			'value' => $meta['przewyzszenie'],
		);
	}

	$gpx_rows = array(
		array(
			'icon'    => 'gpx-distance',
			'label'   => __( 'Dystans', 'adventure-blog' ),
			'value'   => '—',
			'gpx_key' => 'distance',
		),
		array(
			'icon'    => 'ele-min',
			'label'   => __( 'Wysokość min.', 'adventure-blog' ),
			'value'   => '—',
			'gpx_key' => 'ele-min',
		),
		array(
			'icon'    => 'ele-max',
			'label'   => __( 'Wysokość maks.', 'adventure-blog' ),
			'value'   => '—',
			'gpx_key' => 'ele-max',
		),
		array(
			'icon'    => 'ele-gain',
			'label'   => __( 'Suma podejść', 'adventure-blog' ),
			'value'   => '—',
			'gpx_key' => 'ele-gain',
		),
	);

	$detail_rows = array_merge( $manual_rows, $gpx_rows );
	?>
	<div class="route-details">
		<section class="route-details__section" aria-label="<?php esc_attr_e( 'Szczegóły trasy', 'adventure-blog' ); ?>">
			<h3 class="route-details__section-title"><?php esc_html_e( 'Szczegóły trasy', 'adventure-blog' ); ?></h3>
			<ul class="route-details__grid">
				<?php
				foreach ( $detail_rows as $row ) {
					adventure_blog_route_detail_card( $row );
				}
				?>
			</ul>
		</section>
	</div>
	<?php
}

/**
 * Base args for Aktualności post queries (newest first).
 *
 * @param int $limit Posts per page.
 * @return array
 */
function adventure_blog_get_news_query_args( $limit = 3 ) {
	return array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'orderby'             => array(
			'date' => 'DESC',
			'ID'   => 'DESC',
		),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
}

/**
 * Whether archive listing should use route cards/grid.
 *
 * @return bool
 */
function adventure_blog_archive_uses_route_grid() {
	return is_post_type_archive( 'trasa' )
		|| is_tax( 'typ-trasy' )
		|| ( is_home() && ! is_front_page() );
}

/**
 * Latest Aktualności posts for a category (Tatry or Trasy rowerowe).
 *
 * @param string $category_slug Category slug.
 * @param int    $limit         Number of posts.
 * @return WP_Query
 */
function adventure_blog_get_posts_by_news_category( $category_slug, $limit = 3 ) {
	return adventure_blog_get_routes_by_term( $category_slug, $limit );
}

/**
 * Latest trasy from Tatry and Trasy rowerowe combined.
 *
 * @param int $limit Number of routes.
 * @return WP_Query
 */
function adventure_blog_get_latest_posts( $limit = 3 ) {
	return new WP_Query(
		array_merge(
			array(
				'posts_per_page'      => $limit,
				'post_status'         => 'publish',
				'orderby'             => array(
					'date' => 'DESC',
					'ID'   => 'DESC',
				),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			),
			adventure_blog_get_news_routes_query_args()
		)
	);
}

/**
 * Query routes by taxonomy slug.
 *
 * @param string $term_slug Taxonomy term slug.
 * @param int    $limit     Number of posts.
 * @return WP_Query
 */
function adventure_blog_get_routes_by_term( $term_slug, $limit = 3, $featured_only = false ) {
	$args = array(
		'post_type'      => 'trasa',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => array(
			'date' => 'DESC',
			'ID'   => 'DESC',
		),
		'tax_query'      => array(
			array(
				'taxonomy' => 'typ-trasy',
				'field'    => 'slug',
				'terms'    => $term_slug,
			),
		),
	);

	if ( $featured_only ) {
		$args['meta_query'] = array(
			array(
				'key'   => '_adventure_featured',
				'value' => '1',
			),
		);
	}

	$query = new WP_Query( $args );

	if ( $featured_only && $query->post_count < $limit ) {
		$exclude = wp_list_pluck( $query->posts, 'ID' );
		$fallback = new WP_Query(
			array(
				'post_type'      => 'trasa',
				'posts_per_page' => $limit - $query->post_count,
				'post__not_in'   => $exclude,
				'tax_query'      => $args['tax_query'],
			)
		);

		if ( $fallback->have_posts() ) {
			$query->posts      = array_merge( $query->posts, $fallback->posts );
			$query->post_count = count( $query->posts );
		}
	}

	return $query;
}

/**
 * Social links from customizer.
 *
 * @return array
 */
function adventure_blog_social_links() {
	$links = array();

	$instagram = get_theme_mod( 'adventure_instagram_url', '' );
	if ( $instagram ) {
		$links[] = array(
			'label' => 'Instagram',
			'url'   => $instagram,
		);
	}

	$strava = get_theme_mod( 'adventure_strava_url', '' );
	if ( $strava ) {
		$links[] = array(
			'label' => 'Strava',
			'url'   => $strava,
		);
	}

	$komoot = get_theme_mod( 'adventure_komoot_url', '' );
	if ( $komoot ) {
		$links[] = array(
			'label' => 'Komoot',
			'url'   => $komoot,
		);
	}

	return $links;
}

/**
 * Contact page details from the Customizer.
 *
 * @return array{phone: string, address: string, email: string, map_lat: string, map_lng: string, map_label: string}
 */
function adventure_blog_get_contact_details() {
	$email = get_theme_mod( 'adventure_contact_display_email', 'kontakt@6zone.pl' );
	if ( ! $email ) {
		$email = get_theme_mod( 'adventure_contact_email', get_option( 'admin_email' ) );
	}

	return array(
		'phone'     => get_theme_mod( 'adventure_contact_phone', '' ),
		'address'   => get_theme_mod( 'adventure_contact_address', __( 'Katowice, Polska', 'adventure-blog' ) ),
		'email'     => $email,
		'map_lat'   => get_theme_mod( 'adventure_contact_map_lat', '50.2649' ),
		'map_lng'   => get_theme_mod( 'adventure_contact_map_lng', '19.0238' ),
		'map_label' => get_theme_mod( 'adventure_contact_map_label', __( 'Katowice', 'adventure-blog' ) ),
	);
}

/**
 * Render a contact page icon.
 *
 * @param string $name Icon name.
 */
function adventure_blog_render_contact_icon( $name ) {
	$icons = array(
		'phone'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A15 15 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg>',
		'location'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>',
		'email'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/></svg>',
		'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return;
	}

	echo $icons[ $name ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG markup.
}

/**
 * Render footer contact links under the logo.
 */
function adventure_blog_render_footer_contact() {
	$details   = adventure_blog_get_contact_details();
	$instagram = get_theme_mod( 'adventure_instagram_url', 'https://www.instagram.com/zone6' );
	$insta_label = get_theme_mod( 'adventure_instagram_handle', 'ZONE6' );
	$has_contact = $details['email'] || ( $instagram && $insta_label );

	if ( ! $has_contact ) {
		return;
	}
	?>
	<ul class="site-footer__contact">
		<?php if ( $details['email'] ) : ?>
			<li>
				<a class="site-footer__contact-link" href="<?php echo esc_url( 'mailto:' . $details['email'] ); ?>">
					<span class="site-footer__contact-icon" aria-hidden="true">
						<?php adventure_blog_render_contact_icon( 'email' ); ?>
					</span>
					<span class="site-footer__contact-label"><?php echo esc_html( $details['email'] ); ?></span>
				</a>
			</li>
		<?php endif; ?>
		<?php if ( $instagram && $insta_label ) : ?>
			<li>
				<a class="site-footer__contact-link" href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="site-footer__contact-icon" aria-hidden="true">
						<?php adventure_blog_render_contact_icon( 'instagram' ); ?>
					</span>
					<span class="site-footer__contact-label"><?php echo esc_html( $insta_label ); ?></span>
				</a>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}

/**
 * Render site logo and name.
 *
 * @param string $class_prefix CSS block prefix (e.g. site-brand, site-footer__brand).
 */
function adventure_blog_render_brand( $class_prefix = 'site-brand' ) {
	$name = adventure_blog_get_name();
	$home = home_url( '/' );
	?>
	<a class="<?php echo esc_attr( $class_prefix ); ?>" href="<?php echo esc_url( $home ); ?>">
		<?php if ( has_custom_logo() ) : ?>
			<span class="<?php echo esc_attr( $class_prefix ); ?>__logo-wrap">
				<?php the_custom_logo(); ?>
			</span>
		<?php else : ?>
			<img
				src="<?php echo esc_url( ADVENTURE_BLOG_URI . '/assets/images/logo-white.png?v=3' ); ?>"
				alt="<?php echo esc_attr( $name ); ?>"
				class="<?php echo esc_attr( $class_prefix ); ?>__logo"
				width="170"
				height="44"
			>
		<?php endif; ?>
	</a>
	<?php
}

/**
 * Sanitize a URL for use inside an inline CSS url() value.
 *
 * @param string $url Image URL.
 * @return string
 */
function adventure_blog_css_image_url( $url ) {
	return esc_url_raw( $url );
}

/**
 * Build inline background-image style for a hero layer.
 *
 * @param string $url Image URL.
 * @return string
 */
function adventure_blog_hero_bg_style( $url ) {
	$url = adventure_blog_css_image_url( $url );

	if ( ! $url ) {
		return '';
	}

	$url = str_replace( array( "'", ')', '\\' ), array( '%27', '%29', '%5C' ), $url );

	return sprintf( "background-image: url('%s')", $url );
}

/**
 * Build an inline CSS custom property for a hero background image.
 *
 * @param string $url       Image URL.
 * @param string $var_name  CSS custom property name.
 * @return string
 */
function adventure_blog_hero_image_style( $url, $var_name = '--archive-hero-image' ) {
	$url = adventure_blog_css_image_url( $url );

	if ( ! $url ) {
		return '';
	}

	$url = str_replace( array( "'", ')', '\\' ), array( '%27', '%29', '%5C' ), $url );

	return sprintf( '%s: url(\'%s\')', $var_name, $url );
}

/**
 * Ensure a hero image URL is never empty.
 *
 * @param string $url          Candidate image URL.
 * @param string $fallback_key Archive image context key.
 * @return string
 */
function adventure_blog_ensure_hero_image( $url, $fallback_key = 'trasy' ) {
	$url = adventure_blog_css_image_url( $url );

	if ( $url ) {
		return $url;
	}

	return adventure_blog_get_archive_hero_image( $fallback_key );
}

/**
 * Footer background image URL.
 *
 * @return string
 */
function adventure_blog_get_footer_bg_image() {
	$custom = get_theme_mod( 'adventure_footer_bg_image', '' );

	if ( $custom ) {
		return $custom;
	}

	return adventure_blog_get_home_hero_image();
}

/**
 * Default hero images for archive sections.
 *
 * @return array<string, string>
 */
function adventure_blog_archive_hero_defaults() {
	return array(
		'tatry'          => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&q=80',
		'trasy-rowerowe' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80',
		'projekty'       => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?w=1920&q=80',
		'trasy'          => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80',
		'aktualnosci'    => 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1920&q=80',
		'kontakt'        => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1920&q=80',
	);
}

/**
 * Default hero descriptions for archives and pages.
 *
 * @return array<string, string>
 */
function adventure_blog_get_archive_hero_descriptions() {
	return array(
		'aktualnosci'    => __( 'Najnowsze wpisy, relacje i newsy ze szlaków.', 'adventure-blog' ),
		'trasy-rowerowe' => __( 'Trasy na rower — od krótkich wycieczek po całodniowe etapy.', 'adventure-blog' ),
		'tatry'          => __( 'Górskie trasy i szlaki w Tatrach.', 'adventure-blog' ),
		'projekty'       => __( 'Outdoorowe projekty, wyprawy i pomysły w realizacji.', 'adventure-blog' ),
		'trasy'          => __( 'Wszystkie trasy — górskie, rowerowe i wyprawowe.', 'adventure-blog' ),
		'kontakt'        => __( 'Masz pytanie? Napisz — chętnie odpowiem.', 'adventure-blog' ),
	);
}

/**
 * Resolve hero description for a context key.
 *
 * @param string $key Context slug.
 * @return string
 */
function adventure_blog_resolve_archive_hero_description( $key ) {
	$descriptions = adventure_blog_get_archive_hero_descriptions();

	return isset( $descriptions[ $key ] ) ? $descriptions[ $key ] : '';
}

/**
 * Front page hero image.
 *
 * @return string
 */
function adventure_blog_get_home_hero_image() {
	$default = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1920&q=80';
	$image   = get_theme_mod( 'adventure_hero_image', $default );
	return $image ? $image : $default;
}

/**
 * Hero image for an archive context key.
 *
 * @param string $key Context slug.
 * @return string
 */
function adventure_blog_get_archive_hero_image( $key ) {
	$defaults = adventure_blog_archive_hero_defaults();
	$mod_key  = 'adventure_archive_hero_' . str_replace( '-', '_', $key );
	$custom = trim( (string) get_theme_mod( $mod_key, '' ) );

	$broken_fragments = array(
		'photo-1454496522488',
		'photo-1541625602330',
		'photo-1486312338219',
	);

	if ( $custom ) {
		foreach ( $broken_fragments as $fragment ) {
			if ( false !== strpos( $custom, $fragment ) ) {
				$custom = '';
				break;
			}
		}
	}

	if ( $custom ) {
		return $custom;
	}

	if ( isset( $defaults[ $key ] ) ) {
		return $defaults[ $key ];
	}

	return $defaults['trasy'];
}

/**
 * Try to use the latest featured image from posts in a taxonomy term.
 *
 * @param string $term_slug Term slug.
 * @return string
 */
function adventure_blog_get_term_featured_hero_image( $term_slug ) {
	$defaults = adventure_blog_archive_hero_defaults();

	if ( isset( $defaults[ $term_slug ] ) ) {
		$image = adventure_blog_get_archive_hero_image( $term_slug );
		if ( $image ) {
			return $image;
		}
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'trasa',
			'posts_per_page' => 1,
			'meta_key'       => '_thumbnail_id',
			'tax_query'      => array(
				array(
					'taxonomy' => 'typ-trasy',
					'field'    => 'slug',
					'terms'    => $term_slug,
				),
			),
		)
	);

	if ( $query->have_posts() ) {
		$query->the_post();
		$url = get_the_post_thumbnail_url( get_the_ID(), 'route-hero' );
		wp_reset_postdata();
		if ( $url ) {
			return $url;
		}
	}

	return adventure_blog_get_archive_hero_image( $term_slug );
}

/**
 * Whether the current view should show a hero banner.
 *
 * @return bool
 */
function adventure_blog_has_hero_banner() {
	return is_front_page()
		|| is_singular( array( 'trasa', 'post' ) )
		|| adventure_blog_uses_archive_hero()
		|| is_page( array( 'o-mnie', 'kontakt' ) );
}

/**
 * Whether current archive should use a hero banner.
 *
 * @return bool
 */
function adventure_blog_uses_archive_hero() {
	return is_tax( 'typ-trasy' )
		|| is_post_type_archive( 'trasa' )
		|| is_category( adventure_blog_get_news_category_slugs() )
		|| ( is_home() && ! is_front_page() );
}

/**
 * Hero data for static pages.
 *
 * @param string $slug Page slug.
 * @return array{title: string, description: string, image: string}
 */
function adventure_blog_get_page_hero_data( $slug ) {
	$page = get_page_by_path( $slug );

	$data = array(
		'title'       => $page ? get_the_title( $page ) : '',
		'description' => '',
		'image'       => adventure_blog_get_archive_hero_image( $slug ),
	);

	if ( $page && 'kontakt' !== $slug ) {
		if ( $page->post_excerpt ) {
			$data['description'] = wp_strip_all_tags( $page->post_excerpt );
		} elseif ( $page->post_content ) {
			$data['description'] = wp_trim_words( wp_strip_all_tags( $page->post_content ), 28, '…' );
		}
	}

	if ( $page ) {
		if ( has_post_thumbnail( $page ) ) {
			$url = get_the_post_thumbnail_url( $page, 'route-hero' );
			if ( $url ) {
				$data['image'] = $url;
			}
		}
	}

	if ( ! $data['description'] && 'kontakt' !== $slug ) {
		$data['description'] = adventure_blog_resolve_archive_hero_description( $slug );
	}

	$data['image'] = adventure_blog_ensure_hero_image( $data['image'], $slug );

	return $data;
}

/**
 * Data for archive hero template.
 *
 * @return array{title: string, description: string, image: string}
 */
function adventure_blog_get_archive_hero_data() {
	$data = array(
		'title'       => '',
		'description' => '',
		'image'       => '',
	);

	if ( is_tax( 'typ-trasy' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$data['title']       = $term->name;
			$data['description'] = term_description( $term->term_id, 'typ-trasy' );
			$data['description'] = $data['description'] ? wp_strip_all_tags( $data['description'] ) : '';
			$data['image']       = adventure_blog_get_term_featured_hero_image( $term->slug );

			if ( ! $data['description'] ) {
				$data['description'] = adventure_blog_resolve_archive_hero_description( $term->slug );
			}
		}
	} elseif ( is_post_type_archive( 'trasa' ) ) {
		$data['title']       = post_type_archive_title( '', false );
		$data['image']       = adventure_blog_get_archive_hero_image( 'trasy' );
		$data['description'] = adventure_blog_resolve_archive_hero_description( 'trasy' );
	} elseif ( is_home() && ! is_front_page() ) {
		$posts_page = get_post( get_option( 'page_for_posts' ) );

		$data['title'] = $posts_page ? get_the_title( $posts_page ) : __( 'Aktualności', 'adventure-blog' );
		$data['image'] = adventure_blog_get_archive_hero_image( 'aktualnosci' );

		if ( $posts_page ) {
			if ( $posts_page->post_excerpt ) {
				$data['description'] = wp_strip_all_tags( $posts_page->post_excerpt );
			} elseif ( $posts_page->post_content ) {
				$data['description'] = wp_trim_words( wp_strip_all_tags( $posts_page->post_content ), 28, '…' );
			}

			if ( has_post_thumbnail( $posts_page ) ) {
				$url = get_the_post_thumbnail_url( $posts_page, 'route-hero' );
				if ( $url ) {
					$data['image'] = $url;
				}
			}
		}

		if ( ! $data['description'] ) {
			$data['description'] = adventure_blog_resolve_archive_hero_description( 'aktualnosci' );
		}
	} elseif ( is_category() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			$data['title']       = $term->name;
			$data['description'] = $term->description ? wp_strip_all_tags( $term->description ) : '';
			$data['image']       = adventure_blog_get_archive_hero_image( $term->slug );

			if ( ! $data['description'] ) {
				$data['description'] = adventure_blog_resolve_archive_hero_description( $term->slug );
			}
		}
	}

	if ( ! $data['image'] ) {
		$data['image'] = adventure_blog_get_archive_hero_image( 'trasy' );
	} else {
		$fallback_key = 'trasy';
		if ( is_tax( 'typ-trasy' ) ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$fallback_key = $term->slug;
			}
		} elseif ( is_home() && ! is_front_page() ) {
			$fallback_key = 'aktualnosci';
		} elseif ( is_category() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$fallback_key = $term->slug;
			}
		}

		$data['image'] = adventure_blog_ensure_hero_image( $data['image'], $fallback_key );
	}

	return $data;
}

/**
 * Fallback hero image for single posts without a thumbnail.
 *
 * @return string
 */
function adventure_blog_get_post_hero_fallback_image() {
	return adventure_blog_get_archive_hero_image( 'aktualnosci' );
}
