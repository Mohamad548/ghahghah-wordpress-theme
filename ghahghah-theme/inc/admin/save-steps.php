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

	set_theme_mod( 'ghahghah_steps_enabled', ! empty( $_POST['ghahghah_steps_enabled'] ) );
	set_theme_mod( 'ghahghah_steps_image', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_steps_image'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_steps_image_alt', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_steps_image_alt'] ?? '' ), 160 ) );
	set_theme_mod( 'ghahghah_steps_last_saved', time() );

	// Legacy text fields / rows — no longer used on the front.
	remove_theme_mod( 'ghahghah_steps_eyebrow' );
	remove_theme_mod( 'ghahghah_steps_title' );
	remove_theme_mod( 'ghahghah_steps_text' );
	remove_theme_mod( 'ghahghah_steps_items' );

	ghahghah_redirect_config_tab( 'steps' );
}
add_action( 'admin_post_ghahghah_save_steps_settings', 'ghahghah_save_steps_settings' );
