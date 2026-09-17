<?php
/**
 * Front-end SEO: document titles, meta descriptions, Open Graph, Twitter, JSON-LD.
 *
 * Yields to Yoast / Rank Math / SEOPress when those plugins are active.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme mod defaults for editable SEO fields.
 *
 * @return array<string, mixed>
 */
function ghahghah_seo_setting_defaults(): array {
	return array(
		'ghahghah_seo_home_title'       => __( 'قهقهه | اسنک ذرت ترد با طعم‌های متنوع', 'ghahghah' ),
		'ghahghah_seo_home_description' => __( 'قهقهه، تولیدکننده اسنک ذرت ترد در سنندج؛ کاتالوگ محصولات، معرفی کارخانه، خرید عمده و درخواست نمایندگی.', 'ghahghah' ),
		'ghahghah_seo_home_h1'          => __( 'قهقهه؛ طعم شادی با اسنک ذرت', 'ghahghah' ),
		'ghahghah_seo_og_image_id'      => 0,
	);
}

/**
 * Get an SEO theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_seo_mod( string $key ) {
	$defaults = ghahghah_seo_setting_defaults();
	return get_theme_mod( $key, $defaults[ $key ] ?? null );
}

/**
 * Whether a major SEO plugin already owns head tags.
 */
function ghahghah_seo_external_plugin_active(): bool {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| class_exists( 'WPSEO_Frontend', false )
		|| class_exists( 'RankMath', false );
}

/**
 * Collapse whitespace and trim plain text for meta use.
 *
 * @param string $text Raw text.
 */
function ghahghah_seo_plain_text( string $text ): string {
	$text = wp_strip_all_tags( $text );
	$text = preg_replace( '/\s+/u', ' ', $text ) ?? $text;
	return trim( $text );
}

/**
 * Truncate text near a word boundary for meta description length.
 *
 * @param string $text   Plain text.
 * @param int    $max    Max characters.
 */
function ghahghah_seo_truncate( string $text, int $max = 158 ): string {
	$text = ghahghah_seo_plain_text( $text );
	if ( '' === $text || $max < 24 ) {
		return $text;
	}

	$len = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
	if ( $len <= $max ) {
		return $text;
	}

	$slice = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max - 1 ) : substr( $text, 0, $max - 1 );
	$space = function_exists( 'mb_strrpos' ) ? mb_strrpos( $slice, ' ' ) : strrpos( $slice, ' ' );
	if ( false !== $space && $space > (int) ( $max * 0.55 ) ) {
		$slice = function_exists( 'mb_substr' ) ? mb_substr( $slice, 0, $space ) : substr( $slice, 0, $space );
	}

	return rtrim( (string) $slice, " \t.,؛،" ) . '…';
}

/**
 * Site brand name for titles.
 */
function ghahghah_seo_site_name(): string {
	$name = ghahghah_seo_plain_text( (string) get_bloginfo( 'name', 'display' ) );
	return '' !== $name ? $name : 'قهقهه';
}

/**
 * Absolute URL for current view (or home).
 */
function ghahghah_seo_current_url(): string {
	if ( is_singular() ) {
		$url = get_permalink();
		return is_string( $url ) ? $url : home_url( '/' );
	}

	if ( is_home() && ! is_front_page() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page > 0 ) {
			$url = get_permalink( $posts_page );
			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}
	}

	if ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( get_query_var( 'post_type' ) );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$url = get_term_link( $term );
			if ( is_string( $url ) ) {
				return $url;
			}
		}
	}

	if ( is_search() ) {
		return get_search_link();
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	return home_url( $request );
}

/**
 * Default social / OG image URL.
 */
function ghahghah_seo_default_image_url(): string {
	$custom_id = absint( ghahghah_get_seo_mod( 'ghahghah_seo_og_image_id' ) );
	if ( $custom_id > 0 ) {
		$url = wp_get_attachment_image_url( $custom_id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	$site_icon = (int) get_option( 'site_icon' );
	if ( $site_icon > 0 ) {
		$url = wp_get_attachment_image_url( $site_icon, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	if ( function_exists( 'ghahghah_get_bundled_brand_asset_url' ) ) {
		return ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' );
	}

	return GHAHGHAH_THEME_URI . '/assets/images/brand/ghahghah-site-icon-512.png';
}

/**
 * Prefer featured image, then defaults.
 *
 * @param int $post_id Post ID.
 */
function ghahghah_seo_image_for_post( int $post_id ): string {
	if ( $post_id > 0 && has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	return ghahghah_seo_default_image_url();
}

/**
 * First homepage hero banner as social image when available.
 */
function ghahghah_seo_home_image_url(): string {
	$custom_id = absint( ghahghah_get_seo_mod( 'ghahghah_seo_og_image_id' ) );
	if ( $custom_id > 0 ) {
		$url = wp_get_attachment_image_url( $custom_id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	if ( function_exists( 'ghahghah_get_hero_banner_slides' ) ) {
		$slides = ghahghah_get_hero_banner_slides();
		if ( isset( $slides[0]['desktop']['url'] ) && is_string( $slides[0]['desktop']['url'] ) && '' !== $slides[0]['desktop']['url'] ) {
			return $slides[0]['desktop']['url'];
		}
		if ( isset( $slides[0]['mobile']['url'] ) && is_string( $slides[0]['mobile']['url'] ) && '' !== $slides[0]['mobile']['url'] ) {
			return $slides[0]['mobile']['url'];
		}
		if ( isset( $slides[0]['url'] ) && is_string( $slides[0]['url'] ) && '' !== $slides[0]['url'] ) {
			return $slides[0]['url'];
		}
	}

	return ghahghah_seo_default_image_url();
}

/**
 * Screen-reader / document H1 for the front page.
 */
function ghahghah_seo_home_h1(): string {
	$h1 = ghahghah_seo_plain_text( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_h1' ) );
	if ( '' !== $h1 ) {
		return $h1;
	}
	$defaults = ghahghah_seo_setting_defaults();
	return (string) $defaults['ghahghah_seo_home_h1'];
}

/**
 * Resolved SEO context for the current front-end view.
 *
 * @return array{
 *   title: string,
 *   description: string,
 *   image: string,
 *   url: string,
 *   type: string,
 *   omit_site: bool,
 *   robots: string
 * }
 */
function ghahghah_get_seo_context(): array {
	$site = ghahghah_seo_site_name();
	$ctx  = array(
		'title'       => '',
		'description' => '',
		'image'       => ghahghah_seo_default_image_url(),
		'url'         => ghahghah_seo_current_url(),
		'type'        => 'website',
		'omit_site'   => false,
		'robots'      => '',
	);

	if ( is_front_page() ) {
		$title = ghahghah_seo_plain_text( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_title' ) );
		$desc  = ghahghah_seo_plain_text( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_description' ) );
		if ( '' === $title ) {
			$title = (string) ghahghah_seo_setting_defaults()['ghahghah_seo_home_title'];
		}
		if ( '' === $desc ) {
			$desc = (string) ghahghah_seo_setting_defaults()['ghahghah_seo_home_description'];
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		$ctx['image']       = ghahghah_seo_home_image_url();
		$ctx['url']         = home_url( '/' );
		$ctx['omit_site']   = true;
		return $ctx;
	}

	if ( is_home() ) {
		$defaults = function_exists( 'ghahghah_blog_archive_defaults' ) ? ghahghah_blog_archive_defaults() : array();
		$title    = ghahghah_seo_plain_text( (string) ( $defaults['hero_title'] ?? __( 'مقالات قهقهه', 'ghahghah' ) ) );
		$desc     = ghahghah_seo_plain_text( (string) ( $defaults['hero_subtitle'] ?? '' ) );
		if ( '' === $desc ) {
			$desc = __( 'مطالب آموزشی، اخبار و داستان‌های برند قهقهه درباره اسنک ذرت و همکاری تجاری.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( is_post_type_archive( 'ghahghah_product' ) ) {
		$defaults = function_exists( 'ghahghah_products_archive_defaults' ) ? ghahghah_products_archive_defaults() : array();
		$title    = ghahghah_seo_plain_text( (string) ( $defaults['hero_title'] ?? __( 'همه محصولات قهقهه', 'ghahghah' ) ) );
		$desc     = ghahghah_seo_plain_text( (string) ( $defaults['hero_subtitle'] ?? '' ) );
		if ( '' === $desc ) {
			$desc = __( 'کاتالوگ کامل اسنک ذرت قهقهه با طعم‌های متنوع؛ کیفیت ثابت و لذت همیشگی.', 'ghahghah' );
		} else {
			$desc = $desc . ' ' . __( 'کاتالوگ رسمی محصولات برند قهقهه.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( is_singular( 'ghahghah_product' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && function_exists( 'ghahghah_get_single_product_data' ) ) {
			$data  = ghahghah_get_single_product_data( $post );
			$title = ghahghah_seo_plain_text( (string) ( $data['display_title'] ?? get_the_title( $post ) ) );
			$bits  = array_filter(
				array(
					ghahghah_seo_plain_text( (string) ( $data['subtitle'] ?? '' ) ),
					ghahghah_seo_plain_text( (string) ( $data['short_intro'] ?? '' ) ),
				)
			);
			$desc = implode( ' — ', $bits );
			if ( '' === $desc ) {
				$desc = sprintf(
					/* translators: %s: product title */
					__( '%s؛ اسنک ذرت برند قهقهه. مشخصات، طعم و اطلاعات بسته‌بندی را ببینید.', 'ghahghah' ),
					$title
				);
			}
			$ctx['title']       = $title;
			$ctx['description'] = ghahghah_seo_truncate( $desc );
			$ctx['image']       = ghahghah_seo_image_for_post( (int) $post->ID );
			$ctx['type']        = 'product';
			return $ctx;
		}
	}

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$title = ghahghah_seo_plain_text( get_the_title( $post ) );
			$desc  = ghahghah_seo_plain_text( (string) get_the_excerpt( $post ) );
			if ( '' === $desc ) {
				$desc = ghahghah_seo_plain_text( (string) $post->post_content );
			}
			if ( '' === $desc ) {
				$desc = sprintf(
					/* translators: %s: article title */
					__( '%s | مطلبی از مجله قهقهه درباره اسنک و برند غذایی.', 'ghahghah' ),
					$title
				);
			}
			$ctx['title']       = $title;
			$ctx['description'] = ghahghah_seo_truncate( $desc );
			$ctx['image']       = ghahghah_seo_image_for_post( (int) $post->ID );
			$ctx['type']        = 'article';
			return $ctx;
		}
	}

	if ( function_exists( 'ghahghah_is_factory_page' ) && ghahghah_is_factory_page() ) {
		$title = function_exists( 'ghahghah_get_factory_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_title' ) )
			: '';
		$lead  = function_exists( 'ghahghah_get_factory_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_lead' ) )
			: '';
		$intro = function_exists( 'ghahghah_get_factory_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_intro' ) )
			: '';
		$desc  = '' !== $lead ? $lead : $intro;
		if ( '' === $title ) {
			$title = __( 'معرفی کارخانه قهقهه', 'ghahghah' );
		}
		if ( '' === $desc ) {
			$desc = __( 'نگاهی به مسیر تولید، کنترل کیفیت و بسته‌بندی محصولات قهقهه در کارخانه سنندج.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		if ( function_exists( 'ghahghah_get_factory_page_hero_image' ) ) {
			$hero = ghahghah_get_factory_page_hero_image();
			if ( ! empty( $hero['src'] ) && is_string( $hero['src'] ) ) {
				$ctx['image'] = $hero['src'];
			}
		}
		return $ctx;
	}

	if ( function_exists( 'ghahghah_is_contact_page' ) && ghahghah_is_contact_page() ) {
		$title = function_exists( 'ghahghah_get_contact_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_page_title' ) )
			: '';
		$desc  = function_exists( 'ghahghah_get_contact_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_page_lead' ) )
			: '';
		if ( '' === $title ) {
			$title = __( 'تماس با قهقهه', 'ghahghah' );
		}
		if ( '' === $desc ) {
			$desc = __( 'راه‌های ارتباط با قهقهه برای همکاری، خرید عمده و دریافت اطلاعات محصولات.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( function_exists( 'ghahghah_is_wholesale_request_page' ) && ghahghah_is_wholesale_request_page() ) {
		$title = function_exists( 'ghahghah_get_request_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_intro_title' ) )
			: '';
		$desc  = function_exists( 'ghahghah_get_request_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_intro_text' ) )
			: '';
		if ( '' === $title ) {
			$title = __( 'درخواست خرید عمده قهقهه', 'ghahghah' );
		}
		if ( '' === $desc ) {
			$desc = __( 'فرم درخواست خرید عمده محصولات قهقهه؛ اطلاعات سفارش را ثبت کنید تا واحد فروش تماس بگیرد.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( function_exists( 'ghahghah_is_agency_request_page' ) && ghahghah_is_agency_request_page() ) {
		$title = function_exists( 'ghahghah_get_request_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_request_mod( 'ghahghah_agency_intro_title' ) )
			: '';
		$desc  = function_exists( 'ghahghah_get_request_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_request_mod( 'ghahghah_agency_intro_text' ) )
			: '';
		if ( '' === $title ) {
			$title = __( 'درخواست نمایندگی قهقهه', 'ghahghah' );
		}
		if ( '' === $desc ) {
			$desc = __( 'فرم درخواست نمایندگی برند قهقهه؛ اطلاعات همکاری را ثبت کنید تا پس از بررسی تماس گرفته شود.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( function_exists( 'ghahghah_is_faq_page' ) && ghahghah_is_faq_page() ) {
		$title = function_exists( 'ghahghah_get_faq_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_faq_mod( 'ghahghah_faq_intro_title' ) )
			: '';
		$desc  = function_exists( 'ghahghah_get_faq_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_faq_mod( 'ghahghah_faq_intro_text' ) )
			: '';
		if ( '' === $title ) {
			$title = __( 'پرسش‌های متداول قهقهه', 'ghahghah' );
		}
		if ( '' === $desc ) {
			$desc = __( 'پاسخ پرسش‌های رایج درباره محصولات، خرید عمده و همکاری با برند قهقهه.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( function_exists( 'ghahghah_is_privacy_page' ) && ghahghah_is_privacy_page() ) {
		$title = __( 'حریم خصوصی قهقهه', 'ghahghah' );
		$desc  = function_exists( 'ghahghah_get_privacy_page_mod' )
			? ghahghah_seo_plain_text( (string) ghahghah_get_privacy_page_mod( 'ghahghah_privacy_page_lead' ) )
			: '';
		if ( '' === $desc ) {
			$desc = __( 'سیاست حریم خصوصی وب‌سایت قهقهه؛ نحوه جمع‌آوری و استفاده از داده‌ها.', 'ghahghah' );
		}
		$ctx['title']       = $title;
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	if ( is_singular( 'page' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$title = ghahghah_seo_plain_text( get_the_title( $post ) );
			$desc  = ghahghah_seo_plain_text( (string) get_the_excerpt( $post ) );
			if ( '' === $desc ) {
				$desc = ghahghah_seo_plain_text( (string) $post->post_content );
			}
			if ( '' === $desc ) {
				$desc = sprintf(
					/* translators: 1: page title, 2: site name */
					__( '%1$s | صفحه رسمی %2$s.', 'ghahghah' ),
					$title,
					$site
				);
			}
			$ctx['title']       = $title;
			$ctx['description'] = ghahghah_seo_truncate( $desc );
			$ctx['image']       = ghahghah_seo_image_for_post( (int) $post->ID );
			return $ctx;
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$title = ghahghah_seo_plain_text( $term->name );
			$desc  = ghahghah_seo_plain_text( (string) $term->description );
			if ( '' === $desc ) {
				$desc = sprintf(
					/* translators: %s: term name */
					__( 'مطالب مرتبط با «%s» در وب‌سایت قهقهه.', 'ghahghah' ),
					$title
				);
			}
			$ctx['title']       = $title;
			$ctx['description'] = ghahghah_seo_truncate( $desc );
			return $ctx;
		}
	}

	if ( is_search() ) {
		$query = ghahghah_seo_plain_text( get_search_query() );
		$ctx['title']       = '' !== $query
			? sprintf(
				/* translators: %s: search query */
				__( 'نتایج جستجو برای «%s»', 'ghahghah' ),
				$query
			)
			: __( 'نتایج جستجو', 'ghahghah' );
		$ctx['description'] = __( 'نتایج جستجو در وب‌سایت قهقهه.', 'ghahghah' );
		$ctx['robots']      = 'noindex,follow';
		return $ctx;
	}

	if ( is_404() ) {
		$ctx['title']       = __( 'صفحه پیدا نشد', 'ghahghah' );
		$ctx['description'] = __( 'این صفحه در وب‌سایت قهقهه وجود ندارد. به صفحه اصلی یا محصولات بازگردید.', 'ghahghah' );
		$ctx['robots']      = 'noindex,follow';
		return $ctx;
	}

	if ( is_archive() ) {
		$title = ghahghah_seo_plain_text( (string) get_the_archive_title() );
		$desc  = ghahghah_seo_plain_text( (string) get_the_archive_description() );
		if ( '' === $desc ) {
			$desc = sprintf(
				/* translators: %s: archive title */
				__( 'بایگانی «%s» در وب‌سایت قهقهه.', 'ghahghah' ),
				wp_strip_all_tags( $title )
			);
		}
		$ctx['title']       = wp_strip_all_tags( $title );
		$ctx['description'] = ghahghah_seo_truncate( $desc );
		return $ctx;
	}

	$ctx['title']       = $site;
	$ctx['description'] = ghahghah_seo_truncate(
		(string) ghahghah_seo_setting_defaults()['ghahghah_seo_home_description']
	);
	$ctx['omit_site']   = true;
	return $ctx;
}

/**
 * Customize WordPress document title parts.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function ghahghah_filter_document_title_parts( array $parts ): array {
	if ( is_admin() || ghahghah_seo_external_plugin_active() ) {
		return $parts;
	}

	$ctx = ghahghah_get_seo_context();
	if ( '' === $ctx['title'] ) {
		return $parts;
	}

	$parts['title'] = $ctx['title'];
	unset( $parts['tagline'] );

	if ( ! empty( $ctx['omit_site'] ) ) {
		$parts['site'] = '';
	}

	return $parts;
}
add_filter( 'document_title_parts', 'ghahghah_filter_document_title_parts', 20 );

/**
 * Title separator for core title tag.
 *
 * @param string $sep Separator.
 */
function ghahghah_document_title_separator( string $sep ): string {
	if ( ghahghah_seo_external_plugin_active() ) {
		return $sep;
	}
	return '|';
}
add_filter( 'document_title_separator', 'ghahghah_document_title_separator' );

/**
 * Print meta description, robots, Open Graph, and Twitter tags.
 */
function ghahghah_seo_output_head_tags(): void {
	if ( is_admin() || ghahghah_seo_external_plugin_active() ) {
		return;
	}

	$ctx = ghahghah_get_seo_context();
	if ( '' !== $ctx['description'] ) {
		echo '<meta name="description" content="' . esc_attr( $ctx['description'] ) . '" />' . "\n";
	}
	if ( '' !== $ctx['robots'] ) {
		echo '<meta name="robots" content="' . esc_attr( $ctx['robots'] ) . '" />' . "\n";
	}

	$site  = ghahghah_seo_site_name();
	$title = $ctx['title'];
	if ( empty( $ctx['omit_site'] ) && '' !== $title && ! str_contains( $title, $site ) ) {
		$title = $title . ' | ' . $site;
	}

	$og_type = 'website';
	if ( 'article' === $ctx['type'] ) {
		$og_type = 'article';
	} elseif ( 'product' === $ctx['type'] ) {
		$og_type = 'product';
	}

	$tags = array(
		'og:locale'      => 'fa_IR',
		'og:type'        => $og_type,
		'og:site_name'   => $site,
		'og:title'       => $title,
		'og:description' => $ctx['description'],
		'og:url'         => $ctx['url'],
		'og:image'       => $ctx['image'],
	);

	foreach ( $tags as $property => $content ) {
		if ( '' === $content ) {
			continue;
		}
		echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}

	$tw = array(
		'twitter:card'        => 'summary_large_image',
		'twitter:title'       => $title,
		'twitter:description' => $ctx['description'],
		'twitter:image'       => $ctx['image'],
	);
	foreach ( $tw as $name => $content ) {
		if ( '' === $content ) {
			continue;
		}
		echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'ghahghah_seo_output_head_tags', 3 );

/**
 * Organization + WebSite JSON-LD (always when theme owns SEO).
 *
 * @return array<int, array<string, mixed>>
 */
function ghahghah_seo_schema_graphs(): array {
	$site = ghahghah_seo_site_name();
	$home = home_url( '/' );
	$logo = ghahghah_seo_default_image_url();

	$org = array(
		'@type' => 'Organization',
		'@id'   => $home . '#organization',
		'name'  => $site,
		'url'   => $home,
		'logo'  => array(
			'@type' => 'ImageObject',
			'url'   => $logo,
		),
	);

	$phone = '';
	if ( function_exists( 'ghahghah_get_footer_mod' ) ) {
		$phones = ghahghah_seo_plain_text( (string) ghahghah_get_footer_mod( 'ghahghah_footer_phones' ) );
		if ( '' !== $phones ) {
			$lines = preg_split( '/\R/u', $phones ) ?: array();
			$phone = ghahghah_seo_plain_text( (string) ( $lines[0] ?? '' ) );
		}
		$address = ghahghah_seo_plain_text( (string) ghahghah_get_footer_mod( 'ghahghah_footer_address' ) );
		if ( '' !== $address ) {
			$org['address'] = array(
				'@type'         => 'PostalAddress',
				'addressCountry'=> 'IR',
				'streetAddress' => $address,
			);
		}
	}
	if ( '' !== $phone ) {
		$org['telephone'] = $phone;
	}

	$website = array(
		'@type'     => 'WebSite',
		'@id'       => $home . '#website',
		'url'       => $home,
		'name'      => $site,
		'inLanguage'=> 'fa-IR',
		'publisher' => array( '@id' => $home . '#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);

	$graphs = array( $org, $website );
	$ctx    = ghahghah_get_seo_context();

	$graphs[] = array(
		'@type'       => 'WebPage',
		'@id'         => $ctx['url'] . '#webpage',
		'url'         => $ctx['url'],
		'name'        => $ctx['title'],
		'description' => $ctx['description'],
		'isPartOf'    => array( '@id' => $home . '#website' ),
		'inLanguage'  => 'fa-IR',
	);

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$graphs[] = array(
				'@type'            => 'Article',
				'@id'              => $ctx['url'] . '#article',
				'headline'         => $ctx['title'],
				'description'      => $ctx['description'],
				'image'            => array( $ctx['image'] ),
				'datePublished'    => get_the_date( DATE_W3C, $post ),
				'dateModified'     => get_the_modified_date( DATE_W3C, $post ),
				'mainEntityOfPage' => array( '@id' => $ctx['url'] . '#webpage' ),
				'author'           => array(
					'@type' => 'Organization',
					'name'  => $site,
				),
				'publisher'        => array( '@id' => $home . '#organization' ),
			);
		}
	}

	if ( is_singular( 'ghahghah_product' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$product = array(
				'@type'       => 'Product',
				'@id'         => $ctx['url'] . '#product',
				'name'        => $ctx['title'],
				'description' => $ctx['description'],
				'image'       => array( $ctx['image'] ),
				'brand'       => array(
					'@type' => 'Brand',
					'name'  => $site,
				),
				'url'         => $ctx['url'],
			);
			if ( function_exists( 'ghahghah_get_single_product_data' ) ) {
				$data = ghahghah_get_single_product_data( $post );
				if ( ! empty( $data['flavour'] ) ) {
					$product['category'] = ghahghah_seo_plain_text( (string) $data['flavour'] );
				}
			}
			$graphs[] = $product;
		}
	}

	if ( function_exists( 'ghahghah_is_faq_page' ) && ghahghah_is_faq_page() && function_exists( 'ghahghah_get_faq_groups' ) ) {
		$entities = array();
		foreach ( ghahghah_get_faq_groups() as $group ) {
			if ( empty( $group['items'] ) || ! is_array( $group['items'] ) ) {
				continue;
			}
			foreach ( $group['items'] as $item ) {
				$q = ghahghah_seo_plain_text( (string) ( $item['question'] ?? $item['q'] ?? '' ) );
				$a = ghahghah_seo_plain_text( (string) ( $item['answer'] ?? $item['a'] ?? '' ) );
				if ( '' === $q || '' === $a ) {
					continue;
				}
				$entities[] = array(
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $a,
					),
				);
			}
		}
		if ( $entities ) {
			$graphs[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $ctx['url'] . '#faq',
				'mainEntity' => $entities,
			);
		}
	}

	return $graphs;
}

/**
 * Print JSON-LD graph.
 */
function ghahghah_seo_output_schema(): void {
	if ( is_admin() || ghahghah_seo_external_plugin_active() ) {
		return;
	}

	$graphs = ghahghah_seo_schema_graphs();
	if ( ! $graphs ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graphs,
	);

	$json = wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( ! is_string( $json ) || '' === $json ) {
		return;
	}

	echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD from controlled arrays.
}
add_action( 'wp_head', 'ghahghah_seo_output_schema', 4 );
