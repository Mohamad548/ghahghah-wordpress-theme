<?php
/**
 * Manual sync / bootstrap handlers for theme media panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle manual theme media sync from config panel.
 */
function ghahghah_handle_sync_theme_media(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_sync_theme_media', 'ghahghah_theme_media_nonce' );

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$stats = ghahghah_sync_theme_media_library( true, true );
	update_option( 'ghahghah_theme_media_sync_version', GHAHGHAH_THEME_VERSION, false );
	delete_option( 'ghahghah_theme_media_sync_pending' );
	set_transient( 'ghahghah_theme_media_sync_stats', $stats, MINUTE_IN_SECONDS );
	set_theme_mod( 'ghahghah_theme_media_last_saved', time() );

	ghahghah_redirect_config_tab( 'theme-media' );
}
add_action( 'admin_post_ghahghah_sync_theme_media', 'ghahghah_handle_sync_theme_media' );

/**
 * Handle one-click live site bootstrap.
 */
function ghahghah_handle_bootstrap_site(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_bootstrap_site', 'ghahghah_bootstrap_nonce' );

	if ( function_exists( 'ghahghah_ensure_bundled_core_plugin' ) ) {
		ghahghah_ensure_bundled_core_plugin();
	}

	require_once GHAHGHAH_THEME_DIR . '/inc/catalog/import-products.php';
	require_once GHAHGHAH_THEME_DIR . '/inc/catalog/setup-menus.php';
	require_once GHAHGHAH_THEME_DIR . '/inc/catalog/bootstrap-site.php';

	$result = ghahghah_bootstrap_site();
	set_transient( 'ghahghah_bootstrap_result', $result, 5 * MINUTE_IN_SECONDS );
	set_theme_mod( 'ghahghah_theme_media_last_saved', time() );

	ghahghah_redirect_config_tab( 'theme-media' );
}
add_action( 'admin_post_ghahghah_bootstrap_site', 'ghahghah_handle_bootstrap_site' );
