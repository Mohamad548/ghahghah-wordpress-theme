<?php
/**
 * Factory intro configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d = ghahghah_factory_setting_defaults();

$enabled        = (bool) get_theme_mod( 'ghahghah_factory_enabled', $d['ghahghah_factory_enabled'] );
$eyebrow        = (string) get_theme_mod( 'ghahghah_factory_eyebrow', $d['ghahghah_factory_eyebrow'] );
$title          = (string) get_theme_mod( 'ghahghah_factory_title', $d['ghahghah_factory_title'] );
$text           = (string) get_theme_mod( 'ghahghah_factory_text', $d['ghahghah_factory_text'] );
$image_id       = absint( get_theme_mod( 'ghahghah_factory_image', 0 ) );
$image_alt      = (string) get_theme_mod( 'ghahghah_factory_image_alt', $d['ghahghah_factory_image_alt'] );
$topics_heading = (string) get_theme_mod( 'ghahghah_factory_topics_heading', $d['ghahghah_factory_topics_heading'] );
$topic_1        = (string) get_theme_mod( 'ghahghah_factory_topic_1', $d['ghahghah_factory_topic_1'] );
$topic_2        = (string) get_theme_mod( 'ghahghah_factory_topic_2', $d['ghahghah_factory_topic_2'] );
$topic_3        = (string) get_theme_mod( 'ghahghah_factory_topic_3', $d['ghahghah_factory_topic_3'] );
$button_label   = (string) get_theme_mod( 'ghahghah_factory_button_label', $d['ghahghah_factory_button_label'] );
$button_page    = absint( get_theme_mod( 'ghahghah_factory_button_page', 0 ) );
$last_saved     = absint( get_theme_mod( 'ghahghah_factory_last_saved', 0 ) );

$default_image = '';
if ( function_exists( 'ghahghah_get_bundled_brand_asset_url' ) ) {
	$candidate = GHAHGHAH_THEME_DIR . '/assets/images/factory/factory-hero.webp';
	if ( is_readable( $candidate ) ) {
		$default_image = GHAHGHAH_THEME_URI . '/assets/images/factory/factory-hero.webp';
	}
}

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_factory_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="factory" />
	<?php wp_nonce_field( 'ghahghah_save_factory_settings', 'ghahghah_factory_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V9l5-3v15"/><path d="M14 21V6l5 3v12"/><path d="M9 10h.01M9 14h.01M17 12h.01M17 16h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش و متن', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'محتوای بخش معرفی کارخانه که در صفحه اصلی نمایش داده می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_factory_enabled" value="1" <?php checked( $enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش بخش معرفی کارخانه', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'در صورت خاموش بودن، این بلوک در صفحه اصلی دیده نمی‌شود.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کوتاه', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" maxlength="60" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان اصلی', 'ghahghah' ); ?></span>
				<textarea name="ghahghah_factory_title" rows="2" maxlength="160"><?php echo esc_textarea( $title ); ?></textarea>
				<span class="ghahghah-field__help"><?php esc_html_e( 'کلمه «قهقهه» در عنوان به‌صورت خودکار قرمز می‌شود.', 'ghahghah' ); ?></span>
			</label>
		</div>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_factory_text" rows="3" maxlength="400"><?php echo esc_textarea( $text ); ?></textarea>
		</label>
	</section>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تصویر کارخانه', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'تصویر را از کتابخانه رسانه بارگذاری یا انتخاب کنید.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_factory_image',
				__( 'تصویر کارخانه', 'ghahghah' ),
				__( 'پیشنهاد: نسبت تقریبی ۴:۳ · حداقل عرض ۹۶۰ پیکسل', 'ghahghah' ),
				$image_id,
				$default_image
			);
			?>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین تصویر (alt)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_image_alt" value="<?php echo esc_attr( $image_alt ); ?>" maxlength="120" />
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'دکمه', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'اگر صفحه انتخاب نشود، دکمه به صفحه اصلی لینک می‌شود.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_button_label" value="<?php echo esc_attr( $button_label ); ?>" maxlength="50" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'صفحه مقصد', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_factory_button_page',
						'selected'          => $button_page,
						'show_option_none'  => __( '— انتخاب نشده —', 'ghahghah' ),
						'option_none_value' => '0',
					)
				);
				?>
			</label>
		</section>
	</div>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="6" cy="12" r="2.5"/><circle cx="12" cy="12" r="2.5"/><circle cx="18" cy="12" r="2.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'سه موضوع معرفی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عنوان ردیف و برچسب هر موضوع در کارت معرفی.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان ردیف موضوعات', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_factory_topics_heading" value="<?php echo esc_attr( $topics_heading ); ?>" maxlength="80" />
		</label>

		<div class="ghahghah-field-grid ghahghah-field-grid--3">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'موضوع ۱', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_topic_1" value="<?php echo esc_attr( $topic_1 ); ?>" maxlength="40" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'موضوع ۲', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_topic_2" value="<?php echo esc_attr( $topic_2 ); ?>" maxlength="40" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'موضوع ۳', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_factory_topic_3" value="<?php echo esc_attr( $topic_3 ); ?>" maxlength="40" />
			</label>
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
