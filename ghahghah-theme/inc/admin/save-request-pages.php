<?php
/**
 * Save request pages settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle save.
 */
function ghahghah_handle_save_request_pages(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه دسترسی ندارید.', 'ghahghah' ) );
	}
	check_admin_referer( 'ghahghah_save_request_pages', 'ghahghah_request_pages_nonce' );

	$defaults = ghahghah_request_pages_defaults();
	$text_keys = array_keys( $defaults );
	foreach ( $text_keys as $key ) {
		if ( str_ends_with( $key, '_page_id' ) || str_ends_with( $key, '_image_id' ) ) {
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( (string) $_POST[ $key ] );
		set_theme_mod( $key, sanitize_text_field( $raw ) );
		if ( str_contains( $key, '_text' ) ) {
			set_theme_mod( $key, sanitize_textarea_field( $raw ) );
		}
	}

	set_theme_mod( 'ghahghah_wholesale_page_id', absint( wp_unslash( $_POST['ghahghah_wholesale_page_id'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_agency_page_id', absint( wp_unslash( $_POST['ghahghah_agency_page_id'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_wholesale_image_id', absint( wp_unslash( $_POST['ghahghah_wholesale_image_id'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_request_pages_last_saved', time() );

	$tab = sanitize_key( wp_unslash( (string) ( $_POST['ghahghah_return_tab'] ?? 'request-pages' ) ) );
	if ( 'request-pages' !== $tab ) {
		$tab = 'request-pages';
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'                    => GHAHGHAH_CONFIG_PAGE,
				GHAHGHAH_CONFIG_TAB_PARAM => $tab,
				'ghahghah_saved'          => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ghahghah_save_request_pages', 'ghahghah_handle_save_request_pages' );
