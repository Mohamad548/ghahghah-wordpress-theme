<?php
/**
 * FAQ page settings, link targets, and content helpers.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Post meta key for structured FAQ groups on the FAQ page. */
const GHAHGHAH_FAQ_META_GROUPS = '_ghahghah_faq_groups';

/**
 * Theme mod defaults for FAQ chrome (intro + sidebar).
 *
 * @return array<string, mixed>
 */
function ghahghah_faq_defaults(): array {
	return array(
		'ghahghah_faq_page_id'            => 0,
		'ghahghah_contact_page_id'        => 0,
		'ghahghah_faq_intro_title'        => __( 'پرسش‌های متداول', 'ghahghah' ),
		'ghahghah_faq_intro_text'         => __( 'راهنمای محصولات، خرید عمده و همکاری با قهقهه', 'ghahghah' ),
		'ghahghah_faq_support_title'      => __( 'پاسخ خود را پیدا نکردید؟', 'ghahghah' ),
		'ghahghah_faq_support_text'       => __( 'از راه‌های ارتباطی درج‌شده در صفحه تماس با ما استفاده کنید.', 'ghahghah' ),
		'ghahghah_faq_support_button'     => __( 'تماس با ما', 'ghahghah' ),
		'ghahghah_faq_forms_title'        => __( 'دسترسی به فرم‌ها', 'ghahghah' ),
		'ghahghah_faq_forms_wholesale'    => __( 'خرید عمده', 'ghahghah' ),
		'ghahghah_faq_forms_agency'       => __( 'درخواست نمایندگی', 'ghahghah' ),
	);
}

/**
 * Get an FAQ theme mod.
 *
 * @param string $key Mod key.
 * @return mixed
 */
function ghahghah_get_faq_mod( string $key ) {
	$defaults = ghahghah_faq_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( $key, $default );
}

/**
 * Published page ID for FAQ or contact (0 if invalid).
 */
function ghahghah_get_faq_related_page_id( string $which ): int {
	$key = 'contact' === $which ? 'ghahghah_contact_page_id' : 'ghahghah_faq_page_id';
	$id  = absint( ghahghah_get_faq_mod( $key ) );
	if ( $id <= 0 ) {
		return 0;
	}
	$page = get_post( $id );
	if ( ! $page || 'page' !== $page->post_type || ! is_post_publicly_viewable( $page ) ) {
		return 0;
	}
	return $id;
}

/**
 * Public URL for a configured page (empty when unset).
 */
function ghahghah_get_faq_related_page_url( string $which ): string {
	$id = ghahghah_get_faq_related_page_id( $which );
	if ( $id <= 0 ) {
		return '';
	}
	$url = get_permalink( $id );
	return is_string( $url ) && '' !== $url ? $url : '';
}

/**
 * FAQ page public URL.
 */
function ghahghah_get_faq_page_url(): string {
	return ghahghah_get_faq_related_page_url( 'faq' );
}

/**
 * Contact page public URL.
 */
function ghahghah_get_contact_page_url(): string {
	return ghahghah_get_faq_related_page_url( 'contact' );
}

/**
 * Resolve semantic FAQ link targets to real URLs (never "#").
 *
 * @param string $target wholesale_page|representation_page|product_archive|contact_page.
 */
function ghahghah_resolve_faq_link_target( string $target ): string {
	switch ( $target ) {
		case 'wholesale_page':
			$url = function_exists( 'ghahghah_get_request_page_url' )
				? ghahghah_get_request_page_url( 'wholesale' )
				: '';
			return is_string( $url ) ? $url : '';

		case 'representation_page':
			$url = function_exists( 'ghahghah_get_request_page_url' )
				? ghahghah_get_request_page_url( 'agency' )
				: '';
			return is_string( $url ) ? $url : '';

		case 'product_archive':
			return function_exists( 'ghahghah_get_products_archive_url' )
				? ghahghah_get_products_archive_url()
				: '';

		case 'contact_page':
			return ghahghah_get_contact_page_url();

		default:
			return '';
	}
}

/**
 * Path to bundled FAQ JSON.
 */
function ghahghah_faq_bundled_json_path(): string {
	return GHAHGHAH_THEME_DIR . '/inc/catalog/faq/faq-content.fa.json';
}

/**
 * Load starter groups from bundled JSON.
 *
 * @return array<int, array<string, mixed>>
 */
function ghahghah_faq_load_bundled_groups(): array {
	$path = ghahghah_faq_bundled_json_path();
	if ( ! is_readable( $path ) ) {
		return array();
	}
	$raw = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_string( $raw ) || '' === $raw ) {
		return array();
	}
	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) || empty( $data['groups'] ) || ! is_array( $data['groups'] ) ) {
		return array();
	}
	return ghahghah_faq_sanitize_groups( $data['groups'] );
}

/**
 * Allowed link target keys.
 *
 * @return array<int, string>
 */
function ghahghah_faq_link_target_keys(): array {
	return array( 'wholesale_page', 'representation_page', 'product_archive', 'contact_page' );
}

/**
 * Sanitize FAQ groups structure.
 *
 * @param mixed $groups Raw groups.
 * @return array<int, array{key: string, title: string, items: array<int, array<string, mixed>>}>
 */
function ghahghah_faq_sanitize_groups( $groups ): array {
	if ( ! is_array( $groups ) ) {
		return array();
	}

	$out = array();
	foreach ( $groups as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}
		$title = isset( $group['title'] ) ? sanitize_text_field( (string) $group['title'] ) : '';
		if ( '' === $title ) {
			continue;
		}
		$key   = isset( $group['key'] ) ? sanitize_key( (string) $group['key'] ) : '';
		$items = array();
		if ( ! empty( $group['items'] ) && is_array( $group['items'] ) ) {
			foreach ( $group['items'] as $item ) {
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
				if ( ! empty( $item['link'] ) && is_array( $item['link'] ) ) {
					$label  = isset( $item['link']['label'] ) ? sanitize_text_field( (string) $item['link']['label'] ) : '';
					$target = isset( $item['link']['target'] ) ? sanitize_key( (string) $item['link']['target'] ) : '';
					if ( '' !== $label && in_array( $target, ghahghah_faq_link_target_keys(), true ) ) {
						$entry['link'] = array(
							'label'  => $label,
							'target' => $target,
						);
					}
				}
				$items[] = $entry;
			}
		}
		if ( array() === $items ) {
			continue;
		}
		$out[] = array(
			'key'   => $key,
			'title' => $title,
			'items' => $items,
		);
	}

	return $out;
}

/**
 * Ensure only the first item across all groups is initially_open.
 *
 * @param array<int, array<string, mixed>> $groups Groups.
 * @return array<int, array<string, mixed>>
 */
function ghahghah_faq_normalize_open_state( array $groups ): array {
	$seen = false;
	foreach ( $groups as $gi => $group ) {
		if ( empty( $group['items'] ) || ! is_array( $group['items'] ) ) {
			continue;
		}
		foreach ( $group['items'] as $ii => $item ) {
			if ( ! $seen && ! empty( $item['initially_open'] ) ) {
				$groups[ $gi ]['items'][ $ii ]['initially_open'] = true;
				$seen = true;
			} else {
				$groups[ $gi ]['items'][ $ii ]['initially_open'] = false;
			}
		}
	}
	if ( ! $seen && ! empty( $groups[0]['items'][0] ) ) {
		$groups[0]['items'][0]['initially_open'] = true;
	}
	return $groups;
}

/**
 * Read FAQ groups for a page (meta → empty).
 *
 * @param int $page_id Page ID.
 * @return array<int, array<string, mixed>>
 */
function ghahghah_faq_get_page_groups( int $page_id ): array {
	if ( $page_id <= 0 ) {
		return array();
	}
	$raw = get_post_meta( $page_id, GHAHGHAH_FAQ_META_GROUPS, true );
	if ( is_string( $raw ) && '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		$raw     = $decoded;
	}
	$groups = ghahghah_faq_sanitize_groups( $raw );
	return ghahghah_faq_normalize_open_state( $groups );
}

/**
 * Groups for the configured FAQ page, with bundled fallback (read-only).
 *
 * @return array<int, array<string, mixed>>
 */
function ghahghah_get_faq_groups(): array {
	$page_id = ghahghah_get_faq_related_page_id( 'faq' );
	$groups  = $page_id > 0 ? ghahghah_faq_get_page_groups( $page_id ) : array();
	if ( array() !== $groups ) {
		return $groups;
	}
	return ghahghah_faq_normalize_open_state( ghahghah_faq_load_bundled_groups() );
}

/**
 * Persist groups on page meta and sync readable HTML into post_content when empty or marked.
 *
 * @param int                              $page_id Page ID.
 * @param array<int, array<string, mixed>> $groups  Groups.
 * @param bool                             $force_content Overwrite post_content.
 */
function ghahghah_faq_save_page_groups( int $page_id, array $groups, bool $force_content = false ): void {
	$page_id = absint( $page_id );
	if ( $page_id <= 0 ) {
		return;
	}
	$groups = ghahghah_faq_normalize_open_state( ghahghah_faq_sanitize_groups( $groups ) );
	update_post_meta( $page_id, GHAHGHAH_FAQ_META_GROUPS, wp_json_encode( $groups, JSON_UNESCAPED_UNICODE ) );

	$page = get_post( $page_id );
	if ( ! $page instanceof WP_Post ) {
		return;
	}
	$content = (string) $page->post_content;
	$marked  = str_contains( $content, 'ghahghah-faq-seed' ) || str_contains( $content, 'ghahghah-faq-group' );
	if ( $force_content || '' === trim( wp_strip_all_tags( $content ) ) || $marked ) {
		$html = ghahghah_faq_build_page_content_html( $groups );
		remove_action( 'save_post_page', 'ghahghah_faq_sync_meta_from_content', 20 );
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $html,
			)
		);
		add_action( 'save_post_page', 'ghahghah_faq_sync_meta_from_content', 20, 2 );
	}
}

/**
 * Build classic HTML for the page editor (details/summary, no nested controls in summary).
 *
 * @param array<int, array<string, mixed>> $groups Groups.
 */
function ghahghah_faq_build_page_content_html( array $groups ): string {
	$parts   = array( '<!-- ghahghah-faq-seed:editable-via-theme-config -->' );
	$parts[] = '<!-- wp:html -->';
	foreach ( $groups as $group ) {
		$title = (string) ( $group['title'] ?? '' );
		$key   = (string) ( $group['key'] ?? '' );
		$parts[] = sprintf(
			'<section class="ghahghah-faq-group" data-faq-group="%s"><h2 class="ghahghah-faq-group__title">%s</h2>',
			esc_attr( $key ),
			esc_html( $title )
		);
		$items = is_array( $group['items'] ?? null ) ? $group['items'] : array();
		foreach ( $items as $item ) {
			$open     = ! empty( $item['initially_open'] ) ? ' open' : '';
			$question = (string) ( $item['question'] ?? '' );
			$answer   = (string) ( $item['answer'] ?? '' );
			$item_key = (string) ( $item['key'] ?? '' );
			$parts[]  = sprintf(
				'<details class="ghahghah-faq-item" data-faq-item="%s"%s><summary class="ghahghah-faq-item__summary"><span class="ghahghah-faq-item__question">%s</span><span class="ghahghah-faq-item__toggle" aria-hidden="true"></span></summary><div class="ghahghah-faq-item__body"><p>%s</p>',
				esc_attr( $item_key ),
				$open,
				esc_html( $question ),
				esc_html( $answer )
			);
			if ( ! empty( $item['link'] ) && is_array( $item['link'] ) ) {
				$label  = (string) ( $item['link']['label'] ?? '' );
				$target = (string) ( $item['link']['target'] ?? '' );
				$url    = ghahghah_resolve_faq_link_target( $target );
				if ( '' !== $label && '' !== $url ) {
					$parts[] = sprintf(
						'<p class="ghahghah-faq-item__link-wrap"><a class="ghahghah-faq-item__link" href="%s" data-faq-target="%s">%s</a></p>',
						esc_url( $url ),
						esc_attr( $target ),
						esc_html( $label )
					);
				} elseif ( '' !== $label && '' !== $target ) {
					$parts[] = sprintf(
						'<!-- faq-link target=%s label=%s (destination unavailable) -->',
						esc_html( $target ),
						esc_html( $label )
					);
				}
			}
			$parts[] = '</div></details>';
		}
		$parts[] = '</section>';
	}
	$parts[] = '<!-- /wp:html -->';
	return implode( "\n", $parts );
}

/**
 * When FAQ page is saved in the editor, try to refresh meta from HTML.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function ghahghah_faq_sync_meta_from_content( int $post_id, $post ): void {
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$template = (string) get_page_template_slug( $post_id );
	if ( 'page-templates/faq.php' !== $template ) {
		return;
	}
	$parsed = ghahghah_faq_parse_groups_from_html( (string) $post->post_content );
	if ( array() === $parsed ) {
		return;
	}
	update_post_meta( $post_id, GHAHGHAH_FAQ_META_GROUPS, wp_json_encode( $parsed, JSON_UNESCAPED_UNICODE ) );
}
add_action( 'save_post_page', 'ghahghah_faq_sync_meta_from_content', 20, 2 );

/**
 * Parse FAQ groups from seeded HTML.
 *
 * @param string $html Page content.
 * @return array<int, array<string, mixed>>
 */
function ghahghah_faq_parse_groups_from_html( string $html ): array {
	if ( '' === trim( $html ) || ! str_contains( $html, 'ghahghah-faq-group' ) ) {
		return array();
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return array();
	}

	$prev = libxml_use_internal_errors( true );
	$dom  = new DOMDocument();
	$wrapped = '<?xml encoding="utf-8" ?><div id="gh-faq-root">' . $html . '</div>';
	$loaded  = $dom->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );
	if ( ! $loaded ) {
		return array();
	}

	$xpath  = new DOMXPath( $dom );
	$nodes  = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " ghahghah-faq-group ")]' );
	$groups = array();
	if ( ! $nodes ) {
		return array();
	}

	foreach ( $nodes as $section ) {
		if ( ! $section instanceof DOMElement ) {
			continue;
		}
		$title_el = null;
		foreach ( $section->getElementsByTagName( 'h2' ) as $h2 ) {
			$title_el = $h2;
			break;
		}
		$title = $title_el ? trim( $title_el->textContent ?? '' ) : '';
		if ( '' === $title ) {
			continue;
		}
		$items = array();
		foreach ( $section->getElementsByTagName( 'details' ) as $details ) {
			if ( ! $details instanceof DOMElement ) {
				continue;
			}
			$summary = $details->getElementsByTagName( 'summary' )->item( 0 );
			$q_node  = null;
			if ( $summary instanceof DOMElement ) {
				foreach ( $summary->getElementsByTagName( 'span' ) as $span ) {
					if ( $span instanceof DOMElement && str_contains( (string) $span->getAttribute( 'class' ), 'ghahghah-faq-item__question' ) ) {
						$q_node = $span;
						break;
					}
				}
			}
			$question = $q_node ? trim( $q_node->textContent ?? '' ) : ( $summary ? trim( $summary->textContent ?? '' ) : '' );
			$answer   = '';
			$link     = null;
			foreach ( $details->childNodes as $child ) {
				if ( ! $child instanceof DOMElement ) {
					continue;
				}
				if ( 'div' === strtolower( $child->tagName ) && str_contains( (string) $child->getAttribute( 'class' ), 'ghahghah-faq-item__body' ) ) {
					$paras = $child->getElementsByTagName( 'p' );
					if ( $paras->length > 0 ) {
						$answer = trim( $paras->item( 0 )->textContent ?? '' );
					}
					$anchors = $child->getElementsByTagName( 'a' );
					if ( $anchors->length > 0 ) {
						$a = $anchors->item( 0 );
						if ( $a instanceof DOMElement ) {
							$label  = trim( $a->textContent ?? '' );
							$target = sanitize_key( (string) $a->getAttribute( 'data-faq-target' ) );
							if ( '' !== $label && in_array( $target, ghahghah_faq_link_target_keys(), true ) ) {
								$link = array(
									'label'  => $label,
									'target' => $target,
								);
							}
						}
					}
				}
			}
			if ( '' === $question || '' === $answer ) {
				continue;
			}
			$entry = array(
				'key'            => sanitize_key( (string) $details->getAttribute( 'data-faq-item' ) ),
				'question'       => $question,
				'answer'         => $answer,
				'initially_open' => $details->hasAttribute( 'open' ),
			);
			if ( null !== $link ) {
				$entry['link'] = $link;
			}
			$items[] = $entry;
		}
		if ( array() === $items ) {
			continue;
		}
		$groups[] = array(
			'key'   => sanitize_key( (string) $section->getAttribute( 'data-faq-group' ) ),
			'title' => $title,
			'items' => $items,
		);
	}

	return ghahghah_faq_normalize_open_state( ghahghah_faq_sanitize_groups( $groups ) );
}

/**
 * Whether current view uses the FAQ page template.
 */
function ghahghah_is_faq_page(): bool {
	return is_page_template( 'page-templates/faq.php' );
}
