<?php
/**
 * Single content template part.
 *
 * @package Ghahghah
 */

declare(strict_types=1);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
	<header class="entry__header">
		<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__media">
			<?php the_post_thumbnail( 'ghahghah-hero' ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry__content">
		<?php the_content(); ?>
	</div>

	<?php if ( 'ghahghah_product' === get_post_type() && function_exists( 'ghahghah_get_wholesale_form_url_for_product' ) ) : ?>
		<p class="entry__wholesale">
			<a class="entry__wholesale-link" href="<?php echo esc_url( ghahghah_get_wholesale_form_url_for_product( (int) get_the_ID() ) ); ?>">
				<?php esc_html_e( 'درخواست خرید عمده این محصول', 'ghahghah' ); ?>
			</a>
		</p>
	<?php endif; ?>
</article>
