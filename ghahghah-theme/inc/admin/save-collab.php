<?php
/**
 * Persist collab CTA settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save collab settings from the theme config screen.
 */
function ghahghah_save_collab_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_collab_settings', 'ghahghah_collab_nonce' );

	set_theme_mod( 'ghahghah_collab_enabled', ! empty( $_POST['ghahghah_collab_enabled'] ) );
	set_theme_mod( 'ghahghah_collab_eyebrow', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_eyebrow'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_collab_title', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_title'] ?? '' ), 120 ) );
	set_theme_mod( 'ghahghah_collab_text', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_collab_text'] ?? '' ), 200 ) );

	set_theme_mod( 'ghahghah_collab_wholesale_title', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_wholesale_title'] ?? '' ), 80 ) );
	set_theme_mod( 'ghahghah_collab_wholesale_text', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_collab_wholesale_text'] ?? '' ), 240 ) );
	set_theme_mod( 'ghahghah_collab_wholesale_button', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_wholesale_button'] ?? '' ), 50 ) );

	set_theme_mod( 'ghahghah_collab_agency_title', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_agency_title'] ?? '' ), 80 ) );
	set_theme_mod( 'ghahghah_collab_agency_text', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_collab_agency_text'] ?? '' ), 240 ) );
	set_theme_mod( 'ghahghah_collab_agency_button', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_agency_button'] ?? '' ), 50 ) );
	set_theme_mod( 'ghahghah_collab_preview_forms', ! empty( $_POST['ghahghah_collab_preview_forms'] ) );

	ghahghah_redirect_config_tab( 'collab' );
}
add_action( 'admin_post_ghahghah_save_collab_settings', 'ghahghah_save_collab_settings' );
