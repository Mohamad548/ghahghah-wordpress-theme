<?php
/**
 * Footer template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner">
		<div class="site-footer__brand">
			<p class="site-footer__title"><?php bloginfo( 'name' ); ?></p>
			<?php
			$ghahghah_tagline = get_bloginfo( 'description', 'display' );
			if ( $ghahghah_tagline ) :
				?>
				<p class="site-footer__tagline"><?php echo esc_html( $ghahghah_tagline ); ?></p>
			<?php endif; ?>
		</div>

		<div class="site-footer__menus">
			<?php ghahghah_the_nav( 'footer', 'site-nav--footer' ); ?>
			<?php ghahghah_the_nav( 'legal', 'site-nav--legal' ); ?>
		</div>

		<p class="site-footer__credit">
			<?php
			printf(
				/* translators: %s: current year */
				esc_html__( '© %s قهقهه. تمامی حقوق محفوظ است.', 'ghahghah' ),
				esc_html( gmdate( 'Y' ) )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
