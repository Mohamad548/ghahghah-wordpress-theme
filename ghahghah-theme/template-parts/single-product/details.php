<?php
/**
 * Single product intro + specifications (desktop columns / mobile accordion).
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

$specs   = ghahghah_single_product_spec_rows( $data );
$gallery = ghahghah_get_product_gallery_ids( (int) $data['id'] );
$intro_img = ! empty( $gallery[0] ) ? (int) $gallery[0] : 0;
$content   = (string) $data['content'];
?>
<section class="ghahghah-single-product__details">
	<div class="ghahghah-single-product__shell ghahghah-single-product__details-grid">
		<details class="ghahghah-single-product__panel ghahghah-single-product__panel--intro" open>
			<summary class="ghahghah-single-product__panel-summary">
				<span class="ghahghah-single-product__panel-summary-main">
					<?php if ( $intro_img > 0 ) : ?>
						<span class="ghahghah-single-product__panel-thumb">
							<?php
							echo wp_get_attachment_image(
								$intro_img,
								'thumbnail',
								false,
								array(
									'alt'      => '',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
							?>
						</span>
					<?php endif; ?>
					<span><?php esc_html_e( 'معرفی محصول', 'ghahghah' ); ?></span>
				</span>
				<span class="ghahghah-single-product__panel-chevron" aria-hidden="true">
					<?php ghahghah_the_single_product_icon( 'chevron-down' ); ?>
				</span>
			</summary>
			<div class="ghahghah-single-product__panel-body">
				<?php if ( $intro_img > 0 ) : ?>
					<figure class="ghahghah-single-product__intro-media">
						<?php
						echo wp_get_attachment_image(
							$intro_img,
							'large',
							false,
							array(
								'class'    => 'ghahghah-single-product__intro-img',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'alt'      => '',
							)
						);
						?>
					</figure>
				<?php endif; ?>
				<div class="ghahghah-single-product__intro-copy">
					<h2 class="ghahghah-single-product__section-title ghahghah-single-product__section-title--desktop"><?php esc_html_e( 'معرفی محصول', 'ghahghah' ); ?></h2>
					<?php if ( '' !== $content ) : ?>
						<div class="ghahghah-single-product__intro-text">
							<?php
							$paragraphs = preg_split( '/\n+/', $content ) ?: array( $content );
							foreach ( $paragraphs as $paragraph ) {
								$paragraph = trim( (string) $paragraph );
								if ( '' === $paragraph ) {
									continue;
								}
								echo '<p>' . esc_html( $paragraph ) . '</p>';
							}
							?>
						</div>
					<?php endif; ?>
					<?php if ( '' !== (string) $data['motto'] ) : ?>
						<p class="ghahghah-single-product__motto"><?php echo esc_html( (string) $data['motto'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</details>

		<details class="ghahghah-single-product__panel ghahghah-single-product__panel--specs" open>
			<summary class="ghahghah-single-product__panel-summary">
				<span class="ghahghah-single-product__panel-summary-main">
					<span class="ghahghah-single-product__panel-icon" aria-hidden="true">
						<?php ghahghah_the_single_product_icon( 'clipboard' ); ?>
					</span>
					<span><?php esc_html_e( 'مشخصات محصول', 'ghahghah' ); ?></span>
				</span>
				<span class="ghahghah-single-product__panel-chevron" aria-hidden="true">
					<?php ghahghah_the_single_product_icon( 'chevron-down' ); ?>
				</span>
			</summary>
			<div class="ghahghah-single-product__panel-body">
				<h2 class="ghahghah-single-product__section-title ghahghah-single-product__section-title--desktop"><?php esc_html_e( 'مشخصات محصول', 'ghahghah' ); ?></h2>
				<?php if ( $specs ) : ?>
					<table class="ghahghah-single-product__spec-table">
						<tbody>
							<?php foreach ( $specs as $row ) : ?>
								<tr>
									<th scope="row">
										<span class="ghahghah-single-product__spec-icon" aria-hidden="true">
											<?php ghahghah_the_single_product_icon( (string) $row['icon'] ); ?>
										</span>
										<?php echo esc_html( (string) $row['label'] ); ?>
									</th>
									<td><?php echo esc_html( (string) $row['value'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</details>
	</div>
</section>
