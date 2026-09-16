<?php
/**
 * Dedicated «تماس با ما» page settings and helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme mod defaults for the contact page.
 *
 * @return array<string, mixed>
 */
function ghahghah_contact_page_defaults(): array {
	return array(
		'ghahghah_contact_page_id'           => 0,
		'ghahghah_contact_page_title'        => __( 'راه‌های ارتباط با قهقهه', 'ghahghah' ),
		'ghahghah_contact_page_lead'         => __( 'برای همکاری، خرید عمده و دریافت اطلاعات با ما در ارتباط باشید.', 'ghahghah' ),
		'ghahghah_contact_form_title'        => __( 'ارسال پیام برای ما', 'ghahghah' ),
		'ghahghah_contact_form_text'         => __( 'نظر، پیشنهاد یا درخواست خود را از طریق فرم زیر برای ما ارسال کنید.', 'ghahghah' ),
		'ghahghah_contact_info_title'        => __( 'اطلاعات تماس', 'ghahghah' ),
		'ghahghah_contact_info_text'         => __( 'از طریق راه‌های زیر با ما در ارتباط باشید.', 'ghahghah' ),
		'ghahghah_contact_hours'             => __( 'شنبه تا پنجشنبه — ۹ تا ۱۷', 'ghahghah' ),
		'ghahghah_contact_map_url'           => 'https://maps.app.goo.gl/aw3yrmCFCz9uQgYu7',
		'ghahghah_contact_map_text'          => __( 'مشاهده در گوگل مپ', 'ghahghah' ),
		'ghahghah_contact_card_placeholder'  => __( 'اطلاعات رسمی از پیشخوان تکمیل می‌شود', 'ghahghah' ),
		'ghahghah_contact_phone_label'       => __( 'شماره تماس رسمی', 'ghahghah' ),
		'ghahghah_contact_email_label'       => __( 'ایمیل سازمانی', 'ghahghah' ),
		'ghahghah_contact_address_label'     => __( 'نشانی کارخانه و دفتر', 'ghahghah' ),
		'ghahghah_contact_hours_label'       => __( 'ساعات پاسخگویی', 'ghahghah' ),
	);
}

/**
 * Get a contact-page theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_contact_page_mod( string $key ) {
	$defaults = ghahghah_contact_page_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Published contact page ID (0 if unset/invalid).
 */
function ghahghah_get_contact_page_id_resolved(): int {
	$id = absint( ghahghah_get_contact_page_mod( 'ghahghah_contact_page_id' ) );
	if ( $id <= 0 ) {
		$id = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
	}
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
 * Whether the current view uses the contact page template.
 */
function ghahghah_is_contact_page(): bool {
	return is_page_template( 'page-templates/contact.php' );
}

/**
 * Corn hero image for contact page.
 *
 * @return array{src: string, width: int, height: int}
 */
function ghahghah_get_contact_corn_image(): array {
	return array(
		'src'    => GHAHGHAH_THEME_URI . '/assets/images/contact/corn-isolated-transparent-optimized.webp',
		'width'  => 1448,
		'height' => 1086,
	);
}

/**
 * Contact info cards (phone / email / address / hours).
 *
 * @return array<int, array{key: string, label: string, value: string, href: string, icon: string, tone: string}>
 */
function ghahghah_get_contact_info_cards(): array {
	$placeholder = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_card_placeholder' ) );
	if ( '' === $placeholder ) {
		$placeholder = __( 'اطلاعات رسمی از پیشخوان تکمیل می‌شود', 'ghahghah' );
	}

	$phones = function_exists( 'ghahghah_get_footer_phones' ) ? ghahghah_get_footer_phones() : array();
	$phone  = isset( $phones[0] ) ? (string) $phones[0] : '';
	$phone_href = ( '' !== $phone && function_exists( 'ghahghah_footer_phone_href' ) )
		? ghahghah_footer_phone_href( $phone )
		: '';

	$address = '';
	if ( function_exists( 'ghahghah_get_footer_address' ) ) {
		$address = trim( (string) ghahghah_get_footer_address() );
	} elseif ( function_exists( 'ghahghah_get_footer_mod' ) ) {
		$address = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_address' ) );
	}

	$hours = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_hours' ) );

	return array(
		array(
			'key'   => 'phone',
			'label' => (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_phone_label' ),
			'value' => '' !== $phone ? $phone : $placeholder,
			'href'  => $phone_href,
			'icon'  => 'phone',
			'tone'  => 'yellow',
		),
		array(
			'key'   => 'hours',
			'label' => (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_hours_label' ),
			'value' => '' !== $hours ? $hours : $placeholder,
			'href'  => '',
			'icon'  => 'clock',
			'tone'  => 'blue',
		),
		array(
			'key'   => 'address',
			'label' => (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_address_label' ),
			'value' => '' !== $address ? $address : $placeholder,
			'href'  => '',
			'icon'  => 'map-pin',
			'tone'  => 'red',
		),
	);
}

/**
 * Follow HTTP redirects for a URL (used for maps.app.goo.gl short links).
 */
function ghahghah_follow_redirect_url( string $url, int $max = 6 ): string {
	$current = esc_url_raw( $url );
	if ( '' === $current ) {
		return '';
	}

	for ( $i = 0; $i < $max; $i++ ) {
		$response = wp_remote_head(
			$current,
			array(
				'timeout'     => 8,
				'redirection' => 0,
				'headers'     => array(
					'User-Agent' => 'Mozilla/5.0 (compatible; GhahghahContactMap/1.0)',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$response = wp_remote_get(
				$current,
				array(
					'timeout'     => 8,
					'redirection' => 0,
					'headers'     => array(
						'User-Agent' => 'Mozilla/5.0 (compatible; GhahghahContactMap/1.0)',
					),
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			break;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$loc  = wp_remote_retrieve_header( $response, 'location' );
		if ( $code >= 300 && $code < 400 && is_string( $loc ) && '' !== $loc ) {
			if ( str_starts_with( $loc, '/' ) ) {
				$parts = wp_parse_url( $current );
				$host  = is_array( $parts ) ? (string) ( $parts['host'] ?? '' ) : '';
				$scheme = is_array( $parts ) ? (string) ( $parts['scheme'] ?? 'https' ) : 'https';
				if ( '' === $host ) {
					break;
				}
				$loc = $scheme . '://' . $host . $loc;
			}
			$current = esc_url_raw( $loc );
			if ( '' === $current ) {
				break;
			}
			continue;
		}
		break;
	}

	return $current;
}

/**
 * Public share / open URL for the contact map (admin setting or default).
 */
function ghahghah_get_contact_map_share_url(): string {
	$url = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_map_url' ) );
	if ( '' === $url ) {
		$defaults = ghahghah_contact_page_defaults();
		$url      = (string) ( $defaults['ghahghah_contact_map_url'] ?? '' );
	}
	$url = esc_url_raw( $url );
	return is_string( $url ) ? $url : '';
}

/**
 * Build a Google Maps embed src from a share/embed URL.
 *
 * Accepts maps.app.goo.gl, google.com/maps place links, or /maps/embed URLs.
 */
function ghahghah_get_contact_map_embed_url(): string {
	$source = ghahghah_get_contact_map_share_url();
	if ( '' === $source ) {
		return '';
	}

	// Allow pasting full iframe markup.
	if ( preg_match( '/src=["\']([^"\']+)["\']/i', $source, $iframe_src ) ) {
		$source = esc_url_raw( (string) $iframe_src[1] );
		if ( '' === $source ) {
			return '';
		}
	}

	if ( str_contains( $source, '/maps/embed' ) || str_contains( $source, 'output=embed' ) ) {
		return $source;
	}

	$cache_key = 'ghahghah_map_embed_' . md5( $source );
	$cached    = get_transient( $cache_key );
	if ( is_string( $cached ) && '' !== $cached ) {
		return $cached;
	}

	$resolved = $source;
	if ( preg_match( '#(maps\.app\.goo\.gl|goo\.gl/maps)#i', $source ) ) {
		$resolved = ghahghah_follow_redirect_url( $source );
	}

	$embed = '';
	if ( preg_match( '/@(-?\d+\.?\d*),(-?\d+\.?\d*)(?:,(\d+\.?\d*)z)?/', $resolved, $m ) ) {
		$zoom  = isset( $m[3] ) && '' !== $m[3] ? max( 1, (int) round( (float) $m[3] ) ) : 14;
		$embed = add_query_arg(
			array(
				'q'      => $m[1] . ',' . $m[2],
				'z'      => $zoom,
				'output' => 'embed',
				'hl'     => 'fa',
			),
			'https://maps.google.com/maps'
		);
	} elseif ( preg_match( '/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $resolved, $m ) ) {
		$embed = add_query_arg(
			array(
				'q'      => $m[1] . ',' . $m[2],
				'z'      => 14,
				'output' => 'embed',
				'hl'     => 'fa',
			),
			'https://maps.google.com/maps'
		);
	} else {
		$embed = add_query_arg(
			array(
				'q'      => $resolved,
				'output' => 'embed',
				'hl'     => 'fa',
			),
			'https://maps.google.com/maps'
		);
	}

	$embed = esc_url_raw( $embed );
	if ( '' !== $embed ) {
		set_transient( $cache_key, $embed, WEEK_IN_SECONDS );
	}

	return $embed;
}

/**
 * Echo a contact-page icon SVG.
 *
 * @param string               $name Icon key.
 * @param array<string, mixed> $args Optional modifiers/class.
 */
function ghahghah_the_contact_icon( string $name, array $args = array() ): void {
	$key = sanitize_key( $name );
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/contact/' . $key . '.svg';
	if ( ! is_readable( $path ) && function_exists( 'ghahghah_the_form_icon' ) ) {
		ghahghah_the_form_icon( $key, $args );
		return;
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$classes = array( 'gg-contact-icon', 'gg-form-icon' );
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$classes[] = $args['class'];
	}
	if ( ! empty( $args['modifiers'] ) && is_array( $args['modifiers'] ) ) {
		foreach ( $args['modifiers'] as $mod ) {
			$mod = sanitize_html_class( (string) $mod );
			if ( '' !== $mod ) {
				$classes[] = 'gg-contact-icon--' . $mod;
				$classes[] = 'gg-form-icon--' . $mod;
			}
		}
	}

	$class_attr = implode( ' ', array_unique( array_filter( $classes ) ) );
	$svg        = preg_replace( '/\s(?:role|aria-label|aria-hidden|focusable|class)="[^"]*"/i', '', $svg ) ?? $svg;
	$svg        = preg_replace( '/<title\b[^>]*>.*?<\/title>/is', '', $svg ) ?? $svg;
	$svg        = preg_replace(
		'/<svg\b/i',
		'<svg class="' . esc_attr( $class_attr ) . '" aria-hidden="true" focusable="false"',
		$svg,
		1
	) ?? $svg;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local SVG.
	echo $svg;
}
