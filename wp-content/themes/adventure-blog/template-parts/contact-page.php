<?php
/**
 * Contact page layout.
 *
 * @package Adventure_Blog
 */

$details   = adventure_blog_get_contact_details();
$instagram = get_theme_mod( 'adventure_instagram_url', '' );
?>
<section class="section section--contact-form section--band-a section--archive reveal">
	<div class="container contact-form-wrap">
		<p class="contact-form__eyebrow"><?php esc_html_e( 'Masz pytanie? Napisz — chętnie odpowiem.', 'adventure-blog' ); ?></p>

		<?php adventure_blog_contact_notice(); ?>

		<div class="contact-form-layout">
			<form class="contact-form contact-form--page" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="adventure_contact">
				<?php wp_nonce_field( 'adventure_contact_form', 'adventure_contact_nonce' ); ?>

				<div class="contact-form__grid">
					<label class="contact-form__field">
						<span class="screen-reader-text"><?php esc_html_e( 'Imię', 'adventure-blog' ); ?></span>
						<input type="text" name="contact_name" placeholder="<?php esc_attr_e( 'Twoje imię', 'adventure-blog' ); ?>" required>
					</label>
					<label class="contact-form__field">
						<span class="screen-reader-text"><?php esc_html_e( 'Email', 'adventure-blog' ); ?></span>
						<input type="email" name="contact_email" placeholder="<?php esc_attr_e( 'Twój email', 'adventure-blog' ); ?>" required>
					</label>
					<label class="contact-form__field contact-form__field--full">
						<span class="screen-reader-text"><?php esc_html_e( 'Temat', 'adventure-blog' ); ?></span>
						<input type="text" name="contact_subject" placeholder="<?php esc_attr_e( 'Temat', 'adventure-blog' ); ?>">
					</label>
					<label class="contact-form__field contact-form__field--full">
						<span class="screen-reader-text"><?php esc_html_e( 'Wiadomość', 'adventure-blog' ); ?></span>
						<textarea name="contact_message" rows="6" placeholder="<?php esc_attr_e( 'Wiadomość', 'adventure-blog' ); ?>" required></textarea>
					</label>
				</div>

				<div class="contact-form__actions">
					<button type="submit" class="btn btn--primary contact-form__submit">
						<?php esc_html_e( 'Wyślij wiadomość', 'adventure-blog' ); ?>
					</button>
				</div>
			</form>

			<aside
				class="contact-map-panel reveal reveal--right"
				aria-label="<?php esc_attr_e( 'Mapa — Katowice', 'adventure-blog' ); ?>"
			>
				<div
					id="contact-map"
					class="contact-map contact-map--square"
					data-lat="<?php echo esc_attr( $details['map_lat'] ); ?>"
					data-lng="<?php echo esc_attr( $details['map_lng'] ); ?>"
					data-label="<?php echo esc_attr( $details['map_label'] ); ?>"
				></div>
				<p class="contact-map-panel__caption"><?php echo esc_html( $details['address'] ); ?></p>
			</aside>
		</div>
	</div>
</section>

<section class="section section--contact-social section--band-b reveal">
	<div class="container contact-social-block">
		<h2 class="contact-page__heading contact-page__heading--small"><?php esc_html_e( 'Zaobserwuj mnie', 'adventure-blog' ); ?></h2>

		<div class="contact-social-icons">
			<?php if ( $instagram ) : ?>
				<a
					class="contact-social-icons__link contact-social-icons__link--instagram"
					href="<?php echo esc_url( $instagram ); ?>"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="Instagram"
				>
					<?php adventure_blog_render_contact_icon( 'instagram' ); ?>
				</a>
			<?php else : ?>
				<span class="contact-social-icons__link contact-social-icons__link--instagram contact-social-icons__link--static" aria-label="Instagram">
					<?php adventure_blog_render_contact_icon( 'instagram' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</section>
