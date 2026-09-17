<?php
/**
 * Blog archive category chips.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$state = ghahghah_blog_archive_request_state();
$chips = ghahghah_blog_archive_category_chips();
?>
<div class="ghahghah-blog-archive__chips" role="list" aria-label="<?php esc_attr_e( 'دسته‌بندی مقالات', 'ghahghah' ); ?>">
	<?php foreach ( $chips as $key => $chip ) : ?>
		<?php
		$is_active = ( $state['cat'] === $key );
		$url       = ghahghah_blog_archive_url(
			array(
				'gh_q'    => $state['q'],
				'gh_sort' => $state['sort'],
				'gh_cat'  => $key,
			)
		);
		?>
		<a
			class="ghahghah-blog-archive__chip<?php echo $is_active ? ' is-active' : ''; ?>"
			role="listitem"
			href="<?php echo esc_url( $url ); ?>"
			<?php echo $is_active ? ' aria-current="true"' : ''; ?>
		>
			<?php echo esc_html( (string) $chip['label'] ); ?>
		</a>
	<?php endforeach; ?>
</div>
