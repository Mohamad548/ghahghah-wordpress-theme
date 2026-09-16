<?php
/**
 * Factory page hero: breadcrumb, title, image + company intro.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title   = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_title' ) );
$lead    = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_lead' ) );
$company = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_company' ) );
$intro   = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_intro' ) );
$cta     = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_cta_label' ) );
$image   = ghahghah_get_factory_page_hero_image();
$pack    = ghahghah_get_factory_page_product_pack();
$archive = function_exists( 'ghahghah_get_products_archive_url' ) ? ghahghah_get_products_archive_url() : '';
$page_t  = get_the_title();
if ( '' === $title ) {
	$title = __( 'از دانه ذرت تا لحظه‌های خوشمزه', 'ghahghah' );
}
?>
<section class="ghahghah-fp__hero" aria-labelledby="ghahghah-fp-hero-title">
	<nav class="ghahghah-fp__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
		<span class="ghahghah-fp__crumb-sep" aria-hidden="true">/</span>
		<span class="ghahghah-fp__crumb-current" aria-current="page">
			<?php echo esc_html( '' !== $page_t ? $page_t : __( 'معرفی کارخانه', 'ghahghah' ) ); ?>
		</span>
	</nav>

	<header class="ghahghah-fp__hero-head">
		<h1 id="ghahghah-fp-hero-title" class="ghahghah-fp__title">
			<span class="ghahghah-fp__spark ghahghah-fp__spark--start" aria-hidden="true">
				<?php ghahghah_the_factory_page_icon( 'decor-spark', array( 'modifiers' => array( 'spark' ) ) ); ?>
			</span>
			<span class="ghahghah-fp__title-text">
				<?php
				echo wp_kses(
					ghahghah_format_factory_page_title( $title ),
					array( 'span' => array( 'class' => true ) )
				);
				?>
			</span>
			<span class="ghahghah-fp__spark ghahghah-fp__spark--end" aria-hidden="true">
				<?php ghahghah_the_factory_page_icon( 'decor-spark', array( 'modifiers' => array( 'spark' ) ) ); ?>
			</span>
		</h1>
		<?php if ( '' !== $lead ) : ?>
			<p class="ghahghah-fp__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</header>

	<div class="ghahghah-fp__hero-grid">
		<div class="ghahghah-fp__intro-col">
			<article class="ghahghah-fp__intro-card">
				<?php if ( '' !== $company ) : ?>
					<h2 class="ghahghah-fp__company"><?php echo esc_html( $company ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $intro ) : ?>
					<div class="ghahghah-fp__intro-text">
						<?php
						$paras = preg_split( '/\R\R+/', $intro ) ?: array( $intro );
						foreach ( $paras as $para ) {
							$para = trim( (string) $para );
							if ( '' === $para ) {
								continue;
							}
							echo '<p>' . esc_html( $para ) . '</p>';
						}
						?>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $cta && '' !== $archive ) : ?>
					<a class="ghahghah-fp__cta" href="<?php echo esc_url( $archive ); ?>">
						<span><?php echo esc_html( $cta ); ?></span>
						<span class="ghahghah-fp__cta-icon" aria-hidden="true">
							<?php ghahghah_the_factory_page_icon( 'arrow-chevron', array( 'modifiers' => array( 'cta' ) ) ); ?>
						</span>
					</a>
				<?php endif; ?>
			</article>

			<div class="ghahghah-fp__pack" aria-hidden="true">
				<span class="ghahghah-fp__pack-corn">
					<?php ghahghah_the_factory_page_icon( 'decor-corn', array( 'modifiers' => array( 'corn' ) ) ); ?>
				</span>
				<img
					class="ghahghah-fp__pack-img"
					src="<?php echo esc_url( $pack['src'] ); ?>"
					width="<?php echo esc_attr( (string) $pack['width'] ); ?>"
					height="<?php echo esc_attr( (string) $pack['height'] ); ?>"
					alt=""
					decoding="async"
					loading="lazy"
				/>
			</div>
		</div>

		<figure class="ghahghah-fp__media">
			<img
				class="ghahghah-fp__media-img"
				src="<?php echo esc_url( $image['src'] ); ?>"
				<?php if ( '' !== $image['srcset'] ) : ?>
					srcset="<?php echo esc_attr( $image['srcset'] ); ?>"
					sizes="(min-width: 64rem) 48vw, 100vw"
				<?php endif; ?>
				width="<?php echo esc_attr( (string) $image['width'] ); ?>"
				height="<?php echo esc_attr( (string) $image['height'] ); ?>"
				alt="<?php echo esc_attr( $image['alt'] ); ?>"
				decoding="async"
				fetchpriority="high"
				loading="eager"
			/>
			<?php if ( ! empty( $image['is_temp'] ) ) : ?>
				<span class="ghahghah-fp__temp-badge">
					<span class="ghahghah-fp__temp-badge-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="14" rx="2"/><circle cx="12" cy="13" r="3.2"/><path d="M8 6l1.2-2h5.6L16 6"/></svg>
					</span>
					<?php esc_html_e( 'تصویر موقت کارخانه', 'ghahghah' ); ?>
				</span>
			<?php endif; ?>
		</figure>
	</div>
</section>
