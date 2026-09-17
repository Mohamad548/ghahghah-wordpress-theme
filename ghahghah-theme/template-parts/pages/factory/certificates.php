<?php
/**
 * Factory page certificate placeholders (no fake credentials).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$c_title = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_certs_title' ) );
$c_text  = trim( (string) ghahghah_get_factory_page_mod( 'ghahghah_factory_page_certs_text' ) );
?>
<section class="ghahghah-fp__certs" aria-labelledby="ghahghah-fp-certs-title">
	<header class="ghahghah-fp__panel-head">
		<span class="ghahghah-fp__panel-icon ghahghah-fp__panel-icon--blue" aria-hidden="true">
			<?php ghahghah_the_factory_page_icon( 'certificate-document', array( 'modifiers' => array( 'panel' ) ) ); ?>
		</span>
		<div>
			<h2 id="ghahghah-fp-certs-title" class="ghahghah-fp__panel-title">
				<?php echo esc_html( '' !== $c_title ? $c_title : __( 'مدارک و گواهینامه‌ها', 'ghahghah' ) ); ?>
			</h2>
			<?php if ( '' !== $c_text ) : ?>
				<p class="ghahghah-fp__panel-lead"><?php echo esc_html( $c_text ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<ul class="ghahghah-fp__certs-list list-reset">
		<?php for ( $i = 0; $i < 3; $i++ ) : ?>
			<li class="ghahghah-fp__cert-card">
				<span class="ghahghah-fp__cert-icon" aria-hidden="true">
					<?php ghahghah_the_factory_page_icon( 'certificate-document', array( 'modifiers' => array( 'cert' ) ) ); ?>
				</span>
				<h3 class="ghahghah-fp__cert-title"><?php esc_html_e( 'جایگاه گواهینامه', 'ghahghah' ); ?></h3>
				<p class="ghahghah-fp__cert-text"><?php esc_html_e( 'پس از دریافت مدارک رسمی تکمیل می‌شود.', 'ghahghah' ); ?></p>
			</li>
		<?php endfor; ?>
	</ul>
</section>
