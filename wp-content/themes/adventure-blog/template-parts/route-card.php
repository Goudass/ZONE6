<?php
/**
 * Route card partial.
 *
 * @package Adventure_Blog
 */

$meta = adventure_blog_get_route_meta( get_the_ID() );
?>
<article <?php post_class( 'route-card reveal' ); ?>>
	<a class="route-card__link" href="<?php the_permalink(); ?>">
		<div class="route-card__media">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'route-card' );
			} else {
				echo '<img src="https://images.unsplash.com/photo-1541625602330-227af2454372?auto=format&fit=crop&w=640&q=80" alt="" loading="lazy">';
			}
			?>
		</div>
		<div class="route-card__body">
			<h3 class="route-card__title"><?php the_title(); ?></h3>
			<?php adventure_blog_route_card_meta( $meta ); ?>
			<?php adventure_blog_difficulty_badge( $meta['trudnosc'] ); ?>
		</div>
	</a>
</article>
