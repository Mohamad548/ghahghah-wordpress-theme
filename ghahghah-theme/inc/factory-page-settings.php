<?php
/**
 * Dedicated «معرفی کارخانه» page settings and helpers.
 *
 * Separate from the homepage factory intro strip (inc/factory-settings.php).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme mod defaults for the factory intro page chrome.
 *
 * @return array<string, mixed>
 */
function ghahghah_factory_page_defaults(): array {
	return array(
		'ghahghah_factory_page_id'              => 0,
		'ghahghah_factory_page_title'           => __( 'از دانه ذرت تا لحظه‌های خوشمزه', 'ghahghah' ),
		'ghahghah_factory_page_lead'            => __( 'نگاهی به مسیر تولید و کنترل کیفیت محصولات قهقهه', 'ghahghah' ),
		'ghahghah_factory_page_company'         => __( 'شرکت بین‌المللی پیام صنعت پارسا', 'ghahghah' ),
		'ghahghah_factory_page_intro'           => __( "در کارخانه قهقهه، با تکیه بر دانش فنی، تجهیزات مدرن و نیروی انسانی متخصص، مراحل تولید محصولات با دقت و رعایت استانداردهای بهداشتی انجام می‌شود.\n\nما همواره در تلاش هستیم تا با ارائه محصولات باکیفیت و خوش‌طعم، لحظه‌هایی شاد و به‌یادماندنی را برای مصرف‌کنندگان خود رقم بزنیم.", 'ghahghah' ),
		'ghahghah_factory_page_cta_label'       => __( 'آشنایی با محصولات', 'ghahghah' ),
		'ghahghah_factory_page_process_title'   => __( 'مراحل تولید محصول', 'ghahghah' ),
		'ghahghah_factory_page_process_text'    => __( 'از انتخاب مواد اولیه تا بسته‌بندی، با دقت و مراقبت در کنار شما.', 'ghahghah' ),
		'ghahghah_factory_page_quality_title'   => __( 'کیفیت در هر مرحله', 'ghahghah' ),
		'ghahghah_factory_page_quality_text'    => __( 'متعهد به ارائه محصولاتی سالم، باکیفیت و مطمئن', 'ghahghah' ),
		'ghahghah_factory_page_certs_title'     => __( 'مدارک و گواهینامه‌ها', 'ghahghah' ),
		'ghahghah_factory_page_certs_text'      => __( 'پس از دریافت مدارک رسمی تکمیل می‌شود.', 'ghahghah' ),
		'ghahghah_factory_page_hero_image_id'   => 0,
		'ghahghah_factory_page_show_temp_badge' => true,
	);
}

/**
 * Get a factory-page theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_factory_page_mod( string $key ) {
	$defaults = ghahghah_factory_page_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Published factory page ID (0 if unset/invalid).
 */
function ghahghah_get_factory_page_id(): int {
	$id = absint( ghahghah_get_factory_page_mod( 'ghahghah_factory_page_id' ) );
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
 * Public URL for the factory intro page.
 */
function ghahghah_get_factory_page_url(): string {
	$id = ghahghah_get_factory_page_id();
	if ( $id <= 0 ) {
		return '';
	}
	$url = get_permalink( $id );
	return is_string( $url ) && '' !== $url ? $url : '';
}

/**
 * Whether the current view uses the factory intro page template.
 */
function ghahghah_is_factory_page(): bool {
	return is_page_template( 'page-templates/factory-intro.php' );
}

/**
 * Allowed factory-page icon basenames.
 *
 * @return array<int, string>
 */
function ghahghah_factory_page_icon_keys(): array {
	return array(
		'arrow-chevron',
		'certificate-document',
		'decor-corn',
		'decor-spark',
		'quality-packaging-check',
		'quality-process-control',
		'quality-tracking',
		'stage-cooking',
		'stage-extrusion',
		'stage-mixing',
		'stage-packaging',
		'stage-quality',
		'stage-raw-materials-corn',
	);
}

/**
 * Absolute path to a factory-page SVG.
 */
function ghahghah_get_factory_page_icon_path( string $name ): string {
	$key = strtolower( $name );
	$key = preg_replace( '/[^a-z0-9\-]/', '', $key ) ?? '';
	if ( ! in_array( $key, ghahghah_factory_page_icon_keys(), true ) ) {
		return '';
	}
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/factory/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo an inline factory-page SVG icon.
 *
 * @param string               $name Icon key.
 * @param array<string, mixed> $args Optional class / modifiers.
 */
function ghahghah_the_factory_page_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_factory_page_icon_path( $name );
	if ( '' === $path ) {
		return;
	}
	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$classes = array( 'gg-factory-icon' );
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$classes[] = $args['class'];
	}
	if ( ! empty( $args['modifiers'] ) && is_array( $args['modifiers'] ) ) {
		foreach ( $args['modifiers'] as $mod ) {
			$mod = sanitize_html_class( (string) $mod );
			if ( '' !== $mod ) {
				$classes[] = 'gg-factory-icon--' . $mod;
			}
		}
	}
	$class_attr = implode( ' ', array_unique( array_filter( $classes ) ) );

	$svg = preg_replace( '/\s(?:role|aria-label|aria-hidden|focusable|class)="[^"]*"/i', '', $svg ) ?? $svg;
	$svg = preg_replace( '/<title\b[^>]*>.*?<\/title>/is', '', $svg ) ?? $svg;
	$svg = preg_replace(
		'/<svg\b/i',
		'<svg class="' . esc_attr( $class_attr ) . '" aria-hidden="true" focusable="false"',
		$svg,
		1
	) ?? $svg;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local SVG.
	echo $svg;
}

/**
 * Format hero H1 with brand accent on «خوشمزه».
 */
function ghahghah_format_factory_page_title( string $title ): string {
	$escaped = esc_html( $title );
	$out     = preg_replace(
		'/(خوشمزه)/u',
		'<span class="ghahghah-fp__title-accent">$1</span>',
		$escaped,
		1
	);
	return is_string( $out ) ? $out : $escaped;
}

/**
 * Production process stages (fixed copy for this delivery).
 *
 * @return array<int, array{title: string, icon: string, tone: string}>
 */
function ghahghah_factory_page_process_stages(): array {
	return array(
		array(
			'title' => __( 'انتخاب مواد اولیه', 'ghahghah' ),
			'icon'  => 'stage-raw-materials-corn',
			'tone'  => 'yellow',
		),
		array(
			'title' => __( 'آماده‌سازی', 'ghahghah' ),
			'icon'  => 'stage-mixing',
			'tone'  => 'green',
		),
		array(
			'title' => __( 'شکل‌دهی', 'ghahghah' ),
			'icon'  => 'stage-extrusion',
			'tone'  => 'blue',
		),
		array(
			'title' => __( 'پخت و طعم‌دهی', 'ghahghah' ),
			'icon'  => 'stage-cooking',
			'tone'  => 'red',
		),
		array(
			'title' => __( 'کنترل کیفیت', 'ghahghah' ),
			'icon'  => 'stage-quality',
			'tone'  => 'green',
		),
		array(
			'title' => __( 'بسته‌بندی', 'ghahghah' ),
			'icon'  => 'stage-packaging',
			'tone'  => 'orange',
		),
	);
}

/**
 * Quality feature cards.
 *
 * @return array<int, array{title: string, text: string, icon: string, tone: string}>
 */
function ghahghah_factory_page_quality_items(): array {
	return array(
		array(
			'title' => __( 'کنترل فرآیند تولید', 'ghahghah' ),
			'text'  => __( 'نظارت مستمر در خط تولید برای حفظ کیفیت', 'ghahghah' ),
			'icon'  => 'quality-process-control',
			'tone'  => 'red',
		),
		array(
			'title' => __( 'بررسی بسته‌بندی', 'ghahghah' ),
			'text'  => __( 'کنترل سلامت و کیفیت بسته‌بندی محصولات', 'ghahghah' ),
			'icon'  => 'quality-packaging-check',
			'tone'  => 'yellow',
		),
		array(
			'title' => __( 'ثبت و پیگیری کنترل‌ها', 'ghahghah' ),
			'text'  => __( 'مستندسازی و رهگیری در تمام مراحل', 'ghahghah' ),
			'icon'  => 'quality-tracking',
			'tone'  => 'green',
		),
	);
}

/**
 * Hero factory image (Media Library or bundled WebP).
 *
 * @return array{src: string, srcset: string, width: int, height: int, alt: string, is_temp: bool}
 */
function ghahghah_get_factory_page_hero_image(): array {
	$id = absint( ghahghah_get_factory_page_mod( 'ghahghah_factory_page_hero_image_id' ) );
	if ( $id > 0 && wp_attachment_is_image( $id ) ) {
		$url = wp_get_attachment_image_url( $id, 'large' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = (string) wp_get_attachment_image_url( $id, 'full' );
		}
		$meta   = wp_get_attachment_metadata( $id );
		$width  = isset( $meta['width'] ) ? absint( $meta['width'] ) : 1600;
		$height = isset( $meta['height'] ) ? absint( $meta['height'] ) : 900;
		$alt    = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		$srcset = wp_get_attachment_image_srcset( $id, 'large' );
		return array(
			'src'     => $url,
			'srcset'  => is_string( $srcset ) ? $srcset : '',
			'width'   => $width > 0 ? $width : 1600,
			'height'  => $height > 0 ? $height : 900,
			'alt'     => '' !== $alt ? $alt : __( 'نمای داخلی کارخانه قهقهه', 'ghahghah' ),
			'is_temp' => false,
		);
	}

	$webp = GHAHGHAH_THEME_DIR . '/assets/images/factory/factory-hero.webp';
	$png  = GHAHGHAH_THEME_DIR . '/assets/images/factory/factory-hero.png';
	if ( is_readable( $webp ) ) {
		$src = GHAHGHAH_THEME_URI . '/assets/images/factory/factory-hero.webp';
	} else {
		$src = GHAHGHAH_THEME_URI . '/assets/images/factory/factory-hero.png';
	}

	return array(
		'src'     => $src,
		'srcset'  => '',
		'width'   => 1600,
		'height'  => 900,
		'alt'     => __( 'نمای داخلی کارخانه قهقهه', 'ghahghah' ),
		'is_temp' => (bool) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_show_temp_badge' ),
	);
}

/**
 * Decorative product pack URL (prefer catalog pizza pack).
 *
 * @return array{src: string, width: int, height: int, alt: string}
 */
function ghahghah_get_factory_page_product_pack(): array {
	$catalog = GHAHGHAH_THEME_DIR . '/assets/images/products/catalog/pizza-corn-pellet-full.webp';
	if ( is_readable( $catalog ) ) {
		return array(
			'src'    => GHAHGHAH_THEME_URI . '/assets/images/products/catalog/pizza-corn-pellet-full.webp',
			'width'  => 400,
			'height' => 520,
			'alt'    => '',
		);
	}

	$webp = GHAHGHAH_THEME_DIR . '/assets/images/factory/product-pack-fallback.webp';
	if ( is_readable( $webp ) ) {
		return array(
			'src'    => GHAHGHAH_THEME_URI . '/assets/images/factory/product-pack-fallback.webp',
			'width'  => 400,
			'height' => 520,
			'alt'    => '',
		);
	}

	return array(
		'src'    => GHAHGHAH_THEME_URI . '/assets/images/factory/product-pack-fallback.png',
		'width'  => 400,
		'height' => 520,
		'alt'    => '',
	);
}

/**
 * Hide featured image output on the factory intro page.
 *
 * @param string $html Thumbnail HTML.
 */
function ghahghah_factory_page_suppress_thumbnail( string $html ): string {
	if ( ghahghah_is_factory_page() ) {
		return '';
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'ghahghah_factory_page_suppress_thumbnail' );

/**
 * Body class for factory page scoping.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function ghahghah_factory_page_body_class( array $classes ): array {
	if ( ghahghah_is_factory_page() ) {
		$classes[] = 'ghahghah-factory-page-body';
	}
	return $classes;
}
add_filter( 'body_class', 'ghahghah_factory_page_body_class' );
