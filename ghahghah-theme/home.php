<?php
/**
 * Blog posts index (page_for_posts) template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

$defaults = ghahghah_blog_archive_defaults();
$state    = ghahghah_blog_archive_request_state();
$banner   = ghahghah_get_blog_archive_custom_banner();
?>

<main id="main-content" class="site-main ghahghah-blog-archive<?php echo null !== $banner ? ' ghahghah-blog-archive--custom-banner' : ''; ?>" tabindex="-1">
	<?php get_template_part( 'template-parts/blog-archive/hero' ); ?>

	<div class="ghahghah-blog-archive__shell">
		<nav class="ghahghah-blog-archive__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
			<a class="ghahghah-blog-archive__crumb-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php ghahghah_the_blog_archive_icon( 'home', array( 'modifiers' => array( 'muted' ) ) ); ?>
				<span><?php echo esc_html( $defaults['crumb_home'] ); ?></span>
			</a>
			<span class="ghahghah-blog-archive__crumb-sep" aria-hidden="true">/</span>
			<span class="ghahghah-blog-archive__crumb-current" aria-current="page"><?php echo esc_html( $defaults['crumb_current'] ); ?></span>
		</nav>

		<?php get_template_part( 'template-parts/blog-archive/toolbar' ); ?>
		<?php get_template_part( 'template-parts/blog-archive/chips' ); ?>

		<?php if ( have_posts() ) : ?>
			<div class="ghahghah-blog-archive__grid" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/blog-archive/card' );
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( 'قبلی', 'ghahghah' ),
					'next_text' => __( 'بعدی', 'ghahghah' ),
				)
			);
			?>
		<?php else : ?>
			<div class="ghahghah-blog-archive__empty" role="status">
				<p class="ghahghah-blog-archive__empty-title"><?php echo esc_html( $defaults['empty_title'] ); ?></p>
				<p class="ghahghah-blog-archive__empty-text"><?php echo esc_html( $defaults['empty_text'] ); ?></p>
				<?php if ( 'all' !== $state['cat'] || '' !== $state['q'] ) : ?>
					<a class="ghahghah-blog-archive__empty-reset" href="<?php echo esc_url( ghahghah_blog_archive_url() ); ?>">
						<?php esc_html_e( 'نمایش همه مقالات', 'ghahghah' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="ghahghah-blog-archive__motto" aria-hidden="true">
			<p class="ghahghah-blog-archive__motto-left">
				<?php ghahghah_the_blog_archive_icon( 'heart-outline' ); ?>
				<span><?php echo esc_html( $defaults['footer_left'] ); ?></span>
			</p>
			<p class="ghahghah-blog-archive__motto-right">
				<?php ghahghah_the_blog_archive_icon( 'leaf' ); ?>
				<span><?php echo esc_html( $defaults['footer_right'] ); ?></span>
			</p>
		</div>
	</div>
</main>

<?php
get_footer();
