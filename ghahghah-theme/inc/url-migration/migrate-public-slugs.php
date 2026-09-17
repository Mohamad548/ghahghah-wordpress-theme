<?php
/**
 * Public URL slug migration (Persian → English) with 301 map.
 *
 * Resolves content by title / existing slug / catalog key — does not hardcode env IDs.
 *
 * Usage (wp-env):
 *   GHAHGHAH_MIGRATE_MODE=dry-run wp eval-file .../migrate-public-slugs.php
 *   GHAHGHAH_MIGRATE_MODE=apply GHAHGHAH_MIGRATE_FLUSH=1 wp eval-file .../migrate-public-slugs.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

if ( ! class_exists( 'WP_CLI' ) ) {
	echo "WP-CLI required.\n";
	exit( 1 );
}

$args    = array();
$dry_run = true;
$flush   = false;
if ( isset( $GLOBALS['argv'] ) && is_array( $GLOBALS['argv'] ) ) {
	$args = $GLOBALS['argv'];
}
$env_mode = isset( $_SERVER['GHAHGHAH_MIGRATE_MODE'] ) ? (string) $_SERVER['GHAHGHAH_MIGRATE_MODE'] : getenv( 'GHAHGHAH_MIGRATE_MODE' );
if ( is_string( $env_mode ) && 'apply' === $env_mode ) {
	$dry_run = false;
}
if ( is_string( $env_mode ) && 'dry-run' === $env_mode ) {
	$dry_run = true;
}
foreach ( $args as $arg ) {
	if ( '--apply' === $arg ) {
		$dry_run = false;
	}
	if ( '--dry-run' === $arg ) {
		$dry_run = true;
	}
	if ( '--flush' === $arg ) {
		$flush = true;
	}
}
if ( '1' === (string) getenv( 'GHAHGHAH_MIGRATE_FLUSH' ) ) {
	$flush = true;
}

/**
 * Find a page by any of the given slugs or exact title.
 *
 * @param array<int, string> $slugs Slug candidates.
 * @param string             $title Exact title.
 */
$find_page = static function ( array $slugs, string $title ): int {
	foreach ( $slugs as $slug ) {
		$slug = (string) $slug;
		if ( '' === $slug ) {
			continue;
		}
		$page = get_page_by_path( rawurldecode( $slug ) );
		if ( $page instanceof WP_Post && 'page' === $page->post_type ) {
			return (int) $page->ID;
		}
		// Encoded slug stored as post_name.
		$q = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => 'any',
				'name'           => $slug,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $q->posts[0] ) ) {
			return (int) $q->posts[0];
		}
	}
	if ( '' !== $title ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $q->posts[0] ) ) {
			return (int) $q->posts[0];
		}
	}
	return 0;
};

/**
 * Path relative to home (leading slash, trailing slash for directories).
 */
$path_of = static function ( string $url ): string {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( '' === $path || '/' === $path ) {
		return '/';
	}
	return trailingslashit( $path );
};

/**
 * Ensure desired post_name; return old→new path pair when changed.
 *
 * @return array{id:int,old:string,new:string,changed:bool,skipped?:string}|null
 */
$rename = static function ( int $id, string $desired_slug, string $post_type ) use ( $path_of, $dry_run ): ?array {
	$post = get_post( $id );
	if ( ! $post instanceof WP_Post || $post->post_type !== $post_type ) {
		return null;
	}
	$old_url  = get_permalink( $id );
	$old_path = $path_of( (string) $old_url );
	$current  = (string) $post->post_name;
	$desired  = sanitize_title( $desired_slug );
	if ( '' === $desired ) {
		return array(
			'id'      => $id,
			'old'     => $old_path,
			'new'     => $old_path,
			'changed' => false,
			'skipped' => 'empty-desired-slug',
		);
	}
	if ( $current === $desired ) {
		$new_path = $path_of( (string) get_permalink( $id ) );
		return array(
			'id'      => $id,
			'old'     => $old_path,
			'new'     => $new_path,
			'changed' => false,
			'skipped' => 'already',
		);
	}

	// Collision check.
	$conflict = get_page_by_path( $desired, OBJECT, $post_type );
	if ( $conflict instanceof WP_Post && (int) $conflict->ID !== $id ) {
		return array(
			'id'      => $id,
			'old'     => $old_path,
			'new'     => $old_path,
			'changed' => false,
			'skipped' => 'conflict:' . (int) $conflict->ID,
		);
	}

	if ( ! $dry_run ) {
		$updated = wp_update_post(
			array(
				'ID'        => $id,
				'post_name' => $desired,
			),
			true
		);
		if ( is_wp_error( $updated ) ) {
			return array(
				'id'      => $id,
				'old'     => $old_path,
				'new'     => $old_path,
				'changed' => false,
				'skipped' => $updated->get_error_message(),
			);
		}
		clean_post_cache( $id );
	}

	$new_url  = $dry_run
		? home_url( user_trailingslashit( $desired ) )
		: get_permalink( $id );
	if ( 'ghahghah_product' === $post_type ) {
		$archive = (string) get_post_type_archive_link( 'ghahghah_product' );
		$base    = $archive ? untrailingslashit( (string) wp_parse_url( $archive, PHP_URL_PATH ) ) : '/products';
		$new_url = home_url( trailingslashit( $base . '/' . $desired ) );
	} elseif ( 'page' === $post_type ) {
		$new_url = home_url( user_trailingslashit( $desired ) );
	}

	return array(
		'id'      => $id,
		'old'     => $old_path,
		'new'     => $path_of( (string) $new_url ),
		'changed' => true,
		'title'   => get_the_title( $id ),
		'from'    => $current,
		'to'      => $desired,
	);
};

$map_rows   = array();
$redirects  = array();
$report     = array(
	'mode'    => $dry_run ? 'dry-run' : 'apply',
	'at'      => gmdate( 'c' ),
	'home'    => home_url( '/' ),
	'changes' => array(),
	'skips'   => array(),
	'redirects' => array(),
);

// --- Pages (resolve by title / alt slug; target English slug) ---
$page_specs = array(
	array(
		'title'  => 'درخواست خرید عمده',
		'slugs'  => array( 'wholesale', 'درخواست-خرید-عمده' ),
		'target' => 'wholesale',
	),
	array(
		'title'  => 'درخواست نمایندگی',
		'slugs'  => array( 'agency', 'representation', 'درخواست-نمایندگی' ),
		'target' => 'agency',
	),
	array(
		'title'  => 'تماس با ما',
		'slugs'  => array( 'contact', 'تماس-با-ما', 'contact-us' ),
		'target' => 'contact',
	),
	array(
		'title'  => 'کارخانه',
		'slugs'  => array( 'factory', 'کارخانه', 'معرفی-کارخانه' ),
		'target' => 'factory',
	),
	array(
		'title'  => 'مقالات',
		'slugs'  => array( 'articles', 'مقالات', 'blog', 'maghalat' ),
		'target' => 'articles',
	),
	array(
		'title'  => 'پرسش‌های متداول',
		'slugs'  => array( 'faq', 'پرسش-های-متداول', 'پرسشهای-متداول' ),
		'target' => 'faq',
	),
	array(
		'title'  => 'حریم خصوصی',
		'slugs'  => array( 'privacy-policy', 'privacy', 'حریم-خصوصی' ),
		'target' => 'privacy-policy',
	),
);

foreach ( $page_specs as $spec ) {
	$id = $find_page( $spec['slugs'], $spec['title'] );
	if ( $id <= 0 ) {
		$report['skips'][] = array(
			'kind'  => 'page-missing',
			'title' => $spec['title'],
			'target'=> $spec['target'],
		);
		continue;
	}
	$row = $rename( $id, $spec['target'], 'page' );
	if ( ! $row ) {
		continue;
	}
	$report['changes'][] = array_merge( array( 'kind' => 'page' ), $row );
	if ( ! empty( $row['changed'] ) || ( isset( $row['old'], $row['new'] ) && $row['old'] !== $row['new'] ) ) {
		if ( $row['old'] !== $row['new'] && '/' !== $row['old'] ) {
			$redirects[ $row['old'] ] = $row['new'];
		}
	} elseif ( ! empty( $row['skipped'] ) && 'already' === $row['skipped'] && $row['old'] !== $row['new'] ) {
		$redirects[ $row['old'] ] = $row['new'];
	}
	// Always record historical Persian path → new when we know prior slug candidates.
	foreach ( $spec['slugs'] as $hist ) {
		if ( ! preg_match( '/[^\x00-\x7F]/', $hist ) && false === strpos( $hist, '%' ) ) {
			continue; // ascii alt not needed as redirect source unless it was live
		}
		$hist_path = trailingslashit( '/' . rawurlencode( str_replace( '%', '', rawurldecode( $hist ) ) ) );
		// Better: build path from decoded slug the way WP stores it.
		$hist_path = user_trailingslashit( '/' . $hist );
		if ( $hist_path !== $row['new'] && '/' !== $hist_path ) {
			$redirects[ $hist_path ] = $row['new'];
			// Also percent-encoded form.
			$parts = array_map( 'rawurlencode', explode( '/', trim( $hist, '/' ) ) );
			$enc   = '/' . implode( '/', $parts ) . '/';
			$redirects[ $enc ] = $row['new'];
		}
	}
}

// Legacy products listing page → archive (do not claim slug "products").
$legacy_products = $find_page( array( 'محصولات', 'products-legacy' ), 'محصولات' );
$archive_url     = get_post_type_archive_link( 'ghahghah_product' );
$archive_path    = $archive_url ? $path_of( (string) $archive_url ) : '/products/';
if ( $legacy_products > 0 ) {
	$old = $path_of( (string) get_permalink( $legacy_products ) );
	$redirects[ $old ] = $archive_path;
	$redirects['/%d9%85%d8%ad%d8%b5%d9%88%d9%84%d8%a7%d8%aa/'] = $archive_path;
	$redirects['/محصولات/'] = $archive_path;
	$row = $rename( $legacy_products, 'products-legacy', 'page' );
	if ( $row ) {
		$report['changes'][] = array_merge( array( 'kind' => 'legacy-products-page' ), $row );
		// Intermediate English slug must also 301 to archive (no duplicate public page).
		$redirects['/products-legacy/'] = $archive_path;
		if ( ! empty( $row['changed'] ) && $row['old'] !== $row['new'] ) {
			$redirects[ $row['old'] ] = $archive_path;
		}
		if ( ! $dry_run ) {
			$post = get_post( $legacy_products );
			if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
				wp_update_post(
					array(
						'ID'          => $legacy_products,
						'post_status' => 'private',
					)
				);
				$report['changes'][] = array(
					'kind'   => 'legacy-products-private',
					'id'     => $legacy_products,
					'status' => 'private',
				);
			}
		}
	}
}

// Stale flavor / listing pages → product archive filters. Explicit English slugs only
// (never sanitize_title('legacy-' . Persian title)).
$flavor_pages = array(
	array(
		'title'       => 'همه محصولات',
		'slugs'       => array( 'همه-محصولات', 'legacy-all-products', 'legacy-%d9%87%d9%85%d9%87-%d9%85%d8%ad%d8%b5%d9%88%d9%84%d8%a7%d8%aa' ),
		'target_slug' => 'legacy-all-products',
		'target'      => $archive_path,
	),
	array(
		'title'       => 'طعم پیتزا',
		'slugs'       => array( 'طعم-پیتزا', 'legacy-flavor-pizza', 'legacy-%d8%b7%d8%b9%d9%85-%d9%be%db%8c%d8%aa%d8%b2%d8%a7' ),
		'target_slug' => 'legacy-flavor-pizza',
		'target'      => $archive_path . '?gh_flavor=pizza',
	),
	array(
		'title'       => 'طعم لیمویی',
		'slugs'       => array( 'طعم-لیمویی', 'legacy-flavor-lemon', 'legacy-%d8%b7%d8%b9%d9%85-%d9%84%db%8c%d9%85%d9%88%db%8c%db%8c' ),
		'target_slug' => 'legacy-flavor-lemon',
		'target'      => $archive_path . '?gh_flavor=lemon',
	),
);
foreach ( $flavor_pages as $fp ) {
	$id = $find_page( $fp['slugs'], $fp['title'] );
	if ( $id <= 0 ) {
		continue;
	}
	$old = $path_of( (string) get_permalink( $id ) );
	$redirects[ $old ] = $fp['target'];
	$row = $rename( $id, $fp['target_slug'], 'page' );
	if ( $row ) {
		$report['changes'][] = array_merge(
			array(
				'kind'        => 'legacy-flavor-page',
				'redirect_to' => $fp['target'],
			),
			$row
		);
		// Old Persian + prior bad intermediate + final English intermediate → destination.
		$redirects[ $row['old'] ] = $fp['target'];
		$redirects[ '/' . $fp['target_slug'] . '/' ] = $fp['target'];
		foreach ( $fp['slugs'] as $hist ) {
			if ( $hist === $fp['target_slug'] ) {
				continue;
			}
			$hist_path = user_trailingslashit( '/' . $hist );
			if ( $hist_path !== $fp['target'] && '/' !== $hist_path ) {
				$redirects[ $hist_path ] = $fp['target'];
				$parts = array_map( 'rawurlencode', explode( '/', trim( rawurldecode( $hist ), '/' ) ) );
				$enc   = '/' . implode( '/', $parts ) . '/';
				$redirects[ $enc ] = $fp['target'];
			}
		}
		if ( ! $dry_run ) {
			$post = get_post( $id );
			if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'private',
					)
				);
				$report['changes'][] = array(
					'kind'   => 'legacy-flavor-private',
					'id'     => $id,
					'slug'   => $fp['target_slug'],
					'status' => 'private',
				);
			}
		}
	}
}

/**
 * Point nav menu custom/page links that still target legacy paths at final destinations.
 */
$retarget_menus = static function ( array $path_to_final ) use ( $dry_run, &$report ): void {
	$menus = wp_get_nav_menus();
	if ( ! is_array( $menus ) ) {
		return;
	}
	foreach ( $menus as $menu ) {
		if ( ! $menu instanceof WP_Term ) {
			continue;
		}
		$items = wp_get_nav_menu_items( $menu->term_id );
		if ( ! is_array( $items ) ) {
			continue;
		}
		foreach ( $items as $item ) {
			if ( ! $item instanceof WP_Post ) {
				continue;
			}
			$type = (string) $item->type;
			$url  = (string) $item->url;
			if ( 'custom' !== $type && 'post_type' !== $type ) {
				continue;
			}
			$path = (string) wp_parse_url( $url, PHP_URL_PATH );
			if ( '' === $path ) {
				continue;
			}
			$path = trailingslashit( rawurldecode( $path ) );
			$enc_parts = array_map( 'rawurlencode', explode( '/', trim( rawurldecode( $path ), '/' ) ) );
			$enc_path  = '/' . implode( '/', $enc_parts ) . '/';
			$final     = $path_to_final[ $path ] ?? $path_to_final[ $enc_path ] ?? null;
			if ( ! is_string( $final ) || '' === $final ) {
				// Also match object pages that are now private legacy.
				if ( 'post_type' === $type && 'page' === (string) $item->object ) {
					$obj_id = (int) $item->object_id;
					$obj    = get_post( $obj_id );
					if ( $obj instanceof WP_Post ) {
						$obj_name = (string) $obj->post_name;
						if ( 'products-legacy' === $obj_name || 0 === strpos( $obj_name, 'legacy-' ) ) {
							$obj_path = '/' . ( 0 === strpos( $obj_name, 'legacy-' ) || 'products-legacy' === $obj_name ? $obj_name : $obj_name ) . '/';
							// Prefer explicit English intermediate key.
							if ( isset( $path_to_final[ '/' . $obj_name . '/' ] ) ) {
								$final = $path_to_final[ '/' . $obj_name . '/' ];
							} else {
								$obj_path = trailingslashit( (string) wp_parse_url( (string) get_permalink( $obj ), PHP_URL_PATH ) );
								$final    = $path_to_final[ $obj_path ] ?? null;
							}
						} else {
							$obj_path = trailingslashit( (string) wp_parse_url( (string) get_permalink( $obj ), PHP_URL_PATH ) );
							$final    = $path_to_final[ $obj_path ] ?? null;
						}
					}
				}
			}
			if ( ! is_string( $final ) || '' === $final ) {
				continue;
			}
			$final_url = ( 0 === strpos( $final, 'http' ) ) ? $final : home_url( $final );
			$cur_path  = trailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );
			$fin_path  = trailingslashit( (string) wp_parse_url( $final_url, PHP_URL_PATH ) );
			$cur_q     = (string) wp_parse_url( $url, PHP_URL_QUERY );
			$fin_q     = (string) wp_parse_url( $final_url, PHP_URL_QUERY );
			if ( $cur_path === $fin_path && $cur_q === $fin_q && 'custom' === $type ) {
				continue;
			}
			$report['changes'][] = array(
				'kind'    => 'menu-retarget',
				'menu'    => (int) $menu->term_id,
				'item'    => (int) $item->ID,
				'from'    => $url,
				'to'      => $final_url,
				'changed' => ! $dry_run,
			);
			if ( ! $dry_run ) {
				wp_update_nav_menu_item(
					(int) $menu->term_id,
					(int) $item->ID,
					array(
						'menu-item-title'  => $item->title,
						'menu-item-url'    => $final_url,
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
					)
				);
			}
		}
	}
};

// --- Products via catalog key meta ---
$products = get_posts(
	array(
		'post_type'      => 'ghahghah_product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	)
);
foreach ( $products as $product ) {
	if ( ! $product instanceof WP_Post ) {
		continue;
	}
	$key = (string) get_post_meta( $product->ID, '_ghahghah_catalog_key', true );
	if ( '' === $key ) {
		// Derive from known title patterns.
		$title = get_the_title( $product );
		$map   = array(
			'لیمویی'     => 'corn-pellet-lemon',
			'سرک'       => 'corn-pellet-vinegar',
			'پیتزا'      => 'corn-pellet-pizza',
			'ماست موسیر' => 'corn-pellet-shallot-yogurt',
			'پیاز جعفری' => 'corn-pellet-parsley-onion',
			'کچاپ'       => 'corn-pellet-ketchup',
			'پنیر'       => 'corn-pellet-cheese',
			'مرغ'        => 'corn-pellet-chicken',
			'فلفل'       => 'corn-pellet-pepper',
		);
		foreach ( $map as $needle => $slug ) {
			if ( false !== strpos( $title, $needle ) ) {
				$key = $slug;
				break;
			}
		}
	}
	if ( '' === $key ) {
		$report['skips'][] = array(
			'kind'  => 'product-no-key',
			'id'    => (int) $product->ID,
			'title' => get_the_title( $product ),
		);
		continue;
	}
	$old_path = $path_of( (string) get_permalink( $product ) );
	$row      = $rename( (int) $product->ID, $key, 'ghahghah_product' );
	if ( ! $row ) {
		continue;
	}
	$report['changes'][] = array_merge( array( 'kind' => 'product', 'key' => $key ), $row );
	if ( $old_path !== $row['new'] && '/' !== $old_path ) {
		$redirects[ $old_path ] = $row['new'];
	}
}

// Normalize redirect keys; drop identity / home dumps.
$clean = array();
foreach ( $redirects as $from => $to ) {
	$from = trailingslashit( (string) $from );
	$to   = (string) $to;
	if ( '' === $from || '/' === $from || $from === trailingslashit( $to ) ) {
		continue;
	}
	if ( isset( $clean[ $from ] ) && $clean[ $from ] !== $to ) {
		$report['skips'][] = array(
			'kind' => 'redirect-conflict',
			'from' => $from,
			'a'    => $clean[ $from ],
			'b'    => $to,
		);
		continue;
	}
	$clean[ $from ] = $to;
}

// Detect trivial chains in map (a→b, b→c) and flatten to final.
$changed = true;
$guard   = 0;
while ( $changed && $guard < 10 ) {
	$changed = false;
	++$guard;
	foreach ( $clean as $from => $to ) {
		$to_path  = (string) wp_parse_url( $to, PHP_URL_PATH );
		$to_path  = $to_path ? trailingslashit( $to_path ) : trailingslashit( (string) $to );
		$to_query = (string) wp_parse_url( $to, PHP_URL_QUERY );
		if ( isset( $clean[ $to_path ] ) ) {
			$next = $clean[ $to_path ];
			// Preserve query from original target if next has none.
			$next_q = (string) wp_parse_url( $next, PHP_URL_QUERY );
			if ( '' !== $to_query && '' === $next_q ) {
				$next = $next . ( false === strpos( $next, '?' ) ? '?' : '&' ) . $to_query;
			}
			if ( $clean[ $from ] !== $next ) {
				$report['skips'][] = array(
					'kind' => 'redirect-chain-flattened',
					'from' => $from,
					'via'  => $to_path,
					'to'   => $next,
				);
				$clean[ $from ] = $next;
				$changed        = true;
			}
		}
	}
}

$report['redirects'] = $clean;

// Retarget menus to final destinations (after map known).
$retarget_menus( $clean );

if ( ! $dry_run ) {
	$existing = get_option( 'ghahghah_url_redirects', array() );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}
	$merged = array_merge( $existing, $clean );
	// Drop identity mappings.
	foreach ( $merged as $from => $to ) {
		$to_path = (string) wp_parse_url( (string) $to, PHP_URL_PATH );
		$to_path = $to_path ? trailingslashit( $to_path ) : '';
		if ( trailingslashit( (string) $from ) === $to_path && '' === (string) wp_parse_url( (string) $to, PHP_URL_QUERY ) ) {
			unset( $merged[ $from ] );
		}
	}
	update_option( 'ghahghah_url_redirects', $merged, false );
	$report['option_count'] = count( $merged );
	$report['option_delta'] = count( array_diff_assoc( $merged, $existing ) );
	if ( $flush ) {
		flush_rewrite_rules( false );
		$report['flushed'] = true;
	}
}

// Idempotency summary for second runs.
$report['slug_changes'] = count(
	array_filter(
		$report['changes'],
		static function ( $row ) {
			return ! empty( $row['changed'] ) && isset( $row['from'], $row['to'] ) && $row['from'] !== $row['to'];
		}
	)
);
$report['menu_retargets'] = count(
	array_filter(
		$report['changes'],
		static function ( $row ) {
			return isset( $row['kind'] ) && 'menu-retarget' === $row['kind'] && ! empty( $row['changed'] );
		}
	)
);

WP_CLI::log( (string) wp_json_encode( $report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
