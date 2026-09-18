<?php
/**
 * Create and assign default navigation menus (idempotent).
 *
 * Matches the designed theme header on local (8898):
 * صفحه اصلی · محصولات (+ طعم‌ها) · کارخانه · مقالات · تماس با ما
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
 * Ensure a published «مقالات» posts index page and assign page_for_posts.
 */
function ghahghah_ensure_posts_page(): int {
	$page_id = absint( get_option( 'page_for_posts' ) );
	if ( $page_id > 0 ) {
		$page = get_post( $page_id );
		if ( $page instanceof WP_Post && 'page' === $page->post_type ) {
			return $page_id;
		}
	}

	foreach ( array( 'articles', 'maghalat', 'blog', 'مقالات' ) as $slug ) {
		$found = get_page_by_path( $slug );
		if ( $found instanceof WP_Post ) {
			$page_id = (int) $found->ID;
			break;
		}
	}

	if ( $page_id <= 0 ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'مقالات',
				'post_name'    => 'articles',
				'post_content' => '',
			),
			true
		);
		if ( is_wp_error( $page_id ) ) {
			return 0;
		}
		$page_id = (int) $page_id;
	}

	update_option( 'page_for_posts', $page_id );

	// Prefer static front page when a front page already exists.
	$front = absint( get_option( 'page_on_front' ) );
	if ( $front > 0 && $front !== $page_id ) {
		update_option( 'show_on_front', 'page' );
	}

	return $page_id;
}

/**
 * Add or refresh a menu item.
 *
 * @param int                  $menu_id Menu term ID.
 * @param array<string, mixed> $item    Keys: title, url, type, object, object_id, icon, position, force_title.
 * @return int Menu item ID (0 on skip/failure).
 */
function ghahghah_ensure_menu_item( int $menu_id, array $item ): int {
	if ( $menu_id <= 0 ) {
		return 0;
	}

	$title       = sanitize_text_field( (string) ( $item['title'] ?? '' ) );
	$url         = esc_url_raw( (string) ( $item['url'] ?? '' ) );
	$type        = (string) ( $item['type'] ?? 'custom' );
	$obj         = (string) ( $item['object'] ?? 'custom' );
	$oid         = absint( $item['object_id'] ?? 0 );
	$icon        = isset( $item['icon'] ) ? sanitize_key( (string) $item['icon'] ) : '';
	$position    = isset( $item['position'] ) ? absint( $item['position'] ) : 0;
	$force_title = ! empty( $item['force_title'] );
	$aliases     = isset( $item['aliases'] ) && is_array( $item['aliases'] ) ? $item['aliases'] : array();

	if ( '' === $title ) {
		return 0;
	}

	$items      = wp_get_nav_menu_items( $menu_id );
	$existing_id = 0;
	if ( is_array( $items ) ) {
		foreach ( $items as $existing ) {
			if ( ! $existing instanceof WP_Post ) {
				continue;
			}
			if ( (int) $existing->menu_item_parent > 0 ) {
				continue;
			}

			$match = false;
			if ( $oid > 0 && (int) $existing->object_id === $oid ) {
				$match = true;
			} elseif ( '' !== $url && untrailingslashit( (string) $existing->url ) === untrailingslashit( $url ) ) {
				$match = true;
			} elseif ( $title === (string) $existing->title && ( 'custom' === $type || 'custom' === (string) $existing->type ) ) {
				$match = true;
			} elseif ( array() !== $aliases && in_array( (string) $existing->title, $aliases, true ) ) {
				$match = true;
			}

			if ( $match ) {
				$existing_id = (int) $existing->ID;
				break;
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
	if ( $position > 0 ) {
		$args['menu-item-position'] = $position;
	}

	if ( $existing_id > 0 ) {
		$needs_update = $force_title || $position > 0 || $oid > 0;
		if ( $needs_update ) {
			$current_title = (string) get_post_meta( $existing_id, '_menu_item_title', true );
			if ( '' === $current_title ) {
				$current_title = (string) get_the_title( $existing_id );
			}
			if ( $force_title || $current_title !== $title || $position > 0 || $oid > 0 ) {
				wp_update_nav_menu_item( $menu_id, $existing_id, $args );
			}
		}
		if ( '' !== $icon && function_exists( 'ghahghah_sanitize_bottom_nav_icon_key' ) ) {
			$icon = ghahghah_sanitize_bottom_nav_icon_key( $icon );
			if ( '' !== $icon ) {
				$current = ghahghah_sanitize_bottom_nav_icon_key(
					get_post_meta( $existing_id, '_ghahghah_bottom_nav_icon', true )
				);
				if ( '' === $current ) {
					update_post_meta( $existing_id, '_ghahghah_bottom_nav_icon', $icon );
				}
			}
		}
		return $existing_id;
	}

	$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
	if ( is_wp_error( $item_id ) || $item_id <= 0 ) {
		return 0;
	}

	if ( '' !== $icon && function_exists( 'ghahghah_sanitize_bottom_nav_icon_key' ) ) {
		$icon = ghahghah_sanitize_bottom_nav_icon_key( $icon );
		if ( '' !== $icon ) {
			update_post_meta( (int) $item_id, '_ghahghah_bottom_nav_icon', $icon );
		}
	}

	return (int) $item_id;
}

/**
 * Resolve known theme page IDs (theme mod → slug fallback).
 *
 * @return array<string, int>
 */
function ghahghah_resolve_bootstrap_page_ids(): array {
	$page_ids = array(
		'factory'   => absint( get_theme_mod( 'ghahghah_factory_page_id', 0 ) ),
		'contact'   => absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) ),
		'wholesale' => absint( get_theme_mod( 'ghahghah_wholesale_page_id', 0 ) ),
		'agency'    => absint( get_theme_mod( 'ghahghah_agency_page_id', 0 ) ),
		'faq'       => absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) ),
		'privacy'   => absint( get_theme_mod( 'ghahghah_footer_privacy_page_id', 0 ) ),
	);

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

	return $page_ids;
}

/**
 * Remove leftover FAQ top-level item from primary (designed header has no FAQ).
 *
 * @param int $menu_id  Primary menu ID.
 * @param int $faq_page FAQ page ID.
 */
function ghahghah_purge_primary_faq_item( int $menu_id, int $faq_page ): void {
	if ( $menu_id <= 0 ) {
		return;
	}
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return;
	}
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post || (int) $item->menu_item_parent > 0 ) {
			continue;
		}
		$title = trim( (string) $item->title );
		$is_faq_title = in_array( $title, array( 'سوالات متداول', 'پرسش‌های متداول', 'FAQ' ), true );
		$is_faq_page  = $faq_page > 0 && 'page' === $item->object && (int) $item->object_id === $faq_page;
		if ( $is_faq_title || $is_faq_page ) {
			wp_delete_post( (int) $item->ID, true );
		}
	}
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

	$home_url     = home_url( '/' );
	$products_url = post_type_exists( 'ghahghah_product' ) ? (string) get_post_type_archive_link( 'ghahghah_product' ) : '';
	$posts_page   = ghahghah_ensure_posts_page();
	$page_ids     = ghahghah_resolve_bootstrap_page_ids();

	$add_page = static function ( int $menu_id, int $page_id, string $menu_title, string $icon = '', int $position = 0 ) : void {
		if ( $page_id <= 0 || '' === $menu_title ) {
			return;
		}
		$payload = array(
			'title'       => $menu_title,
			'url'         => (string) get_permalink( $page_id ),
			'type'        => 'post_type',
			'object'      => 'page',
			'object_id'   => $page_id,
			'force_title' => true,
		);
		if ( '' !== $icon ) {
			$payload['icon'] = $icon;
		}
		if ( $position > 0 ) {
			$payload['position'] = $position;
		}
		ghahghah_ensure_menu_item( $menu_id, $payload );
	};

	// —— Primary: designed header order ——
	if ( $primary_id > 0 ) {
		ghahghah_purge_primary_faq_item( $primary_id, $page_ids['faq'] );

		ghahghah_ensure_menu_item(
			$primary_id,
			array(
				'title'       => __( 'صفحه اصلی', 'ghahghah' ),
				'url'         => $home_url,
				'type'        => 'custom',
				'position'    => 1,
				'force_title' => true,
				'aliases'     => array( 'خانه', 'Home', 'صفحهٔ اصلی' ),
			)
		);

		if ( '' !== $products_url ) {
			ghahghah_ensure_menu_item(
				$primary_id,
				array(
					'title'       => __( 'محصولات', 'ghahghah' ),
					'url'         => $products_url,
					'type'        => 'custom',
					'position'    => 2,
					'force_title' => true,
				)
			);
		}

		$add_page( $primary_id, $page_ids['factory'], __( 'کارخانه', 'ghahghah' ), '', 3 );

		if ( $posts_page > 0 ) {
			ghahghah_ensure_menu_item(
				$primary_id,
				array(
					'title'       => __( 'مقالات', 'ghahghah' ),
					'url'         => (string) get_permalink( $posts_page ),
					'type'        => 'post_type',
					'object'      => 'page',
					'object_id'   => $posts_page,
					'position'    => 4,
					'force_title' => true,
					'aliases'     => array( 'مطالب', 'وبلاگ', 'Blog' ),
				)
			);
		}

		$add_page( $primary_id, $page_ids['contact'], __( 'تماس با ما', 'ghahghah' ), '', 5 );
	}

	// —— Footer quick links ——
	if ( $footer_id > 0 ) {
		ghahghah_ensure_menu_item(
			$footer_id,
			array(
				'title'       => __( 'صفحه اصلی', 'ghahghah' ),
				'url'         => $home_url,
				'type'        => 'custom',
				'force_title' => true,
				'aliases'     => array( 'خانه' ),
			)
		);
		if ( '' !== $products_url ) {
			ghahghah_ensure_menu_item(
				$footer_id,
				array(
					'title' => __( 'محصولات', 'ghahghah' ),
					'url'   => $products_url,
					'type'  => 'custom',
				)
			);
		}
		$add_page( $footer_id, $page_ids['factory'], __( 'کارخانه', 'ghahghah' ) );
		$add_page( $footer_id, $page_ids['contact'], __( 'تماس با ما', 'ghahghah' ) );
		$add_page( $footer_id, $page_ids['faq'], __( 'سوالات متداول', 'ghahghah' ) );
		if ( $posts_page > 0 ) {
			ghahghah_ensure_menu_item(
				$footer_id,
				array(
					'title'       => __( 'مقالات', 'ghahghah' ),
					'url'         => (string) get_permalink( $posts_page ),
					'type'        => 'post_type',
					'object'      => 'page',
					'object_id'   => $posts_page,
					'force_title' => true,
					'aliases'     => array( 'مطالب', 'وبلاگ' ),
				)
			);
		}
	}

	if ( $business_id > 0 ) {
		$add_page( $business_id, $page_ids['wholesale'], __( 'خرید عمده', 'ghahghah' ) );
		$add_page( $business_id, $page_ids['agency'], __( 'درخواست نمایندگی', 'ghahghah' ) );
	}

	if ( $legal_id > 0 ) {
		$add_page( $legal_id, $page_ids['privacy'], __( 'حریم خصوصی', 'ghahghah' ) );
	}

	// —— Mobile bottom: خانه · محصولات · خرید عمده · تماس ——
	if ( $mobile_id > 0 ) {
		ghahghah_ensure_menu_item(
			$mobile_id,
			array(
				'title'       => __( 'خانه', 'ghahghah' ),
				'url'         => $home_url,
				'type'        => 'custom',
				'icon'        => 'home',
				'position'    => 1,
				'force_title' => true,
				'aliases'     => array( 'صفحه اصلی' ),
			)
		);
		if ( '' !== $products_url ) {
			ghahghah_ensure_menu_item(
				$mobile_id,
				array(
					'title'    => __( 'محصولات', 'ghahghah' ),
					'url'      => $products_url,
					'type'     => 'custom',
					'icon'     => 'products',
					'position' => 2,
				)
			);
		}
		$add_page( $mobile_id, $page_ids['wholesale'], __( 'خرید عمده', 'ghahghah' ), 'wholesale', 3 );
		$add_page( $mobile_id, $page_ids['contact'], __( 'تماس', 'ghahghah' ), 'contact', 4 );

		if ( function_exists( 'ghahghah_sync_bottom_nav_item_icons' ) ) {
			ghahghah_sync_bottom_nav_item_icons( $mobile_id );
		}
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

	// Attach product flavor children under «محصولات» and wire archive links.
	if ( function_exists( 'ghahghah_sync_nav_menus' ) ) {
		ghahghah_sync_nav_menus();
	}
	if ( function_exists( 'ghahghah_sync_primary_blog_archive_link' ) ) {
		ghahghah_sync_primary_blog_archive_link();
	}

	return array(
		'ok'      => true,
		'message' => __( 'فهرست‌ها مطابق هدر قالب ساخته و به جایگاه‌ها وصل شدند (صفحه اصلی، محصولات، کارخانه، مقالات، تماس با ما).', 'ghahghah' ),
		'menus'   => array(
			'primary'         => $primary_id,
			'footer'          => $footer_id,
			'footer_business' => $business_id,
			'legal'           => $legal_id,
			'mobile_bottom'   => $mobile_id,
		),
	);
}
