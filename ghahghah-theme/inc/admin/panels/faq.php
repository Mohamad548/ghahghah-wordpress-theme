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

$d        = ghahghah_faq_defaults();
$faq_id   = absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) );
$contact  = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
$groups   = $faq_id > 0 ? ghahghah_faq_get_page_groups( $faq_id ) : array();
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
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_faq" />
	<input type="hidden" name="ghahghah_return_tab" value="faq" />
	<?php wp_nonce_field( 'ghahghah_save_faq', 'ghahghah_faq_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برگه‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'برگه پرسش‌های متداول و برگه تماس را انتخاب کنید. شناسه‌ها در محیط‌های مختلف فرق می‌کنند.', 'ghahghah' ); ?></p>
			</div>
		</header>

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

		<?php if ( is_string( $edit_url ) && '' !== $edit_url ) : ?>
			<p class="ghahghah-field__hint">
				<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'باز کردن همان برگه در ویرایشگر وردپرس', 'ghahghah' ); ?></a>
			</p>
		<?php endif; ?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'معرفی و کارت‌های کناری', 'ghahghah' ); ?></h3>
			</div>
		</header>
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
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( 'textarea' === $tag ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>" rows="2"><?php echo esc_textarea( $val ); ?></textarea>
				<?php else : ?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" />
				<?php endif; ?>
			</label>
		<?php endforeach; ?>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'گروه‌ها و پرسش‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'پرسش‌ها روی همان برگه ذخیره می‌شوند. فقط نخستین مورد «باز اولیه» اعمال می‌شود. لینک‌ها با کلید مقصد به برگه‌های تنظیم‌شده نگاشت می‌شوند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php foreach ( $groups as $gi => $group ) : ?>
			<div class="ghahghah-config-card" style="margin-bottom:1rem;">
				<label class="ghahghah-field">
					<span class="ghahghah-field__label"><?php echo esc_html( sprintf( /* translators: %d group index */ __( 'عنوان گروه %d', 'ghahghah' ), $gi + 1 ) ); ?></span>
					<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][title]" value="<?php echo esc_attr( (string) ( $group['title'] ?? '' ) ); ?>" />
				</label>
				<input type="hidden" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][key]" value="<?php echo esc_attr( (string) ( $group['key'] ?? '' ) ); ?>" />

				<?php
				$items = is_array( $group['items'] ?? null ) ? $group['items'] : array();
				foreach ( $items as $ii => $item ) :
					$link_target = isset( $item['link']['target'] ) ? (string) $item['link']['target'] : '';
					$link_label  = isset( $item['link']['label'] ) ? (string) $item['link']['label'] : '';
					?>
					<fieldset class="ghahghah-field" style="border:1px solid #e5e5e5;padding:0.85rem;border-radius:8px;margin:0.75rem 0;">
						<legend><?php echo esc_html( sprintf( /* translators: %d item index */ __( 'پرسش %d', 'ghahghah' ), $ii + 1 ) ); ?></legend>
						<input type="hidden" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][key]" value="<?php echo esc_attr( (string) ( $item['key'] ?? '' ) ); ?>" />
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'سؤال', 'ghahghah' ); ?></span>
							<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][question]" value="<?php echo esc_attr( (string) ( $item['question'] ?? '' ) ); ?>" />
						</label>
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'پاسخ', 'ghahghah' ); ?></span>
							<textarea name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][answer]" rows="3"><?php echo esc_textarea( (string) ( $item['answer'] ?? '' ) ); ?></textarea>
						</label>
						<label class="ghahghah-field" style="display:flex;align-items:center;gap:0.5rem;">
							<input type="checkbox" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $ii ); ?>][initially_open]" value="1" <?php checked( ! empty( $item['initially_open'] ) ); ?> />
							<span><?php esc_html_e( 'باز اولیه', 'ghahghah' ); ?></span>
						</label>
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
					</fieldset>
				<?php endforeach; ?>

				<?php
				/* Empty slot to add one more question per group */
				$new_ii = count( $items );
				?>
				<details>
					<summary><?php esc_html_e( 'افزودن پرسش به این گروه', 'ghahghah' ); ?></summary>
					<fieldset class="ghahghah-field" style="border:1px dashed #ccc;padding:0.85rem;border-radius:8px;margin-top:0.5rem;">
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'سؤال جدید', 'ghahghah' ); ?></span>
							<input type="text" name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][question]" value="" />
						</label>
						<label class="ghahghah-field">
							<span class="ghahghah-field__label"><?php esc_html_e( 'پاسخ جدید', 'ghahghah' ); ?></span>
							<textarea name="ghahghah_faq_groups[<?php echo esc_attr( (string) $gi ); ?>][items][<?php echo esc_attr( (string) $new_ii ); ?>][answer]" rows="3"></textarea>
						</label>
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
					</fieldset>
				</details>
			</div>
		<?php endforeach; ?>

		<?php
		$new_gi = count( $groups );
		?>
		<details class="ghahghah-config-card">
			<summary><?php esc_html_e( 'افزودن گروه جدید', 'ghahghah' ); ?></summary>
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

	<p>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره پرسش‌های متداول', 'ghahghah' ); ?></button>
	</p>
</form>
