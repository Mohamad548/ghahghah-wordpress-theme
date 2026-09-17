<?php
/**
 * Save FAQ theme mods + page groups.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle FAQ admin form submit.
 */
function ghahghah_handle_save_faq(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'ghahghah' ) );
	}
	check_admin_referer( 'ghahghah_save_faq', 'ghahghah_faq_nonce' );

	$defaults = ghahghah_faq_defaults();

	$faq_id     = absint( wp_unslash( $_POST['ghahghah_faq_page_id'] ?? 0 ) );
	$contact_id = absint( wp_unslash( $_POST['ghahghah_contact_page_id'] ?? 0 ) );
	set_theme_mod( 'ghahghah_faq_page_id', $faq_id );
	set_theme_mod( 'ghahghah_contact_page_id', $contact_id );

	$text_keys = array(
		'ghahghah_faq_intro_title',
		'ghahghah_faq_intro_text',
		'ghahghah_faq_support_title',
		'ghahghah_faq_support_text',
		'ghahghah_faq_support_button',
		'ghahghah_faq_forms_title',
		'ghahghah_faq_forms_wholesale',
		'ghahghah_faq_forms_agency',
	);
	foreach ( $text_keys as $key ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
		$raw = is_string( $raw ) ? $raw : '';
		if ( str_contains( $key, '_text' ) ) {
			set_theme_mod( $key, sanitize_textarea_field( $raw ) );
		} else {
			set_theme_mod( $key, sanitize_text_field( $raw ) );
		}
	}

	$raw_groups = isset( $_POST['ghahghah_faq_groups'] ) ? wp_unslash( $_POST['ghahghah_faq_groups'] ) : array();
	$built      = array();
	if ( is_array( $raw_groups ) ) {
		foreach ( $raw_groups as $group ) {
			if ( ! is_array( $group ) ) {
				continue;
			}
			$title = isset( $group['title'] ) ? sanitize_text_field( (string) $group['title'] ) : '';
			if ( '' === $title ) {
				continue;
			}
			$items_in = isset( $group['items'] ) && is_array( $group['items'] ) ? $group['items'] : array();
			$items    = array();
			foreach ( $items_in as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				$question = isset( $item['question'] ) ? sanitize_text_field( (string) $item['question'] ) : '';
				$answer   = isset( $item['answer'] ) ? sanitize_textarea_field( (string) $item['answer'] ) : '';
				if ( '' === $question || '' === $answer ) {
					continue;
				}
				$entry = array(
					'key'            => isset( $item['key'] ) ? sanitize_key( (string) $item['key'] ) : '',
					'question'       => $question,
					'answer'         => $answer,
					'initially_open' => ! empty( $item['initially_open'] ),
				);
				$ll = isset( $item['link_label'] ) ? sanitize_text_field( (string) $item['link_label'] ) : '';
				$lt = isset( $item['link_target'] ) ? sanitize_key( (string) $item['link_target'] ) : '';
				if ( '' !== $ll && in_array( $lt, ghahghah_faq_link_target_keys(), true ) ) {
					$entry['link'] = array(
						'label'  => $ll,
						'target' => $lt,
					);
				}
				$items[] = $entry;
			}
			if ( array() === $items ) {
				continue;
			}
			$built[] = array(
				'key'   => isset( $group['key'] ) ? sanitize_key( (string) $group['key'] ) : '',
				'title' => $title,
				'items' => $items,
			);
		}
	}

	if ( $faq_id > 0 && array() !== $built ) {
		$tpl = (string) get_page_template_slug( $faq_id );
		if ( '' === $tpl ) {
			update_post_meta( $faq_id, '_wp_page_template', 'page-templates/faq.php' );
		}
		ghahghah_faq_save_page_groups( $faq_id, $built, true );
	}

	if ( function_exists( 'ghahghah_sync_footer_faq_menu_item' ) ) {
		ghahghah_sync_footer_faq_menu_item();
	}

	$tab = isset( $_POST['ghahghah_return_tab'] ) ? sanitize_key( (string) wp_unslash( $_POST['ghahghah_return_tab'] ) ) : 'faq';
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => GHAHGHAH_CONFIG_PAGE,
				'tab'     => $tab,
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_ghahghah_save_faq', 'ghahghah_handle_save_faq' );
