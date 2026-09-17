<?php
/**
 * 404 template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

status_header( 404 );
nocache_headers();

get_header();

$ghahghah_products_url = ghahghah_404_products_url();
$ghahghah_404_title    = __( 'اوه! این صفحه پیدا نشد', 'ghahghah' );
?>

<main id="main-content" class="site-main ghahghah-404" tabindex="-1">
	<section class="ghahghah-404__section" aria-labelledby="ghahghah-404-title">
		<div class="ghahghah-404__blobs" aria-hidden="true">
			<span class="ghahghah-404__blob ghahghah-404__blob--top">
				<?php ghahghah_the_404_icon( 'bg-blob-top-left', array( 'class' => 'gg-404-icon--blob-top' ) ); ?>
			</span>
			<span class="ghahghah-404__blob ghahghah-404__blob--soft">
				<?php ghahghah_the_404_icon( 'bg-blob-soft', array( 'class' => 'gg-404-icon--blob-soft' ) ); ?>
			</span>
		</div>

		<div class="ghahghah-404__shell">
			<div class="ghahghah-404__grid">
				<div class="ghahghah-404__code-wrap">
					<span class="ghahghah-404__rays" aria-hidden="true">
						<?php ghahghah_the_404_icon( 'accent-rays', array( 'class' => 'gg-404-icon--rays' ) ); ?>
					</span>
					<p class="ghahghah-404__code" aria-hidden="true">۴۰۴</p>
				</div>

				<div class="ghahghah-404__visual" aria-hidden="true">
					<?php get_template_part( 'template-parts/404/collage' ); ?>
				</div>

				<h1 id="ghahghah-404-title" class="ghahghah-404__title">
					<?php echo wp_kses( ghahghah_format_404_title( $ghahghah_404_title ), array( 'span' => array( 'class' => true ) ) ); ?>
				</h1>

				<p class="ghahghah-404__lead">
					<?php esc_html_e( 'ممکن است آدرس صفحه تغییر کرده باشد یا این صفحه دیگر در دسترس نباشد.', 'ghahghah' ); ?>
				</p>

					<div class="ghahghah-404__actions">
						<a class="ghahghah-404__btn ghahghah-404__btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<span class="ghahghah-404__btn-label"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'ghahghah' ); ?></span>
							<span class="ghahghah-404__btn-icon" aria-hidden="true">
								<?php ghahghah_the_404_icon( 'icon-chevron-left', array( 'class' => 'gg-404-icon--chevron' ) ); ?>
							</span>
						</a>
						<a class="ghahghah-404__btn ghahghah-404__btn--secondary" href="<?php echo esc_url( $ghahghah_products_url ); ?>">
							<span class="ghahghah-404__btn-label"><?php esc_html_e( 'مشاهده محصولات', 'ghahghah' ); ?></span>
							<span class="ghahghah-404__btn-icon" aria-hidden="true">
								<?php ghahghah_the_404_icon( 'icon-chevron-left', array( 'class' => 'gg-404-icon--chevron' ) ); ?>
							</span>
						</a>
					</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
