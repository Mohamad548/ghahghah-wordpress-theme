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

$d              = ghahghah_hero_setting_defaults();
$enabled        = (bool) get_theme_mod( 'ghahghah_hero_enabled', $d['ghahghah_hero_enabled'] );
$interval       = ghahghah_sanitize_hero_interval( get_theme_mod( 'ghahghah_hero_interval', $d['ghahghah_hero_interval'] ) );
$slides         = ghahghah_sanitize_hero_slides( get_theme_mod( 'ghahghah_hero_slides', $d['ghahghah_hero_slides'] ) );
$bundled        = ghahghah_hero_bundled_banners();
$last_saved     = absint( get_theme_mod( 'ghahghah_hero_last_saved', 0 ) );
$max_slides     = (int) GHAHGHAH_HERO_SLIDES_MAX;

if ( array() === $slides ) {
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
	$link       = ghahghah_format_hero_slide_link_for_admin( (string) ( $slide['link'] ?? '' ) );
	$alt        = (string) ( $slide['alt'] ?? '' );
	$public     = function_exists( 'ghahghah_get_public_site_url' ) ? ghahghah_get_public_site_url() : 'https://ghahghaheh.com';
	?>
	<article class="ghahghah-slider-row" data-ghahghah-slider-row>
		<div class="ghahghah-slider-row__head">
			<span class="ghahghah-slider-row__badge" data-ghahghah-slider-badge><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
			<div class="ghahghah-slider-row__tools">
				<button type="button" class="ghahghah-btn ghahghah-btn--ghost ghahghah-btn--sm" data-ghahghah-slider-up>
					<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 14l6-6 6 6"/></svg>
					<?php esc_html_e( 'بالا', 'ghahghah' ); ?>
				</button>
				<button type="button" class="ghahghah-btn ghahghah-btn--ghost ghahghah-btn--sm" data-ghahghah-slider-down>
					<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 10l6 6 6-6"/></svg>
					<?php esc_html_e( 'پایین', 'ghahghah' ); ?>
				</button>
				<button type="button" class="ghahghah-btn ghahghah-btn--danger ghahghah-btn--sm" data-ghahghah-slider-remove>
					<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M9 7V5h6v2M8 7l1 12h6l1-12"/></svg>
					<?php esc_html_e( 'حذف', 'ghahghah' ); ?>
				</button>
			</div>
		</div>

		<div class="ghahghah-hero-admin__media-grid">
			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_hero_slide_desktop[]',
				__( 'بنر دسکتاپ', 'ghahghah' ),
				__( 'پیشنهاد: ۱۹۱۶×۸۲۱ · اگر خالی باشد از موبایل یا پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
				$desktop_id,
				$default_desktop
			);

			ghahghah_admin_render_media_field(
				'ghahghah_hero_slide_mobile[]',
				__( 'بنر موبایل', 'ghahghah' ),
				__( 'بنر مخصوص موبایل · اگر خالی باشد از دسکتاپ یا پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
				$mobile_id,
				$default_mobile
			);
			?>
		</div>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'لینک اکشن (اختیاری)', 'ghahghah' ); ?></span>
				<input
					type="url"
					name="ghahghah_hero_slide_link[]"
					value="<?php echo esc_attr( $link ); ?>"
					placeholder="<?php echo esc_attr( $public . '/products/' ); ?>"
					dir="ltr"
				/>
				<span class="ghahghah-field__help"><?php esc_html_e( 'با کلیک روی بنر به این آدرس می‌رود. خالی = بدون لینک. دامنهٔ پیش‌فرض: ghahghaheh.com', 'ghahghah' ); ?></span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن جایگزین تصویر (اختیاری)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_hero_slide_alt[]" value="<?php echo esc_attr( $alt ); ?>" />
				<span class="ghahghah-field__help"><?php esc_html_e( 'برای دسترس‌پذیری و سئو تصویر.', 'ghahghah' ); ?></span>
			</label>
		</div>
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
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 3v2M12 19v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M3 12h2M19 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تنظیمات نمایش اسلایدر', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نمایش در صفحه اصلی و زمان‌بندی تعویض بنرها.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
			<div class="ghahghah-panel-section ghahghah-panel-section--nested">
				<label class="ghahghah-switch">
					<input type="checkbox" name="ghahghah_hero_enabled" value="1" <?php checked( $enabled ); ?> />
					<span class="ghahghah-switch__ui" aria-hidden="true"></span>
					<span class="ghahghah-switch__label">
						<strong><?php esc_html_e( 'نمایش اسلایدر در صفحه اصلی', 'ghahghah' ); ?></strong>
						<small><?php esc_html_e( 'در صورت خاموش بودن، بخش اسلایدر در خانه نمایش داده نمی‌شود.', 'ghahghah' ); ?></small>
					</span>
				</label>
			</div>

			<div class="ghahghah-panel-section ghahghah-panel-section--nested">
				<label class="ghahghah-field ghahghah-field--suffix">
					<span class="ghahghah-field__label"><?php esc_html_e( 'مدت هر اسلاید', 'ghahghah' ); ?></span>
					<span class="ghahghah-field__control">
						<input
							type="number"
							name="ghahghah_hero_interval"
							value="<?php echo esc_attr( (string) $interval ); ?>"
							min="<?php echo esc_attr( (string) GHAHGHAH_HERO_INTERVAL_MIN ); ?>"
							max="<?php echo esc_attr( (string) GHAHGHAH_HERO_INTERVAL_MAX ); ?>"
							step="1"
						/>
						<span class="ghahghah-field__suffix" aria-hidden="true"><?php esc_html_e( 'ثانیه', 'ghahghah' ); ?></span>
					</span>
					<span class="ghahghah-field__help">
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: min seconds, 2: max seconds */
								__( 'بین %1$d تا %2$d ثانیه', 'ghahghah' ),
								(int) GHAHGHAH_HERO_INTERVAL_MIN,
								(int) GHAHGHAH_HERO_INTERVAL_MAX
							)
						);
						?>
					</span>
				</label>
			</div>
		</div>
	</section>

	<section class="ghahghah-panel-section" data-ghahghah-slider-admin>
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/><circle cx="9" cy="9" r="1.2"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بنرها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'ترتیب، تصویر دسکتاپ/موبایل، لینک اکشن و متن جایگزین را مدیریت کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php if ( array() !== $bundled ) : ?>
			<details class="ghahghah-config-card ghahghah-hero-admin__defaults-wrap">
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

		<div class="ghahghah-slider-list" data-ghahghah-slider-list>
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

		<div class="ghahghah-slider-admin__actions">
			<button type="button" class="ghahghah-btn ghahghah-btn--outline" data-ghahghah-slider-add>
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
				<?php esc_html_e( 'افزودن بنر', 'ghahghah' ); ?>
			</button>
			<span class="ghahghah-field__help">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: max slides */
						__( 'حداکثر %d بنر قابل ذخیره است. اگر همه را خالی ذخیره کنید، پیش‌فرض قالب نمایش داده می‌شود.', 'ghahghah' ),
						$max_slides
					)
				);
				?>
			</span>
		</div>

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
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره اسلایدر', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: 1: max slides, 2: last saved label */
				esc_html__( 'حداکثر %1$d بنر · آخرین ذخیره: %2$s', 'ghahghah' ),
				$max_slides,
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
