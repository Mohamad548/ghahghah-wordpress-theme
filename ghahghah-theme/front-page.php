<?php
/**
 * Front page template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main site-main--front" tabindex="-1">
	<?php get_template_part( 'template-parts/hero/site', 'hero' ); ?>
	<?php get_template_part( 'template-parts/featured/site', 'featured' ); ?>
	<?php get_template_part( 'template-parts/factory/site', 'factory' ); ?>
	<?php get_template_part( 'template-parts/steps/site', 'steps' ); ?>
	<?php get_template_part( 'template-parts/collab/site', 'collab' ); ?>
	<?php get_template_part( 'template-parts/articles/site', 'articles' ); ?>

	<?php if ( have_posts() ) : ?>
		<section class="front-content" aria-label="<?php esc_attr_e( 'محتوای صفحه اصلی', 'ghahghah' ); ?>">
			<div class="site-main__inner">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</section>
	<?php elseif ( ! ghahghah_should_render_hero() && ! ghahghah_should_render_featured() && ! ghahghah_should_render_factory() && ! ghahghah_should_render_steps() && ! ghahghah_should_render_collab() && ! ghahghah_should_render_articles() ) : ?>
		<section class="front-intro" aria-labelledby="front-intro-title">
			<div class="site-main__inner">
				<h1 id="front-intro-title" class="front-intro__title"><?php bloginfo( 'name' ); ?></h1>
				<?php if ( ! ghahghah_is_core_active() ) : ?>
					<p class="front-intro__notice" role="status">
						<?php esc_html_e( 'افزونهٔ Ghahghah Core فعال نیست. کاتالوگ محصولات پس از فعال‌سازی افزونه در دسترس خواهد بود.', 'ghahghah' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
