<?php
/**
 * REST endpoint for inquiry submissions.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\Forms;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Registers ghahghah/v1/inquiries.
 */
final class InquiryRest {

	public const NAMESPACE = 'ghahghah/v1';

	/**
	 * Attach hooks.
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/inquiries',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_inquiry' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/form-data',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'form_data' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Shared select data for forms.
	 */
	public function form_data(): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'provinces'  => \Ghahghah\Core\Data\IranLocations::all(),
				'cities'     => \Ghahghah\Core\Data\IranLocations::all_cities(),
				'products'   => InquiryService::products(),
				'activities' => InquiryService::activities(),
			),
			200
		);
	}

	/**
	 * Create inquiry from POST JSON / form body.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_inquiry( WP_REST_Request $request ) {
		$nonce = (string) $request->get_header( 'X-Ghahghah-Nonce' );
		if ( '' === $nonce ) {
			$nonce = (string) $request->get_param( '_ghahghah_nonce' );
		}
		if ( ! wp_verify_nonce( $nonce, 'ghahghah_inquiry_submit' ) ) {
			return new WP_Error( 'invalid_nonce', __( 'نشست منقضی شده است. صفحه را تازه کنید.', 'ghahghah-core' ), array( 'status' => 403 ) );
		}

		// Soft rate limit by IP (transient).
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key = 'ghahghah_inq_' . md5( $ip );
		$hits = (int) get_transient( $key );
		if ( $hits >= 8 ) {
			return new WP_Error( 'rate_limited', __( 'تعداد درخواست‌ها زیاد است. کمی بعد دوباره تلاش کنید.', 'ghahghah-core' ), array( 'status' => 429 ) );
		}
		set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

		$data = $request->get_json_params();
		if ( ! is_array( $data ) || empty( $data ) ) {
			$data = $request->get_params();
		}

		$service = new InquiryService();
		$result  = $service->create( is_array( $data ) ? $data : array() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'ok'   => true,
				'id'   => $result['id'],
				'type' => $result['type'],
			),
			201
		);
	}
}
