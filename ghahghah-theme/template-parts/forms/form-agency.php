<?php
/**
 * Agency representation request form (responsive single markup).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gh_provinces  = is_array( $provinces ?? null ) ? $provinces : array();
$gh_activities = is_array( $activities ?? null ) ? $activities : array();
$gh_rest       = isset( $rest_url ) ? (string) $rest_url : '';
$gh_nonce      = isset( $nonce ) ? (string) $nonce : '';
$gh_variant    = isset( $variant ) ? (string) $variant : 'embed';
$gh_form_title = isset( $form_title ) ? trim( (string) $form_title ) : '';
$gh_home       = isset( $home_url ) && is_string( $home_url ) && '' !== $home_url ? $home_url : home_url( '/' );

if ( '' === $gh_form_title ) {
	$gh_form_title = 'page' === $gh_variant
		? __( 'اطلاعات متقاضی نمایندگی', 'ghahghah' )
		: __( 'درخواست نمایندگی', 'ghahghah' );
}

$province_options = array();
foreach ( array_keys( $gh_provinces ) as $gh_prov ) {
	$province_options[ (string) $gh_prov ] = (string) $gh_prov;
}
$activity_options = array();
foreach ( $gh_activities as $gh_act ) {
	$activity_options[ (string) $gh_act ] = (string) $gh_act;
}

$provinces_json = wp_json_encode( $gh_provinces, JSON_UNESCAPED_UNICODE );
$is_page        = 'page' === $gh_variant;
?>
<div
	class="ghahghah-form ghahghah-form--agency<?php echo $is_page ? ' ghahghah-form--page ghahghah-form--agency-page' : ''; ?>"
	data-ghahghah-form="agency"
	data-ghahghah-form-state="idle"
	data-rest-url="<?php echo esc_url( $gh_rest ); ?>"
	data-nonce="<?php echo esc_attr( $gh_nonce ); ?>"
	data-provinces="<?php echo esc_attr( is_string( $provinces_json ) ? $provinces_json : '{}' ); ?>"
>
	<header class="ghahghah-form__head">
		<div class="ghahghah-form__title-row">
			<span class="ghahghah-form__accent" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
			</span>
			<h2 class="ghahghah-form__title" tabindex="-1"><?php echo esc_html( $gh_form_title ); ?></h2>
			<span class="ghahghah-form__accent ghahghah-form__accent--flip" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent', 'mirrored' ) ) ); ?>
			</span>
		</div>
		<p class="ghahghah-form__subtitle"><?php esc_html_e( 'فیلدهای ستاره‌دار الزامی‌اند.', 'ghahghah' ); ?></p>
	</header>

	<div class="ghahghah-form__views">
		<form class="ghahghah-form__view ghahghah-form__view--idle" data-ghahghah-view="idle" novalidate>
			<div class="ghahghah-form__grid">
				<?php
				ghahghah_form_field(
					array(
						'id'           => 'gh-ag-name',
						'name'         => 'full_name',
						'label'        => __( 'نام و نام خانوادگی', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'user',
						'placeholder'  => $is_page ? __( 'مثال: علی رضایی', 'ghahghah' ) : '',
						'autocomplete' => 'name',
					)
				);
				ghahghah_form_field(
					array(
						'id'           => 'gh-ag-phone',
						'name'         => 'phone',
						'label'        => __( 'شماره تماس', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'phone',
						'type'         => 'tel',
						'placeholder'  => $is_page ? '0912 345 6789' : '09xxxxxxxxx',
						'autocomplete' => 'tel',
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-ag-company',
						'name'        => 'company',
						'label'       => __( 'نام مجموعه یا شرکت', 'ghahghah' ),
						'icon'        => 'building',
						'placeholder' => $is_page ? __( 'مثال: بازرگانی نمونه', 'ghahghah' ) : '',
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-ag-province',
						'name'        => 'province',
						'label'       => __( 'استان', 'ghahghah' ),
						'required'    => true,
						'icon'        => 'map-pin',
						'placeholder' => __( 'انتخاب کنید', 'ghahghah' ),
						'options'     => $province_options,
						'attrs'       => array( 'data-ghahghah-province' => '1' ),
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-ag-city',
						'name'        => 'city',
						'label'       => __( 'شهر', 'ghahghah' ),
						'required'    => true,
						'icon'        => 'city',
						'placeholder' => __( 'انتخاب کنید', 'ghahghah' ),
						'options'     => array(),
						'attrs'       => array( 'data-ghahghah-city' => '1' ),
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-ag-activity',
						'name'        => 'activity',
						'label'       => __( 'زمینه فعالیت', 'ghahghah' ),
						'required'    => true,
						'icon'        => 'briefcase',
						'placeholder' => __( 'انتخاب کنید', 'ghahghah' ),
						'options'     => $activity_options,
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-ag-exp',
						'name'        => 'experience',
						'label'       => __( 'سابقه پخش و فروش', 'ghahghah' ),
						'icon'        => 'chart-column',
						'placeholder' => $is_page ? __( 'مثال: 3 سال', 'ghahghah' ) : '',
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-ag-coverage',
						'name'        => 'coverage',
						'label'       => __( 'شهرهای تحت پوشش', 'ghahghah' ),
						'icon'        => 'globe',
						'placeholder' => $is_page ? __( 'مثال: تهران، کرج، قزوین', 'ghahghah' ) : '',
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-ag-msg',
						'name'        => 'message',
						'label'       => __( 'توضیحات درخواست', 'ghahghah' ),
						'icon'        => 'document',
						'type'        => 'textarea',
						'placeholder' => $is_page
							? __( 'در صورت تمایل، توضیحات بیشتری درباره کسب‌وکار و اهداف خود بنویسید...', 'ghahghah' )
							: __( 'توضیحات تکمیلی', 'ghahghah' ),
						'span'        => 'full',
					)
				);
				?>
			</div>

			<label class="ghahghah-form__consent" data-ghahghah-field="consent">
				<input type="checkbox" name="consent" value="1" required />
				<span>
					<?php
					echo esc_html(
						$is_page
							? __( 'با ثبت این فرم، موافقم اطلاعات وارد شده جهت بررسی درخواست همکاری استفاده شود.', 'ghahghah' )
							: __( 'با استفاده از اطلاعات این فرم برای بررسی درخواست نمایندگی موافقم.', 'ghahghah' )
					);
					?>
				</span>
			</label>
			<p class="ghahghah-form__error ghahghah-form__error--consent" id="gh-ag-consent-error" hidden data-ghahghah-error>
				<span class="ghahghah-form__error-icon" aria-hidden="true"><?php ghahghah_the_form_icon( 'alert-circle', array( 'modifiers' => array( 'error' ) ) ); ?></span>
				<span data-ghahghah-error-text></span>
			</p>

			<button type="submit" class="ghahghah-form__submit" data-ghahghah-submit>
				<span><?php esc_html_e( 'ثبت درخواست نمایندگی', 'ghahghah' ); ?></span>
				<?php if ( $is_page ) : ?>
					<?php ghahghah_the_form_icon( 'send', array( 'modifiers' => array( 'action' ) ) ); ?>
				<?php endif; ?>
			</button>
			<p class="ghahghah-form__legal">
				<span aria-hidden="true"><?php ghahghah_the_form_icon( 'info-circle', array( 'modifiers' => array( 'muted' ) ) ); ?></span>
				<span>
					<?php
					echo esc_html(
						$is_page
							? __( 'درخواست در سیستم ذخیره می‌شود و نتیجه از طریق پیامک اطلاع‌رسانی خواهد شد.', 'ghahghah' )
							: __( 'ثبت درخواست به معنی تأیید نمایندگی نیست.', 'ghahghah' )
					);
					?>
				</span>
			</p>
		</form>

		<div class="ghahghah-form__view ghahghah-form__view--sending" data-ghahghah-view="sending" hidden>
			<div class="ghahghah-form__status">
				<span class="ghahghah-form__status-icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'spinner', array( 'modifiers' => array( 'status', 'spinner' ) ) ); ?>
				</span>
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'در حال ثبت درخواست...', 'ghahghah' ); ?></p>
				<p class="ghahghah-form__status-text"><?php esc_html_e( 'لطفاً چند لحظه صبر کنید.', 'ghahghah' ); ?></p>
			</div>
			<button type="button" class="ghahghah-form__submit ghahghah-form__submit--disabled" disabled>
				<?php esc_html_e( 'در حال ارسال...', 'ghahghah' ); ?>
			</button>
		</div>

		<div class="ghahghah-form__view ghahghah-form__view--success" data-ghahghah-view="success" hidden>
			<div class="ghahghah-form__status">
				<span class="ghahghah-form__status-icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'check-circle', array( 'modifiers' => array( 'status', 'success' ) ) ); ?>
				</span>
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'درخواست نمایندگی ثبت شد.', 'ghahghah' ); ?></p>
				<p class="ghahghah-form__status-text"><?php esc_html_e( 'اطلاعات شما برای بررسی دریافت شد.', 'ghahghah' ); ?></p>
			</div>
			<a class="ghahghah-form__ghost-btn" href="<?php echo esc_url( $gh_home ); ?>">
				<span><?php esc_html_e( 'بازگشت به صفحه اصلی', 'ghahghah' ); ?></span>
				<?php ghahghah_the_form_icon( 'arrow-left', array( 'modifiers' => array( 'action' ) ) ); ?>
			</a>
		</div>

		<div class="ghahghah-form__view ghahghah-form__view--connection" data-ghahghah-view="connection" hidden>
			<div class="ghahghah-form__status">
				<span class="ghahghah-form__status-icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'warning-triangle', array( 'modifiers' => array( 'status', 'warning' ) ) ); ?>
				</span>
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'ارتباط قطع شد', 'ghahghah' ); ?></p>
				<p class="ghahghah-form__status-text"><?php esc_html_e( 'نتیجه ارسال مشخص نیست. دوباره تلاش کنید.', 'ghahghah' ); ?></p>
			</div>
			<button type="button" class="ghahghah-form__ghost-btn" data-ghahghah-retry>
				<?php esc_html_e( 'تلاش دوباره', 'ghahghah' ); ?>
			</button>
		</div>
	</div>
</div>
