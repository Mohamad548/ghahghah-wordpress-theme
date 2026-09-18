<?php
/**
 * Wholesale / agency collab banner section.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_collab() ) {
	return;
}

$ghahghah_banners = ghahghah_get_collab_banners();
if ( array() === $ghahghah_banners ) {
	return;
}
?>
<section class="ghahghah-collab" aria-label="<?php echo esc_attr__( 'خرید عمده و درخواست نمایندگی', 'ghahghah' ); ?>">
	<div class="ghahghah-collab__shell">
		<div class="ghahghah-collab__grid">
			<?php foreach ( $ghahghah_banners as $ghahghah_banner ) : ?>
				<?php
				$ghahghah_type  = (string) $ghahghah_banner['type'];
				$ghahghah_image = $ghahghah_banner['image'];
				$ghahghah_url   = (string) $ghahghah_banner['url'];
				?>
				<?php if ( '' !== $ghahghah_url ) : ?>
					<a
						class="ghahghah-collab__banner ghahghah-collab__banner--<?php echo esc_attr( $ghahghah_type ); ?>"
						href="<?php echo esc_url( $ghahghah_url ); ?>"
					>
				<?php else : ?>
					<div class="ghahghah-collab__banner ghahghah-collab__banner--<?php echo esc_attr( $ghahghah_type ); ?>">
				<?php endif; ?>
						<img
							class="ghahghah-collab__img"
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
				<?php if ( '' !== $ghahghah_url ) : ?>
					</a>
				<?php else : ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
