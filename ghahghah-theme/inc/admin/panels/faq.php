<?php
/**
 * Admin panel: FAQ page + editable Q&A groups.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d          = ghahghah_faq_defaults();
$faq_id     = absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) );
$contact    = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
$last_saved = absint( get_theme_mod( 'ghahghah_faq_last_saved', 0 ) );
$groups     = $faq_id > 0 ? ghahghah_faq_get_page_groups( $faq_id ) : array();
if ( array() === $groups ) {
	$groups = ghahghah_faq_normalize_open_state( ghahghah_faq_load_bundled_groups() );
}
$edit_url = $faq_id > 0 ? get_edit_post_link( $faq_id, 'raw' ) : '';
$targets  = array(
	''                    => __( '— بدون لینک —', 'ghahghah' ),
	'wholesale_page'      => __( 'برگه خرید عمده', 'ghahghah' ),
	'representation_page' => __( 'برگه نمایندگی', 'ghahghah' ),
	'product_archive'     => __( 'آرشیو محصولات', 'ghahghah' ),
	'contact_page'        => __( 'برگه تماس', 'ghahghah' ),
);

/**
 * Render one FAQ item row.
 *
 * @param int                  $gi         Group index.
 * @param int                  $ii         Item index.
 * @param array<string, mixed> $item       Item data.
 * @param array<string, string> $targets   Link targets.
 */
$render_faq_item = static function ( int $gi, int $ii, array $item, array $targets ): void {
	$link_target = isset( $item['link']['target'] ) ? (string) $item['link']['target'] : '';
	$link_label  = isset( $item['link']['label'] ) ? (string) $item['link']['label'] : '';
	?>
	<article class="ghahghah-steps-admin__row" data-ghahghah-faq-item>
		<div class="ghahghah-steps-admin__row-head">
			<span class="ghahghah-steps-admin__badge"><?php echo esc_html( sprintf( '%02d', $ii + 1 ) ); ?></span>
			<strong class="ghahghah-steps-admin__row-title"><?php echo esc_html( sprintf( /* translators: %d item index */ __( 'پرسش %d', 'ghahghah' ), $ii + 1 ) ); ?></strong>
		</div>

		<input type="hidden" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][key]" value="<?php echo esc_attr( (string) ( $item['key'] ?? '' ) ); ?>" />

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'سؤال', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][question]" value="<?php echo esc_attr( (string) ( $item['question'] ?? '' ) ); ?>" />
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'پاسخ', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][answer]" rows="3"><?php echo esc_textarea( (string) ( $item['answer'] ?? '' ) ); ?></textarea>
		</label>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][initially_open]" value="1" <?php checked( ! empty( $item['initially_open'] ) ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'باز اولیه', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'فقط نخستین مورد «باز اولیه» در هر صفحه اعمال می‌شود.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن لینک داخل پاسخ (اختیاری)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][link_label]" value="<?php echo esc_attr( $link_label ); ?>" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'مقصد لینک', 'ghahghah' ); ?></span>
				<select name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][link_target]">
					<?php foreach ( $targets as $t_key => $t_label ) : ?>
						<option value="<?php echo esc_attr( $t_key ); ?>" <?php selected( $link_target, $t_key ); ?>><?php echo esc_html( $t_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>
	</article>
	<?php
};
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_faq" />
	<input type="hidden" name="ghahghah_return_tab" value="faq" />
	<?php wp_nonce_field( 'ghahghah_save_faq', 'ghahghah_faq_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه پرسش‌های متداول و برگه تماس را انتخاب کنید. شناسه‌ها در محیط‌های مختلف فرق می‌کنند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه پرسش‌های متداول', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_faq_page_id',
						'selected'          => $faq_id,
						'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
						'option_none_value' => '0',
					)
				);
				?>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'برگه تماس با ما', 'ghahghah' ); ?></span>
				<?php
				wp_dropdown_pages(
					array(
						'name'              => 'ghahghah_contact_page_id',
						'selected'          => $contact,
						'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
						'option_none_value' => '0',
					)
				);
				?>
			</label>
		</div>

		<?php if ( is_string( $edit_url ) && '' !== $edit_url ) : ?>
			<a class="ghahghah-link-card" href="<?php echo esc_url( $edit_url ); ?>">
				<span><?php esc_html_e( 'باز کردن همان برگه در ویرایشگر وردپرس', 'ghahghah' ); ?></span>
				<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
			</a>
		<?php endif; ?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'معرفی و کارت‌های کناری', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متن معرفی، کارت تماس و لینک‌های دسترسی به فرم‌ها.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-field-grid ghahghah-field-grid--2">
			<?php
			$chrome = array(
				'ghahghah_faq_intro_title'     => __( 'عنوان معرفی', 'ghahghah' ),
				'ghahghah_faq_intro_text'      => __( 'توضیح معرفی', 'ghahghah' ),
				'ghahghah_faq_support_title'   => __( 'عنوان کارت تماس', 'ghahghah' ),
				'ghahghah_faq_support_text'    => __( 'متن کارت تماس', 'ghahghah' ),
				'ghahghah_faq_support_button'  => __( 'دکمه تماس', 'ghahghah' ),
				'ghahghah_faq_forms_title'     => __( 'عنوان دسترسی به فرم‌ها', 'ghahghah' ),
				'ghahghah_faq_forms_wholesale' => __( 'برچسب لینک خرید عمده', 'ghahghah' ),
				'ghahghah_faq_forms_agency'    => __( 'برچسب لینک نمایندگی', 'ghahghah' ),
			);
			foreach ( $chrome as $key => $label ) :
				$val = (string) get_theme_mod( $key, $d[ $key ] );
				$tag = str_contains( $key, '_text' ) ? 'textarea' : 'input';
				?>
				<label class="ghahghah-field<?php echo 'textarea' === $tag ? ' ghahghah-field--full' : ''; ?>">
					<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
					<?php if ( 'textarea' === $tag ) : ?>
						<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
					<?php else : ?>
						<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
					<?php endif; ?>
				</label>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="ghahghah-panel-section" data-ghahghah-faq-admin>
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 3.5"/><path d="M12 17h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'گروه‌ها و پرسش‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'پرسش‌ها روی همان برگه ذخیره می‌شوند. لینک‌ها با کلید مقصد به برگه‌های تنظیم‌شده نگاشت می‌شوند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php foreach ( $groups as $gi => $group ) : ?>
			<div class="ghahghah-panel-section ghahghah-panel-section--nested" data-ghahghah-faq-group>
				<header class="ghahghah-panel-section__head">
					<div>
						<h4 class="ghahghah-panel-section__title"><?php echo esc_html( sprintf( /* translators: %d group index */ __( 'گروه %d', 'ghahghah' ), $gi + 1 ) ); ?></h4>
					</div>
				</header>

				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php echo esc_html( sprintf( /* translators: %d group index */ __( 'عنوان گروه %d', 'ghahghah' ), $gi + 1 ) ); ?></span>
					<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][title]" value="<?php echo esc_attr( (string) ( $group['title'] ?? '' ) ); ?>" />
				</label>
				<input type="hidden" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][key]" value="<?php echo esc_attr( (string) ( $group['key'] ?? '' ) ); ?>" />

				<div class="ghahghah-steps-admin__list" data-ghahghah-faq-items>
					<?php
					$items = is_array( $group['items'] ?? null ) ? $group['items'] : array();
					foreach ( $items as $ii => $item ) :
						$render_faq_item( (int) $gi, (int) $ii, is_array( $item ) ? $item : array(), $targets );
					endforeach;
					?>
				</div>

				<?php $new_ii = count( $items ); ?>
				<details class="ghahghah-details">
					<summary class="ghahghah-btn ghahghah-btn--ghost ghahghah-btn--sm"><?php esc_html_e( 'افزودن پرسش به این گروه', 'ghahghah' ); ?></summary>
					<div class="ghahghah-steps-admin__row" data-ghahghah-faq-item>
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'سؤال جدید', 'ghahghah' ); ?></span>
							<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][question]" value="" />
						</label>
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'پاسخ جدید', 'ghahghah' ); ?></span>
							<textarea name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][answer]" rows="3"></textarea>
						</label>
						<div class="ghahghah-field-grid">
							<label class="ghahghah-field">
								<span class="ghahghah-field__label"><?php esc_html_e( 'متن لینک (اختیاری)', 'ghahghah' ); ?></span>
								<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][link_label]" value="" />
							</label>
							<label class="ghahghah-field">
								<span class="ghahghah-field__label"><?php esc_html_e( 'مقصد لینک', 'ghahghah' ); ?></span>
								<select name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][link_target]">
									<?php foreach ( $targets as $t_key => $t_label ) : ?>
										<option value="<?php echo esc_attr( $t_key ); ?>"><?php echo esc_html( $t_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						</div>
					</div>
				</details>
			</div>
		<?php endforeach; ?>

		<?php $new_gi = count( $groups ); ?>
		<details class="ghahghah-panel-section ghahghah-panel-section--nested">
			<summary class="ghahghah-panel-section__head ghahghah-panel-section__head--action">
				<span class="ghahghah-panel-section__title"><?php esc_html_e( 'افزودن گروه جدید', 'ghahghah' ); ?></span>
			</summary>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان گروه جدید', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $new_gi ); ?>][title]" value="" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'اولین سؤال', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $new_gi ); ?>][items][0][question]" value="" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'اولین پاسخ', 'ghahghah' ); ?></span>
				<textarea name="ghahghah_faq_groups[<?php echo esc_attr( (string) $new_gi ); ?>][items][0][answer]" rows="3"></textarea>
			</label>
		</details>
	</section>

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
