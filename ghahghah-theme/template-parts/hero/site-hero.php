<?php
/**
 * Homepage banner slider — desktop/mobile picture, framed stage.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_hero() ) {
	return;
}

$slides      = ghahghah_get_hero_banner_slides();
$slide_count = count( $slides );
$interval_ms = ghahghah_get_hero_interval_ms();
$multi       = $slide_count > 1;
$bp          = GHAHGHAH_HERO_BREAKPOINT;
?>
<section
	class="ghahghah-hero"
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'اسلایدر بنر صفحه اصلی', 'ghahghah' ); ?>"
	data-ghahghah-hero-slider
	data-interval="<?php echo esc_attr( (string) $interval_ms ); ?>"
	<?php echo $multi ? 'tabindex="0"' : ''; ?>
>
	<div class="ghahghah-hero__shell">
		<div class="ghahghah-hero__frame">
			<div class="ghahghah-hero__viewport">
				<ul class="ghahghah-hero__track">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<?php
						$mobile  = is_array( $slide['mobile'] ?? null ) ? $slide['mobile'] : null;
						$desktop = is_array( $slide['desktop'] ?? null ) ? $slide['desktop'] : null;
						if ( null === $mobile && null === $desktop ) {
							continue;
						}
						if ( null === $mobile ) {
							$mobile = $desktop;
						}
						if ( null === $desktop ) {
							$desktop = $mobile;
						}
						$alt = (string) ( $slide['alt'] ?? '' );
						$link = (string) ( $slide['link'] ?? '' );
						?>
						<li
							class="ghahghah-hero__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-index="<?php echo esc_attr( (string) $index ); ?>"
							aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>"
						>
							<?php if ( '' !== $link ) : ?>
								<a class="ghahghah-hero__link" href="<?php echo esc_url( $link ); ?>">
							<?php else : ?>
								<span class="ghahghah-hero__link">
							<?php endif; ?>
									<picture class="ghahghah-hero__picture">
										<?php if ( 0 === $index ) : ?>
											<source
												media="(min-width: <?php echo esc_attr( $bp ); ?>)"
												srcset="<?php echo esc_url( (string) $desktop['url'] ); ?>"
												<?php echo ! empty( $desktop['srcset'] ) ? 'width="' . esc_attr( (string) $desktop['width'] ) . '" height="' . esc_attr( (string) $desktop['height'] ) . '"' : ''; ?>
											/>
											<img
												class="ghahghah-hero__image"
												src="<?php echo esc_url( (string) $mobile['url'] ); ?>"
												alt="<?php echo esc_attr( $alt ); ?>"
												width="<?php echo esc_attr( (string) $mobile['width'] ); ?>"
												height="<?php echo esc_attr( (string) $mobile['height'] ); ?>"
												<?php echo ! empty( $mobile['srcset'] ) ? 'srcset="' . esc_attr( (string) $mobile['srcset'] ) . '"' : ''; ?>
												sizes="100vw"
												decoding="async"
												fetchpriority="high"
											/>
										<?php else : ?>
											<source
												media="(min-width: <?php echo esc_attr( $bp ); ?>)"
												data-ghahghah-hero-srcset="<?php echo esc_url( (string) $desktop['url'] ); ?>"
												<?php echo ! empty( $desktop['srcset'] ) ? 'width="' . esc_attr( (string) $desktop['width'] ) . '" height="' . esc_attr( (string) $desktop['height'] ) . '"' : ''; ?>
											/>
											<img
												class="ghahghah-hero__image"
												alt="<?php echo esc_attr( $alt ); ?>"
												width="<?php echo esc_attr( (string) $mobile['width'] ); ?>"
												height="<?php echo esc_attr( (string) $mobile['height'] ); ?>"
												data-ghahghah-hero-src="<?php echo esc_url( (string) $mobile['url'] ); ?>"
												<?php echo ! empty( $mobile['srcset'] ) ? 'data-ghahghah-hero-srcset="' . esc_attr( (string) $mobile['srcset'] ) . '"' : ''; ?>
												sizes="100vw"
												decoding="async"
											/>
										<?php endif; ?>
									</picture>
							<?php if ( '' !== $link ) : ?>
								</a>
							<?php else : ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( $multi ) : ?>
				<div class="ghahghah-hero__ui" aria-hidden="false">
					<button type="button" class="ghahghah-hero__nav ghahghah-hero__nav--prev" data-ghahghah-hero-prev aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'ghahghah' ); ?>">
						<?php ghahghah_the_hero_nav_icon( 'prev' ); ?>
					</button>
					<button type="button" class="ghahghah-hero__nav ghahghah-hero__nav--next" data-ghahghah-hero-next aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'ghahghah' ); ?>">
						<?php ghahghah_the_hero_nav_icon( 'next' ); ?>
					</button>
				</div>

				<div class="ghahghah-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'انتخاب اسلاید', 'ghahghah' ); ?>">
					<?php for ( $dot = 0; $dot < $slide_count; $dot++ ) : ?>
						<button
							type="button"
							class="ghahghah-hero__dot<?php echo 0 === $dot ? ' is-active' : ''; ?>"
							data-ghahghah-hero-dot
							data-index="<?php echo esc_attr( (string) $dot ); ?>"
							role="tab"
							aria-selected="<?php echo 0 === $dot ? 'true' : 'false'; ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'اسلاید %d', 'ghahghah' ), $dot + 1 ) ); ?>"
						></button>
					<?php endfor; ?>
				</div>

				<div class="ghahghah-hero__progress" aria-hidden="true">
					<span class="ghahghah-hero__progress-bar" data-ghahghah-hero-progress></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<p class="ghahghah-hero__live screen-reader-text" data-ghahghah-hero-live aria-live="polite"></p>
</section>
