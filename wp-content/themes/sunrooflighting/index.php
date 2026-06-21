<?php
/**
 * Fallback index template.
 *
 * @package Jed_Construction_Supply
 */

get_header();
?>

<main id="main-content" class="jcs-main">
	<div class="jcs-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<h1><?php the_title(); ?></h1>
					<?php the_content(); ?>
				</article>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
