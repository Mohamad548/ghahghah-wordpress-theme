<?php
/**
 * Inline SVG helpers for inquiry form icons.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed form icon basenames (no extension).
 *
 * @return array<int, string>
 */
function ghahghah_form_icon_keys(): array {
	return array(
		'alert-circle',
		'arrow-left',
		'bell',
		'box',
		'briefcase',
		'building',
		'chart-column',
		'check-circle',
		'check-square',
		'chevron-down',
		'chevron-left',
		'city',
		'clipboard',
		'clock',
		'close',
		'decor-splash',
		'document',
		'globe',
		'handshake',
		'hash',
		'home',
		'info',
		'info-circle',
		'link',
		'mail',
		'map',
		'map-pin',
		'menu',
		'message-circle',
		'message-question',
		'minus',
		'note',
		'package',
		'pencil',
		'phone',
		'phone-call',
		'plus',
		'request',
		'search',
		'send',
		'spinner',
		'store',
		'title-accent',
		'user',
		'users',
		'warning-triangle',
	);
}

/**
 * Absolute path to a form/request/faq icon SVG.
 */
function ghahghah_get_form_icon_path( string $name ): string {
	$key = sanitize_key( $name );
	if ( ! in_array( $key, ghahghah_form_icon_keys(), true ) ) {
		return '';
	}

	$dirs = array(
		GHAHGHAH_THEME_DIR . '/assets/icons/request/' . $key . '.svg',
		GHAHGHAH_THEME_DIR . '/assets/icons/forms/' . $key . '.svg',
		GHAHGHAH_THEME_DIR . '/assets/icons/faq/' . $key . '.svg',
		GHAHGHAH_THEME_DIR . '/assets/icons/contact/' . $key . '.svg',
		GHAHGHAH_THEME_DIR . '/assets/icons/bottom-nav/' . $key . '.svg',
	);
	foreach ( $dirs as $path ) {
		if ( is_readable( $path ) ) {
			return $path;
		}
	}

	return '';
}

/**
 * Echo an inline form icon SVG.
 *
 * @param string               $name  Icon key.
 * @param array<string, mixed> $args  Optional: class, modifiers (array of suffix strings).
 */
function ghahghah_the_form_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_form_icon_path( $name );
	if ( '' === $path || ! is_readable( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$classes = array( 'gg-form-icon', 'ghahghah-form-icon' );
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$classes[] = $args['class'];
	}
	if ( ! empty( $args['modifiers'] ) && is_array( $args['modifiers'] ) ) {
		foreach ( $args['modifiers'] as $mod ) {
			$mod = sanitize_html_class( (string) $mod );
			if ( '' !== $mod ) {
				$classes[] = 'gg-form-icon--' . $mod;
			}
		}
	}

	$class_attr = implode( ' ', array_unique( array_filter( $classes ) ) );

	$svg = preg_replace( '/\s(?:role|aria-label|aria-hidden|focusable|class)="[^"]*"/i', '', $svg ) ?? $svg;
	$svg = preg_replace( '/<title\b[^>]*>.*?<\/title>/is', '', $svg ) ?? $svg;
	$svg = preg_replace(
		'/<svg\b/i',
		'<svg class="' . esc_attr( $class_attr ) . '" aria-hidden="true" focusable="false"',
		$svg,
		1
	) ?? $svg;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local SVG with escaped attributes.
	echo $svg;
}
