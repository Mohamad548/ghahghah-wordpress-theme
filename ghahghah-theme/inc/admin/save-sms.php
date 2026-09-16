<?php
/**
 * Persist SMS settings from theme config.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle SMS settings save.
 */
function ghahghah_handle_save_sms_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه دسترسی ندارید.', 'ghahghah' ) );
	}

	check_admin_referer( 'ghahghah_save_sms_settings', 'ghahghah_sms_nonce' );

	$defaults = ghahghah_sms_setting_defaults();
	$out      = array();

	$out['sms_enabled']  = ! empty( $_POST['sms_enabled'] ) ? 1 : 0;
	$out['sms_provider'] = 'melipayamak';
	$out['sms_username'] = sanitize_text_field( wp_unslash( (string) ( $_POST['sms_username'] ?? '' ) ) );
	$out['sms_api_key']  = sanitize_text_field( wp_unslash( (string) ( $_POST['sms_api_key'] ?? '' ) ) );
	$out['sms_api_secret'] = sanitize_text_field( wp_unslash( (string) ( $_POST['sms_api_secret'] ?? '' ) ) );
	$out['sms_line']     = sanitize_text_field( wp_unslash( (string) ( $_POST['sms_line'] ?? '' ) ) );
	$out['admin_phone']  = sanitize_text_field( wp_unslash( (string) ( $_POST['admin_phone'] ?? '' ) ) );
	$out['agency_activities'] = sanitize_textarea_field( wp_unslash( (string) ( $_POST['agency_activities'] ?? $defaults['agency_activities'] ) ) );

	foreach ( array( 'wholesale', 'agency' ) as $prefix ) {
		$out[ $prefix . '_user_enabled' ]  = ! empty( $_POST[ $prefix . '_user_enabled' ] ) ? 1 : 0;
		$out[ $prefix . '_user_pattern' ]  = sanitize_text_field( wp_unslash( (string) ( $_POST[ $prefix . '_user_pattern' ] ?? '' ) ) );
		$out[ $prefix . '_user_message' ]  = sanitize_textarea_field( wp_unslash( (string) ( $_POST[ $prefix . '_user_message' ] ?? '' ) ) );
		$out[ $prefix . '_admin_enabled' ] = ! empty( $_POST[ $prefix . '_admin_enabled' ] ) ? 1 : 0;
		$out[ $prefix . '_admin_phone' ]   = sanitize_text_field( wp_unslash( (string) ( $_POST[ $prefix . '_admin_phone' ] ?? '' ) ) );
		$out[ $prefix . '_admin_pattern' ] = sanitize_text_field( wp_unslash( (string) ( $_POST[ $prefix . '_admin_pattern' ] ?? '' ) ) );
		$out[ $prefix . '_admin_message' ] = sanitize_textarea_field( wp_unslash( (string) ( $_POST[ $prefix . '_admin_message' ] ?? '' ) ) );
	}

	update_option( 'ghahghah_sms_settings', $out, false );

	$tab = sanitize_key( wp_unslash( (string) ( $_POST['ghahghah_return_tab'] ?? 'sms-settings' ) ) );
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                => GHAHGHAH_CONFIG_PAGE,
				GHAHGHAH_CONFIG_TAB_PARAM => $tab,
				'updated'             => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ghahghah_save_sms_settings', 'ghahghah_handle_save_sms_settings' );
