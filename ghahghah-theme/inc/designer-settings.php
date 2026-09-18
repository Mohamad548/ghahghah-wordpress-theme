<?php
/**
 * Designer / developer credit page helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default lead for the designer page.
 */
function ghahghah_designer_page_defaults(): array {
	return array(
		'ghahghah_designer_page_lead' => __( 'طراح و توسعه‌دهنده وب‌سایت قهقهه — معرفی کوتاه، تخصص‌ها، سابقه و مدارک.', 'ghahghah' ),
	);
}

/**
 * Get a designer-page theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_designer_page_mod( string $key ) {
	$defaults = ghahghah_designer_page_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Whether the current view is the designer about page.
 */
function ghahghah_is_designer_page(): bool {
	if ( is_page_template( 'page-templates/designer.php' ) ) {
		return true;
	}

	$page_id = absint( get_theme_mod( 'ghahghah_designer_page_id', 0 ) );
	return $page_id > 0 && is_page( $page_id );
}

/**
 * Public URL for the designer page (empty if missing).
 */
function ghahghah_get_designer_page_url(): string {
	$page_id = absint( get_theme_mod( 'ghahghah_designer_page_id', 0 ) );
	if ( $page_id <= 0 || ! is_post_publicly_viewable( $page_id ) ) {
		$by_path = get_page_by_path( 'mohammad-mahmoudi' );
		if ( $by_path instanceof WP_Post && is_post_publicly_viewable( $by_path ) ) {
			$page_id = (int) $by_path->ID;
		}
	}
	if ( $page_id <= 0 ) {
		return '';
	}
	$url = get_permalink( $page_id );
	return is_string( $url ) ? $url : '';
}

/**
 * Default Persian content for Mohammad Mahmoudi's about page.
 */
function ghahghah_designer_default_content(): string {
	$blocks = array(
		'<!-- wp:paragraph --><p>من <strong>محمد محمودی</strong> هستم؛ طراح و توسعه‌دهنده وب با تمرکز روی وردپرس، رابط کاربری راست‌چین و ساخت قالب‌های اختصاصی برند.</p><!-- /wp:paragraph -->',
		'<!-- wp:paragraph --><p>این وب‌سایت (قهقهه) از صفر به‌صورت قالب اختصاصی وردپرس طراحی و پیاده‌سازی شده است؛ از صفحه اصلی و کاتالوگ محصولات تا فرم‌های همکاری، پنل تنظیمات و بهینه‌سازی موبایل.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">تخصص‌ها</h2><!-- /wp:heading -->',
		'<!-- wp:list --><ul class="wp-block-list"><li>طراحی و توسعه قالب وردپرس اختصاصی (RTL / فارسی)</li><li>پیاده‌سازی UI/UX برندمحور برای وب‌سایت‌های شرکتی و کاتالوگ</li><li>HTML، CSS و JavaScript مدرن با تمرکز روی عملکرد و تجربه موبایل</li><li>PHP و معماری تمیز در وردپرس (تنظیمات قالب، رسانه، بوت‌استرپ محتوا)</li><li>بهینه‌سازی سرعت، دسترس‌پذیری و سئوی پایه</li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">سابقه و همکاری‌ها</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>طراحی و توسعه کامل قالب و افزونه همراه برند غذایی <strong>قهقهه</strong>؛ شامل هویت بصری دیجیتال، صفحات معرفی کارخانه، مطالب، فروش عمده و نمایندگی، و پنل مدیریت محتوای سایت.</p><!-- /wp:paragraph -->',
		'<!-- wp:paragraph --><p>سابقه کار روی پروژه‌های وب اختصاصی با نیاز به ظاهر برندمحور، محتوای فارسی و راه‌اندازی سریع روی هاست‌های واقعی.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">مدارک و گواهی‌ها</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>این بخش را از پیشخوان وردپرس ویرایش کنید و مدارک، دوره‌ها یا گواهی‌های خود را اضافه نمایید. نمونه ساختار:</p><!-- /wp:paragraph -->',
		'<!-- wp:list --><ul class="wp-block-list"><li>گواهی / مدرک تخصصی طراحی وب یا توسعه فرانت‌اند</li><li>گواهی مرتبط با وردپرس یا برنامه‌نویسی</li><li>سایر مدارک حرفه‌ای مرتبط با طراحی و توسعه نرم‌افزار</li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">ارتباط</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>برای همکاری در طراحی و توسعه وب‌سایت یا قالب اختصاصی وردپرس می‌توانید از طریق GitHub با من در ارتباط باشید: <a href="https://github.com/Mohamad548" target="_blank" rel="noopener noreferrer">github.com/Mohamad548</a></p><!-- /wp:paragraph -->',
	);

	return implode( "\n\n", $blocks );
}

/**
 * Ensure the designer credit page exists and is published.
 *
 * @return array{ok: bool, message: string, page_id: int, url?: string}
 */
function ghahghah_setup_designer_page(): array {
	$meta_key = '_ghahghah_designer_page';
	$page_id  = absint( get_theme_mod( 'ghahghah_designer_page_id', 0 ) );

	if ( $page_id > 0 ) {
		$page = get_post( $page_id );
		if ( ! $page || 'page' !== $page->post_type ) {
			$page_id = 0;
		}
	}

	if ( $page_id <= 0 ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => $meta_key,
				'meta_value'     => '1',
			)
		);
		if ( ! empty( $q->posts[0] ) ) {
			$page_id = (int) $q->posts[0];
		}
	}

	if ( $page_id <= 0 ) {
		$by_path = get_page_by_path( 'mohammad-mahmoudi' );
		if ( $by_path instanceof WP_Post ) {
			$page_id = (int) $by_path->ID;
		}
	}

	$created = false;
	if ( $page_id <= 0 ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'محمد محمودی',
				'post_name'    => 'mohammad-mahmoudi',
				'post_content' => ghahghah_designer_default_content(),
			),
			true
		);
		if ( is_wp_error( $page_id ) ) {
			return array(
				'ok'      => false,
				'message' => $page_id->get_error_message(),
				'page_id' => 0,
			);
		}
		$page_id = (int) $page_id;
		$created = true;
	}

	$current = get_post( $page_id );
	$update  = array( 'ID' => $page_id );

	if ( $current instanceof WP_Post ) {
		if ( 'trash' === $current->post_status ) {
			wp_untrash_post( $page_id );
			$current = get_post( $page_id );
		}
		if ( $current instanceof WP_Post && 'publish' !== $current->post_status ) {
			$update['post_status'] = 'publish';
		}
		if ( $current instanceof WP_Post && '' === trim( (string) $current->post_content ) ) {
			$update['post_content'] = ghahghah_designer_default_content();
		}
		if ( $current instanceof WP_Post && ( '' === trim( (string) $current->post_title ) || 'Designer' === $current->post_title ) ) {
			$update['post_title'] = 'محمد محمودی';
		}
	}

	if ( count( $update ) > 1 ) {
		wp_update_post( $update );
	}

	update_post_meta( $page_id, $meta_key, '1' );
	update_post_meta( $page_id, '_wp_page_template', 'page-templates/designer.php' );
	set_theme_mod( 'ghahghah_designer_page_id', $page_id );

	$url = get_permalink( $page_id );

	return array(
		'ok'      => true,
		'message' => $created
			? __( 'برگه معرفی محمد محمودی ایجاد شد.', 'ghahghah' )
			: __( 'برگه معرفی محمد محمودی به‌روز شد.', 'ghahghah' ),
		'page_id' => $page_id,
		'url'     => is_string( $url ) ? $url : '',
	);
}
