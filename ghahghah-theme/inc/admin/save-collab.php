<?php
/**
 * Persist collab banner settings.
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
	set_theme_mod( 'ghahghah_collab_wholesale_image', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_collab_wholesale_image'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_collab_wholesale_image_alt', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_wholesale_image_alt'] ?? '' ), 160 ) );
	set_theme_mod( 'ghahghah_collab_agency_image', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_collab_agency_image'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_collab_agency_image_alt', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_collab_agency_image_alt'] ?? '' ), 160 ) );
	set_theme_mod( 'ghahghah_collab_last_saved', time() );

	// Legacy text CTA fields — no longer used on the front.
	foreach ( array(
		'ghahghah_collab_eyebrow',
		'ghahghah_collab_title',
		'ghahghah_collab_text',
		'ghahghah_collab_wholesale_title',
		'ghahghah_collab_wholesale_text',
		'ghahghah_collab_wholesale_button',
		'ghahghah_collab_agency_title',
		'ghahghah_collab_agency_text',
		'ghahghah_collab_agency_button',
		'ghahghah_collab_preview_forms',
	) as $legacy_key ) {
		remove_theme_mod( $legacy_key );
	}

	ghahghah_redirect_config_tab( 'collab' );
}
add_action( 'admin_post_ghahghah_save_collab_settings', 'ghahghah_save_collab_settings' );
