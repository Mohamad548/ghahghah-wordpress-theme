<?php
/**
 * Production steps section markup (infographic image only).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_steps() ) {
	return;
}

$ghahghah_image = ghahghah_get_steps_image();
if ( null === $ghahghah_image ) {
	return;
}
?>
<section class="ghahghah-steps" aria-label="<?php echo esc_attr( $ghahghah_image['alt'] ); ?>">
	<div class="ghahghah-steps__shell">
		<figure class="ghahghah-steps__figure">
			<img
				class="ghahghah-steps__img"
				src="<?php echo esc_url( $ghahghah_image['url'] ); ?>"
				alt="<?php echo esc_attr( $ghahghah_image['alt'] ); ?>"
				width="<?php echo esc_attr( (string) $ghahghah_image['width'] ); ?>"
				height="<?php echo esc_attr( (string) $ghahghah_image['height'] ); ?>"
				loading="lazy"
				decoding="async"
				<?php if ( '' !== $ghahghah_image['srcset'] ) : ?>
					srcset="<?php echo esc_attr( $ghahghah_image['srcset'] ); ?>"
					sizes="<?php echo esc_attr( $ghahghah_image['sizes'] ); ?>"
				<?php endif; ?>
			/>
		</figure>
	</div>
</section>
