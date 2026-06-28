<?php
/**
 * Built-in contact form handler.
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process contact form submission.
 */
function adventure_blog_handle_contact_form() {
	if ( ! isset( $_POST['adventure_contact_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adventure_contact_nonce'] ) ), 'adventure_contact_form' ) ) {
		adventure_blog_set_contact_notice( 'error', __( 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.', 'adventure-blog' ) );
		return;
	}

	$name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
	$email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
	$subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_subject'] ) ) : '';
	$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

	if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
		adventure_blog_set_contact_notice( 'error', __( 'Wypełnij wszystkie pola.', 'adventure-blog' ) );
		return;
	}

	if ( ! is_email( $email ) ) {
		adventure_blog_set_contact_notice( 'error', __( 'Podaj poprawny adres email.', 'adventure-blog' ) );
		return;
	}

	$to           = get_theme_mod( 'adventure_contact_email', get_option( 'admin_email' ) );
	$mail_subject = $subject
		? sprintf( '[%s] %s', adventure_blog_get_name(), $subject )
		: sprintf( '[%s] %s', adventure_blog_get_name(), __( 'Wiadomość z formularza kontaktowego', 'adventure-blog' ) );
	$body         = sprintf(
		"Imię: %s\nEmail: %s\n\nWiadomość:\n%s",
		$name,
		$email,
		$message
	);
	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );

	$sent = wp_mail( $to, $mail_subject, $body, $headers );

	if ( $sent ) {
		adventure_blog_set_contact_notice( 'success', __( 'Dziękujemy! Wiadomość została wysłana.', 'adventure-blog' ) );
	} else {
		adventure_blog_set_contact_notice( 'error', __( 'Nie udało się wysłać wiadomości. Spróbuj ponownie później.', 'adventure-blog' ) );
	}

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/kontakt/' ) );
	exit;
}
add_action( 'admin_post_nopriv_adventure_contact', 'adventure_blog_handle_contact_form' );
add_action( 'admin_post_adventure_contact', 'adventure_blog_handle_contact_form' );

/**
 * Store contact form notice in transient.
 *
 * @param string $type Notice type.
 * @param string $message Notice message.
 */
function adventure_blog_set_contact_notice( $type, $message ) {
	set_transient(
		'adventure_contact_notice_' . adventure_blog_get_visitor_key(),
		array(
			'type'    => $type,
			'message' => $message,
		),
		60
	);
}

/**
 * Get visitor key for transient.
 *
 * @return string
 */
function adventure_blog_get_visitor_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'guest';
	return md5( $ip . wp_salt() );
}

/**
 * Display contact form notice.
 */
function adventure_blog_contact_notice() {
	$key    = 'adventure_contact_notice_' . adventure_blog_get_visitor_key();
	$notice = get_transient( $key );

	if ( ! $notice ) {
		return;
	}

	delete_transient( $key );

	$class = 'notice--' . ( 'success' === $notice['type'] ? 'success' : 'error' );
	printf(
		'<div class="contact-notice %1$s" role="alert">%2$s</div>',
		esc_attr( $class ),
		esc_html( $notice['message'] )
	);
}
