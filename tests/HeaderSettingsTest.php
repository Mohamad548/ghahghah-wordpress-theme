<?php
/**
 * Header settings sanitization tests.
 *
 * @package Ghahghah\Tests
 */

declare(strict_types=1);

namespace Ghahghah\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Covers header theme_mod sanitizers.
 */
final class HeaderSettingsTest extends TestCase {

	/**
	 * Set up Brain Monkey and load header settings.
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

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);

		require_once GHAHGHAH_THEME_DIR . '/inc/header-settings.php';
	}

	/**
	 * Tear down Brain Monkey.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Desktop logo width is clamped to the allowed range.
	 */
	public function test_desktop_logo_width_is_clamped(): void {
		$this->assertSame( 80, \ghahghah_sanitize_header_logo_width_desktop( 10 ) );
		$this->assertSame( 200, \ghahghah_sanitize_header_logo_width_desktop( 999 ) );
		$this->assertSame( 144, \ghahghah_sanitize_header_logo_width_desktop( 144 ) );
	}

	/**
	 * Mobile logo width is clamped to the allowed range.
	 */
	public function test_mobile_logo_width_is_clamped(): void {
		$this->assertSame( 72, \ghahghah_sanitize_header_logo_width_mobile( 1 ) );
		$this->assertSame( 140, \ghahghah_sanitize_header_logo_width_mobile( 400 ) );
		$this->assertSame( 112, \ghahghah_sanitize_header_logo_width_mobile( 112 ) );
	}

	/**
	 * CTA label is plain text and truncated to 40 characters.
	 */
	public function test_cta_label_is_sanitized_and_truncated(): void {
		Functions\when( 'sanitize_text_field' )->alias(
			static function ( $value ) {
				return trim( wp_strip_all_tags( (string) $value ) );
			}
		);
		Functions\when( 'wp_strip_all_tags' )->alias(
			static function ( $value ) {
				return strip_tags( (string) $value );
			}
		);

		$long = str_repeat( 'آ', 50 );
		$this->assertSame( 40, mb_strlen( \ghahghah_sanitize_header_cta_label( $long ) ) );
		$this->assertSame( 'خرید عمده', \ghahghah_sanitize_header_cta_label( '  خرید عمده  ' ) );
	}
}
