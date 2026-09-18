<?php
/**
 * Homepage banner slider settings and helpers.
 *
 * Supports separate desktop + mobile banner images per slide.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHAHGHAH_HERO_SLIDES_MAX', 12 );
define( 'GHAHGHAH_HERO_INTERVAL_MIN', 3 );
define( 'GHAHGHAH_HERO_INTERVAL_MAX', 20 );
define( 'GHAHGHAH_HERO_INTERVAL_DEFAULT', 6 );
define( 'GHAHGHAH_HERO_BREAKPOINT', '48rem' );

/**
 * Default theme mods for the homepage slider.
 *
 * @return array<string, mixed>
 */
function ghahghah_hero_setting_defaults(): array {
	return array(
		'ghahghah_hero_enabled'  => true,
		'ghahghah_hero_interval' => GHAHGHAH_HERO_INTERVAL_DEFAULT,
		'ghahghah_hero_slides'   => array(),
	);
}

/**
 * Get a hero theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_hero_mod( string $key ) {
	$defaults = ghahghah_hero_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Sanitize short single-line text.
 *
 * @param mixed $value Raw.
 * @param int   $max   Max length.
 */
function ghahghah_sanitize_hero_text( $value, int $max = 120 ): string {
	$text = sanitize_text_field( (string) $value );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $max );
	}
	return substr( $text, 0, $max );
}

/**
 * Sanitize multiline text.
 *
 * @param mixed $value Raw.
 * @param int   $max   Max length.
 */
function ghahghah_sanitize_hero_multiline( $value, int $max = 400 ): string {
	$text = sanitize_textarea_field( (string) $value );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $max );
	}
	return substr( $text, 0, $max );
}

/**
 * Sanitize published page ID (0 allowed).
 *
 * @param mixed $value Raw.
 */
function ghahghah_sanitize_hero_page_id( $value ): int {
	$page_id = absint( $value );
	if ( $page_id <= 0 ) {
		return 0;
	}
	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type || ! is_post_publicly_viewable( $page ) ) {
		return 0;
	}
	return $page_id;
}

/**
 * Sanitize autoplay interval in seconds.
 *
 * @param mixed $value Raw.
 */
function ghahghah_sanitize_hero_interval( $value ): int {
	$seconds = absint( $value );
	if ( $seconds < GHAHGHAH_HERO_INTERVAL_MIN ) {
		return GHAHGHAH_HERO_INTERVAL_DEFAULT;
	}
	if ( $seconds > GHAHGHAH_HERO_INTERVAL_MAX ) {
		return GHAHGHAH_HERO_INTERVAL_MAX;
	}
	return $seconds;
}

/**
 * Public / production site origin for portable defaults (not local wp-env).
 */
function ghahghah_get_public_site_url(): string {
	$url = (string) apply_filters( 'ghahghah_public_site_url', 'https://ghahghaheh.com' );
	$url = esc_url_raw( untrailingslashit( $url ) );
	return '' !== $url ? $url : 'https://ghahghaheh.com';
}

/**
 * Known local / staging origins to strip when normalizing slide links.
 *
 * @return array<int, string>
 */
function ghahghah_get_local_site_origins(): array {
	$origins = array(
		untrailingslashit( home_url( '/' ) ),
		untrailingslashit( site_url( '/' ) ),
		'http://localhost:8888',
		'https://localhost:8888',
		'http://localhost:8898',
		'https://localhost:8898',
		'http://127.0.0.1:8888',
		'http://127.0.0.1:8898',
		'https://ghahghah.com',
		'http://ghahghah.com',
		'https://www.ghahghah.com',
		'https://ghahghah.ir',
		'http://ghahghah.ir',
	);

	$origins = array_values(
		array_unique(
			array_filter(
				array_map(
					static function ( string $origin ): string {
						return untrailingslashit( $origin );
					},
					$origins
				)
			)
		)
	);

	return $origins;
}

/**
 * Convert an absolute same-site / local URL into a site-relative path.
 *
 * @param string $url Absolute or relative URL.
 */
function ghahghah_hero_link_to_relative( string $url ): string {
	$url = trim( $url );
	if ( '' === $url ) {
		return '';
	}

	if ( '/' === $url[0] && 0 !== strpos( $url, '//' ) ) {
		return $url;
	}

	$candidates = array_merge(
		ghahghah_get_local_site_origins(),
		array( ghahghah_get_public_site_url() )
	);

	foreach ( $candidates as $origin ) {
		if ( '' === $origin ) {
			continue;
		}
		if ( 0 === stripos( $url, $origin ) ) {
			$path = substr( $url, strlen( $origin ) );
			if ( false === $path || '' === $path ) {
				return '/';
			}
			return '/' === $path[0] ? $path : '/' . $path;
		}
	}

	return $url;
}

/**
 * Resolve a stored slide link for front-end output (relative → current home).
 *
 * @param string $link Stored link.
 */
function ghahghah_resolve_hero_slide_link( string $link ): string {
	$link = trim( $link );
	if ( '' === $link ) {
		return '';
	}
	if ( '/' === $link[0] && 0 !== strpos( $link, '//' ) ) {
		return home_url( $link );
	}
	return $link;
}

/**
 * Format a stored slide link for admin fields (relative → public domain).
 *
 * @param string $link Stored link.
 */
function ghahghah_format_hero_slide_link_for_admin( string $link ): string {
	$link = trim( $link );
	if ( '' === $link ) {
		return '';
	}

	$relative = ghahghah_hero_link_to_relative( $link );
	if ( '' !== $relative && '/' === $relative[0] && 0 !== strpos( $relative, '//' ) ) {
		return ghahghah_get_public_site_url() . $relative;
	}

	return $link;
}

/**
 * Sanitize one slide link URL (empty allowed).
 * Same-site / localhost URLs are stored as relative paths for portability.
 *
 * @param mixed $value Raw.
 */
function ghahghah_sanitize_hero_slide_link( $value ): string {
	$raw = trim( (string) $value );
	if ( '' === $raw ) {
		return '';
	}

	$relative = ghahghah_hero_link_to_relative( $raw );
	if ( '' !== $relative && '/' === $relative[0] && 0 !== strpos( $relative, '//' ) ) {
		// Keep query string; escape path safely.
		$parts = wp_parse_url( $relative );
		if ( ! is_array( $parts ) ) {
			return '';
		}
		$path  = isset( $parts['path'] ) ? (string) $parts['path'] : '/';
		$query = isset( $parts['query'] ) ? '?' . $parts['query'] : '';
		$frag  = isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '';
		return $path . $query . $frag;
	}

	return esc_url_raw( $raw );
}

/**
 * Sanitize one attachment image ID.
 *
 * @param mixed $value Raw.
 */
function ghahghah_sanitize_hero_image_id( $value ): int {
	$id = function_exists( 'ghahghah_sanitize_attachment_id' )
		? ghahghah_sanitize_attachment_id( $value )
		: absint( $value );
	if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
		return 0;
	}
	return $id;
}

/**
 * Normalize stored slides (desktop + mobile images).
 *
 * @param mixed $raw Raw theme mod.
 * @return array<int, array{desktop_id: int, mobile_id: int, link: string, alt: string}>
 */
function ghahghah_sanitize_hero_slides( $raw ): array {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$slides = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		// Backward compat: older single image_id → both slots.
		$legacy = ghahghah_sanitize_hero_image_id( $row['image_id'] ?? 0 );
		$desktop_id = ghahghah_sanitize_hero_image_id( $row['desktop_id'] ?? 0 );
		$mobile_id  = ghahghah_sanitize_hero_image_id( $row['mobile_id'] ?? 0 );
		if ( $desktop_id <= 0 && $legacy > 0 ) {
			$desktop_id = $legacy;
		}
		if ( $mobile_id <= 0 && $legacy > 0 ) {
			$mobile_id = $legacy;
		}
		if ( $desktop_id <= 0 && $mobile_id <= 0 ) {
			continue;
		}

		$slides[] = array(
			'desktop_id' => $desktop_id,
			'mobile_id'  => $mobile_id,
			'link'       => ghahghah_sanitize_hero_slide_link( $row['link'] ?? '' ),
			'alt'        => ghahghah_sanitize_hero_text( $row['alt'] ?? '', 160 ),
		);

		if ( count( $slides ) >= GHAHGHAH_HERO_SLIDES_MAX ) {
			break;
		}
	}

	return $slides;
}

/**
 * Build slides from admin POST arrays.
 *
 * @param mixed $desktop_ids Desktop attachment IDs.
 * @param mixed $mobile_ids  Mobile attachment IDs.
 * @param mixed $links       URLs.
 * @param mixed $alts        Alt texts.
 * @return array<int, array{desktop_id: int, mobile_id: int, link: string, alt: string}>
 */
function ghahghah_sanitize_hero_slides_from_post( $desktop_ids, $mobile_ids, $links, $alts ): array {
	$desktop_ids = is_array( $desktop_ids ) ? $desktop_ids : array();
	$mobile_ids  = is_array( $mobile_ids ) ? $mobile_ids : array();
	$links       = is_array( $links ) ? $links : array();
	$alts        = is_array( $alts ) ? $alts : array();
	$count       = max( count( $desktop_ids ), count( $mobile_ids ), count( $links ), count( $alts ) );
	$rows        = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$rows[] = array(
			'desktop_id' => $desktop_ids[ $i ] ?? 0,
			'mobile_id'  => $mobile_ids[ $i ] ?? 0,
			'link'       => $links[ $i ] ?? '',
			'alt'        => $alts[ $i ] ?? '',
		);
	}

	return ghahghah_sanitize_hero_slides( $rows );
}

/**
 * Bundled default flavor banners (theme assets).
 * Mobile + desktop filenames may differ.
 *
 * @return array<int, array{mobile: string, desktop: string, alt: string, flavor: string}>
 */
function ghahghah_hero_bundled_banners(): array {
	return array(
		array(
			'mobile'  => '01-cheese-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_cheese_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم پنیری قهقهه', 'ghahghah' ),
			'flavor'  => 'cheese',
		),
		array(
			'mobile'  => '02-parsley-onion-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_parsley_onion_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم پیاز جعفری قهقهه', 'ghahghah' ),
			'flavor'  => 'parsley_onion',
		),
		array(
			'mobile'  => '03-shallot-yogurt-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_shallot_yogurt_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم ماست موسیر قهقهه', 'ghahghah' ),
			'flavor'  => 'shallot_yogurt',
		),
		array(
			'mobile'  => '04-pizza-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_pizza_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم پیتزا قهقهه', 'ghahghah' ),
			'flavor'  => 'pizza',
		),
		array(
			'mobile'  => '05-vinegar-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_vinegar_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم سرکه‌ای قهقهه', 'ghahghah' ),
			'flavor'  => 'vinegar',
		),
		array(
			'mobile'  => '06-lemon-flavour-banner-v2.webp',
			'desktop' => 'ghahghah_lemon_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم لیمویی قهقهه', 'ghahghah' ),
			'flavor'  => 'lemon',
		),
		array(
			'mobile'  => '07-ketchup-flavour-banner.webp',
			'desktop' => 'ghahghah_ketchup_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم کچاپ قهقهه', 'ghahghah' ),
			'flavor'  => 'ketchup',
		),
		array(
			'mobile'  => '07-chicken-flavour-banner.webp',
			'desktop' => 'ghahghah_chicken_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم مرغ قهقهه', 'ghahghah' ),
			'flavor'  => 'chicken',
		),
		array(
			'mobile'  => '08-pepper-flavour-banner.webp',
			'desktop' => 'ghahghah_pepper_flavour_banner_optimized.webp',
			'alt'     => __( 'بنر طعم فلفلی قهقهه', 'ghahghah' ),
			'flavor'  => 'chili',
		),
	);
}

/**
 * Absolute path for a bundled banner file.
 *
 * @param string $slot mobile|desktop.
 * @param string $file Filename.
 */
function ghahghah_hero_bundled_banner_path( string $slot, string $file ): string {
	$slot = 'desktop' === $slot ? 'desktop' : 'mobile';
	$file = ltrim( $file, '/' );
	return GHAHGHAH_THEME_DIR . '/assets/images/hero/banners/' . $slot . '/' . $file;
}

/**
 * Public URL for a bundled banner file.
 *
 * @param string $slot mobile|desktop.
 * @param string $file Filename.
 */
function ghahghah_hero_bundled_banner_url( string $slot, string $file ): string {
	$slot = 'desktop' === $slot ? 'desktop' : 'mobile';
	$file = ltrim( $file, '/' );
	return GHAHGHAH_THEME_URI . '/assets/images/hero/banners/' . $slot . '/' . rawurlencode( $file );
}

/**
 * Resolve link for a bundled flavor banner.
 *
 * @param string $flavor_key Archive flavor chip key.
 */
function ghahghah_hero_bundled_banner_link( string $flavor_key ): string {
	if ( '' === $flavor_key ) {
		return '';
	}
	if ( function_exists( 'ghahghah_products_archive_flavor_url' ) ) {
		$url = ghahghah_products_archive_flavor_url( $flavor_key );
		return is_string( $url ) ? $url : '';
	}
	if ( function_exists( 'ghahghah_products_archive_url' ) ) {
		return ghahghah_products_archive_url();
	}
	return '';
}

/**
 * Image payload from attachment ID.
 *
 * @param int $attachment_id Attachment ID.
 * @return array{url: string, width: int, height: int, srcset: string, sizes: string}|null
 */
function ghahghah_hero_attachment_image_data( int $attachment_id ): ?array {
	if ( $attachment_id <= 0 ) {
		return null;
	}
	$src = wp_get_attachment_image_src( $attachment_id, 'full' );
	if ( ! is_array( $src ) || empty( $src[0] ) ) {
		return null;
	}
	$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
	if ( ! is_string( $srcset ) ) {
		$srcset = '';
	}
	return array(
		'url'    => (string) $src[0],
		'width'  => isset( $src[1] ) ? max( 1, (int) $src[1] ) : 1600,
		'height' => isset( $src[2] ) ? max( 1, (int) $src[2] ) : 900,
		'srcset' => $srcset,
		'sizes'  => '100vw',
	);
}

/**
 * Image payload from a local theme file.
 *
 * @param string $path Absolute path.
 * @param string $url  Public URL.
 * @return array{url: string, width: int, height: int, srcset: string, sizes: string}|null
 */
function ghahghah_hero_file_image_data( string $path, string $url ): ?array {
	if ( ! is_readable( $path ) || '' === $url ) {
		return null;
	}
	$size = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	return array(
		'url'    => $url,
		'width'  => is_array( $size ) && isset( $size[0] ) ? max( 1, (int) $size[0] ) : 1600,
		'height' => is_array( $size ) && isset( $size[1] ) ? max( 1, (int) $size[1] ) : 900,
		'srcset' => '',
		'sizes'  => '100vw',
	);
}

/**
 * Front-end slides from bundled theme banners (mobile + optional desktop).
 *
 * @return array<int, array{desktop: ?array, mobile: ?array, link: string, alt: string}>
 */
function ghahghah_get_hero_bundled_banner_slides(): array {
	$slides = array();
	foreach ( ghahghah_hero_bundled_banners() as $row ) {
		$mobile_file  = (string) ( $row['mobile'] ?? $row['file'] ?? '' );
		$desktop_file = (string) ( $row['desktop'] ?? $mobile_file );
		if ( '' === $mobile_file && '' === $desktop_file ) {
			continue;
		}

		$mobile_path  = '' !== $mobile_file ? ghahghah_hero_bundled_banner_path( 'mobile', $mobile_file ) : '';
		$desktop_path = '' !== $desktop_file ? ghahghah_hero_bundled_banner_path( 'desktop', $desktop_file ) : '';
		$mobile       = ( '' !== $mobile_path )
			? ghahghah_hero_file_image_data( $mobile_path, ghahghah_hero_bundled_banner_url( 'mobile', $mobile_file ) )
			: null;
		$desktop      = ( '' !== $desktop_path )
			? ghahghah_hero_file_image_data( $desktop_path, ghahghah_hero_bundled_banner_url( 'desktop', $desktop_file ) )
			: null;

		if ( null === $mobile && null === $desktop ) {
			continue;
		}
		if ( null === $mobile ) {
			$mobile = $desktop;
		}
		if ( null === $desktop ) {
			$desktop = $mobile;
		}

		$slides[] = array(
			'desktop' => $desktop,
			'mobile'  => $mobile,
			'link'    => ghahghah_hero_bundled_banner_link( (string) $row['flavor'] ),
			'alt'     => (string) $row['alt'],
		);

		if ( count( $slides ) >= GHAHGHAH_HERO_SLIDES_MAX ) {
			break;
		}
	}

	return $slides;
}

/**
 * Resolve front-end banner slides (admin uploads, else bundled defaults).
 *
 * @return array<int, array{desktop: ?array, mobile: ?array, link: string, alt: string}>
 */
function ghahghah_get_hero_banner_slides(): array {
	$stored = ghahghah_sanitize_hero_slides( ghahghah_get_hero_mod( 'ghahghah_hero_slides' ) );
	$slides = array();

	foreach ( $stored as $row ) {
		$desktop = ghahghah_hero_attachment_image_data( (int) $row['desktop_id'] );
		$mobile  = ghahghah_hero_attachment_image_data( (int) $row['mobile_id'] );
		if ( null === $desktop && null === $mobile ) {
			continue;
		}
		if ( null === $mobile ) {
			$mobile = $desktop;
		}
		if ( null === $desktop ) {
			$desktop = $mobile;
		}

		$alt = $row['alt'];
		if ( '' === $alt ) {
			$id = (int) ( $row['mobile_id'] ?: $row['desktop_id'] );
			$meta_alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
			$alt      = is_string( $meta_alt ) ? $meta_alt : '';
		}

		$slides[] = array(
			'desktop' => $desktop,
			'mobile'  => $mobile,
			'link'    => ghahghah_resolve_hero_slide_link( $row['link'] ),
			'alt'     => $alt,
		);
	}

	if ( array() !== $slides ) {
		return $slides;
	}

	return ghahghah_get_hero_bundled_banner_slides();
}

/**
 * Autoplay interval in milliseconds.
 */
function ghahghah_get_hero_interval_ms(): int {
	return ghahghah_sanitize_hero_interval( ghahghah_get_hero_mod( 'ghahghah_hero_interval' ) ) * 1000;
}

/**
 * Echo a simple chevron icon for slider nav.
 *
 * @param string $direction prev|next.
 */
function ghahghah_the_hero_nav_icon( string $direction = 'next' ): void {
	$class = 'prev' === $direction
		? 'ghahghah-hero__nav-icon ghahghah-hero__nav-icon--prev'
		: 'ghahghah-hero__nav-icon ghahghah-hero__nav-icon--next';
	printf(
		'<svg class="%1$s" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6"/></svg>',
		esc_attr( $class )
	);
}

/**
 * Force homepage slider to pick up a new bundled desktop banner pack.
 * Clears stored media-library slide IDs so front-end uses theme asset files.
 */
function ghahghah_maybe_refresh_hero_banner_pack(): void {
	$pack = 'flavour-optimized-1933x813-v1';
	if ( (string) get_option( 'ghahghah_hero_banner_pack', '' ) === $pack ) {
		return;
	}

	remove_theme_mod( 'ghahghah_hero_slides' );
	update_option( 'ghahghah_hero_banner_pack', $pack, false );

	// Allow media sync to re-import and re-bind slides on next admin/version sync.
	delete_option( 'ghahghah_theme_media_sync_version' );
}
add_action( 'init', 'ghahghah_maybe_refresh_hero_banner_pack', 5 );

/**
 * Whether the homepage banner slider should render.
 */
function ghahghah_should_render_hero(): bool {
	if ( ! (bool) ghahghah_get_hero_mod( 'ghahghah_hero_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	return count( ghahghah_get_hero_banner_slides() ) > 0;
}

/**
 * Body class: transparent desktop header over homepage hero.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function ghahghah_hero_overlay_body_class( array $classes ): array {
	if ( ghahghah_should_render_hero() ) {
		$classes[] = 'ghahghah-home-overlay';
	}
	return $classes;
}
add_filter( 'body_class', 'ghahghah_hero_overlay_body_class' );
