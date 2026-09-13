<?php
/**
 * Product CPT unit tests (Brain Monkey).
 *
 * @package Ghahghah\Tests
 */

declare(strict_types=1);

namespace Ghahghah\Tests;

use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Ghahghah\Core\PostTypes\Product;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Verifies product CPT registration wiring.
 */
final class ProductPostTypeTest extends TestCase {

	/**
	 * Set up Brain Monkey.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Tear down Brain Monkey / Mockery.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	/**
	 * Product::register() should hook register_post_type onto init.
	 */
	public function test_register_hooks_init(): void {
		Actions\expectAdded( 'init' )
			->once()
			->with( Mockery::type( 'array' ) );

		$product = new Product();
		$product->register();

		$this->assertTrue( true ); // Expectation above is the assertion contract.
	}

	/**
	 * register_post_type should be called with the expected post type key.
	 */
	public function test_register_post_type_uses_expected_key(): void {
		Functions\expect( 'register_post_type' )
			->once()
			->with(
				Product::POST_TYPE,
				Mockery::type( 'array' )
			);

		Functions\when( '__' )->returnArg();
		Functions\when( 'apply_filters' )->alias(
			static function ( $hook, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				return $value;
			}
		);

		$product = new Product();
		$product->register_post_type();

		$this->assertSame( 'ghahghah_product', Product::POST_TYPE );
	}

	/**
	 * Archive slug constant should be filterable default.
	 */
	public function test_default_archive_slug(): void {
		$this->assertSame( 'products', Product::ARCHIVE_SLUG );
	}
}
