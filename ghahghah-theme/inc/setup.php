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
			'height'      => 64,
			'width'       => 144,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary'         => __( 'منوی اصلی', 'ghahghah' ),
			'footer'          => __( 'منوی پاورقی (دسترسی سریع)', 'ghahghah' ),
			'footer_business' => __( 'منوی همکاری فوتر', 'ghahghah' ),
			'legal'           => __( 'منوی حقوقی', 'ghahghah' ),
			'mobile_bottom'   => __( 'ناوبری پایین موبایل', 'ghahghah' ),
		)
	);

	add_image_size( 'ghahghah-card', 640, 480, true );
	add_image_size( 'ghahghah-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'ghahghah_setup' );

/**
 * Force front-end document direction to RTL for the Persian brand theme.
 *
 * @param string $output Language attributes markup.
 */
function ghahghah_language_attributes( string $output ): string {
	if ( is_admin() ) {
		return $output;
	}

	if ( preg_match( '/\sdir=([\'"])ltr\1/i', $output ) ) {
		$output = preg_replace( '/\sdir=([\'"])ltr\1/i', ' dir=$1rtl$1', $output );
	} elseif ( ! preg_match( '/\sdir=/i', $output ) ) {
		$output .= ' dir="rtl"';
	}

	return $output;
}
add_filter( 'language_attributes', 'ghahghah_language_attributes' );

/**
 * Set content width for embeds and media.
 */
function ghahghah_content_width(): void {
	$GLOBALS['content_width'] = 720;
}
add_action( 'after_setup_theme', 'ghahghah_content_width', 0 );

/**
 * Include blog posts and products in site search results.
 *
 * @param WP_Query $query Main query.
 */
function ghahghah_search_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$types = array( 'post' );
	if ( post_type_exists( 'ghahghah_product' ) ) {
		$types[] = 'ghahghah_product';
	}

	$query->set( 'post_type', $types );
	$query->set( 'posts_per_page', 12 );
}
add_action( 'pre_get_posts', 'ghahghah_search_pre_get_posts' );
