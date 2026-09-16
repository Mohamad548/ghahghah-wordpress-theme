<?php
/**
 * Persist factory intro settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save factory settings from the theme config screen.
 */
function ghahghah_save_factory_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_factory_settings', 'ghahghah_factory_nonce' );

	set_theme_mod( 'ghahghah_factory_enabled', ! empty( $_POST['ghahghah_factory_enabled'] ) );
	set_theme_mod( 'ghahghah_factory_eyebrow', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_eyebrow'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_factory_title', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_factory_title'] ?? '' ), 160 ) );
	set_theme_mod( 'ghahghah_factory_text', ghahghah_sanitize_hero_multiline( wp_unslash( $_POST['ghahghah_factory_text'] ?? '' ), 400 ) );

	set_theme_mod( 'ghahghah_factory_image', ghahghah_sanitize_attachment_id( wp_unslash( $_POST['ghahghah_factory_image'] ?? 0 ) ) );
	set_theme_mod( 'ghahghah_factory_image_alt', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_image_alt'] ?? '' ), 120 ) );

	set_theme_mod( 'ghahghah_factory_topics_heading', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_topics_heading'] ?? '' ), 80 ) );
	set_theme_mod( 'ghahghah_factory_topic_1', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_topic_1'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_factory_topic_2', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_topic_2'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_factory_topic_3', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_topic_3'] ?? '' ), 40 ) );

	set_theme_mod( 'ghahghah_factory_button_label', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_factory_button_label'] ?? '' ), 50 ) );
	set_theme_mod( 'ghahghah_factory_button_page', ghahghah_sanitize_hero_page_id( wp_unslash( $_POST['ghahghah_factory_button_page'] ?? 0 ) ) );

	ghahghah_redirect_config_tab( 'factory' );
}
add_action( 'admin_post_ghahghah_save_factory_settings', 'ghahghah_save_factory_settings' );
