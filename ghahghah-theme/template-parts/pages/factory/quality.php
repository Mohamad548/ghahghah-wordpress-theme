<?php
/**
 * Factory page quality cards.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$q_title = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_quality_title' ) );
$q_text  = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_quality_text' ) );
$items   = ghahghah_factory_page_quality_items();
?>
<section class="ghahghah-fp__quality" aria-labelledby="ghahghah-fp-quality-title">
	<header class="ghahghah-fp__panel-head">
		<span class="ghahghah-fp__panel-icon ghahghah-fp__panel-icon--red" aria-hidden="true">
			<?php ghahghah_the_factory_page_icon( 'stage-quality', array( 'modifiers' => array( 'panel' ) ) ); ?>
		</span>
		<div>
			<h2 id="ghahghah-fp-quality-title" class="ghahghah-fp__panel-title">
				<?php echo esc_html( '' !== $q_title ? $q_title : __( 'کیفیت در هر مرحله', 'ghahghah' ) ); ?>
			</h2>
			<?php if ( '' !== $q_text ) : ?>
				<p class="ghahghah-fp__panel-lead"><?php echo esc_html( $q_text ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<ul class="ghahghah-fp__quality-list list-reset">
		<?php foreach ( $items as $item ) : ?>
			<li class="ghahghah-fp__quality-card ghahghah-fp__quality-card--<?php echo esc_attr( $item['tone'] ); ?>">
				<span class="ghahghah-fp__quality-icon" aria-hidden="true">
					<?php ghahghah_the_factory_page_icon( $item['icon'], array( 'modifiers' => array( 'quality', $item['tone'] ) ) ); ?>
				</span>
				<div>
					<h3 class="ghahghah-fp__quality-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="ghahghah-fp__quality-text"><?php echo esc_html( $item['text'] ); ?></p>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
