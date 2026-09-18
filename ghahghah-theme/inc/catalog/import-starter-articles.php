<?php
/**
 * Import / publish bundled starter articles (idempotent).
 *
 * Admin bootstrap or WP-CLI:
 *   wp eval 'require get_template_directory() . "/inc/catalog/import-starter-articles.php"; var_export( ghahghah_import_starter_articles() );'
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import starter articles from the bundled package and publish them.
 *
 * @param array{publish?: bool} $opts Options.
 * @return array{ok: bool, message: string, stats: array<string, int>}
 */
function ghahghah_import_starter_articles( array $opts = array() ): array {
	$opts = array_merge(
		array(
			'publish' => true,
		),
		$opts
	);

	$package_dir   = GHAHGHAH_THEME_DIR . '/inc/catalog/starter-articles';
	$manifest_path = $package_dir . '/articles.json';
	$empty_stats   = array(
		'posts_created'       => 0,
		'posts_published'     => 0,
		'posts_skipped'       => 0,
		'posts_conflicts'     => 0,
		'attachments_created' => 0,
		'attachments_reused'  => 0,
	);

	if ( ! is_readable( $manifest_path ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'بسته مطالب اولیه یافت نشد.', 'ghahghah' ),
			'stats'   => $empty_stats,
		);
	}

	$manifest = json_decode( (string) file_get_contents( $manifest_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $manifest ) || empty( $manifest['articles'] ) || empty( $manifest['assets'] ) || ! is_array( $manifest['articles'] ) || ! is_array( $manifest['assets'] ) ) {
		return array(
			'ok'      => false,
			'message' => __( 'فهرست مطالب اولیه نامعتبر است.', 'ghahghah' ),
			'stats'   => $empty_stats,
		);
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$meta_source = '_ghahghah_article_source_key';
	$meta_asset  = '_ghahghah_article_asset_source_key';
	$meta_sha    = '_ghahghah_article_asset_sha256';
	$stats       = $empty_stats;
	$target_status = ! empty( $opts['publish'] ) ? 'publish' : 'draft';

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
		$rel      = (string) ( $asset['path'] ?? '' );
		$abs      = $package_dir . '/' . ltrim( str_replace( '\\', '/', $rel ), '/' );
		$expected = strtolower( (string) ( $asset['sha256'] ?? '' ) );
		if ( ! is_readable( $abs ) ) {
			return array(
				'ok'      => false,
				/* translators: %s: asset key */
				'message' => sprintf( __( 'فایل تصویر مطلب یافت نشد: %s', 'ghahghah' ), $key ),
				'stats'   => $stats,
			);
		}
		$real = hash_file( 'sha256', $abs );
		if ( ! is_string( $real ) || strtolower( $real ) !== $expected ) {
			return array(
				'ok'      => false,
				/* translators: %s: asset key */
				'message' => sprintf( __( 'هش تصویر مطلب نامعتبر است: %s', 'ghahghah' ), $key ),
				'stats'   => $stats,
			);
		}
		$real_path = realpath( $abs );
		$root_path = realpath( $package_dir );
		if ( ! $real_path || ! $root_path || 0 !== strpos( $real_path, $root_path ) ) {
			return array(
				'ok'      => false,
				'message' => __( 'مسیر تصویر مطلب خارج از بسته است.', 'ghahghah' ),
				'stats'   => $stats,
			);
		}
	}

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
			return array(
				'ok'      => false,
				'message' => __( 'ایجاد دسته مطالب ناموفق بود.', 'ghahghah' ),
				'stats'   => $stats,
			);
		}
		$category_id = (int) $created['term_id'];
	} else {
		$category_id = (int) $term->term_id;
	}

	$attachment_ids_by_key = array();
	foreach ( $assets_by_key as $key => $asset ) {
		$id = $import_asset( $asset );
		if ( $id <= 0 ) {
			return array(
				'ok'      => false,
				/* translators: %s: asset key */
				'message' => sprintf( __( 'وارد کردن تصویر مطلب ناموفق بود: %s', 'ghahghah' ), $key ),
				'stats'   => $stats,
			);
		}
		$attachment_ids_by_key[ $key ] = $id;
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

	foreach ( $manifest['articles'] as $article ) {
		if ( ! is_array( $article ) ) {
			continue;
		}

		$source_key     = (string) ( $article['source_key'] ?? '' );
		$title          = sanitize_text_field( (string) ( $article['title'] ?? '' ) );
		$excerpt        = sanitize_text_field( (string) ( $article['excerpt'] ?? '' ) );
		$slug           = sanitize_title( (string) ( $article['slug'] ?? '' ) );
		$feat_key       = (string) ( $article['featured_image_key'] ?? '' );
		$content_blocks = isset( $article['content'] ) && is_array( $article['content'] ) ? $article['content'] : array();

		if ( '' === $source_key || '' === $title || '' === $slug || '' === $feat_key ) {
			continue;
		}
		if ( ! isset( $attachment_ids_by_key[ $feat_key ] ) ) {
			continue;
		}

		$existing_id = $find_post_by_source( $source_key );
		if ( $existing_id > 0 ) {
			$status = (string) get_post_status( $existing_id );
			if ( 'trash' === $status ) {
				wp_untrash_post( $existing_id );
			}
			if ( 'publish' !== (string) get_post_status( $existing_id ) && 'publish' === $target_status ) {
				wp_update_post(
					array(
						'ID'          => $existing_id,
						'post_status' => 'publish',
					)
				);
				++$stats['posts_published'];
			} else {
				++$stats['posts_skipped'];
			}
			continue;
		}

		$slug_owner = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $slug_owner instanceof WP_Post ) {
			$owner_key = (string) get_post_meta( $slug_owner->ID, $meta_source, true );
			if ( $owner_key !== $source_key ) {
				++$stats['posts_conflicts'];
				continue;
			}
		}

		$body = $blocks_to_content( $content_blocks, $attachment_ids_by_key, $assets_by_key );

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => $target_status,
				'post_title'   => $title,
				'post_excerpt' => $excerpt,
				'post_name'    => $slug,
				'post_content' => $body,
				'post_author'  => $author_id,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		$post_id = (int) $post_id;
		update_post_meta( $post_id, $meta_source, $source_key );
		wp_set_post_categories( $post_id, array( $category_id ) );
		set_post_thumbnail( $post_id, (int) $attachment_ids_by_key[ $feat_key ] );

		++$stats['posts_created'];
		if ( 'publish' === $target_status ) {
			++$stats['posts_published'];
		}
	}

	if ( absint( get_theme_mod( 'ghahghah_articles_category', 0 ) ) <= 0 && $category_id > 0 ) {
		set_theme_mod( 'ghahghah_articles_category', $category_id );
	}
	set_theme_mod( 'ghahghah_articles_enabled', true );

	return array(
		'ok'      => true,
		'message' => sprintf(
			/* translators: 1: created, 2: published/updated, 3: skipped */
			__( 'مطالب: %1$d جدید، %2$d منتشر، %3$d موجود.', 'ghahghah' ),
			(int) $stats['posts_created'],
			(int) $stats['posts_published'],
			(int) $stats['posts_skipped']
		),
		'stats'   => $stats,
	);
}

/**
 * Move WordPress default sample posts to trash.
 *
 * @return int Number of trashed posts.
 */
function ghahghah_trash_default_sample_posts(): int {
	$trashed = 0;
	$q       = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'posts_per_page'         => 20,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $q->posts as $post_id ) {
		$post = get_post( (int) $post_id );
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$is_sample = (
			'hello-world' === $post->post_name
			|| 'سلام دنیا!' === $post->post_title
			|| 'Hello world!' === $post->post_title
			|| false !== strpos( (string) $post->post_content, 'به وردپرس خوش آمدید' )
			|| false !== stripos( (string) $post->post_content, 'Welcome to WordPress' )
		);

		if ( ! $is_sample ) {
			continue;
		}

		// Never trash theme-managed starter articles.
		if ( '' !== (string) get_post_meta( $post->ID, '_ghahghah_article_source_key', true ) ) {
			continue;
		}

		wp_trash_post( $post->ID );
		++$trashed;
	}

	return $trashed;
}
