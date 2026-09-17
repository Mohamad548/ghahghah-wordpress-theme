<?php
/**
 * Front-end performance: LCP preload, deferred CSS, speculation rules, core trim.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether current view has a clear image LCP candidate to preload.
 */
function ghahghah_perf_should_preload_lcp_image(): bool {
	if ( is_admin() ) {
		return false;
	}
	if ( is_front_page() && function_exists( 'ghahghah_should_render_hero' ) && ghahghah_should_render_hero() ) {
		return true;
	}
	if ( function_exists( 'ghahghah_is_products_archive' ) && ghahghah_is_products_archive() ) {
		return true;
	}
	if ( function_exists( 'ghahghah_is_blog_archive' ) && ghahghah_is_blog_archive() ) {
		return true;
	}
	if ( is_singular( 'ghahghah_product' ) ) {
		return true;
	}
	return false;
}

/**
 * Resolve LCP image URL (+ optional srcset/sizes) for preload.
 *
 * @return array{href: string, imagesrcset?: string, imagesizes?: string}|null
 */
function ghahghah_perf_get_lcp_image(): ?array {
	if ( is_front_page() && function_exists( 'ghahghah_get_hero_banner_slides' ) ) {
		$slides = ghahghah_get_hero_banner_slides();
		$first  = $slides[0] ?? null;
		if ( ! is_array( $first ) ) {
			return null;
		}
		$mobile  = is_array( $first['mobile'] ?? null ) ? $first['mobile'] : null;
		$desktop = is_array( $first['desktop'] ?? null ) ? $first['desktop'] : null;
		$href    = '';
		if ( is_array( $mobile ) && ! empty( $mobile['url'] ) ) {
			$href = (string) $mobile['url'];
		} elseif ( is_array( $desktop ) && ! empty( $desktop['url'] ) ) {
			$href = (string) $desktop['url'];
		}
		if ( '' === $href ) {
			return null;
		}
		$out = array( 'href' => $href );
		if ( is_array( $mobile ) && ! empty( $mobile['srcset'] ) ) {
			$out['imagesrcset'] = (string) $mobile['srcset'];
			$out['imagesizes']  = '100vw';
		}
		return $out;
	}

	if ( function_exists( 'ghahghah_is_products_archive' ) && ghahghah_is_products_archive() ) {
		$banner = function_exists( 'ghahghah_get_products_archive_custom_banner' )
			? ghahghah_get_products_archive_custom_banner()
			: null;
		if ( is_array( $banner ) && ! empty( $banner['desktop']['src'] ) ) {
			return array( 'href' => (string) $banner['desktop']['src'] );
		}
		return array(
			'href' => GHAHGHAH_THEME_URI . '/assets/images/products-archive/corn-hero-transparent.webp',
		);
	}

	if ( function_exists( 'ghahghah_is_blog_archive' ) && ghahghah_is_blog_archive() ) {
		$banner = function_exists( 'ghahghah_get_blog_archive_custom_banner' )
			? ghahghah_get_blog_archive_custom_banner()
			: null;
		if ( is_array( $banner ) && ! empty( $banner['desktop']['src'] ) ) {
			return array( 'href' => (string) $banner['desktop']['src'] );
		}
		return array(
			'href' => GHAHGHAH_THEME_URI . '/assets/images/blog-archive/corn-snack-hero-transparent.webp',
		);
	}

	if ( is_singular( 'ghahghah_product' ) ) {
		$post_id = (int) get_queried_object_id();
		if ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
			$url = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( is_string( $url ) && '' !== $url ) {
				return array( 'href' => $url );
			}
		}
	}

	return null;
}

/**
 * Preload LCP image early in <head>.
 */
function ghahghah_perf_preload_lcp_image(): void {
	if ( ! ghahghah_perf_should_preload_lcp_image() ) {
		return;
	}
	$image = ghahghah_perf_get_lcp_image();
	if ( null === $image || '' === $image['href'] ) {
		return;
	}

	$attrs = array(
		'rel'          => 'preload',
		'as'           => 'image',
		'href'         => $image['href'],
		'fetchpriority'=> 'high',
	);
	if ( ! empty( $image['imagesrcset'] ) ) {
		$attrs['imagesrcset'] = $image['imagesrcset'];
	}
	if ( ! empty( $image['imagesizes'] ) ) {
		$attrs['imagesizes'] = $image['imagesizes'];
	}

	$parts = array();
	foreach ( $attrs as $key => $value ) {
		$parts[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
	}
	echo '<link ' . implode( ' ', $parts ) . ' />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values escaped above.
}
add_action( 'wp_head', 'ghahghah_perf_preload_lcp_image', 2 );

/**
 * Below-fold homepage styles — load without blocking first paint.
 *
 * @return array<int, string>
 */
function ghahghah_perf_defer_style_handles(): array {
	if ( ! is_front_page() ) {
		return array();
	}
	return array(
		'ghahghah-featured',
		'ghahghah-factory',
		'ghahghah-steps',
		'ghahghah-collab',
		'ghahghah-forms',
		'ghahghah-articles',
	);
}

/**
 * Convert selected stylesheets to non-blocking print→all pattern.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 */
function ghahghah_perf_filter_style_loader_tag( string $html, string $handle ): string {
	$defer = ghahghah_perf_defer_style_handles();
	if ( ! in_array( $handle, $defer, true ) ) {
		return $html;
	}

	$html = preg_replace( "/\smedia=(['\"])all\\1/i", ' media="print" onload="this.media=\'all\'"', $html, 1 );
	if ( ! is_string( $html ) ) {
		return '';
	}
	if ( ! str_contains( $html, 'onload=' ) ) {
		$html = str_replace( ' />', ' media="print" onload="this.media=\'all\'" />', $html );
		$html = str_replace( '>', ' media="print" onload="this.media=\'all\'">', $html );
	}

	$href = '';
	if ( preg_match( '/href=(["\'])([^"\']+)\\1/i', $html, $m ) ) {
		$href = $m[2];
	}
	if ( '' !== $href ) {
		$html .= sprintf( '<noscript><link rel="stylesheet" href="%s" /></noscript>' . "\n", esc_url( $href ) );
	}

	return $html;
}
add_filter( 'style_loader_tag', 'ghahghah_perf_filter_style_loader_tag', 10, 2 );

/**
 * Collect high-traffic internal URLs for speculation / prefetch.
 *
 * @return array<int, string>
 */
function ghahghah_perf_nav_prefetch_urls(): array {
	$urls = array( home_url( '/' ) );

	if ( function_exists( 'ghahghah_get_products_archive_url' ) ) {
		$u = ghahghah_get_products_archive_url();
		if ( is_string( $u ) && '' !== $u ) {
			$urls[] = $u;
		}
	} elseif ( post_type_exists( 'ghahghah_product' ) ) {
		$u = get_post_type_archive_link( 'ghahghah_product' );
		if ( is_string( $u ) && '' !== $u ) {
			$urls[] = $u;
		}
	}

	if ( function_exists( 'ghahghah_get_request_page_url' ) ) {
		foreach ( array( 'wholesale', 'agency' ) as $which ) {
			$u = ghahghah_get_request_page_url( $which );
			if ( is_string( $u ) && '' !== $u ) {
				$urls[] = $u;
			}
		}
	}

	if ( function_exists( 'ghahghah_get_contact_page_url' ) ) {
		$u = ghahghah_get_contact_page_url();
		if ( is_string( $u ) && '' !== $u ) {
			$urls[] = $u;
		}
	}

	if ( function_exists( 'ghahghah_get_factory_page_url' ) ) {
		$u = ghahghah_get_factory_page_url();
		if ( is_string( $u ) && '' !== $u ) {
			$urls[] = $u;
		}
	}

	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page > 0 ) {
		$u = get_permalink( $posts_page );
		if ( is_string( $u ) && '' !== $u ) {
			$urls[] = $u;
		}
	}

	$unique = array();
	$current = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current = home_url( (string) wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
	foreach ( $urls as $url ) {
		$url = esc_url_raw( $url );
		if ( '' === $url ) {
			continue;
		}
		if ( untrailingslashit( $url ) === untrailingslashit( $current ) ) {
			continue;
		}
		$unique[ untrailingslashit( $url ) ] = $url;
	}

	return array_values( $unique );
}

/**
 * Speculation Rules + fallback link prefetch for primary destinations.
 */
function ghahghah_perf_output_nav_hints(): void {
	if ( is_admin() ) {
		return;
	}

	$urls = ghahghah_perf_nav_prefetch_urls();
	if ( array() === $urls ) {
		return;
	}

	// Cap to keep hints cheap.
	$urls = array_slice( $urls, 0, 6 );

	$rules = array(
		'prefetch' => array(
			array(
				'source'    => 'list',
				'urls'      => $urls,
				'eagerness' => 'moderate',
			),
		),
	);

	$json = wp_json_encode( $rules, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( is_string( $json ) && '' !== $json ) {
		echo '<script type="speculationrules">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	foreach ( $urls as $url ) {
		printf( '<link rel="prefetch" href="%s" as="document" />' . "\n", esc_url( $url ) );
	}
}
add_action( 'wp_head', 'ghahghah_perf_output_nav_hints', 8 );

/**
 * Trim default WP front-end noise that classic theme templates do not need.
 */
function ghahghah_perf_trim_wp_chrome(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	add_filter( 'emoji_svg_url', '__return_false' );

	add_action(
		'wp_enqueue_scripts',
		static function (): void {
			if ( is_admin() ) {
				return;
			}
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'classic-theme-styles' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_script( 'wp-embed' );
		},
		100
	);
}
add_action( 'after_setup_theme', 'ghahghah_perf_trim_wp_chrome' );
