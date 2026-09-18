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
$last_saved  = absint( get_theme_mod( 'ghahghah_mobile_footer_last_saved', 0 ) );

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_mobile_footer_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="mobile-footer" />
	<?php wp_nonce_field( 'ghahghah_save_mobile_footer_settings', 'ghahghah_mobile_footer_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="3" width="12" height="18" rx="2"/><path d="M6 16h12"/></svg>
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

		<div class="ghahghah-status-banner is-warn">
			<span class="ghahghah-status-banner__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
			</span>
			<p class="ghahghah-status-banner__text">
				<?php esc_html_e( 'سایر تنظیمات فوتر (نوار همکاری، تماس، متن حقوقی و دکمه‌ها) در بخش «فوتر» دسکتاپ هستند.', 'ghahghah' ); ?>
			</p>
			<a class="ghahghah-btn ghahghah-btn--solid" href="<?php echo esc_url( ghahghah_get_config_tab_url( 'footer' ) ); ?>">
				<?php esc_html_e( 'تنظیمات فوتر', 'ghahghah' ); ?>
			</a>
		</div>
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
