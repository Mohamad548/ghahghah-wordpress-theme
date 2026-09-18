<?php
/**
 * Mobile header settings panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_logo_mobile  = absint( get_theme_mod( 'ghahghah_header_logo_mobile', 0 ) );
$ghahghah_width_mobile = ghahghah_get_header_logo_width_mobile();
$last_saved            = absint( get_theme_mod( 'ghahghah_mobile_header_last_saved', 0 ) );

?>

<form
	class="ghahghah-panel-form"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
>
	<input type="hidden" name="action" value="ghahghah_save_mobile_header_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="mobile-header" />
	<?php wp_nonce_field( 'ghahghah_save_mobile_header_settings', 'ghahghah_mobile_header_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="3" width="12" height="18" rx="2"/><path d="M6 8h12"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'لوگوی موبایل', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی نمایش‌داده‌شده در هدر و کشوی موبایل.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-media-grid ghahghah-media-grid--2">
			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_header_logo_mobile',
				__( 'لوگوی موبایل', 'ghahghah' ),
				__( 'نسخه جمع‌وجور برای هدر موبایل.', 'ghahghah' ),
				$ghahghah_logo_mobile,
				ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-mobile.webp' )
			);
			?>
		</div>

		<label class="ghahghah-field ghahghah-field--suffix">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عرض لوگوی موبایل', 'ghahghah' ); ?></span>
			<span class="ghahghah-field__control">
				<input type="number" min="72" max="140" name="ghahghah_header_logo_width_mobile" value="<?php echo esc_attr( (string) $ghahghah_width_mobile ); ?>" />
				<span class="ghahghah-field__suffix" aria-hidden="true">px</span>
			</span>
			<span class="ghahghah-field__help"><?php esc_html_e( 'محدوده پیشنهادی: ۹۶ تا ۱۲۰ پیکسل', 'ghahghah' ); ?></span>
		</label>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: last saved label */
				esc_html__( 'آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
