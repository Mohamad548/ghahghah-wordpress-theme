<?php
/**
 * Serve stored public URL redirects (301) from option ghahghah_url_redirects.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a request path for redirect lookup.
 */
function ghahghah_url_migration_normalize_path( string $path ): string {
	$path = rawurldecode( $path );
	if ( '' === $path ) {
		return '/';
	}
	if ( '/' !== $path[0] ) {
		$path = '/' . $path;
	}
	return trailingslashit( $path );
}

/**
 * Lookup destination for an old public path.
 */
function ghahghah_url_migration_lookup( string $path ): string {
	$map = get_option( 'ghahghah_url_redirects', array() );
	if ( ! is_array( $map ) || array() === $map ) {
		return '';
	}

	$candidates = array(
		ghahghah_url_migration_normalize_path( $path ),
		trailingslashit( $path ),
		untrailingslashit( $path ) . '/',
		$path,
	);

	// Percent-encoded form of decoded path.
	$decoded = rawurldecode( $path );
	if ( $decoded !== $path ) {
		$candidates[] = ghahghah_url_migration_normalize_path( $decoded );
	}
	$parts = array_map( 'rawurlencode', explode( '/', trim( $decoded, '/' ) ) );
	if ( array() !== $parts && '' !== $parts[0] ) {
		$candidates[] = '/' . implode( '/', $parts ) . '/';
	}

	foreach ( $candidates as $key ) {
		if ( isset( $map[ $key ] ) && is_string( $map[ $key ] ) && '' !== $map[ $key ] ) {
			return (string) $map[ $key ];
		}
	}
	return '';
}

/**
 * Issue a single 301 to the mapped destination; preserve safe query args.
 */
function ghahghah_url_migration_template_redirect(): void {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( '' === $path ) {
		return;
	}

	// Never redirect uploads / admin / REST.
	if ( 0 === strpos( $path, '/wp-admin' ) || 0 === strpos( $path, '/wp-json' ) || 0 === strpos( $path, '/wp-content/' ) ) {
		return;
	}

	$dest = ghahghah_url_migration_lookup( $path );
	if ( '' === $dest ) {
		return;
	}

	// Absolute or path.
	if ( 0 === strpos( $dest, 'http://' ) || 0 === strpos( $dest, 'https://' ) ) {
		$target = $dest;
	} elseif ( 0 === strpos( $dest, '/' ) ) {
		$target = home_url( $dest );
	} else {
		$target = home_url( '/' . ltrim( $dest, '/' ) );
	}

	$target_path = (string) wp_parse_url( $target, PHP_URL_PATH );
	$req_path    = ghahghah_url_migration_normalize_path( $path );
	$tgt_path    = $target_path ? ghahghah_url_migration_normalize_path( $target_path ) : '';
	if ( $tgt_path && $req_path === $tgt_path ) {
		return; // already there
	}

	// Preserve pagination / flavor filters when destination has no query.
	$req_query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	$dst_query = (string) wp_parse_url( $target, PHP_URL_QUERY );
	if ( '' !== $req_query && '' === $dst_query ) {
		parse_str( $req_query, $q);
		$keep = array();
		foreach ( array( 'gh_flavor', 'paged', 'page' ) as $key ) {
			if ( isset( $q[ $key ] ) && '' !== (string) $q[ $key ] ) {
				$keep[ $key ] = $q[ $key ];
			}
		}
		if ( array() !== $keep ) {
			$target = add_query_arg( $keep, $target );
		}
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'ghahghah_url_migration_template_redirect', 1 );
