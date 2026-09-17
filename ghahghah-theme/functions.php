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

define( 'GHAHGHAH_THEME_VERSION', '0.9.60' );
define( 'GHAHGHAH_THEME_DIR', get_template_directory() );
define( 'GHAHGHAH_THEME_URI', get_template_directory_uri() );

$ghahghah_includes = array(
	'/inc/setup.php',
	'/inc/assets.php',
	'/inc/template-tags.php',
	'/inc/jalali-date.php',
	'/inc/header-settings.php',
	'/inc/footer-settings.php',
	'/inc/hero-settings.php',
	'/inc/featured-settings.php',
	'/inc/factory-settings.php',
	'/inc/factory-page-settings.php',
	'/inc/steps-settings.php',
	'/inc/collab-settings.php',
	'/inc/articles-settings.php',
	'/inc/form-icons.php',
	'/inc/form-fields.php',
	'/inc/sms-settings.php',
	'/inc/request-pages-settings.php',
	'/inc/faq-settings.php',
	'/inc/contact-page-settings.php',
	'/inc/privacy-settings.php',
	'/inc/error-404.php',
	'/inc/products-archive-settings.php',
	'/inc/blog-archive-settings.php',
	'/inc/live-search.php',
	'/inc/media/sync-theme-media.php',
	'/inc/single-product-settings.php',
	'/inc/single-article-settings.php',
	'/inc/nav-sync.php',
	'/inc/bottom-nav.php',
	'/inc/class-primary-nav-walker.php',
	'/inc/url-migration/redirects.php',
);

if ( is_admin() ) {
	$ghahghah_includes[] = '/inc/admin/config.php';
}

foreach ( $ghahghah_includes as $ghahghah_file ) {
	$ghahghah_path = GHAHGHAH_THEME_DIR . $ghahghah_file;

	if ( is_readable( $ghahghah_path ) ) {
		require_once $ghahghah_path;
	}
}
