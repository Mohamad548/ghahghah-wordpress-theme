<?php
/**
 * Melipayamak pattern SMS sender (aligned with mobile-auth BaseServiceNumber).
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\Sms;

/**
 * Sends pattern SMS via Melipayamak REST.
 */
final class MelipayamakPatternSender {

	/**
	 * Send a pattern SMS.
	 *
	 * @param string               $mobile       Iranian mobile.
	 * @param string               $pattern_code Body/pattern id.
	 * @param string               $message      Template e.g. @409716@{#name#}{#phone#}##shared.
	 * @param array<string, string> $real_data   Variable values keyed by name.
	 * @return array{success: bool, message: string}
	 */
	public function send( string $mobile, string $pattern_code, string $message, array $real_data = array() ): array {
		$settings = $this->resolve_gateway_settings();

		if ( empty( $settings['sms_enabled'] ) || ( $settings['sms_provider'] ?? '' ) !== 'melipayamak' ) {
			return array(
				'success' => false,
				'message' => __( 'ارسال پیامک غیرفعال یا سرویس انتخاب نشده است.', 'ghahghah-core' ),
			);
		}

		$mobile = $this->normalize_mobile( $mobile );
		if ( '' === $mobile ) {
			return array(
				'success' => false,
				'message' => __( 'شماره موبایل معتبر نیست.', 'ghahghah-core' ),
			);
		}

		$username = trim( (string) ( $settings['sms_username'] ?: $settings['sms_api_key'] ) );
		$password = (string) ( $settings['sms_api_secret'] ?? '' );
		if ( '' === $username || '' === $password ) {
			return array(
				'success' => false,
				'message' => __( 'اعتبار ملی‌پیامک کامل نیست.', 'ghahghah-core' ),
			);
		}

		if ( '' === $pattern_code && preg_match( '/@(\d+)@/', $message, $m ) ) {
			$pattern_code = $m[1];
		}
		if ( '' === $pattern_code ) {
			return array(
				'success' => false,
				'message' => __( 'کد پترن وارد نشده است.', 'ghahghah-core' ),
			);
		}

		$text_comma = $this->build_text_comma( $message, $real_data );
		$urls       = array(
			'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber',
			'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber2',
		);

		foreach ( $urls as $url ) {
			$payload = array(
				'username' => $username,
				'password' => $password,
				'to'       => $mobile,
				'bodyId'   => (int) $pattern_code,
				'text'     => $text_comma,
			);

			$response = $this->post_json( $url, $payload );
			if ( null === $response ) {
				continue;
			}

			if ( $this->response_ok( $response ) ) {
				return array(
					'success' => true,
					'message' => __( 'پیامک با موفقیت ارسال شد.', 'ghahghah-core' ),
				);
			}
		}

		// Prefer Mobile_Auth_Sms_Sender when available as secondary path.
		if ( class_exists( '\Mobile_Auth_Sms_Sender' ) ) {
			$sender = new \Mobile_Auth_Sms_Sender();
			if ( method_exists( $sender, 'mobile_auth_send_sms' ) ) {
				$result = $sender->mobile_auth_send_sms( $mobile, $pattern_code, $message, $real_data );
				if ( is_array( $result ) && ! empty( $result['success'] ) ) {
					return array(
						'success' => true,
						'message' => (string) ( $result['message'] ?? '' ),
					);
				}
			}
		}

		return array(
			'success' => false,
			'message' => __( 'خطا در ارتباط با سرویس ملی پیامک.', 'ghahghah-core' ),
		);
	}

	/**
	 * Gateway settings: theme option first, then mobile-auth fallback for credentials.
	 *
	 * @return array<string, mixed>
	 */
	private function resolve_gateway_settings(): array {
		$defaults = array(
			'sms_enabled'    => 0,
			'sms_provider'   => 'melipayamak',
			'sms_username'   => '',
			'sms_api_key'    => '',
			'sms_api_secret' => '',
			'sms_line'       => '',
		);

		$gh = get_option( 'ghahghah_sms_settings', array() );
		if ( ! is_array( $gh ) ) {
			$gh = array();
		}
		$merged = wp_parse_args( $gh, $defaults );

		$creds_empty = '' === trim( (string) ( $merged['sms_username'] ?: $merged['sms_api_key'] ) )
			|| '' === trim( (string) $merged['sms_api_secret'] );

		if ( $creds_empty ) {
			$ma = get_option( 'mobile_auth_settings', array() );
			if ( is_array( $ma ) ) {
				if ( '' === trim( (string) ( $merged['sms_username'] ?: $merged['sms_api_key'] ) ) ) {
					$merged['sms_username'] = (string) ( $ma['sms_username'] ?? $ma['sms_api_key'] ?? '' );
					$merged['sms_api_key']  = (string) ( $ma['sms_api_key'] ?? '' );
				}
				if ( '' === trim( (string) $merged['sms_api_secret'] ) ) {
					$merged['sms_api_secret'] = (string) ( $ma['sms_api_secret'] ?? '' );
				}
				if ( '' === trim( (string) $merged['sms_line'] ) && ! empty( $ma['sms_line'] ) ) {
					$merged['sms_line'] = (string) $ma['sms_line'];
				}
				if ( empty( $merged['sms_provider'] ) && ! empty( $ma['sms_provider'] ) ) {
					$merged['sms_provider'] = (string) $ma['sms_provider'];
				}
			}
		}

		return $merged;
	}

	/**
	 * Normalize Iranian mobile to 09xxxxxxxxx.
	 */
	public function normalize_mobile( string $mobile ): string {
		$mobile = preg_replace( '/[^0-9]/', '', $mobile ) ?? '';
		if ( strlen( $mobile ) === 11 && preg_match( '/^09[0-9]{9}$/', $mobile ) ) {
			return $mobile;
		}
		if ( strlen( $mobile ) === 12 && str_starts_with( $mobile, '98' ) ) {
			return '0' . substr( $mobile, 2 );
		}
		if ( strlen( $mobile ) === 10 && str_starts_with( $mobile, '9' ) ) {
			return '0' . $mobile;
		}
		return '';
	}

	/**
	 * Build comma-separated pattern values in template variable order.
	 *
	 * @param array<string, string> $real_data Data.
	 */
	private function build_text_comma( string $message, array $real_data ): string {
		$cleaned = $message;
		$cleaned = preg_replace( '/^@\d+@/', '', $cleaned ) ?? $cleaned;
		$cleaned = preg_replace( '/@\d+@$/', '', $cleaned ) ?? $cleaned;
		$cleaned = preg_replace( '/^shared##/', '', $cleaned ) ?? $cleaned;
		$cleaned = preg_replace( '/##shared$/', '', $cleaned ) ?? $cleaned;

		$order = array();
		if ( preg_match_all( '/\{\{#(\w+)#\}\}|\{#(\w+)#\}|#(\w+)#/', $cleaned, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $m ) {
				$name = $m[1] ?: ( $m[2] ?: $m[3] );
				if ( $name && ! in_array( $name, $order, true ) ) {
					$order[] = $name;
				}
			}
		}

		$values = array();
		foreach ( $order as $var ) {
			$val = isset( $real_data[ $var ] ) ? trim( (string) $real_data[ $var ] ) : '';
			$values[] = '' !== $val ? $val : '—';
		}

		if ( empty( $values ) ) {
			$values[] = $real_data['name'] ?? $real_data['phone'] ?? '—';
		}

		return implode( ',', $values );
	}

	/**
	 * @param array<string, mixed> $payload JSON body.
	 * @return array<string, mixed>|null
	 */
	private function post_json( string $url, array $payload ): ?array {
		$response = wp_remote_post(
			$url,
			array(
				'body'      => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ),
				'timeout'   => 8,
				'sslverify' => true,
				'headers'   => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log( $response->get_error_message() );
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		return is_array( $data ) ? $data : null;
	}

	/**
	 * @param array<string, mixed> $result API JSON.
	 */
	private function response_ok( array $result ): bool {
		if ( isset( $result['StrRetStatus'] ) && in_array( (string) $result['StrRetStatus'], array( 'Ok', 'OK' ), true ) ) {
			return true;
		}
		if ( isset( $result['RetStatus'] ) && (int) $result['RetStatus'] === 1 ) {
			return true;
		}
		if ( isset( $result['Value'] ) && is_string( $result['Value'] ) && strlen( $result['Value'] ) > 15 && is_numeric( $result['Value'] ) ) {
			return true;
		}
		return false;
	}

	private function log( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[ghahghah-core SMS] ' . $message );
		}
	}
}
