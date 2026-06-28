<?php
/**
 * Archive / taxonomy hero banner.
 *
 * @package Adventure_Blog
 *
 * @var array $args Template args.
 */

$title    = isset( $args['title'] ) ? $args['title'] : '';
$description = isset( $args['description'] ) ? $args['description'] : '';
$image    = isset( $args['image'] ) ? $args['image'] : adventure_blog_get_archive_hero_image( 'trasy' );
$image    = adventure_blog_ensure_hero_image( $image, 'trasy' );
$bg_style = adventure_blog_hero_bg_style( $image );
?>
<section class="archive-hero">
	<?php if ( $bg_style ) : ?>
		<div class="archive-hero__bg" style="<?php echo $bg_style; ?>" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="archive-hero__overlay"></div>
	<div class="container archive-hero__content">
		<?php if ( $title ) : ?>
			<h1 class="archive-hero__title reveal reveal--up"><?php echo esc_html( $title ); ?></h1>
		<?php endif; ?>
	</div>
</section>
