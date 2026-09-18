<?php
/**
 * Featured products admin panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d           = ghahghah_featured_setting_defaults();
$enabled     = (bool) get_theme_mod( 'ghahghah_featured_enabled', $d['ghahghah_featured_enabled'] );
$title       = (string) get_theme_mod( 'ghahghah_featured_title', $d['ghahghah_featured_title'] );
$text        = (string) get_theme_mod( 'ghahghah_featured_text', $d['ghahghah_featured_text'] );
$all_label   = (string) get_theme_mod( 'ghahghah_featured_all_label', $d['ghahghah_featured_all_label'] );
$selected    = ghahghah_sanitize_featured_product_ids( get_theme_mod( 'ghahghah_featured_ids', $d['ghahghah_featured_ids'] ) );
$last_saved  = absint( get_theme_mod( 'ghahghah_featured_last_saved', 0 ) );
$max_items   = (int) GHAHGHAH_FEATURED_MAX;
$selected_n  = count( $selected );

$products = array();
if ( post_type_exists( 'ghahghah_product' ) ) {
	$products = get_posts(
		array(
			'post_type'      => 'ghahghah_product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
}

$order_map = array();
foreach ( $selected as $i => $sid ) {
	$order_map[ $sid ] = $i + 1;
}

// Selected products first (by order), then the rest alphabetically.
usort(
	$products,
	static function ( WP_Post $a, WP_Post $b ) use ( $order_map ): int {
		$ao = $order_map[ (int) $a->ID ] ?? 0;
		$bo = $order_map[ (int) $b->ID ] ?? 0;
		if ( $ao > 0 && $bo > 0 ) {
			return $ao <=> $bo;
		}
		if ( $ao > 0 ) {
			return -1;
		}
		if ( $bo > 0 ) {
			return 1;
		}
		return strcasecmp( get_the_title( $a ), get_the_title( $b ) );
	}
);

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_featured_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="featured" />
	<?php wp_nonce_field( 'ghahghah_save_featured_settings', 'ghahghah_featured_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="13" width="7" height="7" rx="1.5"/><rect x="14" y="13" width="7" height="7" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'تنظیمات بخش محصولات منتخب', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نمایش، عنوان، توضیح و متن دکمهٔ مشاهده همه در صفحه اصلی.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_featured_enabled" value="1" <?php checked( $enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش بخش در صفحه اصلی', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'در صورت خاموش بودن، کاروسل محصولات منتخب در خانه دیده نمی‌شود.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-field-grid ghahghah-field-grid--3">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان بخش', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_featured_title" value="<?php echo esc_attr( $title ); ?>" maxlength="80" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح کوتاه', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_featured_text" value="<?php echo esc_attr( $text ); ?>" maxlength="160" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_featured_all_label" value="<?php echo esc_attr( $all_label ); ?>" maxlength="40" />
			</label>
		</div>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/><path d="M18 10v8M15 14h6"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'انتخاب و ترتیب محصولات', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'محصولات را انتخاب کنید و با شماره ترتیب، اولویت نمایش را مشخص کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php if ( ! post_type_exists( 'ghahghah_product' ) ) : ?>
			<div class="ghahghah-status-banner is-warn">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
				</span>
				<p class="ghahghah-status-banner__text"><?php esc_html_e( 'افزونه Core فعال نیست؛ انتخاب محصول ممکن نیست.', 'ghahghah' ); ?></p>
			</div>
		<?php elseif ( array() === $products ) : ?>
			<div class="ghahghah-status-banner is-warn">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
				</span>
				<p class="ghahghah-status-banner__text"><?php esc_html_e( 'هنوز محصولی ثبت نشده است.', 'ghahghah' ); ?></p>
			</div>
		<?php else : ?>
			<div class="ghahghah-status-banner is-ok ghahghah-featured-picker__status-bar">
				<span class="ghahghah-status-banner__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
				</span>
				<p class="ghahghah-status-banner__text">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: selected count, 2: max */
							__( '%1$d محصول انتخاب شده · حداکثر %2$d محصول', 'ghahghah' ),
							$selected_n,
							$max_items
						)
					);
					?>
				</p>
			</div>

			<p class="ghahghah-field__help ghahghah-featured-picker__hint">
				<?php esc_html_e( 'نام و تصویر هر محصول از صفحه ویرایش همان محصول مدیریت می‌شود. فقط وضعیت «منتشرشده» در سایت نمایش داده می‌شود.', 'ghahghah' ); ?>
			</p>

			<div class="ghahghah-featured-picker">
				<?php foreach ( $products as $p ) : ?>
					<?php
					$pid      = (int) $p->ID;
					$is_sel   = isset( $order_map[ $pid ] );
					$status   = (string) get_post_status( $p );
					$obj      = get_post_status_object( $status );
					$status_l = $obj->label ?? $status;
					$edit     = get_edit_post_link( $pid, 'raw' );
					?>
					<div class="ghahghah-featured-picker__row<?php echo $is_sel ? ' is-selected' : ''; ?><?php echo 'draft' === $status || 'pending' === $status ? ' is-draft' : ''; ?>">
						<span class="ghahghah-featured-picker__index" aria-hidden="true">
							<?php echo $is_sel ? esc_html( (string) $order_map[ $pid ] ) : '·'; ?>
						</span>

						<label class="ghahghah-featured-picker__check">
							<input
								type="checkbox"
								name="ghahghah_featured_check[<?php echo esc_attr( (string) $pid ); ?>]"
								value="1"
								<?php checked( $is_sel ); ?>
							/>
							<span class="ghahghah-featured-picker__thumb">
								<?php
								if ( has_post_thumbnail( $pid ) ) {
									echo get_the_post_thumbnail( $pid, array( 48, 48 ) );
								} else {
									echo '<span class="ghahghah-featured-picker__thumb-empty"></span>';
								}
								?>
							</span>
							<span class="ghahghah-featured-picker__meta">
								<strong><?php echo esc_html( get_the_title( $p ) ); ?></strong>
								<small>
									#<?php echo esc_html( (string) $pid ); ?>
									·
									<span class="ghahghah-featured-picker__status ghahghah-featured-picker__status--<?php echo esc_attr( $status ); ?>">
										<?php echo esc_html( $status_l ); ?>
									</span>
									<?php if ( is_string( $edit ) && '' !== $edit ) : ?>
										· <a href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'ویرایش', 'ghahghah' ); ?></a>
									<?php endif; ?>
								</small>
							</span>
						</label>

						<label class="ghahghah-featured-picker__order">
							<span class="screen-reader-text"><?php esc_html_e( 'ترتیب', 'ghahghah' ); ?></span>
							<input
								type="number"
								name="ghahghah_featured_order[<?php echo esc_attr( (string) $pid ); ?>]"
								min="1"
								max="<?php echo esc_attr( (string) $max_items ); ?>"
								value="<?php echo $is_sel ? esc_attr( (string) $order_map[ $pid ] ) : ''; ?>"
								placeholder="#"
							/>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<div class="ghahghah-panel-form__footer">
		<button type="submit" class="ghahghah-btn ghahghah-btn--save">
			<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M5 3h11l3 3v15H5z"/><path d="M8 3v6h8V3M8 21v-7h8v7"/></svg>
			<?php esc_html_e( 'ذخیره محصولات منتخب', 'ghahghah' ); ?>
		</button>
		<p class="ghahghah-panel-form__meta">
			<?php
			printf(
				/* translators: %s: last saved label */
				esc_html__( 'پس از اعمال تغییرات ذخیره کنید · آخرین ذخیره: %s', 'ghahghah' ),
				esc_html( ghahghah_format_config_last_saved( $last_saved ) )
			);
			?>
		</p>
	</div>
</form>
