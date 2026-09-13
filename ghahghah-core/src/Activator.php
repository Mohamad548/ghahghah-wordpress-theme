<?php
/**
 * Plugin activation routine.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core;

use Ghahghah\Core\PostTypes\Product;

/**
 * Runs on plugin activation.
 */
final class Activator {

	/**
	 * Register CPT then flush rewrite rules once.
	 */
	public static function activate(): void {
		Capabilities::grant();

		$product = new Product();
		$product->register_post_type();

		flush_rewrite_rules();

		update_option( 'ghahghah_core_version', GHAHGHAH_CORE_VERSION, false );
	}
}
