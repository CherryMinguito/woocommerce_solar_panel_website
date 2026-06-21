<?php
/**
 * Shop by category tile grid.
 *
 * @package Sunrooflighting
 */

$departments = jcs_get_departments();
?>
<section class="jcs-section jcs-categories">
	<div class="jcs-container">
		<h2 class="jcs-section-title"><?php esc_html_e( 'Explore Solar Solutions', 'sunrooflighting' ); ?></h2>
		<div class="jcs-category-grid">
			<?php if ( ! empty( $departments ) && ! is_wp_error( $departments ) ) : ?>
				<?php foreach ( $departments as $dept ) : ?>
					<a href="<?php echo esc_url( get_term_link( $dept ) ); ?>" class="jcs-category-tile">
						<span class="jcs-category-icon"><?php echo esc_html( jcs_category_icon( $dept->slug ) ); ?></span>
						<span class="jcs-category-name"><?php echo esc_html( $dept->name ); ?></span>
					</a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
