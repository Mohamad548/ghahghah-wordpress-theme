<?php
/**
 * Decorative product collage for the 404 page.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_404_img = GHAHGHAH_THEME_URI . '/assets/images/404';
?>
<div class="ghahghah-404__collage">
	<span class="ghahghah-404__glow" aria-hidden="true"></span>

	<img
		class="ghahghah-404__pack"
		src="<?php echo esc_url( $ghahghah_404_img . '/product-pizza-pack.webp' ); ?>"
		alt=""
		width="825"
		height="1200"
		decoding="async"
		fetchpriority="high"
	/>

	<img
		class="ghahghah-404__prop ghahghah-404__prop--pizza"
		src="<?php echo esc_url( $ghahghah_404_img . '/pizza-slice.webp' ); ?>"
		alt=""
		width="1200"
		height="790"
		loading="lazy"
		decoding="async"
	/>

	<img
		class="ghahghah-404__prop ghahghah-404__prop--tomato"
		src="<?php echo esc_url( $ghahghah_404_img . '/tomato.webp' ); ?>"
		alt=""
		width="1200"
		height="1090"
		loading="lazy"
		decoding="async"
	/>

	<img
		class="ghahghah-404__prop ghahghah-404__prop--cone ghahghah-404__prop--cone-a"
		src="<?php echo esc_url( $ghahghah_404_img . '/corn-cone.webp' ); ?>"
		alt=""
		width="1200"
		height="809"
		loading="lazy"
		decoding="async"
	/>
	<img
		class="ghahghah-404__prop ghahghah-404__prop--cone ghahghah-404__prop--cone-b"
		src="<?php echo esc_url( $ghahghah_404_img . '/corn-cone.webp' ); ?>"
		alt=""
		width="1200"
		height="809"
		loading="lazy"
		decoding="async"
	/>
	<img
		class="ghahghah-404__prop ghahghah-404__prop--cone ghahghah-404__prop--cone-c"
		src="<?php echo esc_url( $ghahghah_404_img . '/corn-cone.webp' ); ?>"
		alt=""
		width="1200"
		height="809"
		loading="lazy"
		decoding="async"
	/>

	<img
		class="ghahghah-404__prop ghahghah-404__prop--leaf ghahghah-404__prop--leaf-a"
		src="<?php echo esc_url( $ghahghah_404_img . '/parsley-leaves.webp' ); ?>"
		alt=""
		width="1200"
		height="795"
		loading="lazy"
		decoding="async"
	/>
	<img
		class="ghahghah-404__prop ghahghah-404__prop--leaf ghahghah-404__prop--leaf-b"
		src="<?php echo esc_url( $ghahghah_404_img . '/parsley-leaves.webp' ); ?>"
		alt=""
		width="1200"
		height="795"
		loading="lazy"
		decoding="async"
	/>
</div>
