<?php
/**
 * Products archive flavor chips.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$state = ghahghah_products_archive_request_state();
$chips = ghahghah_products_archive_available_flavor_chips();
?>
<div class="ghahghah-products-archive__chips" role="list" aria-label="<?php esc_attr_e( 'فیلتر طعم', 'ghahghah' ); ?>">
	<?php foreach ( $chips as $key => $chip ) : ?>
		<?php
		$is_active = ( $state['flavor'] === $key );
		$url       = ghahghah_products_archive_url(
			array(
				'gh_q'      => $state['q'],
				'gh_sort'   => $state['sort'],
				'gh_flavor' => $key,
			)
		);
		$label = (string) $chip['label'];
		if ( 'all' === $key && ! empty( $chip['short_label'] ) ) {
			$mobile_label = (string) $chip['short_label'];
		} else {
			$mobile_label = $label;
		}
		?>
		<a
			class="ghahghah-products-archive__chip<?php echo $is_active ? ' is-active' : ''; ?>"
			role="listitem"
			href="<?php echo esc_url( $url ); ?>"
			<?php echo $is_active ? ' aria-current="true"' : ''; ?>
		>
			<span class="ghahghah-products-archive__chip-label ghahghah-products-archive__chip-label--full"><?php echo esc_html( $label ); ?></span>
			<span class="ghahghah-products-archive__chip-label ghahghah-products-archive__chip-label--short"><?php echo esc_html( $mobile_label ); ?></span>
		</a>
	<?php endforeach; ?>
</div>
