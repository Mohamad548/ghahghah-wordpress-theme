<?php
/**
 * Persist header / mobile settings from theme config admin screens.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed return tabs after saving.
 *
 * @return array<int, string>
 */
function ghahghah_allowed_return_tabs(): array {
	return array( 'header', 'hero', 'featured', 'factory', 'steps', 'collab', 'articles', 'mobile-header', 'mobile-bottom', 'mobile-footer', 'footer', 'archive-banners', 'theme-media' );
}

/**
 * Redirect back to a config tab.
 *
 * @param string $tab Tab slug.
 */
function ghahghah_redirect_config_tab( string $tab ): void {
	$allowed = ghahghah_allowed_return_tabs();
	if ( ! in_array( $tab, $allowed, true ) ) {
		$tab = 'header';
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => GHAHGHAH_CONFIG_PAGE,
				'tab'            => $tab,
				'ghahghah_saved' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

/**
 * Handle desktop header settings form submission.
 */
function ghahghah_handle_save_header_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه ذخیره این تنظیمات را ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_header_settings', 'ghahghah_header_nonce' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by dedicated helpers.
	$logo_desktop = wp_unslash( $_POST['ghahghah_header_logo_desktop'] ?? 0 );
	$favicon      = wp_unslash( $_POST['ghahghah_header_favicon'] ?? 0 );
	$width_desk   = wp_unslash( $_POST['ghahghah_header_logo_width_desktop'] ?? 144 );
	$cta_label    = wp_unslash( $_POST['ghahghah_header_cta_label'] ?? '' );
	$cta_page     = wp_unslash( $_POST['ghahghah_header_cta_page_id'] ?? 0 );
	$return_tab   = sanitize_key( wp_unslash( (string) ( $_POST['ghahghah_return_tab'] ?? 'header' ) ) );
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	set_theme_mod( 'ghahghah_header_logo_desktop', ghahghah_sanitize_attachment_id( $logo_desktop ) );

	$favicon_id = ghahghah_sanitize_attachment_id( $favicon );
	set_theme_mod( 'ghahghah_header_favicon', $favicon_id );
	if ( $favicon_id > 0 ) {
		update_option( 'site_icon', $favicon_id );
	}

	set_theme_mod( 'ghahghah_header_logo_width_desktop', ghahghah_sanitize_header_logo_width_desktop( $width_desk ) );
	set_theme_mod( 'ghahghah_header_cta_enabled', ! empty( $_POST['ghahghah_header_cta_enabled'] ) );
	set_theme_mod( 'ghahghah_header_cta_label', ghahghah_sanitize_header_cta_label( $cta_label ) );
	set_theme_mod( 'ghahghah_header_cta_page_id', ghahghah_sanitize_header_cta_page_id( $cta_page ) );
	set_theme_mod( 'ghahghah_header_sticky', ! empty( $_POST['ghahghah_header_sticky'] ) );

	ghahghah_redirect_config_tab( $return_tab );
}
add_action( 'admin_post_ghahghah_save_header_settings', 'ghahghah_handle_save_header_settings' );

/**
 * Handle mobile header settings form submission.
 */
function ghahghah_handle_save_mobile_header_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه ذخیره این تنظیمات را ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_mobile_header_settings', 'ghahghah_mobile_header_nonce' );

	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by dedicated helpers.
	$logo_mobile  = wp_unslash( $_POST['ghahghah_header_logo_mobile'] ?? 0 );
	$width_mobile = wp_unslash( $_POST['ghahghah_header_logo_width_mobile'] ?? 112 );
	// phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	set_theme_mod( 'ghahghah_header_logo_mobile', ghahghah_sanitize_attachment_id( $logo_mobile ) );
	set_theme_mod( 'ghahghah_header_logo_width_mobile', ghahghah_sanitize_header_logo_width_mobile( $width_mobile ) );

	ghahghah_redirect_config_tab( 'mobile-header' );
}
add_action( 'admin_post_ghahghah_save_mobile_header_settings', 'ghahghah_handle_save_mobile_header_settings' );

/**
 * Handle mobile bottom nav settings form submission.
 */
function ghahghah_handle_save_mobile_bottom_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه ذخیره این تنظیمات را ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_mobile_bottom_settings', 'ghahghah_mobile_bottom_nonce' );

	set_theme_mod( 'ghahghah_bottom_nav_enabled', ! empty( $_POST['ghahghah_bottom_nav_enabled'] ) );

	ghahghah_redirect_config_tab( 'mobile-bottom' );
}
add_action( 'admin_post_ghahghah_save_mobile_bottom_settings', 'ghahghah_handle_save_mobile_bottom_settings' );
