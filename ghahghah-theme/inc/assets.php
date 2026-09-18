<?php
/**
 * Front-end and editor asset loading.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue theme styles and scripts.
 */
function ghahghah_enqueue_assets(): void {
	$theme_version = GHAHGHAH_THEME_VERSION;

	// One global stylesheet (fonts + base + layout + header + footer + bottom-nav).
	wp_enqueue_style(
		'ghahghah-core',
		GHAHGHAH_THEME_URI . '/assets/css/core.css',
		array(),
		$theme_version
	);

	if ( is_front_page() && function_exists( 'ghahghah_should_render_hero' ) && ghahghah_should_render_hero() ) {
		wp_enqueue_style(
			'ghahghah-hero',
			GHAHGHAH_THEME_URI . '/assets/css/hero.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		$hero_script = GHAHGHAH_THEME_DIR . '/assets/js/hero.js';
		if ( is_readable( $hero_script ) ) {
			wp_enqueue_script(
				'ghahghah-hero',
				GHAHGHAH_THEME_URI . '/assets/js/hero.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( is_front_page() ) {
		wp_enqueue_style(
			'ghahghah-featured',
			GHAHGHAH_THEME_URI . '/assets/css/featured.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		wp_enqueue_style(
			'ghahghah-factory',
			GHAHGHAH_THEME_URI . '/assets/css/factory.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		wp_enqueue_style(
			'ghahghah-steps',
			GHAHGHAH_THEME_URI . '/assets/css/production-steps.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		wp_enqueue_style(
			'ghahghah-collab',
			GHAHGHAH_THEME_URI . '/assets/css/collab.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		wp_enqueue_style(
			'ghahghah-forms',
			GHAHGHAH_THEME_URI . '/assets/css/forms.css',
			array( 'ghahghah-collab' ),
			$theme_version
		);

		wp_enqueue_style(
			'ghahghah-articles',
			GHAHGHAH_THEME_URI . '/assets/css/articles.css',
			array( 'ghahghah-core' ),
			$theme_version
		);

		$featured_script = GHAHGHAH_THEME_DIR . '/assets/js/featured.js';
		if ( is_readable( $featured_script ) ) {
			wp_enqueue_script(
				'ghahghah-featured',
				GHAHGHAH_THEME_URI . '/assets/js/featured.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}

		$articles_script = GHAHGHAH_THEME_DIR . '/assets/js/articles.js';
		if ( is_readable( $articles_script ) ) {
			wp_enqueue_script(
				'ghahghah-articles',
				GHAHGHAH_THEME_URI . '/assets/js/articles.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}

		$forms_script = GHAHGHAH_THEME_DIR . '/assets/js/forms.js';
		if ( is_readable( $forms_script ) ) {
			wp_enqueue_script(
				'ghahghah-forms',
				GHAHGHAH_THEME_URI . '/assets/js/forms.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( is_search() ) {
		wp_enqueue_style(
			'ghahghah-search',
			GHAHGHAH_THEME_URI . '/assets/css/search.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
	}

	if ( function_exists( 'ghahghah_is_faq_page' ) && ghahghah_is_faq_page() ) {
		wp_enqueue_style(
			'ghahghah-faq',
			GHAHGHAH_THEME_URI . '/assets/css/faq.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
	}

	if ( function_exists( 'ghahghah_is_products_archive' ) && ghahghah_is_products_archive() ) {
		wp_enqueue_style(
			'ghahghah-products-archive',
			GHAHGHAH_THEME_URI . '/assets/css/products-archive.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		$pa_script = GHAHGHAH_THEME_DIR . '/assets/js/products-archive.js';
		if ( is_readable( $pa_script ) ) {
			wp_enqueue_script(
				'ghahghah-products-archive',
				GHAHGHAH_THEME_URI . '/assets/js/products-archive.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( function_exists( 'ghahghah_is_blog_archive' ) && ghahghah_is_blog_archive() ) {
		wp_enqueue_style(
			'ghahghah-blog-archive',
			GHAHGHAH_THEME_URI . '/assets/css/blog-archive.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		$ba_script = GHAHGHAH_THEME_DIR . '/assets/js/blog-archive.js';
		if ( is_readable( $ba_script ) ) {
			wp_enqueue_script(
				'ghahghah-blog-archive',
				GHAHGHAH_THEME_URI . '/assets/js/blog-archive.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( function_exists( 'ghahghah_is_single_product' ) && ghahghah_is_single_product() ) {
		wp_enqueue_style(
			'ghahghah-single-product',
			GHAHGHAH_THEME_URI . '/assets/css/single-product.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		$sp_script = GHAHGHAH_THEME_DIR . '/assets/js/single-product.js';
		if ( is_readable( $sp_script ) ) {
			wp_enqueue_script(
				'ghahghah-single-product',
				GHAHGHAH_THEME_URI . '/assets/js/single-product.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( function_exists( 'ghahghah_is_single_article' ) && ghahghah_is_single_article() ) {
		wp_enqueue_style(
			'ghahghah-single-article',
			GHAHGHAH_THEME_URI . '/assets/css/single-article.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		$sa_script = GHAHGHAH_THEME_DIR . '/assets/js/single-article.js';
		if ( is_readable( $sa_script ) ) {
			wp_enqueue_script(
				'ghahghah-single-article',
				GHAHGHAH_THEME_URI . '/assets/js/single-article.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( function_exists( 'ghahghah_is_factory_page' ) && ghahghah_is_factory_page() ) {
		wp_enqueue_style(
			'ghahghah-factory-page',
			GHAHGHAH_THEME_URI . '/assets/css/pages/factory.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
	}

	if ( function_exists( 'ghahghah_is_privacy_page' ) && ghahghah_is_privacy_page() ) {
		wp_enqueue_style(
			'ghahghah-privacy-page',
			GHAHGHAH_THEME_URI . '/assets/css/pages/privacy.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
	}

	if ( is_404() ) {
		wp_enqueue_style(
			'ghahghah-404',
			GHAHGHAH_THEME_URI . '/assets/css/pages/404.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
	}

	if ( function_exists( 'ghahghah_is_contact_page' ) && ghahghah_is_contact_page() ) {
		wp_enqueue_style(
			'ghahghah-forms',
			GHAHGHAH_THEME_URI . '/assets/css/forms.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		wp_enqueue_style(
			'ghahghah-contact-page',
			GHAHGHAH_THEME_URI . '/assets/css/pages/contact.css',
			array( 'ghahghah-forms' ),
			$theme_version
		);
		$forms_script = GHAHGHAH_THEME_DIR . '/assets/js/forms.js';
		if ( is_readable( $forms_script ) ) {
			wp_enqueue_script(
				'ghahghah-forms',
				GHAHGHAH_THEME_URI . '/assets/js/forms.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
		$map_script = GHAHGHAH_THEME_DIR . '/assets/js/contact-map.js';
		if ( is_readable( $map_script ) ) {
			wp_enqueue_script(
				'ghahghah-contact-map',
				GHAHGHAH_THEME_URI . '/assets/js/contact-map.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	if ( ghahghah_is_request_page() ) {
		wp_enqueue_style(
			'ghahghah-forms',
			GHAHGHAH_THEME_URI . '/assets/css/forms.css',
			array( 'ghahghah-core' ),
			$theme_version
		);
		if ( function_exists( 'ghahghah_is_agency_request_page' ) && ghahghah_is_agency_request_page() ) {
			wp_enqueue_style(
				'ghahghah-agency-request',
				GHAHGHAH_THEME_URI . '/assets/css/pages/agency-request.css',
				array( 'ghahghah-forms' ),
				$theme_version
			);
		} elseif ( function_exists( 'ghahghah_is_wholesale_request_page' ) && ghahghah_is_wholesale_request_page() ) {
			wp_enqueue_style(
				'ghahghah-wholesale-request',
				GHAHGHAH_THEME_URI . '/assets/css/pages/wholesale-request.css',
				array( 'ghahghah-forms' ),
				$theme_version
			);
		} else {
			wp_enqueue_style(
				'ghahghah-request-pages',
				GHAHGHAH_THEME_URI . '/assets/css/request-pages.css',
				array( 'ghahghah-forms' ),
				$theme_version
			);
		}
		$forms_script = GHAHGHAH_THEME_DIR . '/assets/js/forms.js';
		if ( is_readable( $forms_script ) ) {
			wp_enqueue_script(
				'ghahghah-forms',
				GHAHGHAH_THEME_URI . '/assets/js/forms.js',
				array(),
				$theme_version,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}

	$header_script = GHAHGHAH_THEME_DIR . '/assets/js/header.js';

	if ( is_readable( $header_script ) ) {
		wp_enqueue_script(
			'ghahghah-header',
			GHAHGHAH_THEME_URI . '/assets/js/header.js',
			array(),
			$theme_version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_localize_script(
			'ghahghah-header',
			'ghahghahHeader',
			array(
				'liveSearchUrl' => esc_url_raw( rest_url( 'ghahghah/v1/live-search' ) ),
				'i18n'          => array(
					'empty'   => __( 'نتیجه‌ای یافت نشد', 'ghahghah' ),
					'loading' => __( 'در حال جستجو…', 'ghahghah' ),
					'more'    => __( 'مشاهده همه نتایج', 'ghahghah' ),
					'error'   => __( 'خطا در جستجو. دوباره تلاش کنید.', 'ghahghah' ),
				),
			)
		);
	}

	$footer_script = GHAHGHAH_THEME_DIR . '/assets/js/footer.js';
	if ( is_readable( $footer_script ) ) {
		wp_enqueue_script(
			'ghahghah-footer',
			GHAHGHAH_THEME_URI . '/assets/js/footer.js',
			array(),
			$theme_version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	$bottom_script = GHAHGHAH_THEME_DIR . '/assets/js/mobile-bottom-nav.js';
	if ( is_readable( $bottom_script ) ) {
		wp_enqueue_script(
			'ghahghah-mobile-bottom-nav',
			GHAHGHAH_THEME_URI . '/assets/js/mobile-bottom-nav.js',
			array(),
			$theme_version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ghahghah_enqueue_assets' );

/**
 * Preload primary Yekan faces so font-display:optional can apply without late swap CLS.
 */
function ghahghah_preload_primary_fonts(): void {
	$base = GHAHGHAH_THEME_URI . '/assets/fonts/yekan-bakh/';
	$files = array(
		'YekanBakhFaNum-Regular.woff',
		'YekanBakhFaNum-SemiBold.woff',
	);

	foreach ( $files as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff" crossorigin>' . "\n",
			esc_url( $base . $file )
		);
	}
}
add_action( 'wp_head', 'ghahghah_preload_primary_fonts', 1 );
