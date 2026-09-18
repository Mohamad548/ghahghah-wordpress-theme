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

/**
 * Default theme mods for production steps.
 *
 * @return array<string, mixed>
 */
function ghahghah_steps_setting_defaults(): array {
	return array(
		'ghahghah_steps_enabled'   => false,
		'ghahghah_steps_image'     => 0,
		'ghahghah_steps_image_alt' => __( 'اینفوگرافیک مراحل تولید محصول قهقهه', 'ghahghah' ),
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
 * Bundled production-steps image URL (theme fallback).
 */
function ghahghah_get_steps_bundled_image_url(): string {
	$path = GHAHGHAH_THEME_DIR . '/assets/images/steps/production-steps.webp';
	if ( ! is_readable( $path ) ) {
		return '';
	}
	return GHAHGHAH_THEME_URI . '/assets/images/steps/production-steps.webp';
}

/**
 * Resolved production-steps image (Media Library preferred, then bundled asset).
 *
 * @return array{id: int, url: string, width: int, height: int, alt: string, srcset: string, sizes: string}|null
 */
function ghahghah_get_steps_image(): ?array {
	$id  = absint( ghahghah_get_steps_mod( 'ghahghah_steps_image' ) );
	$alt = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_image_alt' ) );
	if ( '' === $alt ) {
		$alt = (string) ghahghah_steps_setting_defaults()['ghahghah_steps_image_alt'];
	}

	if ( $id > 0 && wp_attachment_is_image( $id ) ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = wp_get_attachment_image_url( $id, 'large' );
		}
		if ( is_string( $url ) && '' !== $url ) {
			$meta   = wp_get_attachment_metadata( $id );
			$width  = isset( $meta['width'] ) ? absint( $meta['width'] ) : 1933;
			$height = isset( $meta['height'] ) ? absint( $meta['height'] ) : 814;

			$meta_alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			if ( '' === trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_image_alt' ) ) && '' !== $meta_alt ) {
				$alt = $meta_alt;
			}

			$srcset = wp_get_attachment_image_srcset( $id, 'full' );
			if ( ! is_string( $srcset ) ) {
				$srcset = '';
			}

			return array(
				'id'     => $id,
				'url'    => $url,
				'width'  => max( 1, $width ),
				'height' => max( 1, $height ),
				'alt'    => $alt,
				'srcset' => $srcset,
				'sizes'  => '(max-width: 75rem) 100vw, 75rem',
			);
		}
	}

	$bundled = ghahghah_get_steps_bundled_image_url();
	if ( '' === $bundled ) {
		return null;
	}

	return array(
		'id'     => 0,
		'url'    => $bundled,
		'width'  => 1933,
		'height' => 814,
		'alt'    => $alt,
		'srcset' => '',
		'sizes'  => '(max-width: 75rem) 100vw, 75rem',
	);
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

	return null !== ghahghah_get_steps_image();
}
