<?php
/**
 * Blog archive search / sort toolbar.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = ghahghah_blog_archive_defaults();
$state    = ghahghah_blog_archive_request_state();
$sorts    = ghahghah_blog_archive_sort_options();
$action   = ghahghah_blog_archive_url();
?>
<form
	class="ghahghah-blog-archive__toolbar"
	method="get"
	action="<?php echo esc_url( $action ); ?>"
	role="search"
	data-ghahghah-ba-toolbar
>
	<?php if ( 'all' !== $state['cat'] ) : ?>
		<input type="hidden" name="gh_cat" value="<?php echo esc_attr( $state['cat'] ); ?>" />
	<?php endif; ?>

	<label class="ghahghah-blog-archive__search">
		<span class="screen-reader-text"><?php echo esc_html( $defaults['search_placeholder'] ); ?></span>
		<span class="ghahghah-blog-archive__search-icon" aria-hidden="true">
			<?php ghahghah_the_blog_archive_icon( 'search' ); ?>
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

	<div class="ghahghah-blog-archive__sort">
		<label for="ghahghah-ba-sort"><?php echo esc_html( $defaults['sort_label'] ); ?></label>
		<div class="ghahghah-blog-archive__sort-wrap">
			<select id="ghahghah-ba-sort" name="gh_sort" data-ghahghah-ba-sort>
				<?php foreach ( $sorts as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $state['sort'], $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<span class="ghahghah-blog-archive__sort-chevron" aria-hidden="true">
				<?php ghahghah_the_blog_archive_icon( 'chevron-down' ); ?>
			</span>
		</div>
	</div>
</form>
