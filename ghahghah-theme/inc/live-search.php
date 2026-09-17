<?php
/**
 * Live header search REST endpoint (posts + products).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register live-search REST route.
 */
function ghahghah_register_live_search_route(): void {
	register_rest_route(
		'ghahghah/v1',
		'/live-search',
		array(
			'methods'             => 'GET',
			'callback'            => 'ghahghah_rest_live_search',
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'ghahghah_register_live_search_route' );

/**
 * Live search REST callback.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function ghahghah_rest_live_search( WP_REST_Request $request ): WP_REST_Response {
	$q = trim( (string) $request->get_param( 'q' ) );
	if ( mb_strlen( $q ) < 2 ) {
		return new WP_REST_Response(
			array(
				'query'   => $q,
				'items'   => array(),
				'total'   => 0,
				'moreUrl' => '',
			),
			200
		);
	}

	$types = array( 'post' );
	if ( post_type_exists( 'ghahghah_product' ) ) {
		$types[] = 'ghahghah_product';
	}

	$query = new WP_Query(
		array(
			's'              => $q,
			'post_type'      => $types,
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'no_found_rows'  => false,
		)
	);

	$items = array();
	foreach ( $query->posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$is_product = 'ghahghah_product' === $post->post_type;
		$thumb      = get_the_post_thumbnail_url( $post, 'thumbnail' );
		$excerpt    = get_the_excerpt( $post );
		$excerpt    = is_string( $excerpt ) ? wp_strip_all_tags( $excerpt ) : '';
		if ( '' !== $excerpt ) {
			$excerpt = wp_html_excerpt( $excerpt, 90, '…' );
		}

		$items[] = array(
			'id'      => (int) $post->ID,
			'title'   => get_the_title( $post ),
			'url'     => get_permalink( $post ),
			'type'    => $is_product ? 'product' : 'post',
			'label'   => $is_product ? __( 'محصول', 'ghahghah' ) : __( 'مقاله', 'ghahghah' ),
			'excerpt' => $excerpt,
			'image'   => is_string( $thumb ) ? $thumb : '',
		);
	}

	return new WP_REST_Response(
		array(
			'query'   => $q,
			'items'   => $items,
			'total'   => (int) $query->found_posts,
			'moreUrl' => add_query_arg( 's', $q, home_url( '/' ) ),
		),
		200
	);
}
