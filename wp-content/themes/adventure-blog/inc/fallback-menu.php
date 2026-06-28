<?php
/**
 * Fallback menu when no menu assigned.
 */
function adventure_blog_fallback_menu() {
	$items = array(
		home_url( '/o-mnie/' )         => __( 'O mnie', 'adventure-blog' ),
		home_url( '/aktualnosci/' )    => __( 'Aktualności', 'adventure-blog' ),
		adventure_blog_get_news_category_link( 'trasy-rowerowe' ) => __( 'Trasy rowerowe', 'adventure-blog' ),
		adventure_blog_get_news_category_link( 'tatry' ) => __( 'Tatry', 'adventure-blog' ),
		home_url( '/typ-trasy/projekty/' ) => __( 'Projekty', 'adventure-blog' ),
		home_url( '/kontakt/' )        => __( 'Kontakt', 'adventure-blog' ),
	);

	echo '<ul class="site-nav__list">';
	foreach ( $items as $url => $label ) {
		printf(
			'<li><a href="%1$s">%2$s</a></li>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</ul>';
}
