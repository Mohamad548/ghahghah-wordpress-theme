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
$profile    = function_exists( 'ghahghah_get_designer_profile_url' ) ? ghahghah_get_designer_profile_url() : '';
$cert       = function_exists( 'ghahghah_get_designer_certificate_url' ) ? ghahghah_get_designer_certificate_url() : '';
?>
<div class="ghahghah-designer__shell">
	<nav class="ghahghah-designer__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
		<span class="ghahghah-designer__crumb-sep" aria-hidden="true">/</span>
		<span aria-current="page"><?php echo esc_html( '' !== $page_title ? $page_title : __( 'محمد محمودی', 'ghahghah' ) ); ?></span>
	</nav>

	<header class="ghahghah-designer__intro">
		<?php if ( '' !== $profile ) : ?>
			<figure class="ghahghah-designer__portrait">
				<img
					src="<?php echo esc_url( $profile ); ?>"
					alt="<?php echo esc_attr__( 'محمد محمودی', 'ghahghah' ); ?>"
					width="160"
					height="160"
					loading="eager"
					decoding="async"
				/>
			</figure>
		<?php endif; ?>
		<div class="ghahghah-designer__intro-copy">
			<p class="ghahghah-designer__eyebrow"><?php esc_html_e( 'توسعه و طراحی وب‌سایت قهقهه', 'ghahghah' ); ?></p>
			<h1 class="ghahghah-designer__title">
				<?php echo esc_html( '' !== $page_title ? $page_title : __( 'محمد محمودی', 'ghahghah' ) ); ?>
			</h1>
			<p class="ghahghah-designer__role"><?php esc_html_e( 'توسعه‌دهنده فرانت‌اند', 'ghahghah' ); ?></p>
			<?php if ( '' !== $lead ) : ?>
				<p class="ghahghah-designer__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<article <?php post_class( 'ghahghah-designer__article' ); ?>>
		<div class="ghahghah-designer__content entry-content">
			<?php the_content(); ?>
		</div>

		<?php if ( '' !== $cert ) : ?>
			<figure class="ghahghah-designer__certificate">
				<img
					class="ghahghah-designer__certificate-img"
					src="<?php echo esc_url( $cert ); ?>"
					alt="<?php echo esc_attr__( 'گواهینامه دوره React — مکتب شریف — ۱۴۰۲', 'ghahghah' ); ?>"
					width="1600"
					height="1132"
					loading="lazy"
					decoding="async"
				/>
				<figcaption class="ghahghah-designer__certificate-cap">
					<?php esc_html_e( 'گواهینامه دوره React — مکتب شریف — ۱۴۰۲ / ۲۰۲۳ (+۴۰۰ ساعت)', 'ghahghah' ); ?>
				</figcaption>
			</figure>
		<?php endif; ?>
	</article>
</div>
