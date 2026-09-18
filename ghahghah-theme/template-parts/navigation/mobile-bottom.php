<?php
/**
 * Mobile bottom navigation bar + spacer.
 *
 * @package Ghahghah
 *
 * @var array $args {
 *     @type array<int, WP_Post>           $items   Menu items.
 *     @type array{index:int,type:string}|null $current Active item.
 * }
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_items   = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
$ghahghah_current = isset( $args['current'] ) && is_array( $args['current'] ) ? $args['current'] : null;

if ( array() === $ghahghah_items ) {
	return;
}
?>
<nav
	class="gg-bottom-nav"
	aria-label="<?php esc_attr_e( 'ناوبری پایین موبایل', 'ghahghah' ); ?>"
	data-ghahghah-bottom-nav
>
	<ul class="gg-bottom-nav__list">
		<?php foreach ( $ghahghah_items as $ghahghah_index => $ghahghah_item ) : ?>
			<?php
			$ghahghah_url   = isset( $ghahghah_item->url ) ? (string) $ghahghah_item->url : '';
			$ghahghah_title = isset( $ghahghah_item->title ) ? (string) $ghahghah_item->title : '';
			$ghahghah_icon  = ghahghah_get_bottom_nav_item_icon( (int) $ghahghah_item->ID, $ghahghah_item );
			$ghahghah_is_current = is_array( $ghahghah_current ) && (int) $ghahghah_current['index'] === (int) $ghahghah_index;
			$ghahghah_aria = '';
			$ghahghah_link_class = 'gg-bottom-nav__link';

			if ( $ghahghah_is_current ) {
				$ghahghah_link_class .= ' is-active';
				$type = isset( $ghahghah_current['type'] ) ? (string) $ghahghah_current['type'] : 'page';
				$ghahghah_aria = 'location' === $type ? 'location' : 'page';
			}
			?>
			<li class="gg-bottom-nav__item">
				<a
					class="<?php echo esc_attr( $ghahghah_link_class ); ?>"
					href="<?php echo esc_url( $ghahghah_url ); ?>"
					<?php echo '' !== $ghahghah_aria ? ' aria-current="' . esc_attr( $ghahghah_aria ) . '"' : ''; ?>
				>
					<?php ghahghah_the_bottom_nav_icon_svg( $ghahghah_icon ); ?>
					<span class="gg-bottom-nav__label"><?php echo esc_html( $ghahghah_title ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<div class="gg-bottom-nav-spacer" aria-hidden="true" data-ghahghah-bottom-nav-spacer></div>
