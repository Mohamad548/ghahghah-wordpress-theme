<?php
/**
 * Related products strip.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = get_query_var( 'ghahghah_sp_data' );
if ( ! is_array( $data ) ) {
	return;
}

$related = ghahghah_get_related_products( (int) $data['id'], 5 );
if ( ! $related ) {
	return;
}
?>
<section class="ghahghah-single-product__related" aria-labelledby="ghahghah-sp-related-title">
	<div class="ghahghah-single-product__shell">
		<header class="ghahghah-single-product__related-head">
			<h2 id="ghahghah-sp-related-title" class="ghahghah-single-product__section-title">
				<?php esc_html_e( 'محصولات مرتبط', 'ghahghah' ); ?>
			</h2>
		</header>

		<div class="ghahghah-single-product__related-track" role="list">
			<?php foreach ( $related as $item ) : ?>
				<?php
				if ( ! $item instanceof WP_Post ) {
					continue;
				}
				$url   = get_permalink( $item );
				$label = function_exists( 'ghahghah_product_nav_label' )
					? ghahghah_product_nav_label( $item )
					: get_the_title( $item );
				$thumb = (int) get_post_thumbnail_id( $item );
				?>
				<a class="ghahghah-single-product__related-card" role="listitem" href="<?php echo esc_url( is_string( $url ) ? $url : '' ); ?>">
					<span class="ghahghah-single-product__related-media">
						<?php if ( $thumb > 0 ) : ?>
							<?php
							echo wp_get_attachment_image(
								$thumb,
								'large',
								false,
								array(
									'loading'  => 'lazy',
									'decoding' => 'async',
									'alt'      => '',
								)
							);
							?>
						<?php else : ?>
							<span class="ghahghah-single-product__related-placeholder" aria-hidden="true">
								<?php ghahghah_the_single_product_icon( 'package' ); ?>
							</span>
						<?php endif; ?>
					</span>
					<span class="ghahghah-single-product__related-title"><?php echo esc_html( is_string( $label ) ? $label : '' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
