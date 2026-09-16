<?php
/**
 * Contact page layout.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title      = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_page_title' ) );
$lead       = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_page_lead' ) );
$info_title = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_info_title' ) );
$info_text  = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_info_text' ) );
$map_text   = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_map_text' ) );
$cards      = ghahghah_get_contact_info_cards();
$corn       = ghahghah_get_contact_corn_image();
$map_share  = ghahghah_get_contact_map_share_url();
$map_embed  = ghahghah_get_contact_map_embed_url();
$page_title = get_the_title();
$has_core   = function_exists( 'ghahghah_core_render_contact_form' );
?>
<div class="ghahghah-contact-page__shell">
	<header class="ghahghah-contact-page__hero">
		<nav class="ghahghah-contact-page__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
			<a class="ghahghah-contact-page__crumb-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php ghahghah_the_contact_icon( 'home', array( 'modifiers' => array( 'muted' ) ) ); ?>
				<span><?php esc_html_e( 'خانه', 'ghahghah' ); ?></span>
			</a>
			<span class="ghahghah-contact-page__crumb-sep" aria-hidden="true">/</span>
			<span class="ghahghah-contact-page__crumb-current" aria-current="page"><?php echo esc_html( $page_title ); ?></span>
		</nav>

		<div class="ghahghah-contact-page__hero-copy">
			<?php if ( '' !== $title ) : ?>
				<h1 class="ghahghah-contact-page__title">
					<?php
					$safe = esc_html( $title );
					$safe = preg_replace(
						'/(قهقهه)/u',
						'<span class="ghahghah-contact-page__title-brand">$1</span>',
						$safe,
						1
					) ?? $safe;
					echo wp_kses( $safe, array( 'span' => array( 'class' => true ) ) );
					?>
				</h1>
			<?php else : ?>
				<h1 class="ghahghah-contact-page__title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p class="ghahghah-contact-page__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>

		<figure class="ghahghah-contact-page__corn" aria-hidden="true">
			<img
				src="<?php echo esc_url( $corn['src'] ); ?>"
				width="<?php echo esc_attr( (string) $corn['width'] ); ?>"
				height="<?php echo esc_attr( (string) $corn['height'] ); ?>"
				alt=""
				decoding="async"
				fetchpriority="high"
			/>
		</figure>
		<span class="ghahghah-contact-page__kernel ghahghah-contact-page__kernel--1" aria-hidden="true">
			<?php ghahghah_the_contact_icon( 'decor-corn-kernel' ); ?>
		</span>
		<span class="ghahghah-contact-page__kernel ghahghah-contact-page__kernel--2" aria-hidden="true">
			<?php ghahghah_the_contact_icon( 'decor-corn-kernel' ); ?>
		</span>
		<span class="ghahghah-contact-page__wave" aria-hidden="true">
			<?php ghahghah_the_contact_icon( 'decor-wave-ribbon' ); ?>
		</span>
	</header>

	<div class="ghahghah-contact-page__panel">
		<div class="ghahghah-contact-page__grid">
			<section class="ghahghah-contact-page__form-card" aria-labelledby="gh-contact-form-title">
				<?php if ( $has_core ) : ?>
					<?php
					\Ghahghah\Core\Forms\FormRenderer::render(
						'contact',
						array(
							'variant'    => 'page',
							'form_title' => (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_form_title' ),
							'form_text'  => (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_form_text' ),
						)
					);
					?>
				<?php else : ?>
					<p class="ghahghah-contact-page__unavailable" role="status">
						<?php esc_html_e( 'فرم تماس پس از فعال‌سازی افزونه Core در دسترس است.', 'ghahghah' ); ?>
					</p>
				<?php endif; ?>
			</section>

			<section class="ghahghah-contact-page__info" aria-labelledby="gh-contact-info-title">
				<header class="ghahghah-contact-page__info-head">
					<?php if ( '' !== $info_title ) : ?>
						<h2 id="gh-contact-info-title" class="ghahghah-contact-page__info-title"><?php echo esc_html( $info_title ); ?></h2>
					<?php endif; ?>
					<?php if ( '' !== $info_text ) : ?>
						<p class="ghahghah-contact-page__info-text"><?php echo esc_html( $info_text ); ?></p>
					<?php endif; ?>
				</header>

				<ul class="ghahghah-contact-page__cards">
					<?php foreach ( $cards as $card ) : ?>
						<li class="ghahghah-contact-page__card ghahghah-contact-page__card--<?php echo esc_attr( $card['tone'] ); ?><?php echo 'address' === $card['key'] ? ' ghahghah-contact-page__card--full' : ''; ?>">
							<span class="ghahghah-contact-page__card-icon" aria-hidden="true">
								<?php ghahghah_the_contact_icon( $card['icon'] ); ?>
							</span>
							<div class="ghahghah-contact-page__card-body">
								<p class="ghahghah-contact-page__card-label"><?php echo esc_html( $card['label'] ); ?></p>
								<?php if ( '' !== $card['href'] ) : ?>
									<a class="ghahghah-contact-page__card-value" href="<?php echo esc_url( $card['href'] ); ?>">
										<?php echo esc_html( $card['value'] ); ?>
									</a>
								<?php else : ?>
									<p class="ghahghah-contact-page__card-value"><?php echo nl2br( esc_html( $card['value'] ) ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php if ( '' !== $map_embed ) : ?>
					<div class="ghahghah-contact-page__map ghahghah-contact-page__map--live">
						<iframe
							class="ghahghah-contact-page__map-frame"
							src="<?php echo esc_url( $map_embed ); ?>"
							title="<?php echo esc_attr( '' !== $map_text ? $map_text : __( 'نقشه موقعیت', 'ghahghah' ) ); ?>"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							allowfullscreen
						></iframe>
						<?php if ( '' !== $map_share ) : ?>
							<a
								class="ghahghah-contact-page__map-link"
								href="<?php echo esc_url( $map_share ); ?>"
								target="_blank"
								rel="noopener noreferrer"
							>
								<span class="ghahghah-contact-page__map-pin" aria-hidden="true">
									<?php ghahghah_the_contact_icon( 'map-pin', array( 'modifiers' => array( 'map' ) ) ); ?>
								</span>
								<span><?php echo esc_html( '' !== $map_text ? $map_text : __( 'مشاهده در گوگل مپ', 'ghahghah' ) ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				<?php else : ?>
					<div class="ghahghah-contact-page__map" role="img" aria-label="<?php echo esc_attr( $map_text ); ?>">
						<span class="ghahghah-contact-page__map-pin" aria-hidden="true">
							<?php ghahghah_the_contact_icon( 'map-pin', array( 'modifiers' => array( 'map' ) ) ); ?>
						</span>
						<?php if ( '' !== $map_text ) : ?>
							<p class="ghahghah-contact-page__map-text"><?php echo esc_html( $map_text ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</section>
		</div>
	</div>
</div>
