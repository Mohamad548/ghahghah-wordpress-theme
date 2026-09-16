<?php
/**
 * Homepage latest articles section settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Soft cap for the homepage articles carousel. */
const GHAHGHAH_ARTICLES_CAROUSEL_MAX = 12;

/**
 * Default theme mods for latest articles.
 *
 * @return array<string, mixed>
 */
function ghahghah_articles_setting_defaults(): array {
	return array(
		'ghahghah_articles_enabled'    => true,
		'ghahghah_articles_eyebrow'    => __( 'مطالب و دانستنی‌ها', 'ghahghah' ),
		'ghahghah_articles_title'      => __( 'آخرین مطالب قهقهه', 'ghahghah' ),
		'ghahghah_articles_all_label'  => __( 'همه مطالب', 'ghahghah' ),
		'ghahghah_articles_more_label' => __( 'ادامه مطلب', 'ghahghah' ),
		'ghahghah_articles_category'   => 0,
	);
}

/**
 * Get an articles theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_articles_mod( string $key ) {
	$defaults = ghahghah_articles_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Resolve optional category filter for the homepage articles section.
 * Zero means all published posts (any new post can appear).
 */
function ghahghah_get_articles_category_id(): int {
	$saved = absint( ghahghah_get_articles_mod( 'ghahghah_articles_category' ) );
	if ( $saved > 0 && term_exists( $saved, 'category' ) ) {
		return $saved;
	}

	return 0;
}

/**
 * Published posts for the homepage carousel.
 *
 * @return array<int, WP_Post>
 */
function ghahghah_get_articles_posts(): array {
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => GHAHGHAH_ARTICLES_CAROUSEL_MAX,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	$cat_id = ghahghah_get_articles_category_id();
	if ( $cat_id > 0 ) {
		$args['category'] = $cat_id;
	}

	$posts = get_posts( $args );
	return is_array( $posts ) ? $posts : array();
}

/**
 * Archive URL for articles (selected category, posts page, or blog home).
 */
function ghahghah_get_articles_archive_url(): string {
	$page_for_posts = absint( get_option( 'page_for_posts' ) );
	if ( $page_for_posts > 0 ) {
		$link = get_permalink( $page_for_posts );
		if ( is_string( $link ) && '' !== $link ) {
			return $link;
		}
	}

	$cat_id = ghahghah_get_articles_category_id();
	if ( $cat_id > 0 ) {
		$link = get_category_link( $cat_id );
		if ( is_string( $link ) && '' !== $link ) {
			return $link;
		}
	}

	$link = get_post_type_archive_link( 'post' );
	return is_string( $link ) ? $link : home_url( '/' );
}

/**
 * Whether the latest articles section should render.
 */
function ghahghah_should_render_articles(): bool {
	if ( ! (bool) ghahghah_get_articles_mod( 'ghahghah_articles_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	return count( ghahghah_get_articles_posts() ) > 0;
}
