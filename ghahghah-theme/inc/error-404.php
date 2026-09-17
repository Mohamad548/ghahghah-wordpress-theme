<?php
/**
 * 404 page helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed 404 SVG basenames (no extension).
 *
 * @return array<int, string>
 */
function ghahghah_404_icon_keys(): array {
	return array(
		'accent-rays',
		'bg-blob-soft',
		'bg-blob-top-left',
		'icon-chevron-left',
	);
}

/**
 * Absolute path to a 404 SVG asset.
 *
 * @param string $name Icon key.
 */
function ghahghah_get_404_icon_path( string $name ): string {
	$key = strtolower( $name );
	$key = preg_replace( '/[^a-z0-9\-]/', '', $key ) ?? '';
	if ( ! in_array( $key, ghahghah_404_icon_keys(), true ) ) {
		return '';
	}

	$path = GHAHGHAH_THEME_DIR . '/assets/icons/404/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo a trusted inline 404 SVG.
 *
 * @param string               $name Icon key.
 * @param array<string, mixed> $args Optional: class.
 */
function ghahghah_the_404_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_404_icon_path( $name );
	if ( '' === $path ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$classes = array( 'gg-404-icon' );
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$classes[] = $args['class'];
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

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local SVG.
	echo $svg;
}

/**
 * URL for the products CTA on the 404 page.
 */
function ghahghah_404_products_url(): string {
	$archive = get_post_type_archive_link( 'ghahghah_product' );
	if ( is_string( $archive ) && '' !== $archive ) {
		return $archive;
	}

	return home_url( '/products/' );
}

/**
 * Format 404 H1 with red accent on «اوه!».
 *
 * @param string $title Plain title text.
 */
function ghahghah_format_404_title( string $title ): string {
	$escaped = esc_html( $title );
	$out     = preg_replace(
		'/(اوه[!！]?)/u',
		'<span class="ghahghah-404__title-accent">$1</span>',
		$escaped,
		1
	);

	return is_string( $out ) ? $out : $escaped;
}
