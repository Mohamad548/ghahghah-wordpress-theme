<?php
/**
 * Products archive (CPT) helpers, query filters, and copy.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the main query is the products CPT archive.
 */
function ghahghah_is_products_archive(): bool {
	return is_post_type_archive( 'ghahghah_product' );
}

/**
 * Default copy and UI labels for the products archive.
 *
 * @return array<string, string>
 */
function ghahghah_products_archive_defaults(): array {
	return array(
		'hero_title'       => __( 'همه محصولات قهقهه', 'ghahghah' ),
		'hero_subtitle'    => __( 'تنوع طعم، کیفیت ثابت، لذت همیشگی', 'ghahghah' ),
		'hero_tagline'     => __( 'یه دنیا طعم!', 'ghahghah' ),
		'search_placeholder'=> __( 'جستجوی محصول، طعم یا دسته‌بندی...', 'ghahghah' ),
		'sort_label'       => __( 'مرتب‌سازی:', 'ghahghah' ),
		'cta_label'        => __( 'مشاهده محصول', 'ghahghah' ),
		'crumb_home'       => __( 'خانه', 'ghahghah' ),
		'crumb_current'    => __( 'محصولات', 'ghahghah' ),
		'empty_title'      => __( 'محصولی یافت نشد', 'ghahghah' ),
		'empty_text'       => __( 'عبارت جستجو یا فیلتر طعم را تغییر دهید.', 'ghahghah' ),
		'default_weight'   => __( '۶۰ گرم', 'ghahghah' ),
	);
}

/**
 * Flavor filter chips. Needle matches post_title (no taxonomy yet).
 *
 * @return array<string, array{label: string, icon: string, needle: string, short_label?: string}>
 */
function ghahghah_products_archive_flavor_chips(): array {
	return array(
		'all'            => array(
			'label'       => __( 'همه محصولات', 'ghahghah' ),
			'short_label' => __( 'همه', 'ghahghah' ),
			'icon'        => 'grid',
			'needle'      => '',
		),
		'cheese'         => array(
			'label'  => __( 'پنیری', 'ghahghah' ),
			'icon'   => 'cheese',
			'needle' => 'پنیر',
		),
		'ketchup'        => array(
			'label'  => __( 'کچاپ', 'ghahghah' ),
			'icon'   => 'ketchup',
			'needle' => 'کچاپ',
		),
		'lemon'          => array(
			'label'  => __( 'لیمویی', 'ghahghah' ),
			'icon'   => 'lemon',
			'needle' => 'لیمو',
		),
		'vinegar'        => array(
			'label'  => __( 'سرکه‌ای', 'ghahghah' ),
			'icon'   => 'vinegar',
			'needle' => 'سرکه',
		),
		'chicken'        => array(
			'label'  => __( 'مرغ', 'ghahghah' ),
			'icon'   => 'chicken',
			'needle' => 'مرغ',
		),
		'chili'          => array(
			'label'  => __( 'فلفلی', 'ghahghah' ),
			'icon'   => 'chili',
			'needle' => 'فلفل',
		),
		'shallot_yogurt' => array(
			'label'  => __( 'ماست موسیر', 'ghahghah' ),
			'icon'   => 'chip',
			'needle' => 'موسیر',
		),
		'pizza'          => array(
			'label'  => __( 'پیتزا', 'ghahghah' ),
			'icon'   => 'chip',
			'needle' => 'پیتزا',
		),
		'parsley_onion'  => array(
			'label'  => __( 'پیاز جعفری', 'ghahghah' ),
			'icon'   => 'chip',
			'needle' => 'جعفری',
		),
		'other'          => array(
			'label'  => __( 'سایر طعم‌ها', 'ghahghah' ),
			'icon'   => 'package',
			'needle' => '',
		),
	);
}

/**
 * Resolve archive flavor key for a product title (falls back to «other»).
 */
function ghahghah_products_archive_flavor_key_for_title( string $title ): string {
	$title = trim( $title );
	if ( '' === $title ) {
		return 'other';
	}

	foreach ( ghahghah_products_archive_flavor_chips() as $key => $chip ) {
		if ( in_array( $key, array( 'all', 'other' ), true ) ) {
			continue;
		}
		$needle = (string) ( $chip['needle'] ?? '' );
		if ( ghahghah_products_archive_title_has_needle( $title, $needle ) ) {
			return $key;
		}
	}

	return 'other';
}

/**
 * Resolve archive flavor key for a product post.
 */
function ghahghah_products_archive_flavor_key_for_product( WP_Post $product ): string {
	$title = get_the_title( $product );
	return ghahghah_products_archive_flavor_key_for_title( is_string( $title ) ? $title : '' );
}

/**
 * Archive URL filtered to a flavor chip key.
 */
function ghahghah_products_archive_flavor_url( string $flavor_key ): string {
	$flavor_key = sanitize_key( $flavor_key );
	$chips      = ghahghah_products_archive_flavor_chips();
	if ( ! isset( $chips[ $flavor_key ] ) || 'all' === $flavor_key ) {
		return ghahghah_products_archive_url();
	}
	return ghahghah_products_archive_url( array( 'gh_flavor' => $flavor_key ) );
}

/**
 * Sort options for the archive toolbar.
 *
 * @return array<string, string>
 */
function ghahghah_products_archive_sort_options(): array {
	return array(
		'popular' => __( 'محبوب‌ترین', 'ghahghah' ),
		'newest'  => __( 'جدیدترین', 'ghahghah' ),
		'alpha'   => __( 'الفبا', 'ghahghah' ),
	);
}

/**
 * Whether a product title contains a flavor needle.
 */
function ghahghah_products_archive_title_has_needle( string $title, string $needle ): bool {
	$needle = trim( $needle );
	if ( '' === $needle || '' === $title ) {
		return false;
	}
	if ( function_exists( 'mb_stripos' ) ) {
		return false !== mb_stripos( $title, $needle, 0, 'UTF-8' );
	}
	return false !== stripos( $title, $needle );
}

/**
 * Titles of all published products (cached per request).
 *
 * @return array<int, string>
 */
function ghahghah_products_archive_published_titles(): array {
	static $titles = null;
	if ( is_array( $titles ) ) {
		return $titles;
	}

	$titles = array();
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return $titles;
	}

	$query = new WP_Query(
		array(
			'post_type'              => 'ghahghah_product',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'orderby'                => 'none',
		)
	);

	foreach ( $query->posts as $post_id ) {
		$title = get_the_title( (int) $post_id );
		if ( is_string( $title ) && '' !== $title ) {
			$titles[] = $title;
		}
	}

	return $titles;
}

/**
 * Flavor chips that currently have at least one published product.
 * Always includes «همه»؛ «سایر» فقط وقتی محصولی خارج از طعم‌های شناخته‌شده باشد.
 *
 * @return array<string, array{label: string, icon: string, needle: string, short_label?: string}>
 */
function ghahghah_products_archive_available_flavor_chips(): array {
	$all     = ghahghah_products_archive_flavor_chips();
	$titles  = ghahghah_products_archive_published_titles();
	$visible = array(
		'all' => $all['all'],
	);

	foreach ( $all as $key => $chip ) {
		if ( in_array( $key, array( 'all', 'other' ), true ) ) {
			continue;
		}
		$needle = (string) ( $chip['needle'] ?? '' );
		foreach ( $titles as $title ) {
			if ( ghahghah_products_archive_title_has_needle( $title, $needle ) ) {
				$visible[ $key ] = $chip;
				break;
			}
		}
	}

	$needles   = ghahghah_products_archive_known_flavor_needles();
	$has_other = false;
	foreach ( $titles as $title ) {
		$matched = false;
		foreach ( $needles as $needle ) {
			if ( ghahghah_products_archive_title_has_needle( $title, $needle ) ) {
				$matched = true;
				break;
			}
		}
		if ( ! $matched ) {
			$has_other = true;
			break;
		}
	}

	if ( $has_other && isset( $all['other'] ) ) {
		$visible['other'] = $all['other'];
	}

	return $visible;
}

/**
 * Current archive filter state from the request.
 *
 * @return array{q: string, sort: string, flavor: string}
 */
function ghahghah_products_archive_request_state(): array {
	$sorts  = ghahghah_products_archive_sort_options();
	$chips  = ghahghah_products_archive_available_flavor_chips();
	$q      = isset( $_GET['gh_q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['gh_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sort   = isset( $_GET['gh_sort'] ) ? sanitize_key( wp_unslash( (string) $_GET['gh_sort'] ) ) : 'popular'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$flavor = isset( $_GET['gh_flavor'] ) ? sanitize_key( wp_unslash( (string) $_GET['gh_flavor'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! isset( $sorts[ $sort ] ) ) {
		$sort = 'popular';
	}
	if ( ! isset( $chips[ $flavor ] ) ) {
		$flavor = 'all';
	}

	return array(
		'q'      => $q,
		'sort'   => $sort,
		'flavor' => $flavor,
	);
}

/**
 * Build archive URL with optional query args (empty values dropped).
 *
 * @param array<string, string> $args Query args.
 */
function ghahghah_products_archive_url( array $args = array() ): string {
	$base = function_exists( 'ghahghah_get_products_archive_url' )
		? ghahghah_get_products_archive_url()
		: '';
	if ( '' === $base ) {
		$base = home_url( '/products/' );
	}

	$clean = array();
	foreach ( $args as $key => $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			continue;
		}
		if ( 'gh_sort' === $key && 'popular' === $value ) {
			continue;
		}
		if ( 'gh_flavor' === $key && 'all' === $value ) {
			continue;
		}
		$clean[ $key ] = $value;
	}

	return $clean ? add_query_arg( $clean, $base ) : $base;
}

/**
 * Absolute path to a products-archive icon SVG.
 */
function ghahghah_get_products_archive_icon_path( string $name ): string {
	$key = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $name ) );
	if ( ! is_string( $key ) || '' === $key ) {
		return '';
	}
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/products-archive/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo an inline products-archive SVG icon.
 *
 * @param string               $name Icon basename.
 * @param array<string, mixed> $args Optional class / modifiers.
 */
function ghahghah_the_products_archive_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_products_archive_icon_path( $name );
	if ( '' === $path ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$class = 'ghahghah-pa-icon';
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$class .= ' ' . $args['class'];
	}
	if ( ! empty( $args['modifiers'] ) && is_array( $args['modifiers'] ) ) {
		foreach ( $args['modifiers'] as $mod ) {
			if ( is_string( $mod ) && '' !== $mod ) {
				$class .= ' ghahghah-pa-icon--' . sanitize_html_class( $mod );
			}
		}
	}

	$svg = preg_replace( '/<svg\b/i', '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG.
}

/**
 * Short display title (prefer «طعم …»).
 */
function ghahghah_products_archive_card_title( WP_Post $product ): string {
	if ( function_exists( 'ghahghah_product_nav_label' ) ) {
		$label = ghahghah_product_nav_label( $product );
		if ( '' !== $label ) {
			return $label;
		}
	}
	$title = get_the_title( $product );
	return is_string( $title ) ? $title : '';
}

/**
 * Card subtitle: excerpt, then generated flavor line.
 */
function ghahghah_products_archive_card_subtitle( WP_Post $product ): string {
	$excerpt = get_the_excerpt( $product );
	if ( is_string( $excerpt ) ) {
		$excerpt = trim( wp_strip_all_tags( $excerpt ) );
		if ( '' !== $excerpt ) {
			return $excerpt;
		}
	}

	$short = ghahghah_products_archive_card_title( $product );
	if ( '' === $short ) {
		return __( 'اسنک ذرت قهقهه', 'ghahghah' );
	}

	/* translators: %s: short flavor title e.g. طعم پنیری */
	return sprintf( __( 'اسنک ذرت با %s', 'ghahghah' ), $short );
}

/**
 * Net weight label for a product card (empty when unknown and no default).
 */
function ghahghah_products_archive_card_weight( WP_Post $product ): string {
	$meta = get_post_meta( $product->ID, '_ghahghah_net_weight', true );
	if ( is_string( $meta ) && '' !== trim( $meta ) ) {
		return trim( $meta );
	}

	$defaults = ghahghah_products_archive_defaults();
	return (string) $defaults['default_weight'];
}

/**
 * Formatted «N محصول یافت شد» count string.
 */
function ghahghah_products_archive_found_label( int $count ): string {
	/* translators: %d: number of products found */
	return sprintf( _n( '%d محصول یافت شد', '%d محصول یافت شد', $count, 'ghahghah' ), $count );
}

/**
 * Known flavor needles (excluding all/other) for the «سایر» filter.
 *
 * @return array<int, string>
 */
function ghahghah_products_archive_known_flavor_needles(): array {
	$needles = array();
	foreach ( ghahghah_products_archive_flavor_chips() as $key => $chip ) {
		if ( in_array( $key, array( 'all', 'other' ), true ) ) {
			continue;
		}
		$needle = trim( (string) ( $chip['needle'] ?? '' ) );
		if ( '' !== $needle ) {
			$needles[] = $needle;
		}
	}
	return $needles;
}

/**
 * Main-query adjustments for products archive filters.
 *
 * @param WP_Query $query Query.
 */
function ghahghah_products_archive_pre_get_posts( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! $query->is_post_type_archive( 'ghahghah_product' ) ) {
		return;
	}

	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', 24 );

	$state = ghahghah_products_archive_request_state();

	if ( '' !== $state['q'] ) {
		$query->set( 's', $state['q'] );
	}

	switch ( $state['sort'] ) {
		case 'newest':
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
			break;
		case 'alpha':
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
			break;
		case 'popular':
		default:
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				)
			);
			break;
	}

	if ( 'all' !== $state['flavor'] ) {
		$query->set( 'ghahghah_flavor_key', $state['flavor'] );
	}
}
add_action( 'pre_get_posts', 'ghahghah_products_archive_pre_get_posts' );

/**
 * Title-based flavor filtering (extensible until a flavor taxonomy exists).
 *
 * @param string   $where SQL WHERE.
 * @param WP_Query $query Query.
 */
function ghahghah_products_archive_posts_where( string $where, WP_Query $query ): string {
	$key = $query->get( 'ghahghah_flavor_key' );
	if ( ! is_string( $key ) || '' === $key ) {
		return $where;
	}

	$chips = ghahghah_products_archive_flavor_chips();
	if ( ! isset( $chips[ $key ] ) ) {
		return $where;
	}

	global $wpdb;

	if ( 'other' === $key ) {
		foreach ( ghahghah_products_archive_known_flavor_needles() as $needle ) {
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.post_title NOT LIKE %s",
				'%' . $wpdb->esc_like( $needle ) . '%'
			);
		}
		return $where;
	}

	$needle = trim( (string) ( $chips[ $key ]['needle'] ?? '' ) );
	if ( '' === $needle ) {
		return $where;
	}

	$where .= $wpdb->prepare(
		" AND {$wpdb->posts}.post_title LIKE %s",
		'%' . $wpdb->esc_like( $needle ) . '%'
	);

	return $where;
}
add_filter( 'posts_where', 'ghahghah_products_archive_posts_where', 10, 2 );

/**
 * Point primary «محصولات» menu item at the CPT archive.
 *
 * @return bool True when a change was made.
 */
function ghahghah_sync_primary_products_archive_link(): bool {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return false;
	}

	$menu_id = ghahghah_get_nav_menu_id_for_location( 'primary' );
	if ( $menu_id <= 0 ) {
		return false;
	}

	$parent_id = ghahghah_find_primary_products_menu_item_id( $menu_id );
	if ( $parent_id <= 0 ) {
		return false;
	}

	$type   = (string) get_post_meta( $parent_id, '_menu_item_type', true );
	$object = (string) get_post_meta( $parent_id, '_menu_item_object', true );
	if ( 'post_type_archive' === $type && 'ghahghah_product' === $object ) {
		$source = (string) get_post_meta( $parent_id, GHAHGHAH_NAV_SYNC_META, true );
		if ( 'products_archive' !== $source ) {
			update_post_meta( $parent_id, GHAHGHAH_NAV_SYNC_META, 'products_archive' );
		}
		return false;
	}

	$updated = wp_update_nav_menu_item(
		$menu_id,
		$parent_id,
		array(
			'menu-item-title'  => __( 'محصولات', 'ghahghah' ),
			'menu-item-type'   => 'post_type_archive',
			'menu-item-object' => 'ghahghah_product',
			'menu-item-status' => 'publish',
		)
	);

	if ( is_wp_error( $updated ) || $updated <= 0 ) {
		return false;
	}

	update_post_meta( (int) $parent_id, GHAHGHAH_NAV_SYNC_META, 'products_archive' );
	return true;
}

/**
 * Wire primary «محصولات» menu item to the CPT archive (admin / once per request is fine).
 */
function ghahghah_products_archive_after_nav_sync(): void {
	if ( wp_installing() ) {
		return;
	}
	ghahghah_sync_primary_products_archive_link();
}
add_action( 'init', 'ghahghah_products_archive_after_nav_sync', 35 );

/**
 * Bundled default archive banner filenames (theme assets).
 *
 * @return array<string, array{dir: string, desktop: string, mobile: string}>
 */
function ghahghah_archive_bundled_banner_files(): array {
	return array(
		'blog'     => array(
			'dir'     => 'blog-archive/banners',
			'desktop' => 'articles-archive-banner-desktop.webp',
			'mobile'  => 'articles-archive-banner-mobile.webp',
		),
		'products' => array(
			'dir'     => 'products-archive/banners',
			'desktop' => 'products-archive-banner-desktop.webp',
			'mobile'  => 'products-archive-banner-mobile.webp',
		),
	);
}

/**
 * Public URL for a bundled archive banner file.
 *
 * @param string $archive blog|products.
 * @param string $slot    desktop|mobile.
 */
function ghahghah_archive_bundled_banner_url( string $archive, string $slot ): string {
	$files = ghahghah_archive_bundled_banner_files();
	if ( ! isset( $files[ $archive ][ $slot ] ) ) {
		return '';
	}

	$dir  = (string) $files[ $archive ]['dir'];
	$file = (string) $files[ $archive ][ $slot ];

	return GHAHGHAH_THEME_URI . '/assets/images/' . $dir . '/' . rawurlencode( $file );
}

/**
 * Resolve a bundled archive banner from theme assets.
 *
 * @param string $archive blog|products.
 * @param string $slot    desktop|mobile.
 * @param string $alt     Alt text.
 * @return array{src: string, srcset: string, width: int, height: int, alt: string}|null
 */
function ghahghah_resolve_archive_bundled_banner( string $archive, string $slot, string $alt ): ?array {
	$files = ghahghah_archive_bundled_banner_files();
	if ( ! isset( $files[ $archive ][ $slot ] ) ) {
		return null;
	}

	$dir  = (string) $files[ $archive ]['dir'];
	$file = (string) $files[ $archive ][ $slot ];
	$path = GHAHGHAH_THEME_DIR . '/assets/images/' . $dir . '/' . $file;
	$url  = ghahghah_archive_bundled_banner_url( $archive, $slot );

	if ( ! is_readable( $path ) || '' === $url ) {
		return null;
	}

	$data = function_exists( 'ghahghah_hero_file_image_data' )
		? ghahghah_hero_file_image_data( $path, $url )
		: null;

	if ( ! is_array( $data ) || empty( $data['url'] ) ) {
		return null;
	}

	return array(
		'src'    => (string) $data['url'],
		'srcset' => (string) ( $data['srcset'] ?? '' ),
		'width'  => max( 1, (int) ( $data['width'] ?? 1600 ) ),
		'height' => max( 1, (int) ( $data['height'] ?? 600 ) ),
		'alt'    => $alt,
	);
}

/**
 * Resolve a media-library image for archive banners.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $fallback_alt  Alt text when attachment has none.
 * @return array{src: string, srcset: string, width: int, height: int, alt: string}|null
 */
function ghahghah_resolve_archive_banner_attachment( int $attachment_id, string $fallback_alt ): ?array {
	if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
		return null;
	}

	$url = wp_get_attachment_image_url( $attachment_id, 'full' );
	if ( ! is_string( $url ) || '' === $url ) {
		$url = (string) wp_get_attachment_image_url( $attachment_id, 'large' );
	}
	if ( '' === $url ) {
		return null;
	}

	$meta   = wp_get_attachment_metadata( $attachment_id );
	$width  = isset( $meta['width'] ) ? absint( $meta['width'] ) : 1600;
	$height = isset( $meta['height'] ) ? absint( $meta['height'] ) : 600;
	$alt    = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );

	return array(
		'src'    => $url,
		'srcset' => is_string( $srcset ) ? $srcset : '',
		'width'  => $width > 0 ? $width : 1600,
		'height' => $height > 0 ? $height : 600,
		'alt'    => '' !== $alt ? $alt : $fallback_alt,
	);
}

/**
 * Theme-mod keys for products archive custom banner.
 *
 * @return array{desktop: string, mobile: string}
 */
function ghahghah_products_archive_banner_mod_keys(): array {
	return array(
		'desktop' => 'ghahghah_products_archive_banner_desktop_id',
		'mobile'  => 'ghahghah_products_archive_banner_mobile_id',
	);
}

/**
 * Custom products archive banner (null = use designed default hero).
 *
 * @return array{desktop: array{src: string, srcset: string, width: int, height: int, alt: string}, mobile: array{src: string, srcset: string, width: int, height: int, alt: string}|null}|null
 */
function ghahghah_get_products_archive_custom_banner(): ?array {
	$keys     = ghahghah_products_archive_banner_mod_keys();
	$desktop  = absint( get_theme_mod( $keys['desktop'], 0 ) );
	$mobile   = absint( get_theme_mod( $keys['mobile'], 0 ) );
	$fallback = __( 'بنر آرشیو محصولات قهقهه', 'ghahghah' );
	$desk     = ghahghah_resolve_archive_banner_attachment( $desktop, $fallback );

	if ( null === $desk ) {
		$desk = ghahghah_resolve_archive_bundled_banner( 'products', 'desktop', $fallback );
	}

	if ( null === $desk ) {
		return null;
	}

	$mob = ghahghah_resolve_archive_banner_attachment( $mobile, $fallback );
	if ( null === $mob ) {
		$mob = ghahghah_resolve_archive_bundled_banner( 'products', 'mobile', $fallback );
	}

	return array(
		'desktop' => $desk,
		'mobile'  => $mob,
	);
}
