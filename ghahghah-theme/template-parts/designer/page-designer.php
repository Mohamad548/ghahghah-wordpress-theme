<?php
/**
 * Designer about page layout.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = get_the_title();
$lead       = trim( (string) ghahghah_get_designer_page_mod( 'ghahghah_designer_page_lead' ) );
?>
<div class="ghahghah-designer__shell">
	<nav class="ghahghah-designer__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
		<span class="ghahghah-designer__crumb-sep" aria-hidden="true">/</span>
		<span aria-current="page"><?php echo esc_html( '' !== $page_title ? $page_title : __( 'محمد محمودی', 'ghahghah' ) ); ?></span>
	</nav>

	<header class="ghahghah-designer__intro">
		<p class="ghahghah-designer__eyebrow"><?php esc_html_e( 'توسعه و طراحی', 'ghahghah' ); ?></p>
		<h1 class="ghahghah-designer__title">
			<?php echo esc_html( '' !== $page_title ? $page_title : __( 'محمد محمودی', 'ghahghah' ) ); ?>
		</h1>
		<?php if ( '' !== $lead ) : ?>
			<p class="ghahghah-designer__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</header>

	<article <?php post_class( 'ghahghah-designer__article' ); ?>>
		<div class="ghahghah-designer__content entry-content">
			<?php the_content(); ?>
		</div>
	</article>
</div>
