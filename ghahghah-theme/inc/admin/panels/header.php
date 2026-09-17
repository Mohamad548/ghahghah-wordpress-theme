<?php
/**
 * Desktop / shared header configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_defaults      = ghahghah_header_setting_defaults();
$ghahghah_logo_desktop  = absint( get_theme_mod( 'ghahghah_header_logo_desktop', 0 ) );
$ghahghah_favicon       = absint( get_theme_mod( 'ghahghah_header_favicon', 0 ) );
$ghahghah_cta_enabled   = (bool) get_theme_mod( 'ghahghah_header_cta_enabled', $ghahghah_defaults['ghahghah_header_cta_enabled'] );
$ghahghah_cta_label     = (string) get_theme_mod( 'ghahghah_header_cta_label', $ghahghah_defaults['ghahghah_header_cta_label'] );
$ghahghah_cta_page_id   = absint( get_theme_mod( 'ghahghah_header_cta_page_id', 0 ) );
$ghahghah_sticky        = (bool) get_theme_mod( 'ghahghah_header_sticky', $ghahghah_defaults['ghahghah_header_sticky'] );
$ghahghah_width_desktop = ghahghah_get_header_logo_width_desktop();

?>

<form
	class="ghahghah-panel-form"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
>
	<input type="hidden" name="action" value="ghahghah_save_header_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="header" />
	<?php wp_nonce_field( 'ghahghah_save_header_settings', 'ghahghah_header_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'لوگو و آیکن دسکتاپ', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی دسکتاپ و فایوآیکن. تنظیمات موبایل در بخش «حالت موبایل» است.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-media-grid">
			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_header_logo_desktop',
				__( 'لوگوی دسکتاپ', 'ghahghah' ),
				__( 'پیشنهاد: WebP شفاف، عرض حدود ۱۴۴ پیکسل.', 'ghahghah' ),
				$ghahghah_logo_desktop,
				ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-desktop.webp' )
			);
			ghahghah_admin_render_media_field(
				'ghahghah_header_favicon',
				__( 'فایوآیکن / آیکن سایت', 'ghahghah' ),
				__( 'ترجیحاً PNG مربعی ۵۱۲×۵۱۲.', 'ghahghah' ),
				$ghahghah_favicon,
				ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' )
			);
			?>
		</div>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عرض لوگو دسکتاپ (پیکسل)', 'ghahghah' ); ?></span>
			<input type="number" min="80" max="200" name="ghahghah_header_logo_width_desktop" value="<?php echo esc_attr( (string) $ghahghah_width_desktop ); ?>" />
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'منوی اصلی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عنوان، لینک، ترتیب و زیرمنو از فهرست‌های وردپرس مدیریت می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>
		<a class="ghahghah-link-card" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">
			<span><?php esc_html_e( 'باز کردن مدیریت فهرست‌ها', 'ghahghah' ); ?></span>
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
		</a>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="8" width="18" height="10" rx="5"/><path d="M8 13h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'دکمه خرید عمده', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نمایش، متن و برگه مقصد دکمه CTA (دسکتاپ و آیکن موبایل).', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_header_cta_enabled" value="1" <?php checked( $ghahghah_cta_enabled ); ?> />
			<span><?php esc_html_e( 'نمایش دکمه در هدر', 'ghahghah' ); ?></span>
		</label>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه', 'ghahghah' ); ?></span>
				<input type="text" maxlength="40" name="ghahghah_header_cta_label" value="<?php echo esc_attr( $ghahghah_cta_label ); ?>" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه مقصد', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_header_cta_page_id',
						'selected'          => absint( $ghahghah_cta_page_id ),
						'show_option_none'  => esc_html__( '— انتخاب برگه —', 'ghahghah' ),
						'option_none_value' => '0',
						'echo'              => 1,
					)
				);
				?>
			</label>
		</div>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M5 19h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'رفتار هدر', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'چسبان بودن هدر هنگام اسکرول.', 'ghahghah' ); ?></p>
			</div>
		</header>
		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_header_sticky" value="1" <?php checked( $ghahghah_sticky ); ?> />
			<span><?php esc_html_e( 'هدر چسبان (Sticky)', 'ghahghah' ); ?></span>
		</label>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره تنظیمات هدر', 'ghahghah' ); ?>
		</button>
	</div>
</form>
