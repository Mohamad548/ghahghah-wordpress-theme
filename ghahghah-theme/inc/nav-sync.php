<?php
/**
 * Keep footer / primary menus in sync with FAQ page and featured flavors.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Menu item meta: synced source key. */
const GHAHGHAH_NAV_SYNC_META = '_ghahghah_nav_source';

/**
 * Short nav label for a product (prefer «طعم …»).
 */
function ghahghah_product_nav_label( WP_Post $product ): string {
	$title = get_the_title( $product );
	if ( is_string( $title ) && preg_match( '/طعم\s+\S.+/u', $title, $m ) ) {
		return trim( $m[0] );
	}
	return is_string( $title ) ? $title : '';
}

/**
 * Resolve menu term ID for a theme location.
 */
function ghahghah_get_nav_menu_id_for_location( string $location ): int {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return 0;
	}
	return absint( $locations[ $location ] );
}

/**
 * Ensure FAQ page is listed in the footer quick-access menu.
 *
 * @return bool True when a change was made.
 */
function ghahghah_sync_footer_faq_menu_item(): bool {
	$faq_id = function_exists( 'ghahghah_get_faq_related_page_id' )
		? ghahghah_get_faq_related_page_id( 'faq' )
		: 0;
	if ( $faq_id <= 0 ) {
		return false;
	}

	$menu_id = ghahghah_get_nav_menu_id_for_location( 'footer' );
	if ( $menu_id <= 0 ) {
		return false;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		$items = array();
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		$source = (string) get_post_meta( (int) $item->ID, GHAHGHAH_NAV_SYNC_META, true );
		if ( 'faq' === $source ) {
			// Already synced — refresh title/url if needed.
			wp_update_nav_menu_item(
				$menu_id,
				(int) $item->ID,
				array(
					'menu-item-object-id' => $faq_id,
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-title'     => get_the_title( $faq_id ) ?: __( 'پرسش‌های متداول', 'ghahghah' ),
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => 0,
				)
			);
			update_post_meta( (int) $item->ID, GHAHGHAH_NAV_SYNC_META, 'faq' );
			return false;
		}
		if ( 'page' === $item->object && (int) $item->object_id === $faq_id ) {
			update_post_meta( (int) $item->ID, GHAHGHAH_NAV_SYNC_META, 'faq' );
			return false;
		}
	}

	$new_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-object-id' => $faq_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-title'     => get_the_title( $faq_id ) ?: __( 'پرسش‌های متداول', 'ghahghah' ),
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => 0,
			'menu-item-position'  => count( $items ) + 1,
		)
	);

	if ( is_wp_error( $new_id ) || $new_id <= 0 ) {
		return false;
	}
	update_post_meta( (int) $new_id, GHAHGHAH_NAV_SYNC_META, 'faq' );
	return true;
}

/**
 * Find the «محصولات» top-level item in the primary menu.
 */
function ghahghah_find_primary_products_menu_item_id( int $menu_id ): int {
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return 0;
	}
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		if ( (int) $item->menu_item_parent > 0 ) {
			continue;
		}
		$title = trim( (string) $item->title );
		if ( 'محصولات' === $title || 'products' === sanitize_title( $title ) ) {
			return (int) $item->ID;
		}
		if ( 'page' === $item->object && (int) $item->object_id > 0 ) {
			$page = get_post( (int) $item->object_id );
			if ( $page instanceof WP_Post && 'محصولات' === $page->post_title ) {
				return (int) $item->ID;
			}
		}
	}
	return 0;
}

/** Menu item meta: product id behind a flavor-filter custom link. */
const GHAHGHAH_NAV_FLAVOR_PRODUCT_META = '_ghahghah_nav_flavor_product';

/**
 * Sync featured carousel products as direct children of primary «محصولات».
 *
 * Each item links to the products archive with that flavor filter (not the single product).
 *
 * @return bool True when menu changed.
 */
function ghahghah_sync_primary_flavor_menu_items(): bool {
	if ( ! function_exists( 'ghahghah_get_featured_products' ) ) {
		return false;
	}

	$menu_id = ghahghah_get_nav_menu_id_for_location( 'primary' );
	if ( $menu_id <= 0 ) {
		return false;
	}

	$parent_id = ghahghah_find_primary_products_menu_item_id( $menu_id );
	if ( $parent_id <= 0 ) {
		return false;
	}

	$products = ghahghah_get_featured_products();
	if ( array() === $products ) {
		// Fresh installs may not have curated featured IDs yet — use full catalog.
		$fallback = get_posts(
			array(
				'post_type'              => 'ghahghah_product',
				'post_status'            => 'publish',
				'posts_per_page'         => 24,
				'orderby'                => 'menu_order title',
				'order'                  => 'ASC',
				'ignore_sticky_posts'    => true,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);
		$products = is_array( $fallback ) ? $fallback : array();
	}
	$wanted   = array();
	foreach ( $products as $product ) {
		if ( $product instanceof WP_Post ) {
			$wanted[ (int) $product->ID ] = $product;
		}
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		$items = array();
	}

	$changed  = false;
	$existing = array(); // product_id => menu_item_id

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		$item_id     = (int) $item->ID;
		$item_parent = (int) $item->menu_item_parent;
		$source      = (string) get_post_meta( $item_id, GHAHGHAH_NAV_SYNC_META, true );
		$title       = trim( (string) $item->title );

		// Remove nested «سایر طعم‌ها» group (and any leftover flavors_parent marker).
		if ( 'flavors_parent' === $source || ( $item_parent === $parent_id && 'سایر طعم‌ها' === $title ) ) {
			wp_delete_post( $item_id, true );
			$changed = true;
			continue;
		}

		// Remove «همه محصولات» under محصولات.
		if ( $item_parent === $parent_id && ( 'همه محصولات' === $title || 'all-products' === sanitize_title( $title ) ) ) {
			wp_delete_post( $item_id, true );
			$changed = true;
			continue;
		}

		if ( in_array( $source, array( 'featured_product', 'featured_flavor_filter' ), true ) ) {
			$product_id = absint( get_post_meta( $item_id, GHAHGHAH_NAV_FLAVOR_PRODUCT_META, true ) );
			if ( $product_id <= 0 && 'featured_product' === $source ) {
				$product_id = (int) $item->object_id;
			}
			if ( $product_id > 0 && isset( $wanted[ $product_id ] ) ) {
				$existing[ $product_id ] = $item_id;
			} else {
				wp_delete_post( $item_id, true );
				$changed = true;
			}
			continue;
		}

		// Drop legacy CPT product children under محصولات (old single-product links).
		if (
			$item_parent === $parent_id
			&& 'ghahghah_product' === $item->object
			&& 'post_type' === $item->type
		) {
			wp_delete_post( $item_id, true );
			$changed = true;
			continue;
		}

		// Drop legacy page stubs under محصولات (old «طعم …» pages).
		if (
			$item_parent === $parent_id
			&& 'page' === $item->object
			&& preg_match( '/^طعم\s+/u', $title )
		) {
			wp_delete_post( $item_id, true );
			$changed = true;
		}
	}

	$position = 1;
	foreach ( $wanted as $product_id => $product ) {
		$label = ghahghah_product_nav_label( $product );
		if ( '' === $label ) {
			continue;
		}

		$flavor_key = function_exists( 'ghahghah_products_archive_flavor_key_for_product' )
			? ghahghah_products_archive_flavor_key_for_product( $product )
			: 'other';
		$url        = function_exists( 'ghahghah_products_archive_flavor_url' )
			? ghahghah_products_archive_flavor_url( $flavor_key )
			: home_url( '/products/' );

		$args = array(
			'menu-item-type'             => 'custom',
			'menu-item-url'              => $url,
			'menu-item-title'            => $label,
			'menu-item-status'           => 'publish',
			'menu-item-parent-id'        => $parent_id,
			'menu-item-position'         => $position,
			'menu-item-object'           => '',
			'menu-item-object-id'        => 0,
		);
		++$position;

		$menu_item_id = isset( $existing[ $product_id ] ) ? (int) $existing[ $product_id ] : 0;
		$updated_id   = wp_update_nav_menu_item( $menu_id, $menu_item_id, $args );
		if ( is_wp_error( $updated_id ) || $updated_id <= 0 ) {
			continue;
		}

		update_post_meta( (int) $updated_id, GHAHGHAH_NAV_SYNC_META, 'featured_flavor_filter' );
		update_post_meta( (int) $updated_id, GHAHGHAH_NAV_FLAVOR_PRODUCT_META, $product_id );
		$changed = true;
	}

	return $changed;
}

/**
 * Run all menu syncs.
 */
function ghahghah_sync_nav_menus(): void {
	ghahghah_sync_footer_faq_menu_item();
	ghahghah_sync_primary_flavor_menu_items();
	if ( function_exists( 'ghahghah_sync_primary_products_archive_link' ) ) {
		ghahghah_sync_primary_products_archive_link();
	}
}

/**
 * After featured products are saved, refresh flavor menu items.
 */
function ghahghah_sync_nav_after_featured_save(): void {
	ghahghah_sync_primary_flavor_menu_items();
}

/**
 * Hook featured save redirect path — run after theme mod update.
 */
function ghahghah_maybe_sync_nav_on_featured_admin_post(): void {
	if ( ! isset( $_POST['action'] ) || 'ghahghah_save_featured_settings' !== $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	add_action( 'shutdown', 'ghahghah_sync_primary_flavor_menu_items', 5 );
}
add_action( 'admin_post_ghahghah_save_featured_settings', 'ghahghah_maybe_sync_nav_on_featured_admin_post', 5 );

/**
 * After FAQ page id is saved, ensure footer link exists.
 */
function ghahghah_maybe_sync_nav_on_faq_admin_post(): void {
	if ( ! isset( $_POST['action'] ) || 'ghahghah_save_faq' !== $_POST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}
	add_action( 'shutdown', 'ghahghah_sync_footer_faq_menu_item', 5 );
}
add_action( 'admin_post_ghahghah_save_faq', 'ghahghah_maybe_sync_nav_on_faq_admin_post', 5 );
