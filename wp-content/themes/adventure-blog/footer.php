</main>

<?php
$footer_bg   = adventure_blog_get_home_hero_image();
$footer_style = adventure_blog_hero_image_style( $footer_bg, '--footer-bg-image' );
?>
<footer class="site-footer"<?php echo $footer_style ? ' style="' . $footer_style . '"' : ''; ?>>
	<div class="site-footer__overlay" aria-hidden="true"></div>
	<div class="site-footer__content">
		<div class="container site-footer__inner">
			<div class="site-footer__col site-footer__col--brand">
				<?php adventure_blog_render_brand( 'site-footer__brand' ); ?>
				<?php adventure_blog_render_footer_contact(); ?>
			</div>

			<nav class="site-footer__col site-footer__nav" aria-label="<?php esc_attr_e( 'Menu stopki', 'adventure-blog' ); ?>">
				<h3 class="site-footer__heading"><?php esc_html_e( 'Nawigacja', 'adventure-blog' ); ?></h3>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'site-footer__list',
						'fallback_cb'    => 'adventure_blog_fallback_menu',
					)
				);
				?>
			</nav>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
