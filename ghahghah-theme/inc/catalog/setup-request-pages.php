<?php
/**
 * Idempotent setup for wholesale / agency request pages.
 *
 * Creates pages only if missing; never overwrites existing content or theme mods
 * when already configured. Assigns page templates when empty.
 *
 * Usage:
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-request-pages.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find published page by slug or title.
 */
$ghahghah_find_page = static function ( string $slug, string $title ): int {
	$by_slug = get_page_by_path( $slug );
	if ( $by_slug instanceof WP_Post ) {
		return (int) $by_slug->ID;
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

$specs = array(
	'wholesale' => array(
		'slug'     => 'wholesale',
		'title'    => 'درخواست خرید عمده',
		'template' => 'page-templates/wholesale-request.php',
		'mod'      => 'ghahghah_wholesale_page_id',
		'alt_slugs'=> array( 'درخواست-خرید-عمده' ),
	),
	'agency'    => array(
		'slug'     => 'representation',
		'title'    => 'درخواست نمایندگی',
		'template' => 'page-templates/agency-request.php',
		'mod'      => 'ghahghah_agency_page_id',
		'alt_slugs'=> array( 'درخواست-نمایندگی' ),
	),
);

foreach ( $specs as $key => $spec ) {
	$existing_mod = absint( get_theme_mod( $spec['mod'], 0 ) );
	$page_id      = $existing_mod;

	if ( $page_id > 0 ) {
		$page = get_post( $page_id );
		if ( ! $page || 'page' !== $page->post_type ) {
			$page_id = 0;
		}
	}

	if ( $page_id <= 0 ) {
		$page_id = $ghahghah_find_page( $spec['slug'], $spec['title'] );
	}
	if ( $page_id <= 0 ) {
		foreach ( $spec['alt_slugs'] as $alt ) {
			$page_id = $ghahghah_find_page( $alt, $spec['title'] );
			if ( $page_id > 0 ) {
				break;
			}
		}
	}

	$created = false;
	if ( $page_id <= 0 ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $spec['title'],
				'post_name'    => $spec['slug'],
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $page_id ) ) {
			if ( class_exists( 'WP_CLI' ) ) {
				WP_CLI::warning( $key . ': ' . $page_id->get_error_message() );
			}
			continue;
		}
		$created = true;
	}

	$current_template = (string) get_page_template_slug( (int) $page_id );
	if ( '' === $current_template ) {
		update_post_meta( (int) $page_id, '_wp_page_template', $spec['template'] );
	}

	if ( absint( get_theme_mod( $spec['mod'], 0 ) ) <= 0 ) {
		set_theme_mod( $spec['mod'], (int) $page_id );
	}

	$url = get_permalink( (int) $page_id );
	if ( class_exists( 'WP_CLI' ) ) {
		WP_CLI::log(
			sprintf(
				'%s id=%d created=%s template=%s url=%s',
				$key,
				(int) $page_id,
				$created ? 'yes' : 'no',
				(string) get_page_template_slug( (int) $page_id ),
				is_string( $url ) ? $url : ''
			)
		);
	}
}

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::success( 'Request pages setup finished (no overwrite of existing content).' );
}
