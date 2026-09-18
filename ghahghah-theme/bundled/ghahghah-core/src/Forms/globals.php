<?php
/**
 * Global render helpers for theme collab resolver (root namespace).
 *
 * @package Ghahghah\Core
 */

declare(strict_types=1);

use Ghahghah\Core\Forms\FormRenderer;

if ( ! function_exists( 'ghahghah_core_render_wholesale_form' ) ) {
	/**
	 * Render wholesale inquiry form.
	 */
	function ghahghah_core_render_wholesale_form(): void {
		FormRenderer::render( 'wholesale' );
	}
}

if ( ! function_exists( 'ghahghah_core_render_agency_form' ) ) {
	/**
	 * Render agency inquiry form.
	 */
	function ghahghah_core_render_agency_form(): void {
		FormRenderer::render( 'agency' );
	}
}

if ( ! function_exists( 'ghahghah_core_render_contact_form' ) ) {
	/**
	 * Render contact message form.
	 */
	function ghahghah_core_render_contact_form(): void {
		FormRenderer::render( 'contact', array( 'variant' => 'page' ) );
	}
}
