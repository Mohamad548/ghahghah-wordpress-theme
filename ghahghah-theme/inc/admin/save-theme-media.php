<?php
/**
 * Manual sync of bundled theme images into Media Library.
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

	$stats = ghahghah_sync_theme_media_library( true, true );
	update_option( 'ghahghah_theme_media_sync_version', GHAHGHAH_THEME_VERSION, false );
	set_transient(
		'ghahghah_theme_media_sync_stats',
		$stats,
		MINUTE_IN_SECONDS
	);

	ghahghah_redirect_config_tab( 'theme-media' );
}
add_action( 'admin_post_ghahghah_sync_theme_media', 'ghahghah_handle_sync_theme_media' );
