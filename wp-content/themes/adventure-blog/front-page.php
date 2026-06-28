<?php
/**
 * Front page template.
 *
 * @package Adventure_Blog
 */

get_header();

$hero_image = adventure_blog_get_home_hero_image();
$hero_style = adventure_blog_hero_image_style( $hero_image, '--hero-image' );
?>

<section class="hero"<?php echo $hero_style ? ' style="' . $hero_style . '"' : ''; ?> aria-hidden="true">
	<div class="hero__overlay"></div>
</section>

<?php
$news_sections = array(
	array(
		'title'     => __( 'Aktualności', 'adventure-blog' ),
		'link'      => adventure_blog_get_aktualnosci_link(),
		'link_text' => __( 'Zobacz więcej', 'adventure-blog' ),
		'limit'     => 3,
	),
	array(
		'title'     => __( 'Tatry', 'adventure-blog' ),
		'slug'      => 'tatry',
		'link'      => adventure_blog_get_news_category_link( 'tatry' ),
		'link_text' => __( 'Zobacz więcej', 'adventure-blog' ),
		'limit'     => 3,
	),
	array(
		'title'     => __( 'Trasy rowerowe', 'adventure-blog' ),
		'slug'      => 'trasy-rowerowe',
		'link'      => adventure_blog_get_news_category_link( 'trasy-rowerowe' ),
		'link_text' => __( 'Zobacz więcej', 'adventure-blog' ),
		'limit'     => 3,
	),
);
?>

<section class="section section--home-body section--band-a section--about">
	<?php get_template_part( 'template-parts/home', 'about' ); ?>

	<div class="home-news">
		<?php
		foreach ( $news_sections as $section ) :
			$limit = $section['limit'] ?? 3;
			$query = isset( $section['slug'] )
				? adventure_blog_get_routes_by_term( $section['slug'], $limit )
				: adventure_blog_get_latest_posts( $limit );
			?>
			<section class="section section--news">
				<div class="container">
					<div class="section__header reveal reveal--up">
						<h2><?php echo esc_html( $section['title'] ); ?></h2>
						<a class="section__link" href="<?php echo esc_url( $section['link'] ); ?>"><?php echo esc_html( $section['link_text'] ); ?> →</a>
					</div>
					<?php if ( $query->have_posts() ) : ?>
						<div class="route-grid">
							<?php
							while ( $query->have_posts() ) :
								$query->the_post();
								get_template_part( 'template-parts/route', 'card' );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					<?php else : ?>
						<p class="empty-state"><?php esc_html_e( 'Brak aktualności w tej kategorii.', 'adventure-blog' ); ?></p>
					<?php endif; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
</section>

<?php
get_footer();
