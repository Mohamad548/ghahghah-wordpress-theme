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

$d          = ghahghah_contact_page_defaults();
$page       = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
$last_saved = absint( get_theme_mod( 'ghahghah_contact_page_last_saved', 0 ) );

$text_fields = array(
	'ghahghah_contact_page_title'       => __( 'عنوان اصلی', 'ghahghah' ),
	'ghahghah_contact_page_lead'        => __( 'زیرعنوان', 'ghahghah' ),
	'ghahghah_contact_form_title'       => __( 'عنوان فرم', 'ghahghah' ),
	'ghahghah_contact_form_text'        => __( 'توضیح فرم', 'ghahghah' ),
	'ghahghah_contact_info_title'       => __( 'عنوان اطلاعات تماس', 'ghahghah' ),
	'ghahghah_contact_info_text'        => __( 'توضیح اطلاعات تماس', 'ghahghah' ),
	'ghahghah_contact_hours'            => __( 'ساعات پاسخگویی', 'ghahghah' ),
	'ghahghah_contact_card_placeholder' => __( 'متن جایگزین کارت‌ها', 'ghahghah' ),
);
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_contact_page" />
	<input type="hidden" name="ghahghah_return_tab" value="contact-page" />
	<?php wp_nonce_field( 'ghahghah_save_contact_page', 'ghahghah_contact_page_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
			</span>
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

		<div class="ghahghah-status-banner is-warn">
			<span class="ghahghah-status-banner__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
			</span>
			<p class="ghahghah-status-banner__text">
				<?php esc_html_e( 'تلفن، ایمیل و نشانی از بخش «فوتر» تنظیم می‌شوند.', 'ghahghah' ); ?>
			</p>
			<a class="ghahghah-btn ghahghah-btn--solid" href="<?php echo esc_url( ghahghah_get_config_tab_url( 'footer' ) ); ?>">
				<?php esc_html_e( 'تنظیمات فوتر', 'ghahghah' ); ?>
			</a>
		</div>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.5 7-10a7 7 0 1 0-14 0c0 5.5 7 10 7 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نقشه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لینک اشتراک‌گذاری گوگل مپ (مثل maps.app.goo.gl) یا آدرس embed را وارد کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid">
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
		</div>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عناوین فرم، اطلاعات تماس و ساعات پاسخگویی.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid ghahghah-field-grid--2">
			<?php foreach ( $text_fields as $key => $label ) : ?>
				<?php
				$val     = (string) get_theme_mod( $key, $d[ $key ] );
				$is_area = str_contains( $key, '_text' ) || str_contains( $key, '_lead' );
				?>
				<label class="ghahghah-field<?php echo $is_area ? ' ghahghah-field--full' : ''; ?>">
					<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $is_area ) : ?>
						<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
					<?php else : ?>
						<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
					<?php endif; ?>
				</label>
			<?php endforeach; ?>
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
