<?php
/**
 * Idempotent import of Ghahghah starter article drafts (standard posts).
 *
 * Explicit CLI only — not hooked to theme load/activation.
 *
 *   npx @wordpress/env run cli wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/starter-articles/import-starter-articles.php
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

$package_dir   = __DIR__;
$manifest_path = $package_dir . '/articles.json';

if ( ! is_readable( $manifest_path ) ) {
	WP_CLI::error( 'Manifest missing: ' . $manifest_path );
}

$manifest = json_decode( (string) file_get_contents( $manifest_path ), true );
if ( ! is_array( $manifest ) || empty( $manifest['articles'] ) || empty( $manifest['assets'] ) ) {
	WP_CLI::error( 'Invalid articles manifest.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$meta_source = '_ghahghah_article_source_key';
$meta_asset  = '_ghahghah_article_asset_source_key';
$meta_sha    = '_ghahghah_article_asset_sha256';

$stats = array(
	'posts_created'        => 0,
	'posts_skipped'        => 0,
	'posts_conflicts'      => 0,
	'attachments_created'  => 0,
	'attachments_reused'   => 0,
	'assets_hash_mismatch' => 0,
);

$assets_by_key = array();
foreach ( $manifest['assets'] as $asset ) {
	if ( ! is_array( $asset ) ) {
		continue;
	}
	$key = (string) ( $asset['key'] ?? '' );
	if ( '' === $key ) {
		continue;
	}
	$assets_by_key[ $key ] = $asset;
}

foreach ( $assets_by_key as $key => $asset ) {
	$rel  = (string) ( $asset['path'] ?? '' );
	$abs  = $package_dir . '/' . ltrim( str_replace( '\\', '/', $rel ), '/' );
	$expected = strtolower( (string) ( $asset['sha256'] ?? '' ) );
	if ( ! is_readable( $abs ) ) {
		WP_CLI::error( "Asset file missing for key {$key}: {$abs}" );
	}
	$real = hash_file( 'sha256', $abs );
	if ( ! is_string( $real ) || strtolower( $real ) !== $expected ) {
		++$stats['assets_hash_mismatch'];
		WP_CLI::error( "SHA-256 mismatch for {$key}. expected={$expected} got={$real}" );
	}
	// Path must stay inside package directory.
	$real_path = realpath( $abs );
	$root_path = realpath( $package_dir );
	if ( ! $real_path || ! $root_path || 0 !== strpos( $real_path, $root_path ) ) {
		WP_CLI::error( "Asset path escapes package for {$key}" );
	}
}

WP_CLI::log( 'Manifest OK: ' . count( $assets_by_key ) . ' assets, ' . count( $manifest['articles'] ) . ' articles.' );

$find_post_by_source = static function ( string $source_key ) use ( $meta_source ): int {
	$q = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_key'               => $meta_source,
			'meta_value'             => $source_key,
		)
	);
	return ! empty( $q->posts[0] ) ? (int) $q->posts[0] : 0;
};

$find_attachment = static function ( string $source_key, string $sha256 ) use ( $meta_asset, $meta_sha ): int {
	$by_source = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => $meta_asset,
			'meta_value'     => $source_key,
		)
	);
	if ( ! empty( $by_source->posts[0] ) ) {
		return (int) $by_source->posts[0];
	}

	$by_hash = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => $meta_sha,
			'meta_value'     => strtolower( $sha256 ),
		)
	);
	return ! empty( $by_hash->posts[0] ) ? (int) $by_hash->posts[0] : 0;
};

$import_asset = static function ( array $asset ) use ( $package_dir, $find_attachment, $meta_asset, $meta_sha, &$stats ): int {
	$source_key = (string) ( $asset['source_key'] ?? '' );
	$sha256     = strtolower( (string) ( $asset['sha256'] ?? '' ) );
	$rel        = (string) ( $asset['path'] ?? '' );
	$alt        = sanitize_text_field( (string) ( $asset['alt'] ?? '' ) );
	$filename   = basename( str_replace( '\\', '/', $rel ) );

	if ( '' === $source_key || '' === $sha256 || '' === $rel ) {
		return 0;
	}

	$existing = $find_attachment( $source_key, $sha256 );
	if ( $existing > 0 ) {
		++$stats['attachments_reused'];
		if ( '' !== $alt && '' === (string) get_post_meta( $existing, '_wp_attachment_image_alt', true ) ) {
			update_post_meta( $existing, '_wp_attachment_image_alt', $alt );
		}
		update_post_meta( $existing, $meta_asset, $source_key );
		update_post_meta( $existing, $meta_sha, $sha256 );
		return $existing;
	}

	$abs = $package_dir . '/' . ltrim( str_replace( '\\', '/', $rel ), '/' );
	$tmp = wp_tempnam( $filename );
	if ( ! $tmp || ! copy( $abs, $tmp ) ) {
		WP_CLI::warning( "Could not stage asset {$filename}" );
		return 0;
	}

	$id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		0
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}

	$id = (int) $id;
	update_post_meta( $id, $meta_asset, $source_key );
	update_post_meta( $id, $meta_sha, $sha256 );
	if ( '' !== $alt ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}

	++$stats['attachments_created'];
	return $id;
};

$blocks_to_content = static function ( array $blocks, array $attachment_ids_by_key, array $assets_by_key ): string {
	$out = array();

	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) ) {
			continue;
		}
		$type = (string) ( $block['type'] ?? '' );

		if ( 'paragraph' === $type ) {
			$text  = (string) ( $block['text'] ?? '' );
			$out[] = "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
			continue;
		}

		if ( 'heading' === $type ) {
			$level = absint( $block['level'] ?? 2 );
			$level = min( 4, max( 2, $level ) );
			$text  = (string) ( $block['text'] ?? '' );
			$tag   = 'h' . $level;
			$out[] = "<!-- wp:heading {\"level\":{$level}} -->\n<{$tag} class=\"wp-block-heading\">" . esc_html( $text ) . "</{$tag}>\n<!-- /wp:heading -->";
			continue;
		}

		if ( 'image' === $type ) {
			$akey = (string) ( $block['asset_key'] ?? '' );
			$aid  = isset( $attachment_ids_by_key[ $akey ] ) ? (int) $attachment_ids_by_key[ $akey ] : 0;
			if ( $aid <= 0 ) {
				continue;
			}
			$url = wp_get_attachment_image_url( $aid, 'large' );
			if ( ! is_string( $url ) || '' === $url ) {
				$url = wp_get_attachment_url( $aid );
			}
			if ( ! is_string( $url ) || '' === $url ) {
				continue;
			}
			$alt = '';
			if ( isset( $assets_by_key[ $akey ]['alt'] ) ) {
				$alt = sanitize_text_field( (string) $assets_by_key[ $akey ]['alt'] );
			}
			if ( '' === $alt ) {
				$alt = (string) get_post_meta( $aid, '_wp_attachment_image_alt', true );
			}
			$out[] = sprintf(
				'<!-- wp:image {"id":%1$d,"sizeSlug":"large","linkDestination":"none"} -->' . "\n" .
				'<figure class="wp-block-image size-large"><img src="%2$s" alt="%3$s" class="wp-image-%1$d"/></figure>' . "\n" .
				'<!-- /wp:image -->',
				$aid,
				esc_url( $url ),
				esc_attr( $alt )
			);
			continue;
		}

		if ( 'bullet_list' === $type ) {
			$items = isset( $block['items'] ) && is_array( $block['items'] ) ? $block['items'] : array();
			if ( array() === $items ) {
				continue;
			}
			$lis = '';
			foreach ( $items as $item ) {
				$lis .= '<!-- wp:list-item -->' . "\n<li>" . esc_html( (string) $item ) . "</li>\n<!-- /wp:list-item -->\n";
			}
			$out[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n{$lis}</ul>\n<!-- /wp:list -->";
		}
	}

	return implode( "\n\n", $out );
};

// Ensure category.
$cat_name = (string) ( $manifest['category']['name'] ?? 'مطالب قهقهه' );
$cat_slug = sanitize_title( (string) ( $manifest['category']['slug'] ?? 'ghahghah-articles' ) );
$term     = get_term_by( 'slug', $cat_slug, 'category' );
if ( ! $term || is_wp_error( $term ) ) {
	$created = wp_insert_term(
		$cat_name,
		'category',
		array(
			'slug' => $cat_slug,
		)
	);
	if ( is_wp_error( $created ) ) {
		WP_CLI::error( 'Could not create category: ' . $created->get_error_message() );
	}
	$category_id = (int) $created['term_id'];
	WP_CLI::log( "Created category #{$category_id} [{$cat_slug}]" );
} else {
	$category_id = (int) $term->term_id;
	WP_CLI::log( "Using category #{$category_id} [{$cat_slug}]" );
}

// Import assets first (all six).
$attachment_ids_by_key = array();
$asset_report          = array();
foreach ( $assets_by_key as $key => $asset ) {
	$id = $import_asset( $asset );
	if ( $id <= 0 ) {
		WP_CLI::error( "Failed to import asset {$key}" );
	}
	$attachment_ids_by_key[ $key ] = $id;
	$asset_report[]                = array(
		'key'        => $key,
		'source_key' => (string) $asset['source_key'],
		'id'         => $id,
		'sha256'     => (string) $asset['sha256'],
	);
}

$author_id = get_current_user_id();
if ( $author_id <= 0 ) {
	$admins = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => array( 'ID' ),
		)
	);
	$author_id = ! empty( $admins[0]->ID ) ? (int) $admins[0]->ID : 1;
}

$post_report = array();

foreach ( $manifest['articles'] as $article ) {
	if ( ! is_array( $article ) ) {
		continue;
	}

	$source_key = (string) ( $article['source_key'] ?? '' );
	$title      = sanitize_text_field( (string) ( $article['title'] ?? '' ) );
	$excerpt    = sanitize_text_field( (string) ( $article['excerpt'] ?? '' ) );
	$slug       = sanitize_title( (string) ( $article['slug'] ?? '' ) );
	$feat_key   = (string) ( $article['featured_image_key'] ?? '' );
	$content_blocks = isset( $article['content'] ) && is_array( $article['content'] ) ? $article['content'] : array();

	if ( '' === $source_key || '' === $title || '' === $slug || '' === $feat_key ) {
		WP_CLI::warning( 'Skipping incomplete article row.' );
		continue;
	}

	if ( ! isset( $attachment_ids_by_key[ $feat_key ] ) ) {
		WP_CLI::warning( "Missing featured asset {$feat_key} for {$source_key}" );
		continue;
	}

	$existing_id = $find_post_by_source( $source_key );
	if ( $existing_id > 0 ) {
		++$stats['posts_skipped'];
		$status = (string) get_post_status( $existing_id );
		$post_report[] = array(
			'source_key'          => $source_key,
			'title'               => get_the_title( $existing_id ),
			'id'                  => $existing_id,
			'status'              => $status,
			'action'              => 'skipped-existing',
			'featured_id'         => (int) get_post_thumbnail_id( $existing_id ),
			'inline_image_ids'    => array(),
		);
		WP_CLI::log( "SKIP #{$existing_id} [{$source_key}] status={$status}" );
		continue;
	}

	// Slug conflict with unrelated post.
	$slug_owner = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $slug_owner instanceof WP_Post ) {
		$owner_key = (string) get_post_meta( $slug_owner->ID, $meta_source, true );
		if ( $owner_key !== $source_key ) {
			++$stats['posts_conflicts'];
			$post_report[] = array(
				'source_key' => $source_key,
				'title'      => $title,
				'id'         => 0,
				'status'     => '',
				'action'     => 'conflict-slug',
				'conflict_id'=> (int) $slug_owner->ID,
				'slug'       => $slug,
			);
			WP_CLI::warning( "Slug conflict for {$slug}: occupied by post #{$slug_owner->ID} (source_key={$owner_key}). Skipping." );
			continue;
		}
	}

	$body = $blocks_to_content( $content_blocks, $attachment_ids_by_key, $assets_by_key );
	$inline_ids = array();
	foreach ( $content_blocks as $block ) {
		if ( is_array( $block ) && 'image' === ( $block['type'] ?? '' ) ) {
			$akey = (string) ( $block['asset_key'] ?? '' );
			if ( isset( $attachment_ids_by_key[ $akey ] ) ) {
				$inline_ids[] = (int) $attachment_ids_by_key[ $akey ];
			}
		}
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'draft',
			'post_title'   => $title,
			'post_excerpt' => $excerpt,
			'post_name'    => $slug,
			'post_content' => $body,
			'post_author'  => $author_id,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( $post_id->get_error_message() );
		continue;
	}

	$post_id = (int) $post_id;
	update_post_meta( $post_id, $meta_source, $source_key );
	wp_set_post_categories( $post_id, array( $category_id ) );
	set_post_thumbnail( $post_id, (int) $attachment_ids_by_key[ $feat_key ] );

	++$stats['posts_created'];
	$post_report[] = array(
		'source_key'       => $source_key,
		'title'            => $title,
		'id'               => $post_id,
		'status'           => 'draft',
		'action'           => 'created',
		'featured_id'      => (int) $attachment_ids_by_key[ $feat_key ],
		'inline_image_ids' => $inline_ids,
	);
	WP_CLI::success( "CREATED draft #{$post_id} [{$source_key}]" );
}

WP_CLI::log( '--- ASSETS ---' );
WP_CLI::log( (string) wp_json_encode( $asset_report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
WP_CLI::log( '--- POSTS ---' );
WP_CLI::log( (string) wp_json_encode( $post_report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
WP_CLI::log( '--- STATS ---' );
WP_CLI::log( (string) wp_json_encode( $stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
