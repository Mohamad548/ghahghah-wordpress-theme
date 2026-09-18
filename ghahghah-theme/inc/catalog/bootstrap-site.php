<?php
/**
 * One-shot live-site bootstrap: media, pages, products, menus, homepage images.
 *
 * Admin: پیکربندی قالب → رسانه قالب → راه‌اندازی اولیه
 * CLI:
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/bootstrap-site.php
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run full site bootstrap (safe to re-run).
 *
 * @param array<string, bool> $opts Optional flags.
 * @return array{ok: bool, steps: array<string, array{ok: bool, message: string}>}
 */
function ghahghah_bootstrap_site( array $opts = array() ): array {
	$opts = array_merge(
		array(
			'media'    => true,
			'pages'    => true,
			'products' => true,
			'articles' => true,
			'menus'    => true,
			'cleanup'  => true,
		),
		$opts
	);

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	if ( function_exists( 'wp_raise_memory_limit' ) ) {
		wp_raise_memory_limit( 'admin' );
	}

	$steps = array();

	if ( ! empty( $opts['media'] ) && function_exists( 'ghahghah_sync_theme_media_library' ) ) {
		$stats = ghahghah_sync_theme_media_library( true, true );
		update_option( 'ghahghah_theme_media_sync_version', GHAHGHAH_THEME_VERSION, false );
		delete_option( 'ghahghah_theme_media_sync_pending' );

		// Homepage factory strip shares factory-hero with the factory page.
		$hero_id = absint( get_theme_mod( 'ghahghah_factory_page_hero_image_id', 0 ) );
		if ( $hero_id <= 0 && function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
			$hero_id = ghahghah_find_theme_media_attachment( 'factory/factory-hero.webp' );
		}
		if ( $hero_id > 0 && absint( get_theme_mod( 'ghahghah_factory_image', 0 ) ) <= 0 ) {
			set_theme_mod( 'ghahghah_factory_image', $hero_id );
		}

		// Enable image-based homepage sections when assets exist.
		$steps_img = absint( get_theme_mod( 'ghahghah_steps_image', 0 ) );
		if ( $steps_img <= 0 && function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
			$steps_img = ghahghah_find_theme_media_attachment( 'steps/production-steps.webp' );
			if ( $steps_img > 0 ) {
				set_theme_mod( 'ghahghah_steps_image', $steps_img );
			}
		}
		if ( $steps_img > 0 ) {
			set_theme_mod( 'ghahghah_steps_enabled', true );
		}

		foreach ( array( 'wholesale', 'agency' ) as $type ) {
			$key  = 'wholesale' === $type ? 'ghahghah_collab_wholesale_image' : 'ghahghah_collab_agency_image';
			$file = 'wholesale' === $type ? 'collab/wholesale-banner.webp' : 'collab/agency-banner.webp';
			$id   = absint( get_theme_mod( $key, 0 ) );
			if ( $id <= 0 && function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
				$id = ghahghah_find_theme_media_attachment( $file );
				if ( $id > 0 ) {
					set_theme_mod( $key, $id );
				}
			}
		}
		set_theme_mod( 'ghahghah_collab_enabled', true );

		$steps['media'] = array(
			'ok'      => true,
			'message' => sprintf(
				/* translators: 1: total, 2: created, 3: reused */
				__( 'رسانه: %1$d بررسی، %2$d جدید، %3$d موجود.', 'ghahghah' ),
				(int) ( $stats['total'] ?? 0 ),
				(int) ( $stats['created'] ?? 0 ),
				(int) ( $stats['reused'] ?? 0 )
			),
		);
	}

	if ( ! empty( $opts['pages'] ) ) {
		$page_scripts = array(
			'setup-contact-page.php',
			'setup-factory-page.php',
			'setup-request-pages.php',
			'setup-faq-page.php',
			'setup-privacy-page.php',
			'setup-designer-page.php',
		);
		$ok_pages = true;
		foreach ( $page_scripts as $script ) {
			$path = GHAHGHAH_THEME_DIR . '/inc/catalog/' . $script;
			if ( ! is_readable( $path ) ) {
				$ok_pages = false;
				continue;
			}
			require $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
		$steps['pages'] = array(
			'ok'      => $ok_pages,
			'message' => $ok_pages
				? __( 'برگه‌های تماس، کارخانه، عمده، نمایندگی، FAQ، حریم خصوصی و معرفی طراح آماده شدند.', 'ghahghah' )
				: __( 'برخی اسکریپت‌های برگه یافت نشد.', 'ghahghah' ),
		);
	}

	if ( ! empty( $opts['articles'] ) ) {
		if ( ! function_exists( 'ghahghah_import_starter_articles' ) ) {
			require_once GHAHGHAH_THEME_DIR . '/inc/catalog/import-starter-articles.php';
		}
		$result             = ghahghah_import_starter_articles( array( 'publish' => true ) );
		$steps['articles'] = array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
		);

		if ( function_exists( 'ghahghah_trash_default_sample_posts' ) ) {
			$trashed_samples = ghahghah_trash_default_sample_posts();
			if ( $trashed_samples > 0 ) {
				$steps['articles']['message'] .= ' ' . sprintf(
					/* translators: %d: trashed sample posts */
					__( 'نمونه پیش‌فرض وردپرس: %d مورد به زباله‌دان منتقل شد.', 'ghahghah' ),
					$trashed_samples
				);
			}
		}
	}

	if ( ! empty( $opts['products'] ) ) {
		if ( ! function_exists( 'ghahghah_import_product_catalog' ) ) {
			require_once GHAHGHAH_THEME_DIR . '/inc/catalog/import-products.php';
		}
		$result             = ghahghah_import_product_catalog();
		$steps['products'] = array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
		);
	}

	if ( ! empty( $opts['menus'] ) ) {
		if ( ! function_exists( 'ghahghah_bootstrap_default_menus' ) ) {
			require_once GHAHGHAH_THEME_DIR . '/inc/catalog/setup-menus.php';
		}
		$result          = ghahghah_bootstrap_default_menus();
		$steps['menus'] = array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
		);
	}

	if ( ! empty( $opts['cleanup'] ) && function_exists( 'ghahghah_cleanup_duplicate_theme_media' ) ) {
		$clean = ghahghah_cleanup_duplicate_theme_media();
		$steps['cleanup'] = array(
			'ok'      => true,
			'message' => sprintf(
				/* translators: %d: trashed count */
				__( 'پاکسازی رسانه: %d فایل تکراری به زباله‌دان منتقل شد.', 'ghahghah' ),
				(int) ( $clean['trashed'] ?? 0 )
			),
		);
	}

	$all_ok = true;
	foreach ( $steps as $step ) {
		if ( empty( $step['ok'] ) ) {
			$all_ok = false;
			break;
		}
	}

	set_theme_mod( 'ghahghah_bootstrap_last_run', time() );

	return array(
		'ok'    => $all_ok,
		'steps' => $steps,
	);
}

// WP-CLI entry: `wp eval-file .../bootstrap-site.php`
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$cli_script = '';
	if ( isset( $_SERVER['argv'] ) && is_array( $_SERVER['argv'] ) ) {
		$cli_script = (string) end( $_SERVER['argv'] );
	}
	if ( '' !== $cli_script && str_ends_with( str_replace( '\\', '/', $cli_script ), 'bootstrap-site.php' ) ) {
		$result = ghahghah_bootstrap_site();
		foreach ( $result['steps'] as $name => $step ) {
			$label = strtoupper( (string) $name );
			$msg   = (string) ( $step['message'] ?? '' );
			if ( ! empty( $step['ok'] ) ) {
				WP_CLI::success( "{$label}: {$msg}" );
			} else {
				WP_CLI::warning( "{$label}: {$msg}" );
			}
		}
		if ( ! empty( $result['ok'] ) ) {
			WP_CLI::success( 'Bootstrap complete.' );
		} else {
			WP_CLI::error( 'Bootstrap finished with warnings.' );
		}
	}
}
