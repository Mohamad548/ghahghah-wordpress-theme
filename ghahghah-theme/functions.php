<?php
/**
 * قهقهه theme bootstrap.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHAHGHAH_THEME_VERSION', '0.1.0' );
define( 'GHAHGHAH_THEME_DIR', get_template_directory() );
define( 'GHAHGHAH_THEME_URI', get_template_directory_uri() );

$ghahghah_includes = array(
	'/inc/setup.php',
	'/inc/assets.php',
	'/inc/template-tags.php',
);

foreach ( $ghahghah_includes as $ghahghah_file ) {
	$ghahghah_path = GHAHGHAH_THEME_DIR . $ghahghah_file;

	if ( is_readable( $ghahghah_path ) ) {
		require_once $ghahghah_path;
	}
}
