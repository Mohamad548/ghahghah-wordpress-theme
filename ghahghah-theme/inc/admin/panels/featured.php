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

$d         = ghahghah_featured_setting_defaults();
$enabled   = (bool) get_theme_mod( 'ghahghah_featured_enabled', $d['ghahghah_featured_enabled'] );
$title     = (string) get_theme_mod( 'ghahghah_featured_title', $d['ghahghah_featured_title'] );
$text      = (string) get_theme_mod( 'ghahghah_featured_text', $d['ghahghah_featured_text'] );
$all_label = (string) get_theme_mod( 'ghahghah_featured_all_label', $d['ghahghah_featured_all_label'] );
$selected  = ghahghah_sanitize_featured_product_ids( get_theme_mod( 'ghahghah_featured_ids', $d['ghahghah_featured_ids'] ) );

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

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_featured_settings" />
	<?php wp_nonce_field( 'ghahghah_save_featured_settings', 'ghahghah_featured_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="13" width="7" height="7" rx="1.5"/><rect x="14" y="13" width="7" height="7" rx="1.5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'بخش محصولات منتخب', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: max featured products */
							__( 'حداکثر %d محصول با ترتیب. فقط وضعیت «منتشرشده» در سایت نمایش داده می‌شود.', 'ghahghah' ),
							GHAHGHAH_FEATURED_MAX
						)
					);
					?>
				</p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="ghahghah_featured_enabled" value="1" <?php checked( $enabled ); ?> />
			<span><?php esc_html_e( 'نمایش بخش در صفحه اصلی', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان بخش', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_featured_title" value="<?php echo esc_attr( $title ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح کوتاه', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_featured_text" value="<?php echo esc_attr( $text ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه «مشاهده همه»', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_featured_all_label" value="<?php echo esc_attr( $all_label ); ?>" />
		</label>

		<?php if ( ! post_type_exists( 'ghahghah_product' ) ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'افزونه Core فعال نیست؛ انتخاب محصول ممکن نیست.', 'ghahghah' ); ?></p></div>
		<?php elseif ( array() === $products ) : ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'هنوز محصولی ثبت نشده است.', 'ghahghah' ); ?></p></div>
		<?php else : ?>
			<p class="ghahghah-field__help"><?php esc_html_e( 'نام و تصویر هر محصول از صفحه ویرایش همان محصول مدیریت می‌شود.', 'ghahghah' ); ?></p>
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
					<div class="ghahghah-featured-picker__row<?php echo 'draft' === $status ? ' is-draft' : ''; ?>">
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
								max="6"
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
		<button type="submit" class="button button-primary button-hero">
			<?php esc_html_e( 'ذخیره محصولات منتخب', 'ghahghah' ); ?>
		</button>
	</div>
</form>
