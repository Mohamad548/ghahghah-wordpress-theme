<?php
/**
 * Homepage featured products settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defaults for featured products section.
 *
 * @return array<string, mixed>
 */
function ghahghah_featured_setting_defaults(): array {
	return array(
		'ghahghah_featured_enabled'   => true,
		'ghahghah_featured_title'     => __( 'محصولات منتخب قهقهه', 'ghahghah' ),
		'ghahghah_featured_text'      => __( 'طعم‌های قهقهه را بیشتر بشناسید.', 'ghahghah' ),
		'ghahghah_featured_all_label' => __( 'مشاهده همه محصولات', 'ghahghah' ),
		'ghahghah_featured_ids'       => array(),
	);
}

/**
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_featured_mod( string $key ) {
	$defaults = ghahghah_featured_setting_defaults();
	return get_theme_mod( $key, $defaults[ $key ] ?? null );
}

/**
 * Sanitize short text for featured settings.
 *
 * @param mixed $value Raw.
 * @param int   $max   Max length.
 */
function ghahghah_sanitize_featured_text( $value, int $max = 120 ): string {
	$text = sanitize_text_field( (string) $value );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $max );
	}
	return substr( $text, 0, $max );
}

/**
 * Max featured products in the homepage carousel.
 */
define( 'GHAHGHAH_FEATURED_MAX', 12 );

/**
 * Sanitize ordered list of product IDs.
 *
 * @param mixed $value Raw.
 * @return array<int, int>
 */
function ghahghah_sanitize_featured_product_ids( $value ): array {
	if ( is_string( $value ) ) {
		$value = preg_split( '/[\s,]+/', $value ) ?: array();
	}
	if ( ! is_array( $value ) ) {
		return array();
	}

	$out = array();
	foreach ( $value as $id ) {
		$id = absint( $id );
		if ( $id <= 0 || in_array( $id, $out, true ) ) {
			continue;
		}
		$out[] = $id;
		if ( count( $out ) >= GHAHGHAH_FEATURED_MAX ) {
			break;
		}
	}
	return $out;
}

/**
 * Products archive URL when CPT exists.
 */
function ghahghah_get_featured_archive_url(): string {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return '';
	}
	$url = get_post_type_archive_link( 'ghahghah_product' );
	return is_string( $url ) && '' !== $url ? $url : '';
}

/**
 * Escape title and highlight brand word.
 *
 * @param string $title Raw title.
 */
function ghahghah_format_featured_title( string $title ): string {
	$escaped = esc_html( $title );
	return (string) preg_replace(
		'/قهقهه/u',
		'<span class="ghahghah-featured__accent">$0</span>',
		$escaped
	);
}

/**
 * Arrow icon for featured CTAs.
 */
function ghahghah_the_featured_arrow(): void {
	echo '<svg class="ghahghah-featured__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>';
}

/**
 * Published featured products for front display (ordered).
 *
 * @return array<int, WP_Post>
 */
function ghahghah_get_featured_products(): array {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return array();
	}

	$ids = ghahghah_sanitize_featured_product_ids( ghahghah_get_featured_mod( 'ghahghah_featured_ids' ) );
	if ( array() === $ids ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'              => 'ghahghah_product',
			'post_status'            => 'publish',
			'post__in'               => $ids,
			'orderby'                => 'post__in',
			'posts_per_page'         => count( $ids ),
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	return is_array( $posts ) ? $posts : array();
}

/**
 * Whether featured section should render.
 */
function ghahghah_should_render_featured(): bool {
	if ( ! (bool) ghahghah_get_featured_mod( 'ghahghah_featured_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	return array() !== ghahghah_get_featured_products();
}
