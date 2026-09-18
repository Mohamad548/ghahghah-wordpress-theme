<?php
/**
 * Site footer markup.
 *
 * @package Ghahghah
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ghahghah_collab_on     = (bool) ghahghah_get_footer_mod( 'ghahghah_footer_collab_enabled' );
$ghahghah_collab_title  = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_collab_title' ) );
$ghahghah_collab_text   = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_collab_text' ) );
$ghahghah_btn_primary   = ghahghah_get_footer_collab_button( 'primary' );
$ghahghah_btn_secondary = ghahghah_get_footer_collab_button( 'secondary' );
$ghahghah_show_collab   = $ghahghah_collab_on && ( '' !== $ghahghah_collab_title || null !== $ghahghah_btn_primary || null !== $ghahghah_btn_secondary );

$ghahghah_intro        = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_intro' ) );
$ghahghah_logo_desktop = ghahghah_get_footer_logo_url();
$ghahghah_logo_mobile  = ghahghah_get_footer_logo_mobile_url();
$ghahghah_logo_dims    = ghahghah_get_footer_logo_dimensions( $ghahghah_logo_desktop );
$ghahghah_logo_alt     = get_bloginfo( 'name', 'display' );

$ghahghah_quick_title    = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_col_quick_title' ) );
$ghahghah_business_title = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_col_business_title' ) );
$ghahghah_contact_title  = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_col_contact_title' ) );

$ghahghah_has_quick    = ghahghah_footer_menu_has_items( 'footer' );
$ghahghah_has_business = ghahghah_footer_menu_has_items( 'footer_business' );
$ghahghah_contact_rows = ghahghah_get_footer_contact_rows();
$ghahghah_address      = ghahghah_get_footer_address_linear();
$ghahghah_socials      = ghahghah_get_footer_socials();
$ghahghah_has_contact  = array() !== $ghahghah_contact_rows;
$ghahghah_has_address  = '' !== $ghahghah_address;
$ghahghah_has_socials  = array() !== $ghahghah_socials;
$ghahghah_show_strip   = $ghahghah_has_address || $ghahghah_has_socials;

$ghahghah_legal   = trim( (string) ghahghah_get_footer_mod( 'ghahghah_footer_legal_text' ) );
$ghahghah_privacy = ghahghah_get_footer_privacy_url();
$ghahghah_back    = (bool) ghahghah_get_footer_mod( 'ghahghah_footer_back_to_top' );
?>
<footer class="ghahghah-footer" role="contentinfo">
	<div class="ghahghah-footer__glow" aria-hidden="true"></div>
	<div class="ghahghah-footer__shell">
		<?php if ( $ghahghah_show_collab ) : ?>
			<section class="ghahghah-footer__collab" aria-labelledby="ghahghah-footer-collab-title">
				<div class="ghahghah-footer__collab-copy">
					<?php if ( '' !== $ghahghah_collab_title ) : ?>
						<h2 id="ghahghah-footer-collab-title" class="ghahghah-footer__collab-title">
							<?php echo esc_html( $ghahghah_collab_title ); ?>
						</h2>
					<?php endif; ?>
					<?php if ( '' !== $ghahghah_collab_text ) : ?>
						<p class="ghahghah-footer__collab-text"><?php echo esc_html( $ghahghah_collab_text ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( null !== $ghahghah_btn_primary || null !== $ghahghah_btn_secondary ) : ?>
					<div class="ghahghah-footer__collab-actions">
						<?php if ( null !== $ghahghah_btn_primary ) : ?>
							<a class="ghahghah-footer__btn ghahghah-footer__btn--solid" href="<?php echo esc_url( $ghahghah_btn_primary['url'] ); ?>">
								<?php echo esc_html( $ghahghah_btn_primary['label'] ); ?>
							</a>
						<?php endif; ?>
						<?php if ( null !== $ghahghah_btn_secondary ) : ?>
							<a class="ghahghah-footer__btn ghahghah-footer__btn--ghost" href="<?php echo esc_url( $ghahghah_btn_secondary['url'] ); ?>">
								<?php echo esc_html( $ghahghah_btn_secondary['label'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( $ghahghah_show_strip ) : ?>
			<div class="ghahghah-footer__strip">
				<?php if ( $ghahghah_has_address ) : ?>
					<div class="ghahghah-footer__address" aria-label="<?php echo esc_attr__( 'نشانی', 'ghahghah' ); ?>">
						<span class="ghahghah-footer__icon-wrap ghahghah-footer__icon-wrap--sm" aria-hidden="true">
							<?php ghahghah_the_footer_icon( 'map-pin' ); ?>
						</span>
						<p class="ghahghah-footer__address-text"><?php echo esc_html( $ghahghah_address ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $ghahghah_has_socials ) : ?>
					<ul class="ghahghah-footer__social list-reset" aria-label="<?php echo esc_attr__( 'شبکه‌های اجتماعی', 'ghahghah' ); ?>">
						<?php foreach ( $ghahghah_socials as $ghahghah_social ) : ?>
							<li>
								<a
									class="ghahghah-footer__social-link"
									href="<?php echo esc_url( $ghahghah_social['url'] ); ?>"
									target="_blank"
									rel="noopener noreferrer"
									aria-label="<?php echo esc_attr( $ghahghah_social['label'] ); ?>"
								>
									<?php ghahghah_the_footer_social_icon( $ghahghah_social ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="ghahghah-footer__main">
			<div class="ghahghah-footer__brand">
				<a class="ghahghah-footer__logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<picture>
						<source media="(max-width: 63.99rem)" srcset="<?php echo esc_url( $ghahghah_logo_mobile ); ?>" />
						<img
							class="ghahghah-footer__logo"
							src="<?php echo esc_url( $ghahghah_logo_desktop ); ?>"
							alt="<?php echo esc_attr( $ghahghah_logo_alt ); ?>"
							width="<?php echo esc_attr( (string) $ghahghah_logo_dims[0] ); ?>"
							height="<?php echo esc_attr( (string) $ghahghah_logo_dims[1] ); ?>"
							decoding="async"
							loading="lazy"
						/>
					</picture>
				</a>
				<?php if ( '' !== $ghahghah_intro ) : ?>
					<p class="ghahghah-footer__intro"><?php echo esc_html( $ghahghah_intro ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $ghahghah_has_quick ) : ?>
				<section class="ghahghah-footer__col" aria-labelledby="ghahghah-footer-quick-title">
					<?php if ( '' !== $ghahghah_quick_title ) : ?>
						<h2 id="ghahghah-footer-quick-title" class="ghahghah-footer__col-title">
							<?php echo esc_html( $ghahghah_quick_title ); ?>
						</h2>
					<?php endif; ?>
					<?php
					ghahghah_the_footer_menu(
						'footer',
						'ghahghah-footer-nav-quick',
						$ghahghah_quick_title !== '' ? $ghahghah_quick_title : __( 'دسترسی سریع', 'ghahghah' )
					);
					?>
				</section>
			<?php endif; ?>

			<?php if ( $ghahghah_has_business ) : ?>
				<section class="ghahghah-footer__col" aria-labelledby="ghahghah-footer-business-title">
					<?php if ( '' !== $ghahghah_business_title ) : ?>
						<h2 id="ghahghah-footer-business-title" class="ghahghah-footer__col-title">
							<?php echo esc_html( $ghahghah_business_title ); ?>
						</h2>
					<?php endif; ?>
					<?php
					ghahghah_the_footer_menu(
						'footer_business',
						'ghahghah-footer-nav-business',
						$ghahghah_business_title !== '' ? $ghahghah_business_title : __( 'همکاری', 'ghahghah' )
					);
					?>
				</section>
			<?php endif; ?>

			<?php if ( $ghahghah_has_contact ) : ?>
				<section class="ghahghah-footer__col ghahghah-footer__contact-col" aria-labelledby="ghahghah-footer-contact-title">
					<?php if ( '' !== $ghahghah_contact_title ) : ?>
						<h2 id="ghahghah-footer-contact-title" class="ghahghah-footer__col-title">
							<?php echo esc_html( $ghahghah_contact_title ); ?>
						</h2>
					<?php endif; ?>
					<dl class="ghahghah-footer__contact">
						<?php foreach ( $ghahghah_contact_rows as $ghahghah_row ) : ?>
							<div class="ghahghah-footer__contact-row">
								<dt>
									<span class="ghahghah-footer__icon-wrap" aria-hidden="true">
										<?php
										if ( 'phone' === $ghahghah_row['type'] ) {
											ghahghah_the_footer_icon( 'phone' );
										} else {
											ghahghah_the_footer_icon( 'mail' );
										}
										?>
									</span>
									<span class="screen-reader-text">
										<?php
										echo 'phone' === $ghahghah_row['type']
											? esc_html__( 'تلفن', 'ghahghah' )
											: esc_html__( 'ایمیل', 'ghahghah' );
										?>
									</span>
								</dt>
								<dd>
									<a
										class="ghahghah-footer__ltr"
										href="<?php echo esc_url( $ghahghah_row['href'] ); ?>"
										dir="ltr"
									><?php echo esc_html( $ghahghah_row['value'] ); ?></a>
								</dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</section>
			<?php endif; ?>
		</div>

		<div class="ghahghah-footer__meta">
			<?php
			$ghahghah_designer_url = function_exists( 'ghahghah_get_designer_page_url' )
				? ghahghah_get_designer_page_url()
				: '';
			?>
			<?php if ( '' !== $ghahghah_legal || '' !== $ghahghah_designer_url ) : ?>
				<p class="ghahghah-footer__legal">
					<?php if ( '' !== $ghahghah_legal ) : ?>
						<span class="ghahghah-footer__legal-copy"><?php echo esc_html( $ghahghah_legal ); ?></span>
						<span class="ghahghah-footer__legal-sep" aria-hidden="true"> · </span>
					<?php endif; ?>
					<?php if ( '' !== $ghahghah_designer_url ) : ?>
						<a class="ghahghah-footer__designer" href="<?php echo esc_url( $ghahghah_designer_url ); ?>">
							<?php esc_html_e( 'توسعه و طراحی محمد محمودی', 'ghahghah' ); ?>
						</a>
					<?php else : ?>
						<span class="ghahghah-footer__designer"><?php esc_html_e( 'توسعه و طراحی محمد محمودی', 'ghahghah' ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<div class="ghahghah-footer__meta-actions">
				<?php if ( '' !== $ghahghah_privacy ) : ?>
					<a class="ghahghah-footer__privacy" href="<?php echo esc_url( $ghahghah_privacy ); ?>">
						<?php esc_html_e( 'حریم خصوصی', 'ghahghah' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $ghahghah_back ) : ?>
					<a
						class="ghahghah-footer__back"
						href="#ghahghah-top"
						data-ghahghah-back-to-top
						aria-label="<?php echo esc_attr__( 'بازگشت به بالای صفحه', 'ghahghah' ); ?>"
					>
						<?php ghahghah_the_footer_icon( 'arrow-up' ); ?>
						<span class="ghahghah-footer__back-label"><?php esc_html_e( 'بازگشت به بالا', 'ghahghah' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>
