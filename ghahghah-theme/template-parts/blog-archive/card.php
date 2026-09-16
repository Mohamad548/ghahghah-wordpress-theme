<?php
/**
 * Single article card for the blog archive.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_post();
if ( ! $post instanceof WP_Post ) {
	return;
}

$defaults = ghahghah_blog_archive_defaults();
$title    = get_the_title( $post );
$excerpt  = ghahghah_blog_archive_card_excerpt( $post );
$url      = get_permalink( $post );
$url      = is_string( $url ) ? $url : '';
$date     = get_the_date( '', $post );
$thumb_id = (int) get_post_thumbnail_id( $post );
?>
<article class="ghahghah-blog-archive__card" role="listitem">
	<a class="ghahghah-blog-archive__card-media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $thumb_id > 0 ) : ?>
			<?php
			echo wp_get_attachment_image(
				$thumb_id,
				'ghahghah-card',
				false,
				array(
					'class'    => 'ghahghah-blog-archive__card-img',
					'loading'  => 'lazy',
					'decoding' => 'async',
					'alt'      => '',
				)
			);
			?>
		<?php else : ?>
			<img
				class="ghahghah-blog-archive__card-img"
				src="<?php echo esc_url( ghahghah_blog_archive_fallback_image_url() ); ?>"
				alt=""
				width="640"
				height="480"
				loading="lazy"
				decoding="async"
			/>
		<?php endif; ?>
	</a>

	<div class="ghahghah-blog-archive__card-body">
		<h2 class="ghahghah-blog-archive__card-title">
			<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( is_string( $title ) ? $title : '' ); ?></a>
		</h2>

		<?php if ( '' !== $excerpt ) : ?>
			<p class="ghahghah-blog-archive__card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<div class="ghahghah-blog-archive__card-footer">
			<a class="ghahghah-blog-archive__card-cta" href="<?php echo esc_url( $url ); ?>">
				<span><?php echo esc_html( $defaults['cta_label'] ); ?></span>
				<span class="ghahghah-blog-archive__card-cta-icon" aria-hidden="true">
					<?php ghahghah_the_blog_archive_icon( 'chevron-left' ); ?>
				</span>
			</a>
			<?php if ( is_string( $date ) && '' !== $date ) : ?>
				<p class="ghahghah-blog-archive__card-date">
					<span class="ghahghah-blog-archive__card-date-icon" aria-hidden="true">
						<?php ghahghah_the_blog_archive_icon( 'calendar' ); ?>
					</span>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>"><?php echo esc_html( $date ); ?></time>
				</p>
			<?php endif; ?>
		</div>
	</div>
</article>
