<?php
/**
 * Site search results — posts + products.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

$query = get_search_query();
?>

<main id="main-content" class="site-main ghahghah-search" tabindex="-1">
	<div class="ghahghah-search__shell">
		<header class="ghahghah-search__header">
			<p class="ghahghah-search__eyebrow"><?php esc_html_e( 'نتایج جستجو', 'ghahghah' ); ?></p>
			<h1 class="ghahghah-search__title">
				<?php
				if ( '' !== $query ) {
					printf(
						/* translators: %s: search query */
						esc_html__( 'جستجو برای «%s»', 'ghahghah' ),
						esc_html( $query )
					);
				} else {
					esc_html_e( 'جستجو در سایت', 'ghahghah' );
				}
				?>
			</h1>
			<form class="ghahghah-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="ghahghah-search__field">
					<span class="screen-reader-text"><?php esc_html_e( 'جستجو در سایت', 'ghahghah' ); ?></span>
					<input
						type="search"
						name="s"
						value="<?php echo esc_attr( $query ); ?>"
						placeholder="<?php esc_attr_e( 'جستجوی محصول و مقاله...', 'ghahghah' ); ?>"
						enterkeyhint="search"
					/>
				</label>
				<button type="submit" class="ghahghah-search__submit"><?php esc_html_e( 'جستجو', 'ghahghah' ); ?></button>
			</form>
		</header>

		<?php if ( have_posts() ) : ?>
			<p class="ghahghah-search__count" aria-live="polite">
				<?php
				printf(
					/* translators: %d: number of results */
					esc_html( _n( '%d نتیجه یافت شد', '%d نتیجه یافت شد', (int) $GLOBALS['wp_query']->found_posts, 'ghahghah' ) ),
					(int) $GLOBALS['wp_query']->found_posts
				);
				?>
			</p>

			<div class="ghahghah-search__grid" role="list">
				<?php
				while ( have_posts() ) :
					the_post();
					$is_product = 'ghahghah_product' === get_post_type();
					$url        = get_permalink();
					$title      = get_the_title();
					$excerpt    = get_the_excerpt();
					$type_label = $is_product ? __( 'محصول', 'ghahghah' ) : __( 'مقاله', 'ghahghah' );
					?>
					<article class="ghahghah-search__card" role="listitem">
						<a class="ghahghah-search__card-media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail(
									'medium_large',
									array(
										'class'    => 'ghahghah-search__card-img',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
							} else {
								echo '<span class="ghahghah-search__card-placeholder" aria-hidden="true"></span>';
							}
							?>
						</a>
						<div class="ghahghah-search__card-body">
							<p class="ghahghah-search__card-type"><?php echo esc_html( $type_label ); ?></p>
							<h2 class="ghahghah-search__card-title">
								<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
							</h2>
							<?php if ( is_string( $excerpt ) && '' !== trim( $excerpt ) ) : ?>
								<p class="ghahghah-search__card-excerpt"><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
							<?php endif; ?>
						</div>
					</article>
					<?php
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
			<div class="ghahghah-search__empty" role="status">
				<p class="ghahghah-search__empty-title"><?php esc_html_e( 'نتیجه‌ای پیدا نشد', 'ghahghah' ); ?></p>
				<p class="ghahghah-search__empty-text"><?php esc_html_e( 'عبارت دیگری را امتحان کنید یا محصولات و مقالات را از فهرست‌ها ببینید.', 'ghahghah' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
