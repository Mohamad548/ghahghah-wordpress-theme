<?php
/**
 * Latest articles section — horizontal carousel (touch + nav buttons).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_articles() ) {
	return;
}

$ghahghah_posts     = ghahghah_get_articles_posts();
$ghahghah_eyebrow   = trim( (string) ghahghah_get_articles_mod( 'ghahghah_articles_eyebrow' ) );
$ghahghah_title     = trim( (string) ghahghah_get_articles_mod( 'ghahghah_articles_title' ) );
$ghahghah_all_label = trim( (string) ghahghah_get_articles_mod( 'ghahghah_articles_all_label' ) );
$ghahghah_more      = trim( (string) ghahghah_get_articles_mod( 'ghahghah_articles_more_label' ) );
$ghahghah_all_url   = ghahghah_get_articles_archive_url();
$ghahghah_heading   = '' !== $ghahghah_title ? 'ghahghah-articles-title' : 'ghahghah-articles-heading';
$ghahghah_has_all   = '' !== $ghahghah_all_label && '' !== $ghahghah_all_url;
$ghahghah_slides    = count( $ghahghah_posts ) + ( $ghahghah_has_all ? 1 : 0 );

if ( '' === $ghahghah_more ) {
	$ghahghah_more = __( 'ادامه مطلب', 'ghahghah' );
}
if ( '' === $ghahghah_all_label ) {
	$ghahghah_all_label = __( 'همه مطالب', 'ghahghah' );
}
?>
<section class="ghahghah-articles" aria-labelledby="<?php echo esc_attr( $ghahghah_heading ); ?>" data-ghahghah-articles>
	<div class="ghahghah-articles__shell">
		<header class="ghahghah-articles__header">
			<div class="ghahghah-articles__heading">
				<?php if ( '' !== $ghahghah_eyebrow ) : ?>
					<p class="ghahghah-articles__eyebrow"><?php echo esc_html( $ghahghah_eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $ghahghah_title ) : ?>
					<h2 id="ghahghah-articles-title" class="ghahghah-articles__title"><?php echo esc_html( $ghahghah_title ); ?></h2>
				<?php else : ?>
					<span id="ghahghah-articles-heading" class="screen-reader-text"><?php esc_html_e( 'آخرین مطالب', 'ghahghah' ); ?></span>
				<?php endif; ?>
			</div>
		</header>

		<div class="ghahghah-articles__stage">
			<?php if ( $ghahghah_slides > 1 ) : ?>
				<div class="ghahghah-articles__controls" data-ghahghah-articles-controls hidden>
					<button
						type="button"
						class="ghahghah-articles__nav ghahghah-articles__nav--prev"
						data-ghahghah-articles-prev
						aria-controls="ghahghah-articles-track"
						aria-label="<?php echo esc_attr__( 'مطالب قبلی', 'ghahghah' ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
					</button>
					<button
						type="button"
						class="ghahghah-articles__nav ghahghah-articles__nav--next"
						data-ghahghah-articles-next
						aria-controls="ghahghah-articles-track"
						aria-label="<?php echo esc_attr__( 'مطالب بعدی', 'ghahghah' ); ?>"
					>
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
					</button>
				</div>
			<?php endif; ?>

			<div class="ghahghah-articles__viewport" data-ghahghah-articles-viewport>
				<ul
					id="ghahghah-articles-track"
					class="ghahghah-articles__track"
					data-ghahghah-articles-track
					tabindex="0"
					aria-label="<?php echo esc_attr__( 'فهرست آخرین مطالب', 'ghahghah' ); ?>"
				>
					<?php foreach ( $ghahghah_posts as $ghahghah_index => $ghahghah_post ) : ?>
						<?php
						$ghahghah_pid     = (int) $ghahghah_post->ID;
						$ghahghah_plink   = get_permalink( $ghahghah_pid );
						$ghahghah_ptitle  = get_the_title( $ghahghah_pid );
						$ghahghah_excerpt = get_the_excerpt( $ghahghah_pid );
						$ghahghah_date    = get_the_date( '', $ghahghah_pid );
						$ghahghah_date_iso = get_the_date( DATE_W3C, $ghahghah_pid );
						if ( ! is_string( $ghahghah_plink ) || '' === $ghahghah_plink || '' === $ghahghah_ptitle ) {
							continue;
						}
						?>
						<li class="ghahghah-articles__item" data-ghahghah-articles-item data-index="<?php echo esc_attr( (string) $ghahghah_index ); ?>">
							<article class="ghahghah-articles__card">
								<a class="ghahghah-articles__media" href="<?php echo esc_url( $ghahghah_plink ); ?>" tabindex="-1" aria-hidden="true">
									<?php
									if ( has_post_thumbnail( $ghahghah_pid ) ) {
										echo get_the_post_thumbnail(
											$ghahghah_pid,
											'medium_large',
											array(
												'class'    => 'ghahghah-articles__img',
												'loading'  => 'lazy',
												'decoding' => 'async',
												'alt'      => '',
												'sizes'    => '(min-width: 64rem) 22vw, (min-width: 48rem) 28vw, 70vw',
											)
										);
									} else {
										echo '<span class="ghahghah-articles__placeholder"></span>';
									}
									?>
								</a>
								<div class="ghahghah-articles__body">
									<?php if ( is_string( $ghahghah_date ) && '' !== $ghahghah_date ) : ?>
										<p class="ghahghah-articles__date">
											<svg class="ghahghah-articles__date-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
											<time datetime="<?php echo esc_attr( is_string( $ghahghah_date_iso ) ? $ghahghah_date_iso : '' ); ?>"><?php echo esc_html( $ghahghah_date ); ?></time>
										</p>
									<?php endif; ?>
									<h3 class="ghahghah-articles__card-title">
										<a href="<?php echo esc_url( $ghahghah_plink ); ?>"><?php echo esc_html( $ghahghah_ptitle ); ?></a>
									</h3>
									<?php if ( '' !== trim( (string) $ghahghah_excerpt ) ) : ?>
										<p class="ghahghah-articles__excerpt"><?php echo esc_html( wp_strip_all_tags( (string) $ghahghah_excerpt ) ); ?></p>
									<?php endif; ?>
									<a class="ghahghah-articles__more" href="<?php echo esc_url( $ghahghah_plink ); ?>">
										<span><?php echo esc_html( $ghahghah_more ); ?></span>
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
									</a>
								</div>
							</article>
						</li>
					<?php endforeach; ?>

					<?php if ( $ghahghah_has_all ) : ?>
						<li
							class="ghahghah-articles__item ghahghah-articles__item--all"
							data-ghahghah-articles-item
							data-index="<?php echo esc_attr( (string) count( $ghahghah_posts ) ); ?>"
						>
							<a class="ghahghah-articles__card ghahghah-articles__card--all" href="<?php echo esc_url( $ghahghah_all_url ); ?>">
								<span class="ghahghah-articles__all-icon" aria-hidden="true">
									<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
								</span>
								<span class="ghahghah-articles__all-label"><?php echo esc_html( $ghahghah_all_label ); ?></span>
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<p class="screen-reader-text" data-ghahghah-articles-live aria-live="polite" aria-atomic="true"></p>
	</div>
</section>
