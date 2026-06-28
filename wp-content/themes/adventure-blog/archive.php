<?php
/**
 * Archive template for routes and posts.
 *
 * @package Adventure_Blog
 */

get_header();
?>

<div class="archive-page section--surface section--band-a">
<?php
if ( adventure_blog_uses_archive_hero() ) {
	get_template_part( 'template-parts/archive', 'hero', adventure_blog_get_archive_hero_data() );
} else {
	?>
	<section class="page-header reveal">
		<div class="container">
			<h1><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<p><?php the_archive_description(); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
?>

<section class="section section--archive reveal">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="<?php echo adventure_blog_archive_uses_route_grid() ? 'route-grid' : 'news-grid'; ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'trasa' === get_post_type() ) {
						get_template_part( 'template-parts/route', 'card' );
					} else {
						get_template_part( 'template-parts/content', 'card' );
					}
				endwhile;
				?>
			</div>
			<div class="pagination">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Brak wpisów do wyświetlenia.', 'adventure-blog' ); ?></p>
		<?php endif; ?>
	</div>
</section>
</div>

<?php
get_footer();
