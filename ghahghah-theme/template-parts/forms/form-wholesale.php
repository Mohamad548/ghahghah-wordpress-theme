<?php
/**
 * Wholesale purchase request form (responsive single markup).
 *
 * Vars: $cities, $products, $rest_url, $nonce, $products_archive, $variant, $form_title, $preselect_product
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gh_cities     = is_array( $cities ?? null ) ? $cities : array();
$gh_products   = is_array( $products ?? null ) ? $products : array();
$gh_rest       = isset( $rest_url ) ? (string) $rest_url : '';
$gh_nonce      = isset( $nonce ) ? (string) $nonce : '';
$gh_variant    = isset( $variant ) ? (string) $variant : 'embed';
$gh_form_title = isset( $form_title ) ? trim( (string) $form_title ) : '';
$gh_preselect  = isset( $preselect_product ) ? absint( $preselect_product ) : 0;
$gh_archive    = isset( $products_archive ) && is_string( $products_archive ) && '' !== $products_archive
	? $products_archive
	: home_url( '/' );

if ( '' === $gh_form_title ) {
	$gh_form_title = 'page' === $gh_variant
		? __( 'اطلاعات درخواست خرید عمده', 'ghahghah' )
		: __( 'درخواست خرید عمده', 'ghahghah' );
}

$product_options = array();
foreach ( $gh_products as $gh_p ) {
	if ( is_array( $gh_p ) && isset( $gh_p['id'], $gh_p['title'] ) ) {
		$product_options[ (string) (int) $gh_p['id'] ] = (string) $gh_p['title'];
	}
}
$city_options = array();
foreach ( $gh_cities as $gh_c ) {
	$city_options[ (string) $gh_c ] = (string) $gh_c;
}

$is_page   = 'page' === $gh_variant;
$sms_note  = $is_page ? trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_sms_note' ) ) : '';
$price_note = $is_page ? trim( (string) ghahghah_get_request_mod( 'ghahghah_wholesale_price_note' ) ) : '';
?>
<div
	class="ghahghah-form ghahghah-form--wholesale<?php echo $is_page ? ' ghahghah-form--page ghahghah-form--wholesale-page' : ''; ?>"
	data-ghahghah-form="wholesale"
	data-ghahghah-form-state="idle"
	data-rest-url="<?php echo esc_url( $gh_rest ); ?>"
	data-nonce="<?php echo esc_attr( $gh_nonce ); ?>"
	<?php if ( $gh_preselect > 0 ) : ?>
		data-preselect-product="<?php echo esc_attr( (string) $gh_preselect ); ?>"
	<?php endif; ?>
>
	<?php if ( ! $is_page ) : ?>
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
	<?php endif; ?>

	<div class="ghahghah-form__views">
		<form class="ghahghah-form__view ghahghah-form__view--idle" data-ghahghah-view="idle" novalidate>
			<div class="ghahghah-form__grid">
				<?php
				ghahghah_form_field(
					array(
						'id'           => 'gh-wh-name',
						'name'         => 'full_name',
						'label'        => __( 'نام و نام خانوادگی', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'user',
						'placeholder'  => $is_page ? __( 'نام و نام خانوادگی خود را وارد کنید', 'ghahghah' ) : '',
						'autocomplete' => 'name',
					)
				);
				ghahghah_form_field(
					array(
						'id'           => 'gh-wh-phone',
						'name'         => 'phone',
						'label'        => __( 'شماره تماس', 'ghahghah' ),
						'required'     => true,
						'icon'         => 'phone',
						'type'         => 'tel',
						'placeholder'  => $is_page ? __( 'شماره تماس خود را وارد کنید', 'ghahghah' ) : '09xxxxxxxxx',
						'autocomplete' => 'tel',
					)
				);
				ghahghah_form_field(
					array(
						'id'          => 'gh-wh-company',
						'name'        => 'company',
						'label'       => __( 'نام مجموعه یا فروشگاه', 'ghahghah' ),
						'required'    => true,
						'icon'        => $is_page ? 'store' : 'building',
						'placeholder' => $is_page ? __( 'نام مجموعه یا فروشگاه را وارد کنید', 'ghahghah' ) : '',
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-wh-city',
						'name'        => 'city',
						'label'       => __( 'شهر', 'ghahghah' ),
						'required'    => true,
						'icon'        => 'map-pin',
						'placeholder' => $is_page ? __( 'شهر خود را انتخاب کنید', 'ghahghah' ) : __( 'انتخاب شهر', 'ghahghah' ),
						'options'     => $city_options,
					)
				);
				ghahghah_form_select(
					array(
						'id'          => 'gh-wh-product',
						'name'        => 'product',
						'label'       => __( 'محصول موردنظر', 'ghahghah' ),
						'required'    => true,
						'icon'        => $is_page ? 'box' : 'package',
						'placeholder' => $is_page ? __( 'طعم پیتزا', 'ghahghah' ) : __( 'انتخاب محصول', 'ghahghah' ),
						'options'     => $product_options,
						'attrs'       => array( 'data-ghahghah-product' => '1' ),
					)
				);
				?>
				<div class="ghahghah-form__field" data-ghahghah-field="quantity">
					<label class="ghahghah-form__label" for="gh-wh-qty">
						<?php esc_html_e( 'تعداد تقریبی سفارش', 'ghahghah' ); ?>
						<span class="ghahghah-form__req" aria-hidden="true">*</span>
					</label>
					<div class="ghahghah-form__control">
						<span class="ghahghah-form__icon" aria-hidden="true">
							<?php ghahghah_the_form_icon( 'hash', array( 'modifiers' => array( 'muted' ) ) ); ?>
						</span>
						<input
							class="ghahghah-form__input"
							type="text"
							id="gh-wh-qty"
							name="quantity"
							required
							placeholder="<?php echo esc_attr( $is_page ? __( 'تعداد تقریبی سفارش را وارد کنید', 'ghahghah' ) : '' ); ?>"
							aria-describedby="gh-wh-qty-help gh-wh-qty-error"
						/>
					</div>
					<?php if ( ! $is_page ) : ?>
						<p class="ghahghah-form__field-help" id="gh-wh-qty-help"><?php esc_html_e( 'واحد را هم بنویسید؛ مثلاً کارتن.', 'ghahghah' ); ?></p>
					<?php else : ?>
						<span id="gh-wh-qty-help" class="screen-reader-text"><?php esc_html_e( 'تعداد تقریبی سفارش', 'ghahghah' ); ?></span>
					<?php endif; ?>
					<p class="ghahghah-form__error" id="gh-wh-qty-error" hidden data-ghahghah-error>
						<span class="ghahghah-form__error-icon" aria-hidden="true"><?php ghahghah_the_form_icon( 'alert-circle', array( 'modifiers' => array( 'error' ) ) ); ?></span>
						<span data-ghahghah-error-text></span>
					</p>
				</div>
				<?php
				ghahghah_form_field(
					array(
						'id'          => 'gh-wh-msg',
						'name'        => 'message',
						'label'       => __( 'توضیحات درخواست', 'ghahghah' ),
						'icon'        => $is_page ? 'note' : 'pencil',
						'type'        => 'textarea',
						'placeholder' => $is_page
							? __( 'در صورت نیاز توضیحات بیشتری ارائه دهید...', 'ghahghah' )
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
							? __( 'با ثبت درخواست، با تماس واحد فروش موافقم.', 'ghahghah' )
							: __( 'با تماس برای پیگیری این درخواست موافقم.', 'ghahghah' )
					);
					?>
				</span>
			</label>
			<p class="ghahghah-form__error ghahghah-form__error--consent" id="gh-wh-consent-error" hidden data-ghahghah-error>
				<span class="ghahghah-form__error-icon" aria-hidden="true"><?php ghahghah_the_form_icon( 'alert-circle', array( 'modifiers' => array( 'error' ) ) ); ?></span>
				<span data-ghahghah-error-text></span>
			</p>

			<button type="submit" class="ghahghah-form__submit" data-ghahghah-submit>
				<span><?php esc_html_e( 'ثبت درخواست خرید عمده', 'ghahghah' ); ?></span>
				<?php if ( $is_page ) : ?>
					<?php ghahghah_the_form_icon( 'send', array( 'modifiers' => array( 'action' ) ) ); ?>
				<?php endif; ?>
			</button>

			<?php if ( $is_page ) : ?>
				<?php if ( '' !== $sms_note ) : ?>
					<p class="ghahghah-form__notice ghahghah-form__notice--sms">
						<span aria-hidden="true"><?php ghahghah_the_form_icon( 'bell', array( 'modifiers' => array( 'notice' ) ) ); ?></span>
						<span><?php echo esc_html( $sms_note ); ?></span>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $price_note ) : ?>
					<p class="ghahghah-form__notice ghahghah-form__notice--price">
						<span aria-hidden="true"><?php ghahghah_the_form_icon( 'info', array( 'modifiers' => array( 'notice' ) ) ); ?></span>
						<span><?php echo esc_html( $price_note ); ?></span>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<p class="ghahghah-form__legal">
					<span aria-hidden="true"><?php ghahghah_the_form_icon( 'info-circle', array( 'modifiers' => array( 'muted' ) ) ); ?></span>
					<span><?php esc_html_e( 'ثبت این فرم به معنی نهایی‌شدن سفارش نیست.', 'ghahghah' ); ?></span>
				</p>
			<?php endif; ?>
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
				<p class="ghahghah-form__status-title"><?php esc_html_e( 'درخواست خرید عمده ثبت شد.', 'ghahghah' ); ?></p>
				<p class="ghahghah-form__status-text"><?php esc_html_e( 'اطلاعات شما برای بررسی دریافت شد.', 'ghahghah' ); ?></p>
			</div>
			<a class="ghahghah-form__ghost-btn" href="<?php echo esc_url( $gh_archive ); ?>">
				<span><?php esc_html_e( 'بازگشت به محصولات', 'ghahghah' ); ?></span>
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
