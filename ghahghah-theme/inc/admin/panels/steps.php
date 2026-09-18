<?php
/**
 * Production steps configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d             = ghahghah_steps_setting_defaults();
$enabled       = (bool) get_theme_mod( 'ghahghah_steps_enabled', $d['ghahghah_steps_enabled'] );
$image_id      = absint( get_theme_mod( 'ghahghah_steps_image', 0 ) );
$image_alt     = (string) get_theme_mod( 'ghahghah_steps_image_alt', $d['ghahghah_steps_image_alt'] );
$last_saved    = absint( get_theme_mod( 'ghahghah_steps_last_saved', 0 ) );
$default_image = function_exists( 'ghahghah_get_steps_bundled_image_url' ) ? ghahghah_get_steps_bundled_image_url() : '';
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_steps_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="steps" />
	<?php wp_nonce_field( 'ghahghah_save_steps_settings', 'ghahghah_steps_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش بخش', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'بنر اینفوگرافیک مراحل تولید در صفحه اصلی.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_steps_enabled" value="1" <?php checked( $enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش بنر مراحل تولید در صفحه اصلی', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'فقط تصویر نمایش داده می‌شود؛ عنوان جداگانه ندارد.', 'ghahghah' ); ?></small>
			</span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تصویر مراحل تولید', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'اینفوگرافیک را از کتابخانه رسانه انتخاب یا بارگذاری کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php
		ghahghah_admin_render_media_field(
			'ghahghah_steps_image',
			__( 'تصویر اینفوگرافیک', 'ghahghah' ),
			__( 'پیشنهاد: WebP افقی · عرض حدود ۱۹۰۰ پیکسل', 'ghahghah' ),
			$image_id,
			$default_image
		);
		?>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین تصویر (alt)', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_steps_image_alt" value="<?php echo esc_attr( $image_alt ); ?>" maxlength="160" />
			<span class="ghahghah-field__help"><?php esc_html_e( 'برای دسترس‌پذیری و سئو؛ اگر خالی باشد از پیش‌فرض استفاده می‌شود.', 'ghahghah' ); ?></span>
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
