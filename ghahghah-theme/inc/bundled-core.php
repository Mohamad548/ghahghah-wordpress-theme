<?php
/**
 * Install / activate the bundled Ghahghah Core plugin (IranKala-style one-package install).
 *
 * Core ships inside the theme under bundled/ghahghah-core/. On theme activation the
 * theme copies it into wp-content/plugins/ and activates it, then runs site bootstrap.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to the bundled core plugin directory inside the theme.
 */
function ghahghah_bundled_core_source_dir(): string {
	return GHAHGHAH_THEME_DIR . '/bundled/ghahghah-core';
}

/**
 * Relative plugin main file under WP_PLUGIN_DIR.
 */
function ghahghah_bundled_core_plugin_basename(): string {
	return 'ghahghah-core/ghahghah-core.php';
}

/**
 * Recursively copy a directory.
 *
 * @param string $src Source directory.
 * @param string $dst Destination directory.
 */
function ghahghah_recursive_copy_dir( string $src, string $dst ): bool {
	if ( ! is_dir( $src ) ) {
		return false;
	}
	if ( ! wp_mkdir_p( $dst ) ) {
		return false;
	}

	$dir = opendir( $src );
	if ( false === $dir ) {
		return false;
	}

	while ( false !== ( $file = readdir( $dir ) ) ) {
		if ( '.' === $file || '..' === $file ) {
			continue;
		}
		$from = $src . '/' . $file;
		$to   = $dst . '/' . $file;
		if ( is_dir( $from ) ) {
			if ( ! ghahghah_recursive_copy_dir( $from, $to ) ) {
				closedir( $dir );
				return false;
			}
			continue;
		}
		if ( ! copy( $from, $to ) ) {
			closedir( $dir );
			return false;
		}
	}
	closedir( $dir );
	return true;
}

/**
 * Ensure the bundled core plugin is installed and active.
 *
 * @return array{ok: bool, message: string, activated: bool}
 */
function ghahghah_ensure_bundled_core_plugin(): array {
	if ( defined( 'GHAHGHAH_CORE_VERSION' ) ) {
		return array(
			'ok'        => true,
			'message'   => __( 'افزونه Ghahghah Core فعال است.', 'ghahghah' ),
			'activated' => false,
		);
	}

	$source = ghahghah_bundled_core_source_dir();
	$main   = $source . '/ghahghah-core.php';
	if ( ! is_readable( $main ) ) {
		return array(
			'ok'        => false,
			'message'   => __( 'بسته افزونه همراه قالب یافت نشد.', 'ghahghah' ),
			'activated' => false,
		);
	}

	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$basename = ghahghah_bundled_core_plugin_basename();
	$dest     = WP_PLUGIN_DIR . '/ghahghah-core';

	if ( ! is_readable( WP_PLUGIN_DIR . '/' . $basename ) ) {
		if ( is_dir( $dest ) ) {
			// Incomplete install — refresh from bundled copy.
			$removed = true;
			if ( function_exists( 'WP_Filesystem' ) ) {
				// Best-effort cleanup; ignore failures and overwrite files.
			}
			ghahghah_recursive_copy_dir( $source, $dest );
		} elseif ( ! ghahghah_recursive_copy_dir( $source, $dest ) ) {
			return array(
				'ok'        => false,
				'message'   => __( 'کپی افزونه همراه قالب به پوشه افزونه‌ها ناموفق بود (مجوز نوشتن؟).', 'ghahghah' ),
				'activated' => false,
			);
		}
	}

	if ( ! is_readable( WP_PLUGIN_DIR . '/' . $basename ) ) {
		return array(
			'ok'        => false,
			'message'   => __( 'فایل اصلی افزونه پس از کپی در دسترس نیست.', 'ghahghah' ),
			'activated' => false,
		);
	}

	if ( is_plugin_active( $basename ) ) {
		return array(
			'ok'        => true,
			'message'   => __( 'افزونه Ghahghah Core نصب و فعال است.', 'ghahghah' ),
			'activated' => false,
		);
	}

	$result = activate_plugin( $basename, '', false, is_network_admin() );
	if ( is_wp_error( $result ) ) {
		return array(
			'ok'        => false,
			'message'   => $result->get_error_message(),
			'activated' => false,
		);
	}

	return array(
		'ok'        => true,
		'message'   => __( 'افزونه Ghahghah Core از داخل قالب نصب و فعال شد.', 'ghahghah' ),
		'activated' => true,
	);
}

/**
 * One-shot after theme switch: ensure bundled Core is present.
 * Full demo content runs via the multi-step setup wizard (not silently).
 */
function ghahghah_after_switch_theme_install_bundle(): void {
	if ( ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'switch_themes' ) ) {
		return;
	}

	$core = ghahghah_ensure_bundled_core_plugin();
	update_option( 'ghahghah_bundled_core_last', $core, false );
}
add_action( 'after_switch_theme', 'ghahghah_after_switch_theme_install_bundle', 5 );

/**
 * Deferred full bootstrap (pages, products, menus, designer, articles).
 */
function ghahghah_run_deferred_site_bootstrap(): void {
	if ( '1' !== (string) get_option( 'ghahghah_bootstrap_pending', '' ) ) {
		return;
	}

	if ( ! function_exists( 'ghahghah_bootstrap_site' ) ) {
		$bootstrap = GHAHGHAH_THEME_DIR . '/inc/catalog/bootstrap-site.php';
		if ( is_readable( $bootstrap ) ) {
			require_once $bootstrap;
		}
	}

	if ( function_exists( 'ghahghah_bootstrap_site' ) ) {
		$result = ghahghah_bootstrap_site();
		update_option( 'ghahghah_bootstrap_last_result', $result, false );
	}

	delete_option( 'ghahghah_bootstrap_pending' );
}
add_action( 'ghahghah_run_deferred_site_bootstrap', 'ghahghah_run_deferred_site_bootstrap' );

/**
 * Admin safety net: if core still missing while theme is active, try again once.
 */
function ghahghah_admin_maybe_ensure_bundled_core(): void {
	if ( ! is_admin() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( defined( 'GHAHGHAH_CORE_VERSION' ) ) {
		return;
	}
	if ( '1' === (string) get_option( 'ghahghah_bundled_core_admin_tried', '' ) ) {
		return;
	}
	update_option( 'ghahghah_bundled_core_admin_tried', '1', false );
	ghahghah_ensure_bundled_core_plugin();
}
add_action( 'admin_init', 'ghahghah_admin_maybe_ensure_bundled_core', 3 );
