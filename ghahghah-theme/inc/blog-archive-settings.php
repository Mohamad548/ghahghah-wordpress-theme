<?php
/**
 * Blog / articles archive helpers, query filters, and copy.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is the posts blog archive (not the front page).
 */
function ghahghah_is_blog_archive(): bool {
	return ( is_home() && ! is_front_page() ) || is_category();
}

/**
 * Default copy for the blog archive.
 *
 * @return array<string, string>
 */
function ghahghah_blog_archive_defaults(): array {
	return array(
		'hero_title'         => __( 'مقالات قهقهه', 'ghahghah' ),
		'hero_subtitle'      => __( 'مطالب آموزشی، اخبار و داستان‌های برند قهقهه', 'ghahghah' ),
		'hero_tagline_left'  => __( 'خوشمزه‌تر زندگی کنیم :)', 'ghahghah' ),
		'hero_tagline_right' => __( 'طعم لحظات خوب', 'ghahghah' ),
		'search_placeholder' => __( 'جستجوی مقاله...', 'ghahghah' ),
		'sort_label'         => __( 'مرتب‌سازی:', 'ghahghah' ),
		'cta_label'          => __( 'مطالعه مقاله', 'ghahghah' ),
		'crumb_home'         => __( 'خانه', 'ghahghah' ),
		'crumb_current'      => __( 'وبلاگ', 'ghahghah' ),
		'empty_title'        => __( 'مقاله‌ای یافت نشد', 'ghahghah' ),
		'empty_text'         => __( 'عبارت جستجو یا دسته‌بندی را تغییر دهید.', 'ghahghah' ),
		'footer_left'        => __( 'با هر قهقهه، دنیا خوشمزه‌تر است...', 'ghahghah' ),
		'footer_right'       => __( 'طعم خوب، آینده روشن‌تر', 'ghahghah' ),
	);
}

/**
 * Desired blog category chips (slug => label). Created on demand if missing.
 *
 * @return array<string, string>
 */
function ghahghah_blog_archive_category_definitions(): array {
	return array(
		'factory-intro'    => __( 'معرفی کارخانه', 'ghahghah' ),
		'products-blog'    => __( 'محصولات', 'ghahghah' ),
		'quality-production'=> __( 'کیفیت و تولید', 'ghahghah' ),
		'ghahghah-news'    => __( 'اخبار قهقهه', 'ghahghah' ),
		'guides-tips'      => __( 'آموزش و دانستنی‌ها', 'ghahghah' ),
	);
}

/**
 * Ensure blog archive categories exist (idempotent).
 */
function ghahghah_blog_archive_ensure_categories(): void {
	foreach ( ghahghah_blog_archive_category_definitions() as $slug => $name ) {
		if ( term_exists( $slug, 'category' ) ) {
			continue;
		}
		wp_insert_term(
			$name,
			'category',
			array(
				'slug' => $slug,
			)
		);
	}
}
add_action( 'init', 'ghahghah_blog_archive_ensure_categories', 20 );

/**
 * Category chips for the toolbar (only categories that exist).
 *
 * @return array<string, array{label: string, slug: string, id: int}>
 */
function ghahghah_blog_archive_category_chips(): array {
	$chips = array(
		'all' => array(
			'label' => __( 'همه مقالات', 'ghahghah' ),
			'slug'  => '',
			'id'    => 0,
		),
	);

	foreach ( ghahghah_blog_archive_category_definitions() as $slug => $label ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$chips[ $slug ] = array(
			'label' => $label,
			'slug'  => $slug,
			'id'    => (int) $term->term_id,
		);
	}

	return $chips;
}

/**
 * Sort options.
 *
 * @return array<string, string>
 */
function ghahghah_blog_archive_sort_options(): array {
	return array(
		'newest'  => __( 'جدیدترین', 'ghahghah' ),
		'oldest'  => __( 'قدیمی‌ترین', 'ghahghah' ),
		'popular' => __( 'پربازدیدترین', 'ghahghah' ),
	);
}

/**
 * Current filter state from the request.
 *
 * @return array{q: string, sort: string, cat: string}
 */
function ghahghah_blog_archive_request_state(): array {
	$sorts = ghahghah_blog_archive_sort_options();
	$chips = ghahghah_blog_archive_category_chips();

	$q    = isset( $_GET['gh_q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['gh_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sort = isset( $_GET['gh_sort'] ) ? sanitize_key( wp_unslash( (string) $_GET['gh_sort'] ) ) : 'newest'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$cat  = isset( $_GET['gh_cat'] ) ? sanitize_title( wp_unslash( (string) $_GET['gh_cat'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( is_category() ) {
		$queried = get_queried_object();
		if ( $queried instanceof WP_Term && 'category' === $queried->taxonomy ) {
			$cat = $queried->slug;
		}
	}

	if ( ! isset( $sorts[ $sort ] ) ) {
		$sort = 'newest';
	}
	if ( ! isset( $chips[ $cat ] ) ) {
		$cat = 'all';
	}

	return array(
		'q'    => $q,
		'sort' => $sort,
		'cat'  => $cat,
	);
}

/**
 * Blog posts index URL (page_for_posts or fallback).
 */
function ghahghah_get_blog_archive_url(): string {
	$page_for_posts = absint( get_option( 'page_for_posts' ) );
	if ( $page_for_posts > 0 ) {
		$link = get_permalink( $page_for_posts );
		if ( is_string( $link ) && '' !== $link ) {
			return $link;
		}
	}

	if ( function_exists( 'ghahghah_get_articles_archive_url' ) ) {
		return ghahghah_get_articles_archive_url();
	}

	return home_url( '/' );
}

/**
 * Build blog archive URL with optional query args.
 *
 * @param array<string, string> $args Query args.
 */
function ghahghah_blog_archive_url( array $args = array() ): string {
	$base  = ghahghah_get_blog_archive_url();
	$clean = array();
	foreach ( $args as $key => $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			continue;
		}
		if ( 'gh_sort' === $key && 'newest' === $value ) {
			continue;
		}
		if ( 'gh_cat' === $key && ( 'all' === $value || '' === $value ) ) {
			continue;
		}
		$clean[ $key ] = $value;
	}
	return $clean ? add_query_arg( $clean, $base ) : $base;
}

/**
 * Absolute path to a blog-archive icon SVG.
 */
function ghahghah_get_blog_archive_icon_path( string $name ): string {
	$key = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $name ) );
	if ( ! is_string( $key ) || '' === $key ) {
		return '';
	}
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/blog-archive/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo an inline blog-archive SVG icon.
 *
 * @param string               $name Icon basename.
 * @param array<string, mixed> $args Optional class / modifiers.
 */
function ghahghah_the_blog_archive_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_blog_archive_icon_path( $name );
	if ( '' === $path ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$class = 'ghahghah-ba-icon';
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$class .= ' ' . $args['class'];
	}
	if ( ! empty( $args['modifiers'] ) && is_array( $args['modifiers'] ) ) {
		foreach ( $args['modifiers'] as $mod ) {
			if ( is_string( $mod ) && '' !== $mod ) {
				$class .= ' ghahghah-ba-icon--' . sanitize_html_class( $mod );
			}
		}
	}

	$svg = preg_replace( '/<svg\b/i', '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG.
}

/**
 * Card excerpt with fallback.
 */
function ghahghah_blog_archive_card_excerpt( WP_Post $post ): string {
	$excerpt = get_the_excerpt( $post );
	if ( is_string( $excerpt ) ) {
		$excerpt = trim( wp_strip_all_tags( $excerpt ) );
		if ( '' !== $excerpt ) {
			return $excerpt;
		}
	}
	$content = get_post_field( 'post_content', $post );
	if ( ! is_string( $content ) || '' === trim( $content ) ) {
		return '';
	}
	return wp_trim_words( wp_strip_all_tags( $content ), 28, '…' );
}

/**
 * Fallback featured image URL (real snack photo).
 */
function ghahghah_blog_archive_fallback_image_url(): string {
	return GHAHGHAH_THEME_URI . '/assets/images/blog-archive/real-snack-bowl-photo.jpg';
}

/**
 * Found count label.
 */
function ghahghah_blog_archive_found_label( int $count ): string {
	/* translators: %d: number of articles found */
	return sprintf( _n( '%d مقاله یافت شد', '%d مقاله یافت شد', $count, 'ghahghah' ), $count );
}

/**
 * Main-query adjustments for blog archive filters.
 *
 * @param WP_Query $query Query.
 */
function ghahghah_blog_archive_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$is_blog = ( $query->is_home() && ! $query->is_front_page() ) || $query->is_category();
	if ( ! $is_blog ) {
		return;
	}

	$query->set( 'post_type', 'post' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', 9 );

	$state = ghahghah_blog_archive_request_state();

	if ( '' !== $state['q'] ) {
		$query->set( 's', $state['q'] );
	}

	if ( 'all' !== $state['cat'] && ! $query->is_category() ) {
		$query->set( 'category_name', $state['cat'] );
	}

	switch ( $state['sort'] ) {
		case 'oldest':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'ASC' );
			break;
		case 'popular':
			// Fallback to newest unless a views meta key exists later.
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;
		case 'newest':
		default:
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;
	}
}
add_action( 'pre_get_posts', 'ghahghah_blog_archive_pre_get_posts' );

/**
 * Point primary «وبلاگ» / «مقالات» menu item at the posts page.
 *
 * @return bool True when changed.
 */
function ghahghah_sync_primary_blog_archive_link(): bool {
	$page_id = absint( get_option( 'page_for_posts' ) );
	if ( $page_id <= 0 ) {
		return false;
	}

	$menu_id = function_exists( 'ghahghah_get_nav_menu_id_for_location' )
		? ghahghah_get_nav_menu_id_for_location( 'primary' )
		: 0;
	if ( $menu_id <= 0 ) {
		return false;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return false;
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}
		if ( (int) $item->menu_item_parent > 0 ) {
			continue;
		}
		$title = trim( (string) $item->title );
		$match = in_array( $title, array( 'وبلاگ', 'مقالات', 'Blog' ), true )
			|| 'blog' === sanitize_title( $title )
			|| 'maghalat' === sanitize_title( $title );

		if ( ! $match && 'page' === $item->object && (int) $item->object_id === $page_id ) {
			$match = true;
		}

		if ( ! $match ) {
			continue;
		}

		$type   = (string) get_post_meta( (int) $item->ID, '_menu_item_type', true );
		$object = (string) get_post_meta( (int) $item->ID, '_menu_item_object', true );
		$oid    = absint( get_post_meta( (int) $item->ID, '_menu_item_object_id', true ) );
		if ( 'post_type' === $type && 'page' === $object && $oid === $page_id ) {
			return false;
		}

		$updated = wp_update_nav_menu_item(
			$menu_id,
			(int) $item->ID,
			array(
				'menu-item-title'     => $title !== '' ? $title : __( 'وبلاگ', 'ghahghah' ),
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-status'    => 'publish',
			)
		);

		return ! is_wp_error( $updated ) && $updated > 0;
	}

	return false;
}
add_action( 'init', 'ghahghah_sync_primary_blog_archive_link', 36 );

/**
 * Theme-mod keys for blog archive custom banner.
 *
 * @return array{desktop: string, mobile: string}
 */
function ghahghah_blog_archive_banner_mod_keys(): array {
	return array(
		'desktop' => 'ghahghah_blog_archive_banner_desktop_id',
		'mobile'  => 'ghahghah_blog_archive_banner_mobile_id',
	);
}

/**
 * Custom blog archive banner (null = use designed default hero).
 *
 * @return array{desktop: array{src: string, srcset: string, width: int, height: int, alt: string}, mobile: array{src: string, srcset: string, width: int, height: int, alt: string}|null}|null
 */
function ghahghah_get_blog_archive_custom_banner(): ?array {
	$keys     = ghahghah_blog_archive_banner_mod_keys();
	$desktop  = absint( get_theme_mod( $keys['desktop'], 0 ) );
	$mobile   = absint( get_theme_mod( $keys['mobile'], 0 ) );
	$fallback = __( 'بنر آرشیو مقالات قهقهه', 'ghahghah' );
	$desk     = ghahghah_resolve_archive_banner_attachment( $desktop, $fallback );

	if ( null === $desk ) {
		$desk = ghahghah_resolve_archive_bundled_banner( 'blog', 'desktop', $fallback );
	}

	if ( null === $desk ) {
		return null;
	}

	$mob = ghahghah_resolve_archive_banner_attachment( $mobile, $fallback );
	if ( null === $mob ) {
		$mob = ghahghah_resolve_archive_bundled_banner( 'blog', 'mobile', $fallback );
	}

	return array(
		'desktop' => $desk,
		'mobile'  => $mob,
	);
}
