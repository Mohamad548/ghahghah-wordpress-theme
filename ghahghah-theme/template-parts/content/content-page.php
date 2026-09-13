<?php
/**
 * Page content template part.
 *
 * @package Ghahghah
 */

declare(strict_types=1);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>
	<header class="entry__header">
		<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>
	</header>

	<div class="entry__content">
		<?php the_content(); ?>
	</div>
</article>
