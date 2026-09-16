<?php
/**
 * Homepage banner slider configuration panel (desktop + mobile).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d        = ghahghah_hero_setting_defaults();
$enabled  = (bool) get_theme_mod( 'ghahghah_hero_enabled', $d['ghahghah_hero_enabled'] );
$interval = ghahghah_sanitize_hero_interval( get_theme_mod( 'ghahghah_hero_interval', $d['ghahghah_hero_interval'] ) );
$slides   = ghahghah_sanitize_hero_slides( get_theme_mod( 'ghahghah_hero_slides', $d['ghahghah_hero_slides'] ) );
$bundled  = ghahghah_hero_bundled_banners();

if ( array() === $slides ) {
	// Empty custom slides → show one editable row + bundled previews as defaults.
	$slides = array(
		array(
			'desktop_id' => 0,
			'mobile_id'  => 0,
			'link'       => '',
			'alt'        => '',
		),
	);
}

/**
 * Render one admin slide row.
 *
 * @param array{desktop_id?: int, mobile_id?: int, link?: string, alt?: string} $slide Slide data.
 * @param int                                                                   $index Display index (0-based).
 * @param string                                                                $default_desktop Default desktop preview URL.
 * @param string                                                                $default_mobile  Default mobile preview URL.
 */
$render_row = static function ( array $slide, int $index, string $default_desktop = '', string $default_mobile = '' ): void {
	$desktop_id = absint( $slide['desktop_id'] ?? 0 );
	$mobile_id  = absint( $slide['mobile_id'] ?? 0 );
	$link       = (string) ( $slide['link'] ?? '' );
	$alt        = (string) ( $slide['alt'] ?? '' );
	?>
	<article class="ghahghah-steps-admin__row" data-ghahghah-slider-row>
		<div class="ghahghah-steps-admin__row-head">
			<span class="ghahghah-steps-admin__badge" data-ghahghah-slider-badge><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
			<div class="ghahghah-steps-admin__tools">
				<button type="button" class="button button-secondary" data-ghahghah-slider-up><?php esc_html_e( 'بالا', 'ghahghah' ); ?></button>
				<button type="button" class="button button-secondary" data-ghahghah-slider-down><?php esc_html_e( 'پایین', 'ghahghah' ); ?></button>
				<button type="button" class="button" data-ghahghah-slider-remove><?php esc_html_e( 'حذف', 'ghahghah' ); ?></button>
			</div>
		</div>

		<div class="ghahghah-hero-admin__media-grid">
			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_hero_slide_desktop[]',
				__( 'بنر دسکتاپ', 'ghahghah' ),
				__( 'پیشنهاد: ۱۹۱۶×۸۲۱. اگر خالی باشد از بنر موبایل یا پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
				$desktop_id,
				$default_desktop
			);

			ghahghah_admin_render_media_field(
				'ghahghah_hero_slide_mobile[]',
				__( 'بنر موبایل', 'ghahghah' ),
				__( 'بنر مخصوص موبایل. اگر خالی باشد از دسکتاپ یا پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
				$mobile_id,
				$default_mobile
			);
			?>
		</div>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'لینک اکشن (اختیاری)', 'ghahghah' ); ?></span>
			<input type="url" name="ghahghah_hero_slide_link[]" value="<?php echo esc_attr( $link ); ?>" placeholder="https://..." dir="ltr" />
			<span class="ghahghah-field__help"><?php esc_html_e( 'با کلیک روی بنر به این آدرس می‌رود. خالی = بدون لینک.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین تصویر (اختیاری)', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_hero_slide_alt[]" value="<?php echo esc_attr( $alt ); ?>" />
		</label>
	</article>
	<?php
};
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_hero_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="hero" />
	<?php wp_nonce_field( 'ghahghah_save_hero_settings', 'ghahghah_hero_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/><circle cx="9" cy="9" r="1.2"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نمایش اسلایدر', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برای هر اسلاید دو بنر جدا (دسکتاپ و موبایل) بارگذاری کنید. اگر سفارشی نباشد، پیش‌فرض‌های قالب نمایش داده می‌شوند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="ghahghah_hero_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'نمایش اسلایدر در صفحه اصلی', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'مدت هر اسلاید (ثانیه)', 'ghahghah' ); ?></span>
			<input
				type="number"
				name="ghahghah_hero_interval"
				value="<?php echo esc_attr( (string) $interval ); ?>"
				min="<?php echo esc_attr( (string) GHAHGHAH_HERO_INTERVAL_MIN ); ?>"
				max="<?php echo esc_attr( (string) GHAHGHAH_HERO_INTERVAL_MAX ); ?>"
				step="1"
			/>
		</label>
	</section>

	<section class="ghahghah-panel-section" data-ghahghah-slider-admin>
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 12h8M12 8v8"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنرها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'موبایل: پیش‌فرض‌های قالب از بسته flavor-banners. دسکتاپ: به‌زودی فایل‌های نهایی را جایگزین کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php if ( array() !== $bundled ) : ?>
			<details class="ghahghah-config-card" style="margin-bottom:1rem;">
				<summary><?php esc_html_e( 'پیش‌نمایش پیش‌فرض‌های قالب (موبایل)', 'ghahghah' ); ?></summary>
				<div class="ghahghah-hero-admin__defaults">
					<?php foreach ( $bundled as $b ) : ?>
						<?php
						$mfile = (string) ( $b['mobile'] ?? $b['file'] ?? '' );
						$murl  = '' !== $mfile ? ghahghah_hero_bundled_banner_url( 'mobile', $mfile ) : '';
						$mpath = '' !== $mfile ? ghahghah_hero_bundled_banner_path( 'mobile', $mfile ) : '';
						if ( '' === $mpath || ! is_readable( $mpath ) ) {
							continue;
						}
						?>
						<figure class="ghahghah-hero-admin__default-card">
							<img src="<?php echo esc_url( $murl ); ?>" alt="" loading="lazy" />
							<figcaption><?php echo esc_html( (string) $b['alt'] ); ?></figcaption>
						</figure>
					<?php endforeach; ?>
				</div>
			</details>
		<?php endif; ?>

		<div class="ghahghah-steps-admin__list" data-ghahghah-slider-list>
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$bmobile  = isset( $bundled[ $index ]['mobile'] ) ? (string) $bundled[ $index ]['mobile'] : (string) ( $bundled[ $index ]['file'] ?? '' );
				$bdesktop = isset( $bundled[ $index ]['desktop'] ) ? (string) $bundled[ $index ]['desktop'] : $bmobile;
				$def_m    = ( '' !== $bmobile && is_readable( ghahghah_hero_bundled_banner_path( 'mobile', $bmobile ) ) )
					? ghahghah_hero_bundled_banner_url( 'mobile', $bmobile )
					: '';
				$def_d    = ( '' !== $bdesktop && is_readable( ghahghah_hero_bundled_banner_path( 'desktop', $bdesktop ) ) )
					? ghahghah_hero_bundled_banner_url( 'desktop', $bdesktop )
					: $def_m;
				$render_row( $slide, (int) $index, $def_d, $def_m );
				?>
			<?php endforeach; ?>
		</div>

		<p class="ghahghah-steps-admin__actions">
			<button type="button" class="button button-secondary" data-ghahghah-slider-add>
				<?php esc_html_e( 'افزودن بنر', 'ghahghah' ); ?>
			</button>
			<span class="ghahghah-field__help">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: max slides */
						__( 'حداکثر %d بنر. اگر همه را خالی ذخیره کنید، پیش‌فرض قالب نمایش داده می‌شود.', 'ghahghah' ),
						GHAHGHAH_HERO_SLIDES_MAX
					)
				);
				?>
			</span>
		</p>

		<template data-ghahghah-slider-template>
			<?php
			$render_row(
				array(
					'desktop_id' => 0,
					'mobile_id'  => 0,
					'link'       => '',
					'alt'        => '',
				),
				0,
				'',
				''
			);
			?>
		</template>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره اسلایدر', 'ghahghah' ); ?>
		</button>
	</div>
</form>
