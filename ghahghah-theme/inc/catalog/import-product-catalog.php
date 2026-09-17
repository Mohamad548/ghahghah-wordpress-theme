<?php
/**
 * Idempotent import of Ghahghah product drafts.
 *
 * Run inside wp-env:
 *   npx @wordpress/env run cli wp eval-file wp-content/themes/ghahghah/inc/catalog/import-product-catalog.php
 *
 * Theme folder name may be "ghahghah-theme"; adjust path accordingly.
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

if ( ! class_exists( 'WP_CLI' ) ) {
	echo "WP-CLI required.\n";
	exit( 1 );
}

if ( ! post_type_exists( 'ghahghah_product' ) ) {
	WP_CLI::error( 'ghahghah_product is not registered. Activate Ghahghah Core.' );
}

$catalog_dir = __DIR__;
$manifest_path = $catalog_dir . '/products.json';
$site_img_dir  = get_template_directory() . '/assets/images/products/catalog';

if ( ! is_readable( $manifest_path ) ) {
	WP_CLI::error( 'Manifest missing: ' . $manifest_path );
}

$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
if ( ! is_array( $manifest ) || empty( $manifest['products'] ) ) {
	WP_CLI::error( 'Invalid manifest.' );
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
	$token = 'catalog/' . $filename;
	$existing = $find_attachment( $token );
	if ( $existing > 0 ) {
		return $existing;
	}

	$abs = $site_img_dir . '/' . $filename;
	if ( ! is_readable( $abs ) ) {
		WP_CLI::warning( "Site image missing: {$abs}" );
		return 0;
	}

	$tmp = wp_tempnam( $filename );
	if ( ! $tmp || ! copy( $abs, $tmp ) ) {
		WP_CLI::warning( "Could not stage {$filename}" );
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
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}

	update_post_meta( (int) $id, '_ghahghah_catalog_asset', $token );
	update_post_meta( (int) $id, '_ghahghah_source_sha256', hash_file( 'sha256', $abs ) );
	update_post_meta( (int) $id, '_wp_attachment_image_alt', sanitize_text_field( pathinfo( $filename, PATHINFO_FILENAME ) ) );

	return (int) $id;
};

$report = array();

foreach ( $manifest['products'] as $product ) {
	if ( ! is_array( $product ) ) {
		continue;
	}

	$key   = sanitize_key( (string) ( $product['key'] ?? '' ) );
	$title = sanitize_text_field( (string) ( $product['title'] ?? '' ) );
	$file  = (string) ( $product['featured_site'] ?? '' );
	$sources = isset( $product['source_images'] ) && is_array( $product['source_images'] )
		? array_map( 'strval', $product['source_images'] )
		: array();

	if ( '' === $key || '' === $title || '' === $file ) {
		continue;
	}

	$featured_id = $import_site_image( $file );
	$existing_id = $find_product( $key );

	if ( $existing_id > 0 ) {
		$status = (string) get_post_status( $existing_id );
		$action = 'skipped-existing';
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
		if ( $featured_id > 0 ) {
			$current_thumb = (int) get_post_thumbnail_id( $existing_id );
			if ( $current_thumb !== $featured_id ) {
				set_post_thumbnail( $existing_id, $featured_id );
				$action = 'updated-featured';
			}
		}
		// Keep published cheese title aligned with catalog.
		if ( 'corn-pellet-cheese' === $key && get_the_title( $existing_id ) !== $title ) {
			wp_update_post(
				array(
					'ID'         => $existing_id,
					'post_title' => $title,
				)
			);
			$action = 'updated-featured';
		}
		$report[] = array(
			'key'           => $key,
			'title'         => get_the_title( $existing_id ),
			'id'            => $existing_id,
			'status'        => $status,
			'action'        => $action,
			'featured_id'   => (int) get_post_thumbnail_id( $existing_id ),
			'source_images' => $sources,
		);
		WP_CLI::log( strtoupper( $action ) . " #{$existing_id} [{$key}] status={$status}" );
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
		WP_CLI::warning( $post_id->get_error_message() );
		continue;
	}

	$post_id = (int) $post_id;
	update_post_meta( $post_id, '_ghahghah_catalog_key', $key );
	update_post_meta( $post_id, '_ghahghah_catalog_source_images', $sources );

	if ( $featured_id > 0 ) {
		set_post_thumbnail( $post_id, $featured_id );
	}

	$report[] = array(
		'key'           => $key,
		'title'         => $title,
		'id'            => $post_id,
		'status'        => 'publish',
		'action'        => 'created',
		'featured_id'   => $featured_id,
		'source_images' => $sources,
	);
	WP_CLI::success( "CREATED publish #{$post_id} [{$key}]" );
}

WP_CLI::log( '--- SUMMARY ---' );
WP_CLI::log( (string) wp_json_encode( $report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
