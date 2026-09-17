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
		if ( ! empty( $row['changed'] ) && $row['old'] !== $row['new'] ) {
			$redirects[ $row['old'] ] = $archive_path; // still send humans to archive
		}
	}
}

// Stale flavor pages → product archive filters / singles.
$flavor_pages = array(
	array( 'title' => 'همه محصولات', 'slugs' => array( 'همه-محصولات' ), 'target' => $archive_path ),
	array( 'title' => 'طعم پیتزا', 'slugs' => array( 'طعم-پیتزا' ), 'target' => $archive_path . '?gh_flavor=pizza' ),
	array( 'title' => 'طعم لیمویی', 'slugs' => array( 'طعم-لیمویی' ), 'target' => $archive_path . '?gh_flavor=lemon' ),
);
foreach ( $flavor_pages as $fp ) {
	$id = $find_page( $fp['slugs'], $fp['title'] );
	if ( $id <= 0 ) {
		continue;
	}
	$old = $path_of( (string) get_permalink( $id ) );
	$redirects[ $old ] = $fp['target'];
	$row = $rename( $id, sanitize_title( 'legacy-' . $fp['title'] ), 'page' );
	if ( $row ) {
		$report['changes'][] = array_merge( array( 'kind' => 'legacy-flavor-page', 'redirect_to' => $fp['target'] ), $row );
	}
}

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

// Detect trivial chains in map (a→b, b→c).
foreach ( $clean as $from => $to ) {
	$to_path = wp_parse_url( $to, PHP_URL_PATH );
	$to_path = $to_path ? trailingslashit( $to_path ) : $to;
	if ( isset( $clean[ $to_path ] ) ) {
		$report['skips'][] = array(
			'kind' => 'redirect-chain-risk',
			'from' => $from,
			'via'  => $to_path,
			'to'   => $clean[ $to_path ],
		);
		// Flatten.
		$clean[ $from ] = $clean[ $to_path ];
	}
}

$report['redirects'] = $clean;

if ( ! $dry_run ) {
	$existing = get_option( 'ghahghah_url_redirects', array() );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}
	$merged = array_merge( $existing, $clean );
	update_option( 'ghahghah_url_redirects', $merged, false );
	$report['option_count'] = count( $merged );
	if ( $flush ) {
		flush_rewrite_rules( false );
		$report['flushed'] = true;
	}
}

WP_CLI::log( (string) wp_json_encode( $report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
