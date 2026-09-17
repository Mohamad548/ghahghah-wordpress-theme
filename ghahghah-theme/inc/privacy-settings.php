<?php
/**
 * Privacy policy page helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme mod defaults for the privacy page chrome (lead text).
 *
 * @return array<string, string>
 */
function ghahghah_privacy_page_defaults(): array {
	return array(
		'ghahghah_privacy_page_lead' => __( 'در این صفحه توضیح می‌دهیم چه داده‌هایی از طریق وب‌سایت قهقهه جمع‌آوری می‌شود و چگونه از آن‌ها استفاده می‌کنیم.', 'ghahghah' ),
	);
}

/**
 * Get a privacy-page theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_privacy_page_mod( string $key ) {
	$defaults = ghahghah_privacy_page_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Whether the current view is the privacy policy page.
 */
function ghahghah_is_privacy_page(): bool {
	if ( is_page_template( 'page-templates/privacy.php' ) ) {
		return true;
	}

	return function_exists( 'is_privacy_policy' ) && is_privacy_policy();
}

/**
 * Force the branded privacy template for WordPress privacy-policy pages.
 *
 * @param string $template Absolute path to the template.
 */
function ghahghah_privacy_template_include( string $template ): string {
	if ( ! function_exists( 'is_privacy_policy' ) || ! is_privacy_policy() ) {
		return $template;
	}

	if ( is_page_template( 'page-templates/privacy.php' ) ) {
		return $template;
	}

	$custom = locate_template( 'page-templates/privacy.php' );
	return is_string( $custom ) && '' !== $custom ? $custom : $template;
}
add_filter( 'template_include', 'ghahghah_privacy_template_include', 20 );

/**
 * Default Persian privacy policy HTML (block markup).
 */
function ghahghah_privacy_default_content(): string {
	$contact = function_exists( 'ghahghah_get_contact_page_url' )
		? (string) ghahghah_get_contact_page_url()
		: home_url( '/contact/' );

	$blocks = array(
		'<!-- wp:paragraph --><p>وب‌سایت قهقهه یک سایت معرفی محصولات و ثبت درخواست همکاری است و فروش آنلاین مستقیم ندارد. این متن توضیح می‌دهد هنگام بازدید از سایت یا ارسال فرم‌ها، چه اطلاعاتی ممکن است دریافت و پردازش شود.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">چه اطلاعاتی جمع‌آوری می‌شود</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>بسته به بخش‌هایی که استفاده می‌کنید، ممکن است موارد زیر دریافت شود:</p><!-- /wp:paragraph -->',
		'<!-- wp:list --><ul class="wp-block-list"><li>اطلاعات تماس و هویتی که خودتان در فرم‌ها وارد می‌کنید؛ مانند نام، شماره تلفن، شهر، نام کسب‌وکار و متن پیام (فرم تماس، خرید عمده و نمایندگی).</li><li>داده‌های فنی حداقلی سرور مانند آدرس IP، نوع مرورگر و زمان درخواست برای امنیت، پایداری و جلوگیری از سوءاستفاده.</li><li>در صورت ورود مدیران سایت به پیشخوان وردپرس، کوکی‌های نشست و تنظیمات نمایش برای حفظ وضعیت ورود.</li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">هدف استفاده از اطلاعات</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>اطلاعات ارسالی برای پاسخ به درخواست‌ها، بررسی همکاری عمده یا نمایندگی، بهبود تجربه کاربری سایت و حفظ امنیت سرویس استفاده می‌شود. از این داده‌ها برای فروش عمومی پروفایل کاربران یا تبلیغات شخص ثالث استفاده نمی‌کنیم.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">پیامک و اطلاع‌رسانی</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>اگر در فرم‌ها شماره موبایل وارد کنید، ممکن است برای تأیید دریافت درخواست یا پیگیری همان درخواست، پیامک اطلاع‌رسانی ارسال شود. این پیامک‌ها جنبه تراکنشی دارند و مربوط به همان درخواست شما هستند.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">نگهداری اطلاعات</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>درخواست‌های ثبت‌شده تا زمان لازم برای پیگیری کسب‌وکار و الزامات قانونی نگهداری می‌شوند و پس از آن در صورت امکان حذف یا ناشناس‌سازی می‌شوند.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">حقوق شما</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>می‌توانید دربارهٔ داده‌هایی که از طریق فرم‌ها برای ما ارسال کرده‌اید سؤال کنید، اصلاح بخواهید یا درخواست حذف بدهید؛ مگر در مواردی که نگهداری اطلاعات به‌دلیل الزامات قانونی یا امنیتی ضروری باشد.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">تماس درباره حریم خصوصی</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>برای پرسش درباره این سیاست، از صفحه <a href="' . esc_url( $contact ) . '">تماس با ما</a> پیام بفرستید.</p><!-- /wp:paragraph -->',
		'<!-- wp:paragraph --><p><em>آخرین به‌روزرسانی: متناسب با نسخه فعلی وب‌سایت قهقهه. این متن جنبه اطلاع‌رسانی دارد و جایگزین مشاوره حقوقی تخصصی نیست.</em></p><!-- /wp:paragraph -->',
	);

	return implode( "\n", $blocks );
}
