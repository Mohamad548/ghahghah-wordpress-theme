<?php
/**
 * Admin panel: sync bundled theme images to Media Library.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$manifest = function_exists( 'ghahghah_get_theme_media_manifest' )
	? ghahghah_get_theme_media_manifest()
	: array();
$stats = get_transient( 'ghahghah_theme_media_sync_stats' );
if ( is_array( $stats ) ) {
	delete_transient( 'ghahghah_theme_media_sync_stats' );
}
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_sync_theme_media" />
	<input type="hidden" name="ghahghah_return_tab" value="theme-media" />
	<?php wp_nonce_field( 'ghahghah_sync_theme_media', 'ghahghah_theme_media_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'همگام‌سازی تصاویر قالب', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'تمام تصاویر فعال قالب (بنرها، لوگو، محصولات، مقالات و …) در کتابخانه رسانه وردپرس قابل مشاهده می‌شوند.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>

		<?php if ( is_array( $stats ) ) : ?>
			<div class="notice notice-success inline" style="margin: 0 0 1rem;">
				<p>
					<?php
					printf(
						/* translators: 1: total count, 2: created count, 3: reused count, 4: missing count */
						esc_html__( 'همگام‌سازی انجام شد: %1$d تصویر بررسی شد — %2$d مورد جدید، %3$d مورد موجود، %4$d مورد یافت نشد.', 'ghahghah' ),
						(int) ( $stats['total'] ?? 0 ),
						(int) ( $stats['created'] ?? 0 ),
						(int) ( $stats['reused'] ?? 0 ),
						(int) ( $stats['missing'] ?? 0 )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<p>
			<?php
			printf(
				/* translators: %d: number of bundled images */
				esc_html__( 'در حال حاضر %d تصویر فعال در قالب شناسایی شده است.', 'ghahghah' ),
				count( $manifest )
			);
			?>
		</p>

		<ul style="margin: 0 0 1rem 1.25rem; list-style: disc;">
			<li><?php esc_html_e( 'بنرهای اسلایدر صفحه اصلی (دسکتاپ و موبایل)', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'بنرهای آرشیو مقالات و محصولات', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'لوگو، آیکون سایت و تصاویر صفحات', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'تصاویر محصولات، مقالات و تزئینات', 'ghahghah' ); ?></li>
		</ul>

		<p>
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'همگام‌سازی با کتابخانه رسانه', 'ghahghah' ); ?>
			</button>
			<a class="button" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
				<?php esc_html_e( 'مشاهده کتابخانه رسانه', 'ghahghah' ); ?>
			</a>
		</p>
	</section>
</form>
