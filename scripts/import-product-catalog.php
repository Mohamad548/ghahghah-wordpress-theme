<?php
/**
 * Idempotent import of Ghahghah product drafts from design-sources/raw-products.
 *
 * Usage (wp-env):
 *   npx @wordpress/env run cli wp eval-file wp-content/themes/ghahghah-theme/../../scripts/import-product-catalog.php
 *
 * Prefer mounting via absolute path from repo:
 *   wp eval-file /path/to/scripts/import-product-catalog.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Run via WP-CLI eval-file inside WordPress.\n" );
	exit( 1 );
}

if ( ! post_type_exists( 'ghahghah_product' ) ) {
	WP_CLI::error( 'Post type ghahghah_product is not registered. Activate Ghahghah Core.' );
}

$repo_candidates = array(
	dirname( __DIR__ ),
	dirname( __DIR__, 2 ),
);

// When copied into container, allow GHAHGHAH_REPO_ROOT env or theme-relative climb.
$theme_dir = get_template_directory();
$repo_root = getenv( 'GHAHGHAH_REPO_ROOT' );
if ( ! is_string( $repo_root ) || '' === $repo_root ) {
	// Theme lives at <repo>/ghahghah-theme — try parent of theme if scripts are sibling.
	$maybe = dirname( $theme_dir );
	if ( is_readable( $maybe . '/scripts/catalog/products.json' ) ) {
		$repo_root = $maybe;
	} elseif ( is_readable( dirname( __DIR__ ) . '/catalog/products.json' ) ) {
		$repo_root = dirname( __DIR__, 2 );
	} else {
		$repo_root = dirname( __DIR__, 2 );
	}
}

$manifest_path = $repo_root . '/scripts/catalog/products.json';
$raw_dir       = $repo_root . '/design-sources/raw-products';

if ( ! is_readable( $manifest_path ) ) {
	// Fallback: file next to this script.
	$alt = dirname( __FILE__ ) . '/catalog/products.json';
	if ( is_readable( $alt ) ) {
		$manifest_path = $alt;
		$raw_dir       = dirname( __FILE__, 2 ) . '/design-sources/raw-products';
	}
}

if ( ! is_readable( $manifest_path ) ) {
	WP_CLI::error( 'Manifest not found: ' . $manifest_path );
}

$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
if ( ! is_array( $manifest ) || empty( $manifest['products'] ) || ! is_array( $manifest['products'] ) ) {
	WP_CLI::error( 'Invalid catalog manifest.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Find product by catalog key (any status).
 */
$find_product = static function ( string $key ): int {
	$q = new WP_Query(
		array(
			'post_type'              => 'ghahghah_product',
			'post_status'            => 'any',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => '_ghahghah_catalog_key',
			'meta_value'             => $key,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
};

/**
 * Find attachment by source relative path meta.
 */
$find_attachment = static function ( string $rel ): int {
	$q = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'meta_key'               => '_ghahghah_source_relpath',
			'meta_value'             => $rel,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
};

/**
 * Import a local file once.
 *
 * @return int Attachment ID or 0.
 */
$import_file = static function ( string $abs, string $rel ) use ( $find_attachment ): int {
	$existing = $find_attachment( $rel );
	if ( $existing > 0 ) {
		return $existing;
	}

	if ( ! is_readable( $abs ) ) {
		WP_CLI::warning( "Missing file: {$abs}" );
		return 0;
	}

	$tmp = wp_tempnam( basename( $abs ) );
	if ( ! $tmp || ! copy( $abs, $tmp ) ) {
		WP_CLI::warning( "Could not stage file: {$rel}" );
		return 0;
	}

	$file_array = array(
		'name'     => basename( $abs ),
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file_array, 0, null );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		WP_CLI::warning( 'Sideload failed for ' . $rel . ': ' . $id->get_error_message() );
		return 0;
	}

	update_post_meta( (int) $id, '_ghahghah_source_relpath', $rel );
	update_post_meta( (int) $id, '_ghahghah_source_sha256', hash_file( 'sha256', $abs ) );

	$alt = pathinfo( basename( $abs ), PATHINFO_FILENAME );
	update_post_meta( (int) $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );

	return (int) $id;
};

$report = array();

foreach ( $manifest['products'] as $product ) {
	if ( ! is_array( $product ) ) {
		continue;
	}
	$key   = sanitize_key( (string) ( $product['key'] ?? '' ) );
	$title = sanitize_text_field( (string) ( $product['title'] ?? '' ) );
	if ( '' === $key || '' === $title ) {
		continue;
	}

	$image_ids = array();
	$images    = isset( $product['images'] ) && is_array( $product['images'] ) ? $product['images'] : array();
	foreach ( $images as $file ) {
		$file = (string) $file;
		$rel  = 'design-sources/raw-products/' . $file;
		$abs  = $raw_dir . '/' . $file;
		$id   = $import_file( $abs, $rel );
		if ( $id > 0 ) {
			$image_ids[ $file ] = $id;
		}
	}

	$featured_file = (string) ( $product['featured'] ?? '' );
	$featured_id   = isset( $image_ids[ $featured_file ] ) ? (int) $image_ids[ $featured_file ] : 0;
	if ( $featured_id <= 0 && $image_ids ) {
		$featured_id = (int) reset( $image_ids );
	}

	$existing_id = $find_product( $key );
	if ( $existing_id > 0 ) {
		$status = get_post_status( $existing_id );
		// Do not overwrite title/status/content. Only attach featured image if missing.
		if ( $featured_id > 0 && ! has_post_thumbnail( $existing_id ) ) {
			set_post_thumbnail( $existing_id, $featured_id );
		}
		$report[] = array(
			'key'      => $key,
			'title'    => get_the_title( $existing_id ),
			'id'       => $existing_id,
			'status'   => $status,
			'action'   => 'skipped-existing',
			'featured' => (int) get_post_thumbnail_id( $existing_id ),
			'images'   => array_values( $image_ids ),
		);
		WP_CLI::log( "SKIP existing #{$existing_id} [{$key}] status={$status}" );
		continue;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'ghahghah_product',
			'post_status'  => 'draft',
			'post_title'   => $title,
			'post_content' => '',
			'post_excerpt' => '',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'Create failed for ' . $key . ': ' . $post_id->get_error_message() );
		continue;
	}

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_ghahghah_catalog_key', $key );
	update_post_meta( $post_id, '_ghahghah_catalog_image_ids', array_values( array_map( 'intval', $image_ids ) ) );

	if ( $featured_id > 0 ) {
		set_post_thumbnail( $post_id, $featured_id );
	}

	$report[] = array(
		'key'      => $key,
		'title'    => $title,
		'id'       => $post_id,
		'status'   => 'draft',
		'action'   => 'created',
		'featured' => $featured_id,
		'images'   => array_values( $image_ids ),
	);
	WP_CLI::success( "CREATED draft #{$post_id} [{$key}] {$title}" );
}

WP_CLI::log( '--- SUMMARY ---' );
WP_CLI::log( wp_json_encode( $report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
