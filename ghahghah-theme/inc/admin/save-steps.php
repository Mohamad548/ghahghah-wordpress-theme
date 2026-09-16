<?php
/**
 * Persist production steps settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save production steps from the theme config screen.
 */
function ghahghah_save_steps_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_steps_settings', 'ghahghah_steps_nonce' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by dedicated helpers.
	$titles = wp_unslash( $_POST['ghahghah_steps_title_item'] ?? array() );
	$texts  = wp_unslash( $_POST['ghahghah_steps_text_item'] ?? array() );
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	set_theme_mod( 'ghahghah_steps_enabled', ! empty( $_POST['ghahghah_steps_enabled'] ) );
	set_theme_mod( 'ghahghah_steps_eyebrow', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_steps_eyebrow'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_steps_title', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_steps_title'] ?? '' ), 120 ) );
	set_theme_mod( 'ghahghah_steps_text', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_steps_text'] ?? '' ), 300 ) );
	set_theme_mod( 'ghahghah_steps_items', ghahghah_sanitize_steps_items_from_post( $titles, $texts ) );

	ghahghah_redirect_config_tab( 'steps' );
}
add_action( 'admin_post_ghahghah_save_steps_settings', 'ghahghah_save_steps_settings' );
