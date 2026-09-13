<?php
/**
 * Archive item template part.
 *
 * @package Ghahghah
 */

declare(strict_types=1);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--archive' ); ?>>
	<header class="entry__header">
		<?php
		the_title(
			sprintf( '<h2 class="entry__title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ),
			'</a></h2>'
		);
		?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__media">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( 'ghahghah-card' ); ?>
			</a>
		</figure>
	<?php endif; ?>

	<div class="entry__summary">
		<?php the_excerpt(); ?>
	</div>
</article>
