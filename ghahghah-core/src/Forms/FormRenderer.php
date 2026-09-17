<?php
/**
 * Renders inquiry forms via theme templates + shortcodes.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\Forms;

use Ghahghah\Core\Data\IranLocations;

/**
 * Form rendering bridge for the theme collab section.
 */
final class FormRenderer {

	/**
	 * Attach hooks and global render helpers.
	 */
	public function register(): void {
		add_shortcode( 'ghahghah_wholesale_form', array( $this, 'shortcode_wholesale' ) );
		add_shortcode( 'ghahghah_agency_form', array( $this, 'shortcode_agency' ) );
		add_shortcode( 'ghahghah_contact_form', array( $this, 'shortcode_contact' ) );
	}

	/**
	 * @param array<string, string>|string $atts Shortcode atts.
	 */
	public function shortcode_wholesale( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'variant' => 'embed',
				'product' => 0,
			),
			is_array( $atts ) ? $atts : array(),
			'ghahghah_wholesale_form'
		);
		ob_start();
		self::render(
			'wholesale',
			array(
				'variant'           => (string) $atts['variant'],
				'preselect_product' => absint( $atts['product'] ),
			)
		);
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, string>|string $atts Shortcode atts.
	 */
	public function shortcode_agency( $atts = array() ): string {
		$atts = shortcode_atts(
			array( 'variant' => 'embed' ),
			is_array( $atts ) ? $atts : array(),
			'ghahghah_agency_form'
		);
		ob_start();
		self::render( 'agency', array( 'variant' => (string) $atts['variant'] ) );
		return (string) ob_get_clean();
	}

	/**
	 * @param array<string, string>|string $atts Shortcode atts.
	 */
	public function shortcode_contact( $atts = array() ): string {
		$atts = shortcode_atts(
			array( 'variant' => 'page' ),
			is_array( $atts ) ? $atts : array(),
			'ghahghah_contact_form'
		);
		ob_start();
		self::render( 'contact', array( 'variant' => (string) $atts['variant'] ) );
		return (string) ob_get_clean();
	}

	/**
	 * Load theme template for a form type.
	 *
	 * @param array<string, mixed> $args Extra context.
	 */
	public static function render( string $type, array $args = array() ): void {
		if ( ! in_array( $type, array( 'wholesale', 'agency', 'contact' ), true ) ) {
			return;
		}

		$context = array_merge(
			array(
				'type'              => $type,
				'variant'           => 'embed',
				'form_title'        => '',
				'preselect_product' => 0,
				'provinces'         => IranLocations::all(),
				'cities'            => IranLocations::all_cities(),
				'products'          => InquiryService::products(),
				'activities'        => InquiryService::activities(),
				'subjects'          => InquiryService::contact_subjects(),
				'rest_url'          => esc_url_raw( rest_url( InquiryRest::NAMESPACE . '/inquiries' ) ),
				'nonce'             => wp_create_nonce( 'ghahghah_inquiry_submit' ),
				'products_archive'  => function_exists( 'get_post_type_archive_link' )
					? (string) get_post_type_archive_link( 'ghahghah_product' )
					: home_url( '/' ),
				'home_url'          => home_url( '/' ),
			),
			$args
		);

		$slug = match ( $type ) {
			'agency'  => 'form-agency',
			'contact' => 'form-contact',
			default   => 'form-wholesale',
		};
		$theme_path  = trailingslashit( get_stylesheet_directory() ) . 'template-parts/forms/' . $slug . '.php';
		$parent_path = trailingslashit( get_template_directory() ) . 'template-parts/forms/' . $slug . '.php';

		$path = is_readable( $theme_path ) ? $theme_path : $parent_path;
		if ( ! is_readable( $path ) ) {
			echo '<p class="ghahghah-form__missing">' . esc_html__( 'قالب فرم یافت نشد.', 'ghahghah-core' ) . '</p>';
			return;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Scoped template context.
		extract( $context, EXTR_SKIP );
		include $path;
	}
}
