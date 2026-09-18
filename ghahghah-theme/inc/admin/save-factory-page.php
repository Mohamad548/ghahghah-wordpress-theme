<?php
/**
 * Save factory intro page theme mods.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle factory page admin save.
 */
function ghahghah_handle_save_factory_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ) );
	}
	check_admin_referer( 'ghahghah_save_factory_page', 'ghahghah_factory_page_nonce' );

	$page_id = absint( wp_unslash( $_POST['ghahghah_factory_page_id'] ?? 0 ) );
	set_theme_mod( 'ghahghah_factory_page_id', $page_id );
	set_theme_mod( 'ghahghah_factory_page_hero_image_id', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_factory_page_hero_image_id'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_factory_page_show_temp_badge', ! empty( $_POST['ghahghah_factory_page_show_temp_badge'] ) );

	if ( $page_id > 0 && '' === (string) get_page_template_slug( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-templates/factory-intro.php' );
	}

	$defaults = ghahghah_factory_page_defaults();
	$text_keys = array(
		'ghahghah_factory_page_title',
		'ghahghah_factory_page_lead',
		'ghahghah_factory_page_company',
		'ghahghah_factory_page_intro',
		'ghahghah_factory_page_cta_label',
		'ghahghah_factory_page_process_title',
		'ghahghah_factory_page_process_text',
		'ghahghah_factory_page_quality_title',
		'ghahghah_factory_page_quality_text',
		'ghahghah_factory_page_certs_title',
		'ghahghah_factory_page_certs_text',
	);

	foreach ( $text_keys as $key ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$raw = is_string( $raw ) ? $raw : '';
		if ( str_contains( $key, '_intro' ) || str_contains( $key, '_text' ) ) {
			set_theme_mod( $key, sanitize_textarea_field( $raw ) );
		} else {
			set_theme_mod( $key, sanitize_text_field( $raw ) );
		}
	}

	// Keep homepage factory CTA in sync when unset.
	if ( absint( get_theme_mod( 'ghahghah_factory_button_page', 0 ) ) <= 0 && $page_id > 0 ) {
		set_theme_mod( 'ghahghah_factory_button_page', $page_id );
	}

	set_theme_mod( 'ghahghah_factory_page_last_saved', time() );

	$tab = isset( $_POST['ghahghah_return_tab'] ) ? sanitize_key( (string) wp_unslash( $_POST['ghahghah_return_tab'] ) ) : 'factory-page';
	ghahghah_redirect_config_tab( $tab );
}
add_action( 'admin_post_ghahghah_save_factory_page', 'ghahghah_handle_save_factory_page' );
