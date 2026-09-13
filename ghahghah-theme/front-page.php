<?php
/**
 * Front page starter template.
 *
 * Verifies header, navigation, main content and footer wiring.
 * Full marketing layouts land in a later phase.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main site-main--front" tabindex="-1">
	<section class="front-intro" aria-labelledby="front-intro-title">
		<div class="site-main__inner">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<h1 id="front-intro-title" class="front-intro__title"><?php the_title(); ?></h1>
					<p class="front-intro__lead">
						<?php esc_html_e( 'نسخهٔ اولیه قالب شرکتی قهقهه آماده است. این صفحه صحت بارگذاری سربرگ، ناوبری، محتوا و پاورقی را نشان می‌دهد.', 'ghahghah' ); ?>
					</p>
					<?php if ( ! ghahghah_is_core_active() ) : ?>
						<p class="front-intro__notice" role="status">
							<?php esc_html_e( 'افزونهٔ Ghahghah Core فعال نیست. کاتالوگ محصولات پس از فعال‌سازی افزونه در دسترس خواهد بود.', 'ghahghah' ); ?>
						</p>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php else : ?>
				<h1 id="front-intro-title" class="front-intro__title"><?php bloginfo( 'name' ); ?></h1>
				<p class="front-intro__lead">
					<?php esc_html_e( 'نسخهٔ اولیه قالب شرکتی قهقهه آماده است. این صفحه صحت بارگذاری سربرگ، ناوبری، محتوا و پاورقی را نشان می‌دهد.', 'ghahghah' ); ?>
				</p>
				<?php if ( ! ghahghah_is_core_active() ) : ?>
					<p class="front-intro__notice" role="status">
						<?php esc_html_e( 'افزونهٔ Ghahghah Core فعال نیست. کاتالوگ محصولات پس از فعال‌سازی افزونه در دسترس خواهد بود.', 'ghahghah' ); ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( have_posts() ) : ?>
		<section class="front-content" aria-label="<?php esc_attr_e( 'محتوای صفحه اصلی', 'ghahghah' ); ?>">
			<div class="site-main__inner">
				<?php
				rewind_posts();
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
