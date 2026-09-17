<?php
/**
 * Admin panel: archive page banner uploads (articles + products).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_keys = ghahghah_blog_archive_banner_mod_keys();
$prod_keys = ghahghah_products_archive_banner_mod_keys();

$blog_desktop = absint( get_theme_mod( $blog_keys['desktop'], 0 ) );
$blog_mobile  = absint( get_theme_mod( $blog_keys['mobile'], 0 ) );
$prod_desktop = absint( get_theme_mod( $prod_keys['desktop'], 0 ) );
$prod_mobile  = absint( get_theme_mod( $prod_keys['mobile'], 0 ) );

$blog_default = function_exists( 'ghahghah_archive_bundled_banner_url' )
	? ghahghah_archive_bundled_banner_url( 'blog', 'desktop' )
	: GHAHGHAH_THEME_URI . '/assets/images/blog-archive/corn-snack-hero-transparent.webp';
$prod_default = function_exists( 'ghahghah_archive_bundled_banner_url' )
	? ghahghah_archive_bundled_banner_url( 'products', 'desktop' )
	: GHAHGHAH_THEME_URI . '/assets/images/products-archive/corn-hero-transparent.webp';
$blog_mobile_default = function_exists( 'ghahghah_archive_bundled_banner_url' )
	? ghahghah_archive_bundled_banner_url( 'blog', 'mobile' )
	: '';
$prod_mobile_default = function_exists( 'ghahghah_archive_bundled_banner_url' )
	? ghahghah_archive_bundled_banner_url( 'products', 'mobile' )
	: '';
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_archive_banners" />
	<input type="hidden" name="ghahghah_return_tab" value="archive-banners" />
	<?php wp_nonce_field( 'ghahghah_save_archive_banners', 'ghahghah_archive_banners_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر آرشیو مقالات', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'در صورت خالی‌بودن، بنر پیش‌فرض قالب (دسکتاپ و موبایل) نمایش داده می‌شود. با بارگذاری تصویر، جایگزین می‌شود.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>
		<?php
		ghahghah_admin_render_media_field(
			$blog_keys['desktop'],
			__( 'بنر دسکتاپ', 'ghahghah' ),
			__( 'پیشنهاد: عرض حدود ۱۹۰۰px و ارتفاع حدود ۶۰۰–۸۰۰px', 'ghahghah' ),
			$blog_desktop,
			$blog_default
		);
		ghahghah_admin_render_media_field(
			$blog_keys['mobile'],
			__( 'بنر موبایل (اختیاری)', 'ghahghah' ),
			__( 'اگر خالی باشد، همان بنر دسکتاپ در موبایل استفاده می‌شود.', 'ghahghah' ),
			$blog_mobile,
			$blog_mobile_default
		);
		?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر آرشیو محصولات', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'در صورت خالی‌بودن، بنر پیش‌فرض قالب (دسکتاپ و موبایل) نمایش داده می‌شود. با بارگذاری تصویر، جایگزین می‌شود.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>
		<?php
		ghahghah_admin_render_media_field(
			$prod_keys['desktop'],
			__( 'بنر دسکتاپ', 'ghahghah' ),
			__( 'پیشنهاد: عرض حدود ۱۹۰۰px و ارتفاع حدود ۶۰۰–۸۰۰px', 'ghahghah' ),
			$prod_desktop,
			$prod_default
		);
		ghahghah_admin_render_media_field(
			$prod_keys['mobile'],
			__( 'بنر موبایل (اختیاری)', 'ghahghah' ),
			__( 'اگر خالی باشد، همان بنر دسکتاپ در موبایل استفاده می‌شود.', 'ghahghah' ),
			$prod_mobile,
			$prod_mobile_default
		);
		?>
	</section>

	<p>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره بنرهای آرشیو', 'ghahghah' ); ?></button>
	</p>
</form>
