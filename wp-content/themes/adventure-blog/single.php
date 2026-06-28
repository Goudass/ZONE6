<?php
/**
 * Single blog post template.
 *
 * @package Adventure_Blog
 */

get_header();

while ( have_posts() ) :
	the_post();

	$hero_image = adventure_blog_ensure_hero_image(
		has_post_thumbnail()
			? get_the_post_thumbnail_url( get_the_ID(), 'route-hero' )
			: adventure_blog_get_post_hero_fallback_image(),
		'aktualnosci'
	);
	$hero_style = adventure_blog_hero_image_style( $hero_image, '--post-hero-image' );
	?>
	<article <?php post_class( 'post-single' ); ?>>
		<section class="post-hero"<?php echo $hero_style ? ' style="' . $hero_style . '"' : ''; ?>>
			<div class="post-hero__overlay"></div>
			<div class="container post-hero__content">
				<time class="post-hero__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( adventure_blog_format_post_date() ); ?></time>
				<h1 class="post-hero__title"><?php the_title(); ?></h1>
			</div>
		</section>

		<div class="post-single__surface section section--surface section--band-a">
			<div class="container post-single__body reveal">
				<div class="post-content">
					<?php the_content(); ?>
				</div>
			</div>

			<?php get_template_part( 'template-parts/content', 'gallery' ); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
