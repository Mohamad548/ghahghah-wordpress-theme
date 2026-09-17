<?php
/**
 * Template Name: حریم خصوصی
 * Template Post Type: page
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main ghahghah-privacy-page" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/privacy/page', 'privacy' );
	endwhile;
	?>
</main>

<?php
get_footer();
