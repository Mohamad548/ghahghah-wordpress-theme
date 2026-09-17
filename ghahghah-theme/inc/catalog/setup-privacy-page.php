<?php
/**
 * Idempotent setup for the privacy policy page.
 *
 * Usage (WP-CLI / eval-file):
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-privacy-page.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_id = absint( get_option( 'wp_page_for_privacy_policy' ) );
if ( $page_id > 0 ) {
	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type ) {
		$page_id = 0;
	}
}

if ( $page_id <= 0 ) {
	$by_path = get_page_by_path( 'privacy-policy' );
	if ( $by_path instanceof WP_Post ) {
		$page_id = (int) $by_path->ID;
	}
}

if ( $page_id <= 0 ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft' ),
			'title'          => 'Privacy Policy',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $q->posts[0] ) ) {
		$page_id = (int) $q->posts[0];
	}
}

$created = false;
if ( $page_id <= 0 ) {
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'حریم خصوصی',
			'post_name'    => 'privacy-policy',
			'post_content' => function_exists( 'ghahghah_privacy_default_content' )
				? ghahghah_privacy_default_content()
				: '',
		),
		true
	);
	if ( is_wp_error( $page_id ) ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::warning( $page_id->get_error_message() );
		}
		return;
	}
	$created = true;
}

update_option( 'wp_page_for_privacy_policy', (int) $page_id );

$current = get_post( (int) $page_id );
$title   = $current instanceof WP_Post ? (string) $current->post_title : '';
$content = $current instanceof WP_Post ? (string) $current->post_content : '';

$looks_english_default = (
	'Privacy Policy' === $title
	|| false !== stripos( $content, 'Suggested text:' )
	|| false !== stripos( $content, 'Who we are' )
	|| false !== stripos( $content, 'privacy-policy-tutorial' )
);

$update = array( 'ID' => (int) $page_id );
if ( 'Privacy Policy' === $title || '' === trim( $title ) ) {
	$update['post_title'] = 'حریم خصوصی';
}
if ( $looks_english_default && function_exists( 'ghahghah_privacy_default_content' ) ) {
	$update['post_content'] = ghahghah_privacy_default_content();
}
if ( count( $update ) > 1 ) {
	wp_update_post( $update );
}

update_post_meta( (int) $page_id, '_wp_page_template', 'page-templates/privacy.php' );

if ( absint( get_theme_mod( 'ghahghah_footer_privacy_page_id', 0 ) ) <= 0 ) {
	set_theme_mod( 'ghahghah_footer_privacy_page_id', (int) $page_id );
}

$url = get_permalink( (int) $page_id );
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::log(
		sprintf(
			'privacy id=%d created=%s template=%s url=%s',
			(int) $page_id,
			$created ? 'yes' : 'no',
			(string) get_page_template_slug( (int) $page_id ),
			is_string( $url ) ? $url : ''
		)
	);
	WP_CLI::success( 'Privacy page setup finished.' );
}
