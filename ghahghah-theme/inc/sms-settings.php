<?php
/**
 * Theme SMS gateway + pattern settings (Melipayamak).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default SMS option values.
 *
 * @return array<string, mixed>
 */
function ghahghah_sms_setting_defaults(): array {
	return array(
		'sms_enabled'              => 0,
		'sms_provider'             => 'melipayamak',
		'sms_username'             => '',
		'sms_api_key'              => '',
		'sms_api_secret'           => '',
		'sms_line'                 => '',
		'admin_phone'              => '',
		'agency_activities'        => "عمده‌فروشی مواد غذایی\nپخش مویرگی\nفروشگاه زنجیره‌ای\nسوپرمارکت / هایپر\nسایر",
		'wholesale_user_enabled'   => 0,
		'wholesale_user_pattern'   => '',
		'wholesale_user_message'   => '',
		'wholesale_admin_enabled'  => 0,
		'wholesale_admin_phone'    => '',
		'wholesale_admin_pattern'  => '',
		'wholesale_admin_message'  => '',
		'agency_user_enabled'      => 0,
		'agency_user_pattern'      => '',
		'agency_user_message'      => '',
		'agency_admin_enabled'     => 0,
		'agency_admin_phone'       => '',
		'agency_admin_pattern'     => '',
		'agency_admin_message'     => '',
	);
}

/**
 * Get SMS settings merged with defaults.
 *
 * @return array<string, mixed>
 */
function ghahghah_get_sms_settings(): array {
	$saved = get_option( 'ghahghah_sms_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return wp_parse_args( $saved, ghahghah_sms_setting_defaults() );
}
