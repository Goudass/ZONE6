<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="container site-header__inner">
		<?php adventure_blog_render_brand( 'site-brand' ); ?>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-menu" aria-label="<?php esc_attr_e( 'Menu', 'adventure-blog' ); ?>">
			<span></span><span></span><span></span>
		</button>

		<nav class="site-nav" id="primary-menu" aria-label="<?php esc_attr_e( 'Menu główne', 'adventure-blog' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'site-nav__list',
					'fallback_cb'    => 'adventure_blog_fallback_menu',
				)
			);
			?>
		</nav>
	</div>
</header>

<main class="site-main">
