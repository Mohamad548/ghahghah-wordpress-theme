<?php
/**
 * Admin panel: theme media sync + one-click live site bootstrap.
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
$boot_last  = absint( get_theme_mod( 'ghahghah_bootstrap_last_run', 0 ) );
$stats      = get_transient( 'ghahghah_theme_media_sync_stats' );
$boot       = get_transient( 'ghahghah_bootstrap_result' );
if ( is_array( $stats ) ) {
	delete_transient( 'ghahghah_theme_media_sync_stats' );
}
if ( is_array( $boot ) ) {
	delete_transient( 'ghahghah_bootstrap_result' );
}
$pending = '1' === (string) get_option( 'ghahghah_theme_media_sync_pending', '' );
$core_ok = post_type_exists( 'ghahghah_product' );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_bootstrap_site" />
	<input type="hidden" name="ghahghah_return_tab" value="theme-media" />
	<?php wp_nonce_field( 'ghahghah_bootstrap_site', 'ghahghah_bootstrap_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M5 19h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'راه‌اندازی اولیه سایت', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php esc_html_e( 'با یک کلیک: همگام‌سازی رسانه، ساخت برگه‌ها، واردات محصولات، ساخت منوها و اتصال تصویر کارخانه.', 'ghahghah' ); ?>
				</p>
			</div>
		</header>

		<?php if ( ! $core_ok ) : ?>
			<div class="ghahghah-status-banner is-warn">
				<p class="ghahghah-status-banner__text">
					<?php esc_html_e( 'افزونه Ghahghah Core فعال نیست — محصولات ساخته نمی‌شوند. ابتدا Core را فعال کنید.', 'ghahghah' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $boot ) && ! empty( $boot['steps'] ) && is_array( $boot['steps'] ) ) : ?>
			<div class="ghahghah-status-banner <?php echo ! empty( $boot['ok'] ) ? 'is-ok' : 'is-warn'; ?>">
				<p class="ghahghah-status-banner__text">
					<?php esc_html_e( 'نتیجه راه‌اندازی:', 'ghahghah' ); ?>
				</p>
				<ul style="margin:0.5rem 0 0;padding-inline-start:1.25rem;line-height:1.7;">
					<?php foreach ( $boot['steps'] as $step_name => $step ) : ?>
						<li>
							<strong><?php echo esc_html( (string) $step_name ); ?>:</strong>
							<?php echo esc_html( (string) ( $step['message'] ?? '' ) ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<ul class="ghahghah-field__help" style="margin:0 0 1rem;padding-inline-start:1.25rem;line-height:1.7;">
			<li><?php esc_html_e( 'محصولات پیش‌فرض کاتالوگ', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'برگه‌های تماس، کارخانه، عمده، نمایندگی، FAQ، حریم خصوصی', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'منوی اصلی / فوتر / موبایل', 'ghahghah' ); ?></li>
			<li><?php esc_html_e( 'تصاویر قالب + پاکسازی تکراری‌ها', 'ghahghah' ); ?></li>
		</ul>

		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M5 19h14"/></svg>
			<?php esc_html_e( 'اجرای راه‌اندازی اولیه', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-field__help" style="margin-top:0.75rem;">
			<?php
			printf(
				/* translators: %s: last run label */
				esc_html__( 'آخرین اجرا: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $boot_last ) )
			);
			?>
		</p>
	</section>
</form>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1rem;">
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
					<?php esc_html_e( 'فقط تصاویر داخل پوشه قالب را با کتابخانه رسانه هم‌تراز می‌کند (بدون ساخت برگه/محصول).', 'ghahghah' ); ?>
				</p>
			</div>
		</header>

		<?php if ( $pending ) : ?>
			<div class="ghahghah-status-banner is-warn">
				<p class="ghahghah-status-banner__text">
					<?php esc_html_e( 'همگام‌سازی پس از فعال‌سازی قالب در صف است. همین صفحه را یک‌بار رفرش کنید یا دکمه زیر را بزنید.', 'ghahghah' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( is_array( $stats ) ) : ?>
			<div class="ghahghah-status-banner is-ok">
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

		<div class="ghahghah-field-grid">
			<button type="submit" class="ghahghah-btn ghahghah-btn--outline">
				<?php esc_html_e( 'فقط همگام‌سازی رسانه', 'ghahghah' ); ?>
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
