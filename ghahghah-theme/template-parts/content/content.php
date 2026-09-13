<?php
/**
 * Default content template part.
 *
 * @package Ghahghah
 */

declare(strict_types=1);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
	<header class="entry__header">
		<?php
		if ( is_singular() ) {
			the_title( '<h1 class="entry__title">', '</h1>' );
		} else {
			the_title(
				sprintf( '<h2 class="entry__title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ),
				'</a></h2>'
			);
		}
		?>
	</header>

	<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
		<figure class="entry__media">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'ghahghah-card' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="entry__content">
		<?php
		if ( is_singular() ) {
			the_content();
		} else {
			the_excerpt();
		}
		?>
	</div>
</article>
