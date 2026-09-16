<?php
/**
 * Wholesale request full page layout (redesigned).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_intro_title' ) );
$text       = trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_intro_text' ) );
$badge      = trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_hero_badge' ) );
$motto      = trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_footer_motto' ) );
$steps      = ghahghah_get_request_steps( 'wholesale' );
$hero       = ghahghah_get_wholesale_hero_image();
$page_title = get_the_title();
$has_core   = function_exists( 'ghahghah_core_render_wholesale_form' );
$preselect  = absint( wp_unslash( $_GET['product'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( $preselect && ! ghahghah_is_valid_request_product( $preselect ) ) {
	$preselect = 0;
}
?>
<div class="ghahghah-wholesale-page__shell">
	<header class="ghahghah-wholesale-page__head">
		<nav class="ghahghah-wholesale-page__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
			<span class="ghahghah-wholesale-page__crumb-sep" aria-hidden="true">/</span>
			<span class="ghahghah-wholesale-page__crumb-current" aria-current="page"><?php echo esc_html( $page_title ); ?></span>
		</nav>

		<?php if ( '' !== $title ) : ?>
			<h1 class="ghahghah-wholesale-page__title">
				<span class="ghahghah-wholesale-page__title-accent" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
				</span>
				<span class="ghahghah-wholesale-page__title-text">
					<?php
					$safe = esc_html( $title );
					$safe = preg_replace(
						'/(خرید عمده)/u',
						'<span class="ghahghah-wholesale-page__title-brand">$1</span>',
						$safe,
						1
					) ?? $safe;
					echo wp_kses( $safe, array( 'span' => array( 'class' => true ) ) );
					?>
				</span>
				<span class="ghahghah-wholesale-page__title-accent ghahghah-wholesale-page__title-accent--flip" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent', 'mirrored' ) ) ); ?>
				</span>
			</h1>
		<?php else : ?>
			<h1 class="ghahghah-wholesale-page__title"><?php the_title(); ?></h1>
		<?php endif; ?>

		<?php if ( '' !== $text ) : ?>
			<p class="ghahghah-wholesale-page__lead"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</header>

	<div class="ghahghah-wholesale-page__grid">
		<div class="ghahghah-wholesale-page__form-col">
			<div class="ghahghah-wholesale-page__form-card">
				<?php if ( $has_core ) : ?>
					<?php
					\Ghahghah\Core\Forms\FormRenderer::render(
						'wholesale',
						array(
							'variant'           => 'page',
							'preselect_product' => $preselect,
						)
					);
					?>
				<?php else : ?>
					<p class="ghahghah-wholesale-page__unavailable" role="status">
						<?php esc_html_e( 'فرم خرید عمده پس از فعال‌سازی افزونه Core در دسترس است.', 'ghahghah' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="ghahghah-wholesale-page__hero">
			<figure class="ghahghah-wholesale-page__visual">
				<span class="ghahghah-wholesale-page__splash ghahghah-wholesale-page__splash--1" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'decor-splash', array( 'modifiers' => array( 'splash' ) ) ); ?>
				</span>
				<span class="ghahghah-wholesale-page__splash ghahghah-wholesale-page__splash--2" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'decor-splash', array( 'modifiers' => array( 'splash' ) ) ); ?>
				</span>
				<img
					class="ghahghah-wholesale-page__product"
					src="<?php echo esc_url( $hero['src'] ); ?>"
					width="<?php echo esc_attr( (string) $hero['width'] ); ?>"
					height="<?php echo esc_attr( (string) $hero['height'] ); ?>"
					alt="<?php echo esc_attr( '' !== $badge ? $badge : $title ); ?>"
					decoding="async"
					fetchpriority="high"
				/>
				<?php if ( '' !== $badge ) : ?>
					<span class="ghahghah-wholesale-page__badge"><?php echo esc_html( $badge ); ?></span>
				<?php endif; ?>
			</figure>
		</div>
	</div>

	<ol class="ghahghah-wholesale-page__steps" aria-label="<?php esc_attr_e( 'مراحل درخواست خرید عمده', 'ghahghah' ); ?>">
		<?php foreach ( $steps as $i => $step ) : ?>
			<?php
			$tone = isset( $step['tone'] ) ? (string) $step['tone'] : 'pink';
			?>
			<li class="ghahghah-wholesale-page__step ghahghah-wholesale-page__step--<?php echo esc_attr( $tone ); ?>">
				<span class="ghahghah-wholesale-page__step-badge" aria-hidden="true"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
				<span class="ghahghah-wholesale-page__step-icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( $step['icon'], array( 'modifiers' => array( 'step' ) ) ); ?>
				</span>
				<span class="ghahghah-wholesale-page__step-body">
					<span class="ghahghah-wholesale-page__step-title"><?php echo esc_html( $step['title'] ); ?></span>
					<span class="ghahghah-wholesale-page__step-text"><?php echo esc_html( $step['text'] ); ?></span>
				</span>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php if ( '' !== $motto ) : ?>
		<p class="ghahghah-wholesale-page__motto"><?php echo esc_html( $motto ); ?></p>
	<?php endif; ?>
</div>
