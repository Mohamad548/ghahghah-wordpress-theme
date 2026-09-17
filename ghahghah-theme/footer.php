<?php
/**
 * Footer template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/footer/site', 'footer' );

ghahghah_the_bottom_nav();

wp_footer();
?>
</body>
</html>
