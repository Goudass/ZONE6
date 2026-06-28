<?php
/**
 * About page hero banner.
 *
 * @package Adventure_Blog
 */

$image      = adventure_blog_ensure_hero_image( adventure_blog_get_about_hero_image(), 'kontakt' );
$about_copy = adventure_blog_get_about_copy();
$bg_style   = adventure_blog_hero_bg_style( $image );
?>
<section class="about-hero archive-hero">
	<?php if ( $bg_style ) : ?>
		<div class="archive-hero__bg" style="<?php echo $bg_style; ?>" aria-hidden="true"></div>
	<?php endif; ?>
	<div class="archive-hero__overlay"></div>
	<div class="container archive-hero__content">
		<h1 class="archive-hero__title reveal reveal--up"><?php the_title(); ?></h1>
		<?php if ( $about_copy['tagline'] ) : ?>
			<p class="about-hero__tagline reveal reveal--up"><?php echo esc_html( $about_copy['tagline'] ); ?></p>
		<?php endif; ?>
		<?php if ( $about_copy['intro'] ) : ?>
			<p class="about-hero__intro archive-hero__description reveal reveal--up"><?php echo esc_html( $about_copy['intro'] ); ?></p>
		<?php endif; ?>
	</div>
</section>
