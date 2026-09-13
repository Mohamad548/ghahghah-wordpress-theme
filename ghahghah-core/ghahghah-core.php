<?php
/**
 * Plugin Name:       Ghahghah Core
 * Plugin URI:        https://github.com/Mohamad548/ghahghah-wordpress-theme
 * Description:       قابلیت‌های کسب‌وکاری برند قهقهه که باید مستقل از قالب باقی بمانند — کاتالوگ محصولات و زیرساخت فرم‌ها.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.2
 * Author:            قهقهه
 * Author URI:        https://github.com/Mohamad548/ghahghah-wordpress-theme
 * Text Domain:       ghahghah-core
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHAHGHAH_CORE_VERSION', '0.1.0' );
define( 'GHAHGHAH_CORE_FILE', __FILE__ );
define( 'GHAHGHAH_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GHAHGHAH_CORE_URL', plugin_dir_url( __FILE__ ) );

$ghahghah_core_autoload = GHAHGHAH_CORE_DIR . 'vendor/autoload.php';

if ( is_readable( $ghahghah_core_autoload ) ) {
	require_once $ghahghah_core_autoload;
} else {
	/**
	 * Minimal PSR-4 fallback when Composer vendor is not installed.
	 *
	 * @param string $class Fully-qualified class name.
	 */
	spl_autoload_register(
		static function ( string $class_name ): void {
			$prefix = __NAMESPACE__ . '\\';

			if ( ! str_starts_with( $class_name, $prefix ) ) {
				return;
			}

			$relative = substr( $class_name, strlen( $prefix ) );
			$file     = GHAHGHAH_CORE_DIR . 'src/' . str_replace( '\\', '/', $relative ) . '.php';

			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	);
}

/**
 * Bootstrap the plugin.
 */
function ghahghah_core_bootstrap(): void {
	$plugin = Plugin::instance();
	$plugin->init();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\ghahghah_core_bootstrap' );

register_activation_hook(
	__FILE__,
	static function (): void {
		Activator::activate();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		Deactivator::deactivate();
	}
);
