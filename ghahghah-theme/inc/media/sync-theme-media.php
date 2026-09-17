<?php
/**
 * Sync bundled theme images into the WordPress Media Library.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta key marking an attachment as a bundled theme asset.
 */
function ghahghah_theme_media_meta_key(): string {
	return '_ghahghah_theme_asset';
}

/**
 * Find an existing media-library attachment for a bundled theme asset token.
 *
 * @param string $token Path relative to assets/images/, e.g. brand/ghahghah-logo-desktop.webp
 */
function ghahghah_find_theme_media_attachment( string $token ): int {
	$token = ltrim( str_replace( '\\', '/', $token ), '/' );
	if ( '' === $token ) {
		return 0;
	}

	$queries = array(
		array(
			'meta_key'   => ghahghah_theme_media_meta_key(),
			'meta_value' => $token,
		),
	);

	if ( str_starts_with( $token, 'products/catalog/' ) ) {
		$queries[] = array(
			'meta_key'   => '_ghahghah_catalog_asset',
			'meta_value' => 'catalog/' . basename( $token ),
		);
	}

	foreach ( $queries as $query ) {
		$found = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => $query['meta_key'],
				'meta_value'     => $query['meta_value'],
			)
		);

		if ( ! empty( $found->posts[0] ) ) {
			return (int) $found->posts[0];
		}
	}

	return 0;
}

/**
 * Manifest of active bundled images used by the theme.
 *
 * @return array<int, array{path: string, alt: string, title: string, theme_mod: string}>
 */
function ghahghah_get_theme_media_manifest(): array {
	$entries = array();

	$add = static function ( string $path, string $alt = '', string $title = '', string $theme_mod = '' ) use ( &$entries ): void {
		$path = ltrim( str_replace( '\\', '/', $path ), '/' );
		if ( '' === $path || isset( $entries[ $path ] ) ) {
			return;
		}

		$entries[ $path ] = array(
			'path'      => $path,
			'alt'       => $alt,
			'title'     => '' !== $title ? $title : $alt,
			'theme_mod' => $theme_mod,
		);
	};

	if ( function_exists( 'ghahghah_hero_bundled_banners' ) ) {
		foreach ( ghahghah_hero_bundled_banners() as $row ) {
			$alt     = (string) ( $row['alt'] ?? '' );
			$mobile  = (string) ( $row['mobile'] ?? '' );
			$desktop = (string) ( $row['desktop'] ?? $mobile );
			if ( '' !== $mobile ) {
				$add( 'hero/banners/mobile/' . $mobile, $alt );
			}
			if ( '' !== $desktop ) {
				$add( 'hero/banners/desktop/' . $desktop, $alt );
			}
		}
	}

	if ( function_exists( 'ghahghah_archive_bundled_banner_files' ) ) {
		$blog_keys = function_exists( 'ghahghah_blog_archive_banner_mod_keys' )
			? ghahghah_blog_archive_banner_mod_keys()
			: array();
		$prod_keys = function_exists( 'ghahghah_products_archive_banner_mod_keys' )
			? ghahghah_products_archive_banner_mod_keys()
			: array();

		foreach ( ghahghah_archive_bundled_banner_files() as $scope => $files ) {
			$alt = 'blog' === $scope
				? __( 'بنر آرشیو مقالات قهقهه', 'ghahghah' )
				: __( 'بنر آرشیو محصولات قهقهه', 'ghahghah' );
			$dir = (string) ( $files['dir'] ?? '' );
			if ( '' !== $dir && ! empty( $files['desktop'] ) ) {
				$mod = 'blog' === $scope ? (string) ( $blog_keys['desktop'] ?? '' ) : (string) ( $prod_keys['desktop'] ?? '' );
				$add( $dir . '/' . (string) $files['desktop'], $alt, '', $mod );
			}
			if ( '' !== $dir && ! empty( $files['mobile'] ) ) {
				$mod = 'blog' === $scope ? (string) ( $blog_keys['mobile'] ?? '' ) : (string) ( $prod_keys['mobile'] ?? '' );
				$add( $dir . '/' . (string) $files['mobile'], $alt, '', $mod );
			}
		}
	}

	$add( 'brand/ghahghah-logo-desktop.webp', __( 'لوگوی دسکتاپ قهقهه', 'ghahghah' ), '', 'ghahghah_header_logo_desktop' );
	$add( 'brand/ghahghah-logo-mobile.webp', __( 'لوگوی موبایل قهقهه', 'ghahghah' ), '', 'ghahghah_header_logo_mobile' );
	$add( 'brand/ghahghah-site-icon-512.png', __( 'آیکون سایت قهقهه', 'ghahghah' ), '', 'ghahghah_header_favicon' );

	$add( 'factory/factory-hero.webp', __( 'تصویر صفحه کارخانه قهقهه', 'ghahghah' ), '', 'ghahghah_factory_page_hero_image_id' );
	$add( 'factory/product-pack-fallback.webp', __( 'بسته محصول پیش‌فرض کارخانه', 'ghahghah' ) );

	$add( 'wholesale/ghahghah_pizza_packshot_optimized.webp', __( 'تصویر صفحه خرید عمده', 'ghahghah' ), '', 'ghahghah_wholesale_image_id' );
	$add( 'agency/ghahghah_parsley_onion_pack_optimized.webp', __( 'تصویر صفحه نمایندگی', 'ghahghah' ) );
	$add( 'contact/corn-isolated-transparent-optimized.webp', __( 'تصویر تزئینی صفحه تماس', 'ghahghah' ) );

	$add( 'blog-archive/corn-snack-hero-transparent.png', __( 'تزئین بنر مقالات', 'ghahghah' ) );
	$add( 'blog-archive/real-snack-bowl-photo.jpg', __( 'عکس کاسه اسنک مقالات', 'ghahghah' ) );
	$add( 'blog-archive/real-snack-shape-photo.jpg', __( 'عکس اسنک مقالات', 'ghahghah' ) );
	$add( 'products-archive/corn-hero-transparent.png', __( 'تزئین بنر محصولات', 'ghahghah' ) );

	$single_article_files = array(
		'article-hero-banner-real-snack-optimized.webp'   => __( 'بنر مقاله تکی', 'ghahghah' ),
		'article-inline-hands-snack-corn-optimized.webp'  => __( 'تصویر درون متن مقاله', 'ghahghah' ),
		'article-inline-factory-line-real-snack-optimized.webp' => __( 'تصویر خط تولید در مقاله', 'ghahghah' ),
		'bowl-of-real-snacks-transparent-optimized.webp'  => __( 'تزئین کاسه اسنک مقاله', 'ghahghah' ),
		'corn-and-real-snacks-transparent-optimized.webp' => __( 'تزئین ذرت و اسنک مقاله', 'ghahghah' ),
		'decorative-snack-cluster-transparent-optimized.webp' => __( 'تزئین خوشه اسنک مقاله', 'ghahghah' ),
	);
	foreach ( $single_article_files as $file => $alt ) {
		$add( 'single-article/' . $file, $alt );
	}

	$add( 'request/wholesale-snacks.jpg', __( 'پیش‌نمایش عمده در پنل', 'ghahghah' ) );

	$catalog_dir = GHAHGHAH_THEME_DIR . '/assets/images/products/catalog';
	if ( is_dir( $catalog_dir ) ) {
		$patterns = array( '*.webp', '*.jpg', '*.jpeg', '*.png' );
		foreach ( $patterns as $pattern ) {
			foreach ( glob( $catalog_dir . '/' . $pattern ) ?: array() as $abs ) {
				if ( ! is_string( $abs ) || ! is_file( $abs ) ) {
					continue;
				}
				$file = basename( $abs );
				$add(
					'products/catalog/' . $file,
					sprintf(
						/* translators: %s: product image filename */
						__( 'تصویر محصول: %s', 'ghahghah' ),
						$file
					)
				);
			}
		}
	}

	return array_values( $entries );
}

/**
 * Import one bundled theme image into the Media Library.
 *
 * @param array{path: string, alt?: string, title?: string, theme_mod?: string} $entry Manifest row.
 */
function ghahghah_import_theme_media_entry( array $entry ): int {
	$path = ltrim( str_replace( '\\', '/', (string) ( $entry['path'] ?? '' ) ), '/' );
	if ( '' === $path ) {
		return 0;
	}

	$existing = ghahghah_find_theme_media_attachment( $path );
	if ( $existing > 0 ) {
		return $existing;
	}

	$abs = GHAHGHAH_THEME_DIR . '/assets/images/' . $path;
	if ( ! is_readable( $abs ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = basename( $path );
	$tmp      = wp_tempnam( $filename );
	if ( ! $tmp || ! copy( $abs, $tmp ) ) {
		if ( is_string( $tmp ) && '' !== $tmp ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		return 0;
	}

	$title = sanitize_text_field( (string) ( $entry['title'] ?? $entry['alt'] ?? $filename ) );
	$alt   = sanitize_text_field( (string) ( $entry['alt'] ?? '' ) );

	$id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	$id = (int) $id;
	update_post_meta( $id, ghahghah_theme_media_meta_key(), $path );
	update_post_meta( $id, '_ghahghah_source_sha256', hash_file( 'sha256', $abs ) );

	if ( str_starts_with( $path, 'products/catalog/' ) ) {
		update_post_meta( $id, '_ghahghah_catalog_asset', 'catalog/' . $filename );
	}

	if ( '' !== $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}

	return $id;
}

/**
 * Apply default theme mods from imported bundled assets when unset.
 *
 * @param array<string, int> $imported   Map of asset path => attachment ID.
 * @param bool               $force_hero Rebuild homepage hero slides even if already set.
 */
function ghahghah_apply_theme_media_defaults( array $imported, bool $force_hero = false ): void {
	foreach ( ghahghah_get_theme_media_manifest() as $entry ) {
		$path = (string) ( $entry['path'] ?? '' );
		$mod  = (string) ( $entry['theme_mod'] ?? '' );
		if ( '' === $mod || ! isset( $imported[ $path ] ) || $imported[ $path ] <= 0 ) {
			continue;
		}
		if ( absint( get_theme_mod( $mod, 0 ) ) > 0 ) {
			continue;
		}
		set_theme_mod( $mod, (int) $imported[ $path ] );
	}

	if ( ! function_exists( 'ghahghah_sanitize_hero_slides' ) || ! function_exists( 'ghahghah_hero_bundled_banners' ) ) {
		return;
	}

	$stored = ghahghah_sanitize_hero_slides( get_theme_mod( 'ghahghah_hero_slides', array() ) );
	if ( array() !== $stored && ! $force_hero ) {
		return;
	}

	$slides = array();
	foreach ( ghahghah_hero_bundled_banners() as $row ) {
		$mobile_path  = 'hero/banners/mobile/' . (string) ( $row['mobile'] ?? '' );
		$desktop_path = 'hero/banners/desktop/' . (string) ( $row['desktop'] ?? ( $row['mobile'] ?? '' ) );
		$mobile_id    = isset( $imported[ $mobile_path ] ) ? (int) $imported[ $mobile_path ] : 0;
		$desktop_id   = isset( $imported[ $desktop_path ] ) ? (int) $imported[ $desktop_path ] : 0;
		if ( $mobile_id <= 0 && $desktop_id <= 0 ) {
			continue;
		}

		$slides[] = array(
			'desktop_id' => $desktop_id > 0 ? $desktop_id : $mobile_id,
			'mobile_id'  => $mobile_id > 0 ? $mobile_id : $desktop_id,
			'link'       => function_exists( 'ghahghah_hero_bundled_banner_link' )
				? ghahghah_hero_bundled_banner_link( (string) ( $row['flavor'] ?? '' ) )
				: '',
			'alt'        => (string) ( $row['alt'] ?? '' ),
		);
	}

	if ( array() !== $slides ) {
		set_theme_mod( 'ghahghah_hero_slides', ghahghah_sanitize_hero_slides( $slides ) );
	}
}

/**
 * Sync all active bundled theme images into the Media Library.
 *
 * @param bool $apply_defaults Whether to populate empty theme mods from imports.
 * @param bool $force_hero     Rebuild homepage hero slides from bundled banners.
 * @return array{created: int, reused: int, missing: int, total: int, applied_mods: int}
 */
function ghahghah_sync_theme_media_library( bool $apply_defaults = true, bool $force_hero = false ): array {
	$stats = array(
		'created'      => 0,
		'reused'       => 0,
		'missing'      => 0,
		'total'        => 0,
		'applied_mods' => 0,
	);

	$imported = array();
	foreach ( ghahghah_get_theme_media_manifest() as $entry ) {
		++$stats['total'];
		$path = (string) ( $entry['path'] ?? '' );
		if ( '' === $path ) {
			++$stats['missing'];
			continue;
		}

		$before = ghahghah_find_theme_media_attachment( $path );
		$id     = ghahghah_import_theme_media_entry( $entry );
		if ( $id <= 0 ) {
			++$stats['missing'];
			continue;
		}

		$imported[ $path ] = $id;
		if ( $before > 0 ) {
			++$stats['reused'];
		} else {
			++$stats['created'];
		}
	}

	if ( $apply_defaults ) {
		$before_mods = array(
			absint( get_theme_mod( 'ghahghah_header_logo_desktop', 0 ) ),
			absint( get_theme_mod( 'ghahghah_header_logo_mobile', 0 ) ),
			absint( get_theme_mod( 'ghahghah_header_favicon', 0 ) ),
		);
		ghahghah_apply_theme_media_defaults( $imported, $force_hero );
		$after_mods = array(
			absint( get_theme_mod( 'ghahghah_header_logo_desktop', 0 ) ),
			absint( get_theme_mod( 'ghahghah_header_logo_mobile', 0 ) ),
			absint( get_theme_mod( 'ghahghah_header_favicon', 0 ) ),
		);
		foreach ( $before_mods as $index => $value ) {
			if ( $value <= 0 && $after_mods[ $index ] > 0 ) {
				++$stats['applied_mods'];
			}
		}
	}

	return $stats;
}

/**
 * Run sync once per theme version (activation or admin update).
 * Forces hero slide refresh so new bundled desktop banners replace old media IDs.
 */
function ghahghah_maybe_sync_theme_media_library(): void {
	$stored = (string) get_option( 'ghahghah_theme_media_sync_version', '' );
	if ( $stored === GHAHGHAH_THEME_VERSION ) {
		return;
	}

	ghahghah_sync_theme_media_library( true, true );
	update_option( 'ghahghah_theme_media_sync_version', GHAHGHAH_THEME_VERSION, false );
}
add_action( 'after_switch_theme', 'ghahghah_maybe_sync_theme_media_library' );
add_action( 'admin_init', 'ghahghah_maybe_sync_theme_media_library' );
