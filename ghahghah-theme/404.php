<?php
/**
 * 404 template.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main" tabindex="-1">
	<div class="site-main__inner">
		<section class="error-404" aria-labelledby="error-404-title">
			<h1 id="error-404-title"><?php esc_html_e( 'صفحه پیدا نشد', 'ghahghah' ); ?></h1>
			<p><?php esc_html_e( 'متأسفانه آدرس واردشده وجود ندارد یا جابه‌جا شده است.', 'ghahghah' ); ?></p>
			<p>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'بازگشت به صفحه اصلی', 'ghahghah' ); ?>
				</a>
			</p>
		</section>
	</div>
</main>

<?php
get_footer();
