<?php
/**
 * Idempotent setup for the designer about page (محمد محمودی).
 *
 * Usage:
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-designer-page.php
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ghahghah_setup_designer_page' ) ) {
	require_once dirname( __DIR__ ) . '/designer-settings.php';
}

$result = ghahghah_setup_designer_page();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$cli_script = '';
	if ( isset( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) ) {
		$cli_script = (string) end( $_SERVER['argv'] );
	}
	if ( '' !== $cli_script && str_ends_with( str_replace( '\\', '/', $cli_script ), 'setup-designer-page.php' ) ) {
		if ( ! empty( $result['ok'] ) ) {
			WP_CLI::success( (string) ( $result['message'] ?? '' ) . ' id=' . (int) ( $result['page_id'] ?? 0 ) );
		} else {
			WP_CLI::warning( (string) ( $result['message'] ?? 'failed' ) );
		}
	}
}
