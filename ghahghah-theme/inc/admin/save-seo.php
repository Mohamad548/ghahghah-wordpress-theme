<?php
/**
 * Save SEO theme mods.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle SEO admin save.
 */
function ghahghah_handle_save_seo(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ) );
	}
	check_admin_referer( 'ghahghah_save_seo', 'ghahghah_seo_nonce' );

	$defaults = ghahghah_seo_setting_defaults();

	$title = isset( $_POST['ghahghah_seo_home_title'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['ghahghah_seo_home_title'] ) )
		: '';
	if ( function_exists( 'mb_substr' ) ) {
		$title = mb_substr( $title, 0, 70 );
	} else {
		$title = substr( $title, 0, 70 );
	}
	set_theme_mod( 'ghahghah_seo_home_title', '' !== $title ? $title : (string) $defaults['ghahghah_seo_home_title'] );

	$desc = isset( $_POST['ghahghah_seo_home_description'] )
		? sanitize_textarea_field( wp_unslash( (string) $_POST['ghahghah_seo_home_description'] ) )
		: '';
	$desc = trim( preg_replace( '/\s+/u', ' ', $desc ) ?? $desc );
	if ( function_exists( 'mb_substr' ) ) {
		$desc = mb_substr( $desc, 0, 180 );
	} else {
		$desc = substr( $desc, 0, 180 );
	}
	set_theme_mod( 'ghahghah_seo_home_description', '' !== $desc ? $desc : (string) $defaults['ghahghah_seo_home_description'] );

	$h1 = isset( $_POST['ghahghah_seo_home_h1'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['ghahghah_seo_home_h1'] ) )
		: '';
	if ( function_exists( 'mb_substr' ) ) {
		$h1 = mb_substr( $h1, 0, 90 );
	} else {
		$h1 = substr( $h1, 0, 90 );
	}
	set_theme_mod( 'ghahghah_seo_home_h1', '' !== $h1 ? $h1 : (string) $defaults['ghahghah_seo_home_h1'] );

	$og_id = function_exists( 'ghahghah_sanitize_attachment_id' )
		? ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_seo_og_image_id'] ?? 0 ) )
		: absint( wp_unslash( $_POST['ghahghah_seo_og_image_id'] ?? 0 ) );
	set_theme_mod( 'ghahghah_seo_og_image_id', $og_id );

	$tab = isset( $_POST['ghahghah_return_tab'] ) ? sanitize_key( (string) wp_unslash( $_POST['ghahghah_return_tab'] ) ) : 'seo';
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => GHAHGHAH_CONFIG_PAGE,
				'tab'            => $tab,
				'ghahghah_saved' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ghahghah_save_seo', 'ghahghah_handle_save_seo' );
