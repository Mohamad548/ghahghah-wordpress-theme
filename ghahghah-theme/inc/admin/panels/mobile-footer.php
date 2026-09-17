<?php
/**
 * Mobile footer settings panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logo_mobile = absint( get_theme_mod( 'ghahghah_footer_logo_mobile', 0 ) );

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_mobile_footer_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="mobile-footer" />
	<?php wp_nonce_field( 'ghahghah_save_mobile_footer_settings', 'ghahghah_mobile_footer_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 18h4"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'فوتر موبایل', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی مخصوص نمایش فوتر در موبایل. چیدمان واکنش‌گرا به‌صورت خودکار اعمال می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php
		ghahghah_admin_render_media_field(
			'ghahghah_footer_logo_mobile',
			__( 'لوگوی فوتر موبایل', 'ghahghah' ),
			__( 'اگر خالی باشد از لوگوی فوتر دسکتاپ یا لوگوی موبایل هدر استفاده می‌شود.', 'ghahghah' ),
			$logo_mobile,
			ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-mobile.webp' )
		);
		?>

		<div class="ghahghah-config-card" style="margin-top:1rem">
			<p><?php esc_html_e( 'سایر تنظیمات فوتر (نوار همکاری، تماس، متن حقوقی و دکمه‌ها) در بخش «فوتر» دسکتاپ هستند.', 'ghahghah' ); ?></p>
			<p>
				<a href="<?php echo esc_url( ghahghah_get_config_tab_url( 'footer' ) ); ?>">
					<?php esc_html_e( 'رفتن به تنظیمات فوتر دسکتاپ', 'ghahghah' ); ?>
				</a>
			</p>
		</div>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره فوتر موبایل', 'ghahghah' ); ?>
		</button>
	</div>
</form>
