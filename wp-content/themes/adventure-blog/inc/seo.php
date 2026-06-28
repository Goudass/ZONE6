<?php
/**
 * Basic SEO: meta description, Open Graph, XML sitemap.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register SEO meta box for posts and routes.
 */
function adventure_blog_register_seo_meta_box() {
	$post_types = array( 'post', 'trasa', 'page' );

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'adventure_seo',
			__( 'SEO', 'adventure-blog' ),
			'adventure_blog_render_seo_meta_box',
			$post_type,
			'normal',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'adventure_blog_register_seo_meta_box' );

/**
 * Render SEO meta box.
 *
 * @param WP_Post $post Current post.
 */
function adventure_blog_render_seo_meta_box( $post ) {
	wp_nonce_field( 'adventure_seo_meta', 'adventure_seo_meta_nonce' );

	$title       = get_post_meta( $post->ID, '_adventure_seo_title', true );
	$description = get_post_meta( $post->ID, '_adventure_seo_description', true );
	?>
	<p>
		<label for="adventure_seo_title"><strong><?php esc_html_e( 'Meta title', 'adventure-blog' ); ?></strong></label><br>
		<input type="text" name="adventure_seo_title" id="adventure_seo_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>">
	</p>
	<p>
		<label for="adventure_seo_description"><strong><?php esc_html_e( 'Meta description', 'adventure-blog' ); ?></strong></label><br>
		<textarea name="adventure_seo_description" id="adventure_seo_description" rows="3" class="widefat" placeholder="<?php esc_attr_e( 'Krótki opis dla wyszukiwarek (do 160 znaków)', 'adventure-blog' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
	</p>
	<?php
}

/**
 * Save SEO meta.
 *
 * @param int $post_id Post ID.
 */
function adventure_blog_save_seo_meta( $post_id ) {
	if ( ! isset( $_POST['adventure_seo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adventure_seo_meta_nonce'] ) ), 'adventure_seo_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_adventure_seo_title', isset( $_POST['adventure_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_seo_title'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_seo_description', isset( $_POST['adventure_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['adventure_seo_description'] ) ) : '' );
}
add_action( 'save_post', 'adventure_blog_save_seo_meta' );

/**
 * Get SEO description for current page.
 *
 * @return string
 */
function adventure_blog_get_seo_description() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_adventure_seo_description', true );
		if ( $custom ) {
			return $custom;
		}

		$post = get_queried_object();
		if ( $post instanceof WP_Post && ! empty( $post->post_excerpt ) ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}

		if ( $post instanceof WP_Post && ! empty( $post->post_content ) ) {
			return wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );
		}
	}

	if ( is_home() || is_front_page() ) {
		return get_bloginfo( 'description' );
	}

	return '';
}

/**
 * Get SEO title for current page.
 *
 * @return string
 */
function adventure_blog_get_seo_title() {
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_adventure_seo_title', true );
		if ( $custom ) {
			return $custom;
		}
	}

	return wp_get_document_title();
}

/**
 * Get Open Graph image URL.
 *
 * @return string
 */
function adventure_blog_get_og_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_queried_object_id(), 'route-hero' );
		if ( $url ) {
			return $url;
		}
	}

	if ( is_front_page() ) {
		return get_theme_mod(
			'adventure_hero_image',
			'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1920&q=80'
		);
	}

	return '';
}

/**
 * Output meta description and Open Graph tags.
 */
function adventure_blog_output_seo_tags() {
	$description = adventure_blog_get_seo_description();
	$title       = adventure_blog_get_seo_title();
	$image       = adventure_blog_get_og_image();
	$url         = is_singular() ? get_permalink() : home_url( '/' );

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	}

	echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $title ) ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $description ) ) );
	}

	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	}

	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( adventure_blog_get_name() ) );
}
add_action( 'wp_head', 'adventure_blog_output_seo_tags', 5 );

/**
 * Register sitemap rewrite rule.
 */
function adventure_blog_sitemap_rewrite() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?adventure_sitemap=1', 'top' );
}
add_action( 'init', 'adventure_blog_sitemap_rewrite' );

/**
 * Register sitemap query var.
 *
 * @param array $vars Query vars.
 * @return array
 */
function adventure_blog_sitemap_query_var( $vars ) {
	$vars[] = 'adventure_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'adventure_blog_sitemap_query_var' );

/**
 * Render XML sitemap.
 */
function adventure_blog_render_sitemap() {
	if ( ! get_query_var( 'adventure_sitemap' ) ) {
		return;
	}

	header( 'Content-Type: application/xml; charset=UTF-8' );

	$post_types = array( 'post', 'page', 'trasa' );
	$urls       = array(
		array(
			'loc'     => home_url( '/' ),
			'lastmod' => gmdate( 'c' ),
		),
	);

	foreach ( $post_types as $post_type ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		foreach ( $posts as $post ) {
			$urls[] = array(
				'loc'     => get_permalink( $post ),
				'lastmod' => get_post_modified_time( 'c', true, $post ),
			);
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	foreach ( $urls as $entry ) {
		echo "  <url>\n";
		echo '    <loc>' . esc_url( $entry['loc'] ) . "</loc>\n";
		echo '    <lastmod>' . esc_html( $entry['lastmod'] ) . "</lastmod>\n";
		echo "  </url>\n";
	}

	echo "</urlset>\n";
	exit;
}
add_action( 'template_redirect', 'adventure_blog_render_sitemap' );
