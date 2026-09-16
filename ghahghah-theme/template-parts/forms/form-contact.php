<?php
/**
 * Contact message form.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gh_subjects   = is_array( $subjects ?? null ) ? $subjects : array();
$gh_rest       = isset( $rest_url ) ? (string) $rest_url : '';
$gh_nonce      = isset( $nonce ) ? (string) $nonce : '';
$gh_home       = isset( $home_url ) && is_string( $home_url ) && '' !== $home_url ? $home_url : home_url( '/' );
$gh_form_title = isset( $form_title ) ? trim( (string) $form_title ) : '';
$gh_form_text  = isset( $form_text ) ? trim( (string) $form_text ) : '';

if ( '' === $gh_form_title && function_exists( 'ghahghah_get_contact_page_mod' ) ) {
	$gh_form_title = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_form_title' ) );
}
if ( '' === $gh_form_text && function_exists( 'ghahghah_get_contact_page_mod' ) ) {
	$gh_form_text = trim( (string) ghahghah_get_contact_page_mod( 'ghahghah_contact_form_text' ) );
}
if ( '' === $gh_form_title ) {
	$gh_form_title = __( 'ارسال پیام برای ما', 'ghahghah' );
}

$subject_options = array();
foreach ( $gh_subjects as $gh_subj ) {
	$subject_options[ (string) $gh_subj ] = (string) $gh_subj;
}
?>
<div
	class="ghahghah-form ghahghah-form--contact ghahghah-form--page"
	data-ghahghah-form="contact"
	data-ghahghah-form-state="idle"
	data-rest-url="<?php echo esc_url( $gh_rest ); ?>"
	data-nonce="<?php echo esc_attr( $gh_nonce ); ?>"
>
	<header class="ghahghah-form__head ghahghah-form__head--contact">
		<h2 class="ghahghah-form__title ghahghah-form__title--contact" tabindex="-1"><?php echo esc_html( $gh_form_title ); ?></h2>
		<?php if ( '' !== $gh_form_text ) : ?>
			<p class="ghahghah-form__subtitle"><?php echo esc_html( $gh_form_text ); ?></p>
		<?php endif; ?>
	</header>

	<div class="ghahghah-form__views">
		<form class="ghahghah-form__view ghahghah-form__view--idle" data-ghahghah-view="idle" novalidate>
			<div class="ghahghah-form__grid ghahghah-form__grid--contact">
				<?php
				ghahghah_form_field(
					array(
						'id'           => 'gh-ct-name',
						'name'         => 'full_name',
						'label'        => __( 'نام و نام خانوادگی', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'user',
						'placeholder'  => __( 'نام و نام خانوادگی خود را وارد کنید', 'ghahghah' ),
						'autocomplete' => 'name',
						'span'         => 'full',
					)
				);
				ghahghah_form_field(
					array(
						'id'           => 'gh-ct-phone',
						'name'         => 'phone',
						'label'        => __( 'شماره تماس', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'phone',
						'type'         => 'tel',
						'placeholder'  => __( 'شماره تماس خود را وارد کنید', 'ghahghah' ),
						'autocomplete' => 'tel',
						'span'         => 'full',
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-ct-subject',
						'name'        => 'subject',
						'label'       => __( 'موضوع پیام', 'ghahghah' ),
						'required'    => true,
						'placeholder' => __( 'موضوع پیام را انتخاب کنید', 'ghahghah' ),
						'options'     => $subject_options,
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-ct-msg',
						'name'        => 'message',
						'label'       => __( 'متن پیام', 'ghahghah' ),
						'required'    => true,
						'icon'        => 'pencil',
						'type'        => 'textarea',
						'placeholder' => __( 'متن پیام خود را بنویسید', 'ghahghah' ),
						'span'        => 'full',
					)
				);
				?>
			</div>

			<button type="submit" class="ghahghah-form__submit" data-ghahghah-submit>
				<span><?php esc_html_e( 'ارسال پیام', 'ghahghah' ); ?></span>
				<?php ghahghah_the_form_icon( 'send', array( 'modifiers' => array( 'action' ) ) ); ?>
			</button>
		</form>

		<div class="ghahghah-form__view ghahghah-form__view--sending" data-ghahghah-view="sending" hidden>
			<div class="ghahghah-form__status">
				<span class="ghahghah-form__status-icon" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'spinner', array( 'modifiers' => array( 'status', 'spinner' ) ) ); ?>
				</span>
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'در حال ارسال پیام...', 'ghahghah' ); ?></p>
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
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'پیام شما ثبت شد.', 'ghahghah' ); ?></p>
				<p class="ghahghah-form__status-text"><?php esc_html_e( 'به‌زودی با شما تماس می‌گیریم.', 'ghahghah' ); ?></p>
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
