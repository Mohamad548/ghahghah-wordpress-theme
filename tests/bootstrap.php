<?php
/**
 * PHPUnit bootstrap.
 *
 * @package Ghahghah\Tests
 */

declare(strict_types=1);

$ghahghah_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';

if ( ! is_readable( $ghahghah_autoload ) ) {
	fwrite( STDERR, "Composer autoload not found. Run: composer install\n" );
	exit( 1 );
}

require_once $ghahghah_autoload;

// Load Core plugin classes without bootstrapping WordPress hooks.
$ghahghah_core_autoload = dirname( __DIR__ ) . '/ghahghah-core/vendor/autoload.php';

if ( is_readable( $ghahghah_core_autoload ) ) {
	require_once $ghahghah_core_autoload;
} else {
	spl_autoload_register(
		static function ( string $class ): void {
			$prefix = 'Ghahghah\\Core\\';

			if ( ! str_starts_with( $class, $prefix ) ) {
				return;
			}

			$relative = substr( $class, strlen( $prefix ) );
			$file     = dirname( __DIR__ ) . '/ghahghah-core/src/' . str_replace( '\\', '/', $relative ) . '.php';

			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}
	);
}
