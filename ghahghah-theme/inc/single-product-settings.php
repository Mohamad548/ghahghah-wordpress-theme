<?php
/**
 * Single product page: meta fields, helpers, taxonomy, and unique product copy.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Product meta keys. */
const GHAHGHAH_PRODUCT_META_WEIGHT       = '_ghahghah_net_weight';
const GHAHGHAH_PRODUCT_META_FLAVOUR      = '_ghahghah_flavour';
const GHAHGHAH_PRODUCT_META_PRODUCT_TYPE = '_ghahghah_product_type';
const GHAHGHAH_PRODUCT_META_PACKAGE      = '_ghahghah_package_type';
const GHAHGHAH_PRODUCT_META_SHELF        = '_ghahghah_shelf_life';
const GHAHGHAH_PRODUCT_META_STORAGE      = '_ghahghah_storage';
const GHAHGHAH_PRODUCT_META_SUBTITLE     = '_ghahghah_subtitle';
const GHAHGHAH_PRODUCT_META_SHORT_INTRO  = '_ghahghah_short_intro';
const GHAHGHAH_PRODUCT_META_MOTTO        = '_ghahghah_motto';

/**
 * Whether current view is a single product.
 */
function ghahghah_is_single_product(): bool {
	return is_singular( 'ghahghah_product' );
}

/**
 * Shared default specs for all corn-pellet SKUs.
 *
 * @return array<string, string>
 */
function ghahghah_product_shared_defaults(): array {
	return array(
		'weight'       => __( '۶۰ گرم', 'ghahghah' ),
		'product_type' => __( 'اسنک ذرت', 'ghahghah' ),
		'package_type' => __( 'پاکتی', 'ghahghah' ),
		'shelf_life'   => __( '۶ ماه', 'ghahghah' ),
		'storage'      => __( 'در جای خشک و خنک نگهداری شود', 'ghahghah' ),
		'motto'        => __( 'خوشمزه مثل همیشه...', 'ghahghah' ),
		'slogan'       => __( 'طعم شادی در هر لحظه!', 'ghahghah' ),
	);
}

/**
 * Unique copy + specs keyed by `_ghahghah_catalog_key`.
 *
 * @return array<string, array<string, mixed>>
 */
function ghahghah_product_content_catalog(): array {
	$shared = ghahghah_product_shared_defaults();

	return array(
		'corn-pellet-lemon'          => array(
			'display_title' => __( 'اسنک ذرت طعم لیمویی', 'ghahghah' ),
			'subtitle'      => __( 'ترشی ملایم لیمو برای میان‌وعده‌های باانرژی', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم لیمویی، ترکیبی از تردی ذرت برشته و عطر تند و تازه لیمو است؛ انتخابی شاداب برای مدرسه، محل کار و دورهمی‌ها.', 'ghahghah' ),
			'content'       => __( "اسنک ذرت طعم لیمویی قهقهه با بافت ترد و سبک، برای کسانی ساخته شده که طعم‌های تارت و خنک را دوست دارند.\nاین محصول با ذرت منتخب و ادویه‌کاری دقیق، میان‌وعده‌ای سبک و اشتهابرانگیز می‌سازد و در کنار نوشیدنی خنک، طعمی ماندگار به‌جا می‌گذارد.", 'ghahghah' ),
			'flavour'       => __( 'لیمویی', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم لیمویی', 'میان‌وعده', 'ترش و تازه' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-vinegar'        => array(
			'display_title' => __( 'اسنک ذرت طعم سرکه‌ای', 'ghahghah' ),
			'subtitle'      => __( 'طعم کلاسیک سرکه برای دوست‌داران حس تند و دلچسب', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم سرکه‌ای، یادآور طعم‌های کلاسیک و محبوب است؛ ترد، سبک و مناسب لحظه‌هایی که هوس یک میان‌وعده متفاوت دارید.', 'ghahghah' ),
			'content'       => __( "طعم سرکه‌ای قهقهه با تعادل دقیق بین ترشی و ادویه، بافتی ترد و ماندگار دارد.\nاین محصول برای پخش مویرگی، سوپرمارکت و سبد خرید خانواده طراحی شده و در هر بسته، کیفیت ثابت برند قهقهه را حفظ می‌کند.", 'ghahghah' ),
			'flavour'       => __( 'سرکه‌ای', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم سرکه‌ای', 'کلاسیک', 'میان‌وعده' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-pizza'          => array(
			'display_title' => __( 'اسنک ذرت طعم پیتزا', 'ghahghah' ),
			'subtitle'      => __( 'طعمی لذیذ برای لحظات شاد خانوادگی', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم پیتزا، ترکیبی هیجان‌انگیز از ذرت برشته و ادویه‌های مخصوص پیتزا است؛ انتخابی عالی در کنار خانواده و دوستان.', 'ghahghah' ),
			'content'       => __( "اسنک ذرت طعم پیتزا قهقهه، الهام‌گرفته از یکی از محبوب‌ترین طعم‌ها در سراسر دنیاست.\nبا بافت ترد و سبک و عطر ادویه‌های پیتزا، میان‌وعده مدرسه، محل کار و هر زمانی که هوس یک طعم خاص دارید را خوشمزه‌تر می‌کند.", 'ghahghah' ),
			'flavour'       => __( 'پیتزا', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم پیتزا', 'محبوب', 'خانوادگی' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-shallot-yogurt' => array(
			'display_title' => __( 'اسنک ذرت طعم ماست موسیر', 'ghahghah' ),
			'subtitle'      => __( 'طعمی خنک و اصیل با حس ایرانی ماست و موسیر', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم ماست موسیر، ترکیبی ملایم و خاص از خنکی ماست و عطر موسیر است؛ مناسب کسانی که طعم‌های سنتی و متفاوت را می‌پسندند.', 'ghahghah' ),
			'content'       => __( "طعم ماست موسیر قهقهه با ادویه‌کاری دقیق، میان‌وعده‌ای متفاوت در سبد محصولات برند است.\nبافت ترد ذرت در کنار این طعم آشنا، انتخابی جذاب برای دورهمی‌ها و فروش در فروشگاه‌های محلی به‌شمار می‌رود.", 'ghahghah' ),
			'flavour'       => __( 'ماست موسیر', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'ماست موسیر', 'طعم ایرانی', 'میان‌وعده' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-parsley-onion'  => array(
			'display_title' => __( 'اسنک ذرت طعم پیاز جعفری', 'ghahghah' ),
			'subtitle'      => __( 'عطر سبزیجات تازه در یک میان‌وعده ترد', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم پیاز جعفری، طعمی گیاهی، معطر و سبک دارد؛ ایده‌آل برای کسانی که میان‌وعده‌های کمتر تند و بیشتر معطر را دوست دارند.', 'ghahghah' ),
			'content'       => __( "طعم پیاز جعفری قهقهه با ترکیب متعادل سبزیجات خشک و ذرت برشته، حس تازگی را در هر لقمه منتقل می‌کند.\nاین محصول در کنار سایر طعم‌ها، تنوع سبد فروش عمده و خرده‌فروشی را کامل‌تر می‌سازد.", 'ghahghah' ),
			'flavour'       => __( 'پیاز جعفری', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'پیاز جعفری', 'معطر', 'گیاهی' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-ketchup'        => array(
			'display_title' => __( 'اسنک ذرت طعم کچاپ', 'ghahghah' ),
			'subtitle'      => __( 'طعم آشنای گوجه‌ای برای هواداران کچاپ', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم کچاپ، شیرینی ملایم و عطر گوجه را با تردی ذرت ترکیب می‌کند؛ انتخابی محبوب برای کودکان و بزرگسالان.', 'ghahghah' ),
			'content'       => __( "طعم کچاپ قهقهه یکی از پرطرفدارترین گزینه‌های سبد محصولات است.\nبا رنگ و عطر اشتهابرانگیز و بافت سبک، هم برای مصرف خانگی و هم برای سفارش عمده فروشگاه‌ها گزینه‌ای مطمئن به‌حساب می‌آید.", 'ghahghah' ),
			'flavour'       => __( 'کچاپ', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم کچاپ', 'محبوب', 'خانوادگی' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-cheese'         => array(
			'display_title' => __( 'اسنک ذرت طعم پنیری', 'ghahghah' ),
			'subtitle'      => __( 'طعم غنی پنیر برای میان‌وعده‌های سیرکننده', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم پنیری، عطر و مزه پنیر را روی بافت ترد ذرت می‌نشاند؛ انتخابی دلچسب برای هر زمان از روز.', 'ghahghah' ),
			'content'       => __( "طعم پنیری قهقهه با ادویه‌کاری یکنواخت و کیفیت ثابت، یکی از طعم‌های پایه سبد محصولات است.\nاین محصول برای تنوع‌بخشی به قفسه فروش و پاسخ به سلیقه‌های کلاسیک طراحی شده است.", 'ghahghah' ),
			'flavour'       => __( 'پنیری', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم پنیری', 'کلاسیک', 'میان‌وعده' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-chicken'        => array(
			'display_title' => __( 'اسنک ذرت طعم مرغ', 'ghahghah' ),
			'subtitle'      => __( 'طعمی گرم و اشتهابرانگیز برای دوست‌داران مرغ', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم مرغ، عطر ادویه‌های مخصوص مرغ را با تردی ذرت ترکیب می‌کند؛ انتخابی محبوب برای میان‌وعده‌های شور و سیرکننده.', 'ghahghah' ),
			'content'       => __( "طعم مرغ قهقهه با ادویه‌کاری متعادل و بافت سبک، یکی از طعم‌های پرطرفدار سبد محصولات است.\nاین محصول برای تنوع قفسه فروشگاه و پاسخ به سلیقه کسانی که طعم‌های گوشتی را دوست دارند طراحی شده است.", 'ghahghah' ),
			'flavour'       => __( 'مرغ', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم مرغ', 'شور', 'میان‌وعده' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
		'corn-pellet-pepper'         => array(
			'display_title' => __( 'اسنک ذرت طعم فلفلی', 'ghahghah' ),
			'subtitle'      => __( 'تند و هیجان‌انگیز برای هواداران طعم فلفل', 'ghahghah' ),
			'short_intro'   => __( 'اسنک ذرت قهقهه با طعم فلفلی، حرارت ملایم فلفل را روی بافت ترد ذرت می‌نشاند؛ انتخابی جسور برای کسانی که طعم تند را می‌پسندند.', 'ghahghah' ),
			'content'       => __( "طعم فلفلی قهقهه با تعادل دقیق تندی و ادویه، میان‌وعده‌ای پرانرژی در سبد محصولات برند است.\nاین محصول برای کامل‌کردن تنوع طعم‌ها در فروش عمده و خرده‌فروشی گزینه‌ای جذاب به‌شمار می‌رود.", 'ghahghah' ),
			'flavour'       => __( 'فلفلی', 'ghahghah' ),
			'labels'        => array( 'اسنک ذرت', 'طعم فلفلی', 'تند', 'میان‌وعده' ),
			'weight'        => $shared['weight'],
			'product_type'  => $shared['product_type'],
			'package_type'  => $shared['package_type'],
			'shelf_life'    => $shared['shelf_life'],
			'storage'       => $shared['storage'],
			'motto'         => $shared['motto'],
		),
	);
}

/**
 * Feature strip items (shared UI).
 *
 * @return array<int, array{icon: string, title: string, text: string}>
 */
function ghahghah_single_product_features(): array {
	return array(
		array(
			'icon'  => 'crown',
			'title' => __( 'کیفیت بالا', 'ghahghah' ),
			'text'  => __( 'استاندارد و مطمئن', 'ghahghah' ),
		),
		array(
			'icon'  => 'leaf',
			'title' => __( 'مواد اولیه منتخب', 'ghahghah' ),
			'text'  => __( 'ذرت مرغوب و طبیعی', 'ghahghah' ),
		),
		array(
			'icon'  => 'box-outline',
			'title' => __( 'بسته‌بندی استاندارد', 'ghahghah' ),
			'text'  => __( 'حفظ تازگی و کیفیت', 'ghahghah' ),
		),
		array(
			'icon'  => 'heart',
			'title' => __( 'مناسب همه سلیقه‌ها', 'ghahghah' ),
			'text'  => __( 'میان‌وعده‌ای برای همه', 'ghahghah' ),
		),
	);
}

/**
 * Register product label taxonomy.
 */
function ghahghah_register_product_label_taxonomy(): void {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return;
	}

	register_taxonomy(
		'ghahghah_product_label',
		'ghahghah_product',
		array(
			'labels'            => array(
				'name'          => __( 'برچسب‌های محصول', 'ghahghah' ),
				'singular_name' => __( 'برچسب محصول', 'ghahghah' ),
				'search_items'  => __( 'جستجوی برچسب', 'ghahghah' ),
				'all_items'     => __( 'همه برچسب‌ها', 'ghahghah' ),
				'edit_item'     => __( 'ویرایش برچسب', 'ghahghah' ),
				'update_item'   => __( 'به‌روزرسانی برچسب', 'ghahghah' ),
				'add_new_item'  => __( 'افزودن برچسب', 'ghahghah' ),
				'new_item_name' => __( 'نام برچسب جدید', 'ghahghah' ),
				'menu_name'     => __( 'برچسب‌ها', 'ghahghah' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'rewrite'           => array(
				'slug' => 'product-label',
			),
		)
	);
}
add_action( 'init', 'ghahghah_register_product_label_taxonomy', 21 );

/**
 * Catalog key for a product.
 */
function ghahghah_get_product_catalog_key( int $product_id ): string {
	$key = get_post_meta( $product_id, '_ghahghah_catalog_key', true );
	return is_string( $key ) ? $key : '';
}

/**
 * Catalog defaults for a product (by key or empty).
 *
 * @return array<string, mixed>
 */
function ghahghah_get_product_catalog_defaults( int $product_id ): array {
	$key      = ghahghah_get_product_catalog_key( $product_id );
	$catalog  = ghahghah_product_content_catalog();
	$shared   = ghahghah_product_shared_defaults();
	$fallback = array(
		'display_title' => '',
		'subtitle'      => __( 'طعمی لذیذ برای لحظات شاد', 'ghahghah' ),
		'short_intro'   => '',
		'content'       => '',
		'flavour'       => '',
		'labels'        => array( 'اسنک ذرت' ),
		'weight'        => $shared['weight'],
		'product_type'  => $shared['product_type'],
		'package_type'  => $shared['package_type'],
		'shelf_life'    => $shared['shelf_life'],
		'storage'       => $shared['storage'],
		'motto'         => $shared['motto'],
	);

	if ( '' !== $key && isset( $catalog[ $key ] ) && is_array( $catalog[ $key ] ) ) {
		return array_merge( $fallback, $catalog[ $key ] );
	}

	return $fallback;
}

/**
 * Read a product meta string with catalog fallback.
 */
function ghahghah_get_product_field( int $product_id, string $meta_key, string $catalog_key ): string {
	$meta = get_post_meta( $product_id, $meta_key, true );
	if ( is_string( $meta ) && '' !== trim( $meta ) ) {
		return trim( $meta );
	}
	$defaults = ghahghah_get_product_catalog_defaults( $product_id );
	$value    = $defaults[ $catalog_key ] ?? '';
	return is_string( $value ) ? $value : '';
}

/**
 * Resolved product data for the single template.
 *
 * @return array<string, mixed>
 */
function ghahghah_get_single_product_data( WP_Post $product ): array {
	$id       = (int) $product->ID;
	$defaults = ghahghah_get_product_catalog_defaults( $id );
	$nav      = function_exists( 'ghahghah_product_nav_label' ) ? ghahghah_product_nav_label( $product ) : get_the_title( $product );

	$display = ghahghah_get_product_field( $id, '_ghahghah_display_title', 'display_title' );
	if ( '' === $display ) {
		$display = is_string( $nav ) && '' !== $nav ? $nav : get_the_title( $product );
	}

	$short = ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_SHORT_INTRO, 'short_intro' );
	if ( '' === $short ) {
		$excerpt = get_the_excerpt( $product );
		$short   = is_string( $excerpt ) ? trim( wp_strip_all_tags( $excerpt ) ) : '';
	}

	$content = trim( (string) $product->post_content );
	if ( '' === $content && ! empty( $defaults['content'] ) && is_string( $defaults['content'] ) ) {
		$content = $defaults['content'];
	}

	$labels = array();
	$terms  = get_the_terms( $id, 'ghahghah_product_label' );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( $term instanceof WP_Term ) {
				$labels[] = $term->name;
			}
		}
	}
	if ( ! $labels && ! empty( $defaults['labels'] ) && is_array( $defaults['labels'] ) ) {
		foreach ( $defaults['labels'] as $label ) {
			if ( is_string( $label ) && '' !== $label ) {
				$labels[] = $label;
			}
		}
	}

	return array(
		'id'            => $id,
		'display_title' => $display,
		'nav_label'     => is_string( $nav ) ? $nav : '',
		'subtitle'      => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_SUBTITLE, 'subtitle' ),
		'short_intro'   => $short,
		'content'       => $content,
		'weight'        => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_WEIGHT, 'weight' ),
		'flavour'       => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_FLAVOUR, 'flavour' ),
		'product_type'  => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_PRODUCT_TYPE, 'product_type' ),
		'package_type'  => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_PACKAGE, 'package_type' ),
		'shelf_life'    => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_SHELF, 'shelf_life' ),
		'storage'       => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_STORAGE, 'storage' ),
		'motto'         => ghahghah_get_product_field( $id, GHAHGHAH_PRODUCT_META_MOTTO, 'motto' ),
		'slogan'        => (string) ghahghah_product_shared_defaults()['slogan'],
		'labels'        => $labels,
		'permalink'     => get_permalink( $product ),
	);
}

/**
 * Spec rows for quick list / details table.
 *
 * @param array<string, mixed> $data Product data.
 * @return array<int, array{icon: string, label: string, value: string}>
 */
function ghahghah_single_product_spec_rows( array $data ): array {
	$map = array(
		array( 'icon' => 'weight', 'label' => __( 'وزن خالص', 'ghahghah' ), 'key' => 'weight' ),
		array( 'icon' => 'product-type', 'label' => __( 'نوع محصول', 'ghahghah' ), 'key' => 'product_type' ),
		array( 'icon' => 'package', 'label' => __( 'بسته‌بندی', 'ghahghah' ), 'key' => 'package_type' ),
		array( 'icon' => 'flavour', 'label' => __( 'طعم', 'ghahghah' ), 'key' => 'flavour' ),
		array( 'icon' => 'clipboard', 'label' => __( 'ماندگاری', 'ghahghah' ), 'key' => 'shelf_life' ),
		array( 'icon' => 'box-outline', 'label' => __( 'شرایط نگهداری', 'ghahghah' ), 'key' => 'storage' ),
	);

	$rows = array();
	foreach ( $map as $row ) {
		$value = isset( $data[ $row['key'] ] ) ? trim( (string) $data[ $row['key'] ] ) : '';
		if ( '' === $value ) {
			continue;
		}
		$rows[] = array(
			'icon'  => $row['icon'],
			'label' => $row['label'],
			'value' => $value,
		);
	}
	return $rows;
}

/**
 * Gallery attachment IDs: featured first, then attached images (published media only).
 *
 * @return array<int, int>
 */
function ghahghah_get_product_gallery_ids( int $product_id ): array {
	$ids = array();
	$thumb = (int) get_post_thumbnail_id( $product_id );
	if ( $thumb > 0 ) {
		$ids[] = $thumb;
	}

	$attached = get_children(
		array(
			'post_parent'    => $product_id,
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => 8,
			'orderby'        => 'menu_order ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	if ( is_array( $attached ) ) {
		foreach ( $attached as $aid ) {
			$aid = (int) $aid;
			if ( $aid > 0 && ! in_array( $aid, $ids, true ) ) {
				$ids[] = $aid;
			}
		}
	}

	/**
	 * Filter product gallery attachment IDs.
	 *
	 * @param array<int, int> $ids Gallery IDs.
	 * @param int             $product_id Product ID.
	 */
	$ids = apply_filters( 'ghahghah_product_gallery_ids', $ids, $product_id );

	return array_values( array_unique( array_map( 'absint', $ids ) ) );
}

/**
 * Related published products (exclude current).
 *
 * @return array<int, WP_Post>
 */
function ghahghah_get_related_products( int $product_id, int $limit = 5 ): array {
	$query = new WP_Query(
		array(
			'post_type'              => 'ghahghah_product',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'post__not_in'           => array( $product_id ),
			'orderby'                => 'rand',
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	return $query->posts;
}

/**
 * Icon path helper.
 */
function ghahghah_get_single_product_icon_path( string $name ): string {
	$key = preg_replace( '/[^a-z0-9\-]/', '', strtolower( $name ) );
	if ( ! is_string( $key ) || '' === $key ) {
		return '';
	}
	$path = GHAHGHAH_THEME_DIR . '/assets/icons/single-product/' . $key . '.svg';
	return is_readable( $path ) ? $path : '';
}

/**
 * Echo single-product SVG icon.
 *
 * @param string               $name Icon basename.
 * @param array<string, mixed> $args Args.
 */
function ghahghah_the_single_product_icon( string $name, array $args = array() ): void {
	$path = ghahghah_get_single_product_icon_path( $name );
	if ( '' === $path ) {
		return;
	}
	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}
	$class = 'ghahghah-sp-icon';
	if ( ! empty( $args['class'] ) && is_string( $args['class'] ) ) {
		$class .= ' ' . $args['class'];
	}
	$svg = preg_replace( '/<svg\b/i', '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"', $svg, 1 );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Seed / refresh product meta, content, excerpt, and labels from catalog (idempotent fill).
 *
 * @param bool $force Overwrite existing fields.
 * @return int Number of products updated.
 */
function ghahghah_seed_product_single_content( bool $force = false ): int {
	if ( ! post_type_exists( 'ghahghah_product' ) ) {
		return 0;
	}

	$products = get_posts(
		array(
			'post_type'      => 'ghahghah_product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);

	$updated = 0;
	foreach ( $products as $product ) {
		if ( ! $product instanceof WP_Post ) {
			continue;
		}
		$id       = (int) $product->ID;
		$defaults = ghahghah_get_product_catalog_defaults( $id );
		$key      = ghahghah_get_product_catalog_key( $id );
		if ( '' === $key || empty( $defaults['flavour'] ) ) {
			continue;
		}

		$meta_map = array(
			GHAHGHAH_PRODUCT_META_WEIGHT       => 'weight',
			GHAHGHAH_PRODUCT_META_FLAVOUR      => 'flavour',
			GHAHGHAH_PRODUCT_META_PRODUCT_TYPE => 'product_type',
			GHAHGHAH_PRODUCT_META_PACKAGE      => 'package_type',
			GHAHGHAH_PRODUCT_META_SHELF        => 'shelf_life',
			GHAHGHAH_PRODUCT_META_STORAGE      => 'storage',
			GHAHGHAH_PRODUCT_META_SUBTITLE     => 'subtitle',
			GHAHGHAH_PRODUCT_META_SHORT_INTRO  => 'short_intro',
			GHAHGHAH_PRODUCT_META_MOTTO        => 'motto',
			'_ghahghah_display_title'          => 'display_title',
		);

		foreach ( $meta_map as $meta_key => $catalog_key ) {
			$current = get_post_meta( $id, $meta_key, true );
			$value   = isset( $defaults[ $catalog_key ] ) ? (string) $defaults[ $catalog_key ] : '';
			if ( '' === $value ) {
				continue;
			}
			if ( $force || ! is_string( $current ) || '' === trim( $current ) ) {
				update_post_meta( $id, $meta_key, $value );
			}
		}

		$post_update = array( 'ID' => $id );
		$changed     = false;

		if ( ( $force || '' === trim( (string) $product->post_excerpt ) ) && ! empty( $defaults['short_intro'] ) ) {
			$post_update['post_excerpt'] = (string) $defaults['short_intro'];
			$changed                     = true;
		}
		if ( ( $force || '' === trim( (string) $product->post_content ) ) && ! empty( $defaults['content'] ) ) {
			$post_update['post_content'] = (string) $defaults['content'];
			$changed                     = true;
		}
		if ( $changed ) {
			wp_update_post( $post_update );
		}

		if ( ! empty( $defaults['labels'] ) && is_array( $defaults['labels'] ) && taxonomy_exists( 'ghahghah_product_label' ) ) {
			$existing = wp_get_object_terms( $id, 'ghahghah_product_label', array( 'fields' => 'names' ) );
			if ( $force || empty( $existing ) || is_wp_error( $existing ) ) {
				wp_set_object_terms( $id, array_values( $defaults['labels'] ), 'ghahghah_product_label', false );
			}
		}

		++$updated;
	}

	return $updated;
}

/**
 * Run seed once after theme switch / on admin init if flag missing.
 */
function ghahghah_maybe_seed_product_single_content(): void {
	if ( get_option( 'ghahghah_product_single_seeded_v1' ) ) {
		return;
	}
	$count = ghahghah_seed_product_single_content( false );
	if ( $count > 0 ) {
		update_option( 'ghahghah_product_single_seeded_v1', 1, false );
	}
}
add_action( 'init', 'ghahghah_maybe_seed_product_single_content', 40 );

/**
 * Admin meta box for product specs.
 */
function ghahghah_register_product_meta_box(): void {
	add_meta_box(
		'ghahghah_product_specs',
		__( 'مشخصات محصول قهقهه', 'ghahghah' ),
		'ghahghah_render_product_meta_box',
		'ghahghah_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ghahghah_register_product_meta_box' );

/**
 * Render product specs meta box.
 *
 * @param WP_Post $post Post.
 */
function ghahghah_render_product_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'ghahghah_save_product_specs', 'ghahghah_product_specs_nonce' );
	$fields = array(
		'_ghahghah_display_title'          => __( 'عنوان نمایشی', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_SUBTITLE     => __( 'زیرعنوان', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_SHORT_INTRO  => __( 'معرفی کوتاه', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_WEIGHT       => __( 'وزن خالص', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_FLAVOUR      => __( 'طعم', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_PRODUCT_TYPE => __( 'نوع محصول', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_PACKAGE      => __( 'بسته‌بندی', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_SHELF        => __( 'ماندگاری', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_STORAGE      => __( 'شرایط نگهداری', 'ghahghah' ),
		GHAHGHAH_PRODUCT_META_MOTTO        => __( 'جمله تزئینی', 'ghahghah' ),
	);
	$defaults = ghahghah_get_product_catalog_defaults( (int) $post->ID );
	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $key => $label ) {
		$catalog_key = str_replace( array( '_ghahghah_', 'display_title' ), array( '', 'display_title' ), $key );
		$catalog_key = preg_replace( '/^_ghahghah_/', '', $key ) ?? $key;
		$map         = array(
			'_ghahghah_display_title'          => 'display_title',
			GHAHGHAH_PRODUCT_META_SUBTITLE     => 'subtitle',
			GHAHGHAH_PRODUCT_META_SHORT_INTRO  => 'short_intro',
			GHAHGHAH_PRODUCT_META_WEIGHT       => 'weight',
			GHAHGHAH_PRODUCT_META_FLAVOUR      => 'flavour',
			GHAHGHAH_PRODUCT_META_PRODUCT_TYPE => 'product_type',
			GHAHGHAH_PRODUCT_META_PACKAGE      => 'package_type',
			GHAHGHAH_PRODUCT_META_SHELF        => 'shelf_life',
			GHAHGHAH_PRODUCT_META_STORAGE      => 'storage',
			GHAHGHAH_PRODUCT_META_MOTTO        => 'motto',
		);
		$ck      = $map[ $key ] ?? '';
		$current = get_post_meta( $post->ID, $key, true );
		$value   = is_string( $current ) && '' !== $current ? $current : (string) ( $defaults[ $ck ] ?? '' );
		$is_area = in_array( $key, array( GHAHGHAH_PRODUCT_META_SHORT_INTRO, GHAHGHAH_PRODUCT_META_STORAGE ), true );
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( $is_area ) {
			echo '<textarea class="large-text" rows="3" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $value ) . '</textarea>';
		} else {
			echo '<input type="text" class="regular-text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'برچسب‌ها را از باکس «برچسب‌های محصول» تنظیم کنید. تصویر شاخص همان تصویر منتشرشده محصول است.', 'ghahghah' ) . '</p>';
}

/**
 * Save product specs meta box.
 *
 * @param int $post_id Post ID.
 */
function ghahghah_save_product_specs_meta( int $post_id ): void {
	if ( ! isset( $_POST['ghahghah_product_specs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['ghahghah_product_specs_nonce'] ) ), 'ghahghah_save_product_specs' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'ghahghah_product' !== get_post_type( $post_id ) ) {
		return;
	}

	$keys = array(
		'_ghahghah_display_title',
		GHAHGHAH_PRODUCT_META_SUBTITLE,
		GHAHGHAH_PRODUCT_META_SHORT_INTRO,
		GHAHGHAH_PRODUCT_META_WEIGHT,
		GHAHGHAH_PRODUCT_META_FLAVOUR,
		GHAHGHAH_PRODUCT_META_PRODUCT_TYPE,
		GHAHGHAH_PRODUCT_META_PACKAGE,
		GHAHGHAH_PRODUCT_META_SHELF,
		GHAHGHAH_PRODUCT_META_STORAGE,
		GHAHGHAH_PRODUCT_META_MOTTO,
	);

	foreach ( $keys as $key ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( (string) $_POST[ $key ] );
		if ( GHAHGHAH_PRODUCT_META_SHORT_INTRO === $key || GHAHGHAH_PRODUCT_META_STORAGE === $key ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( $raw ) );
		} else {
			update_post_meta( $post_id, $key, sanitize_text_field( $raw ) );
		}
	}
}
add_action( 'save_post_ghahghah_product', 'ghahghah_save_product_specs_meta' );
