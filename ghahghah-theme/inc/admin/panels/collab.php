<?php
/**
 * Collab (wholesale / agency) banner configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d          = ghahghah_collab_setting_defaults();
$enabled    = (bool) get_theme_mod( 'ghahghah_collab_enabled', $d['ghahghah_collab_enabled'] );
$w_image    = absint( get_theme_mod( 'ghahghah_collab_wholesale_image', 0 ) );
$w_alt      = (string) get_theme_mod( 'ghahghah_collab_wholesale_image_alt', $d['ghahghah_collab_wholesale_image_alt'] );
$a_image    = absint( get_theme_mod( 'ghahghah_collab_agency_image', 0 ) );
$a_alt      = (string) get_theme_mod( 'ghahghah_collab_agency_image_alt', $d['ghahghah_collab_agency_image_alt'] );
$last_saved = absint( get_theme_mod( 'ghahghah_collab_last_saved', 0 ) );
$w_default  = ghahghah_get_collab_bundled_image_url( 'wholesale' );
$a_default  = ghahghah_get_collab_bundled_image_url( 'agency' );
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_collab_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="collab" />
	<?php wp_nonce_field( 'ghahghah_save_collab_settings', 'ghahghah_collab_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="7" height="14" rx="1.5"/><rect x="14" y="5" width="7" height="14" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش بخش', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'دو بنر خرید عمده و نمایندگی در صفحه اصلی — بدون متن جداگانه.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_collab_enabled" value="1" <?php checked( $enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش بنرهای عمده و نمایندگی در صفحه اصلی', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'لینک هر بنر از برگه‌های خرید عمده / نمایندگی در تنظیمات مربوطه گرفته می‌شود.', 'ghahghah' ); ?></small>
			</span>
		</label>
	</section>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر خرید عمده', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'تصویر را از کتابخانه رسانه انتخاب کنید.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_collab_wholesale_image',
				__( 'تصویر بنر عمده', 'ghahghah' ),
				__( 'پیشنهاد: WebP افقی · عرض حدود ۱۹۰۰ پیکسل', 'ghahghah' ),
				$w_image,
				$w_default
			);
			?>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین (alt)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_collab_wholesale_image_alt" value="<?php echo esc_attr( $w_alt ); ?>" maxlength="160" />
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر نمایندگی', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'تصویر را از کتابخانه رسانه انتخاب کنید.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_collab_agency_image',
				__( 'تصویر بنر نمایندگی', 'ghahghah' ),
				__( 'پیشنهاد: WebP افقی · عرض حدود ۱۹۰۰ پیکسل', 'ghahghah' ),
				$a_image,
				$a_default
			);
			?>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین (alt)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_collab_agency_image_alt" value="<?php echo esc_attr( $a_alt ); ?>" maxlength="160" />
			</label>
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
				/* translators: %s: last saved label */
				esc_html__( 'آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
