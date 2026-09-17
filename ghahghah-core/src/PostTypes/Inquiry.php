<?php
/**
 * Inquiry custom post type (wholesale + agency requests).
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\PostTypes;

/**
 * Registers ghahghah_inquiry CPT.
 */
final class Inquiry {

	public const POST_TYPE = 'ghahghah_inquiry';

	/**
	 * Attach hooks.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	/**
	 * Register the CPT (admin-only list; not public on front).
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'               => __( 'درخواست‌ها', 'ghahghah-core' ),
			'singular_name'      => __( 'درخواست', 'ghahghah-core' ),
			'menu_name'          => __( 'درخواست‌ها', 'ghahghah-core' ),
			'add_new'            => __( 'افزودن', 'ghahghah-core' ),
			'add_new_item'       => __( 'افزودن درخواست', 'ghahghah-core' ),
			'edit_item'          => __( 'مشاهده درخواست', 'ghahghah-core' ),
			'new_item'           => __( 'درخواست جدید', 'ghahghah-core' ),
			'view_item'          => __( 'مشاهده', 'ghahghah-core' ),
			'search_items'       => __( 'جستجوی درخواست‌ها', 'ghahghah-core' ),
			'not_found'          => __( 'درخواستی یافت نشد.', 'ghahghah-core' ),
			'not_found_in_trash' => __( 'در زباله‌دان درخواستی نیست.', 'ghahghah-core' ),
			'all_items'          => __( 'همه درخواست‌ها', 'ghahghah-core' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'description'         => __( 'درخواست‌های خرید عمده و نمایندگی.', 'ghahghah-core' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_rest'        => false,
				'menu_position'       => 21,
				'menu_icon'           => 'dashicons-email-alt',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'supports'            => array( 'title' ),
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
			)
		);
	}

	/**
	 * Meta box for inquiry details.
	 */
	public function register_meta_boxes(): void {
		add_meta_box(
			'ghahghah_inquiry_details',
			__( 'جزئیات درخواست', 'ghahghah-core' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Read-only meta display.
	 *
	 * @param \WP_Post $post Post.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		$keys = array(
			'inquiry_type'   => __( 'نوع', 'ghahghah-core' ),
			'full_name'      => __( 'نام و نام خانوادگی', 'ghahghah-core' ),
			'phone'          => __( 'شماره تماس', 'ghahghah-core' ),
			'subject'        => __( 'موضوع پیام', 'ghahghah-core' ),
			'company'        => __( 'مجموعه / شرکت', 'ghahghah-core' ),
			'province'       => __( 'استان', 'ghahghah-core' ),
			'city'           => __( 'شهر', 'ghahghah-core' ),
			'product'        => __( 'محصول', 'ghahghah-core' ),
			'quantity'       => __( 'تعداد تقریبی', 'ghahghah-core' ),
			'activity'       => __( 'زمینه فعالیت', 'ghahghah-core' ),
			'experience'     => __( 'سابقه پخش و فروش', 'ghahghah-core' ),
			'coverage'       => __( 'شهرهای تحت پوشش', 'ghahghah-core' ),
			'message'        => __( 'توضیحات', 'ghahghah-core' ),
			'consent'        => __( 'رضایت', 'ghahghah-core' ),
		);

		echo '<table class="widefat striped"><tbody>';
		foreach ( $keys as $meta_key => $label ) {
			$value = get_post_meta( $post->ID, '_ghahghah_' . $meta_key, true );
			if ( 'inquiry_type' === $meta_key ) {
				$value = match ( (string) $value ) {
					'agency'  => __( 'نمایندگی', 'ghahghah-core' ),
					'contact' => __( 'تماس', 'ghahghah-core' ),
					default   => __( 'خرید عمده', 'ghahghah-core' ),
				};
			}
			if ( 'consent' === $meta_key ) {
				$value = $value ? __( 'بله', 'ghahghah-core' ) : __( 'خیر', 'ghahghah-core' );
			}
			printf(
				'<tr><th style="width:28%%">%s</th><td>%s</td></tr>',
				esc_html( $label ),
				esc_html( is_scalar( $value ) ? (string) $value : '' )
			);
		}
		echo '</tbody></table>';
	}

	/**
	 * Admin list columns.
	 *
	 * @param array<string, string> $columns Columns.
	 * @return array<string, string>
	 */
	public function columns( array $columns ): array {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['ghahghah_type']  = __( 'نوع', 'ghahghah-core' );
				$new['ghahghah_phone'] = __( 'تماس', 'ghahghah-core' );
				$new['ghahghah_city']  = __( 'شهر', 'ghahghah-core' );
			}
		}
		return $new;
	}

	/**
	 * Column content.
	 *
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 */
	public function column_content( string $column, int $post_id ): void {
		if ( 'ghahghah_type' === $column ) {
			$type = (string) get_post_meta( $post_id, '_ghahghah_inquiry_type', true );
			$label = match ( $type ) {
				'agency'  => __( 'نمایندگی', 'ghahghah-core' ),
				'contact' => __( 'تماس', 'ghahghah-core' ),
				default   => __( 'خرید عمده', 'ghahghah-core' ),
			};
			echo esc_html( $label );
			return;
		}
		if ( 'ghahghah_phone' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, '_ghahghah_phone', true ) );
			return;
		}
		if ( 'ghahghah_city' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, '_ghahghah_city', true ) );
		}
	}
}
