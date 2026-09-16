<?php
/**
 * Admin panel: contact page settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d    = ghahghah_contact_page_defaults();
$page = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_contact_page" />
	<input type="hidden" name="ghahghah_return_tab" value="contact-page" />
	<?php wp_nonce_field( 'ghahghah_save_contact_page', 'ghahghah_contact_page_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه تماس با ما', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه وردپرس با قالب «تماس با ما» را انتخاب کنید. تلفن، ایمیل و نشانی از تنظیمات فوتر خوانده می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'برگه', 'ghahghah' ); ?></span>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => 'ghahghah_contact_page_id',
					'selected'          => $page,
					'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
					'option_none_value' => '0',
				)
			);
			?>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نقشه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لینک اشتراک‌گذاری گوگل مپ (مثل maps.app.goo.gl) یا آدرس embed را وارد کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'آدرس نقشه', 'ghahghah' ); ?></span>
			<input
				type="url"
				name="ghahghah_contact_map_url"
				value="<?php echo esc_attr( (string) get_theme_mod( 'ghahghah_contact_map_url', $d['ghahghah_contact_map_url'] ) ); ?>"
				placeholder="https://maps.app.goo.gl/..."
				dir="ltr"
			/>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن لینک روی نقشه', 'ghahghah' ); ?></span>
			<input
				type="text"
				name="ghahghah_contact_map_text"
				value="<?php echo esc_attr( (string) get_theme_mod( 'ghahghah_contact_map_text', $d['ghahghah_contact_map_text'] ) ); ?>"
			/>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه', 'ghahghah' ); ?></h3>
		</header>
		<?php
		$fields = array(
			'ghahghah_contact_page_title'       => __( 'عنوان اصلی', 'ghahghah' ),
			'ghahghah_contact_page_lead'        => __( 'زیرعنوان', 'ghahghah' ),
			'ghahghah_contact_form_title'       => __( 'عنوان فرم', 'ghahghah' ),
			'ghahghah_contact_form_text'        => __( 'توضیح فرم', 'ghahghah' ),
			'ghahghah_contact_info_title'       => __( 'عنوان اطلاعات تماس', 'ghahghah' ),
			'ghahghah_contact_info_text'        => __( 'توضیح اطلاعات تماس', 'ghahghah' ),
			'ghahghah_contact_hours'            => __( 'ساعات پاسخگویی', 'ghahghah' ),
			'ghahghah_contact_card_placeholder' => __( 'متن جایگزین کارت‌ها', 'ghahghah' ),
		);
		foreach ( $fields as $key => $label ) :
			$val     = (string) get_theme_mod( $key, $d[ $key ] );
			$is_area = str_contains( $key, '_text' ) || str_contains( $key, '_lead' );
			?>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( $is_area ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات تماس', 'ghahghah' ); ?></button>
	</p>
</form>
