<?php
/**
 * Archive template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main" tabindex="-1">
	<div class="site-main__inner">
		<header class="archive-header">
			<?php the_archive_title( '<h1 class="archive-header__title">', '</h1>' ); ?>
			<?php the_archive_description( '<div class="archive-header__description">', '</div>' ); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="archive-list">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php get_template_part( 'template-parts/content/content', 'archive' ); ?>
				<?php endwhile; ?>
			</div>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
