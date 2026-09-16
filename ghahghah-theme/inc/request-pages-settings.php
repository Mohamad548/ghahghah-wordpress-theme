<?php
/**
 * Dedicated wholesale / agency request page settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defaults for request page theme mods / options.
 *
 * @return array<string, mixed>
 */
function ghahghah_request_pages_defaults(): array {
	return array(
		'ghahghah_wholesale_page_id'           => 0,
		'ghahghah_agency_page_id'              => 0,
		'ghahghah_wholesale_intro_eyebrow'     => '',
		'ghahghah_wholesale_intro_title'       => __( 'درخواست خرید عمده', 'ghahghah' ),
		'ghahghah_wholesale_intro_text'        => __( 'اطلاعات سفارش را ثبت کنید تا واحد فروش با شما تماس بگیرد.', 'ghahghah' ),
		'ghahghah_wholesale_products_label'    => __( 'مشاهده محصولات', 'ghahghah' ),
		'ghahghah_wholesale_hero_badge'        => __( 'طعم پیتزا', 'ghahghah' ),
		'ghahghah_wholesale_footer_motto'      => __( 'قهقهه همراه کسب‌وکارهای موفق!', 'ghahghah' ),
		'ghahghah_wholesale_form_title'        => __( 'اطلاعات درخواست خرید عمده', 'ghahghah' ),
		'ghahghah_wholesale_steps_title'       => __( 'مراحل بررسی درخواست', 'ghahghah' ),
		'ghahghah_wholesale_sms_note'          => __( 'درخواست در سیستم ثبت می‌شود و نتیجه از طریق پیامک اطلاع‌رسانی خواهد شد.', 'ghahghah' ),
		'ghahghah_wholesale_price_note'        => __( 'نمایش قیمت پس از بررسی شرایط سفارش انجام می‌شود.', 'ghahghah' ),
		'ghahghah_wholesale_cross_title'       => __( 'برای همکاری در پخش؟', 'ghahghah' ),
		'ghahghah_wholesale_cross_text'        => __( 'فرم درخواست نمایندگی را تکمیل کنید.', 'ghahghah' ),
		'ghahghah_wholesale_cross_button'      => __( 'درخواست نمایندگی', 'ghahghah' ),
		'ghahghah_agency_intro_eyebrow'        => __( 'طعم لبخند در کنار شما', 'ghahghah' ),
		'ghahghah_agency_intro_title'          => __( 'درخواست نمایندگی قهقهه', 'ghahghah' ),
		'ghahghah_agency_intro_text'           => __( 'اطلاعات همکاری خود را ثبت کنید تا پس از بررسی با شما تماس گرفته شود.', 'ghahghah' ),
		'ghahghah_agency_hero_tagline'         => __( 'با قهقهه بازار را لذیذتر کنید!', 'ghahghah' ),
		'ghahghah_agency_footer_motto'         => __( 'آغاز یک همکاری موفق', 'ghahghah' ),
		'ghahghah_agency_form_title'           => __( 'اطلاعات متقاضی نمایندگی', 'ghahghah' ),
		'ghahghah_agency_steps_title'          => __( 'مسیر بررسی همکاری', 'ghahghah' ),
		'ghahghah_agency_cross_title'          => __( 'قصد خرید عمده دارید؟', 'ghahghah' ),
		'ghahghah_agency_cross_text'           => __( 'درخواست محصول را در فرم خرید عمده ثبت کنید.', 'ghahghah' ),
		'ghahghah_agency_cross_button'         => __( 'خرید عمده', 'ghahghah' ),
		'ghahghah_wholesale_image_id'          => 0,
	);
}

/**
 * Get a request-pages theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_request_mod( string $key ) {
	$defaults = ghahghah_request_pages_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Resolve a published page ID from theme mod (0 if invalid).
 */
function ghahghah_get_request_page_id( string $which ): int {
	$key = 'agency' === $which ? 'ghahghah_agency_page_id' : 'ghahghah_wholesale_page_id';
	$id  = absint( ghahghah_get_request_mod( $key ) );
	if ( $id <= 0 ) {
		return 0;
	}
	$page = get_post( $id );
	if ( ! $page || 'page' !== $page->post_type || ! is_post_publicly_viewable( $page ) ) {
		return 0;
	}
	return $id;
}

/**
 * Public URL for wholesale or agency request page.
 */
function ghahghah_get_request_page_url( string $which ): string {
	$id = ghahghah_get_request_page_id( $which );
	if ( $id > 0 ) {
		$url = get_permalink( $id );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Wholesale page URL (empty when unset).
 */
function ghahghah_get_wholesale_form_url(): string {
	$url = ghahghah_get_request_page_url( 'wholesale' );
	return '' !== $url ? $url : home_url( '/' );
}

/**
 * Agency page URL (empty when unset falls back home).
 */
function ghahghah_get_agency_form_url(): string {
	$url = ghahghah_get_request_page_url( 'agency' );
	return '' !== $url ? $url : home_url( '/' );
}

/**
 * Wholesale URL with optional product preselect.
 */
function ghahghah_get_wholesale_form_url_for_product( int $product_id ): string {
	$base = ghahghah_get_wholesale_form_url();
	$product_id = absint( $product_id );
	if ( $product_id <= 0 || ! ghahghah_is_valid_request_product( $product_id ) ) {
		return $base;
	}
	return add_query_arg( 'product', $product_id, $base );
}

/**
 * Whether a product ID may be preselected / submitted.
 */
function ghahghah_is_valid_request_product( int $product_id ): bool {
	if ( $product_id <= 0 || ! post_type_exists( 'ghahghah_product' ) ) {
		return false;
	}
	$post = get_post( $product_id );
	return $post instanceof WP_Post
		&& 'ghahghah_product' === $post->post_type
		&& 'publish' === $post->post_status;
}

/**
 * Steps for aside / accordion.
 *
 * @param string $which wholesale|agency.
 * @return array<int, array{title: string, text: string, icon: string}>
 */
function ghahghah_get_request_steps( string $which ): array {
	if ( 'agency' === $which ) {
		return array(
			array(
				'title' => __( 'ثبت اطلاعات', 'ghahghah' ),
				'text'  => __( 'فرم درخواست را تکمیل کرده و اطلاعات خود را ثبت کنید.', 'ghahghah' ),
				'icon'  => 'clipboard',
			),
			array(
				'title' => __( 'بررسی درخواست', 'ghahghah' ),
				'text'  => __( 'اطلاعات شما توسط تیم ما بررسی خواهد شد.', 'ghahghah' ),
				'icon'  => 'search',
			),
			array(
				'title' => __( 'تماس برای ادامه همکاری', 'ghahghah' ),
				'text'  => __( 'پس از بررسی، با شما تماس گرفته خواهد شد.', 'ghahghah' ),
				'icon'  => 'handshake',
			),
		);
	}

	return array(
		array(
			'title' => __( 'ثبت درخواست', 'ghahghah' ),
			'text'  => __( 'اطلاعات سفارش خود را در فرم ثبت کنید.', 'ghahghah' ),
			'icon'  => 'request',
			'tone'  => 'pink',
		),
		array(
			'title' => __( 'بررسی واحد فروش', 'ghahghah' ),
			'text'  => __( 'درخواست شما توسط کارشناسان بررسی می‌شود.', 'ghahghah' ),
			'icon'  => 'search',
			'tone'  => 'yellow',
		),
		array(
			'title' => __( 'تماس با متقاضی', 'ghahghah' ),
			'text'  => __( 'واحد فروش برای هماهنگی با شما تماس خواهد گرفت.', 'ghahghah' ),
			'icon'  => 'phone-call',
			'tone'  => 'green',
		),
	);
}

/**
 * Bundled wholesale intro image URLs (responsive).
 * Prefer admin media, else optimized pizza packshot.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function ghahghah_get_wholesale_intro_image(): array {
	$attachment_id = absint( ghahghah_get_request_mod( 'ghahghah_wholesale_image_id' ) );
	if ( $attachment_id > 0 ) {
		$src = wp_get_attachment_image_url( $attachment_id, 'large' );
		if ( is_string( $src ) && '' !== $src ) {
			$srcset = wp_get_attachment_image_srcset( $attachment_id, 'large' );
			return array(
				'src'    => $src,
				'srcset' => is_string( $srcset ) ? $srcset : '',
				'width'  => 1200,
				'height' => 1000,
			);
		}
	}

	return ghahghah_get_wholesale_hero_image();
}

/**
 * Wholesale page hero product image (optimized pizza packshot).
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function ghahghah_get_wholesale_hero_image(): array {
	$base = GHAHGHAH_THEME_URI . '/assets/images/wholesale/';
	return array(
		'src'    => $base . 'ghahghah_pizza_packshot_optimized.webp',
		'srcset' => '',
		'width'  => 1122,
		'height' => 1402,
	);
}

/**
 * Agency page hero product image (optimized parsley-onion packshot).
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function ghahghah_get_agency_hero_image(): array {
	$base = GHAHGHAH_THEME_URI . '/assets/images/agency/';
	return array(
		'src'    => $base . 'ghahghah_parsley_onion_pack_optimized.webp',
		'srcset' => '',
		'width'  => 1122,
		'height' => 1402,
	);
}

/**
 * Whether current query is the wholesale request page template.
 */
function ghahghah_is_wholesale_request_page(): bool {
	return is_page_template( 'page-templates/wholesale-request.php' );
}

/**
 * Whether current query is the agency request page template.
 */
function ghahghah_is_agency_request_page(): bool {
	return is_page_template( 'page-templates/agency-request.php' );
}

/**
 * Whether current query is a request page template.
 */
function ghahghah_is_request_page(): bool {
	return ghahghah_is_wholesale_request_page() || ghahghah_is_agency_request_page();
}
