<?php
/**
 * Homepage articles configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d        = ghahghah_articles_setting_defaults();
$enabled  = (bool) get_theme_mod( 'ghahghah_articles_enabled', $d['ghahghah_articles_enabled'] );
$eyebrow  = (string) get_theme_mod( 'ghahghah_articles_eyebrow', $d['ghahghah_articles_eyebrow'] );
$title    = (string) get_theme_mod( 'ghahghah_articles_title', $d['ghahghah_articles_title'] );
$all      = (string) get_theme_mod( 'ghahghah_articles_all_label', $d['ghahghah_articles_all_label'] );
$more     = (string) get_theme_mod( 'ghahghah_articles_more_label', $d['ghahghah_articles_more_label'] );
$cat_saved = absint( get_theme_mod( 'ghahghah_articles_category', 0 ) );
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_articles_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="articles" />
	<?php wp_nonce_field( 'ghahghah_save_articles_settings', 'ghahghah_articles_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'آخرین مطالب', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'کاروسل افقی آخرین نوشته‌های منتشرشده؛ هر نوشته جدید در قالب در این بخش می‌آید (تا سقف ۱۲ مورد).', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="ghahghah_articles_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'نمایش بخش آخرین مطالب در صفحه اصلی', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کوتاه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_articles_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان اصلی', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_articles_title" value="<?php echo esc_attr( $title ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه همه مطالب', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_articles_all_label" value="<?php echo esc_attr( $all ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن لینک ادامه مطلب', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_articles_more_label" value="<?php echo esc_attr( $more ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'دسته مطالب', 'ghahghah' ); ?></span>
			<?php
			wp_dropdown_categories(
				array(
					'name'             => 'ghahghah_articles_category',
					'selected'         => $cat_saved,
					'show_option_none' => __( '— همه نوشته‌های منتشرشده —', 'ghahghah' ),
					'option_none_value'=> '0',
					'hide_empty'       => false,
					'taxonomy'         => 'category',
				)
			);
			?>
			<span class="ghahghah-field__help"><?php esc_html_e( 'اگر خالی بماند، همه نوشته‌های منتشرشده نمایش داده می‌شوند. با انتخاب دسته، فقط همان دسته فیلتر می‌شود.', 'ghahghah' ); ?></span>
		</label>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?></button>
	</p>
</form>
