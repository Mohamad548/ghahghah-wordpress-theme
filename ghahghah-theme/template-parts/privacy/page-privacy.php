<?php
/**
 * Privacy policy page layout.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = get_the_title();
$lead       = trim( (string) ghahghah_get_privacy_page_mod( 'ghahghah_privacy_page_lead' ) );
$updated    = get_the_modified_date( get_option( 'date_format' ) );
$contact    = function_exists( 'ghahghah_get_contact_page_url' ) ? (string) ghahghah_get_contact_page_url() : '';
?>
<div class="ghahghah-privacy__shell">
	<nav class="ghahghah-privacy__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
		<span class="ghahghah-privacy__crumb-sep" aria-hidden="true">
			<?php ghahghah_the_form_icon( 'chevron-left', array( 'modifiers' => array( 'muted' ) ) ); ?>
		</span>
		<span aria-current="page"><?php echo esc_html( '' !== $page_title ? $page_title : __( 'حریم خصوصی', 'ghahghah' ) ); ?></span>
	</nav>

	<header class="ghahghah-privacy__intro">
		<div class="ghahghah-privacy__intro-copy">
			<p class="ghahghah-privacy__eyebrow"><?php esc_html_e( 'سیاست حفظ اطلاعات', 'ghahghah' ); ?></p>
			<h1 class="ghahghah-privacy__title">
				<span class="ghahghah-privacy__title-accents" aria-hidden="true">
					<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
				</span>
				<?php
				$display_title = '' !== $page_title ? $page_title : __( 'حریم خصوصی', 'ghahghah' );
				echo wp_kses(
					preg_replace(
						'/(خصوصی)/u',
						'<span class="ghahghah-privacy__title-accent">$1</span>',
						esc_html( $display_title ),
						1
					) ?? esc_html( $display_title ),
					array( 'span' => array( 'class' => true ) )
				);
				?>
			</h1>
			<?php if ( '' !== $lead ) : ?>
				<p class="ghahghah-privacy__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
			<?php if ( is_string( $updated ) && '' !== $updated ) : ?>
				<p class="ghahghah-privacy__meta">
					<span class="ghahghah-privacy__meta-icon" aria-hidden="true">
						<?php ghahghah_the_form_icon( 'clock', array( 'modifiers' => array( 'muted' ) ) ); ?>
					</span>
					<span>
						<?php
						printf(
							/* translators: %s: last updated date */
							esc_html__( 'آخرین به‌روزرسانی: %s', 'ghahghah' ),
							esc_html( $updated )
						);
						?>
					</span>
				</p>
			<?php endif; ?>
		</div>
		<div class="ghahghah-privacy__intro-art" aria-hidden="true">
			<span class="ghahghah-privacy__intro-art-disc">
				<?php ghahghah_the_form_icon( 'document', array( 'modifiers' => array( 'hero' ) ) ); ?>
			</span>
		</div>
	</header>

	<article <?php post_class( 'ghahghah-privacy__article' ); ?>>
		<div class="ghahghah-privacy__content entry-content">
			<?php the_content(); ?>
		</div>
	</article>

	<?php if ( '' !== $contact ) : ?>
		<aside class="ghahghah-privacy__aside" aria-label="<?php esc_attr_e( 'تماس درباره حریم خصوصی', 'ghahghah' ); ?>">
			<div class="ghahghah-privacy__aside-copy">
				<h2 class="ghahghah-privacy__aside-title"><?php esc_html_e( 'سؤالی دارید؟', 'ghahghah' ); ?></h2>
				<p class="ghahghah-privacy__aside-text"><?php esc_html_e( 'برای پرسش درباره داده‌های ارسالی یا این سیاست، از صفحه تماس پیام بفرستید.', 'ghahghah' ); ?></p>
			</div>
			<a class="ghahghah-privacy__aside-btn" href="<?php echo esc_url( $contact ); ?>">
				<span><?php esc_html_e( 'تماس با ما', 'ghahghah' ); ?></span>
				<?php ghahghah_the_form_icon( 'arrow-left', array( 'modifiers' => array( 'action' ) ) ); ?>
			</a>
		</aside>
	<?php endif; ?>
</div>
