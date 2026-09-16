<?php
/**
 * Template Name: معرفی کارخانه
 * Template Post Type: page
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main ghahghah-factory-page" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/pages/factory/page', 'factory' );
	endwhile;
	?>
</main>

<?php
get_footer();
