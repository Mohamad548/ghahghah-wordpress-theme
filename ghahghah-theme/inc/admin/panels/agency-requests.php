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

$has_cpt = post_type_exists( 'ghahghah_inquiry' );
$list_url = $has_cpt ? admin_url( 'edit.php?post_type=ghahghah_inquiry' ) : '';
?>
<div class="ghahghah-panel-section">
	<header class="ghahghah-panel-section__head">
		<span class="ghahghah-panel-section__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
		</span>
		<div>
			<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'درخواست‌های عمده و نمایندگی', 'ghahghah' ); ?></h3>
			<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'درخواست‌های ثبت‌شده از فرم‌های صفحه اصلی در افزونه Core نگهداری می‌شوند.', 'ghahghah' ); ?></p>
		</div>
	</header>
	<div class="ghahghah-config-card">
		<?php if ( $has_cpt ) : ?>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( $list_url ); ?>">
					<?php esc_html_e( 'مشاهده فهرست درخواست‌ها', 'ghahghah' ); ?>
				</a>
			</p>
			<p class="ghahghah-field__help">
				<?php esc_html_e( 'فرم خرید عمده:', 'ghahghah' ); ?>
				<code><?php echo esc_html( ghahghah_get_wholesale_form_url() ); ?></code>
			</p>
			<p class="ghahghah-field__help">
				<?php esc_html_e( 'فرم نمایندگی:', 'ghahghah' ); ?>
				<code><?php echo esc_html( ghahghah_get_agency_form_url() ); ?></code>
			</p>
		<?php else : ?>
			<p class="ghahghah-config__placeholder">
				<?php esc_html_e( 'افزونه Ghahghah Core را فعال کنید تا فهرست درخواست‌ها در دسترس باشد.', 'ghahghah' ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
