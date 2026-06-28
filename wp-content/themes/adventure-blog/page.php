<?php
/**
 * Default page template.
 *
 * @package Adventure_Blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$slug = get_post_field( 'post_name', get_the_ID() );

	if ( 'o-mnie' === $slug ) :
		get_template_part( 'template-parts/about', 'hero' );

		$page_body = trim( wp_strip_all_tags( (string) get_the_content() ) );
		if ( $page_body ) :
			?>
			<section class="section section--band-b section--about-page">
				<div class="container page-content reveal">
					<?php the_content(); ?>
				</div>
			</section>
			<?php
		endif;
	elseif ( 'kontakt' === $slug ) :
		get_template_part( 'template-parts/archive', 'hero', adventure_blog_get_page_hero_data( $slug ) );
		get_template_part( 'template-parts/contact', 'page' );
	else :
		?>
	<section class="page-header reveal">
		<div class="container">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>

	<section class="section">
		<div class="container page-content reveal">
			<?php the_content(); ?>
		</div>
	</section>
		<?php
	endif;
endwhile;

get_footer();
