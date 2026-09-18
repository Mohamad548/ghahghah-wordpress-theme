<?php
/**
 * Idempotent import of Ghahghah product drafts (WP-CLI wrapper).
 *
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/import-product-catalog.php
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

require_once get_template_directory() . '/inc/catalog/import-products.php';

$result = ghahghah_import_product_catalog();
if ( empty( $result['ok'] ) ) {
	WP_CLI::error( (string) ( $result['message'] ?? 'Import failed.' ) );
}

foreach ( (array) ( $result['report'] ?? array() ) as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	WP_CLI::log(
		strtoupper( (string) ( $row['action'] ?? '?' ) ) . ' #' . (int) ( $row['id'] ?? 0 ) . ' [' . (string) ( $row['key'] ?? '' ) . ']'
	);
}

WP_CLI::success( (string) ( $result['message'] ?? 'Done.' ) );
WP_CLI::log( (string) wp_json_encode( $result['report'] ?? array(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
