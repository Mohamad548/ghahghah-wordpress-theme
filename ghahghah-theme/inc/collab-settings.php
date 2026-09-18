<?php
/**
 * Homepage wholesale / agency collab banner settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default theme mods for the collab banner section.
 *
 * @return array<string, mixed>
 */
function ghahghah_collab_setting_defaults(): array {
	return array(
		'ghahghah_collab_enabled'            => true,
		'ghahghah_collab_wholesale_image'    => 0,
		'ghahghah_collab_wholesale_image_alt' => __( 'بنر درخواست خرید عمده محصولات قهقهه', 'ghahghah' ),
		'ghahghah_collab_agency_image'       => 0,
		'ghahghah_collab_agency_image_alt'   => __( 'بنر درخواست نمایندگی قهقهه', 'ghahghah' ),
	);
}

/**
 * Get a collab theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_collab_mod( string $key ) {
	$defaults = ghahghah_collab_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Allowed collab path keys.
 *
 * @return array<int, string>
 */
function ghahghah_collab_form_keys(): array {
	return array( 'wholesale', 'agency' );
}

/**
 * Bundled collab banner URL.
 *
 * @param string $type wholesale|agency.
 */
function ghahghah_get_collab_bundled_image_url( string $type ): string {
	$file = 'wholesale' === $type ? 'wholesale-banner.webp' : 'agency-banner.webp';
	$path = GHAHGHAH_THEME_DIR . '/assets/images/collab/' . $file;
	if ( ! is_readable( $path ) ) {
		return '';
	}
	return GHAHGHAH_THEME_URI . '/assets/images/collab/' . $file;
}

/**
 * Resolve one collab banner image.
 *
 * @param string $type wholesale|agency.
 * @return array{id: int, url: string, width: int, height: int, alt: string, srcset: string, sizes: string}|null
 */
function ghahghah_get_collab_image( string $type ): ?array {
	if ( ! in_array( $type, ghahghah_collab_form_keys(), true ) ) {
		return null;
	}

	$id_key  = 'wholesale' === $type ? 'ghahghah_collab_wholesale_image' : 'ghahghah_collab_agency_image';
	$alt_key = 'wholesale' === $type ? 'ghahghah_collab_wholesale_image_alt' : 'ghahghah_collab_agency_image_alt';
	$id      = absint( ghahghah_get_collab_mod( $id_key ) );
	$alt     = trim( (string) ghahghah_get_collab_mod( $alt_key ) );
	if ( '' === $alt ) {
		$alt = (string) ghahghah_collab_setting_defaults()[ $alt_key ];
	}

	$fallback_w = 1933;
	$fallback_h = 814;

	if ( $id > 0 && wp_attachment_is_image( $id ) ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = wp_get_attachment_image_url( $id, 'large' );
		}
		if ( is_string( $url ) && '' !== $url ) {
			$meta   = wp_get_attachment_metadata( $id );
			$width  = isset( $meta['width'] ) ? absint( $meta['width'] ) : $fallback_w;
			$height = isset( $meta['height'] ) ? absint( $meta['height'] ) : $fallback_h;

			$meta_alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
			if ( '' === trim( (string) ghahghah_get_collab_mod( $alt_key ) ) && '' !== $meta_alt ) {
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
				'sizes'  => '(max-width: 47.99rem) 100vw, (max-width: 80rem) 48vw, 38rem',
			);
		}
	}

	$bundled = ghahghah_get_collab_bundled_image_url( $type );
	if ( '' === $bundled ) {
		return null;
	}

	return array(
		'id'     => 0,
		'url'    => $bundled,
		'width'  => $fallback_w,
		'height' => $fallback_h,
		'alt'    => $alt,
		'srcset' => '',
		'sizes'  => '(max-width: 47.99rem) 100vw, (max-width: 80rem) 48vw, 38rem',
	);
}

/**
 * Destination URL for a collab path (request page preferred).
 *
 * @param string $type wholesale|agency.
 */
function ghahghah_get_collab_banner_url( string $type ): string {
	if ( ! in_array( $type, ghahghah_collab_form_keys(), true ) ) {
		return '';
	}
	if ( function_exists( 'ghahghah_get_request_page_url' ) ) {
		$url = ghahghah_get_request_page_url( $type );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Banner cards ready for the front (image required; URL optional).
 *
 * @return array<int, array{type: string, image: array{id: int, url: string, width: int, height: int, alt: string, srcset: string, sizes: string}, url: string}>
 */
function ghahghah_get_collab_banners(): array {
	$out = array();
	foreach ( array( 'wholesale', 'agency' ) as $type ) {
		$image = ghahghah_get_collab_image( $type );
		if ( null === $image ) {
			continue;
		}
		$out[] = array(
			'type'  => $type,
			'image' => $image,
			'url'   => ghahghah_get_collab_banner_url( $type ),
		);
	}
	return $out;
}

/**
 * Whether the collab section should render.
 */
function ghahghah_should_render_collab(): bool {
	if ( ! (bool) ghahghah_get_collab_mod( 'ghahghah_collab_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	return count( ghahghah_get_collab_banners() ) > 0;
}
