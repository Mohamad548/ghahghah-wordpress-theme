<?php
/**
 * Create and assign default navigation menus (idempotent).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure a nav menu exists by name; return term ID.
 */
function ghahghah_ensure_nav_menu( string $name ): int {
	$name = trim( $name );
	if ( '' === $name ) {
		return 0;
	}

	$existing = wp_get_nav_menu_object( $name );
	if ( $existing instanceof WP_Term ) {
		return (int) $existing->term_id;
	}

	$created = wp_create_nav_menu( $name );
	return is_wp_error( $created ) ? 0 : (int) $created;
}

/**
 * Add a custom/page/post link to a menu if not already present.
 *
 * @param int                  $menu_id Menu term ID.
 * @param array<string, mixed> $item    Keys: title, url, type, object, object_id.
 */
function ghahghah_ensure_menu_item( int $menu_id, array $item ): void {
	if ( $menu_id <= 0 ) {
		return;
	}

	$title = sanitize_text_field( (string) ( $item['title'] ?? '' ) );
	$url   = esc_url_raw( (string) ( $item['url'] ?? '' ) );
	$type  = (string) ( $item['type'] ?? 'custom' );
	$obj   = (string) ( $item['object'] ?? 'custom' );
	$oid   = absint( $item['object_id'] ?? 0 );

	if ( '' === $title ) {
		return;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( is_array( $items ) ) {
		foreach ( $items as $existing ) {
			if ( ! $existing instanceof WP_Post ) {
				continue;
			}
			if ( $oid > 0 && (int) $existing->object_id === $oid ) {
				return;
			}
			if ( '' !== $url && untrailingslashit( (string) $existing->url ) === untrailingslashit( $url ) ) {
				return;
			}
			if ( $title === (string) $existing->title && 'custom' === $type ) {
				return;
			}
		}
	}

	$args = array(
		'menu-item-title'  => $title,
		'menu-item-status' => 'publish',
		'menu-item-type'   => $type,
		'menu-item-object' => $obj,
	);
	if ( $oid > 0 ) {
		$args['menu-item-object-id'] = $oid;
	}
	if ( '' !== $url ) {
		$args['menu-item-url'] = $url;
	}

	wp_update_nav_menu_item( $menu_id, 0, $args );
}

/**
 * Build default menus and assign theme locations.
 *
 * @return array{ok: bool, message: string, menus: array<string, int>}
 */
function ghahghah_bootstrap_default_menus(): array {
	$primary_id  = ghahghah_ensure_nav_menu( __( 'منوی اصلی', 'ghahghah' ) );
	$footer_id   = ghahghah_ensure_nav_menu( __( 'منوی فوتر', 'ghahghah' ) );
	$business_id = ghahghah_ensure_nav_menu( __( 'منوی همکاری فوتر', 'ghahghah' ) );
	$legal_id    = ghahghah_ensure_nav_menu( __( 'منوی حقوقی', 'ghahghah' ) );
	$mobile_id   = ghahghah_ensure_nav_menu( __( 'ناوبری پایین موبایل', 'ghahghah' ) );

	$home_url = home_url( '/' );
	$products = post_type_exists( 'ghahghah_product' ) ? (string) get_post_type_archive_link( 'ghahghah_product' ) : '';
	$blog     = (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	if ( '' === $blog || '0' === $blog ) {
		$blog = home_url( '/blog/' );
	}

	$page_ids = array(
		'factory'   => absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) ),
		'contact'   => absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) ),
		'wholesale' => absint( get_theme_mod( 'ghahghah_wholesale_page_id', 0 ) ),
		'agency'    => absint( get_theme_mod( 'ghahghah_agency_page_id', 0 ) ),
		'faq'       => absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) ),
		'privacy'   => absint( get_theme_mod( 'ghahghah_footer_privacy_page_id', 0 ) ),
	);

	// Resolve by slug if theme mod empty.
	$slugs = array(
		'factory'   => 'factory',
		'contact'   => 'contact',
		'wholesale' => 'wholesale',
		'agency'    => 'agency',
		'faq'       => 'faq',
		'privacy'   => 'privacy-policy',
	);
	foreach ( $slugs as $key => $slug ) {
		if ( $page_ids[ $key ] > 0 ) {
			continue;
		}
		$page = get_page_by_path( $slug );
		if ( $page instanceof WP_Post ) {
			$page_ids[ $key ] = (int) $page->ID;
		}
	}

	$add_page = static function ( int $menu_id, int $page_id, string $fallback_title ) : void {
		if ( $page_id <= 0 ) {
			return;
		}
		$title = get_the_title( $page_id );
		if ( ! is_string( $title ) || '' === $title ) {
			$title = $fallback_title;
		}
		ghahghah_ensure_menu_item(
			$menu_id,
			array(
				'title'     => $title,
				'url'       => (string) get_permalink( $page_id ),
				'type'      => 'post_type',
				'object'    => 'page',
				'object_id' => $page_id,
			)
		);
	};

	if ( $primary_id > 0 ) {
		ghahghah_ensure_menu_item(
			$primary_id,
			array(
				'title' => __( 'خانه', 'ghahghah' ),
				'url'   => $home_url,
				'type'  => 'custom',
			)
		);
		if ( '' !== $products ) {
			ghahghah_ensure_menu_item(
				$primary_id,
				array(
					'title' => __( 'محصولات', 'ghahghah' ),
					'url'   => $products,
					'type'  => 'custom',
				)
			);
		}
		$add_page( $primary_id, $page_ids['factory'], __( 'کارخانه', 'ghahghah' ) );
		$add_page( $primary_id, $page_ids['contact'], __( 'تماس', 'ghahghah' ) );
		$add_page( $primary_id, $page_ids['faq'], __( 'سوالات متداول', 'ghahghah' ) );
		ghahghah_ensure_menu_item(
			$primary_id,
			array(
				'title' => __( 'مطالب', 'ghahghah' ),
				'url'   => $blog,
				'type'  => 'custom',
			)
		);
	}

	if ( $footer_id > 0 ) {
		ghahghah_ensure_menu_item(
			$footer_id,
			array(
				'title' => __( 'خانه', 'ghahghah' ),
				'url'   => $home_url,
				'type'  => 'custom',
			)
		);
		if ( '' !== $products ) {
			ghahghah_ensure_menu_item(
				$footer_id,
				array(
					'title' => __( 'محصولات', 'ghahghah' ),
					'url'   => $products,
					'type'  => 'custom',
				)
			);
		}
		$add_page( $footer_id, $page_ids['factory'], __( 'کارخانه', 'ghahghah' ) );
		$add_page( $footer_id, $page_ids['contact'], __( 'تماس', 'ghahghah' ) );
		$add_page( $footer_id, $page_ids['faq'], __( 'سوالات متداول', 'ghahghah' ) );
	}

	if ( $business_id > 0 ) {
		$add_page( $business_id, $page_ids['wholesale'], __( 'خرید عمده', 'ghahghah' ) );
		$add_page( $business_id, $page_ids['agency'], __( 'درخواست نمایندگی', 'ghahghah' ) );
	}

	if ( $legal_id > 0 ) {
		$add_page( $legal_id, $page_ids['privacy'], __( 'حریم خصوصی', 'ghahghah' ) );
	}

	if ( $mobile_id > 0 ) {
		ghahghah_ensure_menu_item(
			$mobile_id,
			array(
				'title' => __( 'خانه', 'ghahghah' ),
				'url'   => $home_url,
				'type'  => 'custom',
			)
		);
		if ( '' !== $products ) {
			ghahghah_ensure_menu_item(
				$mobile_id,
				array(
					'title' => __( 'محصولات', 'ghahghah' ),
					'url'   => $products,
					'type'  => 'custom',
				)
			);
		}
		$add_page( $mobile_id, $page_ids['wholesale'], __( 'خرید عمده', 'ghahghah' ) );
		$add_page( $mobile_id, $page_ids['contact'], __( 'تماس', 'ghahghah' ) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary']         = $primary_id;
	$locations['footer']          = $footer_id;
	$locations['footer_business'] = $business_id;
	$locations['legal']           = $legal_id;
	$locations['mobile_bottom']   = $mobile_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return array(
		'ok'      => true,
		'message' => __( 'فهرست‌های پیش‌فرض ساخته و به جایگاه‌ها وصل شدند.', 'ghahghah' ),
		'menus'   => array(
			'primary'         => $primary_id,
			'footer'          => $footer_id,
			'footer_business' => $business_id,
			'legal'           => $legal_id,
			'mobile_bottom'   => $mobile_id,
		),
	);
}
