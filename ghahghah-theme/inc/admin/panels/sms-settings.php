<?php
/**
 * SMS gateway & pattern configuration panel.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$s          = ghahghah_get_sms_settings();
$last_saved = absint( get_theme_mod( 'ghahghah_sms_last_saved', 0 ) );
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_sms_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="sms-settings" />
	<?php wp_nonce_field( 'ghahghah_save_sms_settings', 'ghahghah_sms_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 18h4"/><path d="M9 7h6M9 10.5h6"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'سامانه پیامکی (ملی‌پیامک)', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'ارسال با پترن BaseServiceNumber — همان روش افزونه احراز هویت پیامکی. اگر فیلدهای اعتبار خالی بمانند، از تنظیمات mobile-auth خوانده می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-switch">
			<input type="checkbox" name="sms_enabled" value="1" <?php checked( ! empty( $s['sms_enabled'] ) ); ?> />
			<span class="ghahghah-switch__ui" aria-hidden="true"></span>
			<span class="ghahghah-switch__label">
				<strong><?php esc_html_e( 'فعال‌سازی ارسال پیامک', 'ghahghah' ); ?></strong>
				<small><?php esc_html_e( 'پس از ثبت موفق درخواست خرید عمده یا نمایندگی.', 'ghahghah' ); ?></small>
			</span>
		</label>

		<div class="ghahghah-field-grid ghahghah-field-grid--2">
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'نام کاربری پنل / API Key', 'ghahghah' ); ?></span>
				<input type="text" name="sms_username" value="<?php echo esc_attr( (string) $s['sms_username'] ); ?>" autocomplete="off" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'API Key (اختیاری، جایگزین نام کاربری)', 'ghahghah' ); ?></span>
				<input type="text" name="sms_api_key" value="<?php echo esc_attr( (string) $s['sms_api_key'] ); ?>" autocomplete="off" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'رمز عبور / API Secret', 'ghahghah' ); ?></span>
				<input type="password" name="sms_api_secret" value="<?php echo esc_attr( (string) $s['sms_api_secret'] ); ?>" autocomplete="new-password" />
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'خط ارسال (اختیاری)', 'ghahghah' ); ?></span>
				<input type="text" name="sms_line" value="<?php echo esc_attr( (string) $s['sms_line'] ); ?>" />
			</label>
		</div>

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین پیش‌فرض', 'ghahghah' ); ?></span>
			<input type="text" name="admin_phone" value="<?php echo esc_attr( (string) $s['admin_phone'] ); ?>" placeholder="09xxxxxxxxx" dir="ltr" />
			<span class="ghahghah-field__help"><?php esc_html_e( 'اگر شماره ادمین هر پترن خالی باشد، از این شماره استفاده می‌شود.', 'ghahghah' ); ?></span>
		</label>
	</section>

	<div class="ghahghah-panel-grid ghahghah-panel-grid--2">
		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21h18"/><path d="M5 21V9l5-3v15"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پترن خرید عمده', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متغیرها: name, phone, company, city, product, quantity, type', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-switch">
				<input type="checkbox" name="wholesale_user_enabled" value="1" <?php checked( ! empty( $s['wholesale_user_enabled'] ) ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'ارسال تأیید به کاربر', 'ghahghah' ); ?></strong>
				</span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن کاربر', 'ghahghah' ); ?></span>
				<input type="text" name="wholesale_user_pattern" value="<?php echo esc_attr( (string) $s['wholesale_user_pattern'] ); ?>" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی کاربر', 'ghahghah' ); ?></span>
				<textarea name="wholesale_user_message" rows="2"><?php echo esc_textarea( (string) $s['wholesale_user_message'] ); ?></textarea>
			</label>

			<label class="ghahghah-switch">
				<input type="checkbox" name="wholesale_admin_enabled" value="1" <?php checked( ! empty( $s['wholesale_admin_enabled'] ) ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'ارسال اعلان به ادمین', 'ghahghah' ); ?></strong>
				</span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین (عمده)', 'ghahghah' ); ?></span>
				<input type="text" name="wholesale_admin_phone" value="<?php echo esc_attr( (string) $s['wholesale_admin_phone'] ); ?>" placeholder="09xxxxxxxxx" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن ادمین', 'ghahghah' ); ?></span>
				<input type="text" name="wholesale_admin_pattern" value="<?php echo esc_attr( (string) $s['wholesale_admin_pattern'] ); ?>" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی ادمین', 'ghahghah' ); ?></span>
				<textarea name="wholesale_admin_message" rows="2"><?php echo esc_textarea( (string) $s['wholesale_admin_message'] ); ?></textarea>
			</label>
		</section>

		<section class="ghahghah-panel-section">
			<header class="ghahghah-panel-section__head">
				<span class="ghahghah-panel-section__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/></svg>
				</span>
				<div>
					<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پترن نمایندگی', 'ghahghah' ); ?></h3>
					<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متغیرها: name, phone, company, province, city, activity, experience, coverage, type', 'ghahghah' ); ?></p>
				</div>
			</header>

			<label class="ghahghah-switch">
				<input type="checkbox" name="agency_user_enabled" value="1" <?php checked( ! empty( $s['agency_user_enabled'] ) ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'ارسال تأیید به کاربر', 'ghahghah' ); ?></strong>
				</span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن کاربر', 'ghahghah' ); ?></span>
				<input type="text" name="agency_user_pattern" value="<?php echo esc_attr( (string) $s['agency_user_pattern'] ); ?>" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی کاربر', 'ghahghah' ); ?></span>
				<textarea name="agency_user_message" rows="2"><?php echo esc_textarea( (string) $s['agency_user_message'] ); ?></textarea>
			</label>

			<label class="ghahghah-switch">
				<input type="checkbox" name="agency_admin_enabled" value="1" <?php checked( ! empty( $s['agency_admin_enabled'] ) ); ?> />
				<span class="ghahghah-switch__ui" aria-hidden="true"></span>
				<span class="ghahghah-switch__label">
					<strong><?php esc_html_e( 'ارسال اعلان به ادمین', 'ghahghah' ); ?></strong>
				</span>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین (نمایندگی)', 'ghahghah' ); ?></span>
				<input type="text" name="agency_admin_phone" value="<?php echo esc_attr( (string) $s['agency_admin_phone'] ); ?>" placeholder="09xxxxxxxxx" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن ادمین', 'ghahghah' ); ?></span>
				<input type="text" name="agency_admin_pattern" value="<?php echo esc_attr( (string) $s['agency_admin_pattern'] ); ?>" dir="ltr" />
			</label>
			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی ادمین', 'ghahghah' ); ?></span>
				<textarea name="agency_admin_message" rows="2"><?php echo esc_textarea( (string) $s['agency_admin_message'] ); ?></textarea>
			</label>

			<label class="ghahghah-field">
				<span class="ghahghah-field__label"><?php esc_html_e( 'گزینه‌های زمینه فعالیت (هر خط یک مورد)', 'ghahghah' ); ?></span>
				<textarea name="agency_activities" rows="5"><?php echo esc_textarea( (string) $s['agency_activities'] ); ?></textarea>
			</label>
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
