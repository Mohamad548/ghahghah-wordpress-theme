<?php
/**
 * Configure Yoast SEO on local :8898 — run via: wp eval-file configure-yoast-8898.php
 * Not committed as a permanent theme feature; kept under artifacts for reproducibility.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}

// Discourage indexing on this local test environment.
update_option( 'blog_public', '0' );

$wpseo = get_option( 'wpseo', array() );
if ( ! is_array( $wpseo ) ) {
	$wpseo = array();
}
$wpseo['company_or_person']       = 'company';
$wpseo['company_name']            = get_bloginfo( 'name', 'display' );
$wpseo['company_logo_id']         = 157; // ghahghah-logo-desktop.webp (existing upload)
$wpseo['company_logo']            = wp_get_attachment_url( 157 ) ?: '';
$wpseo['company_logo_meta']       = array();
$wpseo['person_logo_id']          = 0;
$wpseo['website_name']            = get_bloginfo( 'name', 'display' );
$wpseo['alternate_website_name']  = get_bloginfo( 'description', 'display' );
$wpseo['enable_xml_sitemap']      = true;
$wpseo['enable_index_now']        = false;
$wpseo['enable_enhanced_slack_sharing'] = true;
// Do not ping / verify search engines from localhost.
$wpseo['googleverify'] = '';
$wpseo['msverify']     = '';
$wpseo['baiduverify']  = '';
$wpseo['yandexverify'] = '';
$wpseo['ahrefsverify'] = '';
update_option( 'wpseo', $wpseo );

$social = get_option( 'wpseo_social', array() );
if ( ! is_array( $social ) ) {
	$social = array();
}
$social['og_default_image_id'] = 157;
$social['og_default_image']    = wp_get_attachment_url( 157 ) ?: '';
$social['opengraph']           = true;
$social['twitter']             = true;
$social['twitter_card_type']   = 'summary_large_image';
update_option( 'wpseo_social', $social );

$titles = get_option( 'wpseo_titles', array() );
if ( ! is_array( $titles ) ) {
	$titles = array();
}
$titles['website_name']           = get_bloginfo( 'name', 'display' );
$titles['company_or_person']      = 'company';
$titles['company_name']           = get_bloginfo( 'name', 'display' );
$titles['company_logo_id']        = 157;
$titles['separator']              = 'sc-dash';
$titles['forcerewritetitle']      = false;
$titles['noindex-search']         = true;
$titles['noindex-post_format']    = true;
$titles['disable-author']         = true;
$titles['noindex-author-wp-url']  = true;
$titles['disable-date']           = true;
$titles['noindex-archive-wpseo']  = true;
$titles['breadcrumbs-enable']     = false;
// Custom product CPT — treat as public content in Yoast, not WooCommerce.
$titles['noindex-ptarchive-ghahghah_product'] = false;
$titles['noindex-ghahghah_product']           = false;
$titles['display-metabox-pt-ghahghah_product'] = true;
// Inquiry CPT must stay out of public SEO surfaces.
$titles['noindex-ghahghah_inquiry']            = true;
$titles['display-metabox-pt-ghahghah_inquiry']  = false;
$titles['noindex-ptarchive-ghahghah_inquiry']  = true;
// Home / archives title templates using real site tokens only.
$titles['title-home-wpseo']       = '%%sitename%% %%page%% %%sep%% %%sitedesc%%';
$titles['metadesc-home-wpseo']    = '%%sitedesc%%';
$titles['title-ghahghah_product'] = '%%title%% %%page%% %%sep%% %%sitename%%';
$titles['title-post']             = '%%title%% %%page%% %%sep%% %%sitename%%';
$titles['title-page']             = '%%title%% %%page%% %%sep%% %%sitename%%';
$titles['title-archive-wpseo']    = '%%date%% %%page%% %%sep%% %%sitename%%';
$titles['noindex-404']            = true; // if supported; otherwise WP/Yoast default
update_option( 'wpseo_titles', $titles );

// Site icon from existing upload when unset.
if ( ! (int) get_option( 'site_icon' ) ) {
	update_option( 'site_icon', 161 );
}

/**
 * Page-level titles/descriptions from existing on-page copy (H1 / page purpose).
 * No invented prices, ratings, or commercial claims.
 */
$page_seo = array(
	// Home (front page id 6)
	6  => array(
		'title'   => 'قهقهه %%sep%% برند غذایی قهقهه',
		'metadesc' => 'برند غذایی قهقهه؛ معرفی محصولات اسنک ذرت و محتوای سایت قهقهه.',
	),
	// Products page (may redirect/archive — also set archive via titles)
	7  => array(
		'title'   => 'محصولات %%sep%% قهقهه',
		'metadesc' => 'فهرست محصولات اسنک ذرت قهقهه با طعم‌های گوناگون.',
	),
	9  => array(
		'title'   => 'مقالات %%sep%% قهقهه',
		'metadesc' => 'مقالات و راهنماهای قهقهه درباره اسنک و سرو.',
	),
	10 => array(
		'title'   => 'تماس با ما %%sep%% قهقهه',
		'metadesc' => 'راه‌های ارتباط با قهقهه برای پرسش و پیگیری درخواست‌ها.',
	),
	5  => array(
		'title'   => 'درخواست خرید عمده %%sep%% قهقهه',
		'metadesc' => 'فرم درخواست خرید عمده محصولات قهقهه.',
	),
	32 => array(
		'title'   => 'درخواست نمایندگی %%sep%% قهقهه',
		'metadesc' => 'فرم درخواست نمایندگی قهقهه.',
	),
	8  => array(
		'title'   => 'کارخانه %%sep%% قهقهه',
		'metadesc' => 'آشنایی با کارخانه و تولید قهقهه.',
	),
);

foreach ( $page_seo as $post_id => $meta ) {
	if ( ! get_post( $post_id ) ) {
		continue;
	}
	update_post_meta( $post_id, '_yoast_wpseo_title', $meta['title'] );
	update_post_meta( $post_id, '_yoast_wpseo_metadesc', $meta['metadesc'] );
}

// Product archive description via titles option.
$titles = get_option( 'wpseo_titles', array() );
$titles['title-ptarchive-ghahghah_product']    = 'محصولات %%sep%% %%sitename%%';
$titles['metadesc-ptarchive-ghahghah_product'] = 'فهرست محصولات اسنک ذرت قهقهه با طعم‌های گوناگون.';
$titles['title-ptarchive-post']                = 'مقالات %%sep%% %%sitename%%';
update_option( 'wpseo_titles', $titles );

if ( class_exists( 'WPSEO_Options' ) ) {
	WPSEO_Options::clear_cache();
}

echo "OK blog_public=" . get_option( 'blog_public' ) . " company=" . get_bloginfo( 'name' ) . " logo=" . ( wp_get_attachment_url( 157 ) ?: 'missing' ) . "\n";
