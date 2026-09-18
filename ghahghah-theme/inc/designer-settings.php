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

/** Bump when default designer page body should be refreshed. */
const GHAHGHAH_DESIGNER_CONTENT_VERSION = 2;

/**
 * Default lead for the designer page.
 */
function ghahghah_designer_page_defaults(): array {
	return array(
		'ghahghah_designer_page_lead' => __( 'توسعه‌دهنده فرانت‌اند — ساخت تجربهٔ وب دقیق، سریع و قابل‌مقیاس', 'ghahghah' ),
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
 * Public URL for the designer page (creates the page once if missing).
 */
function ghahghah_get_designer_page_url(): string {
	$page_id = absint( get_theme_mod( 'ghahghah_designer_page_id', 0 ) );
	if ( $page_id <= 0 || ! is_post_publicly_viewable( $page_id ) ) {
		$by_path = get_page_by_path( 'mohammad-mahmoudi' );
		if ( $by_path instanceof WP_Post && is_post_publicly_viewable( $by_path ) ) {
			$page_id = (int) $by_path->ID;
			set_theme_mod( 'ghahghah_designer_page_id', $page_id );
		}
	}

	if ( $page_id <= 0 && function_exists( 'ghahghah_setup_designer_page' ) ) {
		$result  = ghahghah_setup_designer_page();
		$page_id = absint( $result['page_id'] ?? 0 );
		if ( ! empty( $result['url'] ) && is_string( $result['url'] ) ) {
			return $result['url'];
		}
	}

	if ( $page_id <= 0 || ! is_post_publicly_viewable( $page_id ) ) {
		return '';
	}

	$url = get_permalink( $page_id );
	return is_string( $url ) ? $url : '';
}

/**
 * Bundled media URL for a designer asset under assets/images/designer/.
 *
 * @param string $file Filename.
 */
function ghahghah_get_designer_bundled_image_url( string $file ): string {
	$file = ltrim( str_replace( '\\', '/', $file ), '/' );
	$token = 'designer/' . $file;

	if ( function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
		$id = ghahghah_find_theme_media_attachment( $token );
		if ( $id > 0 ) {
			$url = wp_get_attachment_image_url( $id, 'large' );
			if ( ! is_string( $url ) || '' === $url ) {
				$url = wp_get_attachment_url( $id );
			}
			if ( is_string( $url ) && '' !== $url ) {
				return $url;
			}
		}
	}

	$path = GHAHGHAH_THEME_DIR . '/assets/images/' . $token;
	if ( ! is_readable( $path ) ) {
		return '';
	}

	return GHAHGHAH_THEME_URI . '/assets/images/' . $token;
}

/**
 * Profile photo URL.
 */
function ghahghah_get_designer_profile_url(): string {
	return ghahghah_get_designer_bundled_image_url( 'profile.jpg' );
}

/**
 * Maktab Sharif certificate image URL.
 */
function ghahghah_get_designer_certificate_url(): string {
	$id = absint( get_theme_mod( 'ghahghah_designer_certificate_image_id', 0 ) );
	if ( $id > 0 ) {
		$url = wp_get_attachment_image_url( $id, 'large' );
		if ( ! is_string( $url ) || '' === $url ) {
			$url = wp_get_attachment_url( $id );
		}
		if ( is_string( $url ) && '' !== $url ) {
			return $url;
		}
	}

	return ghahghah_get_designer_bundled_image_url( 'maktab-certificate.jpg' );
}

/**
 * Default Persian content for Mohammad Mahmoudi's about page (from portfolio).
 */
function ghahghah_designer_default_content(): string {
	$blocks = array(
		'<!-- wp:paragraph --><p>من <strong>محمد محمودی</strong> هستم؛ توسعه‌دهنده فرانت‌اند با تمرکز روی وردپرس، رابط کاربری راست‌چین و ساخت تجربه‌های وب دقیق و سریع. از سال ۲۰۲۰ فروشگاه‌ها و افزونه‌های اختصاصی وردپرس ساخته‌ام و قالب <strong>قهقهه</strong> را به‌صورت تم اختصاصی طراحی و پیاده‌سازی کرده‌ام.</p><!-- /wp:paragraph -->',
		'<!-- wp:paragraph --><p>من یک توسعه‌دهنده پرشور فرانت‌اند هستم با چندین سال تجربه در طراحی و توسعه وب‌سایت‌های کاربردی. از سال ۲۰۲۰ با وردپرس فروشگاه‌های تجارت الکترونیک ساختم و در سال ۲۰۲۳ برای ارتقای مهارت‌ها در دورهٔ React و Next.js مکتب شریف (+۴۰۰ ساعت) ثبت‌نام کردم. همیشه کیفیت و دقت را اولویت می‌دهم و هدفم ساخت تجربیات کاربری منحصربه‌فرد است.</p><!-- /wp:paragraph -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">تخصص‌ها</h2><!-- /wp:heading -->',
		'<!-- wp:list --><ul class="wp-block-list"><li>طراحی و توسعه قالب وردپرس اختصاصی (RTL / فارسی)</li><li>ووکامرس، افزونه‌های اختصاصی، AJAX و پنل ادمین</li><li>HTML، CSS، JavaScript، Tailwind؛ React / Next.js برای پنل‌های مکمل</li><li>بهینه‌سازی سرعت (Core Web Vitals) و تجربه موبایل</li><li>ساخت فروشگاه‌ها و ابزارهای محتوا از وردپرس تا Next.js</li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">سابقه و همکاری‌ها</h2><!-- /wp:heading -->',
		'<!-- wp:list --><ul class="wp-block-list"><li><strong>۲۰۲۰</strong> — شروع با وردپرس و فروشگاه‌های تجارت الکترونیک</li><li><strong>۲۰۲۲</strong> — توسعه فرانت و سفارشی‌سازی ووکامرس در شاهین تجارت اطمینان</li><li><strong>۲۰۲۳</strong> — بوت‌کمپ React / Next.js مکتب شریف (+۴۰۰ ساعت)</li><li><strong>اکنون</strong> — توسعه‌دهنده Front-End در کسرا امیننس؛ پنل‌های Next.js، یکپارچه‌سازی وردپرس و بهینه‌سازی سرعت</li><li>طراحی و توسعه کامل قالب اختصاصی برند غذایی <strong>قهقهه</strong></li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">تحصیلات و مدارک</h2><!-- /wp:heading -->',
		'<!-- wp:list --><ul class="wp-block-list"><li>کاردانی مهندسی نقشه‌برداری — دانشکده فنی کسرا کرمانشاه — ۱۳۹۳ تا ۱۳۹۵</li><li>گواهینامه دوره React — مکتب شریف — ۱۴۰۲ / ۲۰۲۳ (+۴۰۰ ساعت)</li></ul><!-- /wp:list -->',
		'<!-- wp:heading --><h2 class="wp-block-heading">ارتباط</h2><!-- /wp:heading -->',
		'<!-- wp:paragraph --><p>ایمیل: <a href="mailto:mahmodim222@gmail.com">mahmodim222@gmail.com</a> · تلفن: <a href="tel:+989185480383" dir="ltr">09185480383</a></p><!-- /wp:paragraph -->',
		'<!-- wp:paragraph --><p>پورتفolio: <a href="https://mohamadmahmodi.ir" target="_blank" rel="noopener noreferrer">mohamadmahmodi.ir</a> · LinkedIn: <a href="https://www.linkedin.com/in/mohamadmahmodi/" target="_blank" rel="noopener noreferrer">mohamadmahmodi</a> · GitHub: <a href="https://github.com/Mohamad548" target="_blank" rel="noopener noreferrer">Mohamad548</a></p><!-- /wp:paragraph -->',
	);

	return implode( "\n\n", $blocks );
}

/**
 * Whether designer page content should be refreshed to the bundled default.
 *
 * @param WP_Post $post Page post.
 */
function ghahghah_designer_content_needs_refresh( WP_Post $post ): bool {
	$stored = absint( get_post_meta( $post->ID, '_ghahghah_designer_content_version', true ) );
	if ( $stored < GHAHGHAH_DESIGNER_CONTENT_VERSION ) {
		return true;
	}

	$content = (string) $post->post_content;
	return (
		'' === trim( $content )
		|| false !== strpos( $content, 'این بخش را از پیشخوان وردپرس ویرایش کنید' )
		|| false !== strpos( $content, 'گواهی / مدرک تخصصی طراحی وب' )
	);
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
		update_post_meta( $page_id, '_ghahghah_designer_content_version', GHAHGHAH_DESIGNER_CONTENT_VERSION );
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
		if ( $current instanceof WP_Post && ghahghah_designer_content_needs_refresh( $current ) ) {
			$update['post_content'] = ghahghah_designer_default_content();
			update_post_meta( $page_id, '_ghahghah_designer_content_version', GHAHGHAH_DESIGNER_CONTENT_VERSION );
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

	// Bind profile as featured image when available.
	if ( function_exists( 'ghahghah_find_theme_media_attachment' ) && ! has_post_thumbnail( $page_id ) ) {
		$profile_id = ghahghah_find_theme_media_attachment( 'designer/profile.jpg' );
		if ( $profile_id > 0 ) {
			set_post_thumbnail( $page_id, $profile_id );
		}
	}

	$cert_id = absint( get_theme_mod( 'ghahghah_designer_certificate_image_id', 0 ) );
	if ( $cert_id <= 0 && function_exists( 'ghahghah_find_theme_media_attachment' ) ) {
		$cert_id = ghahghah_find_theme_media_attachment( 'designer/maktab-certificate.jpg' );
		if ( $cert_id > 0 ) {
			set_theme_mod( 'ghahghah_designer_certificate_image_id', $cert_id );
		}
	}

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
