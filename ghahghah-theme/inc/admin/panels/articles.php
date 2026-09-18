<?php
/**
 * Homepage articles configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d         = ghahghah_articles_setting_defaults();
$enabled   = (bool) get_theme_mod( 'ghahghah_articles_enabled', $d['ghahghah_articles_enabled'] );
$eyebrow   = (string) get_theme_mod( 'ghahghah_articles_eyebrow', $d['ghahghah_articles_eyebrow'] );
$title     = (string) get_theme_mod( 'ghahghah_articles_title', $d['ghahghah_articles_title'] );
$all       = (string) get_theme_mod( 'ghahghah_articles_all_label', $d['ghahghah_articles_all_label'] );
$more      = (string) get_theme_mod( 'ghahghah_articles_more_label', $d['ghahghah_articles_more_label'] );
$cat_saved = absint( get_theme_mod( 'ghahghah_articles_category', 0 ) );
$last_saved = absint( get_theme_mod( 'ghahghah_articles_last_saved', 0 ) );

$preview_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 1,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $cat_saved > 0 ) {
	$preview_args['cat'] = $cat_saved;
}
$preview_posts = get_posts( $preview_args );
$preview_post  = $preview_posts[0] ?? null;
?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_articles_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="articles" />
	<?php wp_nonce_field( 'ghahghah_save_articles_settings', 'ghahghah_articles_nonce' ); ?>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تنظیمات بخش آخرین مطالب', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'کاروسل افقی آخرین نوشته‌های منتشرشده در صفحه اصلی (تا سقف ۱۲ مورد).', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-switch">
				<input type="checkbox" name="ghahghah_articles_enabled" value="1" <?php checked( $enabled ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'نمایش بخش آخرین مطالب در صفحه اصلی', 'ghahghah' ); ?></strong>
					<small><?php esc_html_e( 'هر نوشتهٔ جدید منتشرشده در این بخش می‌آید.', 'ghahghah' ); ?></small>
				</span>
			</label>

			<div class="ghahghah-field-grid">
				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان کوتاه', 'ghahghah' ); ?></span>
					<input type="text" name="ghahghah_articles_eyebrow" value="<?php echo esc_attr( $eyebrow ); ?>" maxlength="60" />
				</label>

				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان اصلی', 'ghahghah' ); ?></span>
					<input type="text" name="ghahghah_articles_title" value="<?php echo esc_attr( $title ); ?>" maxlength="120" />
				</label>
			</div>

			<div class="ghahghah-field-grid">
				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه همه مطالب', 'ghahghah' ); ?></span>
					<input type="text" name="ghahghah_articles_all_label" value="<?php echo esc_attr( $all ); ?>" maxlength="40" />
				</label>

				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php esc_html_e( 'متن لینک ادامه مطلب', 'ghahghah' ); ?></span>
					<input type="text" name="ghahghah_articles_more_label" value="<?php echo esc_attr( $more ); ?>" maxlength="40" />
				</label>
			</div>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'دسته مطالب', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_categories(
					array(
						'name'              => 'ghahghah_articles_category',
						'selected'          => $cat_saved,
						'show_option_none'  => __( '— همه نوشته‌های منتشرشده —', 'ghahghah' ),
						'option_none_value' => '0',
						'hide_empty'        => false,
						'taxonomy'          => 'category',
					)
				);
				?>
				<span class="ghahghah-field__help"><?php esc_html_e( 'اگر خالی بماند، همه نوشته‌های منتشرشده نمایش داده می‌شوند. با انتخاب دسته، فقط همان دسته فیلتر می‌شود.', 'ghahghah' ); ?></span>
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پیش‌نمایش کارت مطلب', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نمونهٔ زنده از آخرین نوشتهٔ منطبق با فیلتر دسته.', 'ghahghah' ); ?></p>
				</div>
			</header>

			<?php if ( $preview_post instanceof WP_Post ) : ?>
				<?php
				$cats     = get_the_category( $preview_post->ID );
				$cat_name = ( ! empty( $cats ) && isset( $cats[0]->name ) ) ? (string) $cats[0]->name : '';
				$excerpt  = wp_trim_words( get_the_excerpt( $preview_post ), 18, '…' );
				?>
				<article class="ghahghah-articles-preview">
					<div class="ghahghah-articles-preview__media">
						<?php
						if ( has_post_thumbnail( $preview_post ) ) {
							echo get_the_post_thumbnail( $preview_post, 'medium' );
						} else {
							echo '<span class="ghahghah-articles-preview__placeholder"></span>';
						}
						?>
					</div>
					<div class="ghahghah-articles-preview__body">
						<div class="ghahghah-articles-preview__meta">
							<?php if ( '' !== $cat_name ) : ?>
								<span class="ghahghah-articles-preview__cat"><?php echo esc_html( $cat_name ); ?></span>
							<?php endif; ?>
							<span><?php echo esc_html( get_the_date( '', $preview_post ) ); ?></span>
						</div>
						<strong class="ghahghah-articles-preview__title"><?php echo esc_html( get_the_title( $preview_post ) ); ?></strong>
						<?php if ( '' !== $excerpt ) : ?>
							<p class="ghahghah-articles-preview__excerpt"><?php echo esc_html( $excerpt ); ?></p>
						<?php endif; ?>
						<span class="ghahghah-articles-preview__more"><?php echo esc_html( $more ); ?></span>
					</div>
				</article>
			<?php else : ?>
				<div class="ghahghah-status-banner is-warn">
					<span class="ghahghah-status-banner__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
					</span>
					<p class="ghahghah-status-banner__text"><?php esc_html_e( 'هنوز نوشتهٔ منتشرشده‌ای برای پیش‌نمایش نیست.', 'ghahghah' ); ?></p>
				</div>
			<?php endif; ?>
		</section>
	</div>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره تنظیمات', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: last saved label */
				esc_html__( 'آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
