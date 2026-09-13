<?php
/**
 * Plugin deactivation routine.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core;

/**
 * Runs on plugin deactivation.
 */
final class Deactivator {

	/**
	 * Flush rewrite rules so product archives disappear cleanly.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
