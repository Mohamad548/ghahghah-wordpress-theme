<?php
/**
 * Products archive search / sort toolbar.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = ghahghah_products_archive_defaults();
$state    = ghahghah_products_archive_request_state();
$sorts    = ghahghah_products_archive_sort_options();
$found    = (int) $GLOBALS['wp_query']->found_posts;
$action   = ghahghah_products_archive_url();
?>
<form
	class="ghahghah-products-archive__toolbar"
	method="get"
	action="<?php echo esc_url( $action ); ?>"
	role="search"
	data-ghahghah-pa-toolbar
>
	<?php if ( 'all' !== $state['flavor'] ) : ?>
		<input type="hidden" name="gh_flavor" value="<?php echo esc_attr( $state['flavor'] ); ?>" />
	<?php endif; ?>

	<label class="ghahghah-products-archive__search">
		<span class="screen-reader-text"><?php echo esc_html( $defaults['search_placeholder'] ); ?></span>
		<span class="ghahghah-products-archive__search-icon" aria-hidden="true">
			<?php ghahghah_the_products_archive_icon( 'search' ); ?>
		</span>
		<input
			type="search"
			name="gh_q"
			value="<?php echo esc_attr( $state['q'] ); ?>"
			placeholder="<?php echo esc_attr( $defaults['search_placeholder'] ); ?>"
			autocomplete="off"
			enterkeyhint="search"
		/>
	</label>

	<div class="ghahghah-products-archive__sort">
		<label for="ghahghah-pa-sort"><?php echo esc_html( $defaults['sort_label'] ); ?></label>
		<div class="ghahghah-products-archive__sort-wrap">
			<select id="ghahghah-pa-sort" name="gh_sort" data-ghahghah-pa-sort>
				<?php foreach ( $sorts as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $state['sort'], $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<span class="ghahghah-products-archive__sort-chevron" aria-hidden="true">
				<?php ghahghah_the_products_archive_icon( 'chevron-down' ); ?>
			</span>
		</div>
	</div>

	<p class="ghahghah-products-archive__count ghahghah-products-archive__count--desktop" aria-live="polite">
		<?php echo esc_html( ghahghah_products_archive_found_label( $found ) ); ?>
	</p>
</form>
