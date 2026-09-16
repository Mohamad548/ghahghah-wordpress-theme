<?php
/**
 * Site header markup.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_sticky_class = ghahghah_is_header_sticky() ? ' site-header--sticky' : '';
$ghahghah_cta          = ghahghah_get_header_cta();
?>
<header
	class="site-header<?php echo esc_attr( $ghahghah_sticky_class ); ?>"
	role="banner"
	data-ghahghah-header
>
	<div class="site-header__bar">
		<div class="site-header__inner">
			<button
				type="button"
				class="site-header__menu-button"
				data-ghahghah-drawer-open
				aria-expanded="false"
				aria-controls="ghahghah-mobile-drawer"
			>
				<span class="screen-reader-text"><?php esc_html_e( 'باز کردن منو', 'ghahghah' ); ?></span>
				<span class="site-header__menu-icon" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
				</span>
			</button>

			<div class="site-header__brand-slot">
				<?php ghahghah_the_site_brand(); ?>
			</div>

			<div class="site-header__mobile-end">
				<?php ghahghah_the_header_search_toggle_mobile(); ?>
				<?php ghahghah_the_header_cta_mobile(); ?>
			</div>

			<div class="site-header__desktop">
				<?php ghahghah_the_primary_nav( 'desktop', 'primary-navigation-desktop' ); ?>
				<?php ghahghah_the_header_search( 'desktop' ); ?>
				<?php ghahghah_the_header_cta( 'desktop' ); ?>
			</div>
		</div>

		<div
			id="ghahghah-mobile-search"
			class="site-header__mobile-search"
			data-ghahghah-mobile-search
			hidden
		>
			<?php ghahghah_the_header_search( 'mobile' ); ?>
		</div>
	</div>

	<div
		class="site-header__overlay"
		data-ghahghah-drawer-overlay
		hidden
	></div>

	<div
		id="ghahghah-mobile-drawer"
		class="site-header__drawer"
		data-ghahghah-drawer
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'منوی اصلی', 'ghahghah' ); ?>"
		hidden
	>
		<div class="site-header__drawer-top">
			<?php ghahghah_the_site_brand( 'drawer' ); ?>

			<button
				type="button"
				class="site-header__drawer-close"
				data-ghahghah-drawer-close
			>
				<span class="screen-reader-text"><?php esc_html_e( 'بستن منو', 'ghahghah' ); ?></span>
				<span class="site-header__close-icon" aria-hidden="true"></span>
			</button>
		</div>

		<div class="site-header__drawer-body">
			<?php ghahghah_the_header_search( 'drawer' ); ?>
			<?php ghahghah_the_primary_nav( 'mobile', 'primary-navigation-mobile' ); ?>
		</div>

		<?php if ( null !== $ghahghah_cta ) : ?>
			<div class="site-header__drawer-footer">
				<?php ghahghah_the_header_cta( 'drawer' ); ?>
			</div>
		<?php endif; ?>
	</div>
</header>

<noscript>
	<style>
		.site-header__menu-button,
		.site-header__mobile-end { display: none !important; }
		.site-header__desktop { display: flex !important; }
		.site-header__search--desktop { display: flex !important; }
		.site-header__desktop .site-nav__sub[hidden] { display: block !important; }
		@media (max-width: 1023px) {
			.site-header__inner {
				flex-wrap: wrap;
			}
			.site-header__brand-slot {
				position: static;
				transform: none;
				flex: 1 1 auto;
			}
			.site-header__desktop {
				flex-basis: 100%;
				order: 3;
			}
			.site-header__desktop .site-nav__list {
				flex-direction: column;
				align-items: stretch;
			}
			.site-header__cta--desktop {
				display: inline-flex !important;
				margin-block-start: 0.75rem;
			}
		}
	</style>
</noscript>
