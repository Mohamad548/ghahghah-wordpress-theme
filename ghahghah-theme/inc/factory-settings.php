<?php
/**
 * Homepage factory intro settings and helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default theme mods for the factory intro section.
 *
 * @return array<string, mixed>
 */
function ghahghah_factory_setting_defaults(): array {
	return array(
		'ghahghah_factory_enabled'        => true,
		'ghahghah_factory_eyebrow'        => __( 'آشنایی با قهقهه', 'ghahghah' ),
		'ghahghah_factory_title'          => __( 'از نزدیک با کارخانه قهقهه آشنا شوید', 'ghahghah' ),
		'ghahghah_factory_text'           => __( 'در صفحه کارخانه، درباره مجموعه، مراحل تولید و بسته‌بندی محصولات قهقهه بیشتر بخوانید.', 'ghahghah' ),
		'ghahghah_factory_image'          => 0,
		'ghahghah_factory_image_alt'      => __( 'نمای داخلی کارخانه قهقهه', 'ghahghah' ),
		'ghahghah_factory_topics_heading' => __( 'تولید، بسته‌بندی و کنترل کیفیت', 'ghahghah' ),
		'ghahghah_factory_topic_1'        => __( 'مواد اولیه', 'ghahghah' ),
		'ghahghah_factory_topic_2'        => __( 'تولید و بسته‌بندی', 'ghahghah' ),
		'ghahghah_factory_topic_3'        => __( 'کنترل کیفیت', 'ghahghah' ),
		'ghahghah_factory_button_label'   => __( 'بیشتر درباره کارخانه', 'ghahghah' ),
		'ghahghah_factory_button_page'    => 0,
	);
}

/**
 * Get a factory theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_factory_mod( string $key ) {
	$defaults = ghahghah_factory_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Highlight brand word in factory title.
 *
 * @param string $title Raw title.
 */
function ghahghah_format_factory_title( string $title ): string {
	$escaped = esc_html( $title );
	return (string) preg_replace(
		'/قهقهه/u',
		'<span class="ghahghah-factory__accent">$0</span>',
		$escaped
	);
}

/**
 * Resolved factory image data from Media Library.
 *
 * @return array{id: int, url: string, width: int, height: int, alt: string, srcset: string, sizes: string}|null
 */
function ghahghah_get_factory_image(): ?array {
	$id = absint( ghahghah_get_factory_mod( 'ghahghah_factory_image' ) );
	if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
		return null;
	}

	$url = wp_get_attachment_image_url( $id, 'large' );
	if ( ! is_string( $url ) || '' === $url ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
	}
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	$meta   = wp_get_attachment_metadata( $id );
	$width  = isset( $meta['width'] ) ? absint( $meta['width'] ) : 960;
	$height = isset( $meta['height'] ) ? absint( $meta['height'] ) : 720;

	$alt = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_image_alt' ) );
	if ( '' === $alt ) {
		$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	}
	if ( '' === $alt ) {
		$alt = (string) ghahghah_factory_setting_defaults()['ghahghah_factory_image_alt'];
	}

	$srcset = wp_get_attachment_image_srcset( $id, 'large' );
	if ( ! is_string( $srcset ) ) {
		$srcset = '';
	}

	return array(
		'id'     => $id,
		'url'    => $url,
		'width'  => max( 1, $width ),
		'height' => max( 1, $height ),
		'alt'    => $alt,
		'srcset' => $srcset,
		'sizes'  => '(max-width: 47.99rem) 100vw, 36vw',
	);
}

/**
 * Factory CTA when label is set (page preferred; home as fallback).
 *
 * @return array{label: string, url: string}|null
 */
function ghahghah_get_factory_button(): ?array {
	$label = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_button_label' ) );
	if ( '' === $label ) {
		return null;
	}

	$url     = '';
	$page_id = absint( ghahghah_get_factory_mod( 'ghahghah_factory_button_page' ) );
	if ( $page_id > 0 ) {
		$page = get_post( $page_id );
		if ( $page && 'page' === $page->post_type && is_post_publicly_viewable( $page ) ) {
			$permalink = get_permalink( $page );
			if ( is_string( $permalink ) && '' !== $permalink ) {
				$url = $permalink;
			}
		}
	}

	if ( '' === $url && function_exists( 'ghahghah_get_factory_page_url' ) ) {
		$url = ghahghah_get_factory_page_url();
	}

	if ( '' === $url ) {
		$url = home_url( '/' );
	}

	return array(
		'label' => $label,
		'url'   => $url,
	);
}

/**
 * Three topic labels (skip empties).
 *
 * @return array<int, array{key: string, label: string}>
 */
function ghahghah_get_factory_topics(): array {
	$keys = array(
		'raw'       => 'ghahghah_factory_topic_1',
		'pack'      => 'ghahghah_factory_topic_2',
		'quality'   => 'ghahghah_factory_topic_3',
	);
	$out  = array();
	foreach ( $keys as $icon => $mod ) {
		$label = trim( (string) ghahghah_get_factory_mod( $mod ) );
		if ( '' === $label ) {
			continue;
		}
		$out[] = array(
			'key'   => $icon,
			'label' => $label,
		);
	}
	return $out;
}

/**
 * Whether the factory section should render.
 */
function ghahghah_should_render_factory(): bool {
	if ( ! (bool) ghahghah_get_factory_mod( 'ghahghah_factory_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}

	$title  = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_title' ) );
	$text   = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_text' ) );
	$image  = ghahghah_get_factory_image();
	$topics = ghahghah_get_factory_topics();
	$button = ghahghah_get_factory_button();

	return '' !== $title || '' !== $text || null !== $image || count( $topics ) > 0 || null !== $button;
}

/**
 * Absolute path to a trusted factory topic SVG.
 *
 * @param string $key raw|pack|quality.
 */
function ghahghah_get_factory_topic_icon_path( string $key ): string {
	$map = array(
		'raw'     => 'raw-materials.svg',
		'pack'    => 'production-packaging.svg',
		'quality' => 'quality-control.svg',
	);
	$file = $map[ $key ] ?? $map['quality'];
	return GHAHGHAH_THEME_DIR . '/assets/icons/' . $file;
}

/**
 * Inline SVG for a factory topic icon (decorative; label sits beside it).
 *
 * @param string $key raw|pack|quality.
 */
function ghahghah_the_factory_topic_icon( string $key ): void {
	$path = ghahghah_get_factory_topic_icon_path( $key );
	if ( ! is_readable( $path ) ) {
		$path = ghahghah_get_factory_topic_icon_path( 'quality' );
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$svg = preg_replace( '/\s(?:role|aria-label|focusable)="[^"]*"/i', '', $svg ) ?? $svg;
	$svg = preg_replace( '/<title\b[^>]*>.*?<\/title>/is', '', $svg ) ?? $svg;
	$svg = preg_replace(
		'/<svg\b/i',
		'<svg class="ghahghah-factory__topic-svg" aria-hidden="true" focusable="false"',
		$svg,
		1
	) ?? $svg;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local theme SVG files only.
	echo $svg;
}
