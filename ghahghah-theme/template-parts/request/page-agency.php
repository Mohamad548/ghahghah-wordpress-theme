<?php
/**
 * Agency request full page layout (redesigned).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow    = trim( (string) ghahghah_get_request_mod( 'ghahghah_agency_intro_eyebrow' ) );
$title      = trim( (string) ghahghah_get_request_mod( 'ghahghah_agency_intro_title' ) );
$text       = trim( (string) ghahghah_get_request_mod( 'ghahghah_agency_intro_text' ) );
$tagline    = trim( (string) ghahghah_get_request_mod( 'ghahghah_agency_hero_tagline' ) );
$motto      = trim( (string) ghahghah_get_request_mod( 'ghahghah_agency_footer_motto' ) );
$steps      = ghahghah_get_request_steps( 'agency' );
$hero       = ghahghah_get_agency_hero_image();
$has_core   = function_exists( 'ghahghah_core_render_agency_form' );
$page_title = get_the_title();
?>
<div class="ghahghah-agency-page__shell">
	<div class="ghahghah-agency-page__grid">
		<div class="ghahghah-agency-page__form-col">
			<div class="ghahghah-agency-page__form-card">
				<nav class="ghahghah-agency-page__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
					<a class="ghahghah-agency-page__crumb-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php ghahghah_the_form_icon( 'home', array( 'modifiers' => array( 'muted' ) ) ); ?>
						<span><?php esc_html_e( 'خانه', 'ghahghah' ); ?></span>
					</a>
					<span class="ghahghah-agency-page__crumb-sep" aria-hidden="true">/</span>
					<span class="ghahghah-agency-page__crumb-current" aria-current="page"><?php echo esc_html( $page_title ); ?></span>
				</nav>

				<?php if ( $has_core ) : ?>
					<?php
					\Ghahghah\Core\Forms\FormRenderer::render(
						'agency',
						array(
							'variant' => 'page',
						)
					);
					?>
				<?php else : ?>
					<p class="ghahghah-agency-page__unavailable" role="status">
						<?php esc_html_e( 'فرم نمایندگی پس از فعال‌سازی افزونه Core در دسترس است.', 'ghahghah' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<div class="ghahghah-agency-page__hero">
			<header class="ghahghah-agency-page__head">
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="ghahghah-agency-page__superhead">
						<span><?php echo esc_html( $eyebrow ); ?></span>
						<span class="ghahghah-agency-page__superhead-stroke" aria-hidden="true"></span>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $title ) : ?>
					<h1 class="ghahghah-agency-page__title">
						<?php
						$safe = esc_html( $title );
						$safe = preg_replace(
							'/(قهقهه)/u',
							'<span class="ghahghah-agency-page__title-brand">$1</span>',
							$safe,
							1
						) ?? $safe;
						echo wp_kses( $safe, array( 'span' => array( 'class' => true ) ) );
						?>
					</h1>
				<?php else : ?>
					<h1 class="ghahghah-agency-page__title"><?php the_title(); ?></h1>
				<?php endif; ?>

				<?php if ( '' !== $text ) : ?>
					<p class="ghahghah-agency-page__lead"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
			</header>

			<div class="ghahghah-agency-page__visual">
				<figure class="ghahghah-agency-page__visual-media">
					<span class="ghahghah-agency-page__deco ghahghah-agency-page__deco--leaf-1" aria-hidden="true"></span>
					<span class="ghahghah-agency-page__deco ghahghah-agency-page__deco--leaf-2" aria-hidden="true"></span>
					<span class="ghahghah-agency-page__deco ghahghah-agency-page__deco--snack ghahghah-agency-page__deco--snack-1" aria-hidden="true"></span>
					<span class="ghahghah-agency-page__deco ghahghah-agency-page__deco--snack ghahghah-agency-page__deco--snack-2" aria-hidden="true"></span>
					<img
						class="ghahghah-agency-page__product"
						src="<?php echo esc_url( $hero['src'] ); ?>"
						<?php if ( '' !== $hero['srcset'] ) : ?>
							srcset="<?php echo esc_attr( $hero['srcset'] ); ?>"
						<?php endif; ?>
						width="<?php echo esc_attr( (string) $hero['width'] ); ?>"
						height="<?php echo esc_attr( (string) $hero['height'] ); ?>"
						alt="<?php echo esc_attr( $title !== '' ? $title : $page_title ); ?>"
						decoding="async"
						fetchpriority="high"
					/>
					<?php if ( '' !== $tagline ) : ?>
						<p class="ghahghah-agency-page__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
				</figure>

				<ol class="ghahghah-agency-page__steps" aria-label="<?php esc_attr_e( 'مراحل درخواست نمایندگی', 'ghahghah' ); ?>">
					<?php foreach ( $steps as $i => $step ) : ?>
						<li class="ghahghah-agency-page__step">
							<span class="ghahghah-agency-page__step-badge" aria-hidden="true"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
							<div class="ghahghah-agency-page__step-main">
								<span class="ghahghah-agency-page__step-icon" aria-hidden="true">
									<?php ghahghah_the_form_icon( $step['icon'], array( 'modifiers' => array( 'step' ) ) ); ?>
								</span>
								<span class="ghahghah-agency-page__step-body">
									<span class="ghahghah-agency-page__step-title"><?php echo esc_html( $step['title'] ); ?></span>
									<span class="ghahghah-agency-page__step-text"><?php echo esc_html( $step['text'] ); ?></span>
								</span>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>

			<?php if ( '' !== $motto ) : ?>
				<p class="ghahghah-agency-page__motto">
					<span><?php echo esc_html( $motto ); ?></span>
					<span class="ghahghah-agency-page__motto-stroke" aria-hidden="true"></span>
				</p>
			<?php endif; ?>
		</div>
	</div>
</div>
