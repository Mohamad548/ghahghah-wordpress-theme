<?php
/**
 * Multi-step first-run setup wizard (marketplace-style install experience).
 *
 * After theme activation the admin is redirected here. Each step mirrors the
 * local demo bootstrap so the live host matches localhost without a separate
 * “راه‌اندازی اولیه” click.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GHAHGHAH_SETUP_WIZARD_PAGE = 'ghahghah-setup-wizard';
const GHAHGHAH_SETUP_WIZARD_OPT  = 'ghahghah_setup_wizard_done';

/**
 * Wizard steps in order.
 *
 * @return array<string, array{label: string, desc: string}>
 */
function ghahghah_setup_wizard_steps(): array {
	return array(
		'welcome'  => array(
			'label' => __( 'شروع', 'ghahghah' ),
			'desc'  => __( 'نصب چندمرحله‌ای برای رسیدن به همان ظاهر لوکال روی هاست.', 'ghahghah' ),
		),
		'core'     => array(
			'label' => __( 'هسته', 'ghahghah' ),
			'desc'  => __( 'فعال‌سازی افزونه همراه قالب (محصولات و فرم‌ها).', 'ghahghah' ),
		),
		'content'  => array(
			'label' => __( 'محتوا', 'ghahghah' ),
			'desc'  => __( 'رسانه، برگه‌ها، محصولات، مطالب و صفحه طراح.', 'ghahghah' ),
		),
		'menus'    => array(
			'label' => __( 'فهرست‌ها', 'ghahghah' ),
			'desc'  => __( 'منوی اصلی، فوتر و نوار پایین موبایل مثل لوکال.', 'ghahghah' ),
		),
		'done'     => array(
			'label' => __( 'پایان', 'ghahghah' ),
			'desc'  => __( 'سایت آماده بازدید است.', 'ghahghah' ),
		),
	);
}

/**
 * Register hidden wizard admin page.
 */
function ghahghah_register_setup_wizard_page(): void {
	add_submenu_page(
		null,
		__( 'نصب قالب قهقهه', 'ghahghah' ),
		__( 'نصب قالب قهقهه', 'ghahghah' ),
		'edit_theme_options',
		GHAHGHAH_SETUP_WIZARD_PAGE,
		'ghahghah_render_setup_wizard_page'
	);
}
add_action( 'admin_menu', 'ghahghah_register_setup_wizard_page' );

/**
 * Whether the wizard should still run.
 */
function ghahghah_setup_wizard_needed(): bool {
	if ( '1' === (string) get_option( GHAHGHAH_SETUP_WIZARD_OPT, '' ) ) {
		return false;
	}
	// Already bootstrapped recently → skip nag.
	$last = absint( get_theme_mod( 'ghahghah_bootstrap_last_run', 0 ) );
	if ( $last > 0 && ( time() - $last ) < YEAR_IN_SECONDS ) {
		return false;
	}
	return true;
}

/**
 * After theme switch: flag wizard and redirect once.
 */
function ghahghah_setup_wizard_after_switch_theme(): void {
	update_option( 'ghahghah_setup_wizard_pending', '1', false );
	delete_option( GHAHGHAH_SETUP_WIZARD_OPT );
}
add_action( 'after_switch_theme', 'ghahghah_setup_wizard_after_switch_theme', 20 );

/**
 * Redirect admin to wizard after activation.
 */
function ghahghah_setup_wizard_maybe_redirect(): void {
	if ( ! is_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	if ( wp_doing_ajax() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( '1' !== (string) get_option( 'ghahghah_setup_wizard_pending', '' ) ) {
		return;
	}
	if ( isset( $_GET['page'] ) && GHAHGHAH_SETUP_WIZARD_PAGE === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	delete_option( 'ghahghah_setup_wizard_pending' );
	wp_safe_redirect( admin_url( 'admin.php?page=' . GHAHGHAH_SETUP_WIZARD_PAGE ) );
	exit;
}
add_action( 'admin_init', 'ghahghah_setup_wizard_maybe_redirect', 1 );

/**
 * Run one wizard step and return result payload.
 *
 * @param string $step Step key.
 * @return array{ok: bool, message: string, next: string}
 */
function ghahghah_setup_wizard_run_step( string $step ): array {
	$steps = array_keys( ghahghah_setup_wizard_steps() );
	$idx   = array_search( $step, $steps, true );
	$next  = ( false !== $idx && isset( $steps[ $idx + 1 ] ) ) ? (string) $steps[ $idx + 1 ] : 'done';

	if ( 'welcome' === $step ) {
		return array(
			'ok'      => true,
			'message' => __( 'آماده شروع نصب چندمرحله‌ای.', 'ghahghah' ),
			'next'    => 'core',
		);
	}

	if ( 'core' === $step ) {
		$result = function_exists( 'ghahghah_ensure_bundled_core_plugin' )
			? ghahghah_ensure_bundled_core_plugin()
			: array( 'ok' => false, 'message' => __( 'ماژول افزونه همراه یافت نشد.', 'ghahghah' ) );
		return array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
			'next'    => 'content',
		);
	}

	if ( 'content' === $step ) {
		if ( ! function_exists( 'ghahghah_bootstrap_site' ) ) {
			require_once GHAHGHAH_THEME_DIR . '/inc/catalog/bootstrap-site.php';
		}
		$result = ghahghah_bootstrap_site(
			array(
				'media'    => true,
				'pages'    => true,
				'products' => true,
				'articles' => true,
				'menus'    => false,
				'cleanup'  => true,
			)
		);
		$msgs = array();
		foreach ( (array) ( $result['steps'] ?? array() ) as $name => $step_row ) {
			$msgs[] = $name . ': ' . (string) ( $step_row['message'] ?? '' );
		}
		return array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => implode( ' | ', $msgs ),
			'next'    => 'menus',
		);
	}

	if ( 'menus' === $step ) {
		if ( ! function_exists( 'ghahghah_bootstrap_default_menus' ) ) {
			require_once GHAHGHAH_THEME_DIR . '/inc/catalog/setup-menus.php';
		}
		$result = ghahghah_bootstrap_default_menus();
		return array(
			'ok'      => ! empty( $result['ok'] ),
			'message' => (string) ( $result['message'] ?? '' ),
			'next'    => 'done',
		);
	}

	if ( 'done' === $step ) {
		update_option( GHAHGHAH_SETUP_WIZARD_OPT, '1', false );
		set_theme_mod( 'ghahghah_bootstrap_last_run', time() );
		return array(
			'ok'      => true,
			'message' => __( 'نصب کامل شد.', 'ghahghah' ),
			'next'    => 'done',
		);
	}

	return array(
		'ok'      => false,
		'message' => __( 'مرحله نامعتبر.', 'ghahghah' ),
		'next'    => 'welcome',
	);
}

/**
 * AJAX: run wizard step.
 */
function ghahghah_ajax_setup_wizard_step(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'ghahghah' ) ), 403 );
	}
	check_ajax_referer( 'ghahghah_setup_wizard', 'nonce' );

	$step = isset( $_POST['step'] ) ? sanitize_key( (string) wp_unslash( $_POST['step'] ) ) : '';
	if ( '' === $step ) {
		wp_send_json_error( array( 'message' => __( 'مرحله خالی است.', 'ghahghah' ) ) );
	}

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	$result = ghahghah_setup_wizard_run_step( $step );
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_ghahghah_setup_wizard_step', 'ghahghah_ajax_setup_wizard_step' );

/**
 * Enqueue wizard assets only on wizard page.
 *
 * @param string $hook Hook suffix.
 */
function ghahghah_setup_wizard_assets( string $hook ): void {
	if ( ! isset( $_GET['page'] ) || GHAHGHAH_SETUP_WIZARD_PAGE !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_enqueue_style(
		'ghahghah-setup-wizard',
		GHAHGHAH_THEME_URI . '/assets/css/admin-setup-wizard.css',
		array(),
		GHAHGHAH_THEME_VERSION
	);
	wp_enqueue_script(
		'ghahghah-setup-wizard',
		GHAHGHAH_THEME_URI . '/assets/js/admin-setup-wizard.js',
		array(),
		GHAHGHAH_THEME_VERSION,
		true
	);
	wp_localize_script(
		'ghahghah-setup-wizard',
		'ghahghahSetupWizard',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ghahghah_setup_wizard' ),
			'homeUrl' => home_url( '/' ),
			'configUrl' => admin_url( 'admin.php?page=ghahghah-theme-config' ),
			'i18n'    => array(
				'running' => __( 'در حال اجرا…', 'ghahghah' ),
				'failed'  => __( 'این مرحله با خطا روبه‌رو شد.', 'ghahghah' ),
				'next'    => __( 'ادامه', 'ghahghah' ),
				'finish'  => __( 'مشاهده سایت', 'ghahghah' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'ghahghah_setup_wizard_assets' );

/**
 * Render wizard UI.
 */
function ghahghah_render_setup_wizard_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ) );
	}

	$steps = ghahghah_setup_wizard_steps();
	?>
	<div class="wrap ghahghah-wizard" dir="rtl">
		<div class="ghahghah-wizard__card">
			<header class="ghahghah-wizard__head">
				<p class="ghahghah-wizard__eyebrow"><?php esc_html_e( 'نصب آسان قالب قهقهه', 'ghahghah' ); ?></p>
				<h1 class="ghahghah-wizard__title"><?php esc_html_e( 'راه‌اندازی چندمرحله‌ای مثل دموی لوکال', 'ghahghah' ); ?></h1>
				<p class="ghahghah-wizard__lead">
					<?php esc_html_e( 'بدون نصب دستی افزونه جدا و بدون تنظیم فهرست: هسته، محتوا، منوها و تصاویر را قدم‌به‌قدم مثل پیش‌نمایش آماده می‌کنیم.', 'ghahghah' ); ?>
				</p>
			</header>

			<ol class="ghahghah-wizard__steps" data-ghahghah-wizard-steps>
				<?php foreach ( $steps as $key => $step ) : ?>
					<li class="ghahghah-wizard__step<?php echo 'welcome' === $key ? ' is-current' : ''; ?>" data-step="<?php echo esc_attr( $key ); ?>">
						<span class="ghahghah-wizard__step-index" aria-hidden="true"></span>
						<span class="ghahghah-wizard__step-label"><?php echo esc_html( $step['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>

			<section class="ghahghah-wizard__panel" data-ghahghah-wizard-panel>
				<?php foreach ( $steps as $key => $step ) : ?>
					<div class="ghahghah-wizard__pane<?php echo 'welcome' === $key ? ' is-active' : ''; ?>" data-pane="<?php echo esc_attr( $key ); ?>">
						<h2><?php echo esc_html( $step['label'] ); ?></h2>
						<p><?php echo esc_html( $step['desc'] ); ?></p>
						<?php if ( 'welcome' === $key ) : ?>
							<ul class="ghahghah-wizard__checklist">
								<li><?php esc_html_e( 'افزونه Core از داخل قالب فعال می‌شود', 'ghahghah' ); ?></li>
								<li><?php esc_html_e( 'محصولات، برگه‌ها، مطالب و تصاویر وارد می‌شوند', 'ghahghah' ); ?></li>
								<li><?php esc_html_e( 'منوی اصلی مثل لوکال ساخته می‌شود', 'ghahghah' ); ?></li>
							</ul>
						<?php endif; ?>
						<?php if ( 'done' === $key ) : ?>
							<p class="ghahghah-wizard__success"><?php esc_html_e( 'سایت آماده است. می‌توانید صفحه اصلی را باز کنید یا تنظیمات قالب را ادامه دهید.', 'ghahghah' ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</section>

			<p class="ghahghah-wizard__log" data-ghahghah-wizard-log hidden></p>

			<footer class="ghahghah-wizard__actions">
				<button type="button" class="button button-primary button-hero" data-ghahghah-wizard-next>
					<?php esc_html_e( 'شروع نصب', 'ghahghah' ); ?>
				</button>
				<a class="button button-hero" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-ghahghah-wizard-home hidden>
					<?php esc_html_e( 'مشاهده سایت', 'ghahghah' ); ?>
				</a>
			</footer>
		</div>
	</div>
	<?php
}
