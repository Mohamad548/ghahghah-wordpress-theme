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

$d     = ghahghah_factory_page_defaults();
$page  = absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) );
$hero  = absint( get_theme_mod( 'ghahghah_factory_page_hero_image_id', 0 ) );
$badge = (bool) get_theme_mod( 'ghahghah_factory_page_show_temp_badge', $d['ghahghah_factory_page_show_temp_badge'] );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_factory_page" />
	<input type="hidden" name="ghahghah_return_tab" value="factory-page" />
	<?php wp_nonce_field( 'ghahghah_save_factory_page', 'ghahghah_factory_page_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
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
		<?php
		ghahghah_admin_render_media_field(
			'ghahghah_factory_page_hero_image_id',
			__( 'تصویر کارخانه', 'ghahghah' ),
			__( 'اختیاری؛ در صورت خالی‌بودن از تصویر پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
			$hero,
			GHAHGHAH_THEME_URI . '/assets/images/factory/factory-hero.webp'
		);
		?>
		<label class="ghahghah-field" style="display:flex;align-items:center;gap:0.5rem;">
			<input type="checkbox" name="ghahghah_factory_page_show_temp_badge" value="1" <?php checked( $badge ); ?> />
			<span><?php esc_html_e( 'نمایش برچسب «تصویر موقت کارخانه» روی تصویر پیش‌فرض', 'ghahghah' ); ?></span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'متن صفحه', 'ghahghah' ); ?></h3>
		</header>
		<?php
		$fields = array(
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
		foreach ( $fields as $key => $label ) :
			$val = (string) get_theme_mod( $key, $d[ $key ] );
			$is_area = str_contains( $key, '_intro' ) || str_contains( $key, '_text' );
			?>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( $is_area ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" rows="3"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</section>

	<p>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره صفحه کارخانه', 'ghahghah' ); ?></button>
	</p>
</form>
