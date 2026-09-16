<?php
/**
 * Single article (post) helpers: ToC, reading time, related, icons.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether current view is a single blog post.
 */
function ghahghah_is_single_article(): bool {
	return is_singular( 'post' );
}

/**
 * Default copy for single article chrome.
 *
 * @return array<string, string>
 */
function ghahghah_single_article_defaults(): array {
	return array(
		'hero_overlay'     => __( 'کیفیت از دل طبیعت تا لبخند شما', 'ghahghah' ),
		'toc_title'        => __( 'فهرست مطالب', 'ghahghah' ),
		'related_products' => __( 'محصولات مرتبط', 'ghahghah' ),
		'all_products'     => __( 'مشاهده همه محصولات', 'ghahghah' ),
		'cta_title'        => __( 'با قهقهه، یک همکاری خوش‌طعم را آغاز کنید!', 'ghahghah' ),
		'cta_wholesale'    => __( 'ثبت درخواست عمده', 'ghahghah' ),
		'cta_agency'       => __( 'درخواست نمایندگی', 'ghahghah' ),
		'related_title'    => __( 'مقالات مرتبط', 'ghahghah' ),
		'crumb_blog'       => __( 'وبلاگ', 'ghahghah' ),
		'read_suffix'      => __( 'دقیقه مطالعه', 'ghahghah' ),
	);
}

/**
 * Bundled image URL under assets/images/single-article/.
 */
function ghahghah_single_article_image_url( string $file ): string {
	$file = ltrim( $file, '/' );
	return GHAHGHAH_THEME_URI . '/assets/images/single-article/' . $file;
}

/**
 * Hero image: featured if present, else real-snack banner fallback.
 *
 * @return array{url: string, id: int, alt: string}
 */
function ghahghah_single_article_hero_image( int $post_id = 0 ): array {
	$post_id = $post_id > 0 ? $post_id : (int) get_the_ID();
	$thumb   = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb > 0 ) {
		$url = wp_get_attachment_image_url( $thumb, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			$alt = get_post_meta( $thumb, '_wp_attachment_image_alt', true );
			return array(
				'url' => $url,
				'id'  => $thumb,
				'alt' => is_string( $alt ) ? $alt : '',
			);
		}
	}

	return array(
		'url' => ghahghah_single_article_image_url( 'article-hero-banner-real-snack-optimized.webp' ),
		'id'  => 0,
		'alt' => '',
	);
}

/**
 * Estimated reading time in minutes (min 1).
 */
function ghahghah_single_article_reading_minutes( int $post_id = 0 ): int {
	$post_id = $post_id > 0 ? $post_id : (int) get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	if ( ! is_string( $content ) || '' === $content ) {
		return 1;
	}
	$text  = wp_strip_all_tags( strip_shortcodes( $content ) );
	$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	$count = is_array( $words ) ? count( $words ) : 0;
	return max( 1, (int) ceil( $count / 180 ) );
}

/**
 * Absolute path to a single-article icon.
 */
function ghahghah_get_single_article_icon_path( string $name ): string {
	$key = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $name ) );
	if ( ! is_string( $key ) || '' === $key ) {
		return '';
	}
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/single-article/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo single-article SVG icon.
 *
 * @param string               $name Icon basename.
 * @param array<string, mixed> $args Args.
 */
function ghahghah_the_single_article_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_single_article_icon_path( $name );
	if ( '' === $path ) {
		return;
	}
	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}
	$class = 'ghahghah-sa-icon';
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$class .= ' ' . $args['class'];
	}
	$svg = preg_replace( '/<svg\b/i', '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Build a stable heading id from text.
 */
function ghahghah_single_article_heading_id( string $text, int $index ): string {
	$base = sanitize_title( $text );
	if ( '' === $base ) {
		$base = 'section';
	}
	return 'sa-' . $base . '-' . $index;
}

/**
 * Extract h2/h3 headings from HTML for ToC.
 *
 * @return array<int, array{id: string, text: string, level: int}>
 */
function ghahghah_single_article_parse_headings( string $html ): array {
	$items = array();
	if ( '' === $html ) {
		return $items;
	}
	if ( ! preg_match_all( '/<h([23])\b([^>]*)>(.*?)<\/h\1>/isu', $html, $matches, PREG_SET_ORDER ) ) {
		return $items;
	}
	$index = 0;
	foreach ( $matches as $match ) {
		++$index;
		$level = (int) $match[1];
		$attrs = (string) $match[2];
		$text  = trim( wp_strip_all_tags( $match[3] ) );
		if ( '' === $text ) {
			continue;
		}
		$id = '';
		if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $attrs, $id_match ) ) {
			$id = $id_match[1];
		}
		if ( '' === $id ) {
			$id = ghahghah_single_article_heading_id( $text, $index );
		}
		$items[] = array(
			'id'    => $id,
			'text'  => $text,
			'level' => $level,
		);
	}
	return $items;
}

/**
 * Inject id attributes into h2/h3 in post content for ToC anchors.
 *
 * @param string $content Content HTML.
 */
function ghahghah_single_article_content_with_heading_ids( string $content ): string {
	if ( '' === $content ) {
		return $content;
	}

	$index = 0;
	return (string) preg_replace_callback(
		'/<h([23])\b([^>]*)>(.*?)<\/h\1>/isu',
		static function ( array $m ) use ( &$index ): string {
			++$index;
			$level = $m[1];
			$attrs = $m[2];
			$inner = $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );
			if ( preg_match( '/\bid=["\']/i', $attrs ) ) {
				return $m[0];
			}
			$id = ghahghah_single_article_heading_id( $text, $index );
			return '<h' . $level . $attrs . ' id="' . esc_attr( $id ) . '">' . $inner . '</h' . $level . '>';
		},
		$content
	);
}

/**
 * Filtered the_content for single articles: heading IDs + optional inline real images after first/second h2.
 *
 * @param string $content Content.
 */
function ghahghah_single_article_filter_content( string $content ): string {
	if ( ! ghahghah_is_single_article() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$content = ghahghah_single_article_content_with_heading_ids( $content );

	// Avoid double-injecting on subsequent filters.
	if ( false !== strpos( $content, 'ghahghah-single-article__inline-figure' ) ) {
		return $content;
	}

	$hands   = ghahghah_single_article_image_url( 'article-inline-hands-snack-corn-optimized.webp' );
	$factory = ghahghah_single_article_image_url( 'article-inline-factory-line-real-snack-optimized.webp' );
	$count   = 0;

	return (string) preg_replace_callback(
		'/(<\/h2>)/i',
		static function ( array $m ) use ( &$count, $hands, $factory ): string {
			++$count;
			$figure = '';
			if ( 1 === $count ) {
				$figure = '<figure class="ghahghah-single-article__inline-figure">'
					. '<img src="' . esc_url( $hands ) . '" alt="" width="1448" height="1086" loading="lazy" decoding="async" />'
					. '</figure>';
			} elseif ( 2 === $count ) {
				$figure = '<figure class="ghahghah-single-article__inline-figure">'
					. '<img src="' . esc_url( $factory ) . '" alt="" width="1448" height="1086" loading="lazy" decoding="async" />'
					. '</figure>';
			}
			return $m[1] . $figure;
		},
		$content,
		2
	);
}
add_filter( 'the_content', 'ghahghah_single_article_filter_content', 12 );

/**
 * Related published posts.
 *
 * @return array<int, WP_Post>
 */
function ghahghah_get_related_articles( int $post_id, int $limit = 3 ): array {
	$cat_ids = wp_get_post_categories( $post_id );
	$args    = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);
	if ( ! empty( $cat_ids ) ) {
		$args['category__in'] = $cat_ids;
	}

	$query = new WP_Query( $args );
	$posts = $query->posts;

	if ( count( $posts ) < $limit ) {
		$more = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $limit,
				'post__not_in'        => array_merge( array( $post_id ), wp_list_pluck( $posts, 'ID' ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		$posts = array_merge( $posts, $more->posts );
		$posts = array_slice( $posts, 0, $limit );
	}

	return $posts;
}

/**
 * A few published products for the article sidebar.
 *
 * @return array<int, WP_Post>
 */
function ghahghah_single_article_sidebar_products( int $limit = 2 ): array {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return array();
	}
	$query = new WP_Query(
		array(
			'post_type'              => 'ghahghah_product',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'orderby'                => 'rand',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
		)
	);
	return $query->posts;
}
