<?php
/**
 * Mobile bottom navigation: location, icons, settings, render helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GHAHGHAH_BOTTOM_NAV_MIN         = 2;
const GHAHGHAH_BOTTOM_NAV_MAX         = 5;
const GHAHGHAH_BOTTOM_NAV_ICON_META   = '_ghahghah_bottom_nav_icon';
const GHAHGHAH_BOTTOM_NAV_PRODUCT_CPT = 'ghahghah_product';

/**
 * Allowed bottom-nav icon keys.
 *
 * @return array<int, string>
 */
function ghahghah_bottom_nav_allowed_icons(): array {
	return array( 'home', 'products', 'wholesale', 'contact' );
}

/**
 * Human labels for icon keys.
 *
 * @return array<string, string>
 */
function ghahghah_bottom_nav_icon_labels(): array {
	return array(
		'home'      => __( 'خانه', 'ghahghah' ),
		'products'  => __( 'محصولات', 'ghahghah' ),
		'wholesale' => __( 'خرید عمده', 'ghahghah' ),
		'contact'   => __( 'تماس', 'ghahghah' ),
	);
}

/**
 * Whether the mobile bottom nav is enabled (default on).
 */
function ghahghah_is_bottom_nav_enabled(): bool {
	return (bool) get_theme_mod( 'ghahghah_bottom_nav_enabled', true );
}

/**
 * Sanitize enabled flag.
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_bottom_nav_enabled( $value ): bool {
	return (bool) $value;
}

/**
 * Sanitize icon key; empty/invalid returns empty string (caller may infer).
 *
 * @param mixed $value Raw value.
 */
function ghahghah_sanitize_bottom_nav_icon_key( $value ): string {
	$key = sanitize_key( (string) $value );
	if ( ! in_array( $key, ghahghah_bottom_nav_allowed_icons(), true ) ) {
		return '';
	}
	return $key;
}

/**
 * Infer icon key from a menu item title / URL when meta is missing.
 *
 * @param WP_Post|object $item Nav menu item.
 */
function ghahghah_infer_bottom_nav_icon( $item ): string {
	$title = '';
	$url   = '';
	if ( is_object( $item ) ) {
		$title = isset( $item->title ) ? (string) $item->title : '';
		$url   = isset( $item->url ) ? (string) $item->url : '';
	}

	$title_l = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );
	$url_l   = strtolower( $url );
	$home    = ghahghah_normalize_bottom_nav_url( home_url( '/' ) );
	$norm    = ghahghah_normalize_bottom_nav_url( $url );

	if ( $norm === $home || false !== strpos( $title_l, 'خانه' ) || false !== strpos( $title_l, 'home' ) ) {
		return 'home';
	}

	if (
		ghahghah_is_bottom_nav_products_archive_url( $url )
		|| false !== strpos( $title_l, 'محصول' )
		|| false !== strpos( $title_l, 'product' )
		|| false !== strpos( $url_l, '/products' )
		|| false !== strpos( $url_l, 'ghahghah_product' )
	) {
		return 'products';
	}

	if (
		false !== strpos( $title_l, 'عمده' )
		|| false !== strpos( $title_l, 'wholesale' )
		|| false !== strpos( $url_l, 'wholesale' )
	) {
		return 'wholesale';
	}

	if (
		false !== strpos( $title_l, 'تماس' )
		|| false !== strpos( $title_l, 'contact' )
		|| false !== strpos( $url_l, 'contact' )
	) {
		return 'contact';
	}

	return 'products';
}

/**
 * Absolute path to a trusted bottom-nav SVG.
 *
 * @param string $key Icon key.
 */
function ghahghah_get_bottom_nav_icon_path( string $key ): string {
	$key = ghahghah_sanitize_bottom_nav_icon_key( $key );
	if ( '' === $key ) {
		$key = 'products';
	}
	return GHAHGHAH_THEME_DIR . '/assets/icons/bottom-nav/' . $key . '.svg';
}

/**
 * Menu term ID assigned to mobile_bottom location (0 if none).
 */
function ghahghah_get_bottom_nav_menu_id(): int {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['mobile_bottom'] ) ) {
		return 0;
	}
	return absint( $locations['mobile_bottom'] );
}

/**
 * Whether a menu item URL is usable on the front end.
 *
 * @param string $url Raw URL.
 */
function ghahghah_is_bottom_nav_url_public( string $url ): bool {
	$url = trim( $url );
	if ( '' === $url || '#' === $url || 0 === strpos( $url, '#' ) ) {
		return false;
	}
	return '' !== esc_url_raw( $url );
}

/**
 * Normalize URL for comparison.
 *
 * @param string $url URL.
 */
function ghahghah_normalize_bottom_nav_url( string $url ): string {
	$url  = untrailingslashit( strtolower( $url ) );
	$home = untrailingslashit( strtolower( home_url( '/' ) ) );
	if ( $url === $home || $url === $home . '/index.php' ) {
		return $home;
	}
	return $url;
}

/**
 * Diagnostic / validation state for the assigned menu.
 *
 * @return array{ok: bool, menu_id: int, items: array<int, WP_Post>, messages: array<int, string>}
 */
function ghahghah_get_bottom_nav_validation(): array {
	$messages = array();
	$menu_id  = ghahghah_get_bottom_nav_menu_id();

	if ( $menu_id <= 0 ) {
		$messages[] = __( 'فهرستی به جایگاه «ناوبری پایین موبایل» اختصاص داده نشده است. از نمایش ← فهرست‌ها یک فهرست را به این جایگاه وصل کنید.', 'ghahghah' );
		return array(
			'ok'       => false,
			'menu_id'  => 0,
			'items'    => array(),
			'messages' => $messages,
		);
	}

	$raw = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $raw ) || array() === $raw ) {
		$messages[] = __( 'فهرست ناوبری پایین موبایل خالی است. حداقل ۲ گزینه سطح‌اول با مقصد واقعی اضافه کنید.', 'ghahghah' );
		return array(
			'ok'       => false,
			'menu_id'  => $menu_id,
			'items'    => array(),
			'messages' => $messages,
		);
	}

	$has_children = false;
	$invalid_url  = array();
	$top          = array();

	foreach ( $raw as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			$has_children = true;
			continue;
		}

		$url = isset( $item->url ) ? (string) $item->url : '';
		if ( ! ghahghah_is_bottom_nav_url_public( $url ) ) {
			$invalid_url[] = (string) $item->title;
			continue;
		}

		$top[] = $item;
	}

	if ( $has_children ) {
		$messages[] = __( 'این جایگاه فقط یک سطح است؛ گزینه‌های فرزند در نوار نمایش داده نمی‌شوند. آن‌ها را از فهرست حذف یا به سطح اول منتقل کنید.', 'ghahghah' );
	}

	if ( array() !== $invalid_url ) {
		$messages[] = sprintf(
			/* translators: %s: comma-separated menu item titles */
			__( 'این گزینه‌ها مقصد معتبر ندارند و وارد نوار نمی‌شوند: %s', 'ghahghah' ),
			implode( '، ', $invalid_url )
		);
	}

	$count = count( $top );
	if ( $count < GHAHGHAH_BOTTOM_NAV_MIN || $count > GHAHGHAH_BOTTOM_NAV_MAX ) {
		$messages[] = sprintf(
			/* translators: 1: min items, 2: max items, 3: current count */
			__( 'نوار پایین فقط با %1$d تا %2$d گزینه هم‌سطح معتبر نمایش داده می‌شود (اکنون: %3$d). فهرست را اصلاح کنید.', 'ghahghah' ),
			GHAHGHAH_BOTTOM_NAV_MIN,
			GHAHGHAH_BOTTOM_NAV_MAX,
			$count
		);
		return array(
			'ok'       => false,
			'menu_id'  => $menu_id,
			'items'    => $top,
			'messages' => $messages,
		);
	}

	return array(
		'ok'       => true,
		'menu_id'  => $menu_id,
		'items'    => $top,
		'messages' => $messages,
	);
}

/**
 * Valid top-level items for the bottom nav (empty when not renderable).
 *
 * @return array<int, WP_Post>
 */
function ghahghah_get_bottom_nav_items(): array {
	if ( ! ghahghah_is_bottom_nav_enabled() ) {
		return array();
	}

	$validation = ghahghah_get_bottom_nav_validation();
	if ( ! $validation['ok'] ) {
		return array();
	}

	return $validation['items'];
}

/**
 * Icon key for a menu item (stored meta, else inferred from title/URL).
 *
 * @param int          $menu_item_id Menu item post ID.
 * @param WP_Post|null $item         Optional nav menu item (for inference).
 */
function ghahghah_get_bottom_nav_item_icon( int $menu_item_id, $item = null ): string {
	$raw = get_post_meta( $menu_item_id, GHAHGHAH_BOTTOM_NAV_ICON_META, true );
	$key = ghahghah_sanitize_bottom_nav_icon_key( $raw );
	if ( '' !== $key ) {
		return $key;
	}

	if ( ! is_object( $item ) ) {
		$items = wp_get_nav_menu_items( ghahghah_get_bottom_nav_menu_id() );
		if ( is_array( $items ) ) {
			foreach ( $items as $candidate ) {
				if ( $candidate instanceof WP_Post && (int) $candidate->ID === $menu_item_id ) {
					$item = $candidate;
					break;
				}
			}
		}
	}

	if ( is_object( $item ) ) {
		return ghahghah_infer_bottom_nav_icon( $item );
	}

	return 'products';
}

/**
 * Persist inferred icons on mobile-bottom menu items that lack meta.
 *
 * @param int $menu_id Menu term ID.
 * @return int Number of icons written.
 */
function ghahghah_sync_bottom_nav_item_icons( int $menu_id ): int {
	if ( $menu_id <= 0 ) {
		return 0;
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) || array() === $items ) {
		return 0;
	}

	$written = 0;
	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post || 0 !== (int) $item->menu_item_parent ) {
			continue;
		}
		$existing = ghahghah_sanitize_bottom_nav_icon_key(
			get_post_meta( $item->ID, GHAHGHAH_BOTTOM_NAV_ICON_META, true )
		);
		if ( '' !== $existing ) {
			continue;
		}
		$key = ghahghah_infer_bottom_nav_icon( $item );
		update_post_meta( $item->ID, GHAHGHAH_BOTTOM_NAV_ICON_META, $key );
		++$written;
	}

	return $written;
}

/**
 * Echo trusted inline SVG for an icon key.
 *
 * @param string $key Icon key.
 */
function ghahghah_the_bottom_nav_icon_svg( string $key ): void {
	$key  = ghahghah_sanitize_bottom_nav_icon_key( $key );
	$path = ghahghah_get_bottom_nav_icon_path( '' !== $key ? $key : 'products' );
	if ( ! is_readable( $path ) ) {
		$path = ghahghah_get_bottom_nav_icon_path( 'products' );
	}
	if ( ! is_readable( $path ) ) {
		return;
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme asset.
	if ( ! is_string( $svg ) || '' === $svg ) {
		return;
	}

	$svg = preg_replace(
		'/<svg\b/',
		'<svg class="gg-bottom-nav__icon" aria-hidden="true" focusable="false"',
		$svg,
		1
	);

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted local theme SVG files only.
	echo $svg;
}

/**
 * Whether current request is in the products section (CPT archive / single).
 */
function ghahghah_is_bottom_nav_products_context(): bool {
	$cpt = GHAHGHAH_BOTTOM_NAV_PRODUCT_CPT;
	if ( ! post_type_exists( $cpt ) ) {
		return false;
	}

	if ( is_post_type_archive( $cpt ) || is_singular( $cpt ) ) {
		return true;
	}

	$taxonomies = get_object_taxonomies( $cpt );
	foreach ( $taxonomies as $taxonomy ) {
		if ( is_tax( $taxonomy ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Whether a menu item URL points at the products archive.
 *
 * @param string $url Item URL.
 */
function ghahghah_is_bottom_nav_products_archive_url( string $url ): bool {
	$cpt = GHAHGHAH_BOTTOM_NAV_PRODUCT_CPT;
	if ( ! post_type_exists( $cpt ) ) {
		return false;
	}

	$archive = get_post_type_archive_link( $cpt );
	if ( ! is_string( $archive ) || '' === $archive ) {
		return false;
	}

	return ghahghah_normalize_bottom_nav_url( $url ) === ghahghah_normalize_bottom_nav_url( $archive );
}

/**
 * Resolve active state for bottom-nav items (at most one current).
 *
 * @param array<int, WP_Post> $items Menu items.
 * @return array{index: int, type: string}|null
 */
function ghahghah_resolve_bottom_nav_current( array $items ): ?array {
	$current_url = ghahghah_normalize_bottom_nav_url( (string) add_query_arg( array() ) );
	if ( is_front_page() ) {
		$current_url = ghahghah_normalize_bottom_nav_url( home_url( '/' ) );
	}

	foreach ( $items as $index => $item ) {
		$url = isset( $item->url ) ? (string) $item->url : '';
		if ( ghahghah_normalize_bottom_nav_url( $url ) === $current_url ) {
			return array(
				'index' => (int) $index,
				'type'  => 'page',
			);
		}

		$classes = (array) $item->classes;
		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			return array(
				'index' => (int) $index,
				'type'  => 'page',
			);
		}
	}

	if ( ghahghah_is_bottom_nav_products_context() ) {
		foreach ( $items as $index => $item ) {
			$url = isset( $item->url ) ? (string) $item->url : '';
			if ( ghahghah_is_bottom_nav_products_archive_url( $url ) ) {
				return array(
					'index' => (int) $index,
					'type'  => 'location',
				);
			}
		}
	}

	return null;
}

/**
 * Whether the bottom nav will render on this request.
 */
function ghahghah_should_render_bottom_nav(): bool {
	return array() !== ghahghah_get_bottom_nav_items();
}

/**
 * Render the mobile bottom navigation + spacer.
 */
function ghahghah_the_bottom_nav(): void {
	$items = ghahghah_get_bottom_nav_items();
	if ( array() === $items ) {
		return;
	}

	get_template_part(
		'template-parts/navigation/mobile',
		'bottom',
		array(
			'items'   => $items,
			'current' => ghahghah_resolve_bottom_nav_current( $items ),
		)
	);
}

/**
 * Body class when bottom nav is present.
 *
 * @param array<int, string> $classes Body classes.
 * @return array<int, string>
 */
function ghahghah_bottom_nav_body_class( array $classes ): array {
	if ( ghahghah_should_render_bottom_nav() ) {
		$classes[] = 'has-ghahghah-bottom-nav';
	}
	return $classes;
}
add_filter( 'body_class', 'ghahghah_bottom_nav_body_class' );

/**
 * Register Customizer toggle.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function ghahghah_bottom_nav_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'ghahghah_bottom_nav',
		array(
			'title'    => __( 'ناوبری پایین موبایل', 'ghahghah' ),
			'priority' => 45,
		)
	);

	$wp_customize->add_setting(
		'ghahghah_bottom_nav_enabled',
		array(
			'default'           => true,
			'sanitize_callback' => 'ghahghah_sanitize_bottom_nav_enabled',
			'transport'         => 'refresh',
		)
	);

	$wp_customize->add_control(
		'ghahghah_bottom_nav_enabled',
		array(
			'label'       => __( 'نمایش نوار پایین در موبایل', 'ghahghah' ),
			'description' => __( 'نوار فقط وقتی فهرست معتبری به جایگاه «ناوبری پایین موبایل» وصل باشد دیده می‌شود.', 'ghahghah' ),
			'section'     => 'ghahghah_bottom_nav',
			'type'        => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'ghahghah_bottom_nav_customize_register' );

/**
 * Menu item icon field (nav-menus.php).
 *
 * @param int             $item_id Menu item ID.
 * @param WP_Post         $item    Menu item.
 * @param int             $depth   Depth.
 * @param stdClass|null   $args    Walker args.
 * @param int             $id      Nav menu ID.
 */
function ghahghah_bottom_nav_menu_item_icon_fields( $item_id, $item, $depth = 0, $args = null, $id = 0 ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter, Universal.NamingConventions.NoReservedKeywordParameterNames
	$item_id = (int) $item_id;
	$current = ghahghah_get_bottom_nav_item_icon( $item_id, $item instanceof WP_Post ? $item : null );
	$labels  = ghahghah_bottom_nav_icon_labels();
	?>
	<p class="field-ghahghah-bottom-nav-icon description description-wide">
		<label for="edit-menu-item-ghahghah-bottom-icon-<?php echo esc_attr( (string) $item_id ); ?>">
			<?php esc_html_e( 'آیکن نوار پایین', 'ghahghah' ); ?><br />
			<select
				id="edit-menu-item-ghahghah-bottom-icon-<?php echo esc_attr( (string) $item_id ); ?>"
				class="widefat"
				name="ghahghah_bottom_nav_icon[<?php echo esc_attr( (string) $item_id ); ?>]"
			>
				<?php foreach ( $labels as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current, $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</label>
	</p>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'ghahghah_bottom_nav_menu_item_icon_fields', 10, 5 );

/**
 * Persist menu item icon meta.
 *
 * @param int $menu_id         Menu ID.
 * @param int $menu_item_db_id Menu item ID.
 */
function ghahghah_bottom_nav_save_menu_item_icon( int $menu_id, int $menu_item_db_id ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( ! isset( $_POST['ghahghah_bottom_nav_icon'] ) || ! is_array( $_POST['ghahghah_bottom_nav_icon'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Core nav-menus.php already verified the request.
	$raw_map = wp_unslash( $_POST['ghahghah_bottom_nav_icon'] );
	if ( ! isset( $raw_map[ $menu_item_db_id ] ) ) {
		return;
	}

	$key = ghahghah_sanitize_bottom_nav_icon_key( $raw_map[ $menu_item_db_id ] );
	if ( '' === $key ) {
		return;
	}
	update_post_meta( $menu_item_db_id, GHAHGHAH_BOTTOM_NAV_ICON_META, $key );
}
add_action( 'wp_update_nav_menu_item', 'ghahghah_bottom_nav_save_menu_item_icon', 10, 2 );

/**
 * Admin notice when assigned menu needs attention.
 */
function ghahghah_bottom_nav_admin_notice(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return;
	}

	$relevant = in_array( $screen->id, array( 'nav-menus', 'toplevel_page_ghahghah-theme-config' ), true );
	if ( ! $relevant ) {
		return;
	}

	if ( ! ghahghah_is_bottom_nav_enabled() ) {
		return;
	}

	$validation = ghahghah_get_bottom_nav_validation();
	if ( $validation['ok'] && array() === $validation['messages'] ) {
		return;
	}

	$class = $validation['ok'] ? 'notice-warning' : 'notice-error';
	echo '<div class="notice ' . esc_attr( $class ) . '"><p><strong>' . esc_html__( 'ناوبری پایین موبایل:', 'ghahghah' ) . '</strong></p><ul style="list-style:disc;margin-inline-start:1.25rem">';
	foreach ( $validation['messages'] as $message ) {
		echo '<li>' . esc_html( $message ) . '</li>';
	}
	if ( ! $validation['ok'] ) {
		echo '<li>' . esc_html__( 'تا اصلاح فهرست، نوار در سایت نمایش داده نمی‌شود.', 'ghahghah' ) . '</li>';
	}
	echo '</ul></div>';
}
add_action( 'admin_notices', 'ghahghah_bottom_nav_admin_notice' );
