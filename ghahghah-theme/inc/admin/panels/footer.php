<?php
/**
 * Desktop footer configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$d = ghahghah_footer_setting_defaults();

$logo_id         = absint( get_theme_mod( 'ghahghah_footer_logo', 0 ) );
$intro           = (string) get_theme_mod( 'ghahghah_footer_intro', $d['ghahghah_footer_intro'] );
$quick_title     = (string) get_theme_mod( 'ghahghah_footer_col_quick_title', $d['ghahghah_footer_col_quick_title'] );
$business_title  = (string) get_theme_mod( 'ghahghah_footer_col_business_title', $d['ghahghah_footer_col_business_title'] );
$contact_title   = (string) get_theme_mod( 'ghahghah_footer_col_contact_title', $d['ghahghah_footer_col_contact_title'] );
$collab_enabled  = (bool) get_theme_mod( 'ghahghah_footer_collab_enabled', $d['ghahghah_footer_collab_enabled'] );
$collab_title    = (string) get_theme_mod( 'ghahghah_footer_collab_title', $d['ghahghah_footer_collab_title'] );
$collab_text     = (string) get_theme_mod( 'ghahghah_footer_collab_text', $d['ghahghah_footer_collab_text'] );
$primary_label   = (string) get_theme_mod( 'ghahghah_footer_collab_primary_label', $d['ghahghah_footer_collab_primary_label'] );
$primary_page    = absint( get_theme_mod( 'ghahghah_footer_collab_primary_page', 0 ) );
$secondary_label = (string) get_theme_mod( 'ghahghah_footer_collab_secondary_label', $d['ghahghah_footer_collab_secondary_label'] );
$secondary_page  = absint( get_theme_mod( 'ghahghah_footer_collab_secondary_page', 0 ) );
$phones          = (string) get_theme_mod( 'ghahghah_footer_phones', $d['ghahghah_footer_phones'] );
$email           = (string) get_theme_mod( 'ghahghah_footer_email', $d['ghahghah_footer_email'] );
$address         = (string) get_theme_mod( 'ghahghah_footer_address', $d['ghahghah_footer_address'] );
$legal_text      = (string) get_theme_mod( 'ghahghah_footer_legal_text', $d['ghahghah_footer_legal_text'] );
$privacy_page    = absint( get_theme_mod( 'ghahghah_footer_privacy_page_id', 0 ) );
$back_to_top     = (bool) get_theme_mod( 'ghahghah_footer_back_to_top', $d['ghahghah_footer_back_to_top'] );
$last_saved      = absint( get_theme_mod( 'ghahghah_footer_last_saved', 0 ) );

?>

<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_footer_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="footer" />
	<?php wp_nonce_field( 'ghahghah_save_footer_settings', 'ghahghah_footer_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'برند فوتر', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لوگوی مخصوص فوتر (اختیاری) و معرفی کوتاه برند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<?php
		ghahghah_admin_render_media_field(
			'ghahghah_footer_logo',
			__( 'لوگوی فوتر (دسکتاپ)', 'ghahghah' ),
			__( 'اگر خالی باشد از لوگوی هدر / لوگوی پیش‌فرض قالب استفاده می‌شود.', 'ghahghah' ),
			$logo_id,
			ghahghah_get_bundled_brand_asset_url( 'ghahghah-logo-desktop.webp' )
		);
		?>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'معرفی کوتاه برند', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_footer_intro" rows="3"><?php echo esc_textarea( $intro ); ?></textarea>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h10M4 17h14"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'عناوین ستون‌ها و فهرست‌ها', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لینک‌ها از نمایش ← فهرست‌ها مدیریت می‌شوند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان ستون دسترسی سریع', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_col_quick_title" value="<?php echo esc_attr( $quick_title ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان ستون همکاری', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_col_business_title" value="<?php echo esc_attr( $business_title ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان ستون تماس', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_col_contact_title" value="<?php echo esc_attr( $contact_title ); ?>" />
		</label>

		<a class="ghahghah-link-card" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>">
			<span><?php esc_html_e( 'مدیریت فهرست‌های «پاورقی»، «همکاری فوتر» و «حقوقی»', 'ghahghah' ); ?></span>
			<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
		</a>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M8 12h.01M12 12h.01M16 12h.01"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'نوار همکاری', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'نوار روشن بالای فوتر با دو دکمه.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_footer_collab_enabled" value="1" <?php checked( $collab_enabled ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش نوار همکاری', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'نوار روشن بالای فوتر با دو دکمه.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-field-grid">
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان نوار', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_collab_title" value="<?php echo esc_attr( $collab_title ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'توضیح کوتاه', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_footer_collab_text" rows="2"><?php echo esc_textarea( $collab_text ); ?></textarea>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه قرمز', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_collab_primary_label" value="<?php echo esc_attr( $primary_label ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'برگه مقصد دکمه قرمز', 'ghahghah' ); ?></span>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => 'ghahghah_footer_collab_primary_page',
					'selected'          => $primary_page,
					'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
					'option_none_value' => '0',
				)
			);
			?>
			<span class="ghahghah-field__help"><?php esc_html_e( 'اگر خالی باشد، در صورت وجود از مقصد دکمه هدر (خرید عمده) استفاده می‌شود.', 'ghahghah' ); ?></span>
		</label>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن دکمه خطی', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_collab_secondary_label" value="<?php echo esc_attr( $secondary_label ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'برگه مقصد دکمه خطی', 'ghahghah' ); ?></span>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => 'ghahghah_footer_collab_secondary_page',
					'selected'          => $secondary_page,
					'show_option_none'  => __( '— انتخاب برگه —', 'ghahghah' ),
					'option_none_value' => '0',
				)
			);
			?>
		</label>
		</div>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.8.3 1.6.6 2.3a2 2 0 0 1-.5 2.1L8 9.1a16 16 0 0 0 6 6l1.1-1.1a2 2 0 0 1 2.1-.5c.7.3 1.5.5 2.3.6a2 2 0 0 1 1.7 2z"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'اطلاعات تماس', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'ردیف‌های خالی یا نامعتبر نمایش داده نمی‌شوند.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'شماره تلفن‌ها (هر خط یک شماره)', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_footer_phones" rows="3" dir="ltr"><?php echo esc_textarea( $phones ); ?></textarea>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'ایمیل', 'ghahghah' ); ?></span>
			<input type="email" name="ghahghah_footer_email" value="<?php echo esc_attr( $email ); ?>" dir="ltr" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'نشانی (خطی، تمام‌عرض در فوتر)', 'ghahghah' ); ?></span>
			<textarea name="ghahghah_footer_address" rows="3"><?php echo esc_textarea( $address ); ?></textarea>
			<p class="ghahghah-field__help"><?php esc_html_e( 'می‌توانید چند بخش را در خطوط جدا بنویسید؛ در سایت به‌صورت یک خط با جداکننده «·» نمایش داده می‌شود.', 'ghahghah' ); ?></p>
		</label>
	</section>

	<?php
	$socials_raw = get_theme_mod( 'ghahghah_footer_socials', null );
	$socials     = null === $socials_raw
		? ghahghah_footer_social_defaults()
		: ghahghah_sanitize_footer_socials( $socials_raw );
	if ( array() === $socials ) {
		$socials = ghahghah_footer_social_defaults();
	}

	$render_social_row = static function ( array $row, int $index ): void {
		$label   = (string) ( $row['label'] ?? '' );
		$url     = (string) ( $row['url'] ?? '' );
		$icon_id = absint( $row['icon_id'] ?? 0 );
		$network = ghahghah_sanitize_footer_social_network( $row['network'] ?? 'custom' );
		$networks = ghahghah_footer_social_networks();
		?>
		<article class="ghahghah-steps-admin__row" data-ghahghah-social-row>
			<div class="ghahghah-steps-admin__row-head">
				<span class="ghahghah-steps-admin__badge" data-ghahghah-social-badge><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
				<div class="ghahghah-steps-admin__tools">
					<button type="button" class="ghahghah-btn ghahghah-btn--ghost ghahghah-btn--sm" data-ghahghah-social-up>
						<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 14l6-6 6 6"/></svg>
						<?php esc_html_e( 'بالا', 'ghahghah' ); ?>
					</button>
					<button type="button" class="ghahghah-btn ghahghah-btn--ghost ghahghah-btn--sm" data-ghahghah-social-down>
						<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 10l6 6 6-6"/></svg>
						<?php esc_html_e( 'پایین', 'ghahghah' ); ?>
					</button>
					<button type="button" class="ghahghah-btn ghahghah-btn--danger ghahghah-btn--sm" data-ghahghah-social-remove>
						<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M9 7V5h6v2M8 7l1 12h6l1-12"/></svg>
						<?php esc_html_e( 'حذف', 'ghahghah' ); ?>
					</button>
				</div>
			</div>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'عنوان (برای دسترسی‌پذیری)', 'ghahghah' ); ?></span>
				<input type="text" name="ghahghah_footer_social_label[]" value="<?php echo esc_attr( $label ); ?>" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'لینک', 'ghahghah' ); ?></span>
				<input type="url" name="ghahghah_footer_social_url[]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://t.me/… یا https://wa.me/…" dir="ltr" />
				<span class="ghahghah-field__help"><?php esc_html_e( 'اگر لینک خالی باشد این آیتم در فوتر نمایش داده نمی‌شود.', 'ghahghah' ); ?></span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'نوع شبکه (آیکن پیش‌فرض)', 'ghahghah' ); ?></span>
				<select name="ghahghah_footer_social_network[]">
					<?php foreach ( $networks as $key => $net_label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $network, $key ); ?>><?php echo esc_html( $net_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<?php
			ghahghah_admin_render_media_field(
				'ghahghah_footer_social_icon[]',
				__( 'آیکن سفارشی (اختیاری)', 'ghahghah' ),
				__( 'اگر خالی باشد از آیکن پیش‌فرض تلگرام/واتساپ استفاده می‌شود. برای نوع سفارشی حتماً آیکن آپلود کنید.', 'ghahghah' ),
				$icon_id,
				''
			);
			?>
		</article>
		<?php
	};
	?>

	<section class="ghahghah-panel-section" data-ghahghah-social-admin>
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'شبکه‌های اجتماعی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'لینک و آیکن تلگرام، واتساپ یا شبکه دلخواه را تنظیم کنید.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<div class="ghahghah-steps-admin__list" data-ghahghah-social-list>
			<?php foreach ( $socials as $index => $row ) : ?>
				<?php $render_social_row( $row, (int) $index ); ?>
			<?php endforeach; ?>
		</div>

		<p class="ghahghah-steps-admin__actions">
			<button type="button" class="ghahghah-btn ghahghah-btn--ghost" data-ghahghah-social-add>
				<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
				<?php esc_html_e( 'افزودن شبکه اجتماعی', 'ghahghah' ); ?>
			</button>
		</p>

		<template data-ghahghah-social-template>
			<?php
			$render_social_row(
				array(
					'label'   => '',
					'url'     => '',
					'icon_id' => 0,
					'network' => 'custom',
				),
				0
			);
			?>
		</template>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M5 8h14M7 16h10"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'حقوقی و بازگشت به بالا', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متن کپی‌رایت، حریم خصوصی و دکمه بازگشت.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن حقوقی', 'ghahghah' ); ?></span>
			<input type="text" name="ghahghah_footer_legal_text" value="<?php echo esc_attr( $legal_text ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'برگه حریم خصوصی', 'ghahghah' ); ?></span>
			<?php
			wp_dropdown_pages(
				array(
					'name'              => 'ghahghah_footer_privacy_page_id',
					'selected'          => $privacy_page,
					'show_option_none'  => __( '— پیش‌فرض وردپرس / انتخاب برگه —', 'ghahghah' ),
					'option_none_value' => '0',
				)
			);
			?>
			<p class="ghahghah-field__help"><?php esc_html_e( 'اگر خالی باشد از برگه حریم خصوصی تنظیم‌شده در وردپرس استفاده می‌شود.', 'ghahghah' ); ?></p>
		</label>
		<label class="ghahghah-switch">
			<input type="checkbox" name="ghahghah_footer_back_to_top" value="1" <?php checked( $back_to_top ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'نمایش دکمه بازگشت به بالا', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'دکمه شناور برای بازگشت به بالای صفحه.', 'ghahghah' ); ?></small>
			</span>
		</label>
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
