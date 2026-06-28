<?php
/**
 * Blog post card partial.
 *
 * @package Adventure_Blog
 */
?>
<article <?php post_class( 'content-card reveal' ); ?>>
	<a class="content-card__link" href="<?php the_permalink(); ?>">
		<div class="content-card__media">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'route-card' );
			} else {
				echo '<img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=640&q=80" alt="" loading="lazy">';
			}
			?>
		</div>
		<div class="content-card__body">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( adventure_blog_format_post_date() ); ?></time>
			<h3><?php the_title(); ?></h3>
			<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
		</div>
	</a>
</article>
