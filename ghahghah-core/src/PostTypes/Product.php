<?php
/**
 * Product custom post type.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core\PostTypes;

/**
 * Registers the ghahghah_product CPT.
 */
final class Product {

	public const POST_TYPE = 'ghahghah_product';

	/**
	 * Default public archive slug (filterable later).
	 */
	public const ARCHIVE_SLUG = 'products';

	/**
	 * Attach hooks.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'                  => __( 'محصولات', 'ghahghah-core' ),
			'singular_name'         => __( 'محصول', 'ghahghah-core' ),
			'menu_name'             => __( 'محصولات', 'ghahghah-core' ),
			'name_admin_bar'        => __( 'محصول', 'ghahghah-core' ),
			'add_new'               => __( 'افزودن', 'ghahghah-core' ),
			'add_new_item'          => __( 'افزودن محصول جدید', 'ghahghah-core' ),
			'new_item'              => __( 'محصول جدید', 'ghahghah-core' ),
			'edit_item'             => __( 'ویرایش محصول', 'ghahghah-core' ),
			'view_item'             => __( 'مشاهده محصول', 'ghahghah-core' ),
			'view_items'            => __( 'مشاهده محصولات', 'ghahghah-core' ),
			'all_items'             => __( 'همه محصولات', 'ghahghah-core' ),
			'search_items'          => __( 'جستجوی محصولات', 'ghahghah-core' ),
			'parent_item_colon'     => __( 'محصول والد:', 'ghahghah-core' ),
			'not_found'             => __( 'محصولی یافت نشد.', 'ghahghah-core' ),
			'not_found_in_trash'    => __( 'محصولی در زباله‌دان یافت نشد.', 'ghahghah-core' ),
			'featured_image'        => __( 'تصویر محصول', 'ghahghah-core' ),
			'set_featured_image'    => __( 'تنظیم تصویر محصول', 'ghahghah-core' ),
			'remove_featured_image' => __( 'حذف تصویر محصول', 'ghahghah-core' ),
			'use_featured_image'    => __( 'استفاده به‌عنوان تصویر محصول', 'ghahghah-core' ),
			'archives'              => __( 'بایگانی محصولات', 'ghahghah-core' ),
			'insert_into_item'      => __( 'درج در محصول', 'ghahghah-core' ),
			'uploaded_to_this_item' => __( 'بارگذاری‌شده برای این محصول', 'ghahghah-core' ),
			'filter_items_list'     => __( 'فیلتر فهرست محصولات', 'ghahghah-core' ),
			'items_list_navigation' => __( 'ناوبری فهرست محصولات', 'ghahghah-core' ),
			'items_list'            => __( 'فهرست محصولات', 'ghahghah-core' ),
			'item_published'        => __( 'محصول منتشر شد.', 'ghahghah-core' ),
			'item_updated'          => __( 'محصول به‌روزرسانی شد.', 'ghahghah-core' ),
		);

		/**
		 * Filters the public archive slug for products.
		 *
		 * @param string $slug Archive slug.
		 */
		$archive_slug = (string) apply_filters( 'ghahghah_product_archive_slug', self::ARCHIVE_SLUG );

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'کاتالوگ محصولات برند قهقهه.', 'ghahghah-core' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_rest'        => true,
			'rest_base'           => 'ghahghah-products',
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-products',
			'capability_type'     => array( 'ghahghah_product', 'ghahghah_products' ),
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'excerpt',
				'revisions',
			),
			'has_archive'         => $archive_slug,
			'rewrite'             => array(
				'slug'       => $archive_slug,
				'with_front' => false,
			),
			'query_var'           => true,
			'exclude_from_search' => false,
		);

		/**
		 * Filters CPT registration arguments.
		 *
		 * @param array<string, mixed> $args Post type args.
		 */
		$args = apply_filters( 'ghahghah_product_post_type_args', $args );

		register_post_type( self::POST_TYPE, $args );
	}
}
