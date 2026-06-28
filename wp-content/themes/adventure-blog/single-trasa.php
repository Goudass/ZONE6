<?php
/**
 * Single route template.
 *
 * @package Adventure_Blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$meta     = adventure_blog_get_route_meta( get_the_ID() );
	$gpx_url  = $meta['gpx_id'] ? wp_get_attachment_url( $meta['gpx_id'] ) : '';
	$terms    = wp_get_post_terms( get_the_ID(), 'typ-trasy', array( 'fields' => 'slugs' ) );
	$term_key = ! empty( $terms ) ? $terms[0] : 'trasy';
	$hero_url = adventure_blog_ensure_hero_image(
		has_post_thumbnail()
			? get_the_post_thumbnail_url( get_the_ID(), 'route-hero' )
			: adventure_blog_get_archive_hero_image( $term_key ),
		$term_key
	);
	$hero_style = adventure_blog_hero_image_style( $hero_url, '--route-hero-image' );
	?>
	<article <?php post_class( 'route-single' ); ?>>
		<section class="route-hero"<?php echo $hero_style ? ' style="' . $hero_style . '"' : ''; ?>>
			<div class="route-hero__overlay"></div>
			<div class="container route-hero__content">
				<?php adventure_blog_difficulty_badge( $meta['trudnosc'] ); ?>
				<h1 class="route-hero__title"><?php the_title(); ?></h1>
				<?php adventure_blog_route_stats_compact( $meta ); ?>
			</div>
		</section>

		<div class="route-single__body">
			<div class="route-content route-content--editorial reveal">
				<?php
				remove_filter( 'the_content', 'wpautop' );
				the_content();
				add_filter( 'the_content', 'wpautop' );
				?>
			</div>

			<?php if ( $gpx_url ) : ?>
				<section class="route-map-section route-map-section--editorial reveal" aria-label="<?php esc_attr_e( 'Mapa trasy', 'adventure-blog' ); ?>">
					<div class="container route-map-section__header">
						<p class="route-editorial__eyebrow"><?php esc_html_e( 'Nawigacja', 'adventure-blog' ); ?></p>
						<div class="route-map-section__title-row">
							<h2><?php esc_html_e( 'Mapa trasy', 'adventure-blog' ); ?></h2>
							<a class="btn btn--small btn--outline" href="<?php echo esc_url( $gpx_url ); ?>" download><?php esc_html_e( 'Pobierz GPX', 'adventure-blog' ); ?></a>
						</div>
					</div>
					<div class="container route-map-section__map-wrap">
						<?php adventure_blog_route_details_panel( get_the_ID(), $meta ); ?>
						<div id="route-map" class="route-map" data-gpx-url="<?php echo esc_url( $gpx_url ); ?>"></div>
						<div class="route-elevation route-elevation--compact">
							<div class="route-elevation__header">
								<h3><?php esc_html_e( 'Profil przewyższenia', 'adventure-blog' ); ?></h3>
							</div>
							<ul class="route-elevation__metrics" aria-label="<?php esc_attr_e( 'Podsumowanie profilu', 'adventure-blog' ); ?>">
								<li class="route-elevation__metric">
									<span class="route-elevation__metric-label"><?php esc_html_e( 'Dystans', 'adventure-blog' ); ?></span>
									<span class="route-elevation__metric-value" id="route-ele-stat-distance">—</span>
								</li>
								<li class="route-elevation__metric">
									<span class="route-elevation__metric-label"><?php esc_html_e( 'Min.', 'adventure-blog' ); ?></span>
									<span class="route-elevation__metric-value" id="route-ele-stat-min">—</span>
								</li>
								<li class="route-elevation__metric">
									<span class="route-elevation__metric-label"><?php esc_html_e( 'Maks.', 'adventure-blog' ); ?></span>
									<span class="route-elevation__metric-value" id="route-ele-stat-max">—</span>
								</li>
								<li class="route-elevation__metric">
									<span class="route-elevation__metric-label"><?php esc_html_e( 'Suma podejść', 'adventure-blog' ); ?></span>
									<span class="route-elevation__metric-value" id="route-ele-stat-gain">—</span>
								</li>
							</ul>
							<div class="route-elevation__chart-wrap">
								<canvas id="route-elevation-chart"></canvas>
							</div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/content', 'gallery' ); ?>
		</div>
	</article>

	<section class="section section--band-b section--related-routes reveal">
		<div class="container">
			<div class="section__header reveal reveal--up">
				<h2 class="section__title"><?php esc_html_e( 'Powiązane trasy', 'adventure-blog' ); ?></h2>
			</div>
			<div class="route-grid">
				<?php
				$terms = wp_get_post_terms( get_the_ID(), 'typ-trasy', array( 'fields' => 'ids' ) );
				$related = new WP_Query(
					array(
						'post_type'      => 'trasa',
						'posts_per_page' => 3,
						'post__not_in'   => array( get_the_ID() ),
						'tax_query'      => ! empty( $terms ) ? array(
							array(
								'taxonomy' => 'typ-trasy',
								'field'    => 'term_id',
								'terms'    => $terms,
							),
						) : array(),
					)
				);
				if ( $related->have_posts() ) :
					while ( $related->have_posts() ) :
						$related->the_post();
						get_template_part( 'template-parts/route', 'card' );
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
