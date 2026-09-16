<?php
/**
 * Idempotent setup for the FAQ page.
 *
 * Creates the page only if missing; seeds content/meta only when empty.
 * Never overwrites existing admin content or theme mods already set.
 *
 * Usage:
 *   wp eval-file wp-content/themes/ghahghah-theme/inc/catalog/setup-faq-page.php
 *
 * @package Ghahghah
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$find_page = static function ( string $title, array $slugs = array() ): int {
	foreach ( $slugs as $try ) {
		$try = (string) $try;
		if ( '' === $try ) {
			continue;
		}
		$p = get_page_by_path( $try );
		if ( $p instanceof WP_Post ) {
			return (int) $p->ID;
		}
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $q->posts[0] ) ) {
		return (int) $q->posts[0];
	}
	return 0;
};

$faq_mod = absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) );
$page_id = $faq_mod;
if ( $page_id > 0 ) {
	$page = get_post( $page_id );
	if ( ! $page || 'page' !== $page->post_type ) {
		$page_id = 0;
	}
}
if ( $page_id <= 0 ) {
	$page_id = $find_page(
		'پرسش‌های متداول',
		array( 'faq', 'پرسش-های-متداول', 'پرسشهای-متداول' )
	);
}

$created = false;
if ( $page_id <= 0 ) {
	$page_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'پرسش‌های متداول',
			'post_name'    => 'faq',
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $page_id ) ) {
		if ( class_exists( 'WP_CLI' ) ) {
			WP_CLI::warning( $page_id->get_error_message() );
		}
		return;
	}
	$created = true;
}

$current_template = (string) get_page_template_slug( (int) $page_id );
if ( '' === $current_template ) {
	update_post_meta( (int) $page_id, '_wp_page_template', 'page-templates/faq.php' );
}

if ( absint( get_theme_mod( 'ghahghah_faq_page_id', 0 ) ) <= 0 ) {
	set_theme_mod( 'ghahghah_faq_page_id', (int) $page_id );
}

// Contact page (link target) — set only if empty or wrongly pointing at FAQ page.
$contact_mod = absint( get_theme_mod( 'ghahghah_contact_page_id', 0 ) );
if ( $contact_mod <= 0 || $contact_mod === (int) $page_id ) {
	$contact = $find_page(
		'تماس با ما',
		array( 'contact', 'تماس-با-ما' )
	);
	if ( $contact > 0 && $contact !== (int) $page_id ) {
		set_theme_mod( 'ghahghah_contact_page_id', $contact );
	} elseif ( $contact_mod === (int) $page_id ) {
		set_theme_mod( 'ghahghah_contact_page_id', 0 );
	}
}

$existing_groups = ghahghah_faq_get_page_groups( (int) $page_id );
$page_obj        = get_post( (int) $page_id );
$content_empty   = $page_obj instanceof WP_Post && '' === trim( wp_strip_all_tags( (string) $page_obj->post_content ) );

if ( array() === $existing_groups && $content_empty ) {
	$bundled = ghahghah_faq_load_bundled_groups();
	if ( array() !== $bundled ) {
		ghahghah_faq_save_page_groups( (int) $page_id, $bundled, true );
	}
} elseif ( array() === $existing_groups && $page_obj instanceof WP_Post ) {
	$parsed = ghahghah_faq_parse_groups_from_html( (string) $page_obj->post_content );
	if ( array() !== $parsed ) {
		update_post_meta( (int) $page_id, GHAHGHAH_FAQ_META_GROUPS, wp_json_encode( $parsed, JSON_UNESCAPED_UNICODE ) );
	}
}

$url = get_permalink( (int) $page_id );
if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::log(
		sprintf(
			'faq id=%d created=%s template=%s url=%s groups=%d',
			(int) $page_id,
			$created ? 'yes' : 'no',
			(string) get_page_template_slug( (int) $page_id ),
			is_string( $url ) ? $url : '',
			count( ghahghah_faq_get_page_groups( (int) $page_id ) )
		)
	);
	WP_CLI::success( 'FAQ page setup finished (no overwrite of existing content).' );
}
