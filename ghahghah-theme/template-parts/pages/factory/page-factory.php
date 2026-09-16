<?php
/**
 * Factory intro page orchestrator.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ghahghah-fp">
	<div class="ghahghah-fp__shell">
		<?php get_template_part( 'template-parts/pages/factory/hero' ); ?>
		<?php get_template_part( 'template-parts/pages/factory/process' ); ?>
		<div class="ghahghah-fp__bottom">
			<?php get_template_part( 'template-parts/pages/factory/quality' ); ?>
			<?php get_template_part( 'template-parts/pages/factory/certificates' ); ?>
		</div>
	</div>
</div>
