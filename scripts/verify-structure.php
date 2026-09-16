<?php
/**
 * Structural verification without a full WordPress install.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

$root  = dirname( __DIR__ );
$fail  = 0;
$theme = $root . '/ghahghah-theme';
$core  = $root . '/ghahghah-core';

/**
 * Print a check result.
 *
 * @param bool   $ok      Pass/fail.
 * @param string $message Message.
 */
function ghahghah_check( bool $ok, string $message ): void {
	global $fail;
	echo ( $ok ? '[PASS] ' : '[FAIL] ' ) . $message . PHP_EOL;
	if ( ! $ok ) {
		++$fail;
	}
}

$theme_required = array(
	'style.css',
	'functions.php',
	'theme.json',
	'index.php',
	'front-page.php',
	'header.php',
	'footer.php',
	'page.php',
	'single.php',
	'archive.php',
	'404.php',
	'inc/setup.php',
	'inc/assets.php',
	'inc/template-tags.php',
	'inc/header-settings.php',
	'inc/class-primary-nav-walker.php',
	'inc/admin/config.php',
	'inc/admin/panels/header.php',
	'inc/admin/panels/footer.php',
	'inc/admin/panels/agency-requests.php',
	'inc/admin/panels/sms-settings.php',
	'template-parts/header/site-header.php',
	'template-parts/footer/site-footer.php',
	'assets/css/base.css',
	'assets/css/layout.css',
	'assets/css/header.css',
	'assets/css/fonts.css',
	'assets/css/admin-config.css',
	'assets/js/theme.js',
	'assets/js/header.js',
	'assets/js/admin-config.js',
	'assets/fonts/yekan-bakh/YekanBakhFaNum-Regular.woff',
	'assets/fonts/yekan-bakh/YekanBakhFaNum-SemiBold.woff',
	'assets/images/brand/ghahghah-logo-desktop.webp',
	'assets/images/brand/ghahghah-logo-mobile.webp',
	'assets/images/brand/ghahghah-site-icon-512.png',
);

foreach ( $theme_required as $rel ) {
	ghahghah_check( is_readable( $theme . '/' . $rel ), "Theme file exists: $rel" );
}

$core_required = array(
	'ghahghah-core.php',
	'composer.json',
	'src/Plugin.php',
	'src/Activator.php',
	'src/Deactivator.php',
	'src/Capabilities.php',
	'src/PostTypes/Product.php',
);

foreach ( $core_required as $rel ) {
	ghahghah_check( is_readable( $core . '/' . $rel ), "Core file exists: $rel" );
}

$theme_css = file_get_contents( $theme . '/style.css' );
ghahghah_check( (bool) preg_match( '/Text Domain:\s*ghahghah\b/', $theme_css ), 'Theme text domain is ghahghah' );

$core_main = file_get_contents( $core . '/ghahghah-core.php' );
ghahghah_check( (bool) preg_match( '/Text Domain:\s*ghahghah-core\b/', $core_main ), 'Core text domain is ghahghah-core' );
ghahghah_check( str_contains( $core_main, 'GHAHGHAH_CORE_VERSION' ), 'Core defines GHAHGHAH_CORE_VERSION' );

$product = file_get_contents( $core . '/src/PostTypes/Product.php' );
ghahghah_check( str_contains( $product, "POST_TYPE = 'ghahghah_product'" ), 'Product CPT key is ghahghah_product' );
ghahghah_check( str_contains( $product, 'show_in_rest' ), 'Product CPT enables REST' );
ghahghah_check( str_contains( $product, 'ghahghah_product_archive_slug' ), 'Archive slug is filterable' );

$activator = file_get_contents( $core . '/src/Activator.php' );
$deactivator = file_get_contents( $core . '/src/Deactivator.php' );
ghahghah_check( str_contains( $activator, 'flush_rewrite_rules' ), 'Flush on activation' );
ghahghah_check( str_contains( $deactivator, 'flush_rewrite_rules' ), 'Flush on deactivation' );

// Ensure theme does not register the CPT.
$theme_php = '';
$iterator  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme ) );
foreach ( $iterator as $file ) {
	if ( $file->isFile() && str_ends_with( $file->getFilename(), '.php' ) ) {
		$theme_php .= file_get_contents( $file->getPathname() );
	}
}
ghahghah_check( ! str_contains( $theme_php, 'register_post_type' ), 'Theme does not register post types' );
ghahghah_check( str_contains( $theme_php, 'ghahghah_is_core_active' ), 'Theme has Core inactive fallback helper' );
ghahghah_check( str_contains( $theme_php, 'ghahghah_register_config_menu' ), 'Theme registers configuration admin menu' );
ghahghah_check( str_contains( $theme_php, 'GHAHGHAH_CONFIG_TAB_PARAM' ), 'Theme config uses tab URL param' );
ghahghah_check( str_contains( $theme_php, 'ghahghah_header' ), 'Theme registers ghahghah_header Customizer section' );
ghahghah_check( str_contains( $theme_php, 'Ghahghah_Primary_Nav_Walker' ), 'Theme defines primary nav walker' );
ghahghah_check( str_contains( $theme_php, 'ghahghah_get_header_logo_url' ), 'Theme resolves header logo URLs' );
ghahghah_check( str_contains( file_get_contents( $theme . '/assets/css/fonts.css' ), 'Yekan Bakh FaNum' ), 'Theme bundles Yekan Bakh font face' );

$setup = file_get_contents( $theme . '/inc/setup.php' );
ghahghah_check( str_contains( $setup, "'primary'" ) && str_contains( $setup, "'footer'" ) && str_contains( $setup, "'legal'" ), 'Menu locations registered' );

$forbidden_patterns = array(
	'elementor'              => '/\belementor\b/i',
	'woocommerce'            => '/\bwoocommerce\b/i',
	'advanced-custom-fields' => '/advanced-custom-fields/i',
	'acf/'                   => '/\bacf\//i',
	'Bootstrap framework'    => '/\btwitter-bootstrap\b|bootstrap\.(min\.)?(css|js)\b|getbootstrap\.com/i',
	'jQuery dependency'      => '/\bjquery(\.min)?\.js\b|wp_enqueue_script\s*\(\s*[\'"]jquery[\'"]/i',
	'Tailwind'               => '/\btailwind(css)?\b/i',
);

$scan_files = array(
	$theme . '/inc/assets.php',
	$theme . '/functions.php',
	$core . '/ghahghah-core.php',
	$core . '/composer.json',
	$theme . '/style.css',
);
$haystack = '';
foreach ( $scan_files as $scan_file ) {
	$haystack .= file_get_contents( $scan_file );
}
$haystack .= $theme_php . $product;

foreach ( $forbidden_patterns as $label => $pattern ) {
	ghahghah_check( 1 !== preg_match( $pattern, $haystack ), "No dependency reference: $label" );
}

// Colors present in theme.json and CSS.
$theme_json = file_get_contents( $theme . '/theme.json' );
$base_css   = file_get_contents( $theme . '/assets/css/base.css' );
foreach ( array( '#D71920', '#F5B400', '#3A8F45', '#222222', '#FFFFFF', '#F7F7F5' ) as $hex ) {
	ghahghah_check( str_contains( $theme_json, $hex ), "theme.json contains $hex" );
	ghahghah_check( str_contains( strtolower( $base_css ), strtolower( $hex ) ), "base.css contains $hex" );
}

// Fabricated business data heuristics (avoid bare "certificate": factory page has a real certificates UI section).
$all_text = $theme_php . $core_main;
foreach ( array( 'kcal', 'nutrition', 'ISO ', 'factory capacity', '021-', '+98' ) as $bad ) {
	ghahghah_check( ! str_contains( strtolower( $all_text ), strtolower( $bad ) ), "No fabricated marker: $bad" );
}

echo PHP_EOL . ( 0 === $fail ? 'Structural verification passed.' : "Structural verification failed ($fail)." ) . PHP_EOL;
exit( $fail > 0 ? 1 : 0 );
