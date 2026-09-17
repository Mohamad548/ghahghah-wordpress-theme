<?php
/**
 * Featured products section markup (manual carousel).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_featured() ) {
	return;
}

$ghahghah_title     = trim( (string) ghahghah_get_featured_mod( 'ghahghah_featured_title' ) );
$ghahghah_text      = trim( (string) ghahghah_get_featured_mod( 'ghahghah_featured_text' ) );
$ghahghah_all_label = trim( (string) ghahghah_get_featured_mod( 'ghahghah_featured_all_label' ) );
$ghahghah_products  = ghahghah_get_featured_products();
$ghahghah_archive   = ghahghah_get_featured_archive_url();
$ghahghah_heading_id = '' !== $ghahghah_title ? 'ghahghah-featured-title' : 'ghahghah-featured-heading';
$ghahghah_has_all    = '' !== $ghahghah_archive;
$ghahghah_slide_count = count( $ghahghah_products ) + ( $ghahghah_has_all ? 1 : 0 );

if ( '' === $ghahghah_all_label ) {
	$ghahghah_all_label = __( 'مشاهده همه محصولات', 'ghahghah' );
}
?>
<section class="ghahghah-featured" aria-labelledby="<?php echo esc_attr( $ghahghah_heading_id ); ?>" data-ghahghah-featured>
	<div class="ghahghah-featured__shell">
		<header class="ghahghah-featured__head">
			<div class="ghahghah-featured__intro">
				<?php if ( '' !== $ghahghah_title ) : ?>
					<h2 id="ghahghah-featured-title" class="ghahghah-featured__title">
						<?php echo wp_kses( ghahghah_format_featured_title( $ghahghah_title ), array( 'span' => array( 'class' => true ) ) ); ?>
					</h2>
				<?php else : ?>
					<span id="ghahghah-featured-heading" class="screen-reader-text"><?php esc_html_e( 'محصولات منتخب', 'ghahghah' ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $ghahghah_text ) : ?>
					<p class="ghahghah-featured__text"><?php echo esc_html( $ghahghah_text ); ?></p>
				<?php endif; ?>
			</div>
		</header>

		<div class="ghahghah-featured__stage">
			<?php if ( $ghahghah_slide_count > 1 ) : ?>
				<div class="ghahghah-featured__controls" data-ghahghah-featured-controls hidden>
					<button
						type="button"
						class="ghahghah-featured__nav ghahghah-featured__nav--prev"
						data-ghahghah-featured-prev
						aria-controls="ghahghah-featured-track"
						aria-label="<?php echo esc_attr__( 'محصولات قبلی', 'ghahghah' ); ?>"
					>
						<svg class="ghahghah-featured__arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
					</button>
					<button
						type="button"
						class="ghahghah-featured__nav ghahghah-featured__nav--next"
						data-ghahghah-featured-next
						aria-controls="ghahghah-featured-track"
						aria-label="<?php echo esc_attr__( 'محصولات بعدی', 'ghahghah' ); ?>"
					>
						<svg class="ghahghah-featured__arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
					</button>
				</div>
			<?php endif; ?>

			<div
				class="ghahghah-featured__viewport"
				data-ghahghah-featured-viewport
			>
				<ul
					id="ghahghah-featured-track"
					class="ghahghah-featured__track"
					data-ghahghah-featured-track
					tabindex="0"
					aria-label="<?php echo esc_attr__( 'فهرست محصولات منتخب', 'ghahghah' ); ?>"
				>
					<?php foreach ( $ghahghah_products as $ghahghah_index => $ghahghah_product ) : ?>
						<?php
						$ghahghah_pid  = (int) $ghahghah_product->ID;
						$ghahghah_url  = get_permalink( $ghahghah_product );
						$ghahghah_name = get_the_title( $ghahghah_product );
						if ( ! is_string( $ghahghah_url ) || '' === $ghahghah_url || '' === $ghahghah_name ) {
							continue;
						}
						?>
						<li class="ghahghah-featured__item" data-ghahghah-featured-item data-index="<?php echo esc_attr( (string) $ghahghah_index ); ?>">
							<article class="ghahghah-featured__card">
								<div class="ghahghah-featured__media">
									<?php
									if ( has_post_thumbnail( $ghahghah_pid ) ) {
										echo get_the_post_thumbnail(
											$ghahghah_pid,
											'large',
											array(
												'class'    => 'ghahghah-featured__img',
												'loading'  => 'lazy',
												'decoding' => 'async',
												'alt'      => $ghahghah_name,
												'sizes'    => '(min-width: 64rem) 12vw, (min-width: 48rem) 18vw, 55vw',
											)
										);
									}
									?>
								</div>
								<div class="ghahghah-featured__body">
									<h3 class="ghahghah-featured__name"><?php echo esc_html( $ghahghah_name ); ?></h3>
									<a class="ghahghah-featured__cta" href="<?php echo esc_url( $ghahghah_url ); ?>">
										<span><?php esc_html_e( 'مشاهده محصول', 'ghahghah' ); ?></span>
										<?php ghahghah_the_featured_arrow(); ?>
									</a>
								</div>
							</article>
						</li>
					<?php endforeach; ?>

					<?php if ( $ghahghah_has_all ) : ?>
						<li
							class="ghahghah-featured__item ghahghah-featured__item--all"
							data-ghahghah-featured-item
							data-ghahghah-featured-all
							data-index="<?php echo esc_attr( (string) count( $ghahghah_products ) ); ?>"
						>
							<a class="ghahghah-featured__card ghahghah-featured__card--all" href="<?php echo esc_url( $ghahghah_archive ); ?>">
								<span class="ghahghah-featured__all-icon" aria-hidden="true">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
								</span>
								<span class="ghahghah-featured__all-label"><?php echo esc_html( $ghahghah_all_label ); ?></span>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<p class="screen-reader-text" data-ghahghah-featured-live aria-live="polite" aria-atomic="true"></p>
	</div>
</section>
