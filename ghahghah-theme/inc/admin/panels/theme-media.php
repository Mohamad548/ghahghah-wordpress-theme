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

$manifest   = function_exists( 'ghahghah_get_theme_media_manifest' )
	? ghahghah_get_theme_media_manifest()
	: array();
$last_saved = absint( get_theme_mod( 'ghahghah_theme_media_last_saved', 0 ) );
$stats      = get_transient( 'ghahghah_theme_media_sync_stats' );
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
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="13" width="7" height="7" rx="1.5"/><rect x="14" y="13" width="7" height="7" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'همگام‌سازی تصاویر قالب', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'تمام تصاویر فعال قالب (بنرها، لوگو، محصولات، مقالات و …) در کتابخانه رسانه وردپرس قابل مشاهده می‌شوند.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>

		<?php if ( is_array( $stats ) ) : ?>
			<div class="ghahghah-status-banner is-ok">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
				</span>
				<p class="ghahghah-status-banner__text">
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

		<p class="ghahghah-field__help">
			<?php
			printf(
				/* translators: %d: number of bundled images */
				esc_html__( 'در حال حاضر %d تصویر فعال در قالب شناسایی شده است.', 'ghahghah' ),
				count( $manifest )
			);
			?>
		</p>

		<ul class="ghahghah-field__help" style="margin:0 0 1rem;padding-inline-start:1.25rem;line-height:1.7;">
			<li><?php esc_html_e( 'بنرهای اسلایدر صفحه اصلی (دسکتاپ و موبایل)', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'بنرهای آرشیو مقالات و محصولات', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'لوگو، آیکون سایت و تصاویر صفحات', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'تصاویر محصولات، مقالات و تزئینات', 'ghahghah' ); ?></li>
		</ul>

		<div class="ghahghah-field-grid">
			<button type="submit" class="ghahghah-btn ghahghah-btn--save">
				<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
				<?php esc_html_e( 'همگام‌سازی با کتابخانه رسانه', 'ghahghah' ); ?>
			</button>
			<a class="ghahghah-btn ghahghah-btn--ghost" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>">
				<?php esc_html_e( 'مشاهده کتابخانه رسانه', 'ghahghah' ); ?>
			</a>
		</div>
	</section>

	<div class="ghahghah-panel-form__footer">
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: last saved label */
				esc_html__( 'آخرین همگام‌سازی: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
