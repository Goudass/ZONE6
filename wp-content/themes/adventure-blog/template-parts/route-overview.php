<?php
/**
 * Route overview — visual intro with GPX line overlay.
 *
 * @package Adventure_Blog
 */

$post_id    = get_the_ID();
$meta       = adventure_blog_get_route_meta( $post_id );
$gpx_url    = $meta['gpx_id'] ? wp_get_attachment_url( $meta['gpx_id'] ) : '';
$intro      = adventure_blog_get_route_overview_intro( $post_id );
$image_url  = has_post_thumbnail()
	? get_the_post_thumbnail_url( $post_id, 'route-hero' )
	: '';
$route_start = $meta['route_start'] ?: __( 'Start', 'adventure-blog' );
$route_end   = $meta['route_end'] ?: __( 'Meta', 'adventure-blog' );
?>
<section
	class="route-overview reveal"
	<?php echo $gpx_url ? ' data-gpx-url="' . esc_url( $gpx_url ) . '"' : ''; ?>
	aria-label="<?php esc_attr_e( 'Przegląd trasy', 'adventure-blog' ); ?>"
>
	<div class="route-overview__visual">
		<?php if ( $image_url ) : ?>
			<img
				class="route-overview__image"
				src="<?php echo esc_url( $image_url ); ?>"
				alt=""
				loading="eager"
				decoding="async"
			>
		<?php endif; ?>
		<div class="route-overview__overlay" aria-hidden="true"></div>

		<?php if ( $gpx_url ) : ?>
			<svg class="route-overview__svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
				<polyline class="route-overview__line" points=""></polyline>
				<circle class="route-overview__dot route-overview__dot--start" r="1.8" cx="0" cy="0"></circle>
				<circle class="route-overview__dot route-overview__dot--end" r="1.8" cx="0" cy="0"></circle>
			</svg>
		<?php endif; ?>

		<div class="route-overview__top">
			<?php adventure_blog_route_stats_compact( $meta ); ?>
		</div>

		<div class="route-overview__markers">
			<span class="route-overview__marker route-overview__marker--start">
				<?php echo esc_html( $route_start ); ?>
			</span>
			<span class="route-overview__marker route-overview__marker--end">
				<?php echo esc_html( $route_end ); ?>
			</span>
		</div>
	</div>

	<?php if ( $intro ) : ?>
		<div class="container route-overview__intro">
			<p class="route-editorial__eyebrow"><?php esc_html_e( 'Przegląd trasy', 'adventure-blog' ); ?></p>
			<p class="route-overview__lead"><?php echo esc_html( $intro ); ?></p>
		</div>
	<?php endif; ?>
</section>
