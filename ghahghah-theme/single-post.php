<?php
/**
 * Single blog post (article) template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();

while ( have_posts() ) :
	the_post();
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		continue;
	}

	$defaults   = ghahghah_single_article_defaults();
	$hero       = ghahghah_single_article_hero_image( (int) $post->ID );
	$minutes    = ghahghah_single_article_reading_minutes( (int) $post->ID );
	$content    = apply_filters( 'the_content', $post->post_content );
	$toc        = ghahghah_single_article_parse_headings( is_string( $content ) ? $content : '' );
	$cats       = get_the_category( (int) $post->ID );
	$blog_url   = function_exists( 'ghahghah_get_blog_archive_url' ) ? ghahghah_get_blog_archive_url() : home_url( '/' );
	$excerpt    = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
	$related    = ghahghah_get_related_articles( (int) $post->ID, 3 );
	$products   = ghahghah_single_article_sidebar_products( 2 );
	$archive_p  = function_exists( 'ghahghah_get_products_archive_url' ) ? ghahghah_get_products_archive_url() : '';
	$wholesale  = function_exists( 'ghahghah_get_wholesale_form_url' ) ? ghahghah_get_wholesale_form_url() : '';
	$agency     = function_exists( 'ghahghah_get_agency_form_url' ) ? ghahghah_get_agency_form_url() : '';
	$deco_bowl  = ghahghah_single_article_image_url( 'bowl-of-real-snacks-transparent-optimized.webp' );
	$deco_corn  = ghahghah_single_article_image_url( 'corn-and-real-snacks-transparent-optimized.webp' );
	$deco_cluster = ghahghah_single_article_image_url( 'decorative-snack-cluster-transparent-optimized.webp' );
	?>
	<main id="main-content" class="site-main ghahghah-single-article" tabindex="-1">
		<article <?php post_class( 'ghahghah-single-article__article' ); ?>>
			<div class="ghahghah-single-article__shell">
				<nav class="ghahghah-single-article__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php ghahghah_the_single_article_icon( 'home' ); ?>
						<span><?php esc_html_e( 'خانه', 'ghahghah' ); ?></span>
					</a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $blog_url ); ?>"><?php echo esc_html( $defaults['crumb_blog'] ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>

				<figure class="ghahghah-single-article__hero">
					<img
						class="ghahghah-single-article__hero-img"
						src="<?php echo esc_url( $hero['url'] ); ?>"
						alt="<?php echo esc_attr( $hero['alt'] !== '' ? $hero['alt'] : get_the_title() ); ?>"
						width="1500"
						height="560"
						decoding="async"
						fetchpriority="high"
					/>
					<span class="ghahghah-single-article__hero-overlay" aria-hidden="true">
						<?php echo esc_html( $defaults['hero_overlay'] ); ?>
					</span>
					<img class="ghahghah-single-article__hero-deco" src="<?php echo esc_url( $deco_cluster ); ?>" alt="" width="180" height="140" loading="lazy" decoding="async" aria-hidden="true" />
				</figure>

				<header class="ghahghah-single-article__header">
					<?php if ( ! empty( $cats ) && $cats[0] instanceof WP_Term ) : ?>
						<p class="ghahghah-single-article__badge">
							<?php ghahghah_the_single_article_icon( 'category' ); ?>
							<span><?php echo esc_html( $cats[0]->name ); ?></span>
						</p>
					<?php endif; ?>

					<h1 class="ghahghah-single-article__title"><?php the_title(); ?></h1>

					<?php if ( is_string( $excerpt ) && '' !== trim( wp_strip_all_tags( $excerpt ) ) ) : ?>
						<p class="ghahghah-single-article__subtitle"><?php echo esc_html( trim( wp_strip_all_tags( $excerpt ) ) ); ?></p>
					<?php endif; ?>

					<ul class="ghahghah-single-article__meta">
						<li>
							<?php ghahghah_the_single_article_icon( 'calendar' ); ?>
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</li>
						<li>
							<?php ghahghah_the_single_article_icon( 'clock' ); ?>
							<span><?php echo esc_html( (string) $minutes . ' ' . $defaults['read_suffix'] ); ?></span>
						</li>
						<li>
							<?php ghahghah_the_single_article_icon( 'book-open' ); ?>
							<span><?php echo esc_html( get_the_author() ); ?></span>
						</li>
					</ul>
				</header>

				<div class="ghahghah-single-article__layout">
					<aside class="ghahghah-single-article__sidebar">
						<?php if ( $toc ) : ?>
							<details class="ghahghah-single-article__toc" open>
								<summary class="ghahghah-single-article__toc-summary">
									<span class="ghahghah-single-article__toc-title">
										<?php ghahghah_the_single_article_icon( 'list' ); ?>
										<span><?php echo esc_html( $defaults['toc_title'] ); ?></span>
									</span>
									<span class="ghahghah-single-article__toc-chevron" aria-hidden="true">
										<?php ghahghah_the_single_article_icon( 'chevron-down' ); ?>
									</span>
								</summary>
								<nav class="ghahghah-single-article__toc-nav" aria-label="<?php echo esc_attr( $defaults['toc_title'] ); ?>">
									<ol>
										<?php foreach ( $toc as $item ) : ?>
											<li class="ghahghah-single-article__toc-item ghahghah-single-article__toc-item--h<?php echo esc_attr( (string) $item['level'] ); ?>">
												<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ol>
								</nav>
							</details>
						<?php endif; ?>

						<?php if ( $products ) : ?>
							<div class="ghahghah-single-article__side-products">
								<p class="ghahghah-single-article__side-products-title">
									<?php ghahghah_the_single_article_icon( 'product' ); ?>
									<span><?php echo esc_html( $defaults['related_products'] ); ?></span>
								</p>
								<ul class="ghahghah-single-article__side-products-list">
									<?php foreach ( $products as $product ) : ?>
										<?php
										if ( ! $product instanceof WP_Post ) {
											continue;
										}
										$purl  = get_permalink( $product );
										$label = function_exists( 'ghahghah_product_nav_label' )
											? ghahghah_product_nav_label( $product )
											: get_the_title( $product );
										$thumb = (int) get_post_thumbnail_id( $product );
										?>
										<li>
											<a class="ghahghah-single-article__side-product" href="<?php echo esc_url( is_string( $purl ) ? $purl : '' ); ?>">
												<span class="ghahghah-single-article__side-product-media">
													<?php if ( $thumb > 0 ) : ?>
														<?php
														echo wp_get_attachment_image(
															$thumb,
															'medium',
															false,
															array(
																'loading'  => 'lazy',
																'decoding' => 'async',
																'alt'      => '',
															)
														);
														?>
													<?php endif; ?>
												</span>
												<span class="ghahghah-single-article__side-product-title"><?php echo esc_html( is_string( $label ) ? $label : '' ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php if ( '' !== $archive_p ) : ?>
									<a class="ghahghah-single-article__side-products-all" href="<?php echo esc_url( $archive_p ); ?>">
										<span><?php echo esc_html( $defaults['all_products'] ); ?></span>
										<?php ghahghah_the_single_article_icon( 'arrow-left' ); ?>
									</a>
								<?php endif; ?>
								<img class="ghahghah-single-article__side-deco" src="<?php echo esc_url( $deco_bowl ); ?>" alt="" width="140" height="120" loading="lazy" decoding="async" aria-hidden="true" />
							</div>
						<?php endif; ?>
					</aside>

					<div class="ghahghah-single-article__content entry-content">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
					</div>
				</div>

				<section class="ghahghah-single-article__cta" aria-labelledby="ghahghah-sa-cta-title">
					<img class="ghahghah-single-article__cta-deco" src="<?php echo esc_url( $deco_corn ); ?>" alt="" width="200" height="160" loading="lazy" decoding="async" aria-hidden="true" />
					<h2 id="ghahghah-sa-cta-title" class="ghahghah-single-article__cta-title"><?php echo esc_html( $defaults['cta_title'] ); ?></h2>
					<div class="ghahghah-single-article__cta-actions">
						<?php if ( '' !== $wholesale ) : ?>
							<a class="ghahghah-single-article__cta-btn ghahghah-single-article__cta-btn--wholesale" href="<?php echo esc_url( $wholesale ); ?>">
								<?php ghahghah_the_single_article_icon( 'wholesale' ); ?>
								<span><?php echo esc_html( $defaults['cta_wholesale'] ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( '' !== $agency ) : ?>
							<a class="ghahghah-single-article__cta-btn ghahghah-single-article__cta-btn--agency" href="<?php echo esc_url( $agency ); ?>">
								<?php ghahghah_the_single_article_icon( 'agency' ); ?>
								<span><?php echo esc_html( $defaults['cta_agency'] ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</section>

				<?php if ( $related ) : ?>
					<section class="ghahghah-single-article__related" aria-labelledby="ghahghah-sa-related-title">
						<h2 id="ghahghah-sa-related-title" class="ghahghah-single-article__related-title"><?php echo esc_html( $defaults['related_title'] ); ?></h2>
						<div class="ghahghah-single-article__related-grid" role="list">
							<?php foreach ( $related as $item ) : ?>
								<?php
								if ( ! $item instanceof WP_Post ) {
									continue;
								}
								$url   = get_permalink( $item );
								$mins  = ghahghah_single_article_reading_minutes( (int) $item->ID );
								$thumb = (int) get_post_thumbnail_id( $item );
								?>
								<a class="ghahghah-single-article__related-card" role="listitem" href="<?php echo esc_url( is_string( $url ) ? $url : '' ); ?>">
									<span class="ghahghah-single-article__related-media">
										<?php if ( $thumb > 0 ) : ?>
											<?php
											echo wp_get_attachment_image(
												$thumb,
												'ghahghah-card',
												false,
												array(
													'loading'  => 'lazy',
													'decoding' => 'async',
													'alt'      => '',
												)
											);
											?>
										<?php else : ?>
											<img src="<?php echo esc_url( ghahghah_single_article_image_url( 'article-hero-banner-real-snack-optimized.webp' ) ); ?>" alt="" loading="lazy" decoding="async" />
										<?php endif; ?>
									</span>
									<span class="ghahghah-single-article__related-card-title"><?php echo esc_html( get_the_title( $item ) ); ?></span>
									<span class="ghahghah-single-article__related-card-meta">
										<?php ghahghah_the_single_article_icon( 'clock' ); ?>
										<span><?php echo esc_html( (string) $mins . ' ' . $defaults['read_suffix'] ); ?></span>
									</span>
								</a>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>
			</div>
		</article>
	</main>
	<?php
endwhile;

get_footer();
