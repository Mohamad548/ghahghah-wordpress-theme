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
 */
function ghahghah_the_site_brand(): void {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$blog_name = get_bloginfo( 'name', 'display' );

	printf(
		'<a class="site-brand__link" href="%1$s" rel="home">%2$s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( $blog_name )
	);
}

/**
 * Render a navigation menu or an accessible empty fallback.
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
 * Human-readable label for a registered menu location.
 *
 * @param string $location Theme location slug.
 */
function ghahghah_nav_label( string $location ): string {
	$labels = array(
		'primary' => __( 'منوی اصلی', 'ghahghah' ),
		'footer'  => __( 'منوی پاورقی', 'ghahghah' ),
		'legal'   => __( 'منوی حقوقی', 'ghahghah' ),
	);

	return $labels[ $location ] ?? __( 'ناوبری', 'ghahghah' );
}
