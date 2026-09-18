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

$blog_keys  = ghahghah_blog_archive_banner_mod_keys();
$prod_keys  = ghahghah_products_archive_banner_mod_keys();
$last_saved = absint( get_theme_mod( 'ghahghah_archive_banners_last_saved', 0 ) );

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

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر آرشیو مقالات', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc">
						<?php esc_html_e( 'در صورت خالی‌بودن، بنر پیش‌فرض قالب نمایش داده می‌شود.', 'ghahghah' ); ?>
					</p>
				</div>
			</header>

			<div class="ghahghah-media-grid ghahghah-media-grid--2">
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
			</div>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنر آرشیو محصولات', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc">
						<?php esc_html_e( 'در صورت خالی‌بودن، بنر پیش‌فرض قالب نمایش داده می‌شود.', 'ghahghah' ); ?>
					</p>
				</div>
			</header>

			<div class="ghahghah-media-grid ghahghah-media-grid--2">
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
			</div>
		</section>
	</div>

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
