<?php
/**
 * Persist footer settings from theme config admin screens.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle desktop footer settings form submission.
 */
function ghahghah_handle_save_footer_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه ذخیره این تنظیمات را ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_footer_settings', 'ghahghah_footer_nonce' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by dedicated helpers.
	set_theme_mod( 'ghahghah_footer_logo', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_footer_logo'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_footer_intro', ghahghah_sanitize_footer_multiline( wp_unslash( $_POST['ghahghah_footer_intro'] ?? '' ) ) );
	set_theme_mod( 'ghahghah_footer_col_quick_title', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_col_quick_title'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_footer_col_business_title', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_col_business_title'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_footer_col_contact_title', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_col_contact_title'] ?? '' ), 60 ) );

	set_theme_mod( 'ghahghah_footer_collab_enabled', ! empty( $_POST['ghahghah_footer_collab_enabled'] ) );
	set_theme_mod( 'ghahghah_footer_collab_title', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_collab_title'] ?? '' ), 80 ) );
	set_theme_mod( 'ghahghah_footer_collab_text', ghahghah_sanitize_footer_multiline( wp_unslash( $_POST['ghahghah_footer_collab_text'] ?? '' ) ) );
	set_theme_mod( 'ghahghah_footer_collab_primary_label', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_collab_primary_label'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_footer_collab_primary_page', ghahghah_sanitize_footer_page_id( wp_unslash( $_POST['ghahghah_footer_collab_primary_page'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_footer_collab_secondary_label', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_collab_secondary_label'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_footer_collab_secondary_page', ghahghah_sanitize_footer_page_id( wp_unslash( $_POST['ghahghah_footer_collab_secondary_page'] ?? 0 ) ) );

	set_theme_mod( 'ghahghah_footer_phones', ghahghah_sanitize_footer_multiline( wp_unslash( $_POST['ghahghah_footer_phones'] ?? '' ) ) );
	set_theme_mod( 'ghahghah_footer_email', ghahghah_sanitize_footer_email( wp_unslash( $_POST['ghahghah_footer_email'] ?? '' ) ) );
	set_theme_mod( 'ghahghah_footer_address', ghahghah_sanitize_footer_multiline( wp_unslash( $_POST['ghahghah_footer_address'] ?? '' ) ) );
	set_theme_mod( 'ghahghah_footer_socials', ghahghah_sanitize_footer_socials_from_post( wp_unslash( $_POST ) ) );

	set_theme_mod( 'ghahghah_footer_legal_text', ghahghah_sanitize_footer_text( wp_unslash( $_POST['ghahghah_footer_legal_text'] ?? '' ), 160 ) );
	set_theme_mod( 'ghahghah_footer_privacy_page_id', ghahghah_sanitize_footer_page_id( wp_unslash( $_POST['ghahghah_footer_privacy_page_id'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_footer_back_to_top', ! empty( $_POST['ghahghah_footer_back_to_top'] ) );
	set_theme_mod( 'ghahghah_footer_last_saved', time() );
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	ghahghah_redirect_config_tab( 'footer' );
}
add_action( 'admin_post_ghahghah_save_footer_settings', 'ghahghah_handle_save_footer_settings' );

/**
 * Handle mobile footer settings form submission.
 */
function ghahghah_handle_save_mobile_footer_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه ذخیره این تنظیمات را ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_mobile_footer_settings', 'ghahghah_mobile_footer_nonce' );

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	set_theme_mod( 'ghahghah_footer_logo_mobile', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_footer_logo_mobile'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_mobile_footer_last_saved', time() );

	ghahghah_redirect_config_tab( 'mobile-footer' );
}
add_action( 'admin_post_ghahghah_save_mobile_footer_settings', 'ghahghah_handle_save_mobile_footer_settings' );
