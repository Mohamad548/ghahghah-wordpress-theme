<?php
/**
 * Admin panel: SEO title and meta description.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d = ghahghah_seo_setting_defaults();
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_seo" />
	<input type="hidden" name="ghahghah_return_tab" value="seo" />
	<?php wp_nonce_field( 'ghahghah_save_seo', 'ghahghah_seo_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'سئو صفحه اصلی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'عنوان مرورگر، توضیح متا و H1 صفحه اصلی. برای بقیه صفحات، قالب به‌صورت خودکار از متن همان صفحه عنوان و توضیح می‌سازد.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان SEO (تگ title)', 'ghahghah' ); ?></span>
			<input
				type="text"
				name="ghahghah_seo_home_title"
				value="<?php echo esc_attr( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_title' ) ); ?>"
				maxlength="70"
			/>
			<span class="ghahghah-field__hint"><?php esc_html_e( 'پیشنهاد: حداکثر حدود ۶۰ کاراکتر. نام برند را داخل عنوان بگذارید.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح متا (meta description)', 'ghahghah' ); ?></span>
			<textarea
				name="ghahghah_seo_home_description"
				rows="3"
				maxlength="180"
			><?php echo esc_textarea( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_description' ) ); ?></textarea>
			<span class="ghahghah-field__hint"><?php esc_html_e( 'پیشنهاد: ۱۲۰ تا ۱۶۰ کاراکتر؛ محصول، شهر و اقدام کاربر را ذکر کنید.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان H1 صفحه اصلی', 'ghahghah' ); ?></span>
			<input
				type="text"
				name="ghahghah_seo_home_h1"
				value="<?php echo esc_attr( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_h1' ) ); ?>"
				maxlength="90"
			/>
			<span class="ghahghah-field__hint"><?php esc_html_e( 'برای دسترس‌پذیری و سئو؛ به‌صورت بصری مخفی است و هویت برند را در طرح کلی سند نگه می‌دارد.', 'ghahghah' ); ?></span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تصویر اشتراک‌گذاری (Open Graph)', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'اگر خالی باشد، بنر اول اسلایدر یا آیکون سایت استفاده می‌شود. برای صفحات محصول و مقاله، تصویر شاخص همان محتوا اولویت دارد.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>
		<?php
		$og_default = function_exists( 'ghahghah_get_bundled_brand_asset_url' )
			? ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' )
			: '';
		ghahghah_admin_render_media_field(
			'ghahghah_seo_og_image_id',
			__( 'تصویر پیش‌فرض شبکه‌های اجتماعی', 'ghahghah' ),
			__( 'اختیاری؛ اگر خالی باشد از بنر اسلایدر یا آیکون سایت استفاده می‌شود.', 'ghahghah' ),
			absint( ghahghah_get_seo_mod( 'ghahghah_seo_og_image_id' ) ),
			$og_default
		);
		?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پوشش خودکار صفحات', 'ghahghah' ); ?></h3>
			</div>
		</header>
		<ul class="ghahghah-field__hint" style="margin:0;padding-inline-start:1.25rem;line-height:1.7;">
			<li><?php esc_html_e( 'محصولات: عنوان نمایشی + معرفی کوتاه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'مقالات: عنوان نوشته + خلاصه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'کارخانه، تماس، عمده، نمایندگی، FAQ: متن همان صفحه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'آرشیو محصولات و مقالات: عنوان و زیرعنوان بنر', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'اگر افزونه Yoast یا Rank Math فعال باشد، قالب تگ‌ها را به آن‌ها واگذار می‌کند.', 'ghahghah' ); ?></li>
		</ul>
	</section>

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات سئو', 'ghahghah' ); ?></button>
	</p>
</form>
