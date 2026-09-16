<?php
/**
 * Factory page production process strip.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$process_title = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_process_title' ) );
$process_text  = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_process_text' ) );
$stages        = ghahghah_factory_page_process_stages();
?>
<section class="ghahghah-fp__process" aria-labelledby="ghahghah-fp-process-title">
	<header class="ghahghah-fp__section-head">
		<h2 id="ghahghah-fp-process-title" class="ghahghah-fp__section-title">
			<?php echo esc_html( '' !== $process_title ? $process_title : __( 'مراحل تولید محصول', 'ghahghah' ) ); ?>
		</h2>
		<?php if ( '' !== $process_text ) : ?>
			<p class="ghahghah-fp__section-lead"><?php echo esc_html( $process_text ); ?></p>
		<?php endif; ?>
	</header>

	<ol class="ghahghah-fp__stages">
		<?php foreach ( $stages as $index => $stage ) : ?>
			<?php if ( $index > 0 ) : ?>
				<li class="ghahghah-fp__stage-chevron" aria-hidden="true">
					<?php ghahghah_the_factory_page_icon( 'arrow-chevron', array( 'modifiers' => array( 'chevron' ) ) ); ?>
				</li>
			<?php endif; ?>
			<li class="ghahghah-fp__stage ghahghah-fp__stage--<?php echo esc_attr( $stage['tone'] ); ?>">
				<span class="ghahghah-fp__stage-disc" aria-hidden="true">
					<?php ghahghah_the_factory_page_icon( $stage['icon'], array( 'modifiers' => array( 'stage', $stage['tone'] ) ) ); ?>
				</span>
				<span class="ghahghah-fp__stage-title"><?php echo esc_html( $stage['title'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ol>
</section>
