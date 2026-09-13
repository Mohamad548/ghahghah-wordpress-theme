<?php
/**
 * Single content template part.
 *
 * @package Ghahghah
 */

declare(strict_types=1);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
	<header class="entry__header">
		<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="entry__media">
			<?php the_post_thumbnail( 'ghahghah-hero' ); ?>
		</figure>
	<?php endif; ?>

	<div class="entry__content">
		<?php the_content(); ?>
	</div>
</article>
