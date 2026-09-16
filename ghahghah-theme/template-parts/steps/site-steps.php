<?php
/**
 * Production steps section markup.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ghahghah_should_render_steps() ) {
	return;
}

$ghahghah_eyebrow = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_eyebrow' ) );
$ghahghah_title   = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_title' ) );
$ghahghah_text    = trim( (string) ghahghah_get_steps_mod( 'ghahghah_steps_text' ) );
$ghahghah_steps   = ghahghah_get_steps_items();
$ghahghah_heading = '' !== $ghahghah_title ? 'ghahghah-steps-title' : 'ghahghah-steps-heading';
?>
<section class="ghahghah-steps" aria-labelledby="<?php echo esc_attr( $ghahghah_heading ); ?>" data-ghahghah-steps>
	<div class="ghahghah-steps__shell">
		<header class="ghahghah-steps__header">
			<?php if ( '' !== $ghahghah_eyebrow ) : ?>
				<p class="ghahghah-steps__eyebrow"><?php echo esc_html( $ghahghah_eyebrow ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $ghahghah_title ) : ?>
				<h2 id="ghahghah-steps-title" class="ghahghah-steps__title"><?php echo esc_html( $ghahghah_title ); ?></h2>
			<?php else : ?>
				<span id="ghahghah-steps-heading" class="screen-reader-text"><?php esc_html_e( 'مراحل تولید محصول', 'ghahghah' ); ?></span>
			<?php endif; ?>

			<?php if ( '' !== $ghahghah_text ) : ?>
				<p class="ghahghah-steps__lead"><?php echo esc_html( $ghahghah_text ); ?></p>
			<?php endif; ?>
		</header>

		<ol class="ghahghah-steps__list">
			<?php foreach ( $ghahghah_steps as $ghahghah_index => $ghahghah_step ) : ?>
				<?php
				$ghahghah_num   = $ghahghah_index + 1;
				$ghahghah_panel = 'ghahghah-step-panel-' . $ghahghah_num;
				?>
				<li class="ghahghah-steps__item">
					<?php /* All open by default so content stays readable without JS. */ ?>
					<details class="ghahghah-steps__details" open data-ghahghah-step-details>
						<summary class="ghahghah-steps__summary">
							<span class="ghahghah-steps__num"><?php echo esc_html( ghahghah_format_step_number( $ghahghah_num ) ); ?></span>
							<span class="ghahghah-steps__name" id="<?php echo esc_attr( 'ghahghah-step-label-' . $ghahghah_num ); ?>">
								<?php echo esc_html( $ghahghah_step['title'] ); ?>
							</span>
							<span class="ghahghah-steps__chevron" aria-hidden="true">
								<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="m6 9 6 6 6-6"/></svg>
							</span>
						</summary>
						<div class="ghahghah-steps__body" id="<?php echo esc_attr( $ghahghah_panel ); ?>">
							<?php if ( '' !== $ghahghah_step['text'] ) : ?>
								<p class="ghahghah-steps__desc"><?php echo esc_html( $ghahghah_step['text'] ); ?></p>
							<?php endif; ?>
						</div>
					</details>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
