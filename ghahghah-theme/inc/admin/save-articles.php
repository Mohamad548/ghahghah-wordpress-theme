<?php
/**
 * Persist homepage articles settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Save articles section settings.
 */
function ghahghah_save_articles_settings(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'اجازه ذخیره ندارید.', 'ghahghah' ), 403 );
	}

	check_admin_referer( 'ghahghah_save_articles_settings', 'ghahghah_articles_nonce' );

	$cat = absint( wp_unslash( $_POST['ghahghah_articles_category'] ?? 0 ) );
	if ( $cat > 0 && ! term_exists( $cat, 'category' ) ) {
		$cat = 0;
	}

	set_theme_mod( 'ghahghah_articles_enabled', ! empty( $_POST['ghahghah_articles_enabled'] ) );
	set_theme_mod( 'ghahghah_articles_eyebrow', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_articles_eyebrow'] ?? '' ), 60 ) );
	set_theme_mod( 'ghahghah_articles_title', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_articles_title'] ?? '' ), 120 ) );
	set_theme_mod( 'ghahghah_articles_all_label', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_articles_all_label'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_articles_more_label', ghahghah_sanitize_hero_text( wp_unslash( $_POST['ghahghah_articles_more_label'] ?? '' ), 40 ) );
	set_theme_mod( 'ghahghah_articles_category', $cat );

	ghahghah_redirect_config_tab( 'articles' );
}
add_action( 'admin_post_ghahghah_save_articles_settings', 'ghahghah_save_articles_settings' );
