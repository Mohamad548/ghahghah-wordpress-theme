<?php
/**
 * Products archive hero.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = ghahghah_products_archive_defaults();
$banner   = ghahghah_get_products_archive_custom_banner();
$corn_url = GHAHGHAH_THEME_URI . '/assets/images/products-archive/corn-hero-transparent.webp';

$title_html = esc_html( $defaults['hero_title'] );
$title_html = preg_replace(
	'/(قهقهه)/u',
	'<span class="ghahghah-products-archive__hero-brand">$1</span>',
	$title_html,
	1
) ?? $title_html;
?>
<section
	class="ghahghah-products-archive__hero<?php echo null !== $banner ? ' ghahghah-products-archive__hero--custom' : ''; ?>"
	aria-labelledby="ghahghah-pa-hero-title"
>
	<?php if ( null !== $banner ) : ?>
		<div class="ghahghah-products-archive__hero-stage ghahghah-products-archive__hero-stage--banner">
			<picture class="ghahghah-products-archive__hero-picture">
				<?php if ( null !== $banner['mobile'] ) : ?>
					<source
						media="(max-width: 47.99rem)"
						srcset="<?php echo esc_url( $banner['mobile']['src'] ); ?>"
					/>
				<?php endif; ?>
				<img
					class="ghahghah-products-archive__hero-banner"
					src="<?php echo esc_url( $banner['desktop']['src'] ); ?>"
					<?php if ( '' !== $banner['desktop']['srcset'] ) : ?>
						srcset="<?php echo esc_attr( $banner['desktop']['srcset'] ); ?>"
					<?php endif; ?>
					alt="<?php echo esc_attr( $banner['desktop']['alt'] ); ?>"
					width="<?php echo esc_attr( (string) $banner['desktop']['width'] ); ?>"
					height="<?php echo esc_attr( (string) $banner['desktop']['height'] ); ?>"
					decoding="async"
					fetchpriority="high"
				/>
			</picture>
			<div class="ghahghah-products-archive__hero-banner-copy">
				<h1 id="ghahghah-pa-hero-title" class="screen-reader-text">
					<?php echo esc_html( $defaults['hero_title'] ); ?>
				</h1>
			</div>
		</div>
	<?php else : ?>
		<div class="ghahghah-products-archive__hero-bg" aria-hidden="true"></div>
		<div class="ghahghah-products-archive__hero-inner">
			<div class="ghahghah-products-archive__hero-copy">
				<h1 id="ghahghah-pa-hero-title" class="ghahghah-products-archive__hero-title">
					<?php echo wp_kses( $title_html, array( 'span' => array( 'class' => true ) ) ); ?>
				</h1>
				<p class="ghahghah-products-archive__hero-subtitle"><?php echo esc_html( $defaults['hero_subtitle'] ); ?></p>
			</div>

			<p class="ghahghah-products-archive__hero-tagline" aria-hidden="true">
				<?php echo esc_html( $defaults['hero_tagline'] ); ?>
			</p>

			<div class="ghahghah-products-archive__hero-media">
				<img
					class="ghahghah-products-archive__hero-corn"
					src="<?php echo esc_url( $corn_url ); ?>"
					alt=""
					width="520"
					height="420"
					decoding="async"
					fetchpriority="high"
				/>
				<span class="ghahghah-products-archive__hero-chip ghahghah-products-archive__hero-chip--a" aria-hidden="true"></span>
				<span class="ghahghah-products-archive__hero-chip ghahghah-products-archive__hero-chip--b" aria-hidden="true"></span>
				<span class="ghahghah-products-archive__hero-chip ghahghah-products-archive__hero-chip--c" aria-hidden="true"></span>
			</div>
		</div>
	<?php endif; ?>
</section>
