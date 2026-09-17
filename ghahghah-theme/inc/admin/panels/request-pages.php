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

$d = ghahghah_request_pages_defaults();
$wh_id = absint( get_theme_mod( 'ghahghah_wholesale_page_id', 0 ) );
$ag_id = absint( get_theme_mod( 'ghahghah_agency_page_id', 0 ) );
$img_id = absint( get_theme_mod( 'ghahghah_wholesale_image_id', 0 ) );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_request_pages" />
	<input type="hidden" name="ghahghah_return_tab" value="request-pages" />
	<?php wp_nonce_field( 'ghahghah_save_request_pages', 'ghahghah_request_pages_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه‌های درخواست', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه‌های وردپرس با قالب «خرید عمده» و «درخواست نمایندگی» را انتخاب کنید. شناسه در محیط‌های مختلف فرق می‌کند.', 'ghahghah' ); ?></p>
			</div>
		</header>

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

		<?php
		ghahghah_admin_render_media_field(
			'ghahghah_wholesale_image_id',
			__( 'تصویر معرفی خرید عمده', 'ghahghah' ),
			__( 'اختیاری؛ در صورت خالی‌بودن از تصویر بهینه‌شده قالب استفاده می‌شود.', 'ghahghah' ),
			$img_id,
			GHAHGHAH_THEME_URI . '/assets/images/request/wholesale-snacks.jpg'
		);
		?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه خرید عمده', 'ghahghah' ); ?></h3>
			</div>
		</header>
		<?php
		$wh_fields = array(
			'ghahghah_wholesale_intro_title'    => __( 'عنوان اصلی', 'ghahghah' ),
			'ghahghah_wholesale_intro_text'     => __( 'زیرعنوان', 'ghahghah' ),
			'ghahghah_wholesale_hero_badge'     => __( 'برچسب کنار تصویر', 'ghahghah' ),
			'ghahghah_wholesale_footer_motto'   => __( 'کپشن پایین ستون چپ', 'ghahghah' ),
			'ghahghah_wholesale_sms_note'       => __( 'نوار اطلاع‌رسانی پیامک', 'ghahghah' ),
			'ghahghah_wholesale_price_note'     => __( 'نوار اطلاع‌رسانی قیمت', 'ghahghah' ),
			'ghahghah_wholesale_products_label' => __( 'متن لینک محصولات (legacy)', 'ghahghah' ),
			'ghahghah_wholesale_form_title'     => __( 'عنوان فرم (پنل همکاری)', 'ghahghah' ),
			'ghahghah_wholesale_steps_title'    => __( 'عنوان مراحل (legacy)', 'ghahghah' ),
			'ghahghah_wholesale_cross_title'    => __( 'عنوان کارت نمایندگی (legacy)', 'ghahghah' ),
			'ghahghah_wholesale_cross_text'     => __( 'متن کارت نمایندگی (legacy)', 'ghahghah' ),
			'ghahghah_wholesale_cross_button'   => __( 'دکمه نمایندگی (legacy)', 'ghahghah' ),
		);
		foreach ( $wh_fields as $key => $label ) :
			$val = (string) get_theme_mod( $key, $d[ $key ] );
			$tag = str_contains( $key, '_text' ) ? 'textarea' : 'input';
			?>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( 'textarea' === $tag ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه نمایندگی', 'ghahghah' ); ?></h3>
			</div>
		</header>
		<?php
		$ag_fields = array(
			'ghahghah_agency_intro_eyebrow' => __( 'سوپرهد (بالای عنوان)', 'ghahghah' ),
			'ghahghah_agency_intro_title'   => __( 'عنوان اصلی', 'ghahghah' ),
			'ghahghah_agency_intro_text'    => __( 'زیرعنوان', 'ghahghah' ),
			'ghahghah_agency_hero_tagline'  => __( 'تگ‌لاین کنار تصویر', 'ghahghah' ),
			'ghahghah_agency_footer_motto'  => __( 'عبارت پایین ستون چپ', 'ghahghah' ),
			'ghahghah_agency_form_title'    => __( 'عنوان فرم (پنل همکاری)', 'ghahghah' ),
			'ghahghah_agency_steps_title'   => __( 'عنوان مسیر بررسی (legacy)', 'ghahghah' ),
			'ghahghah_agency_cross_title'   => __( 'عنوان کارت عمده (legacy)', 'ghahghah' ),
			'ghahghah_agency_cross_text'    => __( 'متن کارت عمده (legacy)', 'ghahghah' ),
			'ghahghah_agency_cross_button'  => __( 'دکمه عمده (legacy)', 'ghahghah' ),
		);
		foreach ( $ag_fields as $key => $label ) :
			$val = (string) get_theme_mod( $key, $d[ $key ] );
			$tag = str_contains( $key, '_text' ) ? 'textarea' : 'input';
			?>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( 'textarea' === $tag ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات صفحات', 'ghahghah' ); ?></button>
	</p>
</form>
