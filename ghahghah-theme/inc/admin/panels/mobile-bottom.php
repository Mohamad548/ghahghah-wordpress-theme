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
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="16" width="16" height="5" rx="1.5"/><path d="M8 18.5h.01M12 18.5h.01M16 18.5h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نوار پایین صفحه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نوار ثابت پایین موبایل با چهار مسیر اصلی؛ ظاهر مطابق طرح قهقهه.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_bottom_nav_enabled" value="1" <?php checked( $ghahghah_bottom_enabled ); ?> />
			<span><?php esc_html_e( 'نمایش نوار پایین در موبایل', 'ghahghah' ); ?></span>
		</label>

		<div class="ghahghah-config-card" style="margin-top:1rem">
			<p>
				<?php
				printf(
					/* translators: %s: menus admin URL */
					wp_kses(
						__( 'عنوان، ترتیب و مقصد هر گزینه را از <a href="%s">نمایش ← فهرست‌ها</a> مدیریت کنید و فهرست را به جایگاه «ناوبری پایین موبایل» اختصاص دهید.', 'ghahghah' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( $ghahghah_menus_url )
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'برای هر گزینه فهرست، فیلد «آیکن نوار پایین» را از چهار کلید مجاز (خانه / محصولات / خرید عمده / تماس) انتخاب کنید.', 'ghahghah' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $ghahghah_customizer_url ); ?>">
					<?php esc_html_e( 'باز کردن همین تنظیم در سفارشی‌سازی', 'ghahghah' ); ?>
				</a>
			</p>
		</div>

		<?php if ( array() !== $ghahghah_validation['messages'] ) : ?>
			<div class="ghahghah-status-card ghahghah-status-card--<?php echo $ghahghah_validation['ok'] ? 'warn' : 'error'; ?>">
				<ul class="ghahghah-status-card__list">
					<?php foreach ( $ghahghah_validation['messages'] as $ghahghah_message ) : ?>
						<li><?php echo esc_html( $ghahghah_message ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php elseif ( $ghahghah_bottom_enabled && $ghahghah_validation['ok'] ) : ?>
			<div class="ghahghah-status-card ghahghah-status-card--ok">
				<p><?php esc_html_e( 'فهرست معتبر است و نوار در موبایل آماده نمایش است.', 'ghahghah' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره نوار پایین موبایل', 'ghahghah' ); ?>
		</button>
	</div>
</form>
