<?php
/**
 * Footer settings, sanitizers, and front-end helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default theme mods for the site footer.
 *
 * @return array<string, mixed>
 */
function ghahghah_footer_setting_defaults(): array {
	return array(
		'ghahghah_footer_logo'                 => 0,
		'ghahghah_footer_logo_mobile'          => 0,
		'ghahghah_footer_intro'                => "قهقهه؛ طعم شادی\nبا محصولات قهقهه آشنا شوید",
		'ghahghah_footer_col_quick_title'      => __( 'دسترسی سریع', 'ghahghah' ),
		'ghahghah_footer_col_business_title'   => __( 'همکاری', 'ghahghah' ),
		'ghahghah_footer_col_contact_title'    => __( 'راه‌های ارتباط', 'ghahghah' ),
		'ghahghah_footer_collab_enabled'       => true,
		'ghahghah_footer_collab_title'         => __( 'همکاری با قهقهه', 'ghahghah' ),
		'ghahghah_footer_collab_text'          => __( 'برای خرید عمده یا دریافت نمایندگی با ما در ارتباط باشید.', 'ghahghah' ),
		'ghahghah_footer_collab_primary_label' => __( 'خرید عمده', 'ghahghah' ),
		'ghahghah_footer_collab_primary_page'  => 0,
		'ghahghah_footer_collab_secondary_label' => __( 'درخواست نمایندگی', 'ghahghah' ),
		'ghahghah_footer_collab_secondary_page'  => 0,
		'ghahghah_footer_phones'               => "087-35155151\n09188805055",
		'ghahghah_footer_email'                => '',
		'ghahghah_footer_address'              => "آدرس کارخانه: کردستان، سنندج، شهرک صنعتی شماره ۲ (دهگلان) · آدرس دفتر مرکزی: سنندج، خیابان کوسه هجیج، خیابان داراب، پلاک ۳، طبقه اول، واحد ۲",
		'ghahghah_footer_legal_text'           => __( 'تمام حقوق این وب‌سایت متعلق به قهقهه است', 'ghahghah' ),
		'ghahghah_footer_privacy_page_id'      => 0,
		'ghahghah_footer_back_to_top'          => true,
		'ghahghah_footer_socials'              => array(
			array(
				'label'    => 'تلگرام',
				'url'      => '',
				'icon_id'  => 0,
				'network'  => 'telegram',
			),
			array(
				'label'    => 'واتساپ',
				'url'      => '',
				'icon_id'  => 0,
				'network'  => 'whatsapp',
			),
		),
	);
}

/**
 * Sanitize multiline plain text.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_footer_multiline( $value ): string {
	$text = sanitize_textarea_field( (string) $value );
	return trim( $text );
}

/**
 * Sanitize short single-line text.
 *
 * @param mixed $value Raw value.
 * @param int   $max   Max length.
 */
function ghahghah_sanitize_footer_text( $value, int $max = 120 ): string {
	$text = sanitize_text_field( (string) $value );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $max );
	}
	return substr( $text, 0, $max );
}

/**
 * Sanitize published public page ID (0 allowed).
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_footer_page_id( $value ): int {
	$id = absint( $value );
	if ( $id <= 0 ) {
		return 0;
	}
	$post = get_post( $id );
	if ( ! $post || 'page' !== $post->post_type || ! is_post_publicly_viewable( $post ) ) {
		return 0;
	}
	return $id;
}

/**
 * Sanitize email (empty allowed).
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_footer_email( $value ): string {
	$email = sanitize_email( (string) $value );
	return is_email( $email ) ? $email : '';
}

/**
 * Get a footer theme mod with default fallback.
 *
 * @param string $key Setting key.
 * @return mixed
 */
function ghahghah_get_footer_mod( string $key ) {
	$defaults = ghahghah_footer_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Footer logo URL (desktop / shared).
 */
function ghahghah_get_footer_logo_url(): string {
	$id = absint( ghahghah_get_footer_mod( 'ghahghah_footer_logo' ) );
	if ( $id > 0 ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	// Fall back to header desktop logo or bundled asset.
	if ( function_exists( 'ghahghah_get_header_logo_url' ) ) {
		return ghahghah_get_header_logo_url( 'desktop' );
	}

	return ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-desktop.webp' );
}

/**
 * Footer logo URL for small screens.
 */
function ghahghah_get_footer_logo_mobile_url(): string {
	$id = absint( ghahghah_get_footer_mod( 'ghahghah_footer_logo_mobile' ) );
	if ( $id > 0 ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	$footer_desktop = absint( ghahghah_get_footer_mod( 'ghahghah_footer_logo' ) );
	if ( $footer_desktop > 0 ) {
		return ghahghah_get_footer_logo_url();
	}

	if ( function_exists( 'ghahghah_get_header_logo_url' ) ) {
		return ghahghah_get_header_logo_url( 'mobile' );
	}

	return ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-mobile.webp' );
}

/**
 * Intrinsic dimensions for a logo URL (bundled fallbacks known).
 *
 * @param string $url Logo URL.
 * @return array{0: int, 1: int}
 */
function ghahghah_get_footer_logo_dimensions( string $url ): array {
	if ( str_contains( $url, 'ghahghah-logo-mobile.webp' ) ) {
		return array( 288, 177 );
	}
	if ( str_contains( $url, 'ghahghah-logo-desktop.webp' ) ) {
		return array( 480, 295 );
	}

	$id = absint( ghahghah_get_footer_mod( 'ghahghah_footer_logo_mobile' ) );
	if ( $id <= 0 ) {
		$id = absint( ghahghah_get_footer_mod( 'ghahghah_footer_logo' ) );
	}
	if ( $id > 0 ) {
		$meta = wp_get_attachment_image_src( $id, 'full' );
		if ( is_array( $meta ) ) {
			return array( (int) $meta[1], (int) $meta[2] );
		}
	}

	return array( 480, 295 );
}

/**
 * Collaboration CTA button data.
 *
 * @param string $which primary|secondary.
 * @return array{label: string, url: string}|null
 */
function ghahghah_get_footer_collab_button( string $which ): ?array {
	$which = 'secondary' === $which ? 'secondary' : 'primary';
	$label = (string) ghahghah_get_footer_mod( 'ghahghah_footer_collab_' . $which . '_label' );
	$page  = absint( ghahghah_get_footer_mod( 'ghahghah_footer_collab_' . $which . '_page' ) );

	// Primary wholesale can fall back to header CTA / homepage form hash.
	if ( 'primary' === $which && $page <= 0 && function_exists( 'ghahghah_get_header_cta' ) ) {
		$header_cta = ghahghah_get_header_cta();
		if ( is_array( $header_cta ) && ! empty( $header_cta['url'] ) ) {
			if ( '' === trim( $label ) ) {
				$label = (string) $header_cta['label'];
			}
			return array(
				'label' => $label,
				'url'   => (string) $header_cta['url'],
			);
		}
	}

	$label = trim( $label );

	// Secondary agency falls back to homepage agency form.
	if ( 'secondary' === $which && $page <= 0 && '' !== $label && function_exists( 'ghahghah_get_agency_form_url' ) ) {
		return array(
			'label' => $label,
			'url'   => ghahghah_get_agency_form_url(),
		);
	}

	if ( '' === $label || $page <= 0 ) {
		return null;
	}

	$url = get_permalink( $page );
	if ( ! is_string( $url ) || '' === $url ) {
		return null;
	}

	return array(
		'label' => $label,
		'url'   => $url,
	);
}

/**
 * Parsed phone numbers from settings.
 *
 * @return array<int, string>
 */
function ghahghah_get_footer_phones(): array {
	$raw   = (string) ghahghah_get_footer_mod( 'ghahghah_footer_phones' );
	$lines = preg_split( '/\r\n|\r|\n/', $raw ) ?: array();
	$out   = array();

	foreach ( $lines as $line ) {
		$line = trim( sanitize_text_field( $line ) );
		if ( '' === $line ) {
			continue;
		}
		// Keep display text; require at least a few digits.
		$digits = preg_replace( '/\D+/', '', $line );
		if ( ! is_string( $digits ) || strlen( $digits ) < 7 ) {
			continue;
		}
		$out[] = $line;
	}

	return $out;
}

/**
 * tel: href from a display phone string.
 *
 * @param string $display Display phone.
 */
function ghahghah_footer_phone_href( string $display ): string {
	$digits = preg_replace( '/\D+/', '', $display );
	if ( ! is_string( $digits ) || '' === $digits ) {
		return '';
	}
	return 'tel:' . $digits;
}

/**
 * Footer address with postal-code lines stripped (multiline preserved for admin).
 */
function ghahghah_get_footer_address(): string {
	$raw   = (string) ghahghah_get_footer_mod( 'ghahghah_footer_address' );
	$lines = preg_split( '/\r\n|\r|\n/', $raw ) ?: array();
	$out   = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		// Drop postal-code lines (Latin or Persian digits / spacing variants).
		if ( preg_match( '/^\s*کد\s*پستی\s*[:：]/u', $line ) ) {
			continue;
		}
		$out[] = $line;
	}

	return implode( "\n", $out );
}

/**
 * Single-line address for the full-width footer strip.
 */
function ghahghah_get_footer_address_linear(): string {
	$address = ghahghah_get_footer_address();
	if ( '' === $address ) {
		return '';
	}

	$parts = preg_split( '/\r\n|\r|\n|·|•|\|/', $address ) ?: array();
	$clean = array();
	foreach ( $parts as $part ) {
		$part = trim( (string) $part );
		if ( '' !== $part ) {
			$clean[] = $part;
		}
	}

	return implode( ' · ', $clean );
}

/**
 * Allowed social network presets (bundled SVG icons).
 *
 * @return array<string, string>
 */
function ghahghah_footer_social_networks(): array {
	return array(
		'telegram' => __( 'تلگرام', 'ghahghah' ),
		'whatsapp' => __( 'واتساپ', 'ghahghah' ),
		'custom'   => __( 'سفارشی', 'ghahghah' ),
	);
}

/**
 * Default social rows.
 *
 * @return array<int, array{label: string, url: string, icon_id: int, network: string}>
 */
function ghahghah_footer_social_defaults(): array {
	$defaults = ghahghah_footer_setting_defaults();
	$socials  = $defaults['ghahghah_footer_socials'] ?? array();
	return is_array( $socials ) ? $socials : array();
}

/**
 * Sanitize one social network key.
 *
 * @param mixed $value Raw network.
 */
function ghahghah_sanitize_footer_social_network( $value ): string {
	$key = sanitize_key( (string) $value );
	$allowed = array_keys( ghahghah_footer_social_networks() );
	return in_array( $key, $allowed, true ) ? $key : 'custom';
}

/**
 * Sanitize social rows from theme mod / POST.
 *
 * @param mixed $value Raw value.
 * @return array<int, array{label: string, url: string, icon_id: int, network: string}>
 */
function ghahghah_sanitize_footer_socials( $value ): array {
	if ( ! is_array( $value ) ) {
		return ghahghah_footer_social_defaults();
	}

	$out = array();
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$label   = ghahghah_sanitize_footer_text( $row['label'] ?? '', 40 );
		$url     = esc_url_raw( trim( (string) ( $row['url'] ?? '' ) ) );
		$icon_id = function_exists( 'ghahghah_sanitize_attachment_id' )
			? ghahghah_sanitize_attachment_id( $row['icon_id'] ?? 0 )
			: absint( $row['icon_id'] ?? 0 );
		$network = ghahghah_sanitize_footer_social_network( $row['network'] ?? 'custom' );

		if ( '' === $label && '' === $url && $icon_id <= 0 && 'custom' === $network ) {
			continue;
		}

		if ( '' === $label ) {
			$networks = ghahghah_footer_social_networks();
			$label    = (string) ( $networks[ $network ] ?? __( 'شبکه اجتماعی', 'ghahghah' ) );
		}

		$out[] = array(
			'label'   => $label,
			'url'     => $url,
			'icon_id' => $icon_id,
			'network' => $network,
		);

		if ( count( $out ) >= 8 ) {
			break;
		}
	}

	return $out;
}

/**
 * Sanitize socials posted as parallel arrays from the admin form.
 *
 * @param array<string, mixed> $post Request payload (already unslashed).
 * @return array<int, array{label: string, url: string, icon_id: int, network: string}>
 */
function ghahghah_sanitize_footer_socials_from_post( array $post ): array {
	$labels   = isset( $post['ghahghah_footer_social_label'] ) && is_array( $post['ghahghah_footer_social_label'] ) ? $post['ghahghah_footer_social_label'] : array();
	$urls     = isset( $post['ghahghah_footer_social_url'] ) && is_array( $post['ghahghah_footer_social_url'] ) ? $post['ghahghah_footer_social_url'] : array();
	$icons    = isset( $post['ghahghah_footer_social_icon'] ) && is_array( $post['ghahghah_footer_social_icon'] ) ? $post['ghahghah_footer_social_icon'] : array();
	$networks = isset( $post['ghahghah_footer_social_network'] ) && is_array( $post['ghahghah_footer_social_network'] ) ? $post['ghahghah_footer_social_network'] : array();

	$count = max( count( $labels ), count( $urls ), count( $icons ), count( $networks ) );
	$rows  = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$rows[] = array(
			'label'   => $labels[ $i ] ?? '',
			'url'     => $urls[ $i ] ?? '',
			'icon_id' => $icons[ $i ] ?? 0,
			'network' => $networks[ $i ] ?? 'custom',
		);
	}

	$sanitized = ghahghah_sanitize_footer_socials( $rows );
	return array() !== $sanitized ? $sanitized : ghahghah_footer_social_defaults();
}

/**
 * Social items ready for the front-end (must have a URL).
 *
 * @return array<int, array{label: string, url: string, icon_id: int, network: string, icon_url: string}>
 */
function ghahghah_get_footer_socials(): array {
	$raw = get_theme_mod( 'ghahghah_footer_socials', null );
	if ( null === $raw ) {
		$rows = ghahghah_footer_social_defaults();
	} else {
		$rows = ghahghah_sanitize_footer_socials( $raw );
	}

	$out = array();
	foreach ( $rows as $row ) {
		$url = (string) ( $row['url'] ?? '' );
		if ( '' === $url ) {
			continue;
		}

		$icon_id  = absint( $row['icon_id'] ?? 0 );
		$icon_url = '';
		if ( $icon_id > 0 ) {
			$maybe = wp_get_attachment_image_url( $icon_id, 'thumbnail' );
			if ( is_string( $maybe ) && '' !== $maybe ) {
				$icon_url = $maybe;
			}
		}

		$out[] = array(
			'label'    => (string) ( $row['label'] ?? '' ),
			'url'      => $url,
			'icon_id'  => $icon_id,
			'network'  => (string) ( $row['network'] ?? 'custom' ),
			'icon_url' => $icon_url,
		);
	}

	return $out;
}

/**
 * Phone / email contact rows (no address).
 *
 * @return array<int, array{type: string, value: string, href?: string}>
 */
function ghahghah_get_footer_contact_rows(): array {
	$rows = array();

	foreach ( ghahghah_get_footer_phones() as $phone ) {
		$href = ghahghah_footer_phone_href( $phone );
		if ( '' === $href ) {
			continue;
		}
		$rows[] = array(
			'type'  => 'phone',
			'value' => $phone,
			'href'  => $href,
		);
	}

	$email = (string) ghahghah_get_footer_mod( 'ghahghah_footer_email' );
	if ( is_email( $email ) ) {
		$rows[] = array(
			'type'  => 'email',
			'value' => $email,
			'href'  => 'mailto:' . $email,
		);
	}

	return $rows;
}

/**
 * Privacy policy URL (theme override or WP setting).
 */
function ghahghah_get_footer_privacy_url(): string {
	$page_id = absint( ghahghah_get_footer_mod( 'ghahghah_footer_privacy_page_id' ) );
	if ( $page_id <= 0 ) {
		$page_id = absint( get_option( 'wp_page_for_privacy_policy' ) );
	}
	if ( $page_id <= 0 || ! is_post_publicly_viewable( $page_id ) ) {
		return '';
	}
	$url = get_permalink( $page_id );
	return is_string( $url ) ? $url : '';
}

/**
 * Echo a trusted footer icon SVG.
 *
 * @param string $key phone|mail|map-pin|arrow-up|telegram|whatsapp.
 */
function ghahghah_the_footer_icon( string $key ): void {
	$allowed = array( 'phone', 'mail', 'map-pin', 'arrow-up', 'telegram', 'whatsapp' );
	if ( ! in_array( $key, $allowed, true ) ) {
		return;
	}

	$path = GHAHGHAH_THEME_DIR . '/assets/icons/footer/' . $key . '.svg';
	if ( ! is_readable( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$svg = preg_replace(
		'/<svg\b/',
		'<svg class="ghahghah-footer__icon" aria-hidden="true" focusable="false"',
		$svg,
		1
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local SVG.
	echo $svg;
}

/**
 * Echo social icon (custom upload or bundled network SVG).
 *
 * @param array{label?: string, icon_url?: string, network?: string} $item Social item.
 */
function ghahghah_the_footer_social_icon( array $item ): void {
	$icon_url = (string) ( $item['icon_url'] ?? '' );
	if ( '' !== $icon_url ) {
		printf(
			'<img class="ghahghah-footer__social-img" src="%1$s" alt="" width="22" height="22" loading="lazy" decoding="async" />',
			esc_url( $icon_url )
		);
		return;
	}

	$network = (string) ( $item['network'] ?? 'custom' );
	if ( in_array( $network, array( 'telegram', 'whatsapp' ), true ) ) {
		ghahghah_the_footer_icon( $network );
		return;
	}

	// Generic link glyph fallback.
	echo '<svg class="ghahghah-footer__icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
}

/**
 * Render a footer menu location if it has items (no placeholder # links).
 *
 * @param string $location Menu location.
 * @param string $nav_id   Element id.
 * @param string $label    Accessible name.
 */
function ghahghah_the_footer_menu( string $location, string $nav_id, string $label ): void {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location'       => $location,
			'container'            => 'nav',
			'container_id'         => $nav_id,
			'container_class'      => 'ghahghah-footer__nav',
			'container_aria_label' => $label,
			'menu_class'           => 'ghahghah-footer__menu list-reset',
			'fallback_cb'          => false,
			'depth'                => 1,
		)
	);
}

/**
 * Whether a footer menu location has at least one item.
 *
 * @param string $location Location slug.
 */
function ghahghah_footer_menu_has_items( string $location ): bool {
	if ( ! has_nav_menu( $location ) ) {
		return false;
	}
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations[ $location ] ) ? absint( $locations[ $location ] ) : 0;
	if ( $menu_id <= 0 ) {
		return false;
	}
	$items = wp_get_nav_menu_items( $menu_id );
	return is_array( $items ) && array() !== $items;
}
