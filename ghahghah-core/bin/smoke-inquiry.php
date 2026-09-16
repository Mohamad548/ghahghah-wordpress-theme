<?php
/**
 * One-off inquiry smoke test.
 *
 * @package Ghahghah\Core
 */

$svc = new \Ghahghah\Core\Forms\InquiryService();

$agency = $svc->create(
	array(
		'type'      => 'agency',
		'full_name' => 'Smoke Agency',
		'phone'     => '09124445566',
		'company'   => 'Co',
		'province'  => 'تهران',
		'city'      => 'تهران',
		'activity'  => 'پخش مویرگی',
		'consent'   => 1,
	)
);

if ( is_wp_error( $agency ) ) {
	WP_CLI::warning( $agency->get_error_message() );
	WP_CLI::log( wp_json_encode( $agency->get_error_data(), JSON_UNESCAPED_UNICODE ) );
} else {
	WP_CLI::success( 'agency id=' . $agency['id'] );
}
