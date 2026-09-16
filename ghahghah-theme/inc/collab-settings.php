<?php
/**
 * Homepage wholesale / agency collab CTA settings.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default theme mods for the collab CTA section.
 *
 * @return array<string, mixed>
 */
function ghahghah_collab_setting_defaults(): array {
	return array(
		'ghahghah_collab_enabled'           => true,
		'ghahghah_collab_eyebrow'           => __( 'ارتباط با قهقهه', 'ghahghah' ),
		'ghahghah_collab_title'             => __( 'خرید عمده و درخواست نمایندگی', 'ghahghah' ),
		'ghahghah_collab_text'              => __( 'مسیر موردنظر خود را انتخاب کنید.', 'ghahghah' ),
		'ghahghah_collab_wholesale_title'   => __( 'خرید عمده محصولات', 'ghahghah' ),
		'ghahghah_collab_wholesale_text'    => __( 'برای استعلام شرایط خرید عمده، درخواست خود را ثبت کنید.', 'ghahghah' ),
		'ghahghah_collab_wholesale_button'  => __( 'درخواست خرید عمده', 'ghahghah' ),
		'ghahghah_collab_agency_title'      => __( 'درخواست نمایندگی', 'ghahghah' ),
		'ghahghah_collab_agency_text'       => __( 'برای بررسی شرایط نمایندگی، اطلاعات خود را ارسال کنید.', 'ghahghah' ),
		'ghahghah_collab_agency_button'     => __( 'ثبت درخواست نمایندگی', 'ghahghah' ),
		'ghahghah_collab_preview_forms'     => false,
	);
}

/**
 * Get a collab theme mod with default fallback.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function ghahghah_get_collab_mod( string $key ) {
	$defaults = ghahghah_collab_setting_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Allowed collab form keys.
 *
 * @return array<int, string>
 */
function ghahghah_collab_form_keys(): array {
	return array( 'wholesale', 'agency' );
}

/**
 * Whether a collab inquiry form is available (Core / filter / shortcode).
 *
 * @param string $type wholesale|agency.
 */
function ghahghah_collab_form_is_available( string $type ): bool {
	if ( ! in_array( $type, ghahghah_collab_form_keys(), true ) ) {
		return false;
	}

	/**
	 * Force availability for a collab form type.
	 *
	 * @param bool   $available Whether the form can be revealed.
	 * @param string $type      wholesale|agency.
	 */
	$forced = apply_filters( 'ghahghah_collab_form_available', null, $type );
	if ( is_bool( $forced ) ) {
		return $forced;
	}

	$html = ghahghah_get_collab_form_html( $type );
	return '' !== trim( $html );
}

/**
 * Markup for an inquiry form embedded under the CTA cards.
 * Theme never owns submit/SMS; Core (or a filter) supplies the form HTML.
 *
 * @param string $type wholesale|agency.
 */
function ghahghah_get_collab_form_html( string $type ): string {
	if ( ! in_array( $type, ghahghah_collab_form_keys(), true ) ) {
		return '';
	}

	/**
	 * Provide inquiry form HTML for a collab path (no theme-owned submit).
	 *
	 * @param string $html Form markup.
	 * @param string $type wholesale|agency.
	 */
	$html = (string) apply_filters( 'ghahghah_collab_form_html', '', $type );
	if ( '' !== trim( $html ) ) {
		return $html;
	}

	$callback = 'wholesale' === $type ? 'ghahghah_core_render_wholesale_form' : 'ghahghah_core_render_agency_form';
	if ( function_exists( $callback ) ) {
		ob_start();
		call_user_func( $callback );
		$rendered = (string) ob_get_clean();
		if ( '' !== trim( $rendered ) ) {
			return $rendered;
		}
	}

	$shortcode = 'wholesale' === $type ? 'ghahghah_wholesale_form' : 'ghahghah_agency_form';
	if ( shortcode_exists( $shortcode ) ) {
		$rendered = (string) do_shortcode( '[' . $shortcode . ']' );
		if ( '' !== trim( wp_strip_all_tags( $rendered ) ) ) {
			return $rendered;
		}
	}

	if ( (bool) ghahghah_get_collab_mod( 'ghahghah_collab_preview_forms' ) ) {
		return ghahghah_get_collab_preview_form_html( $type );
	}

	return '';
}

/**
 * Non-submitting preview shell so layout can be reviewed before Core forms ship.
 *
 * @param string $type wholesale|agency.
 */
function ghahghah_get_collab_preview_form_html( string $type ): string {
	$message = 'wholesale' === $type
		? __( 'پیش‌نمایش فرم خرید عمده. ارسال درخواست و پیامک هنوز فعال نیست.', 'ghahghah' )
		: __( 'پیش‌نمایش فرم نمایندگی. ارسال درخواست و پیامک هنوز فعال نیست.', 'ghahghah' );

	return '<p class="ghahghah-collab__preview-note" role="status">' . esc_html( $message ) . '</p>';
}

/**
 * Human status for admin reports.
 *
 * @param string $type wholesale|agency.
 * @return array{available: bool, source: string, label: string}
 */
function ghahghah_get_collab_form_status( string $type ): array {
	$labels = array(
		'wholesale' => __( 'فرم خرید عمده', 'ghahghah' ),
		'agency'    => __( 'فرم نمایندگی', 'ghahghah' ),
	);
	$label  = $labels[ $type ] ?? $type;

	if ( ! in_array( $type, ghahghah_collab_form_keys(), true ) ) {
		return array(
			'available' => false,
			'source'    => 'invalid',
			'label'     => $label,
		);
	}

	$forced = apply_filters( 'ghahghah_collab_form_available', null, $type );
	if ( false === $forced ) {
		return array(
			'available' => false,
			'source'    => 'filter',
			'label'     => $label,
		);
	}

	$html = (string) apply_filters( 'ghahghah_collab_form_html', '', $type );
	if ( '' !== trim( $html ) ) {
		return array(
			'available' => true,
			'source'    => 'filter',
			'label'     => $label,
		);
	}

	$callback = 'wholesale' === $type ? 'ghahghah_core_render_wholesale_form' : 'ghahghah_core_render_agency_form';
	if ( function_exists( $callback ) ) {
		return array(
			'available' => true,
			'source'    => 'core-function',
			'label'     => $label,
		);
	}

	$shortcode = 'wholesale' === $type ? 'ghahghah_wholesale_form' : 'ghahghah_agency_form';
	if ( shortcode_exists( $shortcode ) ) {
		return array(
			'available' => true,
			'source'    => 'shortcode',
			'label'     => $label,
		);
	}

	if ( (bool) ghahghah_get_collab_mod( 'ghahghah_collab_preview_forms' ) ) {
		return array(
			'available' => true,
			'source'    => 'preview',
			'label'     => $label,
		);
	}

	return array(
		'available' => false,
		'source'    => 'missing',
		'label'     => $label,
	);
}

/**
 * Card config when the related form/page is ready.
 *
 * @param string $type wholesale|agency.
 * @return array{type: string, title: string, text: string, button: string, form_html: string, form_id: string, heading_id: string, url: string}|null
 */
function ghahghah_get_collab_card( string $type ): ?array {
	$page_url = function_exists( 'ghahghah_get_request_page_url' ) ? ghahghah_get_request_page_url( $type ) : '';
	$has_page = '' !== $page_url;

	if ( ! $has_page && ! ghahghah_collab_form_is_available( $type ) ) {
		return null;
	}

	$form_html = $has_page ? '' : ghahghah_get_collab_form_html( $type );
	if ( ! $has_page && '' === trim( $form_html ) ) {
		return null;
	}

	if ( 'wholesale' === $type ) {
		$title  = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_wholesale_title' ) );
		$text   = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_wholesale_text' ) );
		$button = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_wholesale_button' ) );
	} else {
		$title  = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_agency_title' ) );
		$text   = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_agency_text' ) );
		$button = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_agency_button' ) );
	}

	if ( '' === $title || '' === $button ) {
		return null;
	}

	return array(
		'type'       => $type,
		'title'      => $title,
		'text'       => $text,
		'button'     => $button,
		'form_html'  => $form_html,
		'form_id'    => 'ghahghah-collab-form-' . $type,
		'heading_id' => 'ghahghah-collab-form-title-' . $type,
		'url'        => $page_url,
	);
}

/**
 * Ordered cards for the front (wholesale first / RTL right).
 *
 * @return array<int, array{type: string, title: string, text: string, button: string, form_html: string, form_id: string, heading_id: string}>
 */
function ghahghah_get_collab_cards(): array {
	$out = array();
	foreach ( array( 'wholesale', 'agency' ) as $type ) {
		$card = ghahghah_get_collab_card( $type );
		if ( null !== $card ) {
			$out[] = $card;
		}
	}
	return $out;
}

/**
 * Whether the collab section should render.
 */
function ghahghah_should_render_collab(): bool {
	if ( ! (bool) ghahghah_get_collab_mod( 'ghahghah_collab_enabled' ) ) {
		return false;
	}
	if ( ! is_front_page() ) {
		return false;
	}
	return count( ghahghah_get_collab_cards() ) > 0;
}
