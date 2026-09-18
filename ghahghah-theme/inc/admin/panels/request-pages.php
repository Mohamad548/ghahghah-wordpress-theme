<?php
/**
 * Admin panel: request page destinations + copy.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d      = ghahghah_request_pages_defaults();
$wh_id  = absint( get_theme_mod( 'ghahghah_wholesale_page_id', 0 ) );
$ag_id  = absint( get_theme_mod( 'ghahghah_agency_page_id', 0 ) );
$img_id = absint( get_theme_mod( 'ghahghah_wholesale_image_id', 0 ) );
$last_saved = absint( get_theme_mod( 'ghahghah_request_pages_last_saved', 0 ) );

$wh_url = $wh_id > 0 ? get_permalink( $wh_id ) : '';
$ag_url = $ag_id > 0 ? get_permalink( $ag_id ) : '';
$wh_title_preview = (string) get_theme_mod( 'ghahghah_wholesale_intro_title', $d['ghahghah_wholesale_intro_title'] );
$ag_title_preview = (string) get_theme_mod( 'ghahghah_agency_intro_title', $d['ghahghah_agency_intro_title'] );

/**
 * Render a labeled text/textarea field from theme mod.
 *
 * @param string               $key      Theme mod key.
 * @param string               $label    Field label.
 * @param array<string, mixed> $defaults Defaults map.
 * @param bool                 $textarea Force textarea.
 */
$render_field = static function ( string $key, string $label, array $defaults, bool $textarea = false ): void {
	$val  = (string) get_theme_mod( $key, $defaults[ $key ] ?? '' );
	$is_ta = $textarea || str_contains( $key, '_text' ) || str_contains( $key, '_note' );
	?>
	<label class="ghahghah-field">
		<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
		<?php if ( $is_ta ) : ?>
			<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
		<?php else : ?>
			<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
		<?php endif; ?>
	</label>
	<?php
};
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_request_pages" />
	<input type="hidden" name="ghahghah_return_tab" value="request-pages" />
	<?php wp_nonce_field( 'ghahghah_save_request_pages', 'ghahghah_request_pages_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه‌های درخواست', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه‌های وردپرس با قالب «خرید عمده» و «درخواست نمایندگی» را انتخاب کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه خرید عمده', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_wholesale_page_id',
						'selected'          => $wh_id,
						'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
						'option_none_value' => '0',
					)
				);
				?>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه درخواست نمایندگی', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_agency_page_id',
						'selected'          => $ag_id,
						'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
						'option_none_value' => '0',
					)
				);
				?>
			</label>
		</div>
	</section>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تصویر معرفی خرید عمده', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'اختیاری؛ در صورت خالی‌بودن از تصویر بهینه‌شده قالب استفاده می‌شود.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_wholesale_image_id',
				__( 'تصویر معرفی', 'ghahghah' ),
				__( 'پیشنهاد: تصویر محصول / اسنک با نسبت نزدیک عمودی یا مربعی.', 'ghahghah' ),
				$img_id,
				GHAHGHAH_THEME_URI . '/assets/images/request/wholesale-snacks.jpg'
			);
			?>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h7v7H4zM13 5h7v7h-7zM4 14h7v5H4zM13 14h7v5h-7z"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پیش‌نمایش صفحات', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'پس از انتخاب برگه، لینک مشاهدهٔ سریع اینجا نمایش داده می‌شود.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<div class="ghahghah-request-preview">
				<div class="ghahghah-request-preview__card">
					<strong><?php echo esc_html( $wh_title_preview ); ?></strong>
					<small><?php esc_html_e( 'صفحه خرید عمده', 'ghahghah' ); ?></small>
					<?php if ( is_string( $wh_url ) && '' !== $wh_url ) : ?>
						<a class="ghahghah-btn ghahghah-btn--solid ghahghah-btn--sm" href="<?php echo esc_url( $wh_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'مشاهده صفحه', 'ghahghah' ); ?>
						</a>
					<?php else : ?>
						<span class="ghahghah-field__help"><?php esc_html_e( 'برگه هنوز انتخاب نشده است.', 'ghahghah' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="ghahghah-request-preview__card">
					<strong><?php echo esc_html( $ag_title_preview ); ?></strong>
					<small><?php esc_html_e( 'صفحه نمایندگی', 'ghahghah' ); ?></small>
					<?php if ( is_string( $ag_url ) && '' !== $ag_url ) : ?>
						<a class="ghahghah-btn ghahghah-btn--solid ghahghah-btn--sm" href="<?php echo esc_url( $ag_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'مشاهده صفحه', 'ghahghah' ); ?>
						</a>
					<?php else : ?>
						<span class="ghahghah-field__help"><?php esc_html_e( 'برگه هنوز انتخاب نشده است.', 'ghahghah' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</div>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 12h10"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه خرید عمده', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عناوین، نوارهای اطلاع‌رسانی و متن‌های فرم.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<div class="ghahghah-field-grid">
				<?php
				$render_field( 'ghahghah_wholesale_intro_title', __( 'عنوان اصلی', 'ghahghah' ), $d );
				$render_field( 'ghahghah_wholesale_intro_text', __( 'زیرعنوان', 'ghahghah' ), $d, true );
				$render_field( 'ghahghah_wholesale_hero_badge', __( 'برچسب کنار تصویر', 'ghahghah' ), $d );
				$render_field( 'ghahghah_wholesale_footer_motto', __( 'کپشن پایین ستون چپ', 'ghahghah' ), $d );
				$render_field( 'ghahghah_wholesale_sms_note', __( 'نوار اطلاع‌رسانی پیامک', 'ghahghah' ), $d, true );
				$render_field( 'ghahghah_wholesale_price_note', __( 'نوار اطلاع‌رسانی قیمت', 'ghahghah' ), $d, true );
				$render_field( 'ghahghah_wholesale_form_title', __( 'عنوان فرم', 'ghahghah' ), $d );
				?>
			</div>

			<details class="ghahghah-config-card" style="margin-top:0.75rem">
				<summary><?php esc_html_e( 'فیلدهای legacy خرید عمده', 'ghahghah' ); ?></summary>
				<div class="ghahghah-field-grid" style="margin-top:0.75rem">
					<?php
					$render_field( 'ghahghah_wholesale_products_label', __( 'متن لینک محصولات (legacy)', 'ghahghah' ), $d );
					$render_field( 'ghahghah_wholesale_steps_title', __( 'عنوان مراحل (legacy)', 'ghahghah' ), $d );
					$render_field( 'ghahghah_wholesale_cross_title', __( 'عنوان کارت نمایندگی (legacy)', 'ghahghah' ), $d );
					$render_field( 'ghahghah_wholesale_cross_text', __( 'متن کارت نمایندگی (legacy)', 'ghahghah' ), $d, true );
					$render_field( 'ghahghah_wholesale_cross_button', __( 'دکمه نمایندگی (legacy)', 'ghahghah' ), $d );
					?>
				</div>
			</details>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه نمایندگی', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عناوین معرفی، تگ‌لاین و عنوان فرم نمایندگی.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<div class="ghahghah-field-grid">
				<?php
				$render_field( 'ghahghah_agency_intro_eyebrow', __( 'سوپرهد (بالای عنوان)', 'ghahghah' ), $d );
				$render_field( 'ghahghah_agency_intro_title', __( 'عنوان اصلی', 'ghahghah' ), $d );
				$render_field( 'ghahghah_agency_intro_text', __( 'زیرعنوان', 'ghahghah' ), $d, true );
				$render_field( 'ghahghah_agency_hero_tagline', __( 'تگ‌لاین کنار تصویر', 'ghahghah' ), $d );
				$render_field( 'ghahghah_agency_footer_motto', __( 'عبارت پایین ستون چپ', 'ghahghah' ), $d );
				$render_field( 'ghahghah_agency_form_title', __( 'عنوان فرم', 'ghahghah' ), $d );
				?>
			</div>

			<details class="ghahghah-config-card" style="margin-top:0.75rem">
				<summary><?php esc_html_e( 'فیلدهای legacy نمایندگی', 'ghahghah' ); ?></summary>
				<div class="ghahghah-field-grid" style="margin-top:0.75rem">
					<?php
					$render_field( 'ghahghah_agency_steps_title', __( 'عنوان مسیر بررسی (legacy)', 'ghahghah' ), $d );
					$render_field( 'ghahghah_agency_cross_title', __( 'عنوان کارت عمده (legacy)', 'ghahghah' ), $d );
					$render_field( 'ghahghah_agency_cross_text', __( 'متن کارت عمده (legacy)', 'ghahghah' ), $d, true );
					$render_field( 'ghahghah_agency_cross_button', __( 'دکمه عمده (legacy)', 'ghahghah' ), $d );
					?>
				</div>
			</details>
		</section>
	</div>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره تنظیمات صفحات', 'ghahghah' ); ?>
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
