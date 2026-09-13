<?php
/**
 * Front-end and editor asset loading.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme styles and scripts.
 */
function ghahghah_enqueue_assets(): void {
	$theme_version = GHAHGHAH_THEME_VERSION;

	wp_enqueue_style(
		'ghahghah-base',
		GHAHGHAH_THEME_URI . '/assets/css/base.css',
		array(),
		$theme_version
	);

	wp_enqueue_style(
		'ghahghah-layout',
		GHAHGHAH_THEME_URI . '/assets/css/layout.css',
		array( 'ghahghah-base' ),
		$theme_version
	);

	$script_path = GHAHGHAH_THEME_DIR . '/assets/js/theme.js';

	if ( is_readable( $script_path ) ) {
		wp_enqueue_script(
			'ghahghah-theme',
			GHAHGHAH_THEME_URI . '/assets/js/theme.js',
			array(),
			$theme_version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ghahghah_enqueue_assets' );
