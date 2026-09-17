<?php
/**
 * Template helper functions.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the Ghahghah Core plugin is active and bootstrapped.
 */
function ghahghah_is_core_active(): bool {
	return defined( 'GHAHGHAH_CORE_VERSION' );
}

/**
 * Render the site brand (custom logo or site name).
 *
 * @param string $context Optional context class suffix (e.g. drawer).
 */
function ghahghah_the_site_brand( string $context = '' ): void {
	$extra_class = '' !== $context ? ' site-brand--' . sanitize_html_class( $context ) : '';
	$desktop_url = ghahghah_get_header_logo_url( 'desktop' );
	$mobile_url  = ghahghah_get_header_logo_url( 'mobile' );
	$alt         = get_bloginfo( 'name', 'display' );
	$width       = ghahghah_get_header_logo_width_desktop();
	$height      = 64;

	echo '<div class="site-brand' . esc_attr( $extra_class ) . '">';

	printf(
		'<a class="custom-logo-link site-brand__link" href="%1$s" rel="home"><picture><source media="(max-width: 63.99rem)" srcset="%2$s" /><img src="%3$s" class="custom-logo site-brand__image" alt="%4$s" width="%5$d" height="%6$d" decoding="async" fetchpriority="low" /></picture></a>',
		esc_url( home_url( '/' ) ),
		esc_url( $mobile_url ),
		esc_url( $desktop_url ),
		esc_attr( $alt ),
		(int) $width,
		(int) $height
	);

	echo '</div>';
}

/**
 * Render a navigation menu or an accessible empty fallback.
 *
 * Used by footer (and other non-header locations). Keeps simple markup.
 *
 * @param string $location  Theme location slug.
 * @param string $css_class Extra CSS class for the nav element.
 */
function ghahghah_the_nav( string $location, string $css_class = '' ): void {
	$nav_class = trim( 'site-nav ' . $css_class );

	if ( ! has_nav_menu( $location ) ) {
		printf(
			'<nav class="%1$s" aria-label="%2$s"><p class="site-nav__empty">%3$s</p></nav>',
			esc_attr( $nav_class ),
			esc_attr( ghahghah_nav_label( $location ) ),
			esc_html__( 'منویی برای این بخش تنظیم نشده است.', 'ghahghah' )
		);
		return;
	}

	wp_nav_menu(
		array(
			'theme_location'       => $location,
			'container'            => 'nav',
			'container_class'      => $nav_class,
			'container_aria_label' => ghahghah_nav_label( $location ),
			'menu_class'           => 'site-nav__list',
			'fallback_cb'          => false,
			'depth'                => 2,
		)
	);
}

/**
 * Render the primary header navigation with accessible submenu toggles.
 *
 * @param string $context Unique DOM context: desktop|mobile.
 * @param string $nav_id  Element id for the nav container.
 */
function ghahghah_the_primary_nav( string $context, string $nav_id ): void {
	$nav_class = 'site-nav site-nav--primary site-nav--' . sanitize_html_class( $context );

	if ( ! has_nav_menu( 'primary' ) ) {
		$empty = current_user_can( 'edit_theme_options' )
			? __( 'منوی اصلی تنظیم نشده است. از نمایش ← فهرست‌ها، یک فهرست به جایگاه «منوی اصلی» اختصاص دهید.', 'ghahghah' )
			: '';

		if ( '' !== $empty ) {
			printf(
				'<nav id="%1$s" class="%2$s" aria-label="%3$s"><p class="site-nav__empty">%4$s</p></nav>',
				esc_attr( $nav_id ),
				esc_attr( $nav_class ),
				esc_attr( ghahghah_nav_label( 'primary' ) ),
				esc_html( $empty )
			);
		}
		return;
	}

	wp_nav_menu(
		array(
			'theme_location'       => 'primary',
			'container'            => 'nav',
			'container_id'         => $nav_id,
			'container_class'      => $nav_class,
			'container_aria_label' => ghahghah_nav_label( 'primary' ),
			'menu_class'           => 'site-nav__list list-reset',
			'menu_id'              => 'primary-menu-' . sanitize_html_class( $context ),
			'fallback_cb'          => false,
			'depth'                => 3,
			'walker'               => new Ghahghah_Primary_Nav_Walker( $context ),
		)
	);
}

/**
 * Render the wholesale CTA button when configured and destination is public.
 *
 * @param string $modifier BEM modifier (desktop|drawer).
 */
function ghahghah_the_header_cta( string $modifier = 'desktop' ): void {
	$cta = ghahghah_get_header_cta();
	if ( null === $cta ) {
		return;
	}

	$class = 'site-header__cta site-header__cta--' . sanitize_html_class( $modifier );

	printf(
		'<a class="%1$s" href="%2$s"><span class="site-header__cta-label">%3$s</span><span class="site-header__cta-icon" aria-hidden="true"></span></a>',
		esc_attr( $class ),
		esc_url( $cta['url'] ),
		esc_html( $cta['label'] )
	);
}

/**
 * Render a compact text CTA for the mobile header end (no cart icon).
 */
function ghahghah_the_header_cta_mobile(): void {
	$cta = ghahghah_get_header_cta();
	if ( null === $cta ) {
		return;
	}

	printf(
		'<a class="site-header__cta site-header__cta--mobile-text" href="%1$s"><span class="site-header__cta-mobile-label">%2$s</span></a>',
		esc_url( $cta['url'] ),
		esc_html__( 'خرید عمده', 'ghahghah' )
	);
}

/**
 * Render mobile header search icon button.
 */
function ghahghah_the_header_search_toggle_mobile(): void {
	$icon = '';
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/header/search.svg';
	if ( is_readable( $path ) ) {
		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( is_string( $raw ) && '' !== $raw ) {
			$icon = (string) preg_replace(
				'/<svg\b/i',
				'<svg class="site-header__search-toggle-glyph" aria-hidden="true" focusable="false"',
				$raw,
				1
			);
		}
	}
	?>
	<button
		type="button"
		class="site-header__search-toggle"
		data-ghahghah-mobile-search-open
		aria-expanded="false"
		aria-controls="ghahghah-mobile-search"
	>
		<span class="screen-reader-text"><?php esc_html_e( 'باز کردن جستجو', 'ghahghah' ); ?></span>
		<?php
		if ( '' !== $icon ) {
			echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme SVG asset.
		}
		?>
	</button>
	<?php
}

/**
 * Render header search form (products + articles + site).
 *
 * @param string $modifier BEM modifier: desktop|drawer|mobile.
 */
function ghahghah_the_header_search( string $modifier = 'desktop' ): void {
	$modifier = sanitize_html_class( $modifier );
	$class    = 'site-header__search site-header__search--' . $modifier;
	$query    = get_search_query();
	$icon     = '';
	$path     = GHAHGHAH_THEME_DIR . '/assets/icons/header/search.svg';
	if ( is_readable( $path ) ) {
		$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( is_string( $raw ) && '' !== $raw ) {
			$icon = (string) preg_replace(
				'/<svg\b/i',
				'<svg class="site-header__search-glyph" aria-hidden="true" focusable="false"',
				$raw,
				1
			);
		}
	}
	?>
	<form
		class="<?php echo esc_attr( $class ); ?>"
		role="search"
		method="get"
		action="<?php echo esc_url( home_url( '/' ) ); ?>"
		data-ghahghah-live-search
	>
		<label class="site-header__search-field">
			<span class="screen-reader-text"><?php esc_html_e( 'جستجو در سایت', 'ghahghah' ); ?></span>
			<span class="site-header__search-icon" aria-hidden="true">
				<?php
				if ( '' !== $icon ) {
					echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme SVG asset.
				}
				?>
			</span>
			<input
				type="search"
				class="site-header__search-input"
				name="s"
				value="<?php echo esc_attr( $query ); ?>"
				placeholder="<?php esc_attr_e( 'جستجوی محصول و مقاله...', 'ghahghah' ); ?>"
				autocomplete="off"
				enterkeyhint="search"
				data-ghahghah-live-search-input
				aria-autocomplete="list"
				aria-controls="ghahghah-live-search-<?php echo esc_attr( $modifier ); ?>"
				aria-expanded="false"
			/>
		</label>
		<button type="submit" class="site-header__search-submit">
			<span class="screen-reader-text"><?php esc_html_e( 'جستجو', 'ghahghah' ); ?></span>
			<span aria-hidden="true"><?php esc_html_e( 'جستجو', 'ghahghah' ); ?></span>
		</button>
		<div
			id="ghahghah-live-search-<?php echo esc_attr( $modifier ); ?>"
			class="site-header__search-results"
			data-ghahghah-live-search-results
			hidden
		></div>
	</form>
	<?php
}

/**
 * Human-readable label for a registered menu location.
 *
 * @param string $location Theme location slug.
 */
function ghahghah_nav_label( string $location ): string {
	$labels = array(
		'primary'         => __( 'منوی اصلی', 'ghahghah' ),
		'footer'          => __( 'دسترسی سریع', 'ghahghah' ),
		'footer_business' => __( 'همکاری', 'ghahghah' ),
		'legal'           => __( 'منوی حقوقی', 'ghahghah' ),
		'mobile_bottom'   => __( 'ناوبری پایین موبایل', 'ghahghah' ),
	);

	return $labels[ $location ] ?? __( 'ناوبری', 'ghahghah' );
}
