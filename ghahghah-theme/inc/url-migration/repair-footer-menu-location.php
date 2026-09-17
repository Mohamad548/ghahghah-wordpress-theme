<?php
/**
 * One-shot: if footer === mobile_bottom menu location, reattach footer to «دسترسی سریع».
 *
 * Not hooked on front-end init — run explicitly via WP-CLI:
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/url-migration/repair-footer-menu-location.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

if ( ! function_exists( 'ghahghah_ensure_footer_menu_location_distinct' ) ) {
	fwrite( STDERR, "ghahghah_ensure_footer_menu_location_distinct missing\n" );
	exit( 1 );
}

$before = get_nav_menu_locations();
$changed = ghahghah_ensure_footer_menu_location_distinct();
$after  = get_nav_menu_locations();

// Also sync products archive menu targets once (same as after_switch_theme).
$sync = function_exists( 'ghahghah_products_archive_after_nav_sync' )
	? ghahghah_products_archive_after_nav_sync()
	: array();

$report = array(
	'at'              => gmdate( 'c' ),
	'footer_changed'  => $changed,
	'locations_before'=> $before,
	'locations_after' => $after,
	'products_sync'   => $sync,
	'note'            => 'Subsequent admin menu location edits are not auto-reverted.',
);

$json = wp_json_encode( $report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::log( (string) $json );
} else {
	echo $json . "\n";
}
