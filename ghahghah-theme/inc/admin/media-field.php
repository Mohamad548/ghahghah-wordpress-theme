<?php
/**
 * Shared admin media upload field renderer.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render one media upload field.
 *
 * @param string $name          Field name.
 * @param string $label         Field label.
 * @param string $help          Help text.
 * @param int    $attachment_id Current attachment ID.
 * @param string $default_url   Bundled fallback preview URL.
 */
function ghahghah_admin_render_media_field(
	string $name,
	string $label,
	string $help,
	int $attachment_id,
	string $default_url
): void {
	$preview = $default_url;
	if ( $attachment_id > 0 ) {
		$custom = wp_get_attachment_image_url( $attachment_id, 'medium' );
		if ( is_string( $custom ) && '' !== $custom ) {
			$preview = $custom;
		}
	}
	?>
	<div class="ghahghah-media-field" data-ghahghah-media-field>
		<label class="ghahghah-field__label"><?php echo esc_html( $label ); ?></label>
		<p class="ghahghah-field__help"><?php echo esc_html( $help ); ?></p>
		<div class="ghahghah-media-field__preview<?php echo '' === $preview ? ' is-empty' : ''; ?>">
			<img
				src="<?php echo '' !== $preview ? esc_url( $preview ) : ''; ?>"
				alt=""
				data-ghahghah-media-preview
				data-default-src="<?php echo esc_url( $default_url ); ?>"
				<?php echo '' === $preview ? 'hidden' : ''; ?>
			/>
			<?php if ( '' === $preview ) : ?>
				<span class="ghahghah-media-field__empty"><?php esc_html_e( 'هنوز تصویری انتخاب نشده است', 'ghahghah' ); ?></span>
			<?php endif; ?>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $attachment_id ); ?>" data-ghahghah-media-input />
		<div class="ghahghah-media-field__actions">
			<button type="button" class="button button-secondary" data-ghahghah-media-upload>
				<?php esc_html_e( 'انتخاب / بارگذاری', 'ghahghah' ); ?>
			</button>
			<button type="button" class="button-link-delete" data-ghahghah-media-reset <?php echo $attachment_id > 0 ? '' : 'hidden'; ?>>
				<?php echo '' !== $default_url ? esc_html__( 'بازگشت به پیش‌فرض', 'ghahghah' ) : esc_html__( 'حذف تصویر', 'ghahghah' ); ?>
			</button>
		</div>
	</div>
	<?php
}
