<?php
/**
 * Idempotent setup for the factory intro page.
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

$page_id = absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) );
if ( $page_id > 0 ) {
	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type ) {
		$page_id = 0;
	}
}

if ( $page_id <= 0 ) {
	$page_id = $find_page(
		'کارخانه',
		array( 'factory', 'کارخانه', 'معرفی-کارخانه' )
	);
}

$created = false;
if ( $page_id <= 0 ) {
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'معرفی کارخانه',
			'post_name'    => 'factory',
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
}

$template = (string) get_page_template_slug( (int) $page_id );
if ( '' === $template || 'default' === $template ) {
	update_post_meta( (int) $page_id, '_wp_page_template', 'page-templates/factory-intro.php' );
}

if ( absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) ) <= 0 ) {
	set_theme_mod( 'ghahghah_factory_page_id', (int) $page_id );
}

// Homepage factory CTA → this page when unset.
if ( absint( get_theme_mod( 'ghahghah_factory_button_page', 0 ) ) <= 0 ) {
	set_theme_mod( 'ghahghah_factory_button_page', (int) $page_id );
}

$url = get_permalink( (int) $page_id );
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::log(
		sprintf(
			'factory id=%d created=%s template=%s url=%s',
			(int) $page_id,
			$created ? 'yes' : 'no',
			(string) get_page_template_slug( (int) $page_id ),
			is_string( $url ) ? $url : ''
		)
	);
	WP_CLI::success( 'Factory page setup finished (no overwrite of existing content).' );
}
