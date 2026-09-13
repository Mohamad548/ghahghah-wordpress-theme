<?php
/**
 * Theme helper smoke tests.
 *
 * @package Ghahghah\Tests
 */

declare(strict_types=1);

namespace Ghahghah\Tests;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * Confirms theme helpers degrade safely without the Core plugin.
 */
final class ThemeCoreFallbackTest extends TestCase {

	/**
	 * Set up Brain Monkey.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/tmp/' );
		}

		if ( ! defined( 'GHAHGHAH_THEME_DIR' ) ) {
			define( 'GHAHGHAH_THEME_DIR', dirname( __DIR__ ) . '/ghahghah-theme' );
		}

		require_once GHAHGHAH_THEME_DIR . '/inc/template-tags.php';
	}

	/**
	 * Tear down Brain Monkey.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Core detection must be false when the plugin constant is absent.
	 */
	public function test_core_inactive_when_constant_missing(): void {
		$this->assertFalse(
			defined( 'GHAHGHAH_CORE_VERSION' ),
			'Test environment should not define Core version.'
		);
		$this->assertFalse( \ghahghah_is_core_active() );
	}
}
