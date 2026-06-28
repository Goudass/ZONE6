<?php
/**
 * Home page — short about teaser (inner content only).
 *
 * @package Adventure_Blog
 */

$about_page = get_page_by_path( 'o-mnie' );
$about_url  = $about_page ? get_permalink( $about_page ) : home_url( '/o-mnie/' );
$about_copy = adventure_blog_get_about_copy();
$teaser     = get_theme_mod( 'adventure_home_about_teaser', '' );
$image      = adventure_blog_get_home_about_image();
?>
<div class="home-about">
	<div class="home-about__inner">
		<div class="home-about__content route-editorial__text route-editorial__text--framed reveal reveal--left">
			<h2 class="home-about__title"><?php esc_html_e( 'O mnie', 'adventure-blog' ); ?></h2>
			<?php if ( $teaser ) : ?>
				<p class="home-about__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $teaser ), 42, '…' ) ); ?></p>
			<?php else : ?>
				<?php if ( $about_copy['tagline'] ) : ?>
					<p class="home-about__tagline"><?php echo esc_html( $about_copy['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( $about_copy['intro'] ) : ?>
					<p class="home-about__text"><?php echo esc_html( $about_copy['intro'] ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
			<a class="btn btn--outline home-about__link" href="<?php echo esc_url( $about_url ); ?>">
				<?php esc_html_e( 'Czytaj więcej', 'adventure-blog' ); ?>
			</a>
		</div>
		<figure class="home-about__media reveal reveal--right">
			<img
				src="<?php echo esc_url( $image ); ?>"
				alt="<?php esc_attr_e( 'Zdjęcie autora bloga', 'adventure-blog' ); ?>"
				width="900"
				height="900"
				loading="lazy"
			>
		</figure>
	</div>
</div>
