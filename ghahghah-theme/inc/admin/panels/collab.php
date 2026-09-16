<?php
/**
 * Collab (wholesale / agency) CTA configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d       = ghahghah_collab_setting_defaults();
$enabled = (bool) get_theme_mod( 'ghahghah_collab_enabled', $d['ghahghah_collab_enabled'] );
$eyebrow = (string) get_theme_mod( 'ghahghah_collab_eyebrow', $d['ghahghah_collab_eyebrow'] );
$title   = (string) get_theme_mod( 'ghahghah_collab_title', $d['ghahghah_collab_title'] );
$text    = (string) get_theme_mod( 'ghahghah_collab_text', $d['ghahghah_collab_text'] );

$w_title  = (string) get_theme_mod( 'ghahghah_collab_wholesale_title', $d['ghahghah_collab_wholesale_title'] );
$w_text   = (string) get_theme_mod( 'ghahghah_collab_wholesale_text', $d['ghahghah_collab_wholesale_text'] );
$w_button = (string) get_theme_mod( 'ghahghah_collab_wholesale_button', $d['ghahghah_collab_wholesale_button'] );

$a_title  = (string) get_theme_mod( 'ghahghah_collab_agency_title', $d['ghahghah_collab_agency_title'] );
$a_text   = (string) get_theme_mod( 'ghahghah_collab_agency_text', $d['ghahghah_collab_agency_text'] );
$a_button = (string) get_theme_mod( 'ghahghah_collab_agency_button', $d['ghahghah_collab_agency_button'] );

$wholesale_status = ghahghah_get_collab_form_status( 'wholesale' );
$agency_status    = ghahghah_get_collab_form_status( 'agency' );
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_collab_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="collab" />
	<?php wp_nonce_field( 'ghahghah_save_collab_settings', 'ghahghah_collab_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="7" height="14" rx="1.5"/><rect x="14" y="5" width="7" height="14" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش و عنوان بخش', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'کارت‌ها فقط وقتی در سایت دیده می‌شوند که فرم مرتبط از Core آماده باشد.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="ghahghah_collab_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'نمایش بخش خرید عمده و نمایندگی در صفحه اصلی', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کوتاه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان اصلی', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_title" value="<?php echo esc_attr( $title ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_collab_text" rows="2"><?php echo esc_textarea( $text ); ?></textarea>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'وضعیت فرم‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'ثبت درخواست و پیامک در Core انجام می‌شود؛ قالب فقط فرم آماده را آشکار می‌کند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php foreach ( array( $wholesale_status, $agency_status ) as $status ) : ?>
			<div class="ghahghah-status-card ghahghah-status-card--<?php echo $status['available'] ? 'ok' : 'warn'; ?>">
				<p>
					<?php
					echo esc_html( $status['label'] . ': ' );
					if ( $status['available'] ) {
						if ( 'preview' === $status['source'] ) {
							esc_html_e( 'فقط پیش‌نمایش ظاهر (بدون ارسال)', 'ghahghah' );
						} else {
							esc_html_e( 'آماده اتصال', 'ghahghah' );
						}
					} else {
						esc_html_e( 'هنوز آماده نیست — در خروجی عمومی نمایش داده نمی‌شود', 'ghahghah' );
					}
					?>
				</p>
			</div>
		<?php endforeach; ?>

		<label class="ghahghah-field ghahghah-field--check" style="margin-top:1rem">
			<input type="checkbox" name="ghahghah_collab_preview_forms" value="1" <?php checked( (bool) get_theme_mod( 'ghahghah_collab_preview_forms', $d['ghahghah_collab_preview_forms'] ) ); ?> />
			<span><?php esc_html_e( 'پیش‌نمایش کارت‌ها تا آماده شدن فرم Core (بدون ارسال درخواست/پیامک)', 'ghahghah' ); ?></span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 12h10"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'کارت خرید عمده', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متن کارت قرمز سمت راست در دسکتاپ.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کارت', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_wholesale_title" value="<?php echo esc_attr( $w_title ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح کارت', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_collab_wholesale_text" rows="2"><?php echo esc_textarea( $w_text ); ?></textarea>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_wholesale_button" value="<?php echo esc_attr( $w_button ); ?>" />
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'کارت نمایندگی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متن کارت کرم سمت چپ در دسکتاپ.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کارت', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_agency_title" value="<?php echo esc_attr( $a_title ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح کارت', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_collab_agency_text" rows="2"><?php echo esc_textarea( $a_text ); ?></textarea>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_collab_agency_button" value="<?php echo esc_attr( $a_button ); ?>" />
		</label>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?></button>
	</p>
</form>
