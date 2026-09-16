<?php
/**
 * Create and notify inquiry submissions.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\Forms;

use Ghahghah\Core\Data\IranLocations;
use Ghahghah\Core\PostTypes\Inquiry;
use Ghahghah\Core\Sms\MelipayamakPatternSender;
use WP_Error;

/**
 * Validates and stores inquiry forms.
 */
final class InquiryService {

	/**
	 * Default activity options for agency form.
	 *
	 * @return array<int, string>
	 */
	public static function default_activities(): array {
		return array(
			'عمده‌فروشی مواد غذایی',
			'پخش مویرگی',
			'فروشگاه زنجیره‌ای',
			'سوپرمارکت / هایپر',
			'سایر',
		);
	}

	/**
	 * Activity list from theme option or defaults.
	 *
	 * @return array<int, string>
	 */
	public static function activities(): array {
		$settings = get_option( 'ghahghah_sms_settings', array() );
		$raw      = is_array( $settings ) ? (string) ( $settings['agency_activities'] ?? '' ) : '';
		if ( '' === trim( $raw ) ) {
			return self::default_activities();
		}
		$lines = preg_split( '/\r\n|\r|\n/', $raw ) ?: array();
		$out   = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out ?: self::default_activities();
	}

	/**
	 * Published product titles for wholesale select.
	 *
	 * @return array<int, array{id: int, title: string}>
	 */
	public static function products(): array {
		if ( ! post_type_exists( 'ghahghah_product' ) ) {
			return array();
		}
		$posts = get_posts(
			array(
				'post_type'      => 'ghahghah_product',
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		$out = array();
		foreach ( $posts as $post ) {
			$out[] = array(
				'id'    => (int) $post->ID,
				'title' => get_the_title( $post ),
			);
		}
		return $out;
	}

	/**
	 * Default contact form subjects.
	 *
	 * @return array<int, string>
	 */
	public static function default_contact_subjects(): array {
		return array(
			'همکاری و نمایندگی',
			'خرید عمده',
			'پیشنهاد یا انتقاد',
			'سایر',
		);
	}

	/**
	 * Contact subject options (theme filterable).
	 *
	 * @return array<int, string>
	 */
	public static function contact_subjects(): array {
		$subjects = self::default_contact_subjects();
		/**
		 * Filter contact message subjects.
		 *
		 * @param array<int, string> $subjects Subjects.
		 */
		$filtered = apply_filters( 'ghahghah_contact_subjects', $subjects );
		return is_array( $filtered ) && array() !== $filtered ? array_values( array_map( 'strval', $filtered ) ) : $subjects;
	}

	/**
	 * Validate and create an inquiry.
	 *
	 * @param array<string, mixed> $data Request body.
	 * @return array{id: int, type: string}|WP_Error
	 */
	public function create( array $data ) {
		$type = sanitize_key( (string) ( $data['type'] ?? '' ) );
		if ( ! in_array( $type, array( 'wholesale', 'agency', 'contact' ), true ) ) {
			return new WP_Error( 'invalid_type', __( 'نوع درخواست نامعتبر است.', 'ghahghah-core' ), array( 'status' => 400 ) );
		}

		$errors = array();

		$full_name = sanitize_text_field( (string) ( $data['full_name'] ?? '' ) );
		$phone_raw = (string) ( $data['phone'] ?? '' );
		$sender    = new MelipayamakPatternSender();
		$phone     = $sender->normalize_mobile( $phone_raw );
		$company   = sanitize_text_field( (string) ( $data['company'] ?? '' ) );
		$city      = sanitize_text_field( (string) ( $data['city'] ?? '' ) );
		$message   = sanitize_textarea_field( (string) ( $data['message'] ?? '' ) );
		$consent   = ! empty( $data['consent'] );

		if ( '' === $full_name ) {
			$errors['full_name'] = __( 'نام و نام خانوادگی را وارد کنید.', 'ghahghah-core' );
		}
		if ( '' === $phone ) {
			$errors['phone'] = __( 'شماره تماس را وارد کنید.', 'ghahghah-core' );
		}
		if ( 'contact' !== $type && ! $consent ) {
			$errors['consent'] = __( 'برای ادامه باید با پیگیری درخواست موافقت کنید.', 'ghahghah-core' );
		}
		if ( 'contact' === $type ) {
			$consent = true;
		}

		$meta = array(
			'inquiry_type' => $type,
			'full_name'    => $full_name,
			'phone'        => $phone,
			'company'      => $company,
			'city'         => $city,
			'message'      => $message,
			'consent'      => $consent ? '1' : '',
			'province'     => '',
			'product'      => '',
			'quantity'     => '',
			'activity'     => '',
			'experience'   => '',
			'coverage'     => '',
			'subject'      => '',
		);

		if ( 'contact' === $type ) {
			$subject = sanitize_text_field( (string) ( $data['subject'] ?? '' ) );
			if ( '' === $subject || ! in_array( $subject, self::contact_subjects(), true ) ) {
				$errors['subject'] = __( 'موضوع پیام را انتخاب کنید.', 'ghahghah-core' );
			}
			if ( '' === $message ) {
				$errors['message'] = __( 'متن پیام را وارد کنید.', 'ghahghah-core' );
			}
			$meta['subject'] = $subject;
		} elseif ( 'wholesale' === $type ) {
			$product_raw = (string) ( $data['product'] ?? '' );
			$quantity    = sanitize_text_field( (string) ( $data['quantity'] ?? '' ) );
			$product_id  = absint( $product_raw );
			$product_label = '';

			if ( $product_id > 0 ) {
				$product_post = get_post( $product_id );
				if (
					! $product_post
					|| 'ghahghah_product' !== $product_post->post_type
					|| 'publish' !== $product_post->post_status
				) {
					$errors['product'] = __( 'محصول انتخاب‌شده معتبر نیست.', 'ghahghah-core' );
				} else {
					$product_label = get_the_title( $product_post );
				}
			} else {
				$product_label = sanitize_text_field( $product_raw );
			}

			if ( '' === $city ) {
				$errors['city'] = __( 'شهر را انتخاب کنید.', 'ghahghah-core' );
			}
			if ( '' === $company ) {
				$errors['company'] = __( 'نام مجموعه یا فروشگاه را وارد کنید.', 'ghahghah-core' );
			}
			if ( '' === $product_label ) {
				$errors['product'] = __( 'محصول موردنظر را انتخاب کنید.', 'ghahghah-core' );
			}
			if ( '' === $quantity ) {
				$errors['quantity'] = __( 'تعداد تقریبی سفارش را وارد کنید.', 'ghahghah-core' );
			}
			$meta['product']    = $product_label;
			$meta['product_id'] = $product_id > 0 ? (string) $product_id : '';
			$meta['quantity']   = $quantity;
		} else {
			$province   = sanitize_text_field( (string) ( $data['province'] ?? '' ) );
			$activity   = sanitize_text_field( (string) ( $data['activity'] ?? '' ) );
			$experience = sanitize_text_field( (string) ( $data['experience'] ?? '' ) );
			$coverage   = sanitize_text_field( (string) ( $data['coverage'] ?? '' ) );

			if ( '' === $province || ! isset( IranLocations::all()[ $province ] ) ) {
				$errors['province'] = __( 'استان را انتخاب کنید.', 'ghahghah-core' );
			}
			if ( '' === $city ) {
				$errors['city'] = __( 'شهر را انتخاب کنید.', 'ghahghah-core' );
			} elseif ( $province && ! in_array( $city, IranLocations::cities_for( $province ), true ) ) {
				$errors['city'] = __( 'شهر با استان هم‌خوان نیست.', 'ghahghah-core' );
			}
			if ( '' === $activity ) {
				$errors['activity'] = __( 'زمینه فعالیت را انتخاب کنید.', 'ghahghah-core' );
			}

			$meta['province']   = $province;
			$meta['activity']   = $activity;
			$meta['experience'] = $experience;
			$meta['coverage']   = $coverage;
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'validation_failed',
				__( 'لطفاً خطاهای فرم را برطرف کنید.', 'ghahghah-core' ),
				array(
					'status' => 400,
					'fields' => $errors,
				)
			);
		}

		$type_label = match ( $type ) {
			'agency'  => __( 'نمایندگی', 'ghahghah-core' ),
			'contact' => __( 'تماس', 'ghahghah-core' ),
			default   => __( 'خرید عمده', 'ghahghah-core' ),
		};
		$title = sprintf( '%s — %s', $type_label, $full_name );

		$post_id = wp_insert_post(
			array(
				'post_type'   => Inquiry::POST_TYPE,
				'post_status' => 'private',
				'post_title'  => $title,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'save_failed', __( 'ذخیره درخواست ممکن نشد.', 'ghahghah-core' ), array( 'status' => 500 ) );
		}

		foreach ( $meta as $key => $value ) {
			update_post_meta( (int) $post_id, '_ghahghah_' . $key, $value );
		}

		$this->send_notifications( $type, $meta );

		return array(
			'id'   => (int) $post_id,
			'type' => $type,
		);
	}

	/**
	 * Best-effort pattern SMS after successful save.
	 *
	 * @param array<string, string> $meta Sanitized meta.
	 */
	private function send_notifications( string $type, array $meta ): void {
		$settings = get_option( 'ghahghah_sms_settings', array() );
		if ( ! is_array( $settings ) || empty( $settings['sms_enabled'] ) ) {
			return;
		}

		if ( 'contact' === $type ) {
			return;
		}

		$prefix = 'wholesale' === $type ? 'wholesale' : 'agency';
		$vars   = array(
			'name'       => $meta['full_name'],
			'phone'      => $meta['phone'],
			'company'    => $meta['company'],
			'city'       => $meta['city'],
			'province'   => $meta['province'],
			'product'    => $meta['product'],
			'quantity'   => $meta['quantity'],
			'activity'   => $meta['activity'],
			'experience' => $meta['experience'],
			'coverage'   => $meta['coverage'],
			'subject'    => $meta['subject'] ?? '',
			'type'       => 'agency' === $type ? 'نمایندگی' : 'خرید عمده',
		);

		$sender = new MelipayamakPatternSender();

		if ( ! empty( $settings[ $prefix . '_user_enabled' ] ) ) {
			$code = trim( (string) ( $settings[ $prefix . '_user_pattern' ] ?? '' ) );
			$msg  = trim( (string) ( $settings[ $prefix . '_user_message' ] ?? '' ) );
			if ( '' !== $code || '' !== $msg ) {
				$sender->send( $meta['phone'], $code, $msg, $vars );
			}
		}

		if ( ! empty( $settings[ $prefix . '_admin_enabled' ] ) ) {
			$admin_raw = trim( (string) ( $settings[ $prefix . '_admin_phone' ] ?? '' ) );
			if ( '' === $admin_raw ) {
				$admin_raw = trim( (string) ( $settings['admin_phone'] ?? '' ) );
			}
			$admin_phone = $sender->normalize_mobile( $admin_raw );
			$code        = trim( (string) ( $settings[ $prefix . '_admin_pattern' ] ?? '' ) );
			$msg         = trim( (string) ( $settings[ $prefix . '_admin_message' ] ?? '' ) );
			if ( '' !== $admin_phone && ( '' !== $code || '' !== $msg ) ) {
				$sender->send( $admin_phone, $code, $msg, $vars );
			}
		}
	}
}
