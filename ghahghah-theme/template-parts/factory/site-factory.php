<?php
/**
 * Factory intro section markup.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_factory() ) {
	return;
}

$ghahghah_eyebrow        = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_eyebrow' ) );
$ghahghah_title          = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_title' ) );
$ghahghah_text           = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_text' ) );
$ghahghah_image          = ghahghah_get_factory_image();
$ghahghah_topics_heading = trim( (string) ghahghah_get_factory_mod( 'ghahghah_factory_topics_heading' ) );
$ghahghah_topics         = ghahghah_get_factory_topics();
$ghahghah_button         = ghahghah_get_factory_button();
$ghahghah_heading_id     = '' !== $ghahghah_title ? 'ghahghah-factory-title' : 'ghahghah-factory-heading';
?>
<section class="ghahghah-factory" aria-labelledby="<?php echo esc_attr( $ghahghah_heading_id ); ?>" data-ghahghah-factory>
	<div class="ghahghah-factory__shell">
		<div class="ghahghah-factory__card">
			<div class="ghahghah-factory__intro">
				<?php if ( '' !== $ghahghah_eyebrow ) : ?>
					<p class="ghahghah-factory__eyebrow"><?php echo esc_html( $ghahghah_eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $ghahghah_title ) : ?>
					<h2 id="ghahghah-factory-title" class="ghahghah-factory__title">
						<?php echo wp_kses( ghahghah_format_factory_title( $ghahghah_title ), array( 'span' => array( 'class' => true ) ) ); ?>
					</h2>
				<?php else : ?>
					<span id="ghahghah-factory-heading" class="screen-reader-text"><?php esc_html_e( 'معرفی کارخانه', 'ghahghah' ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $ghahghah_text ) : ?>
					<p class="ghahghah-factory__text"><?php echo esc_html( $ghahghah_text ); ?></p>
				<?php endif; ?>
			</div>

			<figure class="ghahghah-factory__media">
				<span class="ghahghah-factory__glow" aria-hidden="true"></span>
				<?php if ( null !== $ghahghah_image ) : ?>
					<img
						class="ghahghah-factory__img"
						src="<?php echo esc_url( $ghahghah_image['url'] ); ?>"
						<?php if ( '' !== $ghahghah_image['srcset'] ) : ?>
							srcset="<?php echo esc_attr( $ghahghah_image['srcset'] ); ?>"
							sizes="<?php echo esc_attr( $ghahghah_image['sizes'] ); ?>"
						<?php endif; ?>
						width="<?php echo esc_attr( (string) $ghahghah_image['width'] ); ?>"
						height="<?php echo esc_attr( (string) $ghahghah_image['height'] ); ?>"
						alt="<?php echo esc_attr( $ghahghah_image['alt'] ); ?>"
						loading="lazy"
						decoding="async"
					/>
				<?php else : ?>
					<div class="ghahghah-factory__placeholder" role="img" aria-label="<?php echo esc_attr__( 'تصویر کارخانه', 'ghahghah' ); ?>"></div>
				<?php endif; ?>
			</figure>

			<?php if ( '' !== $ghahghah_topics_heading || count( $ghahghah_topics ) > 0 ) : ?>
				<div class="ghahghah-factory__topics-block">
					<?php if ( '' !== $ghahghah_topics_heading ) : ?>
						<h3 class="ghahghah-factory__topics-heading"><?php echo esc_html( $ghahghah_topics_heading ); ?></h3>
					<?php endif; ?>
					<?php if ( count( $ghahghah_topics ) > 0 ) : ?>
						<ul class="ghahghah-factory__topics">
							<?php foreach ( $ghahghah_topics as $ghahghah_topic ) : ?>
								<li class="ghahghah-factory__topic">
									<span class="ghahghah-factory__topic-icon" aria-hidden="true">
										<?php ghahghah_the_factory_topic_icon( $ghahghah_topic['key'] ); ?>
									</span>
									<span class="ghahghah-factory__topic-label"><?php echo esc_html( $ghahghah_topic['label'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( null !== $ghahghah_button ) : ?>
				<a class="ghahghah-factory__cta" href="<?php echo esc_url( $ghahghah_button['url'] ); ?>">
					<span><?php echo esc_html( $ghahghah_button['label'] ); ?></span>
					<svg class="ghahghah-factory__cta-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
