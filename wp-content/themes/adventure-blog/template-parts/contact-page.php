<?php
/**
 * Contact page layout.
 *
 * @package Adventure_Blog
 */

$details       = adventure_blog_get_contact_details();
$instagram     = get_theme_mod( 'adventure_instagram_url', 'https://www.instagram.com/zone6' );
$insta_label   = get_theme_mod( 'adventure_instagram_handle', 'ZONE6' );
$insta_display = $insta_label ? '@' . ltrim( $insta_label, '@' ) : __( 'Instagram', 'adventure-blog' );
?>
<div class="contact-page__body">
	<div class="container contact-page__container">
		<?php adventure_blog_contact_notice(); ?>

		<div class="contact-cards reveal reveal--up">
			<?php if ( $details['email'] ) : ?>
				<div class="contact-card">
					<span class="contact-card__icon" aria-hidden="true">
						<?php adventure_blog_render_contact_icon( 'email' ); ?>
					</span>
					<p class="contact-card__label"><?php esc_html_e( 'Email', 'adventure-blog' ); ?></p>
					<p class="contact-card__value">
						<a href="<?php echo esc_url( 'mailto:' . $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $details['phone'] ) : ?>
				<div class="contact-card">
					<span class="contact-card__icon" aria-hidden="true">
						<?php adventure_blog_render_contact_icon( 'phone' ); ?>
					</span>
					<p class="contact-card__label"><?php esc_html_e( 'Telefon', 'adventure-blog' ); ?></p>
					<p class="contact-card__value">
						<a href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $details['phone'] ) ); ?>"><?php echo esc_html( $details['phone'] ); ?></a>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $instagram ) : ?>
				<div class="contact-card">
					<span class="contact-card__icon" aria-hidden="true">
						<?php adventure_blog_render_contact_icon( 'instagram' ); ?>
					</span>
					<p class="contact-card__label"><?php esc_html_e( 'Instagram', 'adventure-blog' ); ?></p>
					<p class="contact-card__value">
						<a href="<?php echo esc_url( $instagram ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $insta_display ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<div class="contact-form-layout reveal reveal--up">
			<div class="contact-form-panel">
				<h2 class="contact-form-panel__title"><?php esc_html_e( 'Formularz', 'adventure-blog' ); ?></h2>

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
							<textarea name="contact_message" rows="7" placeholder="<?php esc_attr_e( 'Wiadomość', 'adventure-blog' ); ?>" required></textarea>
						</label>
					</div>

					<div class="contact-form__actions">
						<button type="submit" class="btn btn--primary contact-form__submit">
							<?php esc_html_e( 'Wyślij wiadomość', 'adventure-blog' ); ?>
						</button>
					</div>
				</form>
			</div>

			<aside class="contact-map-panel" aria-label="<?php esc_attr_e( 'Mapa', 'adventure-blog' ); ?>">
				<h2 class="contact-map-panel__title"><?php esc_html_e( 'Mapa', 'adventure-blog' ); ?></h2>
				<div
					id="contact-map"
					class="contact-map contact-map--panel"
					data-lat="<?php echo esc_attr( $details['map_lat'] ); ?>"
					data-lng="<?php echo esc_attr( $details['map_lng'] ); ?>"
					data-label="<?php echo esc_attr( $details['map_label'] ); ?>"
				></div>
			</aside>
		</div>
	</div>
</div>
