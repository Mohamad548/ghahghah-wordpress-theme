<?php
/**
 * Persist featured products settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save featured products settings.
 */
function ghahghah_save_featured_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_featured_settings', 'ghahghah_featured_nonce' );

	set_theme_mod( 'ghahghah_featured_enabled', ! empty( $_POST['ghahghah_featured_enabled'] ) );
	set_theme_mod(
		'ghahghah_featured_title',
		ghahghah_sanitize_featured_text( wp_unslash( $_POST['ghahghah_featured_title'] ?? '' ), 80 )
	);
	set_theme_mod(
		'ghahghah_featured_text',
		ghahghah_sanitize_featured_text( wp_unslash( $_POST['ghahghah_featured_text'] ?? '' ), 160 )
	);
	set_theme_mod(
		'ghahghah_featured_all_label',
		ghahghah_sanitize_featured_text( wp_unslash( $_POST['ghahghah_featured_all_label'] ?? '' ), 40 )
	);

	$checks = isset( $_POST['ghahghah_featured_check'] ) && is_array( $_POST['ghahghah_featured_check'] )
		? wp_unslash( $_POST['ghahghah_featured_check'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();
	$orders = isset( $_POST['ghahghah_featured_order'] ) && is_array( $_POST['ghahghah_featured_order'] )
		? wp_unslash( $_POST['ghahghah_featured_order'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();

	$ranked = array();
	foreach ( $checks as $pid => $on ) {
		if ( empty( $on ) ) {
			continue;
		}
		$pid = absint( $pid );
		if ( $pid <= 0 ) {
			continue;
		}
		$ord = isset( $orders[ $pid ] ) ? absint( $orders[ $pid ] ) : 99;
		if ( $ord < 1 ) {
			$ord = 99;
		}
		$ranked[] = array(
			'id'    => $pid,
			'order' => $ord,
		);
	}

	usort(
		$ranked,
		static function ( array $a, array $b ): int {
			if ( $a['order'] === $b['order'] ) {
				return $a['id'] <=> $b['id'];
			}
			return $a['order'] <=> $b['order'];
		}
	);

	$ids = array();
	foreach ( $ranked as $row ) {
		$ids[] = $row['id'];
		if ( count( $ids ) >= GHAHGHAH_FEATURED_MAX ) {
			break;
		}
	}

	set_theme_mod( 'ghahghah_featured_ids', ghahghah_sanitize_featured_product_ids( $ids ) );
	set_theme_mod( 'ghahghah_featured_last_saved', time() );

	if ( function_exists( 'ghahghah_sync_primary_flavor_menu_items' ) ) {
		ghahghah_sync_primary_flavor_menu_items();
	}

	ghahghah_redirect_config_tab( 'featured' );
}
add_action( 'admin_post_ghahghah_save_featured_settings', 'ghahghah_save_featured_settings' );
