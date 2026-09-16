<?php
/**
 * FAQ page layout (desktop sidebar + mobile stack).
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title          = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_intro_title' ) );
$lead           = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_intro_text' ) );
$support_title  = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_support_title' ) );
$support_text   = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_support_text' ) );
$support_btn    = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_support_button' ) );
$forms_title    = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_forms_title' ) );
$forms_wh       = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_forms_wholesale' ) );
$forms_ag       = trim( (string) ghahghah_get_faq_mod( 'ghahghah_faq_forms_agency' ) );
$groups         = ghahghah_get_faq_groups();
$contact_url    = ghahghah_get_contact_page_url();
$wholesale_url  = function_exists( 'ghahghah_get_request_page_url' ) ? ghahghah_get_request_page_url( 'wholesale' ) : '';
$agency_url     = function_exists( 'ghahghah_get_request_page_url' ) ? ghahghah_get_request_page_url( 'agency' ) : '';
$page_title     = get_the_title();
?>
<div class="ghahghah-faq">
	<div class="ghahghah-faq__shell">
		<nav class="ghahghah-faq__crumbs" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'ghahghah' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ghahghah' ); ?></a>
			<span class="ghahghah-faq__crumb-sep" aria-hidden="true">
				<?php ghahghah_the_form_icon( 'chevron-left', array( 'modifiers' => array( 'muted' ) ) ); ?>
			</span>
			<span aria-current="page"><?php echo esc_html( '' !== $page_title ? $page_title : __( 'پرسش‌های متداول', 'ghahghah' ) ); ?></span>
		</nav>

		<header class="ghahghah-faq__intro">
			<div class="ghahghah-faq__intro-copy">
				<h1 class="ghahghah-faq__title">
					<span class="ghahghah-faq__title-accents" aria-hidden="true">
						<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
					</span>
					<?php
					$display_title = '' !== $title ? $title : ( '' !== $page_title ? $page_title : __( 'پرسش‌های متداول', 'ghahghah' ) );
					echo wp_kses(
						preg_replace(
							'/(متداول)/u',
							'<span class="ghahghah-faq__title-accent">$1</span>',
							esc_html( $display_title ),
							1
						) ?? esc_html( $display_title ),
						array( 'span' => array( 'class' => true ) )
					);
					?>
				</h1>
				<?php if ( '' !== $lead ) : ?>
					<p class="ghahghah-faq__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>
			</div>
			<div class="ghahghah-faq__intro-art" aria-hidden="true">
				<span class="ghahghah-faq__intro-art-disc">
					<?php ghahghah_the_form_icon( 'message-question', array( 'modifiers' => array( 'hero' ) ) ); ?>
				</span>
			</div>
		</header>

		<div class="ghahghah-faq__layout">
			<div class="ghahghah-faq__main">
				<?php foreach ( $groups as $group ) : ?>
					<?php
					$g_title = (string) ( $group['title'] ?? '' );
					$items   = is_array( $group['items'] ?? null ) ? $group['items'] : array();
					if ( '' === $g_title || array() === $items ) {
						continue;
					}
					?>
					<section class="ghahghah-faq__group">
						<h2 class="ghahghah-faq__group-title">
							<span class="ghahghah-faq__group-accent" aria-hidden="true">
								<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
							</span>
							<span><?php echo esc_html( $g_title ); ?></span>
						</h2>
						<div class="ghahghah-faq__list">
							<?php foreach ( $items as $item ) : ?>
								<?php
								$question = (string) ( $item['question'] ?? '' );
								$answer   = (string) ( $item['answer'] ?? '' );
								if ( '' === $question || '' === $answer ) {
									continue;
								}
								$open = ! empty( $item['initially_open'] );
								?>
								<details class="ghahghah-faq__item"<?php echo $open ? ' open' : ''; ?>>
									<summary class="ghahghah-faq__summary">
										<span class="ghahghah-faq__question"><?php echo esc_html( $question ); ?></span>
										<span class="ghahghah-faq__toggle" aria-hidden="true">
											<span class="ghahghah-faq__toggle-plus">
												<?php ghahghah_the_form_icon( 'plus', array( 'modifiers' => array( 'faq-toggle' ) ) ); ?>
											</span>
											<span class="ghahghah-faq__toggle-minus">
												<?php ghahghah_the_form_icon( 'minus', array( 'modifiers' => array( 'faq-toggle' ) ) ); ?>
											</span>
										</span>
									</summary>
									<div class="ghahghah-faq__answer">
										<p><?php echo esc_html( $answer ); ?></p>
										<?php
										if ( ! empty( $item['link'] ) && is_array( $item['link'] ) ) :
											$label  = (string) ( $item['link']['label'] ?? '' );
											$target = (string) ( $item['link']['target'] ?? '' );
											$url    = ghahghah_resolve_faq_link_target( $target );
											if ( '' !== $label && '' !== $url ) :
												?>
												<p class="ghahghah-faq__link-wrap">
													<a class="ghahghah-faq__link" href="<?php echo esc_url( $url ); ?>">
														<span><?php echo esc_html( $label ); ?></span>
														<?php ghahghah_the_form_icon( 'arrow-left', array( 'modifiers' => array( 'action' ) ) ); ?>
													</a>
												</p>
												<?php
											endif;
										endif;
										?>
									</div>
								</details>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			</div>

			<aside class="ghahghah-faq__aside">
				<?php if ( '' !== $support_title || '' !== $support_text || ( '' !== $support_btn && '' !== $contact_url ) ) : ?>
					<section class="ghahghah-faq__card ghahghah-faq__card--support" aria-labelledby="gh-faq-support-title">
						<h2 id="gh-faq-support-title" class="ghahghah-faq__card-title">
							<span class="ghahghah-faq__card-accent" aria-hidden="true">
								<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
							</span>
							<span><?php echo esc_html( '' !== $support_title ? $support_title : __( 'پاسخ خود را پیدا نکردید؟', 'ghahghah' ) ); ?></span>
						</h2>
						<div class="ghahghah-faq__card-body">
							<span class="ghahghah-faq__card-icon" aria-hidden="true">
								<?php ghahghah_the_form_icon( 'message-circle', array( 'modifiers' => array( 'aside' ) ) ); ?>
							</span>
							<?php if ( '' !== $support_text ) : ?>
								<p class="ghahghah-faq__card-text"><?php echo esc_html( $support_text ); ?></p>
							<?php endif; ?>
						</div>
						<?php if ( '' !== $support_btn && '' !== $contact_url ) : ?>
							<a class="ghahghah-faq__btn ghahghah-faq__btn--solid" href="<?php echo esc_url( $contact_url ); ?>">
								<?php echo esc_html( $support_btn ); ?>
							</a>
						<?php endif; ?>
					</section>
				<?php endif; ?>

				<?php if ( '' !== $forms_title || '' !== $wholesale_url || '' !== $agency_url ) : ?>
					<section class="ghahghah-faq__card ghahghah-faq__card--forms" aria-labelledby="gh-faq-forms-title">
						<h2 id="gh-faq-forms-title" class="ghahghah-faq__card-title">
							<span class="ghahghah-faq__card-accent" aria-hidden="true">
								<?php ghahghah_the_form_icon( 'title-accent', array( 'modifiers' => array( 'accent' ) ) ); ?>
							</span>
							<span><?php echo esc_html( '' !== $forms_title ? $forms_title : __( 'دسترسی به فرم‌ها', 'ghahghah' ) ); ?></span>
						</h2>
						<div class="ghahghah-faq__card-body ghahghah-faq__card-body--forms">
							<span class="ghahghah-faq__card-icon ghahghah-faq__card-icon--soft" aria-hidden="true">
								<?php ghahghah_the_form_icon( 'link', array( 'modifiers' => array( 'aside' ) ) ); ?>
							</span>
							<div class="ghahghah-faq__form-links">
								<?php if ( '' !== $wholesale_url && '' !== $forms_wh ) : ?>
									<a class="ghahghah-faq__btn ghahghah-faq__btn--outline" href="<?php echo esc_url( $wholesale_url ); ?>">
										<span><?php echo esc_html( $forms_wh ); ?></span>
										<?php ghahghah_the_form_icon( 'arrow-left', array( 'modifiers' => array( 'action' ) ) ); ?>
									</a>
								<?php endif; ?>
								<?php if ( '' !== $agency_url && '' !== $forms_ag ) : ?>
									<a class="ghahghah-faq__btn ghahghah-faq__btn--outline" href="<?php echo esc_url( $agency_url ); ?>">
										<span><?php echo esc_html( $forms_ag ); ?></span>
										<?php ghahghah_the_form_icon( 'arrow-left', array( 'modifiers' => array( 'action' ) ) ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</section>
				<?php endif; ?>
			</aside>
		</div>
	</div>
</div>
