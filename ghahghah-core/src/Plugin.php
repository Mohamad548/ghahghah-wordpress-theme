<?php
/**
 * Main plugin orchestrator.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core;

use Ghahghah\Core\Forms\FormRenderer;
use Ghahghah\Core\Forms\InquiryRest;
use Ghahghah\Core\PostTypes\Inquiry;
use Ghahghah\Core\PostTypes\Product;

/**
 * Singleton plugin bootstrap.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Registered service objects.
	 *
	 * @var array<int, object>
	 */
	private array $services = array();

	/**
	 * Get the singleton instance.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 */
	private function __construct() {}

	/**
	 * Wire hooks and services.
	 */
	public function init(): void {
		load_plugin_textdomain(
			'ghahghah-core',
			false,
			dirname( plugin_basename( GHAHGHAH_CORE_FILE ) ) . '/languages'
		);

		$this->services[] = new Product();
		$this->services[] = new Inquiry();
		$this->services[] = new InquiryRest();
		$this->services[] = new FormRenderer();

		foreach ( $this->services as $service ) {
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}

		$globals = GHAHGHAH_CORE_DIR . 'src/Forms/globals.php';
		if ( is_readable( $globals ) ) {
			require_once $globals;
		}

		/**
		 * Fires after Ghahghah Core has finished bootstrapping.
		 *
		 * @param self $plugin Plugin instance.
		 */
		do_action( 'ghahghah_core_loaded', $this );
	}

	/**
	 * Expose registered services (for tests / extensions).
	 *
	 * @return array<int, object>
	 */
	public function services(): array {
		return $this->services;
	}
}
