<?php
/**
 * Homepage production steps settings and helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Soft cap for admin/storage safety (not a fixed design of four). */
const GHAHGHAH_STEPS_MAX = 12;

/**
 * Default theme mods for production steps (disabled until editor content exists).
 *
 * @return array<string, mixed>
 */
function ghahghah_steps_setting_defaults(): array {
	return array(
		'ghahghah_steps_enabled' => false,
		'ghahghah_steps_eyebrow' => '',
		'ghahghah_steps_title'   => '',
		'ghahghah_steps_text'    => '',
		'ghahghah_steps_items'   => array(),
	);
}

/**
 * Get a steps theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_steps_mod( string $key ) {
	$defaults = ghahghah_steps_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Sanitize a list of production steps from admin POST or stored value.
 *
 * @param mixed $raw Raw steps.
 * @return array<int, array{title: string, text: string}>
 */
function ghahghah_sanitize_steps_items( $raw ): array {
	if ( is_string( $raw ) ) {
		$decoded = json_decode( $raw, true );
		$raw     = is_array( $decoded ) ? $decoded : array();
	}

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$out = array();
	foreach ( $raw as $row ) {
		if ( count( $out ) >= GHAHGHAH_STEPS_MAX ) {
			break;
		}
		if ( ! is_array( $row ) ) {
			continue;
		}
		$title = ghahghah_sanitize_hero_text( $row['title'] ?? '', 80 );
		$text  = ghahghah_sanitize_hero_multiline( $row['text'] ?? '', 500 );
		if ( '' === $title && '' === $text ) {
			continue;
		}
		$out[] = array(
			'title' => $title,
			'text'  => $text,
		);
	}

	return $out;
}

/**
 * Sanitize steps posted as parallel title/text arrays (order preserved).
 *
 * @param mixed $titles Titles array.
 * @param mixed $texts  Texts array.
 * @return array<int, array{title: string, text: string}>
 */
function ghahghah_sanitize_steps_items_from_post( $titles, $texts ): array {
	$titles = is_array( $titles ) ? $titles : array();
	$texts  = is_array( $texts ) ? $texts : array();
	$keys   = array_unique( array_merge( array_keys( $titles ), array_keys( $texts ) ) );
	$rows   = array();

	foreach ( $keys as $key ) {
		$rows[] = array(
			'title' => $titles[ $key ] ?? '',
			'text'  => $texts[ $key ] ?? '',
		);
	}

	return ghahghah_sanitize_steps_items( $rows );
}

/**
 * Steps with non-empty title (numbers come from order).
 *
 * @return array<int, array{title: string, text: string}>
 */
function ghahghah_get_steps_items(): array {
	$items = ghahghah_get_steps_mod( 'ghahghah_steps_items' );
	$items = ghahghah_sanitize_steps_items( $items );
	$out   = array();

	foreach ( $items as $item ) {
		if ( '' === $item['title'] ) {
			continue;
		}
		$out[] = $item;
	}

	return $out;
}

/**
 * Zero-padded step index for display (FaNum font maps digits).
 *
 * @param int $index 1-based index.
 */
function ghahghah_format_step_number( int $index ): string {
	$index = max( 1, $index );
	return sprintf( '%02d', $index );
}

/**
 * Whether the production steps section should render.
 */
function ghahghah_should_render_steps(): bool {
	if ( ! (bool) ghahghah_get_steps_mod( 'ghahghah_steps_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}

	$steps = ghahghah_get_steps_items();
	if ( count( $steps ) < 1 ) {
		return false;
	}

	$title   = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_title' ) );
	$eyebrow = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_eyebrow' ) );
	$text    = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_text' ) );

	return '' !== $title || '' !== $eyebrow || '' !== $text || count( $steps ) > 0;
}
