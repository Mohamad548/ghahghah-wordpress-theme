<?php
/**
 * Role capability helpers for the product CPT.
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

namespace Ghahghah\Core;

/**
 * Grants and removes custom product capabilities.
 */
final class Capabilities {

	/**
	 * Capability primitive names derived from capability_type.
	 *
	 * @return array<int, string>
	 */
	public static function product_caps(): array {
		return array(
			'edit_ghahghah_product',
			'read_ghahghah_product',
			'delete_ghahghah_product',
			'edit_ghahghah_products',
			'edit_others_ghahghah_products',
			'publish_ghahghah_products',
			'read_private_ghahghah_products',
			'delete_ghahghah_products',
			'delete_private_ghahghah_products',
			'delete_published_ghahghah_products',
			'delete_others_ghahghah_products',
			'edit_private_ghahghah_products',
			'edit_published_ghahghah_products',
			'create_ghahghah_products',
		);
	}

	/**
	 * Add product caps to administrator and editor roles.
	 */
	public static function grant(): void {
		$roles = array( 'administrator', 'editor' );

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			foreach ( self::product_caps() as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove product caps from roles (optional cleanup on uninstall later).
	 */
	public static function revoke(): void {
		$roles = array( 'administrator', 'editor' );

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );

			if ( ! $role ) {
				continue;
			}

			foreach ( self::product_caps() as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}
}
