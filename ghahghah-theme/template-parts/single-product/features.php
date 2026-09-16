<?php
/**
 * Single product feature strip.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$features = ghahghah_single_product_features();
?>
<section class="ghahghah-single-product__features" aria-label="<?php esc_attr_e( 'ویژگی‌های محصول', 'ghahghah' ); ?>">
	<div class="ghahghah-single-product__shell">
		<ul class="ghahghah-single-product__feature-grid">
			<?php foreach ( $features as $feature ) : ?>
				<li class="ghahghah-single-product__feature">
					<span class="ghahghah-single-product__feature-icon" aria-hidden="true">
						<?php ghahghah_the_single_product_icon( (string) $feature['icon'] ); ?>
					</span>
					<span class="ghahghah-single-product__feature-title"><?php echo esc_html( (string) $feature['title'] ); ?></span>
					<span class="ghahghah-single-product__feature-text"><?php echo esc_html( (string) $feature['text'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
