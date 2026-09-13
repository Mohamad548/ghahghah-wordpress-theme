<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports, menus, and image sizes.
 */
function ghahghah_setup(): void {
	load_theme_textdomain( 'ghahghah', GHAHGHAH_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی', 'ghahghah' ),
			'footer'  => __( 'منوی پاورقی', 'ghahghah' ),
			'legal'   => __( 'منوی حقوقی', 'ghahghah' ),
		)
	);

	add_image_size( 'ghahghah-card', 640, 480, true );
	add_image_size( 'ghahghah-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'ghahghah_setup' );

/**
 * Set content width for embeds and media.
 */
function ghahghah_content_width(): void {
	$GLOBALS['content_width'] = 720;
}
add_action( 'after_setup_theme', 'ghahghah_content_width', 0 );
