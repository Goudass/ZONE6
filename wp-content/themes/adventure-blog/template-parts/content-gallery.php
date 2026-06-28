<?php
/**
 * Photo gallery slider for posts and routes.
 *
 * @package Adventure_Blog
 */

$gallery_ids = adventure_blog_get_gallery_ids();

if ( empty( $gallery_ids ) ) {
	return;
}
?>
<section class="route-gallery route-gallery--editorial reveal container" aria-label="<?php esc_attr_e( 'Galeria zdjęć', 'adventure-blog' ); ?>">
	<h2><?php esc_html_e( 'Galeria', 'adventure-blog' ); ?></h2>
	<div class="swiper route-gallery__swiper">
		<div class="swiper-wrapper">
			<?php foreach ( $gallery_ids as $attachment_id ) : ?>
				<div class="swiper-slide">
					<?php echo wp_get_attachment_image( (int) $attachment_id, 'gallery-thumb' ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Poprzednie zdjęcie', 'adventure-blog' ); ?>"></div>
		<div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Następne zdjęcie', 'adventure-blog' ); ?>"></div>
		<div class="swiper-pagination"></div>
	</div>
</section>
