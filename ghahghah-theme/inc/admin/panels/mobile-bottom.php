<?php
/**
 * Mobile bottom navigation settings panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_bottom_enabled = ghahghah_is_bottom_nav_enabled();
$ghahghah_validation     = ghahghah_get_bottom_nav_validation();
$ghahghah_menus_url      = admin_url( 'nav-menus.php' );
$ghahghah_customizer_url = admin_url( 'customize.php?autofocus[section]=ghahghah_bottom_nav' );
$last_saved              = absint( get_theme_mod( 'ghahghah_mobile_bottom_last_saved', 0 ) );

?>

<form
	class="ghahghah-panel-form"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
>
	<input type="hidden" name="action" value="ghahghah_save_mobile_bottom_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="mobile-bottom" />
	<?php wp_nonce_field( 'ghahghah_save_mobile_bottom_settings', 'ghahghah_mobile_bottom_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/><path d="M18 15v4M16 17h4"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نوار پایین صفحه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نوار ثابت پایین موبایل با چهار مسیر اصلی؛ ظاهر مطابق طرح قهقهه.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_bottom_nav_enabled" value="1" <?php checked( $ghahghah_bottom_enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش نوار پایین در موبایل', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'چهار مسیر اصلی در پایین صفحه ثابت می‌مانند.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-status-banner is-warn">
			<span class="ghahghah-status-banner__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
			</span>
			<p class="ghahghah-status-banner__text">
				<?php esc_html_e( 'عنوان، ترتیب و مقصد هر گزینه را از فهرست‌های وردپرس مدیریت کنید.', 'ghahghah' ); ?>
			</p>
			<a class="ghahghah-btn ghahghah-btn--solid" href="<?php echo esc_url( $ghahghah_menus_url ); ?>">
				<?php esc_html_e( 'مدیریت فهرست‌ها', 'ghahghah' ); ?>
			</a>
		</div>

		<p class="ghahghah-field__help">
			<?php esc_html_e( 'برای هر گزینه فهرست، فیلد «آیکن نوار پایین» را از چهار کلید مجاز (خانه / محصولات / خرید عمده / تماس) انتخاب کنید.', 'ghahghah' ); ?>
		</p>

		<a class="ghahghah-link-card" href="<?php echo esc_url( $ghahghah_customizer_url ); ?>">
			<span><?php esc_html_e( 'باز کردن همین تنظیم در سفارشی‌سازی', 'ghahghah' ); ?></span>
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
		</a>

		<?php if ( array() !== $ghahghah_validation['messages'] ) : ?>
			<div class="ghahghah-status-banner <?php echo $ghahghah_validation['ok'] ? 'is-warn' : 'is-warn'; ?>">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
				</span>
				<ul class="ghahghah-status-banner__text">
					<?php foreach ( $ghahghah_validation['messages'] as $ghahghah_message ) : ?>
						<li><?php echo esc_html( $ghahghah_message ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( $ghahghah_bottom_enabled && $ghahghah_validation['ok'] ) : ?>
			<div class="ghahghah-status-banner is-ok">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
				</span>
				<p class="ghahghah-status-banner__text"><?php esc_html_e( 'فهرست معتبر است و نوار در موبایل آماده نمایش است.', 'ghahghah' ); ?></p>
			</div>
		<?php endif; ?>
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
