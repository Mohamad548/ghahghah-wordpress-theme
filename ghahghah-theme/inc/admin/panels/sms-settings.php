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

$s = ghahghah_get_sms_settings();
?>
<form class="ghahghah-panel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="ghahghah_save_sms_settings" />
	<input type="hidden" name="ghahghah_return_tab" value="sms-settings" />
	<?php wp_nonce_field( 'ghahghah_save_sms_settings', 'ghahghah_sms_nonce' ); ?>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<span class="ghahghah-panel-section__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 18h4"/><path d="M9 7h6M9 10.5h6"/></svg>
			</span>
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'سامانه پیامکی (ملی‌پیامک)', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'ارسال با پترن BaseServiceNumber — همان روش افزونه احراز هویت پیامکی. اگر فیلدهای اعتبار خالی بمانند، از تنظیمات mobile-auth خوانده می‌شود.', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="sms_enabled" value="1" <?php checked( ! empty( $s['sms_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'فعال‌سازی ارسال پیامک پس از ثبت موفق درخواست', 'ghahghah' ); ?></span>
		</label>

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

		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین پیش‌فرض', 'ghahghah' ); ?></span>
			<input type="text" name="admin_phone" value="<?php echo esc_attr( (string) $s['admin_phone'] ); ?>" placeholder="09xxxxxxxxx" />
			<span class="ghahghah-field__help"><?php esc_html_e( 'اگر شماره ادمین هر پترن خالی باشد، از این شماره استفاده می‌شود.', 'ghahghah' ); ?></span>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پترن خرید عمده', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متغیرها: name, phone, company, city, product, quantity, type — ترتیب {#var#} باید با پترن پنل یکی باشد. نمونه: @123456@{#name#}{#phone#}##shared', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="wholesale_user_enabled" value="1" <?php checked( ! empty( $s['wholesale_user_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'ارسال تأیید به کاربر', 'ghahghah' ); ?></span>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن کاربر', 'ghahghah' ); ?></span>
			<input type="text" name="wholesale_user_pattern" value="<?php echo esc_attr( (string) $s['wholesale_user_pattern'] ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی کاربر', 'ghahghah' ); ?></span>
			<textarea name="wholesale_user_message" rows="2"><?php echo esc_textarea( (string) $s['wholesale_user_message'] ); ?></textarea>
		</label>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="wholesale_admin_enabled" value="1" <?php checked( ! empty( $s['wholesale_admin_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'ارسال اعلان به ادمین', 'ghahghah' ); ?></span>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین (عمده)', 'ghahghah' ); ?></span>
			<input type="text" name="wholesale_admin_phone" value="<?php echo esc_attr( (string) $s['wholesale_admin_phone'] ); ?>" placeholder="09xxxxxxxxx" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن ادمین', 'ghahghah' ); ?></span>
			<input type="text" name="wholesale_admin_pattern" value="<?php echo esc_attr( (string) $s['wholesale_admin_pattern'] ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی ادمین', 'ghahghah' ); ?></span>
			<textarea name="wholesale_admin_message" rows="2"><?php echo esc_textarea( (string) $s['wholesale_admin_message'] ); ?></textarea>
		</label>
	</section>

	<section class="ghahghah-panel-section">
		<header class="ghahghah-panel-section__head">
			<div>
				<h3 class="ghahghah-panel-section__title"><?php esc_html_e( 'پترن نمایندگی', 'ghahghah' ); ?></h3>
				<p class="ghahghah-panel-section__desc"><?php esc_html_e( 'متغیرها: name, phone, company, province, city, activity, experience, coverage, type', 'ghahghah' ); ?></p>
			</div>
		</header>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="agency_user_enabled" value="1" <?php checked( ! empty( $s['agency_user_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'ارسال تأیید به کاربر', 'ghahghah' ); ?></span>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن کاربر', 'ghahghah' ); ?></span>
			<input type="text" name="agency_user_pattern" value="<?php echo esc_attr( (string) $s['agency_user_pattern'] ); ?>" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'متن الگوی کاربر', 'ghahghah' ); ?></span>
			<textarea name="agency_user_message" rows="2"><?php echo esc_textarea( (string) $s['agency_user_message'] ); ?></textarea>
		</label>

		<label class="ghahghah-field ghahghah-field--check">
			<input type="checkbox" name="agency_admin_enabled" value="1" <?php checked( ! empty( $s['agency_admin_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'ارسال اعلان به ادمین', 'ghahghah' ); ?></span>
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'شماره ادمین (نمایندگی)', 'ghahghah' ); ?></span>
			<input type="text" name="agency_admin_phone" value="<?php echo esc_attr( (string) $s['agency_admin_phone'] ); ?>" placeholder="09xxxxxxxxx" />
		</label>
		<label class="ghahghah-field">
			<span class="ghahghah-field__label"><?php esc_html_e( 'کد پترن ادمین', 'ghahghah' ); ?></span>
			<input type="text" name="agency_admin_pattern" value="<?php echo esc_attr( (string) $s['agency_admin_pattern'] ); ?>" />
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

	<p class="submit">
		<button type="submit" class="button button-primary"><?php esc_html_e( 'ذخیره تنظیمات پیامک', 'ghahghah' ); ?></button>
	</p>
</form>
