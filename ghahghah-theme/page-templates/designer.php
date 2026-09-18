<?php
/**
 * Template Name: معرفی طراح
 * Template Post Type: page
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main ghahghah-designer-page" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/designer/page', 'designer' );
	endwhile;
	?>
</main>

<?php
get_footer();
