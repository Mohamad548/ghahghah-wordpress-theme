<?php
/**
 * Header template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'رفتن به محتوای اصلی', 'ghahghah' ); ?></a>

<header class="site-header" role="banner">
	<div class="site-header__inner">
		<div class="site-brand">
			<?php ghahghah_the_site_brand(); ?>
		</div>

		<button
			type="button"
			class="site-header__toggle"
			data-ghahghah-nav-toggle
			aria-expanded="false"
			aria-controls="primary-navigation"
		>
			<span class="screen-reader-text"><?php esc_html_e( 'باز و بسته کردن منو', 'ghahghah' ); ?></span>
			<span aria-hidden="true">☰</span>
		</button>

		<div id="primary-navigation" class="site-header__nav" data-ghahghah-nav-panel>
			<?php ghahghah_the_nav( 'primary', 'site-nav--primary' ); ?>
		</div>
	</div>
</header>
