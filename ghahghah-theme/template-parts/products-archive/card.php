<?php
/**
 * Single product card for the products archive.
 *
 * Desktop: stacked card. Mobile: RTL media-object (image right → title → subtitle → weight → CTA).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = get_post();
if ( ! $product instanceof WP_Post ) {
	return;
}

$defaults = ghahghah_products_archive_defaults();
$title    = ghahghah_products_archive_card_title( $product );
$subtitle = ghahghah_products_archive_card_subtitle( $product );
$weight   = ghahghah_products_archive_card_weight( $product );
$url      = get_permalink( $product );
$url      = is_string( $url ) ? $url : '';
$thumb_id = (int) get_post_thumbnail_id( $product );
?>
<article class="ghahghah-products-archive__card" role="listitem">
	<a class="ghahghah-products-archive__card-media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $thumb_id > 0 ) : ?>
			<?php
			echo wp_get_attachment_image(
				$thumb_id,
				'large',
				false,
				array(
					'class'    => 'ghahghah-products-archive__card-img',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'alt'      => '',
					'sizes'    => '(max-width: 767px) 42vw, (max-width: 1023px) 30vw, 16vw',
				)
			);
			?>
		<?php else : ?>
			<span class="ghahghah-products-archive__card-placeholder" aria-hidden="true">
				<?php ghahghah_the_products_archive_icon( 'package' ); ?>
			</span>
		<?php endif; ?>
	</a>

	<div class="ghahghah-products-archive__card-body">
		<h2 class="ghahghah-products-archive__card-title">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
		</h2>

		<?php if ( '' !== $subtitle ) : ?>
			<p class="ghahghah-products-archive__card-subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $weight ) : ?>
			<p class="ghahghah-products-archive__card-weight">
				<span class="ghahghah-products-archive__card-weight-icon" aria-hidden="true">
					<?php ghahghah_the_products_archive_icon( 'weight' ); ?>
				</span>
				<span>
					<?php
					/* translators: %s: net weight value */
					echo esc_html( sprintf( __( 'وزن خالص: %s', 'ghahghah' ), $weight ) );
					?>
				</span>
			</p>
		<?php endif; ?>

		<a class="ghahghah-products-archive__card-cta" href="<?php echo esc_url( $url ); ?>">
			<span><?php echo esc_html( $defaults['cta_label'] ); ?></span>
			<span class="ghahghah-products-archive__card-cta-icon" aria-hidden="true">
				<?php ghahghah_the_products_archive_icon( 'chevron-left' ); ?>
			</span>
		</a>
	</div>
</article>
