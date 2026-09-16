<?php
/**
 * Save contact page theme mods.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle contact page admin save.
 */
function ghahghah_handle_save_contact_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ) );
	}
	check_admin_referer( 'ghahghah_save_contact_page', 'ghahghah_contact_page_nonce' );

	$page_id = absint( wp_unslash( $_POST['ghahghah_contact_page_id'] ?? 0 ) );
	set_theme_mod( 'ghahghah_contact_page_id', $page_id );

	if ( $page_id > 0 && '' === (string) get_page_template_slug( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-templates/contact.php' );
	}

	$defaults = ghahghah_contact_page_defaults();
	$old_map  = (string) get_theme_mod( 'ghahghah_contact_map_url', $defaults['ghahghah_contact_map_url'] ?? '' );

	foreach ( array_keys( $defaults ) as $key ) {
		if ( 'ghahghah_contact_page_id' === $key ) {
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( (string) $_POST[ $key ] );
		if ( 'ghahghah_contact_map_url' === $key ) {
			// Allow bare share links or iframe markup; strip tags first.
			$raw = trim( wp_strip_all_tags( $raw ) );
			if ( preg_match( '/src=["\']([^"\']+)["\']/i', $raw, $m ) ) {
				$raw = (string) $m[1];
			}
			set_theme_mod( $key, esc_url_raw( $raw ) );
			continue;
		}
		if ( str_contains( $key, '_text' ) || str_contains( $key, '_lead' ) ) {
			set_theme_mod( $key, sanitize_textarea_field( $raw ) );
		} else {
			set_theme_mod( $key, sanitize_text_field( $raw ) );
		}
	}

	$new_map = (string) get_theme_mod( 'ghahghah_contact_map_url', '' );
	if ( $old_map !== $new_map ) {
		delete_transient( 'ghahghah_map_embed_' . md5( $old_map ) );
		if ( '' !== $new_map ) {
			delete_transient( 'ghahghah_map_embed_' . md5( $new_map ) );
		}
	}

	$tab = isset( $_POST['ghahghah_return_tab'] ) ? sanitize_key( (string) wp_unslash( $_POST['ghahghah_return_tab'] ) ) : 'contact-page';
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => GHAHGHAH_CONFIG_PAGE,
				'tab'     => $tab,
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ghahghah_save_contact_page', 'ghahghah_handle_save_contact_page' );
