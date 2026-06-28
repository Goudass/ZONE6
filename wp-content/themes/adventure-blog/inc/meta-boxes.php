<?php
/**
 * Meta boxes for route posts (admin form).
 *
 * @package Adventure_Blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register meta box.
 */
function adventure_blog_register_meta_boxes() {
	add_meta_box(
		'adventure_route_details',
		__( 'Szczegóły trasy', 'adventure-blog' ),
		'adventure_blog_render_route_meta_box',
		'trasa',
		'normal',
		'high'
	);

	add_meta_box(
		'adventure_post_gallery',
		__( 'Galeria zdjęć', 'adventure-blog' ),
		'adventure_blog_render_post_gallery_meta_box',
		'post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'adventure_blog_register_meta_boxes' );

/**
 * Render admin form for route details.
 *
 * @param WP_Post $post Current post.
 */
function adventure_blog_render_route_meta_box( $post ) {
	wp_nonce_field( 'adventure_route_meta', 'adventure_route_meta_nonce' );

	$fields = adventure_blog_get_route_meta( $post->ID );
	$gpx_id = (int) get_post_meta( $post->ID, '_adventure_gpx_id', true );
	$gallery_ids = get_post_meta( $post->ID, '_adventure_gallery_ids', true );
	if ( ! is_array( $gallery_ids ) ) {
		$gallery_ids = array();
	}
	?>
	<div class="adventure-meta-box">
		<style>
			.adventure-meta-box { display: grid; gap: 16px; }
			.adventure-meta-box__row { display: grid; grid-template-columns: 180px 1fr; gap: 12px; align-items: center; }
			.adventure-meta-box__row label { font-weight: 600; }
			.adventure-meta-box__row input[type="text"],
			.adventure-meta-box__row select,
			.adventure-meta-box__row textarea { width: 100%; max-width: 420px; }
			.adventure-meta-box__hint { color: #666; font-size: 12px; margin-top: 4px; }
			.adventure-gallery-preview { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
			.adventure-gallery-preview img { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
		</style>

		<?php $featured = (bool) get_post_meta( $post->ID, '_adventure_featured', true ); ?>
		<div class="adventure-meta-box__row">
			<label for="adventure_featured"><?php esc_html_e( 'Wyróżniona trasa', 'adventure-blog' ); ?></label>
			<label>
				<input type="checkbox" name="adventure_featured" id="adventure_featured" value="1" <?php checked( $featured ); ?>>
				<?php esc_html_e( 'Pokaż na stronie głównej w sekcji wyróżnionych', 'adventure-blog' ); ?>
			</label>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_trudnosc"><?php esc_html_e( 'Trudność', 'adventure-blog' ); ?></label>
			<select name="adventure_trudnosc" id="adventure_trudnosc">
				<option value="latwa" <?php selected( $fields['trudnosc'], 'latwa' ); ?>><?php esc_html_e( 'Łatwa', 'adventure-blog' ); ?></option>
				<option value="srednia" <?php selected( $fields['trudnosc'], 'srednia' ); ?>><?php esc_html_e( 'Średnia', 'adventure-blog' ); ?></option>
				<option value="trudna" <?php selected( $fields['trudnosc'], 'trudna' ); ?>><?php esc_html_e( 'Trudna', 'adventure-blog' ); ?></option>
			</select>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_czas"><?php esc_html_e( 'Czas', 'adventure-blog' ); ?></label>
			<div>
				<input type="text" name="adventure_czas" id="adventure_czas" value="<?php echo esc_attr( $fields['czas'] ); ?>" placeholder="np. 4h 30min">
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_dystans"><?php esc_html_e( 'Dystans', 'adventure-blog' ); ?></label>
			<div>
				<input type="text" name="adventure_dystans" id="adventure_dystans" value="<?php echo esc_attr( $fields['dystans'] ); ?>" placeholder="np. 42 km">
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_przewyzszenie"><?php esc_html_e( 'Przewyższenie', 'adventure-blog' ); ?></label>
			<div>
				<input type="text" name="adventure_przewyzszenie" id="adventure_przewyzszenie" value="<?php echo esc_attr( $fields['przewyzszenie'] ); ?>" placeholder="np. 850 m">
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_route_start"><?php esc_html_e( 'Punkt startu', 'adventure-blog' ); ?></label>
			<div>
				<input type="text" name="adventure_route_start" id="adventure_route_start" value="<?php echo esc_attr( $fields['route_start'] ); ?>" placeholder="np. Schronisko Murowaniec">
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_route_end"><?php esc_html_e( 'Punkt końcowy', 'adventure-blog' ); ?></label>
			<div>
				<input type="text" name="adventure_route_end" id="adventure_route_end" value="<?php echo esc_attr( $fields['route_end'] ); ?>" placeholder="np. Dolina Pięciu Stawów">
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label for="adventure_overview_intro"><?php esc_html_e( 'Wstęp — przegląd trasy', 'adventure-blog' ); ?></label>
			<div>
				<textarea name="adventure_overview_intro" id="adventure_overview_intro" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Krótki opis startu i charakteru trasy (2–3 zdania).', 'adventure-blog' ); ?>"><?php echo esc_textarea( $fields['overview_intro'] ); ?></textarea>
				<p class="adventure-meta-box__hint"><?php esc_html_e( 'Wyświetlane pod wizualizacją trasy. Jeśli puste — użyty zostanie skrót wpisu.', 'adventure-blog' ); ?></p>
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label><?php esc_html_e( 'Plik GPX', 'adventure-blog' ); ?></label>
			<div>
				<input type="hidden" name="adventure_gpx_id" id="adventure_gpx_id" value="<?php echo esc_attr( $gpx_id ); ?>">
				<button type="button" class="button" id="adventure_gpx_upload"><?php esc_html_e( 'Wybierz plik GPX', 'adventure-blog' ); ?></button>
				<button type="button" class="button" id="adventure_gpx_remove"><?php esc_html_e( 'Usuń', 'adventure-blog' ); ?></button>
				<p class="adventure-meta-box__hint" id="adventure_gpx_filename">
					<?php
					if ( $gpx_id ) {
						echo esc_html( basename( get_attached_file( $gpx_id ) ) );
					} else {
						esc_html_e( 'Brak pliku GPX', 'adventure-blog' );
					}
					?>
				</p>
			</div>
		</div>

		<div class="adventure-meta-box__row">
			<label><?php esc_html_e( 'Galeria zdjęć', 'adventure-blog' ); ?></label>
			<div>
				<input type="hidden" name="adventure_gallery_ids" id="adventure_gallery_ids" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>">
				<button type="button" class="button" id="adventure_gallery_upload"><?php esc_html_e( 'Dodaj zdjęcia', 'adventure-blog' ); ?></button>
				<button type="button" class="button" id="adventure_gallery_clear"><?php esc_html_e( 'Wyczyść', 'adventure-blog' ); ?></button>
				<div class="adventure-gallery-preview" id="adventure_gallery_preview">
					<?php foreach ( $gallery_ids as $attachment_id ) : ?>
						<?php echo wp_get_attachment_image( (int) $attachment_id, 'thumbnail' ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render gallery meta box for blog posts.
 *
 * @param WP_Post $post Current post.
 */
function adventure_blog_render_post_gallery_meta_box( $post ) {
	wp_nonce_field( 'adventure_post_gallery_meta', 'adventure_post_gallery_nonce' );

	$gallery_ids = adventure_blog_get_gallery_ids( $post->ID );
	?>
	<div class="adventure-meta-box">
		<style>
			.adventure-gallery-preview { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
			.adventure-gallery-preview img { width: 80px; height: 60px; object-fit: cover; border-radius: 4px; }
		</style>
		<p class="adventure-meta-box__hint"><?php esc_html_e( 'Zdjęcia wyświetlą się na końcu wpisu w sliderze.', 'adventure-blog' ); ?></p>
		<input type="hidden" name="adventure_gallery_ids" id="adventure_gallery_ids" value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>">
		<button type="button" class="button" id="adventure_gallery_upload"><?php esc_html_e( 'Dodaj zdjęcia', 'adventure-blog' ); ?></button>
		<button type="button" class="button" id="adventure_gallery_clear"><?php esc_html_e( 'Wyczyść', 'adventure-blog' ); ?></button>
		<div class="adventure-gallery-preview" id="adventure_gallery_preview">
			<?php foreach ( $gallery_ids as $attachment_id ) : ?>
				<?php echo wp_get_attachment_image( (int) $attachment_id, 'thumbnail' ); ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Enqueue admin scripts for media uploader.
 *
 * @param string $hook Current admin page hook.
 */
function adventure_blog_admin_scripts( $hook ) {
	global $post_type;

	if ( ( 'post.php' !== $hook && 'post-new.php' !== $hook ) || ! in_array( $post_type, array( 'trasa', 'post' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'adventure-blog-admin',
		ADVENTURE_BLOG_URI . '/assets/js/admin-route.js',
		array( 'jquery' ),
		ADVENTURE_BLOG_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'adventure_blog_admin_scripts' );

/**
 * Save route meta.
 *
 * @param int $post_id Post ID.
 */
function adventure_blog_save_route_meta( $post_id ) {
	if ( ! isset( $_POST['adventure_route_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adventure_route_meta_nonce'] ) ), 'adventure_route_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed_difficulty = array( 'latwa', 'srednia', 'trudna' );
	$trudnosc           = isset( $_POST['adventure_trudnosc'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_trudnosc'] ) ) : 'latwa';
	if ( ! in_array( $trudnosc, $allowed_difficulty, true ) ) {
		$trudnosc = 'latwa';
	}

	update_post_meta( $post_id, '_adventure_featured', isset( $_POST['adventure_featured'] ) ? '1' : '' );
	update_post_meta( $post_id, '_adventure_trudnosc', $trudnosc );
	update_post_meta( $post_id, '_adventure_czas', isset( $_POST['adventure_czas'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_czas'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_dystans', isset( $_POST['adventure_dystans'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_dystans'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_przewyzszenie', isset( $_POST['adventure_przewyzszenie'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_przewyzszenie'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_route_start', isset( $_POST['adventure_route_start'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_route_start'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_route_end', isset( $_POST['adventure_route_end'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_route_end'] ) ) : '' );
	update_post_meta( $post_id, '_adventure_overview_intro', isset( $_POST['adventure_overview_intro'] ) ? sanitize_textarea_field( wp_unslash( $_POST['adventure_overview_intro'] ) ) : '' );

	$gpx_id = isset( $_POST['adventure_gpx_id'] ) ? absint( $_POST['adventure_gpx_id'] ) : 0;
	update_post_meta( $post_id, '_adventure_gpx_id', $gpx_id );

	$gallery_raw = isset( $_POST['adventure_gallery_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_gallery_ids'] ) ) : '';
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );
	update_post_meta( $post_id, '_adventure_gallery_ids', $gallery_ids );
}
add_action( 'save_post_trasa', 'adventure_blog_save_route_meta' );

/**
 * Save gallery meta for blog posts.
 *
 * @param int $post_id Post ID.
 */
function adventure_blog_save_post_gallery_meta( $post_id ) {
	if ( ! isset( $_POST['adventure_post_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['adventure_post_gallery_nonce'] ) ), 'adventure_post_gallery_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$gallery_raw = isset( $_POST['adventure_gallery_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['adventure_gallery_ids'] ) ) : '';
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );
	update_post_meta( $post_id, '_adventure_gallery_ids', $gallery_ids );
}
add_action( 'save_post_post', 'adventure_blog_save_post_gallery_meta' );

/**
 * Allow GPX uploads.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function adventure_blog_allow_gpx_upload( $mimes ) {
	$mimes['gpx'] = 'application/gpx+xml';
	$mimes['xml'] = 'text/xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'adventure_blog_allow_gpx_upload' );

/**
 * Fix GPX file type detection.
 *
 * @param array  $data     File data.
 * @param string $file     File path.
 * @param string $filename File name.
 * @return array
 */
function adventure_blog_fix_gpx_filetype( $data, $file, $filename ) {
	if ( preg_match( '/\.gpx$/i', $filename ) ) {
		$data['ext']  = 'gpx';
		$data['type'] = 'application/gpx+xml';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'adventure_blog_fix_gpx_filetype', 10, 3 );
