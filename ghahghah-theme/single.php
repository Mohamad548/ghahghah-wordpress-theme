<?php
/**
 * Single post template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main" tabindex="-1">
	<div class="site-main__inner">
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php get_template_part( 'template-parts/content/content', 'single' ); ?>
		<?php endwhile; ?>
	</div>
</main>

<?php
get_footer();
