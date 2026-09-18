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
$ghahghah_menu_set      = has_nav_menu( 'primary' );
$ghahghah_last_saved    = absint( get_theme_mod( 'ghahghah_header_last_saved', 0 ) );

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
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'لوگو و فایوآیکن', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی دسکتاپ و آیکن مرورگر. تنظیمات موبایل در بخش «هدر موبایل» است.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-media-grid ghahghah-media-grid--2">
			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_header_logo_desktop',
				__( 'لوگوی دسکتاپ', 'ghahghah' ),
				__( 'ابعاد پیشنهادی: ۱۴۴×۶۲ پیکسل · PNG / WebP شفاف', 'ghahghah' ),
				$ghahghah_logo_desktop,
				ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-desktop.webp' )
			);
			ghahghah_admin_render_media_field(
				'ghahghah_header_favicon',
				__( 'فایوآیکن', 'ghahghah' ),
				__( 'ابعاد پیشنهادی: ۵۱۲×۵۱۲ پیکسل · PNG / ICO / WebP', 'ghahghah' ),
				$ghahghah_favicon,
				ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' )
			);
			?>
		</div>
	</section>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M8 12h8M10 17h4"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'عرض لوگوی دسکتاپ', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عرض نمایش لوگو در هدر دسکتاپ (پیکسل).', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-field ghahghah-field--suffix">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عرض لوگو', 'ghahghah' ); ?></span>
				<span class="ghahghah-field__control">
					<input type="number" min="80" max="200" name="ghahghah_header_logo_width_desktop" value="<?php echo esc_attr( (string) $ghahghah_width_desktop ); ?>" />
					<span class="ghahghah-field__suffix" aria-hidden="true">px</span>
				</span>
				<span class="ghahghah-field__help"><?php esc_html_e( 'محدوده پیشنهادی: ۱۲۰ تا ۱۸۰ پیکسل', 'ghahghah' ); ?></span>
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'منوی اصلی', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عنوان، لینک، ترتیب و زیرمنو از فهرست‌های وردپرس مدیریت می‌شود.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<div class="ghahghah-status-banner <?php echo $ghahghah_menu_set ? 'is-ok' : 'is-warn'; ?>">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<?php if ( $ghahghah_menu_set ) : ?>
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
					<?php else : ?>
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
					<?php endif; ?>
				</span>
				<p class="ghahghah-status-banner__text">
					<?php
					echo $ghahghah_menu_set
						? esc_html__( 'برای موقعیت «منوی اصلی» یک فهرست تنظیم شده است.', 'ghahghah' )
						: esc_html__( 'هنوز فهرستی به موقعیت «منوی اصلی» اختصاص داده نشده است.', 'ghahghah' );
					?>
				</p>
				<a class="ghahghah-btn ghahghah-btn--solid" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">
					<?php esc_html_e( 'مدیریت فهرست‌ها', 'ghahghah' ); ?>
				</a>
			</div>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M5 19h14"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'چسبندگی هدر', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'هدر هنگام اسکرول در بالای صفحه ثابت بماند.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-switch">
				<input type="checkbox" name="ghahghah_header_sticky" value="1" <?php checked( $ghahghah_sticky ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'فعال است', 'ghahghah' ); ?></strong>
					<small><?php esc_html_e( 'هدر هنگام اسکرول در بالای صفحه ثابت می‌ماند.', 'ghahghah' ); ?></small>
				</span>
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="8" width="18" height="10" rx="5"/><path d="M8 13h.01"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'دکمه اقدام (CTA)', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نمایش، متن و برگه مقصد دکمه هدر.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-switch">
				<input type="checkbox" name="ghahghah_header_cta_enabled" value="1" <?php checked( $ghahghah_cta_enabled ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'نمایش دکمه CTA', 'ghahghah' ); ?></strong>
					<small><?php esc_html_e( 'در هدر دسکتاپ (و آیکن موبایل در صورت فعال بودن)', 'ghahghah' ); ?></small>
				</span>
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
	</div>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: relative last-saved label */
				esc_html__( 'آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $ghahghah_last_saved ) )
			);
			?>
		</p>
	</div>
</form>
