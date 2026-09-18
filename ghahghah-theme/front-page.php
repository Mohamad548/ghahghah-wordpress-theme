<?php
/**
 * Front page template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

$ghahghah_front_has_sections = ghahghah_should_render_hero()
	|| ghahghah_should_render_featured()
	|| ghahghah_should_render_factory()
	|| ghahghah_should_render_steps()
	|| ghahghah_should_render_collab()
	|| ghahghah_should_render_articles();
?>

<main id="main-content" class="site-main site-main--front" tabindex="-1">
	<?php if ( $ghahghah_front_has_sections ) : ?>
		<?php
		/*
		 * One document H1 for the homepage brand outline.
		 * Visually hidden while the hero/sections carry the visual identity —
		 * same pattern as image-led archive heroes. Not tied to slide, catalog, or core.
		 */
		?>
		<h1 class="screen-reader-text"><?php echo esc_html( function_exists( 'ghahghah_seo_home_h1' ) ? ghahghah_seo_home_h1() : get_bloginfo( 'name', 'display' ) ); ?></h1>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/hero/site', 'hero' ); ?>
	<?php get_template_part( 'template-parts/featured/site', 'featured' ); ?>
	<?php get_template_part( 'template-parts/factory/site', 'factory' ); ?>
	<?php get_template_part( 'template-parts/steps/site', 'steps' ); ?>
	<?php get_template_part( 'template-parts/collab/site', 'collab' ); ?>
	<?php get_template_part( 'template-parts/articles/site', 'articles' ); ?>

	<?php if ( ! $ghahghah_front_has_sections ) : ?>
		<section class="front-intro" aria-labelledby="front-intro-title">
			<div class="site-main__inner">
				<h1 id="front-intro-title" class="front-intro__title"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></h1>
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
