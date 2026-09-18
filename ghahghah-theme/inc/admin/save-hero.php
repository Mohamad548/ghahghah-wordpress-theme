<?php
/**
 * Persist homepage banner slider settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save slider settings from the theme config screen.
 */
function ghahghah_save_hero_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_hero_settings', 'ghahghah_hero_nonce' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by dedicated helpers.
	$desktop_ids = wp_unslash( $_POST['ghahghah_hero_slide_desktop'] ?? array() );
	$mobile_ids  = wp_unslash( $_POST['ghahghah_hero_slide_mobile'] ?? array() );
	$links       = wp_unslash( $_POST['ghahghah_hero_slide_link'] ?? array() );
	$alts        = wp_unslash( $_POST['ghahghah_hero_slide_alt'] ?? array() );
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	set_theme_mod( 'ghahghah_hero_enabled', ! empty( $_POST['ghahghah_hero_enabled'] ) );
	set_theme_mod( 'ghahghah_hero_interval', ghahghah_sanitize_hero_interval( wp_unslash( $_POST['ghahghah_hero_interval'] ?? GHAHGHAH_HERO_INTERVAL_DEFAULT ) ) );
	set_theme_mod( 'ghahghah_hero_slides', ghahghah_sanitize_hero_slides_from_post( $desktop_ids, $mobile_ids, $links, $alts ) );
	set_theme_mod( 'ghahghah_hero_last_saved', time() );

	ghahghah_redirect_config_tab( 'hero' );
}
add_action( 'admin_post_ghahghah_save_hero_settings', 'ghahghah_save_hero_settings' );
