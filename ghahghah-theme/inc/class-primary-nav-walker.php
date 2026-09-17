<?php
/**
 * Accessible primary navigation walker (link + independent submenu toggle).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Walker for the primary header menu.
 */
class Ghahghah_Primary_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Instance counter for unique submenu / button IDs.
	 *
	 * @var int
	 */
	private static int $instance = 0;

	/**
	 * Context prefix (desktop|mobile) for unique IDs.
	 *
	 * @var string
	 */
	private string $context;

	/**
	 * Pending submenu element ID for the next start_lvl call.
	 *
	 * @var string
	 */
	private string $pending_submenu_id = '';

	/**
	 * Constructor.
	 *
	 * @param string $context Unique context slug.
	 */
	public function __construct( string $context = 'desktop' ) {
		$this->context = sanitize_key( $context );
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$indent  = str_repeat( "\t", $depth );
		$id_attr = '';

		if ( '' !== $this->pending_submenu_id ) {
			$id_attr                  = ' id="' . esc_attr( $this->pending_submenu_id ) . '"';
			$this->pending_submenu_id = '';
		}

		$hidden  = 0 === (int) $depth ? ' hidden' : '';
		$output .= "\n{$indent}<ul class=\"site-nav__sub list-reset\"{$id_attr}{$hidden}>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string   $output Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$indent  = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Starts the element output.
	 *
	 * @param string   $output Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
		$classes   = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'site-nav__item';
		$classes[] = 'menu-item-' . (int) $item->ID;

		$has_children = in_array( 'menu-item-has-children', $classes, true );

		if ( $has_children ) {
			$classes[] = 'site-nav__item--has-children';
		}

		$class_names = implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) );

		$output .= '<li class="' . esc_attr( $class_names ) . '">';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';
		$atts['class']  = 'site-nav__link';

		if ( in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true ) ) {
			$atts['aria-current'] = 'page';
			$atts['class']       .= ' is-active';
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value ) {
				$value       = 'href' === $attr ? esc_url( (string) $value ) : esc_attr( (string) $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core nav filter.
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core nav filter.

		$item_output  = $args->before ?? '';
		$item_output .= '<div class="site-nav__row">';
		$item_output .= '<a' . $attributes . '>';
		$item_output .= ( $args->link_before ?? '' ) . esc_html( $title ) . ( $args->link_after ?? '' );
		$item_output .= '</a>';

		if ( $has_children && 0 === (int) $depth ) {
			++self::$instance;
			$submenu_id               = 'ghahghah-submenu-' . $this->context . '-' . self::$instance;
			$this->pending_submenu_id = $submenu_id;

			$item_output .= sprintf(
				'<button type="button" class="site-nav__toggle" data-ghahghah-submenu-toggle aria-expanded="false" aria-controls="%1$s"><span class="screen-reader-text">%2$s</span><span class="site-nav__chevron" aria-hidden="true"></span></button>',
				esc_attr( $submenu_id ),
				esc_html(
					sprintf(
						/* translators: %s: parent menu title */
						__( 'باز و بسته کردن زیرمنوی %s', 'ghahghah' ),
						$title
					)
				)
			);
		}

		$item_output .= '</div>';
		$item_output .= $args->after ?? '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core walker filter.
	}
}
