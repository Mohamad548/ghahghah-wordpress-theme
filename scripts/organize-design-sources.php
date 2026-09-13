<?php
/**
 * One-shot safe image organizer for design-sources.
 * Does not modify source files.
 *
 * Usage:
 *   php scripts/organize-design-sources.php <REPO_ROOT> <RAW_SOURCE_DIR> <UI_SOURCE_DIR>
 *
 * Or set environment variables:
 *   GHAHGHAH_REPO_ROOT, GHAHGHAH_RAW_SOURCE, GHAHGHAH_UI_SOURCE
 *
 * @package Ghahghah
 */

declare(strict_types=1);

$root = $argv[1] ?? getenv( 'GHAHGHAH_REPO_ROOT' ) ?: '';
$raw  = $argv[2] ?? getenv( 'GHAHGHAH_RAW_SOURCE' ) ?: '';
$ui   = $argv[3] ?? getenv( 'GHAHGHAH_UI_SOURCE' ) ?: '';

$root = is_string( $root ) ? rtrim( str_replace( '\\', '/', $root ), '/' ) : '';
$raw  = is_string( $raw ) ? rtrim( str_replace( '\\', '/', $raw ), '/' ) : '';
$ui   = is_string( $ui ) ? rtrim( str_replace( '\\', '/', $ui ), '/' ) : '';

if ( '' === $root || '' === $raw || '' === $ui || ! is_dir( $root ) || ! is_dir( $raw ) || ! is_dir( $ui ) ) {
	fwrite(
		STDERR,
		"Usage: php scripts/organize-design-sources.php <REPO_ROOT> <RAW_SOURCE_DIR> <UI_SOURCE_DIR>\n" .
		"Or set GHAHGHAH_REPO_ROOT, GHAHGHAH_RAW_SOURCE, GHAHGHAH_UI_SOURCE.\n"
	);
	exit( 1 );
}

$seen_hashes = array();
$inventory   = array();
$stats       = array(
	'folder1_expected'   => 16,
	'folder1_actual'     => 0,
	'folder2_actual'     => 0,
	'copied'             => 0,
	'duplicates_skipped' => 0,
	'superseded'         => 0,
	'review'             => 0,
	'failed'             => 0,
);

/**
 * Collect file metadata.
 *
 * @param string $path Absolute path.
 * @return array{size:int,hash:string,w:?int,h:?int,mime:string}
 */
function ghahghah_img_meta( string $path ): array {
	$size = (int) filesize( $path );
	$hash = hash_file( 'sha256', $path );
	$w    = null;
	$h    = null;
	$mime = mime_content_type( $path ) ?: '';
	$info = @getimagesize( $path );
	if ( is_array( $info ) ) {
		$w    = (int) $info[0];
		$h    = (int) $info[1];
		$mime = $info['mime'] ?? $mime;
	}
	return array(
		'size' => $size,
		'hash' => $hash,
		'w'    => $w,
		'h'    => $h,
		'mime' => $mime,
	);
}

/**
 * Neutralize absolute source roots in inventory paths.
 *
 * @param string $path Absolute path.
 * @param string $raw  Raw source root.
 * @param string $ui   UI source root.
 */
function ghahghah_neutral_path( string $path, string $raw, string $ui ): string {
	$normalized = str_replace( '\\', '/', $path );
	if ( str_starts_with( $normalized, $raw . '/' ) || $normalized === $raw ) {
		return '<RAW_SOURCE_DIR>' . substr( $normalized, strlen( $raw ) );
	}
	if ( str_starts_with( $normalized, $ui . '/' ) || $normalized === $ui ) {
		return '<UI_SOURCE_DIR>' . substr( $normalized, strlen( $ui ) );
	}
	return basename( $normalized );
}

/**
 * Safely copy one file.
 *
 * @param string               $src            Source absolute path.
 * @param string               $dest_rel       Destination relative to repo root.
 * @param string               $classification Classification label.
 * @param string               $status         approved-reference|superseded|review|...
 * @param string               $reason         Human reason.
 * @param array<string,string> $seen_hashes    Hash map.
 * @param array<int,array>     $inventory      Inventory rows.
 * @param array<string,int>    $stats          Counters.
 * @param string               $root           Repo root.
 * @param string               $raw_root       Raw source root for inventory labels.
 * @param string               $ui_root        UI source root for inventory labels.
 */
function ghahghah_safe_copy(
	string $src,
	string $dest_rel,
	string $classification,
	string $status,
	string $reason,
	array &$seen_hashes,
	array &$inventory,
	array &$stats,
	string $root,
	string $raw_root,
	string $ui_root
): void {
	$m    = ghahghah_img_meta( $src );
	$orig = basename( $src );

	$row_base = array(
		'original_path'     => ghahghah_neutral_path( $src, $raw_root, $ui_root ),
		'original_filename' => $orig,
		'size'              => $m['size'],
		'dimensions'        => ( $m['w'] && $m['h'] ) ? "{$m['w']}×{$m['h']}" : 'n/a',
		'mime'              => $m['mime'],
		'sha256'            => $m['hash'],
		'classification'    => $classification,
		'reason'            => $reason,
	);

	if ( isset( $seen_hashes[ $m['hash'] ] ) ) {
		$inventory[] = $row_base + array(
			'destination'      => '(skipped duplicate of ' . $seen_hashes[ $m['hash'] ] . ')',
			'duplicate_status' => 'byte-identical duplicate — skipped',
			'status'           => 'duplicate-skipped',
			'reason'           => $reason . ' Same SHA-256 as already copied file.',
		);
		++$stats['duplicates_skipped'];
		return;
	}

	$dest_path = $root . '/' . $dest_rel;
	$dest_dir  = dirname( $dest_path );

	if ( is_file( $dest_path ) ) {
		$existing_hash = hash_file( 'sha256', $dest_path );
		if ( hash_equals( $existing_hash, $m['hash'] ) ) {
			$inventory[] = $row_base + array(
				'destination'      => $dest_rel,
				'duplicate_status' => 'destination already identical',
				'status'           => 'duplicate-skipped',
			);
			++$stats['duplicates_skipped'];
			$seen_hashes[ $m['hash'] ] = $dest_rel;
			return;
		}
		$ext       = pathinfo( $dest_path, PATHINFO_EXTENSION );
		$base      = pathinfo( $dest_path, PATHINFO_FILENAME );
		$dest_rel  = dirname( $dest_rel ) . '/' . $base . '-' . substr( $m['hash'], 0, 8 ) . ( $ext ? '.' . $ext : '' );
		$dest_path = $root . '/' . $dest_rel;
	}

	if ( ! is_dir( $dest_dir ) && ! mkdir( $dest_dir, 0777, true ) && ! is_dir( $dest_dir ) ) {
		++$stats['failed'];
		$inventory[] = $row_base + array(
			'destination'      => $dest_rel,
			'duplicate_status' => 'n/a',
			'status'           => 'failed',
			'reason'           => 'Could not create destination directory.',
		);
		return;
	}

	if ( ! copy( $src, $dest_path ) ) {
		++$stats['failed'];
		$inventory[] = $row_base + array(
			'destination'      => $dest_rel,
			'duplicate_status' => 'n/a',
			'status'           => 'failed',
			'reason'           => 'copy() failed.',
		);
		return;
	}

	$dest_hash = hash_file( 'sha256', $dest_path );
	if ( ! hash_equals( $m['hash'], $dest_hash ) ) {
		++$stats['failed'];
		unlink( $dest_path );
		$inventory[] = $row_base + array(
			'destination'      => $dest_rel,
			'duplicate_status' => 'n/a',
			'status'           => 'failed',
			'reason'           => 'Post-copy hash mismatch; destination removed.',
		);
		return;
	}

	$seen_hashes[ $m['hash'] ] = $dest_rel;
	++$stats['copied'];
	if ( 'superseded' === $status ) {
		++$stats['superseded'];
	}
	if ( 'review' === $status ) {
		++$stats['review'];
	}

	$inventory[] = $row_base + array(
		'destination'      => $dest_rel,
		'duplicate_status' => 'unique',
		'status'           => $status,
	);
}

$dirs = array(
	"$root/design-sources/brand",
	"$root/design-sources/raw-products",
	"$root/design-sources/temporary",
	"$root/design-sources/ui-reference/components",
	"$root/design-sources/ui-reference/desktop",
	"$root/design-sources/ui-reference/mobile",
	"$root/design-sources/ui-reference/superseded",
	"$root/design-sources/ui-reference/review",
);
foreach ( $dirs as $dir ) {
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}
}

$raw_files = array_values(
	array_filter(
		scandir( $raw ) ?: array(),
		static function ( string $f ) use ( $raw ): bool {
			return '.' !== $f && '..' !== $f && is_file( "$raw/$f" );
		}
	)
);
$stats['folder1_actual'] = count( $raw_files );

foreach ( $raw_files as $f ) {
	$src = "$raw/$f";
	$ext = strtolower( (string) pathinfo( $f, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp', 'avif' ), true ) ) {
		ghahghah_safe_copy( $src, "design-sources/ui-reference/review/$f", 'review', 'review', 'Unexpected extension from folder 1.', $seen_hashes, $inventory, $stats, $root, $raw, $ui );
		continue;
	}
	if ( 'photo_2026-09-13_16-34-45.jpg' === $f ) {
		ghahghah_safe_copy(
			$src,
			'design-sources/brand/ghahghah-logo-source.jpg',
			'brand-logo',
			'approved-source',
			'Known standalone Ghahghah logo (white background preserved, no conversion).',
			$seen_hashes,
			$inventory,
			$stats,
			$root,
			$raw,
			$ui
		);
		continue;
	}
	ghahghah_safe_copy(
		$src,
		"design-sources/raw-products/$f",
		'raw-product',
		'source-preserve',
		'Product/package photograph from folder 1; original bytes preserved. Package text is not treated as structured product data.',
		$seen_hashes,
		$inventory,
		$stats,
		$root,
		$raw,
		$ui
	);
}

$ui_map = array(
	'Qahqah Persian UI component library.png'       => array( 'design-sources/ui-reference/components/Qahqah Persian UI component library.png', 'ui-component', 'approved-reference', 'Filename identifies UI component / design-system library.' ),
	'Corrected RTL Persian Mobile Homepage.png'     => array( 'design-sources/ui-reference/mobile/Corrected RTL Persian Mobile Homepage.png', 'ui-mobile-homepage', 'approved-reference', 'Explicitly named Corrected mobile homepage; portrait dimensions; preferred over earlier mobile homepage.' ),
	'Persian Pizza-Flavored Snack Mobile Page.png'  => array( 'design-sources/ui-reference/mobile/Persian Pizza-Flavored Snack Mobile Page.png', 'ui-mobile-product', 'approved-reference', 'Filename and portrait dimensions indicate mobile product page.' ),
	"Qahqahé’s vibrant Persian snack homepage.png"  => array( 'design-sources/ui-reference/superseded/Qahqahé’s vibrant Persian snack homepage.png', 'ui-mobile-homepage', 'superseded', 'Earlier mobile homepage alternative; superseded by Corrected RTL Persian Mobile Homepage.' ),
	'Ghahghah Persian Snack Website Hero.png'       => array( 'design-sources/ui-reference/desktop/Ghahghah Persian Snack Website Hero.png', 'ui-desktop-hero', 'approved-reference', 'Desktop hero using identifiable Ghahghah flavour packaging (preferred over placeholder/generic hero).' ),
	'Qahqheh Corn Snack Website Hero.png'           => array( 'design-sources/ui-reference/superseded/Qahqheh Corn Snack Website Hero.png', 'ui-desktop-hero', 'superseded', 'Older hero with placeholder product imagery note; superseded by Ghahghah Persian Snack Website Hero.' ),
	'Persian article archive with handshake icon.png' => array( 'design-sources/ui-reference/desktop/Persian article archive with handshake icon.png', 'ui-desktop-article-archive', 'approved-reference', 'Filename identifies handshake/partnership CTA variant preferred over shopping-cart article archive.' ),
	'Ghaghehe Persian Snack Article Archive.png'    => array( 'design-sources/ui-reference/superseded/Ghaghehe Persian Snack Article Archive.png', 'ui-desktop-article-archive', 'superseded', 'Older article-archive alternative; superseded by handshake-icon variant per correction rule 1.' ),
	'Persian RTL form with neutral placeholders.png' => array( 'design-sources/ui-reference/desktop/Persian RTL form with neutral placeholders.png', 'ui-desktop-representation-form', 'approved-reference', 'Representation form with neutral placeholders; preferred over form with fabricated sample name/phone.' ),
	'Qahqah Partnership Request Form.png'           => array( 'design-sources/ui-reference/superseded/Qahqah Partnership Request Form.png', 'ui-desktop-representation-form', 'superseded', 'Representation form using sample personal name and phone placeholders; superseded by neutral-placeholder form.' ),
	'Factory Introduction & Quality Control.png'    => array( 'design-sources/ui-reference/desktop/Factory Introduction & Quality Control.png', 'ui-desktop-factory-intro-section', 'approved-reference', 'Factory introduction section with temporary factory image label and certificate placeholders.' ),
	'RTL Persian factory webpage mockup.png'        => array( 'design-sources/ui-reference/desktop/RTL Persian factory webpage mockup.png', 'ui-desktop-factory-page', 'approved-reference', 'Full factory page with temporary image label, certificate placeholders, and partnership-style CTA.' ),
	'From Corn to Crunch_ Factory Showcase.png'     => array( 'design-sources/ui-reference/superseded/From Corn to Crunch_ Factory Showcase.png', 'ui-desktop-factory-page', 'superseded', 'Factory page alternative with shopping-cart header CTA; superseded by RTL Persian factory webpage mockup.' ),
	'Corn Snack Production Timeline.png'            => array( 'design-sources/ui-reference/desktop/Corn Snack Production Timeline.png', 'ui-desktop-production-process', 'approved-reference', 'Desktop production-process / timeline section reference.' ),
	'Qahqaha Corn Snack Production Feature.png'     => array( 'design-sources/ui-reference/desktop/Qahqaha Corn Snack Production Feature.png', 'ui-desktop-production-article', 'approved-reference', 'Desktop production-process article/feature page reference; content marked temporary pending factory approval.' ),
	'Ghaghahé Snack Products Showcase.png'          => array( 'design-sources/ui-reference/desktop/Ghaghahé Snack Products Showcase.png', 'ui-desktop-product-showcase', 'approved-reference', 'Desktop product showcase / collection section reference.' ),
	'Ghahghah’s Featured Corn Snack Collection.png' => array( 'design-sources/ui-reference/desktop/Ghahghah’s Featured Corn Snack Collection.png', 'ui-desktop-product-collection', 'approved-reference', 'Desktop featured product collection / archive-style reference.' ),
	'Premium Pizza Snack Product Page.png'          => array( 'design-sources/ui-reference/desktop/Premium Pizza Snack Product Page.png', 'ui-desktop-single-product', 'approved-reference', 'Desktop single product page reference.' ),
	'Ghahgheh Persian Corporate Contact Page.png'   => array( 'design-sources/ui-reference/desktop/Ghahgheh Persian Corporate Contact Page.png', 'ui-desktop-contact', 'approved-reference', 'Desktop contact page reference. Visible contact details in mockup are not verified business data.' ),
	'Ghahghah Wholesale and Representation.png'     => array( 'design-sources/ui-reference/desktop/Ghahghah Wholesale and Representation.png', 'ui-desktop-wholesale-cta', 'approved-reference', 'Desktop wholesale / representation CTA section reference.' ),
	'Qahqah wholesale inquiry page.png'             => array( 'design-sources/ui-reference/desktop/Qahqah wholesale inquiry page.png', 'ui-desktop-wholesale-form', 'approved-reference', 'Desktop wholesale inquiry form page reference.' ),
	'Qahqah Persian Corn Snack Footer.png'          => array( 'design-sources/ui-reference/desktop/Qahqah Persian Corn Snack Footer.png', 'ui-desktop-footer', 'approved-reference', 'Desktop footer reference. Footer contact/address text is mockup-only, not verified business data.' ),
);

$ui_files = array_values(
	array_filter(
		scandir( $ui ) ?: array(),
		static function ( string $f ) use ( $ui ): bool {
			return '.' !== $f && '..' !== $f && is_file( "$ui/$f" );
		}
	)
);
$stats['folder2_actual'] = count( $ui_files );

foreach ( $ui_files as $f ) {
	$src = "$ui/$f";
	if ( ! isset( $ui_map[ $f ] ) ) {
		ghahghah_safe_copy(
			$src,
			"design-sources/ui-reference/review/$f",
			'review',
			'review',
			'No confident classification mapping; preserved for human review.',
			$seen_hashes,
			$inventory,
			$stats,
			$root,
			$raw,
			$ui
		);
		continue;
	}
	[ $dest, $class, $status, $reason ] = $ui_map[ $f ];
	ghahghah_safe_copy( $src, $dest, $class, $status, $reason, $seen_hashes, $inventory, $stats, $root, $raw, $ui );
}

$md  = "# Image inventory\n\n";
$md .= 'Generated: ' . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n";
$md .= "## Summary\n\n";
$md .= "| Metric | Value |\n|---|---|\n";
$md .= '| Expected folder `1` count (from screenshot) | ~' . $stats['folder1_expected'] . " |\n";
$md .= '| Actual folder `1` count | ' . $stats['folder1_actual'] . " |\n";
$md .= '| Actual folder `2` count | ' . $stats['folder2_actual'] . " |\n";
$md .= '| Successfully copied | ' . $stats['copied'] . " |\n";
$md .= '| Duplicates skipped | ' . $stats['duplicates_skipped'] . " |\n";
$md .= '| Marked superseded (copied) | ' . $stats['superseded'] . " |\n";
$md .= '| Ambiguous / review | ' . $stats['review'] . " |\n";
$md .= '| Failed | ' . $stats['failed'] . " |\n\n";
$md .= "> Files in folder `2` are **visual references**, not automatically approved final art. Status is recorded per file below.\n\n";
$md .= "## Source roots\n\n";
$md .= "- Folder 1: `<RAW_SOURCE_DIR>`\n";
$md .= "- Folder 2: `<UI_SOURCE_DIR>`\n\n";
$md .= "## Files\n\n";

foreach ( $inventory as $i => $row ) {
	$n   = $i + 1;
	$md .= "### {$n}. {$row['original_filename']}\n\n";
	$md .= "- **Original absolute path:** `{$row['original_path']}`\n";
	$md .= "- **Original filename:** `{$row['original_filename']}`\n";
	$md .= "- **Destination relative path:** `{$row['destination']}`\n";
	$md .= "- **File size:** {$row['size']} bytes\n";
	$md .= "- **Pixel dimensions:** {$row['dimensions']}\n";
	$md .= "- **Detected MIME/format:** {$row['mime']}\n";
	$md .= "- **SHA-256:** `{$row['sha256']}`\n";
	$md .= "- **Classification:** {$row['classification']}\n";
	$md .= "- **Duplicate status:** {$row['duplicate_status']}\n";
	$md .= "- **Status:** {$row['status']}\n";
	$md .= "- **Reason:** {$row['reason']}\n\n";
}

file_put_contents( "$root/design-sources/image-inventory.md", str_replace( "\r\n", "\n", $md ) );

echo json_encode( $stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
echo 'Inventory entries: ' . count( $inventory ) . PHP_EOL;
