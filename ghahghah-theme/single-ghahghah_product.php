<?php
/**
 * Single product template (ghahghah_product).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

while ( have_posts() ) :
	the_post();
	$product = get_post();
	if ( ! $product instanceof WP_Post ) {
		continue;
	}
	$data = ghahghah_get_single_product_data( $product );
	?>
	<main id="main-content" class="site-main ghahghah-single-product" tabindex="-1">
		<?php
		set_query_var( 'ghahghah_sp_data', $data );
		get_template_part( 'template-parts/single-product/hero' );
		get_template_part( 'template-parts/single-product/features' );
		get_template_part( 'template-parts/single-product/details' );
		get_template_part( 'template-parts/single-product/related' );
		?>
	</main>
	<?php
endwhile;

get_footer();
