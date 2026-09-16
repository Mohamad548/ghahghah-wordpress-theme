<?php
/**
 * Single product hero: gallery + info + CTAs.
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

$gallery  = ghahghah_get_product_gallery_ids( (int) $data['id'] );
$specs    = array_slice( ghahghah_single_product_spec_rows( $data ), 0, 4 );
$archive  = function_exists( 'ghahghah_get_products_archive_url' ) ? ghahghah_get_products_archive_url() : '';
$wholesale = function_exists( 'ghahghah_get_wholesale_form_url_for_product' )
	? ghahghah_get_wholesale_form_url_for_product( (int) $data['id'] )
	: '';
$agency = function_exists( 'ghahghah_get_agency_form_url' ) ? ghahghah_get_agency_form_url() : '';
$shared = ghahghah_product_shared_defaults();
?>
<section class="ghahghah-single-product__hero">
	<div class="ghahghah-single-product__shell ghahghah-single-product__hero-grid">
		<div class="ghahghah-single-product__gallery" data-ghahghah-sp-gallery>
			<div class="ghahghah-single-product__gallery-stage">
				<?php if ( ! empty( $gallery ) ) : ?>
					<?php foreach ( $gallery as $index => $attachment_id ) : ?>
						<figure
							class="ghahghah-single-product__gallery-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-index="<?php echo esc_attr( (string) $index ); ?>"
							<?php echo 0 === $index ? '' : ' hidden'; ?>
						>
							<?php
							echo wp_get_attachment_image(
								(int) $attachment_id,
								'large',
								false,
								array(
									'class'    => 'ghahghah-single-product__gallery-img',
									'loading'  => 0 === $index ? 'eager' : 'lazy',
									'decoding' => 'async',
									'alt'      => esc_attr( (string) $data['display_title'] ),
								)
							);
							?>
						</figure>
					<?php endforeach; ?>
				<?php else : ?>
					<figure class="ghahghah-single-product__gallery-slide is-active">
						<span class="ghahghah-single-product__gallery-placeholder" aria-hidden="true">
							<?php ghahghah_the_single_product_icon( 'package' ); ?>
						</span>
					</figure>
				<?php endif; ?>
			</div>

			<?php if ( count( $gallery ) > 1 ) : ?>
				<div class="ghahghah-single-product__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'گالری محصول', 'ghahghah' ); ?>">
					<?php foreach ( $gallery as $index => $attachment_id ) : ?>
						<button
							type="button"
							class="ghahghah-single-product__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							data-ghahghah-sp-thumb="<?php echo esc_attr( (string) $index ); ?>"
						>
							<?php
							echo wp_get_attachment_image(
								(int) $attachment_id,
								'thumbnail',
								false,
								array(
									'alt'      => '',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</button>
					<?php endforeach; ?>
				</div>
				<div class="ghahghah-single-product__dots" aria-hidden="true">
					<?php foreach ( $gallery as $index => $_id ) : ?>
						<span class="ghahghah-single-product__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) $index ); ?>"></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="ghahghah-single-product__info">
			<p class="ghahghah-single-product__slogan" aria-hidden="true"><?php echo esc_html( (string) $shared['slogan'] ); ?></p>

			<nav class="ghahghah-single-product__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( '' !== $archive ) : ?>
					<a href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'محصولات', 'ghahghah' ); ?></a>
					<span aria-hidden="true">/</span>
				<?php endif; ?>
				<span aria-current="page"><?php echo esc_html( (string) $data['nav_label'] ); ?></span>
			</nav>

			<?php if ( ! empty( $data['labels'] ) && is_array( $data['labels'] ) ) : ?>
				<ul class="ghahghah-single-product__labels">
					<?php foreach ( $data['labels'] as $label ) : ?>
						<li><?php echo esc_html( (string) $label ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<h1 class="ghahghah-single-product__title"><?php echo esc_html( (string) $data['display_title'] ); ?></h1>

			<?php if ( '' !== (string) $data['subtitle'] ) : ?>
				<p class="ghahghah-single-product__subtitle"><?php echo esc_html( (string) $data['subtitle'] ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== (string) $data['short_intro'] ) : ?>
				<p class="ghahghah-single-product__lead"><?php echo esc_html( (string) $data['short_intro'] ); ?></p>
			<?php endif; ?>

			<?php if ( $specs ) : ?>
				<ul class="ghahghah-single-product__quick-specs">
					<?php foreach ( $specs as $row ) : ?>
						<li>
							<span class="ghahghah-single-product__quick-icon" aria-hidden="true">
								<?php ghahghah_the_single_product_icon( (string) $row['icon'] ); ?>
							</span>
							<span>
								<?php echo esc_html( (string) $row['label'] ); ?>:
								<strong><?php echo esc_html( (string) $row['value'] ); ?></strong>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="ghahghah-single-product__ctas">
				<?php if ( '' !== $wholesale ) : ?>
					<a class="ghahghah-single-product__cta ghahghah-single-product__cta--wholesale" href="<?php echo esc_url( $wholesale ); ?>">
						<?php ghahghah_the_single_product_icon( 'wholesale' ); ?>
						<span><?php esc_html_e( 'درخواست خرید عمده', 'ghahghah' ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( '' !== $agency ) : ?>
					<a class="ghahghah-single-product__cta ghahghah-single-product__cta--agency" href="<?php echo esc_url( $agency ); ?>">
						<?php ghahghah_the_single_product_icon( 'agency' ); ?>
						<span><?php esc_html_e( 'درخواست نمایندگی', 'ghahghah' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
