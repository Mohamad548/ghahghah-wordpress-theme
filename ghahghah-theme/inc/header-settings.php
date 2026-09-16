<?php
/**
 * Header Customizer settings and helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme mod keys and defaults for the header.
 *
 * @return array<string, mixed>
 */
function ghahghah_header_setting_defaults(): array {
	return array(
		'ghahghah_header_logo_width_desktop' => 144,
		'ghahghah_header_logo_width_mobile'  => 112,
		'ghahghah_header_logo_desktop'       => 0,
		'ghahghah_header_logo_mobile'        => 0,
		'ghahghah_header_favicon'            => 0,
		'ghahghah_header_cta_enabled'        => true,
		'ghahghah_header_cta_label'          => __( 'درخواست خرید عمده', 'ghahghah' ),
		'ghahghah_header_cta_page_id'        => 0,
		'ghahghah_header_sticky'             => true,
	);
}

/**
 * Register Customizer section and controls for the header.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function ghahghah_customize_register_header( WP_Customize_Manager $wp_customize ): void {
	$defaults = ghahghah_header_setting_defaults();

	$wp_customize->add_section(
		'ghahghah_header',
		array(
			'title'    => __( 'هدر قهقهه', 'ghahghah' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_logo_width_desktop',
		array(
			'default'           => $defaults['ghahghah_header_logo_width_desktop'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_logo_width_desktop',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_logo_width_desktop',
		array(
			'label'       => __( 'عرض لوگو در دسکتاپ (پیکسل)', 'ghahghah' ),
			'description' => __( 'بین ۸۰ تا ۲۰۰. ارتفاع حداکثر ۶۴ پیکسل رعایت می‌شود.', 'ghahghah' ),
			'section'     => 'ghahghah_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 80,
				'max'  => 200,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_logo_width_mobile',
		array(
			'default'           => $defaults['ghahghah_header_logo_width_mobile'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_logo_width_mobile',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_logo_width_mobile',
		array(
			'label'       => __( 'عرض لوگو در موبایل (پیکسل)', 'ghahghah' ),
			'description' => __( 'بین ۷۲ تا ۱۴۰. ارتفاع حداکثر ۴۴ پیکسل رعایت می‌شود.', 'ghahghah' ),
			'section'     => 'ghahghah_header',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 72,
				'max'  => 140,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_cta_enabled',
		array(
			'default'           => $defaults['ghahghah_header_cta_enabled'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_cta_enabled',
		array(
			'label'   => __( 'نمایش دکمه خرید عمده', 'ghahghah' ),
			'section' => 'ghahghah_header',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_cta_label',
		array(
			'default'           => $defaults['ghahghah_header_cta_label'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_cta_label',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_cta_label',
		array(
			'label'       => __( 'متن دکمه خرید عمده', 'ghahghah' ),
			'description' => __( 'حداکثر ۴۰ نویسه.', 'ghahghah' ),
			'section'     => 'ghahghah_header',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_cta_page_id',
		array(
			'default'           => $defaults['ghahghah_header_cta_page_id'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_cta_page_id',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_cta_page_id',
		array(
			'label'       => __( 'برگه مقصد دکمه خرید عمده', 'ghahghah' ),
			'description' => __( 'فقط برگه‌های منتشرشده و قابل مشاهده عمومی.', 'ghahghah' ),
			'section'     => 'ghahghah_header',
			'type'        => 'dropdown-pages',
		)
	);

	$wp_customize->add_setting(
		'ghahghah_header_sticky',
		array(
			'default'           => $defaults['ghahghah_header_sticky'],
			'type'              => 'theme_mod',
			'capability'        => 'edit_theme_options',
			'sanitize_callback' => 'ghahghah_sanitize_header_checkbox',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_header_sticky',
		array(
			'label'   => __( 'هدر چسبان (Sticky)', 'ghahghah' ),
			'section' => 'ghahghah_header',
			'type'    => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'ghahghah_customize_register_header' );

/**
 * Sanitize checkbox / boolean theme mods.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_header_checkbox( $value ): bool {
	return (bool) $value;
}

/**
 * Sanitize desktop logo width.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_header_logo_width_desktop( $value ): int {
	$width = absint( $value );
	$width = max( 80, min( 200, $width ) );

	return 0 === $width ? (int) ghahghah_header_setting_defaults()['ghahghah_header_logo_width_desktop'] : $width;
}

/**
 * Sanitize mobile logo width.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_header_logo_width_mobile( $value ): int {
	$width = absint( $value );
	$width = max( 72, min( 140, $width ) );

	return 0 === $width ? (int) ghahghah_header_setting_defaults()['ghahghah_header_logo_width_mobile'] : $width;
}

/**
 * Sanitize CTA label (plain text, max 40 chars).
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_header_cta_label( $value ): string {
	$label = sanitize_text_field( (string) $value );

	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $label, 0, 40 );
	}

	return substr( $label, 0, 40 );
}

/**
 * Sanitize CTA destination page ID.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_header_cta_page_id( $value ): int {
	$page_id = absint( $value );

	if ( $page_id <= 0 ) {
		return 0;
	}

	$page = get_post( $page_id );

	if ( ! $page || 'page' !== $page->post_type ) {
		return 0;
	}

	if ( ! is_post_publicly_viewable( $page ) ) {
		return 0;
	}

	return $page_id;
}

/**
 * Whether sticky header is enabled.
 */
function ghahghah_is_header_sticky(): bool {
	$defaults = ghahghah_header_setting_defaults();

	return (bool) get_theme_mod( 'ghahghah_header_sticky', $defaults['ghahghah_header_sticky'] );
}

/**
 * Desktop logo width in pixels.
 */
function ghahghah_get_header_logo_width_desktop(): int {
	$defaults = ghahghah_header_setting_defaults();

	return ghahghah_sanitize_header_logo_width_desktop(
		get_theme_mod(
			'ghahghah_header_logo_width_desktop',
			$defaults['ghahghah_header_logo_width_desktop']
		)
	);
}

/**
 * Mobile logo width in pixels.
 */
function ghahghah_get_header_logo_width_mobile(): int {
	$defaults = ghahghah_header_setting_defaults();

	return ghahghah_sanitize_header_logo_width_mobile(
		get_theme_mod(
			'ghahghah_header_logo_width_mobile',
			$defaults['ghahghah_header_logo_width_mobile']
		)
	);
}

/**
 * Resolved CTA data for front-end render, or null when unavailable.
 *
 * @return array{label: string, url: string}|null
 */
function ghahghah_get_header_cta(): ?array {
	$defaults = ghahghah_header_setting_defaults();

	$enabled = (bool) get_theme_mod( 'ghahghah_header_cta_enabled', $defaults['ghahghah_header_cta_enabled'] );
	if ( ! $enabled ) {
		return null;
	}

	$label = ghahghah_sanitize_header_cta_label(
		get_theme_mod( 'ghahghah_header_cta_label', $defaults['ghahghah_header_cta_label'] )
	);

	if ( '' === $label ) {
		return null;
	}

	$page_id = absint( get_theme_mod( 'ghahghah_header_cta_page_id', $defaults['ghahghah_header_cta_page_id'] ) );

	// Prefer dedicated wholesale request page when configured.
	if ( function_exists( 'ghahghah_get_request_page_url' ) ) {
		$request_url = ghahghah_get_request_page_url( 'wholesale' );
		if ( '' !== $request_url ) {
			return array(
				'label' => $label,
				'url'   => $request_url,
			);
		}
	}

	if ( $page_id > 0 ) {
		$page = get_post( $page_id );
		if ( $page && 'page' === $page->post_type && is_post_publicly_viewable( $page ) ) {
			$url = get_permalink( $page );
			if ( is_string( $url ) && '' !== $url ) {
				return array(
					'label' => $label,
					'url'   => $url,
				);
			}
		}
	}

	return array(
		'label' => $label,
		'url'   => function_exists( 'ghahghah_get_wholesale_form_url' )
			? ghahghah_get_wholesale_form_url()
			: home_url( '/' ),
	);
}

/**
 * Sanitize an attachment ID used for brand assets.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_attachment_id( $value ): int {
	$attachment_id = absint( $value );

	if ( $attachment_id <= 0 ) {
		return 0;
	}

	if ( 'attachment' !== get_post_type( $attachment_id ) ) {
		return 0;
	}

	if ( ! wp_attachment_is_image( $attachment_id ) ) {
		return 0;
	}

	return $attachment_id;
}

/**
 * Bundled default brand asset URL.
 *
 * @param string $file Filename under assets/images/brand/.
 */
function ghahghah_get_bundled_brand_asset_url( string $file ): string {
	return GHAHGHAH_THEME_URI . '/assets/images/brand/' . ltrim( $file, '/' );
}

/**
 * Resolve logo URL for a viewport variant.
 *
 * @param string $variant desktop|mobile.
 */
function ghahghah_get_header_logo_url( string $variant = 'desktop' ): string {
	$variant       = 'mobile' === $variant ? 'mobile' : 'desktop';
	$mod_key       = 'mobile' === $variant ? 'ghahghah_header_logo_mobile' : 'ghahghah_header_logo_desktop';
	$attachment_id = absint( get_theme_mod( $mod_key, 0 ) );

	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	$file = 'mobile' === $variant ? 'ghahghah-logo-mobile.webp' : 'ghahghah-logo-desktop.webp';

	return ghahghah_get_bundled_brand_asset_url( $file );
}

/**
 * Resolve favicon / site icon URL.
 */
function ghahghah_get_header_favicon_url(): string {
	$attachment_id = absint( get_theme_mod( 'ghahghah_header_favicon', 0 ) );

	if ( $attachment_id > 0 ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	$site_icon = (int) get_option( 'site_icon', 0 );
	if ( $site_icon > 0 ) {
		$url = wp_get_attachment_image_url( $site_icon, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	return ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' );
}

/**
 * Prefer theme favicon when WordPress site icon is empty.
 *
 * @param string $url  Current icon URL.
 * @param int    $size Requested size.
 */
function ghahghah_filter_site_icon_url( $url, $size ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$attachment_id = absint( get_theme_mod( 'ghahghah_header_favicon', 0 ) );

	if ( $attachment_id > 0 ) {
		$custom = wp_get_attachment_image_url( $attachment_id, array( $size, $size ) );
		if ( is_string( $custom ) && '' !== $custom ) {
			return $custom;
		}
	}

	if ( is_string( $url ) && '' !== $url ) {
		return $url;
	}

	return ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' );
}
add_filter( 'get_site_icon_url', 'ghahghah_filter_site_icon_url', 10, 2 );

/**
 * Print CSS custom properties for logo sizing.
 */
function ghahghah_header_dynamic_css(): void {
	$desktop = ghahghah_get_header_logo_width_desktop();
	$mobile  = ghahghah_get_header_logo_width_mobile();

	$css = sprintf(
		':root{--ghahghah-logo-width-desktop:%dpx;--ghahghah-logo-width-mobile:%dpx;}',
		$desktop,
		$mobile
	);

	wp_add_inline_style( 'ghahghah-header', $css );
}
add_action( 'wp_enqueue_scripts', 'ghahghah_header_dynamic_css', 20 );
