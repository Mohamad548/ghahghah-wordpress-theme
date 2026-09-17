<?php
/**
 * Jalali (Shamsi) display dates for the front end.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persian month names (1–12).
 *
 * @return array<int, string>
 */
function ghahghah_jalali_month_names(): array {
	return array(
		1  => 'فروردین',
		2  => 'اردیبهشت',
		3  => 'خرداد',
		4  => 'تیر',
		5  => 'مرداد',
		6  => 'شهریور',
		7  => 'مهر',
		8  => 'آبان',
		9  => 'آذر',
		10 => 'دی',
		11 => 'بهمن',
		12 => 'اسفند',
	);
}

/**
 * Convert Western digits to Persian digits.
 */
function ghahghah_to_persian_digits( string $value ): string {
	return strtr(
		$value,
		array(
			'0' => '۰',
			'1' => '۱',
			'2' => '۲',
			'3' => '۳',
			'4' => '۴',
			'5' => '۵',
			'6' => '۶',
			'7' => '۷',
			'8' => '۸',
			'9' => '۹',
		)
	);
}

/**
 * Convert Gregorian Y-m-d to Jalali [year, month, day].
 *
 * @return array{0: int, 1: int, 2: int}
 */
function ghahghah_gregorian_to_jalali( int $gy, int $gm, int $gd ): array {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
	$days %= 12053;
	$jy   += 4 * (int) ( $days / 1461 );
	$days %= 1461;

	if ( $days > 365 ) {
		$jy   += (int) ( ( $days - 1 ) / 365 );
		$days  = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jy, $jm, $jd );
}

/**
 * Whether a date format is machine-oriented (keep Gregorian).
 */
function ghahghah_is_machine_date_format( string $format ): bool {
	$format = trim( $format );
	if ( '' === $format ) {
		return false;
	}

	$machine = array(
		DATE_W3C,
		DATE_ATOM,
		DATE_COOKIE,
		DATE_ISO8601,
		DATE_RFC822,
		DATE_RFC850,
		DATE_RFC1036,
		DATE_RFC1123,
		DATE_RFC2822,
		DATE_RFC3339,
		DATE_RSS,
		'c',
		'r',
		'U',
		'Y-m-d',
		'Y-m-d H:i:s',
		'Ymd',
	);

	return in_array( $format, $machine, true );
}

/**
 * Format Gregorian civil date as a human Jalali label: «۲۲ شهریور ۱۴۰۵».
 */
function ghahghah_format_jalali_date( int $gy, int $gm, int $gd ): string {
	if ( $gy < 1 || $gm < 1 || $gm > 12 || $gd < 1 || $gd > 31 ) {
		return '';
	}

	[ $jy, $jm, $jd ] = ghahghah_gregorian_to_jalali( $gy, $gm, $gd );
	$months           = ghahghah_jalali_month_names();
	$month_name       = $months[ $jm ] ?? '';

	if ( '' === $month_name ) {
		return '';
	}

	$label = sprintf( '%d %s %d', $jd, $month_name, $jy );
	return ghahghah_to_persian_digits( $label );
}

/**
 * Local civil Y-n-j for a post published or modified date.
 *
 * @param WP_Post|int|null $post Post object, ID, or null for current.
 * @return array{0: int, 1: int, 2: int}|null
 */
function ghahghah_get_post_civil_ymd( $post = null, string $field = 'date' ): ?array {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	if ( 'modified' === $field ) {
		$gy = (int) get_post_modified_time( 'Y', false, $post );
		$gm = (int) get_post_modified_time( 'n', false, $post );
		$gd = (int) get_post_modified_time( 'j', false, $post );
	} else {
		$gy = (int) get_post_time( 'Y', false, $post );
		$gm = (int) get_post_time( 'n', false, $post );
		$gd = (int) get_post_time( 'j', false, $post );
	}

	if ( $gy < 1 || $gm < 1 || $gd < 1 ) {
		return null;
	}

	return array( $gy, $gm, $gd );
}

/**
 * Convert front-end display dates to Jalali; keep machine formats untouched.
 *
 * @param string       $the_date Formatted date string.
 * @param string       $format   PHP date format.
 * @param WP_Post|null $post     Post object.
 */
function ghahghah_filter_the_date_jalali( string $the_date, string $format, $post ): string {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $the_date;
	}

	if ( ghahghah_is_machine_date_format( $format ) ) {
		return $the_date;
	}

	$ymd = ghahghah_get_post_civil_ymd( $post, 'date' );
	if ( null === $ymd ) {
		return $the_date;
	}

	$jalali = ghahghah_format_jalali_date( $ymd[0], $ymd[1], $ymd[2] );
	return '' !== $jalali ? $jalali : $the_date;
}
add_filter( 'get_the_date', 'ghahghah_filter_the_date_jalali', 10, 3 );

/**
 * Convert front-end modified dates to Jalali.
 *
 * @param string       $the_date Formatted date string.
 * @param string       $format   PHP date format.
 * @param WP_Post|null $post     Post object.
 */
function ghahghah_filter_the_modified_date_jalali( string $the_date, string $format, $post ): string {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $the_date;
	}

	if ( ghahghah_is_machine_date_format( $format ) ) {
		return $the_date;
	}

	$ymd = ghahghah_get_post_civil_ymd( $post, 'modified' );
	if ( null === $ymd ) {
		return $the_date;
	}

	$jalali = ghahghah_format_jalali_date( $ymd[0], $ymd[1], $ymd[2] );
	return '' !== $jalali ? $jalali : $the_date;
}
add_filter( 'get_the_modified_date', 'ghahghah_filter_the_modified_date_jalali', 10, 3 );
