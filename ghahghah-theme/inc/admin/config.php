<?php
/**
 * Theme configuration admin screen (sidebar + nested tab URL routing).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GHAHGHAH_CONFIG_PAGE', 'ghahghah-theme-config' );
define( 'GHAHGHAH_CONFIG_TAB_PARAM', 'tab' );

require_once GHAHGHAH_THEME_DIR . '/inc/admin/media-field.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-header.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-footer.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-hero.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-featured.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-factory.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-steps.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-collab.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-articles.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-sms.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-request-pages.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-faq.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-factory-page.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-contact-page.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-archive-banners.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-theme-media.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/save-seo.php';
require_once GHAHGHAH_THEME_DIR . '/inc/admin/setup-wizard.php';

/**
 * Register the top-level theme configuration menu.
 */
function ghahghah_register_config_menu(): void {
	add_menu_page(
		__( 'پیکربندی قالب', 'ghahghah' ),
		__( 'پیکربندی قالب', 'ghahghah' ),
		'edit_theme_options',
		GHAHGHAH_CONFIG_PAGE,
		'ghahghah_render_config_page',
		'dashicons-admin-customizer',
		59
	);
}
add_action( 'admin_menu', 'ghahghah_register_config_menu' );

/**
 * Flat leaf tabs (panel files).
 *
 * @return array<string, array{label: string, description: string, icon: string}>
 */
function ghahghah_get_config_tabs(): array {
	return array(
		'header'          => array(
			'label'       => __( 'هدر دسکتاپ', 'ghahghah' ),
			'description' => __( 'لوگو، منوی اصلی، دکمه اقدام و چسبندگی نوار بالا را از اینجا تنظیم کنید.', 'ghahghah' ),
			'icon'        => 'header',
		),
		'hero'            => array(
			'label'       => __( 'اسلایدر', 'ghahghah' ),
			'description' => __( 'مدیریت بنرهای صفحه اصلی، لینک اکشن و زمان‌بندی نمایش.', 'ghahghah' ),
			'icon'        => 'hero',
		),
		'featured'        => array(
			'label'       => __( 'محصولات منتخب', 'ghahghah' ),
			'description' => __( 'انتخاب و ترتیب محصولات نمایش‌داده‌شده در صفحه اصلی.', 'ghahghah' ),
			'icon'        => 'featured',
		),
		'factory'         => array(
			'label'       => __( 'معرفی کارخانه', 'ghahghah' ),
			'description' => __( 'مدیریت محتوای بخش معرفی کارخانه که در صفحه اصلی نمایش داده می‌شود.', 'ghahghah' ),
			'icon'        => 'factory',
		),
		'steps'           => array(
			'label'       => __( 'مراحل تولید', 'ghahghah' ),
			'description' => __( 'بنر اینفوگرافیک مراحل تولید در صفحه اصلی.', 'ghahghah' ),
			'icon'        => 'steps',
		),
		'collab'          => array(
			'label'       => __( 'خرید عمده و نمایندگی', 'ghahghah' ),
			'description' => __( 'بنرهای تصویری خرید عمده و نمایندگی در صفحه اصلی.', 'ghahghah' ),
			'icon'        => 'collab',
		),
		'articles'        => array(
			'label'       => __( 'آخرین مطالب', 'ghahghah' ),
			'description' => __( 'نمایش نوشته‌های منتشرشده در صفحه اصلی.', 'ghahghah' ),
			'icon'        => 'articles',
		),
		'request-pages'   => array(
			'label'       => __( 'صفحات درخواست', 'ghahghah' ),
			'description' => __( 'برگه خرید عمده و نمایندگی، متن معرفی و تصویر.', 'ghahghah' ),
			'icon'        => 'collab',
		),
		'factory-page'    => array(
			'label'       => __( 'صفحه معرفی کارخانه', 'ghahghah' ),
			'description' => __( 'برگه کارخانه، متن‌ها و تصویر', 'ghahghah' ),
			'icon'        => 'factory',
		),
		'faq'             => array(
			'label'       => __( 'پرسش‌های متداول', 'ghahghah' ),
			'description' => __( 'برگه FAQ، معرفی، کارت‌ها و ویرایش پرسش و پاسخ‌ها', 'ghahghah' ),
			'icon'        => 'articles',
		),
		'contact-page'    => array(
			'label'       => __( 'تماس با ما', 'ghahghah' ),
			'description' => __( 'برگه تماس، متن‌ها و ساعات پاسخگویی', 'ghahghah' ),
			'icon'        => 'footer',
		),
		'archive-banners' => array(
			'label'       => __( 'بنر آرشیوها', 'ghahghah' ),
			'description' => __( 'بنر سفارشی صفحات مقالات و محصولات؛ در صورت خالی‌بودن طرح پیش‌فرض', 'ghahghah' ),
			'icon'        => 'hero',
		),
		'mobile-header'   => array(
			'label'       => __( 'هدر موبایل', 'ghahghah' ),
			'description' => __( 'لوگو و اندازه مخصوص نمایش موبایل', 'ghahghah' ),
			'icon'        => 'mobile-header',
		),
		'mobile-bottom'   => array(
			'label'       => __( 'منو و ناوبری موبایل', 'ghahghah' ),
			'description' => __( 'ناوبری چسبان پایین صفحه در موبایل', 'ghahghah' ),
			'icon'        => 'mobile-bottom',
		),
		'mobile-footer'   => array(
			'label'       => __( 'فوتر موبایل', 'ghahghah' ),
			'description' => __( 'لوگوی فوتر مخصوص نمایش موبایل', 'ghahghah' ),
			'icon'        => 'mobile-footer',
		),
		'footer'          => array(
			'label'       => __( 'فوتر', 'ghahghah' ),
			'description' => __( 'نوار همکاری، ستون‌ها، اطلاعات تماس و متن حقوقی', 'ghahghah' ),
			'icon'        => 'footer',
		),
		'seo'             => array(
			'label'       => __( 'سئو و متا', 'ghahghah' ),
			'description' => __( 'عنوان، توضیح متا و تصویر اشتراک‌گذاری صفحه اصلی', 'ghahghah' ),
			'icon'        => 'seo',
		),
		'theme-media'     => array(
			'label'       => __( 'کتابخانه رسانه', 'ghahghah' ),
			'description' => __( 'راه‌اندازی اولیه سایت + همگام‌سازی تصاویر قالب', 'ghahghah' ),
			'icon'        => 'featured',
		),
		'sms-settings'    => array(
			'label'       => __( 'پیامک', 'ghahghah' ),
			'description' => __( 'اتصال ملی‌پیامک و پترن‌های عمده / نمایندگی', 'ghahghah' ),
			'icon'        => 'sms',
		),
		'agency-requests' => array(
			'label'       => __( 'درخواست‌ها', 'ghahghah' ),
			'description' => __( 'فهرست درخواست‌های خرید عمده و نمایندگی', 'ghahghah' ),
			'icon'        => 'agency',
		),
	);
}

/**
 * Grouped sidebar navigation (IA A→E). Leaves map to panel tab slugs.
 *
 * @return array<int, array{id: string, label: string, items: array<int, string>}>
 */
function ghahghah_get_config_nav_groups(): array {
	return array(
		array(
			'id'    => 'home',
			'label' => __( 'صفحه اصلی', 'ghahghah' ),
			'items' => array( 'header', 'hero', 'featured', 'factory', 'steps', 'collab', 'articles' ),
		),
		array(
			'id'    => 'pages',
			'label' => __( 'صفحات داخلی', 'ghahghah' ),
			'items' => array( 'request-pages', 'factory-page', 'faq', 'contact-page', 'archive-banners' ),
		),
		array(
			'id'    => 'mobile',
			'label' => __( 'تنظیمات موبایل', 'ghahghah' ),
			'items' => array( 'mobile-header', 'mobile-bottom', 'mobile-footer' ),
		),
		array(
			'id'    => 'footer',
			'label' => __( 'فوتر و شبکه‌ها', 'ghahghah' ),
			'items' => array( 'footer' ),
		),
		array(
			'id'    => 'general',
			'label' => __( 'تنظیمات عمومی', 'ghahghah' ),
			'items' => array( 'seo', 'theme-media', 'sms-settings', 'agency-requests' ),
		),
	);
}

/**
 * Inline SVG icon for admin sidebar.
 *
 * @param string $icon Icon key.
 */
function ghahghah_config_tab_icon( string $icon ): void {
	$icons = array(
		'header'        => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="6" rx="1.5"/><path d="M3 14h18M3 18h12"/></svg>',
		'hero'          => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15l2.5-3 2 2 3.5-4.5L17 15"/><circle cx="9" cy="9" r="1.2"/></svg>',
		'featured'      => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="13" width="7" height="7" rx="1.5"/><rect x="14" y="13" width="7" height="7" rx="1.5"/></svg>',
		'factory'       => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V9l5-3v15"/><path d="M14 21V6l5 3v12"/><path d="M9 10h.01M9 14h.01M17 12h.01M17 16h.01"/></svg>',
		'steps'         => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>',
		'collab'        => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/><path d="M14.5 19c.8-1.6 2.2-2.5 3.5-2.5 1 0 1.8.4 2.5 1"/></svg>',
		'articles'      => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>',
		'mobile'        => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
		'mobile-header' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="6" y="3" width="12" height="18" rx="2"/><path d="M6 8h12"/></svg>',
		'mobile-bottom' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/><path d="M18 15v4M16 17h4"/></svg>',
		'mobile-footer' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="6" y="3" width="12" height="18" rx="2"/><path d="M6 16h12"/></svg>',
		'footer'        => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="14" width="18" height="6" rx="1.5"/><path d="M3 6h18M3 10h12"/></svg>',
		'agency'        => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c1.5-3 4-4.5 6-4.5S13.5 16 15 19"/></svg>',
		'sms'           => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10 18h4"/><path d="M9 7h6M9 10.5h6"/></svg>',
		'seo'           => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M8.5 11h5M11 8.5v5"/></svg>',
		'help'          => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3a7 7 0 0 0-4 12.7V18h8v-2.3A7 7 0 0 0 12 3z"/><path d="M9 21h6"/></svg>',
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Hard-coded SVG markup.
	echo $icons[ $icon ] ?? $icons['header'];
}

/**
 * Resolve and sanitize the active config tab.
 */
function ghahghah_get_active_config_tab(): string {
	$tabs      = ghahghah_get_config_tabs();
	$default   = 'header';
	$requested = '';

	if ( isset( $_GET[ GHAHGHAH_CONFIG_TAB_PARAM ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$requested = sanitize_key( wp_unslash( (string) $_GET[ GHAHGHAH_CONFIG_TAB_PARAM ] ) );
	}

	if ( '' === $requested || ! isset( $tabs[ $requested ] ) ) {
		return $default;
	}

	return $requested;
}

/**
 * Build admin URL for a configuration tab.
 *
 * @param string $tab Tab slug.
 */
function ghahghah_get_config_tab_url( string $tab ): string {
	return add_query_arg(
		array(
			'page'                    => GHAHGHAH_CONFIG_PAGE,
			GHAHGHAH_CONFIG_TAB_PARAM => $tab,
		),
		admin_url( 'admin.php' )
	);
}

/**
 * Default configuration tab slug.
 */
function ghahghah_get_default_config_tab(): string {
	return 'header';
}

/**
 * Format a config last-saved timestamp for display.
 *
 * @param int $timestamp Unix timestamp.
 */
function ghahghah_format_config_last_saved( int $timestamp ): string {
	if ( $timestamp <= 0 ) {
		return __( 'هنوز ذخیره نشده', 'ghahghah' );
	}

	$today_ymd  = wp_date( 'Y-m-d' );
	$saved_ymd  = wp_date( 'Y-m-d', $timestamp );
	$time_label = wp_date( 'H:i', $timestamp );

	if ( $today_ymd === $saved_ymd ) {
		return sprintf(
			/* translators: %s: time HH:MM */
			__( 'امروز، %s', 'ghahghah' ),
			$time_label
		);
	}

	return (string) wp_date( 'j F Y، H:i', $timestamp );
}

/**
 * Enqueue assets only on the theme configuration screen.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function ghahghah_enqueue_config_assets( string $hook_suffix ): void {
	if ( 'toplevel_page_' . GHAHGHAH_CONFIG_PAGE !== $hook_suffix ) {
		return;
	}

	$version = GHAHGHAH_THEME_VERSION;

	wp_enqueue_media();

	wp_enqueue_style(
		'ghahghah-fonts',
		GHAHGHAH_THEME_URI . '/assets/css/fonts.css',
		array(),
		$version
	);

	wp_enqueue_style(
		'ghahghah-admin-config',
		GHAHGHAH_THEME_URI . '/assets/css/admin-config.css',
		array( 'ghahghah-fonts' ),
		$version
	);

	wp_enqueue_script(
		'ghahghah-admin-config',
		GHAHGHAH_THEME_URI . '/assets/js/admin-config.js',
		array( 'media-editor' ),
		$version,
		true
	);

	wp_localize_script(
		'ghahghah-admin-config',
		'ghahghahAdminConfig',
		array(
			'tabParam'    => GHAHGHAH_CONFIG_TAB_PARAM,
			'defaultTab'  => ghahghah_get_default_config_tab(),
			'validTabs'   => array_keys( ghahghah_get_config_tabs() ),
			'mediaTitle'  => __( 'انتخاب تصویر', 'ghahghah' ),
			'mediaButton' => __( 'استفاده از این تصویر', 'ghahghah' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ghahghah_enqueue_config_assets' );

/**
 * Success message for a saved config tab.
 *
 * @param string $tab Tab slug.
 */
function ghahghah_get_config_saved_message( string $tab ): string {
	$messages = array(
		'header'          => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'hero'            => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'featured'        => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'factory'         => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'steps'           => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'collab'          => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'articles'        => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'request-pages'   => __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' ),
		'factory-page'    => __( 'تنظیمات صفحه معرفی کارخانه ذخیره شد.', 'ghahghah' ),
		'faq'             => __( 'پرسش‌های متداول ذخیره شد.', 'ghahghah' ),
		'contact-page'    => __( 'تنظیمات تماس با ما ذخیره شد.', 'ghahghah' ),
		'mobile-header'   => __( 'تغییرات هدر موبایل ذخیره شد.', 'ghahghah' ),
		'mobile-bottom'   => __( 'تغییرات نوار پایین موبایل ذخیره شد.', 'ghahghah' ),
		'mobile-footer'   => __( 'تغییرات فوتر موبایل ذخیره شد.', 'ghahghah' ),
		'footer'          => __( 'تغییرات فوتر ذخیره شد.', 'ghahghah' ),
		'archive-banners' => __( 'بنرهای آرشیو مقالات و محصولات ذخیره شد.', 'ghahghah' ),
		'theme-media'     => __( 'راه‌اندازی / همگام‌سازی رسانه انجام شد.', 'ghahghah' ),
		'seo'             => __( 'تنظیمات سئو ذخیره شد.', 'ghahghah' ),
		'sms-settings'    => __( 'تنظیمات پیامک ذخیره شد.', 'ghahghah' ),
	);

	return $messages[ $tab ] ?? __( 'تنظیمات با موفقیت ذخیره شد.', 'ghahghah' );
}

/**
 * Render the theme configuration shell.
 */
function ghahghah_render_config_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'شما اجازه دسترسی به این صفحه را ندارید.', 'ghahghah' ) );
	}

	$tabs       = ghahghah_get_config_tabs();
	$nav_groups = ghahghah_get_config_nav_groups();
	$active_tab = ghahghah_get_active_config_tab();
	$panels_dir = GHAHGHAH_THEME_DIR . '/inc/admin/panels';
	$just_saved = isset( $_GET['ghahghah_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$brand_logo = ghahghah_get_bundled_brand_asset_url( 'ghahghah-site-icon-512.png' );
	$docs_url   = (string) apply_filters( 'ghahghah_config_docs_url', '' );
	?>
	<div
		class="wrap ghahghah-config"
		data-ghahghah-config
		data-active-tab="<?php echo esc_attr( $active_tab ); ?>"
	>
		<?php if ( $just_saved ) : ?>
			<div class="ghahghah-config__toast" role="status" data-ghahghah-config-toast>
				<span class="ghahghah-config__toast-icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5"/></svg>
				</span>
				<p class="ghahghah-config__toast-text"><?php echo esc_html( ghahghah_get_config_saved_message( $active_tab ) ); ?></p>
				<button type="button" class="ghahghah-config__toast-close" data-ghahghah-toast-close aria-label="<?php esc_attr_e( 'بستن پیام', 'ghahghah' ); ?>">×</button>
			</div>
		<?php endif; ?>

		<div class="ghahghah-config__layout">
			<aside class="ghahghah-config__sidebar" aria-label="<?php esc_attr_e( 'بخش‌های پیکربندی', 'ghahghah' ); ?>">
				<div class="ghahghah-config__brand">
					<div class="ghahghah-config__brand-mark">
						<img src="<?php echo esc_url( $brand_logo ); ?>" alt="" width="44" height="44" />
					</div>
					<div class="ghahghah-config__brand-copy">
						<p class="ghahghah-config__brand-title"><?php esc_html_e( 'پنل قالب قهقهه', 'ghahghah' ); ?></p>
						<p class="ghahghah-config__brand-sub"><?php esc_html_e( 'تنظیمات قالب وردپرس', 'ghahghah' ); ?></p>
						<p class="ghahghah-config__brand-ver">
							<?php
							printf(
								/* translators: %s: theme version */
								esc_html__( 'نسخه %s', 'ghahghah' ),
								esc_html( GHAHGHAH_THEME_VERSION )
							);
							?>
						</p>
					</div>
				</div>

				<nav class="ghahghah-config__nav">
					<?php foreach ( $nav_groups as $group ) : ?>
						<section class="ghahghah-config__nav-section" data-nav-group="<?php echo esc_attr( $group['id'] ); ?>">
							<p class="ghahghah-config__nav-heading"><?php echo esc_html( $group['label'] ); ?></p>
							<ul class="ghahghah-config__nav-list">
								<?php foreach ( $group['items'] as $item_id ) : ?>
									<?php if ( ! isset( $tabs[ $item_id ] ) ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<?php $item = $tabs[ $item_id ]; ?>
									<li>
										<a
											class="ghahghah-config__nav-link<?php echo $item_id === $active_tab ? ' is-active' : ''; ?>"
											href="<?php echo esc_url( ghahghah_get_config_tab_url( $item_id ) ); ?>"
											data-ghahghah-config-tab="<?php echo esc_attr( $item_id ); ?>"
											aria-current="<?php echo $item_id === $active_tab ? 'page' : 'false'; ?>"
										>
											<span class="ghahghah-config__nav-icon" aria-hidden="true">
												<?php ghahghah_config_tab_icon( $item['icon'] ); ?>
											</span>
											<span class="ghahghah-config__nav-label"><?php echo esc_html( $item['label'] ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endforeach; ?>
				</nav>

				<div class="ghahghah-config__help">
					<span class="ghahghah-config__help-icon" aria-hidden="true">
						<?php ghahghah_config_tab_icon( 'help' ); ?>
					</span>
					<p class="ghahghah-config__help-title"><?php esc_html_e( 'نیاز به راهنمایی؟', 'ghahghah' ); ?></p>
					<p class="ghahghah-config__help-text"><?php esc_html_e( 'مستندات پنل و راهنمای تنظیمات را ببینید.', 'ghahghah' ); ?></p>
					<?php if ( '' !== $docs_url ) : ?>
						<a class="ghahghah-config__help-btn" href="<?php echo esc_url( $docs_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'مشاهده مستندات', 'ghahghah' ); ?>
						</a>
					<?php else : ?>
						<span class="ghahghah-config__help-btn ghahghah-config__help-btn--muted">
							<?php esc_html_e( 'مشاهده مستندات', 'ghahghah' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</aside>

			<main class="ghahghah-config__content" id="ghahghah-config-content" tabindex="-1">
				<?php foreach ( $tabs as $tab_slug => $tab_meta ) : ?>
					<section
						id="ghahghah-config-panel-<?php echo esc_attr( $tab_slug ); ?>"
						class="ghahghah-config__panel<?php echo $tab_slug === $active_tab ? ' is-active' : ''; ?>"
						data-ghahghah-config-panel="<?php echo esc_attr( $tab_slug ); ?>"
						<?php echo $tab_slug === $active_tab ? '' : 'hidden'; ?>
						aria-labelledby="ghahghah-config-heading-<?php echo esc_attr( $tab_slug ); ?>"
					>
						<header class="ghahghah-config__panel-header">
							<span class="ghahghah-config__panel-icon" aria-hidden="true">
								<?php ghahghah_config_tab_icon( $tab_meta['icon'] ); ?>
							</span>
							<div>
								<h2
									id="ghahghah-config-heading-<?php echo esc_attr( $tab_slug ); ?>"
									class="ghahghah-config__panel-title"
								>
									<?php echo esc_html( $tab_meta['label'] ); ?>
								</h2>
								<p class="ghahghah-config__panel-lead"><?php echo esc_html( $tab_meta['description'] ); ?></p>
							</div>
						</header>

						<div class="ghahghah-config__panel-body">
							<?php
							$panel_file = $panels_dir . '/' . $tab_slug . '.php';
							if ( is_readable( $panel_file ) ) {
								require $panel_file;
							} else {
								echo '<div class="ghahghah-config-card"><p class="ghahghah-config__placeholder">';
								esc_html_e( 'این بخش به‌زودی تکمیل می‌شود.', 'ghahghah' );
								echo '</p></div>';
							}
							?>
						</div>
					</section>
				<?php endforeach; ?>
			</main>
		</div>
	</div>
	<?php
}
