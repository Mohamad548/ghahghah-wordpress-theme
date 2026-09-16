<?php
/**
 * Wholesale / agency collab CTA section.
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

$ghahghah_eyebrow = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_eyebrow' ) );
$ghahghah_title   = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_title' ) );
$ghahghah_text    = trim( (string) ghahghah_get_collab_mod( 'ghahghah_collab_text' ) );
$ghahghah_cards   = ghahghah_get_collab_cards();
$ghahghah_heading = '' !== $ghahghah_title ? 'ghahghah-collab-title' : 'ghahghah-collab-heading';
$ghahghah_has_inline = false;
foreach ( $ghahghah_cards as $ghahghah_card_check ) {
	if ( empty( $ghahghah_card_check['url'] ) && ! empty( $ghahghah_card_check['form_html'] ) ) {
		$ghahghah_has_inline = true;
		break;
	}
}
?>
<section class="ghahghah-collab" aria-labelledby="<?php echo esc_attr( $ghahghah_heading ); ?>" <?php echo $ghahghah_has_inline ? 'data-ghahghah-collab' : ''; ?>>
	<div class="ghahghah-collab__shell">
		<div class="ghahghah-collab__frame">
			<header class="ghahghah-collab__header">
				<?php if ( '' !== $ghahghah_eyebrow ) : ?>
					<p class="ghahghah-collab__eyebrow"><?php echo esc_html( $ghahghah_eyebrow ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $ghahghah_title ) : ?>
					<h2 id="ghahghah-collab-title" class="ghahghah-collab__title"><?php echo esc_html( $ghahghah_title ); ?></h2>
				<?php else : ?>
					<span id="ghahghah-collab-heading" class="screen-reader-text"><?php esc_html_e( 'خرید عمده و درخواست نمایندگی', 'ghahghah' ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $ghahghah_text ) : ?>
					<p class="ghahghah-collab__lead"><?php echo esc_html( $ghahghah_text ); ?></p>
				<?php endif; ?>
			</header>

			<div class="ghahghah-collab__grid">
				<?php foreach ( $ghahghah_cards as $ghahghah_card ) : ?>
					<article class="ghahghah-collab__card ghahghah-collab__card--<?php echo esc_attr( $ghahghah_card['type'] ); ?>">
						<div class="ghahghah-collab__card-copy">
							<h3 class="ghahghah-collab__card-title"><?php echo esc_html( $ghahghah_card['title'] ); ?></h3>
							<?php if ( '' !== $ghahghah_card['text'] ) : ?>
								<p class="ghahghah-collab__card-text"><?php echo esc_html( $ghahghah_card['text'] ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( ! empty( $ghahghah_card['url'] ) ) : ?>
							<a class="ghahghah-collab__btn" href="<?php echo esc_url( (string) $ghahghah_card['url'] ); ?>">
								<span><?php echo esc_html( $ghahghah_card['button'] ); ?></span>
								<svg class="ghahghah-collab__btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
							</a>
						<?php else : ?>
							<button
								type="button"
								class="ghahghah-collab__btn"
								data-ghahghah-collab-open="<?php echo esc_attr( $ghahghah_card['form_id'] ); ?>"
								aria-controls="<?php echo esc_attr( $ghahghah_card['form_id'] ); ?>"
								aria-expanded="false"
							>
								<span><?php echo esc_html( $ghahghah_card['button'] ); ?></span>
								<svg class="ghahghah-collab__btn-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
							</button>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if ( $ghahghah_has_inline ) : ?>
				<?php foreach ( $ghahghah_cards as $ghahghah_card ) : ?>
					<?php if ( ! empty( $ghahghah_card['url'] ) || empty( $ghahghah_card['form_html'] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<section
						id="<?php echo esc_attr( $ghahghah_card['form_id'] ); ?>"
						class="ghahghah-collab__form-panel"
						data-ghahghah-collab-panel
						hidden
						aria-labelledby="<?php echo esc_attr( $ghahghah_card['heading_id'] ); ?>"
					>
						<div class="ghahghah-collab__form-head">
							<h3 id="<?php echo esc_attr( $ghahghah_card['heading_id'] ); ?>" class="ghahghah-collab__form-title" tabindex="-1">
								<?php echo esc_html( $ghahghah_card['title'] ); ?>
							</h3>
							<button type="button" class="ghahghah-collab__form-close" data-ghahghah-collab-close aria-label="<?php esc_attr_e( 'بستن فرم', 'ghahghah' ); ?>">×</button>
						</div>
						<div class="ghahghah-collab__form-body">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted Core/filter markup only.
							echo $ghahghah_card['form_html'];
							?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
