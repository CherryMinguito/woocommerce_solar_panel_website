<?php
/**
 * Savings calculator page template.
 *
 * @package Sunrooflighting
 */

get_header();
?>

<main id="main-content" class="jcs-page jcs-page-calculator">
	<div class="jcs-container">
		<header class="jcs-page-header">
			<h1><?php esc_html_e( 'Solar Savings Calculator', 'sunrooflighting' ); ?></h1>
			<p><?php esc_html_e( 'Upload your utility bill or enter your usage to see how much you could save with solar.', 'sunrooflighting' ); ?></p>
		</header>
		<?php echo do_shortcode( '[solar_calculator]' ); ?>
	</div>
</main>

<?php
get_footer();
