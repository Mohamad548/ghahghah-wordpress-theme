<?php
/**
 * Products CPT archive template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

$defaults = ghahghah_products_archive_defaults();
$state    = ghahghah_products_archive_request_state();
$found    = (int) $GLOBALS['wp_query']->found_posts;
$banner   = ghahghah_get_products_archive_custom_banner();
?>

<main id="main-content" class="site-main ghahghah-products-archive<?php echo null !== $banner ? ' ghahghah-products-archive--custom-banner' : ''; ?>" tabindex="-1">
	<?php get_template_part( 'template-parts/products-archive/hero' ); ?>

	<div class="ghahghah-products-archive__shell">
		<nav class="ghahghah-products-archive__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
			<a class="ghahghah-products-archive__crumb-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php ghahghah_the_products_archive_icon( 'home', array( 'modifiers' => array( 'muted' ) ) ); ?>
				<span><?php echo esc_html( $defaults['crumb_home'] ); ?></span>
			</a>
			<span class="ghahghah-products-archive__crumb-sep" aria-hidden="true">/</span>
			<span class="ghahghah-products-archive__crumb-current" aria-current="page"><?php echo esc_html( $defaults['crumb_current'] ); ?></span>
		</nav>

		<?php get_template_part( 'template-parts/products-archive/toolbar' ); ?>
		<?php get_template_part( 'template-parts/products-archive/chips' ); ?>

		<p class="ghahghah-products-archive__count ghahghah-products-archive__count--mobile" aria-live="polite">
			<?php echo esc_html( ghahghah_products_archive_found_label( $found ) ); ?>
		</p>

		<?php if ( have_posts() ) : ?>
			<div class="ghahghah-products-archive__grid" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/products-archive/card' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'قبلی', 'ghahghah' ),
					'next_text' => __( 'بعدی', 'ghahghah' ),
					'class'     => 'ghahghah-products-archive__pagination',
				)
			);
			?>
		<?php else : ?>
			<div class="ghahghah-products-archive__empty" role="status">
				<p class="ghahghah-products-archive__empty-title"><?php echo esc_html( $defaults['empty_title'] ); ?></p>
				<p class="ghahghah-products-archive__empty-text"><?php echo esc_html( $defaults['empty_text'] ); ?></p>
				<?php if ( 'all' !== $state['flavor'] || '' !== $state['q'] ) : ?>
					<a class="ghahghah-products-archive__empty-reset" href="<?php echo esc_url( ghahghah_products_archive_url() ); ?>">
						<?php esc_html_e( 'نمایش همه محصولات', 'ghahghah' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
