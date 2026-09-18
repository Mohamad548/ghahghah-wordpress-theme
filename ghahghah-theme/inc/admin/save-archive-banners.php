<?php
/**
 * Save archive banner theme mods (articles + products).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle archive banners admin save.
 */
function ghahghah_handle_save_archive_banners(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_archive_banners', 'ghahghah_archive_banners_nonce' );

	$blog_keys = ghahghah_blog_archive_banner_mod_keys();
	$prod_keys = ghahghah_products_archive_banner_mod_keys();

	foreach ( array_merge( array_values( $blog_keys ), array_values( $prod_keys ) ) as $key ) {
		$raw = wp_unslash( $_POST[ $key ] ?? 0 );
		set_theme_mod( $key, ghahghah_sanitize_attachment_id( $raw ) );
	}

	set_theme_mod( 'ghahghah_archive_banners_last_saved', time() );

	ghahghah_redirect_config_tab( 'archive-banners' );
}
add_action( 'admin_post_ghahghah_save_archive_banners', 'ghahghah_handle_save_archive_banners' );
