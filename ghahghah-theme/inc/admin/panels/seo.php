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

$last_saved = absint( get_theme_mod( 'ghahghah_seo_last_saved', 0 ) );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_seo" />
	<input type="hidden" name="ghahghah_return_tab" value="seo" />
	<?php wp_nonce_field( 'ghahghah_save_seo', 'ghahghah_seo_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
			</span>
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
			<span class="ghahghah-field__help"><?php esc_html_e( 'پیشنهاد: حداکثر حدود ۶۰ کاراکتر. نام برند را داخل عنوان بگذارید.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح متا (meta description)', 'ghahghah' ); ?></span>
			<textarea
				name="ghahghah_seo_home_description"
				rows="3"
				maxlength="180"
			><?php echo esc_textarea( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_description' ) ); ?></textarea>
			<span class="ghahghah-field__help"><?php esc_html_e( 'پیشنهاد: ۱۲۰ تا ۱۶۰ کاراکتر؛ محصول، شهر و اقدام کاربر را ذکر کنید.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان H1 صفحه اصلی', 'ghahghah' ); ?></span>
			<input
				type="text"
				name="ghahghah_seo_home_h1"
				value="<?php echo esc_attr( (string) ghahghah_get_seo_mod( 'ghahghah_seo_home_h1' ) ); ?>"
				maxlength="90"
			/>
			<span class="ghahghah-field__help"><?php esc_html_e( 'برای دسترس‌پذیری و سئو؛ به‌صورت بصری مخفی است و هویت برند را در طرح کلی سند نگه می‌دارد.', 'ghahghah' ); ?></span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="m21 16-4.5-4.5L9 19"/></svg>
			</span>
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
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پوشش خودکار صفحات', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برای صفحات دیگر، قالب عنوان و توضیح را از محتوای همان صفحه می‌سازد.', 'ghahghah' ); ?></p>
			</div>
		</header>
		<ul class="ghahghah-field__help" style="margin:0;padding-inline-start:1.25rem;line-height:1.7;">
			<li><?php esc_html_e( 'محصولات: عنوان نمایشی + معرفی کوتاه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'مقالات: عنوان نوشته + خلاصه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'کارخانه، تماس، عمده، نمایندگی، FAQ: متن همان صفحه', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'آرشیو محصولات و مقالات: عنوان و زیرعنوان بنر', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'اگر افزونه Yoast یا Rank Math فعال باشد، قالب تگ‌ها را به آن‌ها واگذار می‌کند.', 'ghahghah' ); ?></li>
		</ul>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: last saved label */
				esc_html__( 'آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
