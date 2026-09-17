<?php
/**
 * Idempotent setup for the contact page.
 *
 * Usage (WP-CLI / eval-file):
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-contact-page.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$find_page = static function ( string $title, array $slugs ): int {
	foreach ( $slugs as $slug ) {
		$p = get_page_by_path( (string) $slug );
		if ( $p instanceof WP_Post ) {
			return (int) $p->ID;
		}
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $q->posts[0] ) ) {
		return (int) $q->posts[0];
	}
	return 0;
};

$page_id = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
if ( $page_id > 0 ) {
	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type ) {
		$page_id = 0;
	}
}

if ( $page_id <= 0 ) {
	$page_id = $find_page(
		'تماس با ما',
		array( 'contact', 'تماس-با-ما', 'contact-us' )
	);
}

$created = false;
if ( $page_id <= 0 ) {
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'تماس با ما',
			'post_name'    => 'contact',
			'post_content' => '',
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
} elseif ( 'contact' !== (string) get_post_field( 'post_name', (int) $page_id ) ) {
	wp_update_post(
		array(
			'ID'        => (int) $page_id,
			'post_name' => 'contact',
		)
	);
}

$template = (string) get_page_template_slug( (int) $page_id );
if ( '' === $template || 'default' === $template ) {
	update_post_meta( (int) $page_id, '_wp_page_template', 'page-templates/contact.php' );
}

if ( absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) ) <= 0 ) {
	set_theme_mod( 'ghahghah_contact_page_id', (int) $page_id );
}

$url = get_permalink( (int) $page_id );
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::log(
		sprintf(
			'contact id=%d created=%s template=%s url=%s',
			(int) $page_id,
			$created ? 'yes' : 'no',
			(string) get_page_template_slug( (int) $page_id ),
			is_string( $url ) ? $url : ''
		)
	);
	WP_CLI::success( 'Contact page setup finished (no overwrite of existing content).' );
}
