<?php
/**
 * Agency / wholesale requests — link to Core CPT list.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_cpt  = post_type_exists( 'ghahghah_inquiry' );
$list_url = $has_cpt ? admin_url( 'edit.php?post_type=ghahghah_inquiry' ) : '';
?>
<section class="ghahghah-panel-section">
	<header class="ghahghah-panel-section__head">
		<span class="ghahghah-panel-section__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/></svg>
		</span>
		<div>
			<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'درخواست‌های عمده و نمایندگی', 'ghahghah' ); ?></h3>
			<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'درخواست‌های ثبت‌شده از فرم‌های صفحه اصلی در افزونه Core نگهداری می‌شوند.', 'ghahghah' ); ?></p>
		</div>
	</header>

	<?php if ( $has_cpt ) : ?>
		<div class="ghahghah-status-banner is-ok">
			<span class="ghahghah-status-banner__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
			</span>
			<p class="ghahghah-status-banner__text"><?php esc_html_e( 'افزونه Core فعال است. فهرست درخواست‌ها آماده مشاهده است.', 'ghahghah' ); ?></p>
			<a class="ghahghah-btn ghahghah-btn--solid" href="<?php echo esc_url( $list_url ); ?>">
				<?php esc_html_e( 'مشاهده فهرست درخواست‌ها', 'ghahghah' ); ?>
			</a>
		</div>

		<div class="ghahghah-field-grid">
			<div class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'فرم خرید عمده', 'ghahghah' ); ?></span>
				<code dir="ltr" style="display:block;padding:0.5rem 0.65rem;background:var(--gg-admin-bg,#f5f6f8);border-radius:6px;font-size:12px;"><?php echo esc_html( ghahghah_get_wholesale_form_url() ); ?></code>
			</div>
			<div class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'فرم نمایندگی', 'ghahghah' ); ?></span>
				<code dir="ltr" style="display:block;padding:0.5rem 0.65rem;background:var(--gg-admin-bg,#f5f6f8);border-radius:6px;font-size:12px;"><?php echo esc_html( ghahghah_get_agency_form_url() ); ?></code>
			</div>
		</div>
	<?php else : ?>
		<div class="ghahghah-status-banner is-warn">
			<span class="ghahghah-status-banner__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
			</span>
			<p class="ghahghah-status-banner__text">
				<?php esc_html_e( 'افزونه Ghahghah Core را فعال کنید تا فهرست درخواست‌ها در دسترس باشد.', 'ghahghah' ); ?>
			</p>
		</div>
	<?php endif; ?>
</section>
