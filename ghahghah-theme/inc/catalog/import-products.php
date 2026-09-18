<?php
/**
 * Import product catalog into ghahghah_product posts (idempotent).
 *
 * Callable from admin bootstrap or WP-CLI wrapper.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import / refresh the bundled product catalog.
 *
 * @return array{ok: bool, message: string, report: array<int, array<string, mixed>>}
 */
function ghahghah_import_product_catalog(): array {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'نوع نوشته محصول ثبت نشده است. افزونه Ghahghah Core را فعال کنید.', 'ghahghah' ),
			'report'  => array(),
		);
	}

	$manifest_path = GHAHGHAH_THEME_DIR . '/inc/catalog/products.json';
	$site_img_dir  = GHAHGHAH_THEME_DIR . '/assets/images/products/catalog';

	if ( ! is_readable( $manifest_path ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'فایل فهرست محصولات یافت نشد.', 'ghahghah' ),
			'report'  => array(),
		);
	}

	$manifest = json_decode( (string) file_get_contents( $manifest_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $manifest ) || empty( $manifest['products'] ) || ! is_array( $manifest['products'] ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'فهرست محصولات نامعتبر است.', 'ghahghah' ),
			'report'  => array(),
		);
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$find_product = static function ( string $key ): int {
		$q = new WP_Query(
			array(
				'post_type'      => 'ghahghah_product',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_ghahghah_catalog_key',
				'meta_value'     => $key,
			)
		);
		return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
	};

	$find_attachment = static function ( string $token ): int {
		if ( function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
			$id = ghahghah_find_theme_media_attachment( 'products/catalog/' . basename( str_replace( 'catalog/', '', $token ) ) );
			if ( $id > 0 ) {
				return $id;
			}
		}
		$q = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => '_ghahghah_catalog_asset',
				'meta_value'     => $token,
			)
		);
		return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
	};

	$import_site_image = static function ( string $filename ) use ( $site_img_dir, $find_attachment ): int {
		$token    = 'catalog/' . $filename;
		$existing = $find_attachment( $token );
		if ( $existing > 0 ) {
			return $existing;
		}

		$abs = $site_img_dir . '/' . $filename;
		if ( ! is_readable( $abs ) ) {
			return 0;
		}

		$tmp = wp_tempnam( $filename );
		if ( ! $tmp || ! copy( $abs, $tmp ) ) {
			if ( is_string( $tmp ) && '' !== $tmp ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
			return 0;
		}

		$id = media_handle_sideload(
			array(
				'name'     => $filename,
				'tmp_name' => $tmp,
			),
			0
		);

		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return 0;
		}

		$id = (int) $id;
		update_post_meta( $id, '_ghahghah_catalog_asset', $token );
		update_post_meta( $id, '_ghahghah_theme_asset', 'products/catalog/' . $filename );
		update_post_meta( $id, '_ghahghah_source_sha256', hash_file( 'sha256', $abs ) );
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ) );

		return $id;
	};

	$report  = array();
	$created = 0;
	$updated = 0;

	foreach ( $manifest['products'] as $product ) {
		if ( ! is_array( $product ) ) {
			continue;
		}

		$key     = sanitize_key( (string) ( $product['key'] ?? '' ) );
		$title   = sanitize_text_field( (string) ( $product['title'] ?? '' ) );
		$file    = (string) ( $product['featured_site'] ?? '' );
		$sources = isset( $product['source_images'] ) && is_array( $product['source_images'] )
			? array_map( 'strval', $product['source_images'] )
			: array();

		if ( '' === $key || '' === $title || '' === $file ) {
			continue;
		}

		$featured_id = $import_site_image( $file );
		$existing_id = $find_product( $key );

		if ( $existing_id > 0 ) {
			$action       = 'skipped-existing';
			$desired_slug = sanitize_title( $key );
			$current_slug = (string) get_post_field( 'post_name', $existing_id );
			if ( '' !== $desired_slug && $current_slug !== $desired_slug ) {
				wp_update_post(
					array(
						'ID'        => $existing_id,
						'post_name' => $desired_slug,
					)
				);
				$action = 'updated-slug';
			}
			if ( $featured_id > 0 && (int) get_post_thumbnail_id( $existing_id ) !== $featured_id ) {
				set_post_thumbnail( $existing_id, $featured_id );
				$action = 'updated-featured';
			}
			if ( get_the_title( $existing_id ) !== $title ) {
				wp_update_post(
					array(
						'ID'         => $existing_id,
						'post_title' => $title,
					)
				);
				$action = 'updated-title';
			}
			if ( 'skipped-existing' !== $action ) {
				++$updated;
			}
			$report[] = array(
				'key'    => $key,
				'id'     => $existing_id,
				'action' => $action,
			);
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'ghahghah_product',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $key,
				'post_content' => '',
				'post_excerpt' => '',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			$report[] = array(
				'key'    => $key,
				'action' => 'error',
				'error'  => $post_id->get_error_message(),
			);
			continue;
		}

		$post_id = (int) $post_id;
		update_post_meta( $post_id, '_ghahghah_catalog_key', $key );
		update_post_meta( $post_id, '_ghahghah_catalog_source_images', $sources );
		if ( $featured_id > 0 ) {
			set_post_thumbnail( $post_id, $featured_id );
		}
		++$created;
		$report[] = array(
			'key'    => $key,
			'id'     => $post_id,
			'action' => 'created',
		);
	}

	// Seed homepage featured / primary dropdown when unset.
	$featured = function_exists( 'ghahghah_sanitize_featured_product_ids' )
		? ghahghah_sanitize_featured_product_ids( get_theme_mod( 'ghahghah_featured_ids', array() ) )
		: array();
	if ( array() === $featured ) {
		$all = get_posts(
			array(
				'post_type'              => 'ghahghah_product',
				'post_status'            => 'publish',
				'posts_per_page'         => 24,
				'orderby'                => 'menu_order title',
				'order'                  => 'ASC',
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( is_array( $all ) && array() !== $all ) {
			$ids = array_map( 'absint', $all );
			set_theme_mod( 'ghahghah_featured_ids', $ids );
			set_theme_mod( 'ghahghah_featured_enabled', true );
		}
	}

	return array(
		'ok'      => true,
		'message' => sprintf(
			/* translators: 1: created count, 2: updated count */
			__( 'محصولات: %1$d ایجاد، %2$d به‌روزرسانی.', 'ghahghah' ),
			$created,
			$updated
		),
		'report'  => $report,
	);
}
