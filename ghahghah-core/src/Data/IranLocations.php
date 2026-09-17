<?php
/**
 * Compact Iran province → cities map for agency / wholesale forms.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\Data;

/**
 * Static location helpers.
 */
final class IranLocations {

	/**
	 * Province => city list.
	 *
	 * @return array<string, array<int, string>>
	 */
	public static function all(): array {
		return array(
			'آذربایجان شرقی'   => array( 'تبریز', 'مراغه', 'مرند', 'اهر', 'میانه' ),
			'آذربایجان غربی'   => array( 'ارومیه', 'خوی', 'مهاباد', 'بوکان', 'میاندوآب' ),
			'اردبیل'           => array( 'اردبیل', 'پارس‌آباد', 'مشگین‌شهر', 'خلخال' ),
			'اصفهان'           => array( 'اصفهان', 'کاشان', 'نجف‌آباد', 'خمینی‌شهر', 'شاهین‌شهر' ),
			'البرز'            => array( 'کرج', 'فردیس', 'نظرآباد', 'ساوجبلاغ', 'طالقان' ),
			'ایلام'            => array( 'ایلام', 'دهلران', 'ایوان', 'آبدانان' ),
			'بوشهر'            => array( 'بوشهر', 'برازجان', 'گناوه', 'کنگان', 'عسلویه' ),
			'تهران'            => array( 'تهران', 'اسلامشهر', 'شهریار', 'ری', 'قدس', 'ملارد', 'پاکدشت', 'ورامین' ),
			'چهارمحال و بختیاری' => array( 'شهرکرد', 'بروجن', 'فارسان', 'لردگان' ),
			'خراسان جنوبی'     => array( 'بیرجند', 'قائن', 'طبس', 'فردوس' ),
			'خراسان رضوی'      => array( 'مشهد', 'نیشابور', 'سبزوار', 'تربت‌حیدریه', 'قوچان' ),
			'خراسان شمالی'     => array( 'بجنورد', 'شیروان', 'اسفراین', 'جاجرم' ),
			'خوزستان'          => array( 'اهواز', 'آبادان', 'دزفول', 'ماهشهر', 'اندیمشک', 'خرمشهر' ),
			'زنجان'            => array( 'زنجان', 'ابهر', 'خدابنده', 'خرمدره' ),
			'سمنان'            => array( 'سمنان', 'شاهرود', 'دامغان', 'گرمسار' ),
			'سیستان و بلوچستان' => array( 'زاهدان', 'چابهار', 'ایرانشهر', 'زابل' ),
			'فارس'             => array( 'شیراز', 'مرودشت', 'جهرم', 'فسا', 'کازرون', 'لار' ),
			'قزوین'            => array( 'قزوین', 'تاکستان', 'آبیک', 'بوئین‌زهرا' ),
			'قم'               => array( 'قم' ),
			'کردستان'          => array( 'سنندج', 'سقز', 'مريوان', 'بانه', 'قروه' ),
			'کرمان'            => array( 'کرمان', 'سیرجان', 'رفسنجان', 'جیرفت', 'بم' ),
			'کرمانشاه'         => array( 'کرمانشاه', 'اسلام‌آباد غرب', 'سنقر', 'هرسین' ),
			'کهگیلویه و بویراحمد' => array( 'یاسوج', 'دهدشت', 'گچساران' ),
			'گلستان'           => array( 'گرگان', 'گنبدکاووس', 'علی‌آباد', 'آق‌قلا', 'بندرترکمن' ),
			'گیلان'            => array( 'رشت', 'انزلی', 'لاهیجان', 'لنگرود', 'آستارا', 'تالش' ),
			'لرستان'           => array( 'خرم‌آباد', 'بروجرد', 'دورود', 'الیگودرز', 'کوهدشت' ),
			'مازندران'         => array( 'ساری', 'بابل', 'آمل', 'قائم‌شهر', 'بابلسر', 'چالوس', 'تنکابن' ),
			'مرکزی'            => array( 'اراک', 'ساوه', 'خمین', 'محلات', 'دلیجان' ),
			'هرمزگان'          => array( 'بندرعباس', 'میناب', 'قشم', 'کیش', 'بندرلنگه' ),
			'همدان'            => array( 'همدان', 'ملایر', 'نهاوند', 'اسدآباد' ),
			'یزد'              => array( 'یزد', 'میبد', 'اردکان', 'مهریز' ),
		);
	}

	/**
	 * Province names only.
	 *
	 * @return array<int, string>
	 */
	public static function provinces(): array {
		return array_keys( self::all() );
	}

	/**
	 * Cities for a province (empty if unknown).
	 *
	 * @return array<int, string>
	 */
	public static function cities_for( string $province ): array {
		$map = self::all();
		return $map[ $province ] ?? array();
	}

	/**
	 * Flat unique city list (wholesale city dropdown).
	 *
	 * @return array<int, string>
	 */
	public static function all_cities(): array {
		$cities = array();
		foreach ( self::all() as $list ) {
			foreach ( $list as $city ) {
				$cities[ $city ] = $city;
			}
		}
		$out = array_values( $cities );
		sort( $out, SORT_STRING );
		return $out;
	}
}
