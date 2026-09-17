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
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 18h4"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'لوگوی موبایل', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی نمایش‌داده‌شده در هدر و کشوی موبایل.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-media-grid" style="grid-template-columns: minmax(0, 280px);">
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

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عرض لوگو موبایل (پیکسل)', 'ghahghah' ); ?></span>
			<input type="number" min="72" max="140" name="ghahghah_header_logo_width_mobile" value="<?php echo esc_attr( (string) $ghahghah_width_mobile ); ?>" />
		</label>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره تنظیمات هدر موبایل', 'ghahghah' ); ?>
		</button>
	</div>
</form>
