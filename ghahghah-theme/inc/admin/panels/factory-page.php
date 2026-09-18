<?php
/**
 * Admin panel: factory intro page settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d          = ghahghah_factory_page_defaults();
$page       = absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) );
$hero       = absint( get_theme_mod( 'ghahghah_factory_page_hero_image_id', 0 ) );
$badge      = (bool) get_theme_mod( 'ghahghah_factory_page_show_temp_badge', $d['ghahghah_factory_page_show_temp_badge'] );
$last_saved = absint( get_theme_mod( 'ghahghah_factory_page_last_saved', 0 ) );

$text_fields = array(
	'ghahghah_factory_page_title'         => __( 'عنوان اصلی', 'ghahghah' ),
	'ghahghah_factory_page_lead'          => __( 'زیرعنوان', 'ghahghah' ),
	'ghahghah_factory_page_company'       => __( 'نام شرکت', 'ghahghah' ),
	'ghahghah_factory_page_intro'         => __( 'متن معرفی', 'ghahghah' ),
	'ghahghah_factory_page_cta_label'     => __( 'دکمه محصولات', 'ghahghah' ),
	'ghahghah_factory_page_process_title' => __( 'عنوان مراحل تولید', 'ghahghah' ),
	'ghahghah_factory_page_process_text'  => __( 'توضیح مراحل', 'ghahghah' ),
	'ghahghah_factory_page_quality_title' => __( 'عنوان کیفیت', 'ghahghah' ),
	'ghahghah_factory_page_quality_text'  => __( 'توضیح کیفیت', 'ghahghah' ),
	'ghahghah_factory_page_certs_title'   => __( 'عنوان مدارک', 'ghahghah' ),
	'ghahghah_factory_page_certs_text'    => __( 'توضیح مدارک', 'ghahghah' ),
);
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_factory_page" />
	<input type="hidden" name="ghahghah_return_tab" value="factory-page" />
	<?php wp_nonce_field( 'ghahghah_save_factory_page', 'ghahghah_factory_page_nonce' ); ?>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه معرفی کارخانه', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه وردپرس با قالب «معرفی کارخانه» را انتخاب کنید.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_factory_page_id',
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
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تصویر و برچسب', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'تصویر اختیاری و نمایش برچسب موقت روی تصویر پیش‌فرض.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_factory_page_hero_image_id',
				__( 'تصویر کارخانه', 'ghahghah' ),
				__( 'اختیاری؛ در صورت خالی‌بودن از تصویر پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
				$hero,
				GHAHGHAH_THEME_URI . '/assets/images/factory/factory-hero.webp'
			);
			?>

			<label class="ghahghah-switch">
				<input type="checkbox" name="ghahghah_factory_page_show_temp_badge" value="1" <?php checked( $badge ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'برچسب «تصویر موقت کارخانه»', 'ghahghah' ); ?></strong>
					<small><?php esc_html_e( 'روی تصویر پیش‌فرض نمایش داده می‌شود.', 'ghahghah' ); ?></small>
				</span>
			</label>
		</section>
	</div>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'عناوین، معرفی و بخش‌های مراحل، کیفیت و مدارک.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid ghahghah-field-grid--2">
			<?php foreach ( $text_fields as $key => $label ) : ?>
				<?php
				$val     = (string) get_theme_mod( $key, $d[ $key ] );
				$is_area = str_contains( $key, '_intro' ) || str_contains( $key, '_text' );
				?>
				<label class="ghahghah-field<?php echo $is_area ? ' ghahghah-field--full' : ''; ?>">
					<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
					<?php if ( $is_area ) : ?>
						<textarea name="<?php echo esc_attr( $key ); ?>" rows="3"><?php echo esc_textarea( $val ); ?></textarea>
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
