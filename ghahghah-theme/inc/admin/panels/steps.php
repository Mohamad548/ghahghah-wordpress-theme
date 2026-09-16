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

$d       = ghahghah_steps_setting_defaults();
$enabled = (bool) get_theme_mod( 'ghahghah_steps_enabled', $d['ghahghah_steps_enabled'] );
$eyebrow = (string) get_theme_mod( 'ghahghah_steps_eyebrow', $d['ghahghah_steps_eyebrow'] );
$title   = (string) get_theme_mod( 'ghahghah_steps_title', $d['ghahghah_steps_title'] );
$text    = (string) get_theme_mod( 'ghahghah_steps_text', $d['ghahghah_steps_text'] );
$items   = ghahghah_sanitize_steps_items( get_theme_mod( 'ghahghah_steps_items', $d['ghahghah_steps_items'] ) );

if ( array() === $items ) {
	$items = array(
		array(
			'title' => '',
			'text'  => '',
		),
	);
}
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_steps_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="steps" />
	<?php wp_nonce_field( 'ghahghah_save_steps_settings', 'ghahghah_steps_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش و عنوان', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'بخش تا وقتی محتوا و مراحل را تأیید نکرده‌اید، خاموش بماند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="ghahghah_steps_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'نمایش بخش مراحل تولید در صفحه اصلی', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کوتاه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_steps_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" placeholder="<?php esc_attr_e( 'مثلاً: از مواد اولیه تا محصول', 'ghahghah' ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان اصلی', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_steps_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'مثلاً: مراحل تولید محصول', 'ghahghah' ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_steps_text" rows="2" placeholder="<?php esc_attr_e( 'جمله کوتاه زیر عنوان', 'ghahghah' ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
		</label>
	</section>

	<section class="ghahghah-panel-section" data-ghahghah-steps-admin>
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'مراحل', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'افزودن، حذف و جابه‌جایی مراحل. شماره از ترتیب ردیف ساخته می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-steps-admin__list" data-ghahghah-steps-list>
			<?php foreach ( $items as $index => $item ) : ?>
				<article class="ghahghah-steps-admin__row" data-ghahghah-steps-row>
					<div class="ghahghah-steps-admin__row-head">
						<span class="ghahghah-steps-admin__badge" data-ghahghah-steps-badge><?php echo esc_html( ghahghah_format_step_number( $index + 1 ) ); ?></span>
						<div class="ghahghah-steps-admin__tools">
							<button type="button" class="button button-secondary" data-ghahghah-steps-up><?php esc_html_e( 'بالا', 'ghahghah' ); ?></button>
							<button type="button" class="button button-secondary" data-ghahghah-steps-down><?php esc_html_e( 'پایین', 'ghahghah' ); ?></button>
							<button type="button" class="button" data-ghahghah-steps-remove><?php esc_html_e( 'حذف', 'ghahghah' ); ?></button>
						</div>
					</div>
					<label class="ghahghah-field">
						<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان مرحله', 'ghahghah' ); ?></span>
						<input type="text" name="ghahghah_steps_title_item[]" value="<?php echo esc_attr( $item['title'] ); ?>" />
					</label>
					<label class="ghahghah-field">
						<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح مرحله', 'ghahghah' ); ?></span>
						<textarea name="ghahghah_steps_text_item[]" rows="2"><?php echo esc_textarea( $item['text'] ); ?></textarea>
					</label>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="ghahghah-steps-admin__actions">
			<button type="button" class="button button-secondary" data-ghahghah-steps-add>
				<?php esc_html_e( 'افزودن مرحله', 'ghahghah' ); ?>
			</button>
			<span class="ghahghah-field__help"><?php echo esc_html( sprintf( /* translators: %d: max steps */ __( 'حداکثر %d مرحله.', 'ghahghah' ), GHAHGHAH_STEPS_MAX ) ); ?></span>
		</p>

		<template data-ghahghah-steps-template>
			<article class="ghahghah-steps-admin__row" data-ghahghah-steps-row>
				<div class="ghahghah-steps-admin__row-head">
					<span class="ghahghah-steps-admin__badge" data-ghahghah-steps-badge>01</span>
					<div class="ghahghah-steps-admin__tools">
						<button type="button" class="button button-secondary" data-ghahghah-steps-up><?php esc_html_e( 'بالا', 'ghahghah' ); ?></button>
						<button type="button" class="button button-secondary" data-ghahghah-steps-down><?php esc_html_e( 'پایین', 'ghahghah' ); ?></button>
						<button type="button" class="button" data-ghahghah-steps-remove><?php esc_html_e( 'حذف', 'ghahghah' ); ?></button>
					</div>
				</div>
				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان مرحله', 'ghahghah' ); ?></span>
					<input type="text" name="ghahghah_steps_title_item[]" value="" />
				</label>
				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح مرحله', 'ghahghah' ); ?></span>
					<textarea name="ghahghah_steps_text_item[]" rows="2"></textarea>
				</label>
			</article>
		</template>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?></button>
	</p>
</form>
