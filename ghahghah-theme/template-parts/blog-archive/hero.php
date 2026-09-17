<?php
/**
 * Blog archive hero.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = ghahghah_blog_archive_defaults();
$banner   = ghahghah_get_blog_archive_custom_banner();
$hero_url = GHAHGHAH_THEME_URI . '/assets/images/blog-archive/corn-snack-hero-transparent.png';
$bowl_url = GHAHGHAH_THEME_URI . '/assets/images/blog-archive/real-snack-bowl-photo.jpg';
$chip_url = GHAHGHAH_THEME_URI . '/assets/images/blog-archive/real-snack-shape-photo.jpg';

$title_html = esc_html( $defaults['hero_title'] );
$title_html = preg_replace(
	'/(قهقهه)/u',
	'<span class="ghahghah-blog-archive__hero-brand">$1</span>',
	$title_html,
	1
) ?? $title_html;
?>
<section
	class="ghahghah-blog-archive__hero<?php echo null !== $banner ? ' ghahghah-blog-archive__hero--custom' : ''; ?>"
	aria-labelledby="ghahghah-ba-hero-title"
>
	<?php if ( null !== $banner ) : ?>
		<div class="ghahghah-blog-archive__hero-stage ghahghah-blog-archive__hero-stage--banner">
			<picture class="ghahghah-blog-archive__hero-picture">
				<?php if ( null !== $banner['mobile'] ) : ?>
					<source
						media="(max-width: 47.99rem)"
						srcset="<?php echo esc_url( $banner['mobile']['src'] ); ?>"
					/>
				<?php endif; ?>
				<img
					class="ghahghah-blog-archive__hero-banner"
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
			<div class="ghahghah-blog-archive__hero-banner-copy">
				<h1 id="ghahghah-ba-hero-title" class="screen-reader-text">
					<?php echo esc_html( $defaults['hero_title'] ); ?>
				</h1>
			</div>
		</div>
	<?php else : ?>
		<div class="ghahghah-blog-archive__hero-stage">
			<span class="ghahghah-blog-archive__hero-glow ghahghah-blog-archive__hero-glow--a" aria-hidden="true"></span>
			<span class="ghahghah-blog-archive__hero-glow ghahghah-blog-archive__hero-glow--b" aria-hidden="true"></span>
			<span class="ghahghah-blog-archive__hero-wave ghahghah-blog-archive__hero-wave--start" aria-hidden="true"></span>
			<span class="ghahghah-blog-archive__hero-wave ghahghah-blog-archive__hero-wave--end" aria-hidden="true"></span>

			<p class="ghahghah-blog-archive__hero-chip ghahghah-blog-archive__hero-chip--start">
				<span><?php echo esc_html( $defaults['hero_tagline_left'] ); ?></span>
			</p>
			<p class="ghahghah-blog-archive__hero-chip ghahghah-blog-archive__hero-chip--end">
				<?php ghahghah_the_blog_archive_icon( 'heart-outline' ); ?>
				<span><?php echo esc_html( $defaults['hero_tagline_right'] ); ?></span>
			</p>

			<div class="ghahghah-blog-archive__hero-inner">
				<div class="ghahghah-blog-archive__hero-copy">
					<p class="ghahghah-blog-archive__hero-eyebrow"><?php esc_html_e( 'مجله برند قهقهه', 'ghahghah' ); ?></p>
					<h1 id="ghahghah-ba-hero-title" class="ghahghah-blog-archive__hero-title">
						<?php echo wp_kses( $title_html, array( 'span' => array( 'class' => true ) ) ); ?>
					</h1>
					<p class="ghahghah-blog-archive__hero-subtitle"><?php echo esc_html( $defaults['hero_subtitle'] ); ?></p>
				</div>

				<div class="ghahghah-blog-archive__hero-media">
					<img
						class="ghahghah-blog-archive__hero-corn"
						src="<?php echo esc_url( $hero_url ); ?>"
						alt=""
						width="560"
						height="420"
						decoding="async"
						fetchpriority="high"
					/>
					<img
						class="ghahghah-blog-archive__hero-bowl"
						src="<?php echo esc_url( $bowl_url ); ?>"
						alt=""
						width="160"
						height="160"
						decoding="async"
						loading="lazy"
					/>
					<img
						class="ghahghah-blog-archive__hero-chip-img"
						src="<?php echo esc_url( $chip_url ); ?>"
						alt=""
						width="96"
						height="96"
						decoding="async"
						loading="lazy"
						aria-hidden="true"
					/>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
